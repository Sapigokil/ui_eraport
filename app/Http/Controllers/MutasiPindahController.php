<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Pembelajaran;
use App\Models\RiwayatPindahKelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MutasiPindahController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $siswaAktif = collect();

        // 1. FILTER SISWA AKTIF (Bagian Atas)
        if ($request->has('id_kelas_asal') && $request->id_kelas_asal != '') {
            $siswaAktif = Siswa::where('status', 'aktif')
                        ->where('id_kelas', $request->id_kelas_asal)
                        ->orderBy('nama_siswa')
                        ->get();
        }

        // 2. GENERATE LIST TAHUN AJARAN (Untuk Dropdown Filter History)
        $tahunSekarang = date('Y');
        $tahunAjaranList = [];
        for ($tahun = $tahunSekarang + 1; $tahun >= $tahunSekarang - 3; $tahun--) {
            $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
        }

        // 3. FILTER RIWAYAT (Bagian Bawah)
        $queryRiwayat = RiwayatPindahKelas::with(['siswa', 'kelasLama', 'kelasBaru']);

        // Filter by Nama
        if ($request->filled('h_nama')) {
            $queryRiwayat->whereHas('siswa', function($q) use ($request) {
                $q->where('nama_siswa', 'like', '%' . $request->h_nama . '%');
            });
        }

        // Filter by Kelas (Bisa Kelas Lama atau Kelas Baru)
        if ($request->filled('h_kelas')) {
            $h_kelas = $request->h_kelas;
            $queryRiwayat->where(function($q) use ($h_kelas) {
                $q->where('id_kelas_lama', $h_kelas)
                  ->orWhere('id_kelas_baru', $h_kelas);
            });
        }

        // Filter by Tahun Ajaran (Konversi Tahun Ajaran ke Range Tanggal)
        // Misal: 2025/2026 -> 1 Juli 2025 s/d 30 Juni 2026
        if ($request->filled('h_ta')) {
            $ta = explode('/', $request->h_ta);
            if (count($ta) == 2) {
                $start_date = $ta[0] . '-07-01';
                $end_date   = $ta[1] . '-06-30';
                $queryRiwayat->whereBetween('tgl_pindah', [$start_date, $end_date]);
            }
        }

        // Gunakan pagination agar tidak berat jika data ribuan
        $riwayat = $queryRiwayat->orderBy('tgl_pindah', 'desc')
                                ->paginate(10)
                                ->appends($request->query()); // appends menjaga filter tetap aktif saat pindah halaman

        return view('mutasi.pindah_index', compact('kelas', 'siswaAktif', 'riwayat', 'tahunAjaranList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kelas_asal' => 'required|exists:kelas,id_kelas',
            'id_kelas_tujuan' => 'required|exists:kelas,id_kelas|different:id_kelas_asal',
            'siswa_ids' => 'required|array|min:1', // Array ID siswa yang dicentang
            'tgl_pindah' => 'required|date',
            'alasan' => 'required|string',
        ]);

        // Tentukan Periode Berjalan (Bisa diambil dari Session atau Helper Sekolah)
        // Asumsi: Kita ambil dari request atau setting global. 
        // Disini saya hardcode contoh logika periodenya, silakan sesuaikan dengan Global Helper Anda.
        $tahunSekarang = date('Y');
        $bulan = date('n');
        if ($bulan < 7) {
            $tahunAjaran = ($tahunSekarang - 1) . '/' . $tahunSekarang;
            $semester = 2; // Genap
        } else {
            $tahunAjaran = $tahunSekarang . '/' . ($tahunSekarang + 1);
            $semester = 1; // Ganjil
        }

        DB::beginTransaction();
        try {
            // 1. ANALISA MAPEL (Logika Irisan)
            // Ambil ID Mapel di Kelas Lama
            $mapelLama = Pembelajaran::where('id_kelas', $request->id_kelas_asal)
                            ->pluck('id_mapel'); // Collection of IDs

            // Ambil ID Mapel di Kelas Baru
            $mapelBaru = Pembelajaran::where('id_kelas', $request->id_kelas_tujuan)
                            ->pluck('id_mapel');

            // Cari Irisan (Intersection): Mapel yang ada di KEDUA kelas
            $mapelSama = $mapelLama->intersect($mapelBaru)->values()->all();

            // 2. LOOPING SISWA
            foreach ($request->siswa_ids as $idSiswa) {
                $siswa = Siswa::find($idSiswa);
                
                // Safety check: Pastikan siswa memang di kelas asal (cegah manipulasi HTML)
                if(!$siswa || $siswa->id_kelas != $request->id_kelas_asal) continue;

                // A. Catat Riwayat
                DB::table('riwayat_pindah_kelas')->insert([
                    'id_siswa' => $siswa->id_siswa,
                    'id_kelas_lama' => $request->id_kelas_asal,
                    'id_kelas_baru' => $request->id_kelas_tujuan,
                    'tgl_pindah' => $request->tgl_pindah,
                    'alasan' => $request->alasan,
                    'user_input' => Auth::user()->name ?? 'System',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // B. Migrasi Nilai Berjalan                
                // 1. Update Sumatif & Project (Terikat dengan Mata Pelajaran)
                if (!empty($mapelSama)) {
                    // Update Sumatif
                    DB::table('sumatif')
                        ->where('id_siswa', $idSiswa)
                        ->where('id_kelas', $request->id_kelas_asal)
                        ->where('semester', $semester)
                        ->where('tahun_ajaran', $tahunAjaran)
                        ->whereIn('id_mapel', $mapelSama) // Hanya mapel yang match
                        ->update(['id_kelas' => $request->id_kelas_tujuan]);

                    // Update Project
                    DB::table('project')
                        ->where('id_siswa', $idSiswa)
                        ->where('id_kelas', $request->id_kelas_asal)
                        ->where('semester', $semester)
                        ->where('tahun_ajaran', $tahunAjaran)
                        ->whereIn('id_mapel', $mapelSama) // Hanya mapel yang match
                        ->update(['id_kelas' => $request->id_kelas_tujuan]);
                }

                // 2. Update Catatan Non-Akademik (Absensi, Ekskul, Wali Kelas)
                // Karena ini terikat pada Siswa & Kelas (Bukan Mapel), maka langsung dipindahkan saja
                DB::table('catatan')
                    ->where('id_siswa', $idSiswa)
                    ->where('id_kelas', $request->id_kelas_asal)
                    ->where('semester', $semester)
                    ->where('tahun_ajaran', $tahunAjaran)
                    // HAPUS BARIS INI: ->whereIn('id_mapel', $mapelSama) 
                    ->update(['id_kelas' => $request->id_kelas_tujuan]);

                // C. Pindahkan Siswa (Master Data)
                $siswa->update(['id_kelas' => $request->id_kelas_tujuan]);
                
                // D. Update Detail Siswa (Jika ada tabel detail_siswa yang menyimpan id_kelas)
                DB::table('detail_siswa')->where('id_siswa', $idSiswa)->update(['id_kelas' => $request->id_kelas_tujuan]);
            }

            DB::commit();
            return back()->with('success', 'Berhasil memindahkan ' . count($request->siswa_ids) . ' siswa dan migrasi nilai terkait.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}