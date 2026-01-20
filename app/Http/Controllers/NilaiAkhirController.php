<?php
// File: app/Http/Controllers/NilaiAkhirController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiAkhir;
use App\Models\Pembelajaran;
use App\Models\Sumatif; 
use App\Models\Project; 
use App\Models\BobotNilai;
use App\Http\Controllers\RaporController;
use App\Services\NilaiAkhirService;
use App\Services\CapaianAkhirService;



class NilaiAkhirController extends Controller
{
    private function mapSemesterToInt(string $semester): ?int
    {
        $map = [
            'GANJIL' => 1,
            'GENAP' => 2,
        ];
        return $map[strtoupper(trim($semester))] ?? null;
    }

    public function generateCapaianAkhir($siswa, $semuaNilai): ?string
    {
        if ($semuaNilai->count() === 0) {
            return "Data nilai intrakurikuler dan project belum tersedia.";
        }

        // ===============================
        // 🔥 BATASI MAKSIMAL 2 TP SAJA
        // ===============================
        if ($semuaNilai->count() > 2) {
            $terendahTmp  = $semuaNilai->sortBy('nilai')->first();
            $tertinggiTmp = $semuaNilai->sortByDesc('nilai')->first();

            // Kalau TP terendah & tertinggi sama (edge case)
            if ($terendahTmp['tp'] === $tertinggiTmp['tp']) {
                $semuaNilai = collect([$terendahTmp]);
            } else {
                $semuaNilai = collect([$terendahTmp, $tertinggiTmp]);
            }
        }

        $nilaiValid = $semuaNilai
            ->filter(fn ($n) => !empty(trim((string) $n['tp'])));

        if ($nilaiValid->isEmpty()) {
            return null;
        }

        // === BARU LANJUT KE LOGIKA LAMA ===
        $terendah  = $nilaiValid->sortBy('nilai')->first();
        $tertinggi = $nilaiValid->sortByDesc('nilai')->first();

        // Kasus 1: Nilai Tunggal atau Sama Semua
        if ($nilaiValid->count() === 1 || $terendah['nilai'] === $tertinggi['nilai']) {
            $nilaiKomparasi = $terendah['nilai']; 
            
            if ($nilaiKomparasi > 84) {
                // 🛑 DIHAPUS: Tag **
                $narasi = "Menunjukkan penguasaan yang baik dalam hal";
            } else {
                // 🛑 DIHAPUS: Tag **
                $narasi = "Perlu penguatan dalam hal";
            }
            
            $allTujuan = $nilaiValid
            ->pluck('tp')
            ->filter(fn ($tp) => !empty(trim((string) $tp)))
            ->unique()
            ->implode(', ');
            

        // 🔥 GUARD WAJIB DI SINI
        if ($allTujuan === '') {
            return null;
        }

        return $narasi . " " . $allTujuan . ".";


        } 
        
        // Kasus 2: Nilai Bervariasi (Komparasi Terendah vs Tertinggi)
        
        // A. Kualifikasi Nilai Terendah (Area Peningkatan)
        $nilaiRendah = $terendah['nilai'];
        $tpRendah = trim((string) ($terendah['tp'] ?? ''));

        // if ($tpRendah === '' || strtolower($tpRendah) === 'belum ditentukan') {
        //     $tpRendah = 'tujuan pembelajaran yang perlu ditingkatkan';
        // }


        if ($nilaiRendah < 81) {
            $kunciRendah = "Perlu peningkatan dalam hal";
        } else {
            $kunciRendah = "Perlu penguatan dalam hal";
        }
        
        // B. Kualifikasi Nilai Tertinggi (Area Penguasaan)
        $nilaiTinggi = $tertinggi['nilai'];
        $tpTinggi = trim((string) ($tertinggi['tp'] ?? ''));

        // if ($tpTinggi === '' || strtolower($tpTinggi) === 'belum ditentukan') {
        //     $tpTinggi = 'tujuan pembelajaran yang telah dikuasai';
        // }


        if ($nilaiTinggi > 89) {
            $kunciTinggi = "Mahir dalam hal";
        } else {
            $kunciTinggi = "Baik dalam hal";
        }

        // C. Bentuk Narasi Komparasi
        // 🛑 DIHAPUS: Tag **
        $narasi = "{$kunciRendah} {$tpRendah}, namun menunjukkan capaian {$kunciTinggi} {$tpTinggi}.";
        
        return $narasi;
    }


    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $mapel = collect();
        $siswa = collect();
        $rekap = [];
        $error = null;
        
        if ($request->id_kelas) {
            // $mapel = Pembelajaran::with('mapel')
            //     ->where('id_kelas', $request->id_kelas)
            //     ->get()
            //     ->map(fn($p) => $p->mapel)
            //     ->filter()
            //     ->values();
            $mapel = Pembelajaran::with(['mapel' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->where('id_kelas', $request->id_kelas)
            ->get()
            ->pluck('mapel')
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

            // ... (Pengambilan Siswa) ...
            // $selectedMapel = MataPelajaran::find($idMapel);
            $selectedMapel = MataPelajaran::where('id_mapel', $idMapel)
                ->where('is_active', 1)
                ->first();

            if (!$selectedMapel) {
                $error = 'Mata pelajaran sudah tidak aktif.';
                goto render_view;
            }

            $querySiswa = Siswa::with('detail')->where('id_kelas', $idKelas);
            if ($selectedMapel && $selectedMapel->agama_khusus) {
                $querySiswa->whereHas('detail', fn ($q) => $q->where('agama', $selectedMapel->agama_khusus));
            }
            $siswa = $querySiswa->orderBy('nama_siswa')->get();

            if ($siswa->isEmpty()) {
                goto render_view;
            }

            // --- QUERY UTAMA SUMATIF DAN PROJECT ---
            $baseQuery = [
                'id_kelas' => $idKelas,
                'id_mapel' => $idMapel,
                'semester' => $semesterDB, 
                'tahun_ajaran' => $tahunAjaran,
            ];
            
            $allSumatif = Sumatif::select(['id_siswa', 'nilai', 'sumatif', 'tujuan_pembelajaran']) 
                ->where($baseQuery)
                ->get()
                ->groupBy('id_siswa'); 
            
            $allProject = Project::where($baseQuery)->get()->keyBy('id_siswa');

            //hitung bobot dari pengaturan bobot 
            $bobotSetting = BobotNilai::where('tahun_ajaran', $tahunAjaran)
                ->where('semester', strtoupper($request->semester))
                ->first();

            if (!$bobotSetting) {
                $error = 'Bobot nilai belum diatur untuk semester dan tahun ajaran ini.';
                goto render_view;
            }

            $bobotSumatifPersen = $bobotSetting->bobot_sumatif; // contoh: 60
            $bobotProjectPersen = $bobotSetting->bobot_project; // contoh: 40


            foreach ($siswa as $s) {
                $idSiswa = $s->id_siswa;

                // --- 1. PROSES SUMATIF & CASTING ---
                $sumatifCollection = $allSumatif->get($idSiswa) ?? collect();
                
                $s1_raw = optional($sumatifCollection->firstWhere('sumatif', 1))->nilai;
                $s2_raw = optional($sumatifCollection->firstWhere('sumatif', 2))->nilai;
                $s3_raw = optional($sumatifCollection->firstWhere('sumatif', 3))->nilai;
                $s4_raw = optional($sumatifCollection->firstWhere('sumatif', 4))->nilai;
                $s5_raw = optional($sumatifCollection->firstWhere('sumatif', 5))->nilai;
                
                $s1 = ($s1_raw !== null && $s1_raw !== '') ? (int)$s1_raw : null;
                $s2 = ($s2_raw !== null && $s2_raw !== '') ? (int)$s2_raw : null;
                $s3 = ($s3_raw !== null && $s3_raw !== '') ? (int)$s3_raw : null;
                $s4 = ($s4_raw !== null && $s4_raw !== '') ? (int)$s4_raw : null;
                $s5 = ($s5_raw !== null && $s5_raw !== '') ? (int)$s5_raw : null;

                // Kumpulkan TP Sumatif yang memiliki nilai
                $tpSumatif = $sumatifCollection
                    ->filter(fn ($i) => $i->nilai !== null)
                    ->map(fn ($item) => [
                        'nilai' => (float) $item->nilai,
                        'tp'    => $item->tujuan_pembelajaran, 
                        'label' => 'Sumatif ' . $item->sumatif, 
                    ]);

                $nilaiSumatif = collect([$s1, $s2, $s3, $s4, $s5])
                    ->filter(fn ($v) => $v !== null);
                $rataSumatif = $nilaiSumatif->count() >= 2
                ? round($nilaiSumatif->sum() / $nilaiSumatif->count(), 2)
                : null;
                // $bobotSumatif = $rataSumatif !== null ? round($rataSumatif * 0.4, 2) : null;
                $bobotSumatif = $rataSumatif !== null
                    ? round($rataSumatif * ($bobotSumatifPersen / 100), 2)
                    : null;


                // --- 2. PROSES PROJECT ---
                $projectItem = $allProject->get($idSiswa);
                $nilaiMentahProject = optional($projectItem)->nilai; 
                $rataProject = $nilaiMentahProject; 

                // Kumpulkan TP Project
                $tpProject = $projectItem
                    ? collect([[
                        'nilai' => (float) $projectItem->nilai,
                        'tp'    => $projectItem->tujuan_pembelajaran, 
                        'label' => 'Project',
                    ]])
                    : collect();

                $bobotProject = $rataProject !== null
                    ? round($rataProject * ($bobotProjectPersen / 100), 2)
                    : null;
                if ($bobotProject === null && $rataProject !== null) {
                    // $bobotProject = round($rataProject * 0.6, 2); // 0 tetap masuk
                    $bobotProject = $rataProject !== null
                        ? round($rataProject * ($bobotProjectPersen / 100), 2)
                        : null;
                }


                $jumlahSumatifTerisi = $nilaiSumatif->count();
                $projectTerisi = $rataProject !== null;

                $aturanTerpenuhi = $jumlahSumatifTerisi >= 3 && $projectTerisi;
                // --- 3. HITUNG AKHIR ---
                if ($aturanTerpenuhi) {
                    $nilaiAkhir = round(($bobotSumatif ?? 0) + ($bobotProject ?? 0), 2);
                } else {
                    $nilaiAkhir = 0;
                    $bobotSumatif = 0;
                    $bobotProject = 0;
                    $capaianAkhir = null;
                }
                // $nilaiAkhir = round(($bobotSumatif ?? 0) + ($bobotProject ?? 0), 2); ---> rumus sebelumnya


                // --- 4. GENERATE DAN SIMPAN CAPAIAN AKHIR ---
                $semuaNilai = $tpSumatif
                    ->merge($tpProject)
                    ->filter(fn ($n) => $n['nilai'] !== null); 
                
                $capaianAkhir = $this->generateCapaianAkhir($s, $semuaNilai);

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
                        'nilai_s1' => $s1 ?? 0, 'nilai_s2' => $s2 ?? 0, 'nilai_s3' => $s3 ?? 0, 'nilai_s4' => $s4 ?? 0, 'nilai_s5' => $s5 ?? 0,
                        'rata_sumatif' => $rataSumatif ?? 0.00,
                        'bobot_sumatif' => $bobotSumatif ?? 0.00,
                        'nilai_project' => $nilaiMentahProject ?? 0.00, 
                        'rata_project' => $rataProject ?? 0.00, 
                        'bobot_project' => $bobotProject ?? 0.00,
                        'nilai_akhir' => $nilaiAkhir ?? 0.00,
                        'capaian_akhir' => $capaianAkhir,
                    ]
                );

                // --- 6. DATA UNTUK VIEW ---
                $rekap[$s->id_siswa] = [
                    's1' => ($s1 !== null) ? $s1 : '-', 
                    's2' => ($s2 !== null) ? $s2 : '-',
                    's3' => ($s3 !== null) ? $s3 : '-',
                    's4' => ($s4 !== null) ? $s4 : '-',
                    's5' => ($s5 !== null) ? $s5 : '-',
                    'rata_sumatif' => $rataSumatif,
                    'bobot_sumatif' => $bobotSumatif,
                    'nilai_project' => $nilaiMentahProject ?? '-',
                    'rata_project' => $rataProject, 
                    'bobot_project' => $bobotProject,
                    'nilai_akhir' => $nilaiAkhir,
                    'capaian_akhir' => $capaianAkhir,
                ];

                // // Panggil mesin penghitung status rapor
                // $raporCtrl = app(RaporController::class);
                // foreach ($siswa as $s) {
                //     $raporCtrl->perbaruiStatusRapor(
                //         $s->id_siswa, 
                //         $request->semester, 
                //         $request->tahun_ajaran
                //     );
                // }
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