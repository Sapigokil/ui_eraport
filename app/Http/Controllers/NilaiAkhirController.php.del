<?php
// File: app/Http/Controllers/NilaiAkhirController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiAkhir;
use App\Models\Pembelajaran;
use App\Models\Sumatif; // Pastikan Model ini diimport
use App\Models\Project; // Pastikan Model ini diimport


class NilaiAkhirController extends Controller
{
    // Helper untuk mapping semester string ke integer (Konsisten dengan ProjectController)
    private function mapSemesterToInt(string $semester): ?int
    {
        $map = [
            'GANJIL' => 1,
            'GENAP' => 2,
        ];
        return $map[strtoupper($semester)] ?? null;
    }

    private function kualifikasi($nilai)
    {
        if ($nilai < 78) return 'belum berkembang';
        if ($nilai <= 85) return 'layak';
        if ($nilai <= 92) return 'cakap';
        return 'mahir';
    }

    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $mapel = collect();
        $siswa = collect();
        $rekap = [];
        $error = null;

        if ($request->id_kelas) {
            $mapel = Pembelajaran::with('mapel')
                ->where('id_kelas', $request->id_kelas)
                ->get()
                ->map(fn($p) => $p->mapel)
                ->filter()
                ->values();
        }

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


            $selectedMapel = MataPelajaran::find($idMapel);
            $querySiswa = Siswa::with('detail')->where('id_kelas', $idKelas);

            if ($selectedMapel && $selectedMapel->agama_khusus) {
                $querySiswa->whereHas('detail', fn ($q) => $q->where('agama', $selectedMapel->agama_khusus));
            }
            $siswa = $querySiswa->orderBy('nama_siswa')->get();

            if ($siswa->isEmpty()) {
                goto render_view;
            }

            // --- Query Dasar untuk Nilai ---
            $baseQuery = [
                'id_kelas' => $idKelas,
                'id_mapel' => $idMapel,
                'semester' => $semesterDB, // Menggunakan Integer
                'tahun_ajaran' => $tahunAjaran,
            ];

            // Mengambil data Sumatif dan Project
            $allSumatif = Sumatif::where($baseQuery)->get()->groupBy('id_siswa');
            $allProject = Project::where($baseQuery)->get()->keyBy('id_siswa');


            foreach ($siswa as $s) {
                $idSiswa = $s->id_siswa;

                // --- 1. PROSES SUMATIF ---
                $sumatifCollection = $allSumatif->get($idSiswa) ?? collect();
                
                // PENTING: Mengambil nilai dari kolom 'sumatif' di Model Sumatif
                $s1 = optional($sumatifCollection->firstWhere('sumatif', 1))->nilai;
                $s2 = optional($sumatifCollection->firstWhere('sumatif', 2))->nilai;
                $s3 = optional($sumatifCollection->firstWhere('sumatif', 3))->nilai;

                $tpSumatif = $sumatifCollection
                    ->filter(fn ($i) => $i->nilai !== null)
                    ->map(fn ($item) => [
                        'nilai' => (float) $item->nilai,
                        'tp'    => $item->tujuan_pembelajaran,
                        'label' => 'Sumatif ' . $item->sumatif,
                    ]);

                $nilaiSumatif = collect([$s1, $s2, $s3])
                    ->filter(fn ($v) => $v !== null && $v > 0);

                $rataSumatif = $nilaiSumatif->count() >= 2
                    ? round($nilaiSumatif->avg(), 2)
                    : null;

                $bobotSumatif = $rataSumatif !== null
                    ? round($rataSumatif * 0.4, 2)
                    : null;

                // --- 2. PROSES PROJECT ---
                $projectItem = $allProject->get($idSiswa);
                
                $nilaiMentahProject = optional($projectItem)->nilai; 
                $rataProject = $nilaiMentahProject; 

                $tpProject = $projectItem
                    ? collect([[
                        'nilai' => (float) $projectItem->nilai,
                        'tp'    => $projectItem->tujuan_pembelajaran,
                        'label' => 'Project',
                    ]])
                    : collect();

                $bobotProject = optional($projectItem)->nilai_bobot;
                
                if ($bobotProject === null && $rataProject !== null) {
                    $bobotProject = round($rataProject * 0.6, 2); 
                }


                // --- 3. HITUNG AKHIR ---
                $nilaiAkhir = round(($bobotSumatif ?? 0) + ($bobotProject ?? 0), 2);


                // --- 4. CAPAIAN AKHIR ---
                $semuaNilai = $tpSumatif
                    ->merge($tpProject)
                    ->filter(fn ($n) => $n['nilai'] !== null);
                
                $capaianAkhir = null; 
                if ($semuaNilai->count() > 0) {
                    $nama = $s->nama_siswa;
                    if ($semuaNilai->count() === 1) {
                        $data = $semuaNilai->first();
                        $kualifikasi = $this->kualifikasi($data['nilai']);
                        $capaianAkhir = "Ananda {$nama} {$kualifikasi} pada {$data['label']} (tujuan pembelajaran: {$data['tp']})";
                    } else {
                        $terendah  = $semuaNilai->sortBy('nilai')->first();
                        $tertinggi = $semuaNilai->sortByDesc('nilai')->first();
                        $rendah = $this->kualifikasi($terendah['nilai']);
                        $tinggi = $this->kualifikasi($tertinggi['nilai']);
                        $capaianAkhir = "Ananda {$nama} {$rendah} pada {$terendah['label']} ({$terendah['tp']}), namun {$tinggi} pada {$tertinggi['label']} ({$tertinggi['tp']})"; 
                    }
                }

                // --- 5. SIMPAN KE NILAI AKHIR MODEL ---
                NilaiAkhir::updateOrCreate(
                    [
                        'id_kelas' => $idKelas,
                        'id_mapel' => $idMapel,
                        'id_siswa' => $idSiswa,
                        'tahun_ajaran' => $tahunAjaran,
                        'semester' => $semesterDB,
                    ],
                    [
                        // Jika S1/S2/S3 null, simpan sebagai 0
                        'nilai_s1' => $s1 ?? 0, 
                        'nilai_s2' => $s2 ?? 0, 
                        'nilai_s3' => $s3 ?? 0, 
                        'rata_sumatif' => $rataSumatif ?? 0,
                        'bobot_sumatif' => $bobotSumatif ?? 0,
                        'nilai_project' => $nilaiMentahProject ?? 0, 
                        'rata_project' => $rataProject ?? 0, 
                        'bobot_project' => $bobotProject ?? 0,
                        'nilai_akhir' => $nilaiAkhir ?? 0,
                        'capaian_akhir' => $capaianAkhir,
                    ]
                );

                // --- 6. DATA UNTUK VIEW ---
                $rekap[$s->id_siswa] = [
                    's1' => $s1 ?? '-', // Tampilkan '-' jika null di view
                    's2' => $s2 ?? '-',
                    's3' => $s3 ?? '-',
                    'rata_sumatif' => $rataSumatif,
                    'bobot_sumatif' => $bobotSumatif,
                    'nilai_project' => $nilaiMentahProject ?? '-', // Nilai Mentah Project
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
            'rekap',
            'error' 
        ));
    }
}