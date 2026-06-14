<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Pembelajaran;
use App\Models\BobotNilai;
use App\Models\Season;
use App\Models\NilaiAkhir;
use App\Models\NilaiAkhirRapor;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Helpers\NilaiCalculator;

class MonitoringWaliController extends Controller
{
    private function getAgamaAliases(?string $agama_khusus): array
    {
        if (!$agama_khusus) return [];

        $agama = trim(strtolower($agama_khusus));

        $aliases = [
            'katholik' => ['katholik', 'katolik'],
            'katolik'  => ['katholik', 'katolik'],
            'kristen'  => ['kristen', 'protestan', 'kristen protestan'],
            'konghucu' => ['konghucu', 'kong hucu', 'khonghucu'],
        ];

        return $aliases[$agama] ?? [$agama];
    }

    public function index(Request $request)
    {
        $periode = $this->getPeriode($request);
        
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']);

        $queryKelas = Kelas::orderBy('nama_kelas', 'asc');
        
        if ($isGuru) {
            $queryKelas->where('id_guru', $user->id_guru);
        }
        
        $kelasList = $queryKelas->get();

        if ($request->has('id_kelas') && $request->id_kelas != '') {
            if ($isGuru) {
                $cekAksesKelas = $kelasList->where('id_kelas', $request->id_kelas)->first();
                if (!$cekAksesKelas) {
                    return redirect()->route('walikelas.monitoring.wali')->with('error', 'Akses Ditolak! Anda bukan Wali Kelas untuk kelas tersebut.');
                }
            }

            $kelasTarget = Kelas::find($request->id_kelas);
        } else {
            $kelasTarget = $kelasList->first();
        }

        if (!$kelasTarget) {
             return view('monitoring.kesiapan_rapor.index_wali', array_merge(
                [
                    'dataKelas' => null,
                    'kelasList' => $kelasList, 
                    'selected_kelas_id' => null,
                    'gate' => null,
                    'bobotInfo' => null,
                    'isGuru' => $isGuru
                ], 
                $periode,
                ['stats' => null]
            ))->with('error', 'Data Kelas Kosong atau Anda tidak terdaftar sebagai Wali Kelas.');
        }

        $semesterRaw = $periode['semester'];
        $tahun_ajaran = $periode['tahun_ajaran'];
        $semesterInt = (strtoupper($semesterRaw) == 'GENAP' || $semesterRaw == '2') ? 2 : 1;
        
        $bobotInfo = BobotNilai::where('tahun_ajaran', $tahun_ajaran)
            ->where(function($query) use ($semesterRaw, $semesterInt) {
                $query->where('semester', strtoupper($semesterRaw))
                      ->orWhere('semester', $semesterInt)
                      ->orWhere('semester', ucfirst($semesterRaw));
            })->first();

        $result = $this->hitungMonitoringData(collect([$kelasTarget]), $periode, $bobotInfo);
        $singleData = $result['monitoringData'][0] ?? null;

        $gate = $this->checkPrerequisites($periode, $singleData);

        return view('monitoring.kesiapan_rapor.index_wali', array_merge(
            [
                'dataKelas' => $singleData,
                'kelasList' => $kelasList, 
                'selected_kelas_id' => $kelasTarget->id_kelas,
                'gate' => $gate,
                'bobotInfo' => $bobotInfo, 
                'isGuru' => $isGuru 
            ], 
            $periode,
            ['stats' => $result['stats']]
        ));
    }

    public function generateRaporWalikelas(Request $request)
    {
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']);

        $request->validate([
            'id_kelas' => 'required',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);

        $id_kelas = $request->id_kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterRaw = $request->semester;
        $semesterInt = (strtoupper($semesterRaw) == 'GENAP' || $semesterRaw == '2') ? 2 : 1;

        if ($isGuru) {
            $cekHakAkses = Kelas::where('id_kelas', $id_kelas)->where('id_guru', $user->id_guru)->exists();
            if (!$cekHakAkses) {
                return back()->with('error', 'Akses Ditolak: Anda tidak memiliki wewenang memfinalisasi kelas ini.');
            }
        }

        $isLocked = DB::table('nilai_akhir_rapor')
            ->where('id_kelas', $id_kelas)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('status_data', 'cetak') 
            ->exists();

        if ($isLocked) {
            return back()->with('error', 'Akses Ditolak: Rapor kelas ini sudah masuk tahap CETAK dan terkunci. Tidak bisa di-generate ulang.');
        }

        $bobot = BobotNilai::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', strtoupper($semesterRaw))
            ->first();
        
        if (!$bobot) return back()->with('error', "Gagal: Bobot Nilai belum disetting Admin.");

        $kelas = Kelas::find($id_kelas);
        
        $guruWali = DB::table('guru')->where('id_guru', $kelas->id_guru)->first();
        if (!$guruWali) $guruWali = DB::table('guru')->where('nama_guru', $kelas->wali_kelas)->first();
        $namaWaliSnapshot = $guruWali->nama_guru ?? $kelas->wali_kelas ?? '-';
        $nipWaliSnapshot  = $guruWali->nip ?? '-';

        $sekolah = DB::table('info_sekolah')->first();
        $kepsekName = $sekolah->nama_kepsek ?? '-';
        $kepsekNip  = $sekolah->nip_kepsek ?? '-';

        $namaKelasSnapshot = $kelas->nama_kelas;
        $tingkatSnapshot = (int) preg_replace('/[^0-9]/', '', $kelas->tingkat ?? '10'); 
        $faseSnapshot = ($tingkatSnapshot >= 11) ? 'F' : 'E';

        $siswaList = Siswa::leftJoin('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa')
            ->where('siswa.id_kelas', $id_kelas)
            ->select(
                'siswa.id_siswa', 
                'siswa.nama_siswa', 
                'siswa.nisn', 
                'siswa.nipd', 
                'detail_siswa.agama'
            )
            ->get();

        $listPembelajaran = Pembelajaran::with(['mapel' => function($q){ 
            $q->where('is_active', 1); 
        }, 'guru'])->where('id_kelas', $id_kelas)->get();

        if ($siswaList->isEmpty()) return back()->with('error', 'Tidak ada siswa di kelas ini.');

        DB::beginTransaction();
        try {
            $countSiswa = 0;

            foreach ($siswaList as $siswa) {
                $agamaSiswa = strtolower(trim($siswa->agama ?? ''));

                foreach ($listPembelajaran as $pemb) {
                    if (!$pemb->mapel) continue;

                    $syaratAgama = $pemb->mapel->agama_khusus; 
                    if (!empty($syaratAgama)) {
                        $agamaList = $this->getAgamaAliases($syaratAgama);
                        if (!in_array($agamaSiswa, $agamaList)) continue; 
                    }

                    $existingNilai = DB::table('nilai_akhir')->where([
                        'id_siswa' => $siswa->id_siswa, 'id_mapel' => $pemb->id_mapel,
                        'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                    ])->first();

                    if ($existingNilai) {
                        DB::table('nilai_akhir')
                            ->where('id_siswa', $siswa->id_siswa)
                            ->where('id_mapel', $pemb->id_mapel)
                            ->where('semester', $semesterInt)
                            ->where('tahun_ajaran', $tahun_ajaran)
                            ->update([
                                'status_data' => 'final',
                                'updated_at'  => now()
                            ]);
                    } else {
                        $namaMapelSnapshot = $pemb->mapel->nama_mapel;
                        $kodeMapelSnapshot = $pemb->mapel->nama_singkat ?? '-';
                        $namaGuruSnapshot  = ($pemb->guru) ? $pemb->guru->nama_guru : 'Guru Belum Ditentukan';
                        
                        $kategoriLabel = 'Mata Pelajaran Umum';
                        if (isset($pemb->mapel->kategori)) {
                            $mapKategori = [1 => 'Mata Pelajaran Umum', 2 => 'Mata Pelajaran Kejuruan', 3 => 'Mata Pelajaran Pilihan', 4 => 'Muatan Lokal'];
                            $kategoriLabel = $mapKategori[$pemb->mapel->kategori] ?? $pemb->mapel->kategori;
                        }

                        $sumatifData = DB::table('sumatif')->where([
                            'id_siswa' => $siswa->id_siswa, 'id_mapel' => $pemb->id_mapel,
                            'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                        ])->get();

                        $projectRow = DB::table('project')->where([
                            'id_siswa' => $siswa->id_siswa, 'id_mapel' => $pemb->id_mapel,
                            'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                        ])->first();
                        $nilaiProject = $projectRow ? $projectRow->nilai : 0;

                        $hasil = \App\Helpers\NilaiCalculator::process($sumatifData, $nilaiProject, $bobot);
                        $deskripsi = $this->generateDeskripsiOtomatis($siswa->id_siswa, $pemb->id_mapel, $semesterInt, $tahun_ajaran);

                        DB::table('nilai_akhir')->insert(
                            array_merge($hasil['s_vals'], [
                                'id_siswa' => $siswa->id_siswa,
                                'id_mapel' => $pemb->id_mapel,
                                'semester' => $semesterInt,
                                'tahun_ajaran' => $tahun_ajaran,
                                'id_kelas' => $id_kelas,
                                'rata_sumatif'  => $hasil['rata_sumatif'],
                                'bobot_sumatif' => $hasil['bobot_sumatif'],
                                'nilai_project' => $hasil['nilai_project'],
                                'rata_project'  => $hasil['rata_project'],
                                'bobot_project' => $hasil['bobot_project'],
                                'nilai_akhir'   => $hasil['nilai_akhir'],
                                'capaian_akhir' => $deskripsi,
                                'nama_mapel_snapshot'     => $namaMapelSnapshot,
                                'kode_mapel_snapshot'     => $kodeMapelSnapshot,
                                'kategori_mapel_snapshot' => $kategoriLabel,
                                'nama_guru_snapshot'      => $namaGuruSnapshot,
                                'nama_kelas_snapshot'     => $namaKelasSnapshot,
                                'tingkat'                 => $tingkatSnapshot,
                                'fase'                    => $faseSnapshot,
                                'status_data' => 'final',
                                'updated_at'  => now(),
                                'created_at'  => now()
                            ])
                        );
                    }
                }

                $catatan = DB::table('catatan')->where([
                    'id_siswa' => $siswa->id_siswa, 
                    'semester' => $semesterInt, 
                    'tahun_ajaran' => $tahun_ajaran
                ])->first();

                $activeEkskuls = DB::table('ekskul_siswa')
                    ->join('ekskul', 'ekskul_siswa.id_ekskul', '=', 'ekskul.id_ekskul')
                    ->where('ekskul_siswa.id_siswa', $siswa->id_siswa)
                    ->select('ekskul.id_ekskul', 'ekskul.nama_ekskul')
                    ->get();

                $nilaiEkskuls = DB::table('nilai_ekskul')
                    ->where('id_siswa', $siswa->id_siswa)
                    ->where('semester', $semesterInt)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->get()
                    ->keyBy('id_ekskul');

                $ekskulSnapshot = [];
                
                foreach($activeEkskuls as $ae) {
                    $nilai = $nilaiEkskuls->get($ae->id_ekskul);
                    
                    $ekskulSnapshot[] = [
                        'nama'       => $ae->nama_ekskul, 
                        'predikat'   => $nilai->predikat ?? '-',
                        'keterangan' => $nilai->keterangan ?? '-'
                    ];
                }

                DB::table('nilai_akhir_rapor')->updateOrInsert(
                    [
                        'id_siswa' => $siswa->id_siswa, 
                        'semester' => $semesterInt, 
                        'tahun_ajaran' => $tahun_ajaran
                    ],
                    [
                        'id_kelas' => $id_kelas,
                        
                        'nama_siswa_snapshot' => $siswa->nama_siswa,
                        'nisn_snapshot'       => $siswa->nisn ?? '-',
                        'nipd_snapshot'       => $siswa->nipd ?? '-', 
                        'nama_kelas_snapshot' => $namaKelasSnapshot,
                        'tingkat'             => $tingkatSnapshot,
                        'fase'                => $faseSnapshot, 
                        'wali_kelas_snapshot' => $namaWaliSnapshot,
                        'nip_wali_snapshot'   => $nipWaliSnapshot,
                        'kepsek_snapshot'     => $kepsekName,
                        'nip_kepsek_snapshot' => $kepsekNip,

                        'sakit' => $catatan->sakit ?? 0, 
                        'ijin'  => $catatan->ijin ?? 0, 
                        'alpha' => $catatan->alpha ?? 0,
                        'kokurikuler'        => $catatan->kokurikuler ?? '-',
                        'catatan_wali_kelas' => $catatan->catatan_wali_kelas ?? '-',
                        'status_kenaikan'    => $catatan->status_kenaikan ?? 'proses',
                        
                        'data_ekskul'   => json_encode($ekskulSnapshot),

                        'tanggal_cetak' => now(),
                        'status_data'   => 'final',
                        'updated_at'    => now(),
                        'created_at'    => DB::raw('IFNULL(created_at, NOW())')
                    ]
                );
                
                $countSiswa++;
            }

            DB::commit();
            return back()->with('success', "Berhasil! Data rapor untuk $countSiswa siswa telah digenerate.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    private function checkPrerequisites($periode, $dataKelas)
    {
        $activeSeason = Season::where('is_active', 1)->first();
        
        if (!$activeSeason) {
            return ['allowed' => false, 'message' => 'Season belum diatur oleh Admin.', 'icon' => 'fas fa-ban', 'color' => 'danger'];
        }
        
        if ($activeSeason->is_open == 0) {
            return ['allowed' => false, 'message' => 'Akses Generate Rapor sedang DITUTUP oleh Admin.', 'icon' => 'fas fa-lock', 'color' => 'danger'];
        }
        
        $today = Carbon::today();
        if ($today->lt($activeSeason->start_date)) {
            return ['allowed' => false, 'message' => 'Periode Generate Rapor BELUM DIMULAI.', 'icon' => 'fas fa-clock', 'color' => 'danger'];
        }
        
        if ($today->gt($activeSeason->end_date)) {
            return ['allowed' => false, 'message' => 'Periode Generate Rapor TELAH BERAKHIR.', 'icon' => 'fas fa-history', 'color' => 'danger'];
        }

        $filterTahun = $periode['tahun_ajaran'];
        $filterSemStr = $periode['semester']; 
        $filterSemInt = ($filterSemStr == 'Ganjil' || $filterSemStr == '1') ? 1 : 2;

        if ($filterTahun != $activeSeason->tahun_ajaran || $filterSemInt != $activeSeason->semester) {
            return ['allowed' => false, 'message' => 'Generate Rapor hanya bisa dilakukan pada Tahun & Semester yang aktif.', 'icon' => 'fas fa-calendar-times', 'color' => 'danger'];
        }

        if (!$dataKelas) {
            return ['allowed' => false, 'message' => 'Data kelas tidak terbaca.', 'icon' => 'fas fa-exclamation-triangle', 'color' => 'danger'];
        }

        $hasCetak = collect($dataKelas->detail)->merge($dataKelas->detail_catatan)
            ->contains(function($item) {
                return $item['status'] == 'cetak';
            });

        if ($hasCetak) {
            return [
                'allowed' => false, 
                'message' => 'Akses Terkunci: Rapor kelas ini sudah masuk tahap CETAK.', 
                'icon' => 'fas fa-lock', 
                'color' => 'dark' 
            ];
        }

        $mapelBelumSiap = collect($dataKelas->detail)->filter(function($m){
            return in_array($m['status'], ['kosong', 'proses']);
        })->count();

        $catatanBelumSiap = collect($dataKelas->detail_catatan)->filter(function($c){
            return in_array($c['status'], ['kosong', 'proses']);
        })->count();

        if ($mapelBelumSiap > 0) {
            return ['allowed' => false, 'message' => "Terdapat $mapelBelumSiap Mapel yang nilainya belum lengkap dari Guru.", 'icon' => 'fas fa-exclamation-circle', 'color' => 'warning'];
        }

        if ($catatanBelumSiap > 0) {
            return ['allowed' => false, 'message' => "Terdapat $catatanBelumSiap Siswa yang belum memiliki Catatan/Absensi lengkap.", 'icon' => 'fas fa-user-edit', 'color' => 'warning'];
        }

        $needAction = collect($dataKelas->detail)->merge($dataKelas->detail_catatan)
            ->contains(fn($item) => in_array($item['status'], ['ready', 'update']));

        if ($needAction) {
            return [
                'allowed' => true, 
                'message' => 'Data Lengkap. Klik Generate untuk menyimpan/memperbarui Rapor.', 
                'icon' => 'fas fa-check-double', 
                'color' => 'primary'
            ];
        }

        return [
            'allowed' => false, 
            'message' => 'Semua data rapor sudah berstatus FINAL. Tidak ada perubahan yang perlu digenerate.', 
            'icon' => 'fas fa-check-circle', 
            'color' => 'success'
        ];
    }

    private function getPeriode($request)
    {
        $bulanSekarang = date('n');
        $tahunSekarang = date('Y');
        
        if ($bulanSekarang >= 7) {
            $semDefault = 'Ganjil';
            $taDefault  = $tahunSekarang . '/' . ($tahunSekarang + 1);
        } else {
            $semDefault = 'Genap';
            $taDefault  = ($tahunSekarang - 1) . '/' . $tahunSekarang;
        }

        $tahun_ajaran = $request->tahun_ajaran ?? $taDefault;
        $semester     = $request->semester ?? $semDefault;
        
        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 2; $t <= $tahunSekarang + 1; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }

        return compact('tahun_ajaran', 'semester', 'tahunAjaranList');
    }

    private function hitungMonitoringData($listKelas, $periode, $bobotInfo = null)
    {
        $tahun_ajaran = $periode['tahun_ajaran'];
        $semester = $periode['semester'];
        
        $smtInt = ($semester == 'Ganjil' || $semester == '1') ? 1 : 2;

        $monitoringData = [];
        $stats = [
            'total_rombel' => $listKelas->count(),
            'mapel_final'  => 0,
            'mapel_total'  => 0,
            'persen_global'=> 0
        ];

        $batasMinimal = $bobotInfo ? (int) $bobotInfo->jumlah_sumatif : 3;

        foreach ($listKelas as $k) {
            $siswaCollection = DB::table('siswa')
                ->leftJoin('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa')
                ->where('siswa.id_kelas', $k->id_kelas)
                ->select('siswa.id_siswa', 'siswa.nama_siswa', 'siswa.nisn', 'detail_siswa.agama')
                ->orderBy('siswa.nama_siswa')
                ->get();

            $totalSiswaKelas = $siswaCollection->count();
            if ($totalSiswaKelas == 0) continue; 

            $pembelajaran = Pembelajaran::with(['mapel' => function ($q) {
                    $q->where('is_active', 1);
                }, 'guru']) 
                ->where('id_kelas', $k->id_kelas)
                ->get();

            $mapelDiKelas = $pembelajaran->map(function ($item) {
                if (!$item->mapel) return null;
                $item->mapel->nama_guru_pengampu = $item->guru->nama_guru ?? 'Belum diset';
                return $item->mapel;
            })->filter()->sortBy([['kategori', 'asc'], ['urutan', 'asc']]);

            $detailMapel = [];
            $kelasMapelTotalHitung = 0;
            $kelasMapelSelesai = 0;

            foreach ($mapelDiKelas as $m) {
                $syaratAgama = $m->agama_khusus;
                $siswaTargetList = $siswaCollection;
                
                if (!empty($syaratAgama)) {
                    $agamaList = $this->getAgamaAliases($syaratAgama);
                    $siswaTargetList = $siswaCollection->filter(function($s) use ($agamaList) {
                        return in_array(strtolower(trim($s->agama ?? '')), $agamaList);
                    });
                }

                $targetSiswa = $siswaTargetList->count();
                if ($targetSiswa == 0) continue; 

                $validSiswaIds = $siswaTargetList->pluck('id_siswa')->toArray();

                $rawSumatif = DB::table('sumatif')
                    ->where('id_mapel', $m->id_mapel)
                    ->where('semester', $smtInt)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->whereIn('id_siswa', $validSiswaIds)
                    ->whereNotNull('nilai')
                    ->select('id_siswa', DB::raw('COUNT(sumatif) as jml_sumatif'), DB::raw('MAX(updated_at) as last_update'))
                    ->groupBy('id_siswa')
                    ->get();

                $siswaMemenuhiSyarat = 0;
                $totalInputPoint = 0;
                $targetPoint = $targetSiswa * $batasMinimal; 
                
                foreach ($rawSumatif as $rs) {
                    if ($rs->jml_sumatif >= $batasMinimal) {
                        $siswaMemenuhiSyarat++;
                        $totalInputPoint += $batasMinimal;
                    } else {
                        $totalInputPoint += $rs->jml_sumatif;
                    }
                }

                $lastSumatifUpdate = $rawSumatif->max('last_update');

                $rawProject = DB::table('project')
                    ->where('id_mapel', $m->id_mapel)
                    ->where('semester', $smtInt)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->whereIn('id_siswa', $validSiswaIds)
                    ->whereNotNull('nilai')
                    ->select(DB::raw('count(distinct id_siswa) as total'), DB::raw('max(updated_at) as last_update'))
                    ->first();

                $lastProjectUpdate = $rawProject->last_update;
                $lastRawUpdate = max($lastSumatifUpdate, $lastProjectUpdate);

                $finalData = DB::table('nilai_akhir')
                    ->where('id_mapel', $m->id_mapel)
                    ->where('semester', $smtInt)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->whereIn('id_siswa', $validSiswaIds)
                    ->select(DB::raw('count(*) as total'), DB::raw('max(updated_at) as last_update'), DB::raw('MAX(status_data) as db_status'))
                    ->first();

                $countFinal = $finalData->total;
                $lastFinalUpdate = $finalData->last_update;
                $dbStatus = $finalData->db_status; 

                $status = 'kosong';
                if ($totalInputPoint == 0) {
                    $status = 'kosong';
                } elseif ($siswaMemenuhiSyarat < $targetSiswa) {
                    $status = 'proses';
                } else {
                    if ($countFinal < $targetSiswa) {
                        $status = 'ready';
                    } else {
                        if (strtotime($lastRawUpdate) > strtotime($lastFinalUpdate)) {
                            $status = 'update';
                        } else {
                            if ($dbStatus == 'cetak') {
                                $status = 'cetak';
                            } elseif ($dbStatus == 'draft') {
                                $status = 'ready'; 
                            } else {
                                $status = 'final';
                            }
                        }
                    }
                }

                if (in_array($status, ['final', 'update', 'ready', 'cetak'])) {
                    $kelasMapelSelesai++;
                    if (in_array($status, ['final', 'cetak'])) $stats['mapel_final']++;
                }

                $persenProgress = ($targetPoint > 0) ? round(($totalInputPoint / $targetPoint) * 100) : 0;

                $detailMapel[] = [
                    'id_mapel' => $m->id_mapel,
                    'mapel'    => $m->nama_mapel,
                    'guru'     => $m->nama_guru_pengampu,
                    'progress' => $siswaMemenuhiSyarat,
                    'total'    => $targetSiswa,
                    'status'   => $status,
                    'persen'   => $persenProgress,
                    'kategori' => $m->kategori
                ];
                
                $kelasMapelTotalHitung++;
                $stats['mapel_total']++;
            }

            $catatanList = DB::table('catatan')
                ->whereIn('id_siswa', $siswaCollection->pluck('id_siswa'))
                ->where('semester', $smtInt)->where('tahun_ajaran', $tahun_ajaran)->get()->keyBy('id_siswa');

            $allEkskulValues = DB::table('nilai_ekskul')
                ->join('ekskul', 'nilai_ekskul.id_ekskul', '=', 'ekskul.id_ekskul')
                ->whereIn('nilai_ekskul.id_siswa', $siswaCollection->pluck('id_siswa'))
                ->where('nilai_ekskul.semester', $smtInt)->where('nilai_ekskul.tahun_ajaran', $tahun_ajaran)
                ->select('nilai_ekskul.id_siswa', 'ekskul.nama_ekskul', 'nilai_ekskul.predikat')
                ->get()->groupBy('id_siswa');

            $finalRaporList = DB::table('nilai_akhir_rapor')
                ->whereIn('id_siswa', $siswaCollection->pluck('id_siswa'))
                ->where('semester', $smtInt)->where('tahun_ajaran', $tahun_ajaran)
                ->get()->keyBy('id_siswa');

            $detailCatatan = [];
            $siswaAdaCatatan = 0; 

            foreach ($siswaCollection as $s) {
                $c = $catatanList->get($s->id_siswa);
                $f = $finalRaporList->get($s->id_siswa);
                $ekskulSiswa = $allEkskulValues->get($s->id_siswa);

                $isiCatatan = $c->catatan_wali_kelas ?? null;
                $rawExists = !empty($isiCatatan) && trim((string)$isiCatatan) !== '' && trim((string)$isiCatatan) !== '-';

                $ekskulFormatted = [];
                if ($ekskulSiswa && $ekskulSiswa->isNotEmpty()) {
                    foreach ($ekskulSiswa as $ex) {
                        $ekskulFormatted[] = "• <b>{$ex->nama_ekskul}</b> ({$ex->predikat})";
                    }
                } else {
                    $ekskulFormatted[] = "<span class='text-muted text-xs'>- Tidak ada ekskul -</span>";
                }

                $statusCatatan = 'kosong';
                if (!$rawExists) {
                    $statusCatatan = 'kosong';
                } elseif (!$f) {
                    $statusCatatan = 'ready'; 
                } else {
                    $isDifferent = (
                        (int)($c->sakit ?? 0) !== (int)($f->sakit ?? 0) ||
                        (int)($c->ijin ?? 0)  !== (int)($f->ijin ?? 0)  || 
                        (int)($c->alpha ?? 0) !== (int)($f->alpha ?? 0) ||
                        trim((string)($c->catatan_wali_kelas ?? '-')) !== trim((string)($f->catatan_wali_kelas ?? '-')) ||
                        trim((string)($c->kokurikuler ?? '-'))    !== trim((string)($f->kokurikuler ?? '-')) ||
                        trim((string)($c->status_kenaikan ?? 'proses')) !== trim((string)($f->status_kenaikan ?? 'proses'))
                    );
                    
                    if ($isDifferent) {
                        $statusCatatan = 'update'; 
                    } else {
                        if ($f->status_data == 'cetak') {
                            $statusCatatan = 'cetak';
                        } elseif ($f->status_data == 'draft') {
                            $statusCatatan = 'ready'; 
                        } else {
                            $statusCatatan = 'final';
                        }
                    }
                }

                $detailCatatan[] = [
                    'nama_siswa' => $s->nama_siswa,
                    'nisn'       => $s->nisn,
                    'kokurikuler_short'=> \Illuminate\Support\Str::limit($c->kokurikuler ?? '-', 30), 
                    'kokurikuler_full' => $c->kokurikuler ?? '-',
                    'ekskul_html'=> implode('<br>', $ekskulFormatted), 
                    'sakit'      => $c->sakit ?? 0, 
                    'ijin'       => $c->ijin ?? 0, 
                    'alpha'      => $c->alpha ?? 0, 
                    'catatan_short' => \Illuminate\Support\Str::limit($c->catatan_wali_kelas ?? '-', 30), 
                    'catatan_full'  => $c->catatan_wali_kelas ?? '-',
                    'status_kenaikan' => $c->status_kenaikan ?? null,
                    'status'     => $statusCatatan
                ];
                
                if ($rawExists) $siswaAdaCatatan++;
            }

            $persenKelas = ($kelasMapelTotalHitung > 0) ? round(($kelasMapelSelesai / $kelasMapelTotalHitung) * 100) : 0;
            $persenCatatan = ($totalSiswaKelas > 0) ? round(($siswaAdaCatatan / $totalSiswaKelas) * 100) : 0;

            $monitoringData[] = (object) [
                'kelas'         => $k,
                'wali_kelas'    => $k->wali_kelas ?? '-',
                'jml_mapel'     => $kelasMapelTotalHitung,
                'mapel_selesai' => $kelasMapelSelesai,
                'persen'        => $persenKelas,
                'persen_catatan'=> $persenCatatan,
                'detail'        => $detailMapel, 
                'detail_catatan'=> $detailCatatan 
            ];
        }

        if ($stats['mapel_total'] > 0) {
            $persen = round(($stats['mapel_final'] / $stats['mapel_total']) * 100);
            if ($persen >= 100 && $stats['mapel_final'] < $stats['mapel_total']) $persen = 99;
            $stats['persen_global'] = (int) $persen;
        }

        return compact('monitoringData', 'stats');
    }

    private function generateDeskripsiOtomatis($id_siswa, $id_mapel, $semester, $tahun_ajaran)
    {
        $sumatif = DB::table('sumatif')
            ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $semester, 'tahun_ajaran' => $tahun_ajaran])
            ->whereNotNull('nilai')
            ->get()->map(function($item) { return ['nilai' => (float) $item->nilai, 'tp' => $item->tujuan_pembelajaran]; });

        $project = DB::table('project')
            ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $semester, 'tahun_ajaran' => $tahun_ajaran])
            ->get()->map(function($item) { return ['nilai' => (float) $item->nilai, 'tp' => $item->tujuan_pembelajaran]; });

        $semuaNilai = $sumatif->merge($project)->filter(function($item) { return !empty(trim((string)$item['tp'])); });

        if ($semuaNilai->isEmpty()) return "-";

        $tertinggi = $semuaNilai->sortByDesc('nilai')->first();
        if ($tertinggi['nilai'] <= 0) return "-";

        $terendah = $semuaNilai->sortBy('nilai')->first();

        if ($semuaNilai->count() === 1 || $terendah['nilai'] === $tertinggi['nilai']) {
            $narasi = ($terendah['nilai'] > 84) ? "Menunjukkan penguasaan yang baik dalam hal" : "Perlu penguatan dalam hal";
            return $narasi . " " . $semuaNilai->pluck('tp')->unique()->implode(', ') . ".";
        }

        $kunciRendah = ($terendah['nilai'] < 75) ? "Perlu bimbingan dalam hal" : "Perlu peningkatan dalam hal";
        $kunciTinggi = ($tertinggi['nilai'] > 89) ? "Sangat mahir dalam hal" : "Menunjukkan penguasaan yang baik dalam hal";

        return "{$kunciTinggi} " . trim($tertinggi['tp']) . ", namun {$kunciRendah} " . trim($terendah['tp']) . ".";
    }
}