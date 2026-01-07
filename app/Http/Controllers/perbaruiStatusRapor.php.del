<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Catatan;
use App\Models\StatusRapor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RaporController extends Controller
{
    /**
     * FUNGSI INTI: Menghitung & Menyimpan Status Kelengkapan ke Tabel Opsi B
     * Fungsi ini bisa dipanggil secara internal oleh fungsi lain.
     */
    public function perbaruiStatusRapor($id_siswa, $semester, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semester) == 'GANJIL') ? 1 : 2;

        // 1. Ambil semua mapel yang harus diikuti siswa berdasarkan kelasnya
        $siswa = Siswa::find($id_siswa);
        $daftarMapel = DB::table('pembelajaran')
            ->where('id_kelas', $siswa->id_kelas)
            ->pluck('id_mapel');

        $totalMapelSeharusnya = $daftarMapel->count();
        $mapelTuntas = 0;

        // 2. Cek Kelengkapan 2 Sumatif + 1 Project per Mapel
        foreach ($daftarMapel as $id_mapel) {
            $countSumatif = DB::table('nilai_sumatif') // Sesuaikan nama tabel Anda
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->count();

            $countProject = DB::table('nilai_project') // Sesuaikan nama tabel Anda
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->count();

            if ($countSumatif >= 2 && $countProject >= 1) {
                $mapelTuntas++;
            }
        }

        // 3. Cek apakah Wali Kelas sudah isi Catatan
        $isCatatanReady = Catatan::where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->exists();

        // 4. Tentukan Status Akhir
        $statusAkhir = ($mapelTuntas == $totalMapelSeharusnya && $isCatatanReady) 
                        ? 'Siap Cetak' 
                        : 'Belum Lengkap';

        // 5. Simpan/Update ke tabel status_rapor (Opsi B)
        return StatusRapor::updateOrCreate(
            [
                'id_siswa' => $id_siswa,
                'semester' => $semesterInt,
                'tahun_ajaran' => $tahun_ajaran,
            ],
            [
                'id_kelas' => $siswa->id_kelas,
                'total_mapel_seharusnya' => $totalMapelSeharusnya,
                'mapel_tuntas_input' => $mapelTuntas,
                'is_catatan_wali_ready' => $isCatatanReady,
                'status_akhir' => $statusAkhir,
            ]
        );
    }
}