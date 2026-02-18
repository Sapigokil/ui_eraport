<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Pembelajaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MutasiPindahController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $siswaAktif = collect();

        // Load siswa jika ada filter kelas
        if ($request->has('id_kelas_asal') && $request->id_kelas_asal != '') {
            $siswaAktif = Siswa::where('status', 'aktif')
                        ->where('id_kelas', $request->id_kelas_asal)
                        ->orderBy('nama_siswa')
                        ->get();
        }

        return view('mutasi.pindah_index', compact('kelas', 'siswaAktif'));
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

                // B. Migrasi Nilai Berjalan (Hanya Mapel yang Sama)
                if (!empty($mapelSama)) {
                    // Update Sumatif
                    DB::table('sumatif')
                        ->where('id_siswa', $idSiswa)
                        ->where('id_kelas', $request->id_kelas_asal) // Pastikan hanya data kelas lama
                        ->where('semester', $semester)
                        ->where('tahun_ajaran', $tahunAjaran)
                        ->whereIn('id_mapel', $mapelSama) // FILTER MAPEL SAMA
                        ->update(['id_kelas' => $request->id_kelas_tujuan]);

                    // Update Project (P5) - Jika logic mapel P5 sama
                    DB::table('project')
                        ->where('id_siswa', $idSiswa)
                        ->where('id_kelas', $request->id_kelas_asal)
                        ->where('semester', $semester)
                        ->where('tahun_ajaran', $tahunAjaran)
                        ->whereIn('id_mapel', $mapelSama)
                        ->update(['id_kelas' => $request->id_kelas_tujuan]);
                    
                    // Update Catatan (Jika catatan terikat Mapel)
                    DB::table('catatan')
                        ->where('id_siswa', $idSiswa)
                        ->where('id_kelas', $request->id_kelas_asal)
                        ->where('semester', $semester)
                        ->where('tahun_ajaran', $tahunAjaran)
                        ->whereIn('id_mapel', $mapelSama)
                        ->update(['id_kelas' => $request->id_kelas_tujuan]);
                }

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