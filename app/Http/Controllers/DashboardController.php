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
        $rentangNilai = $request->rentang_nilai ?? 'lt78';

        // =====================
        // LOGIKA DEFAULT TAHUN AJARAN & SEMESTER
        // =====================
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');

        if ($bulanSekarang < 7) {
            $defaultTA1 = $tahunSekarang - 1;
            $defaultTA2 = $tahunSekarang;
            $defaultSemester = 'Genap';
        } else {
            $defaultTA1 = $tahunSekarang;
            $defaultTA2 = $tahunSekarang + 1;
            $defaultSemester = 'Ganjil';
        }

        $defaultTahunAjaran = $defaultTA1 . '/' . $defaultTA2;
                
        // =====================
        // CARD STATISTIK
        // =====================
        $totalSiswa = Siswa::count();
        $totalGuru  = Guru::count();
        $totalKelas = Kelas::count();
        $totalMapel = MataPelajaran::count();

            // =====================
            // TAHUN AJARAN & SEMESTER AKTIF (DARI REQUEST / DEFAULT)
            // =====================
            $tahunAjaranAktif = $request->tahun_ajaran ?? $defaultTahunAjaran;

            $semesterRequest = $request->semester ?? $defaultSemester;

            $semesterAktif = match ($semesterRequest) {
                'Ganjil' => 1,
                'Genap'  => 2,
                default  => 1
            };

        // =====================
        // LIST JURUSAN
        // =====================
        $jurusanList = Kelas::select('jurusan')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan');

        $jurusan = $request->jurusan ?? null;

        // =====================
        // LIST TAHUN AJARAN
        // =====================
        $tahunAjaran = collect();

        for ($i = -1; $i <= 5; $i++) {
            $awal = $tahunSekarang + $i;
            $akhir = $awal + 1;
            $tahunAjaran->push($awal . '/' . $akhir);
        }
     
        // =====================
// PROGRESS INPUT NILAI (PER JURUSAN BERDASARKAN TINGKAT)
// =====================

$tingkatFilter = $request->tingkat ?? null;

$progressLabels = [];
$progressData   = [];
$progressDetail = [];

// ambil jurusan yang ADA di tingkat tsb
$jurusanListProgress = Kelas::when($tingkatFilter, function ($q) use ($tingkatFilter) {
        $q->where('tingkat', $tingkatFilter);
    })
    ->select('jurusan')
    ->distinct()
    ->orderBy('jurusan')
    ->pluck('jurusan');

foreach ($jurusanListProgress as $jurusanNama) {

    $progress = $this->hitungProgressByJurusan(
        $jurusanNama,
        $tingkatFilter,
        $tahunAjaranAktif,
        $semesterAktif
    );

    $progressLabels[] = $jurusanNama;
    $progressData[]   = $progress;

    $progressDetail[$jurusanNama] = [
        'progress' => $progress,
        'belum' => $this->getDetailMapelBelumInputByJurusan(
            $jurusanNama,
            $tingkatFilter,
            $tahunAjaranAktif,
            $semesterAktif
        )
    ];
}

// =====================
// FILTER KELAS (STATISTIK NILAI)
// =====================
$kelasList = Kelas::orderBy('nama_kelas')->get();
$queryNilai = NilaiAkhir::where('semester', $semesterAktif)
    ->where('nilai_akhir', '>', 0);

if ($request->filled('tahun_ajaran')) {
    $queryNilai->where('tahun_ajaran', $request->tahun_ajaran);
} else {
    $queryNilai->where('tahun_ajaran', $tahunAjaranAktif);
}

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
        // DETAIL SISWA NILAI MERAH (<78) + MAPEL
        // =====================
        $detailNilaiMerahQuery = NilaiAkhir::with(['siswa', 'mapel'])
    ->where('tahun_ajaran', $tahunAjaranAktif)
    ->where('semester', $semesterAktif)
    ->where('nilai_akhir', '>', 0);

switch ($rentangNilai) {
    case '78_85':
        $detailNilaiMerahQuery->whereBetween('nilai_akhir', [78, 85]);
        break;

    case '86_92':
        $detailNilaiMerahQuery->whereBetween('nilai_akhir', [86, 92]);
        break;

    case 'gte93':
        $detailNilaiMerahQuery->where('nilai_akhir', '>=', 93);
        break;

    default: // lt78
        $detailNilaiMerahQuery->where('nilai_akhir', '<', 78);
        break;
}

        if ($request->filled('kelas')) {
            $detailNilaiMerahQuery->where('id_kelas', $request->kelas);
        }

        $detailNilaiMerah = $detailNilaiMerahQuery
            ->orderBy('id_siswa')
            ->orderBy('nilai_akhir')
            ->get();

        $judulDetailNilai = match ($rentangNilai) {
    '78_85' => 'Nilai 78 – 85',
    '86_92' => 'Nilai 86 – 92',
    'gte93' => 'Nilai ≥ 93',
    default => 'Nilai di Bawah 78'
};

        // =====================
        // STATUS RAPOR
        // =====================
        $statusRapor = $this->getStatusRapor();

        // =====================
        // UPCOMING EVENT (H sampai H+2)
        // =====================
        $events = Event::whereBetween(
                'tanggal',
                [
                    Carbon::today(),               // hari ini
                    Carbon::today()->addDays(2)    // H+2
                ]
            )
            ->orderBy('tanggal')
            ->get();

            // =====================
            // NOTIFIKASI (H sampai H+2)
            // =====================
            $notifications = Notifikasi::whereBetween(
                    'tanggal',
                    [
                        Carbon::today(),
                        Carbon::today()->addDays(2)
                    ]
                )
                ->orderBy('tanggal')
                ->get();

        return view('dashboard', compact(
            'totalSiswa',
            'totalGuru',
            'totalKelas',
            'totalMapel',
            'jurusanList',
            'progressLabels',
            'progressData',
            'progressDetail',
            'kelasList',
            'statistikNilai',
            'statusRapor',
            'events',
            'notifications',
            'detailNilaiMerah',
            'semesterAktif',
            'tahunAjaran',
            'defaultSemester',
            'defaultTahunAjaran',
            'judulDetailNilai'
        ));
    }

    // =====================
    // HITUNG PROGRESS PER TINGKAT
    // =====================
   private function hitungProgressByJurusan(
    $jurusan,
    $tingkat,
    $tahunAjaran,
    $semester
) {
    $kelasIds = Kelas::where('jurusan', $jurusan)
        ->when($tingkat, fn($q) => $q->where('tingkat', $tingkat))
        ->pluck('id_kelas');

    if ($kelasIds->isEmpty()) return 0;

    $mapelList = Pembelajaran::whereIn('id_kelas', $kelasIds)
        ->select('id_mapel')
        ->distinct()
        ->get();

    $totalMapel = $mapelList->count();
    $mapelLengkap = 0;

    foreach ($mapelList as $mapel) {

        $kelasMapel = Pembelajaran::where('id_mapel', $mapel->id_mapel)
            ->whereIn('id_kelas', $kelasIds)
            ->pluck('id_kelas');

        $totalTarget = Siswa::whereIn('id_kelas', $kelasMapel)->count();
        if ($totalTarget === 0) continue;

        $totalSudah = NilaiAkhir::whereIn('id_kelas', $kelasMapel)
            ->where('id_mapel', $mapel->id_mapel)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->where('nilai_akhir', '>', 0)
            ->distinct('id_siswa')
            ->count('id_siswa');

        if ($totalSudah === $totalTarget) {
            $mapelLengkap++;
        }
    }

    return $totalMapel > 0
        ? round(($mapelLengkap / $totalMapel) * 100)
        : 0;
}

// =====================
// DETAIL MAPEL BELUM INPUT NILAI
// =====================
private function getDetailMapelBelumInputByJurusan(
    $jurusan,
    $tingkat,
    $tahunAjaran,
    $semester
) {
    $kelasIds = Kelas::where('jurusan', $jurusan)
        ->when($tingkat, fn($q) => $q->where('tingkat', $tingkat))
        ->pluck('id_kelas');

    if ($kelasIds->isEmpty()) return collect();

    $mapelList = Pembelajaran::whereIn('id_kelas', $kelasIds)
        ->select('id_mapel')
        ->distinct()
        ->get();

    $mapelBelum = collect();

    foreach ($mapelList as $mapel) {

        $kelasMapel = Pembelajaran::where('id_mapel', $mapel->id_mapel)
            ->whereIn('id_kelas', $kelasIds)
            ->pluck('id_kelas');

        $totalTarget = Siswa::whereIn('id_kelas', $kelasMapel)->count();
        if ($totalTarget === 0) continue;

        $totalSudah = NilaiAkhir::whereIn('id_kelas', $kelasMapel)
            ->where('id_mapel', $mapel->id_mapel)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->where('nilai_akhir', '>', 0)
            ->distinct('id_siswa')
            ->count('id_siswa');

        if ($totalSudah < $totalTarget) {
            $mapelBelum->push(
                MataPelajaran::where('id_mapel', $mapel->id_mapel)
                    ->value('nama_mapel')
            );
        }
    }

    return $mapelBelum;
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
        ->where('nilai_akhir', '>', 0)
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