<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiAkhir;
use App\Models\Pembelajaran;
use App\Models\Event;
use App\Models\Notifikasi;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index(Request $request)
    {

        
        // =====================
        // CARD STATISTIK
        // =====================
        $totalSiswa = Siswa::count();
        $totalGuru  = Guru::count();
        $totalKelas = Kelas::count();
        $totalMapel = MataPelajaran::count();

        // =====================
        // TAHUN AJARAN & SEMESTER AKTIF
        // =====================
        $tahunAjaranAktif = '2025/2026';
        $semesterAktif = 1; // 1 = Ganjil, 2 = Genap

        // =====================
        // LIST JURUSAN
        // =====================
        $jurusanList = Kelas::select('jurusan')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan');

        $jurusan = $request->jurusan ?? null;

        // =====================
        // PROGRESS INPUT NILAI
        // =====================
        $progressLabels = ['10','11','12']; // contoh
        $progressData = [];

foreach ($progressLabels as $label => $tingkat) {
    $progressData[] = $this->hitungProgressByTingkat(
        $tingkat,
        $jurusan,
        $tahunAjaranAktif,
        $semesterAktif
    );
}

// =====================
// FILTER KELAS (STATISTIK NILAI)
// =====================
$kelasList = Kelas::orderBy('nama_kelas')->get();
$queryNilai = NilaiAkhir::where('tahun_ajaran', $tahunAjaranAktif)
    ->where('semester', $semesterAktif);

if ($request->filled('kelas')) {
    $queryNilai->where('id_kelas', $request->kelas);
}


        // =====================
        // STATISTIK NILAI
        // =====================
        $statistikNilai = [
            (clone $queryNilai)->where('nilai_akhir', '<', 78)->count(),
            (clone $queryNilai)->whereBetween('nilai_akhir', [78, 85])->count(),
            (clone $queryNilai)->whereBetween('nilai_akhir', [86, 92])->count(),
            (clone $queryNilai)->where('nilai_akhir', '>=', 93)->count(),
        ];

        // =====================
        // STATUS RAPOR
        // =====================
        $statusRapor = $this->getStatusRapor();

        // =====================
        // UPCOMING EVENT
        // =====================
        $events = Event::whereDate('tanggal', '>=', Carbon::today()->subDays(3))
            ->orderBy('tanggal')
            ->get();

        // Ambil notifikasi terbaru (misal 5 notifikasi terakhir)
        $notifications = Notifikasi::latest('created_at')->take(5)->get();

        return view('dashboard', compact(
            'totalSiswa',
            'totalGuru',
            'totalKelas',
            'totalMapel',
            'jurusanList',
            'progressLabels',
            'progressData',
            'kelasList',
            'statistikNilai',
            'statusRapor',
            'events',
            'notifications'
        ));
    }

    // =====================
    // HITUNG PROGRESS PER TINGKAT
    // =====================
    private function hitungProgressByTingkat(
    $tingkat,
    $jurusan = null,
    $tahunAjaran,
    $semester
) {
    // 1. Ambil ID kelas berdasarkan tingkat (+ jurusan kalau ada)
    $kelasQuery = Kelas::where('tingkat', $tingkat);

    if ($jurusan) {
        $kelasQuery->where('jurusan', $jurusan);
    }

    $kelasIds = $kelasQuery->pluck('id_kelas');

    if ($kelasIds->isEmpty()) {
        return 0;
    }

    // 2. TOTAL YANG SEHARUSNYA DIISI
    // Berdasarkan data REAL di tabel nilai_akhir
    $totalHarusDiisi = NilaiAkhir::whereIn('id_kelas', $kelasIds)
    ->where('tahun_ajaran', $tahunAjaran)
    ->where('semester', $semester)
    ->select('id_siswa', 'id_mapel')
    ->distinct()
    ->count();

    if ($totalHarusDiisi === 0) {
        return 0;
    }

    // 3. TOTAL YANG SUDAH TERISI
    $totalTerisi = NilaiAkhir::whereIn('id_kelas', $kelasIds)
    ->where('tahun_ajaran', $tahunAjaran)
    ->where('semester', $semester)
    ->where('nilai_akhir', '>', 0) // 🔥 FIX PENTING
    ->select('id_siswa', 'id_mapel')
    ->distinct()
    ->count();

    // 4. HITUNG PROGRESS (%)
    return round(($totalTerisi / $totalHarusDiisi) * 100, 1);
}

// =====================
// STATUS RAPOR
// =====================
private function getStatusRapor()
{
    return Kelas::orderBy('nama_kelas')->get()->map(function ($kelas) {

        // TOTAL MAPEL SESUAI PEMBELAJARAN KELAS
        $totalMapel = Pembelajaran::where('id_kelas', $kelas->id_kelas)
            ->distinct()
            ->count('id_mapel');

        // MAPEL YANG SUDAH ADA NILAI
        $mapelTerisi = NilaiAkhir::where('id_kelas', $kelas->id_kelas)
            ->distinct()
            ->count('id_mapel');

        // LOGIKA STATUS
        if ($mapelTerisi === 0) {
            return [
                'kelas' => $kelas->nama_kelas,
                'tingkat' => $kelas->tingkat,
                'status' => 'Belum Input',
                'warna' => 'danger'
            ];
        }

        if ($mapelTerisi < $totalMapel) {
            return [
                'kelas' => $kelas->nama_kelas,
                'tingkat' => $kelas->tingkat,
                'status' => 'Belum Lengkap',
                'warna' => 'warning'
            ];
        }

        return [
            'kelas' => $kelas->nama_kelas,
            'tingkat' => $kelas->tingkat,
            'status' => 'Siap',
            'warna' => 'success'
        ];
    });
}

// =====================
// SIMPAN EVENT
// =====================
public function storeEvent(Request $request)
{
    $request->validate([
        'deskripsi' => 'required|string',
        'tanggal'   => 'required|date',
    ]);

    Event::create([
        'deskripsi' => $request->deskripsi,
        'tanggal'   => $request->tanggal,
    ]);

    return redirect()->back()->with('success', 'Event berhasil ditambahkan');
}

// =====================
// HAPUS EVENT
// =====================
public function destroy($id)
{
    Event::where('id_event', $id)->delete();

    return redirect()->back()->with('success', 'Event berhasil dihapus');
}

// =====================
// UPDATE EVENT
// =====================
public function update(Request $request, $id)
{
    $request->validate([
        'deskripsi' => 'required|string',
        'tanggal'   => 'required|date',
    ]);

    Event::where('id_event', $id)->update([
        'deskripsi' => $request->deskripsi,
        'tanggal'   => $request->tanggal,
    ]);

    return redirect()->back()->with('success', 'Event berhasil diperbarui');
}


}