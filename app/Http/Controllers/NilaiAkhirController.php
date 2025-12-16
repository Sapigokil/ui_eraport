<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiAkhir;
use App\Models\Pembelajaran;
use App\Models\Sumatif;
use App\Models\Project;


class NilaiAkhirController extends Controller
{
    private function mapSemesterToInt(string $semester): ?int
    {
        $map = [
            'GANJIL' => 1,
            'GENAP' => 2,
        ];
        // Menggunakan strtoupper dan trim untuk memastikan input konsisten
        return $map[strtoupper(trim($semester))] ?? null;
    }
    
    public function index(Request $request)
    {
        // ... (Pengambilan $kelas, $mapel, $siswa tetap sama) ...
    
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $mapel = collect();
        $siswa = collect();
        $rekap = [];
        $error = null;

        // 🛑 KOREKSI UTAMA: LOGIKA PENGAMBILAN MAPEL BERDASARKAN KELAS
        if ($request->id_kelas) {
            $mapel = Pembelajaran::with('mapel')
                ->where('id_kelas', $request->id_kelas)
                ->get()
                ->map(fn($p) => $p->mapel)
                ->filter()
                ->values();
        }
        // 🛑 END KOREKSI UTAMA
        
        if (
            $request->filled(['id_kelas', 'id_mapel', 'tahun_ajaran', 'semester'])
        ) {
            
            $semesterDB = $this->mapSemesterToInt($request->semester);
            $idKelas = $request->id_kelas;
            $idMapel = $request->id_mapel;
            $tahunAjaran = trim($request->tahun_ajaran);

            if (is_null($semesterDB)) {
                $error = 'Nilai semester tidak valid.';
                goto render_view;
            }

            // 1. Pengambilan Siswa (sama seperti sebelumnya, ini sudah efektif)
            $selectedMapel = MataPelajaran::find($idMapel);
            $querySiswa = Siswa::with('detail')->where('id_kelas', $idKelas);
            // ... (Filter Agama Khusus) ...
            $siswa = $querySiswa->orderBy('nama_siswa')->get();

            if ($siswa->isEmpty()) {
                goto render_view;
            }

            // --- QUERY UTAMA SUMATIF DAN PROJECT ---
            $baseQuery = [
                'id_kelas' => $idKelas,
                'id_mapel' => $idMapel,
                'semester' => $semesterDB, // Filter Integer
                'tahun_ajaran' => $tahunAjaran,
            ];
            
            // 2. Ambil Semua Data Sumatif yang Disesuaikan dengan Filter
            // Kita hanya mengambil kolom id_siswa, nilai, dan penanda sumatif
            $allSumatif = Sumatif::select(['id_siswa', 'nilai', 'sumatif']) // 🛑 ASUMSI: Kolom penanda adalah 'sumatif' 🛑
                ->where($baseQuery)
                ->get()
                ->groupBy('id_siswa'); 
            
            // 3. Ambil Semua Data Project
            $allProject = Project::where($baseQuery)->get()->keyBy('id_siswa');


            foreach ($siswa as $s) {
                $idSiswa = $s->id_siswa;
                
                // --- 1. PROSES SUMATIF DARI KOLEKSI ---
                $sumatifCollection = $allSumatif->get($idSiswa) ?? collect();
                
                // Mengambil nilai S1, S2, S3 secara langsung
                // Jika kolom Anda bernama 'sumsumatif', ganti 'sumatif' di bawah
                $s1 = optional($sumatifCollection->firstWhere('sumatif', 1))->nilai;
                $s2 = optional($sumatifCollection->firstWhere('sumatif', 2))->nilai;
                $s3 = optional($sumatifCollection->firstWhere('sumatif', 3))->nilai;

                // 🛑 Konversi ke numeric (int) untuk keamanan (seperti yang kita pelajari)
                $s1 = ($s1 !== null && $s1 !== '') ? (int)$s1 : null;
                $s2 = ($s2 !== null && $s2 !== '') ? (int)$s2 : null;
                $s3 = ($s3 !== null && $s3 !== '') ? (int)$s3 : null;

                $nilaiSumatif = collect([$s1, $s2, $s3])
                    ->filter(fn ($v) => $v !== null && $v > 0);

                $rataSumatif = $nilaiSumatif->count() >= 2 ? round($nilaiSumatif->avg(), 2) : null;
                $bobotSumatif = $rataSumatif !== null ? round($rataSumatif * 0.4, 2) : null;

                // ... (Logika Project, Nilai Akhir, dan Capaian Akhir) ...
                
                $projectItem = $allProject->get($idSiswa);
                $nilaiMentahProject = optional($projectItem)->nilai; 
                $rataProject = $nilaiMentahProject; 
                $bobotProject = optional($projectItem)->nilai_bobot;
                if ($bobotProject === null && $rataProject !== null) {
                    $bobotProject = round($rataProject * 0.6, 2); 
                }
                $nilaiAkhir = round(($bobotSumatif ?? 0) + ($bobotProject ?? 0), 2);
                // ... (Logika Capaian Akhir menggunakan $tpSumatif dan $tpProject) ...
                $capaianAkhir = '...'; // (Diisi dari logika kualifikasi Anda)

                // 4. Update/Create Nilai Akhir (Sudah dioptimalkan)
                NilaiAkhir::updateOrCreate(
                    [
                        'id_kelas' => $idKelas,
                        'id_mapel' => $idMapel,
                        'id_siswa' => $idSiswa,
                        'tahun_ajaran' => $tahunAjaran,
                        'semester' => $semesterDB,
                    ],
                    [
                        'nilai_s1' => $s1 ?? 0, 'nilai_s2' => $s2 ?? 0, 'nilai_s3' => $s3 ?? 0, 
                        'rata_sumatif' => $rataSumatif ?? 0.00,
                        'bobot_sumatif' => $bobotSumatif ?? 0.00,
                        'nilai_project' => $nilaiMentahProject ?? 0.00, 
                        'rata_project' => $rataProject ?? 0.00, 
                        'bobot_project' => $bobotProject ?? 0.00,
                        'nilai_akhir' => $nilaiAkhir ?? 0.00,
                        'capaian_akhir' => $capaianAkhir,
                    ]
                );

                // 5. Isi Rekap untuk View
                $rekap[$s->id_siswa] = [
                    's1' => $s1 ?? '-', 's2' => $s2 ?? '-', 's3' => $s3 ?? '-',
                    'rata_sumatif' => $rataSumatif,
                    'bobot_sumatif' => $bobotSumatif,
                    'nilai_project' => $nilaiMentahProject ?? '-',
                    'rata_project' => $rataProject, 
                    'bobot_project' => $bobotProject,
                    'nilai_akhir' => $nilaiAkhir,
                    'capaian_akhir' => $capaianAkhir,
                ];
            }
        }

        render_view:
        return view('nilai.nilaiakhir', compact(
            'kelas',
            'mapel',
            'siswa',
            'rekap'
        ));
    }

    private function kualifikasi($nilai)
    {
        if ($nilai < 78) return 'belum berkembang';
        if ($nilai <= 85) return 'layak';
        if ($nilai <= 92) return 'cakap';
        return 'mahir';
    }

}
