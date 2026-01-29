<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\Pembelajaran;
use Illuminate\Support\Str;

class MonitoringController extends Controller
{
    /**
     * MENU 1: MONITORING GLOBAL (ADMIN)
     */
    public function index(Request $request)
    {
        $periode = $this->getPeriode($request);
        
        // Ambil Semua Kelas
        $listKelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        
        // Hitung Data dengan Logika Snapshot-First
        $result = $this->hitungMonitoringData($listKelas, $periode);

        return view('monitoring.kesiapan_rapor.index', array_merge($result, $periode));
    }

    // =========================================================================
    // PRIVATE HELPER (KHUSUS ADMIN)
    // =========================================================================

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

    private function hitungMonitoringData($listKelas, $periode)
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

        foreach ($listKelas as $k) {
            // A. DATA SISWA (Baseline Master)
            $siswaCollection = DB::table('siswa')
                ->join('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa')
                ->where('siswa.id_kelas', $k->id_kelas)
                ->select('siswa.id_siswa', 'siswa.nama_siswa', 'siswa.nisn', 'detail_siswa.agama', 'siswa.nipd')
                ->orderBy('siswa.nama_siswa')
                ->get();

            $totalSiswaKelas = $siswaCollection->count();
            if ($totalSiswaKelas == 0) continue; 

            // B. DATA SNAPSHOT RAPOR
            $snapshotRaporList = DB::table('nilai_akhir_rapor')
                ->where('id_kelas', $k->id_kelas)
                ->where('semester', $smtInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->get()
                ->keyBy('id_siswa');

            // C. BAGIAN 1: NILAI MAPEL (Snapshot nilai_akhir)
            $pembelajaran = Pembelajaran::with(['mapel' => function ($q) {
                    $q->where('is_active', 1);
                }, 'guru']) 
                ->where('id_kelas', $k->id_kelas)
                ->get();

            $detailMapel = [];
            $kelasMapelSelesai = 0;
            $kelasMapelTotalHitung = 0;

            foreach ($pembelajaran as $p) {
                if (!$p->mapel) continue;

                // FIX: Logika Target Siswa per Mapel Agama
                $targetSiswaMapel = $totalSiswaKelas; 
                $namaMapel = $p->mapel->nama_mapel;
                
                $agamas = ['Islam', 'Kristen', 'Katholik', 'Katolik', 'Hindu', 'Buddha', 'Budha', 'Khonghucu', 'Konghucu'];
                foreach ($agamas as $rel) {
                    if (stripos($namaMapel, $rel) !== false) {
                        $search = ($rel == 'Katolik' || $rel == 'Katholik') ? ['katholik', 'katolik'] : 
                                  (($rel == 'Budha' || $rel == 'Buddha') ? ['buddha', 'budha'] : 
                                  (($rel == 'Konghucu' || $rel == 'Khonghucu') ? ['konghucu', 'khonghucu'] : [strtolower($rel)]));
                        
                        $targetSiswaMapel = $siswaCollection->filter(function($s) use ($search) {
                            return in_array(strtolower($s->agama), $search);
                        })->count();
                        break; 
                    }
                }

                if ($targetSiswaMapel == 0) continue; 

                // Hitung record yang sudah "Final"
                $countSnapshotNilai = DB::table('nilai_akhir')
                    ->where('id_kelas', $k->id_kelas)
                    ->where('id_mapel', $p->id_mapel)
                    ->where('semester', $smtInt)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->where('status_data', 'final')
                    ->count();

                $statusMapel = 'kosong'; 
                if ($countSnapshotNilai >= $targetSiswaMapel) {
                    $statusMapel = 'lengkap';
                    $kelasMapelSelesai++;
                    $stats['mapel_final']++;
                } elseif ($countSnapshotNilai > 0) {
                    $statusMapel = 'proses';
                }

                $detailMapel[] = [
                    'id_mapel' => $p->id_mapel,
                    'mapel'    => $p->mapel->nama_mapel,
                    'guru'     => $p->guru->nama_guru ?? 'Belum ditentukan', // Lengkapi Key Guru
                    'progress' => $countSnapshotNilai,
                    'total'    => $targetSiswaMapel,
                    'status'   => $statusMapel,
                    'persen'   => round(($countSnapshotNilai / $targetSiswaMapel) * 100),
                    'kategori' => $p->mapel->kategori
                ];
                
                $kelasMapelTotalHitung++;
                $stats['mapel_total']++;
            }

            // D. BAGIAN 2: CATATAN WALI KELAS (Snapshot nilai_akhir_rapor)
            $detailCatatan = [];
            $siswaTersnapshot = 0;

            foreach ($siswaCollection as $s) {
                $snap = $snapshotRaporList->get($s->id_siswa);
                $hasSnapshot = ($snap !== null);

                $detailCatatan[] = [
                    'nama_siswa'    => $hasSnapshot ? $snap->nama_siswa_snapshot : $s->nama_siswa,
                    'nisn'          => $hasSnapshot ? $snap->nisn_snapshot : $s->nisn,
                    // FIX: Sertakan data _short dan _full untuk Kokurikuler
                    'kokurikuler_short'   => Str::limit($snap->kokurikuler ?? '-', 30),
                    'kokurikuler_full' => $snap->kokurikuler ?? '-',
                    'ekskul_html'   => $hasSnapshot ? $this->formatEkskul($snap->data_ekskul) : '-', 
                    'sakit'         => $snap->sakit ?? 0, 
                    'ijin'          => $snap->ijin ?? 0, 
                    'alpha'         => $snap->alpha ?? 0, 
                    // FIX: Sertakan data _short dan _full untuk Catatan
                    'catatan_short' => Str::limit($snap->catatan_wali_kelas ?? '-', 30), 
                    'catatan_full'  => $snap->catatan_wali_kelas ?? '-',
                    'status'        => ($hasSnapshot && $snap->status_data === 'final') ? 'ada' : 'kosong'
                ];

                if ($hasSnapshot && $snap->status_data === 'final') {
                    $siswaTersnapshot++;
                }
            }

            $persenKelas = ($kelasMapelTotalHitung > 0) ? round(($kelasMapelSelesai / $kelasMapelTotalHitung) * 100) : 0;
            $persenRapor = ($totalSiswaMaster ?? $totalSiswaKelas > 0) ? round(($siswaTersnapshot / $totalSiswaKelas) * 100) : 0;

            $monitoringData[] = (object) [
                'kelas'          => $k,
                'wali_kelas'     => $k->wali_kelas ?? '-',
                'jml_mapel'      => $kelasMapelTotalHitung,
                'mapel_selesai'  => $kelasMapelSelesai,
                'persen'         => $persenKelas,
                'persen_catatan' => $persenRapor, // Sesuai permintaan Anda
                'detail'         => $detailMapel,
                'detail_catatan' => $detailCatatan
            ];
        }

        // Statistik Global
        if ($stats['mapel_total'] > 0) {
            $persenGlobal = round(($stats['mapel_final'] / $stats['mapel_total']) * 100);
            if ($persenGlobal >= 100 && $stats['mapel_final'] < $stats['mapel_total']) $persenGlobal = 99;
            $stats['persen_global'] = (int) $persenGlobal;
        }

        return compact('monitoringData', 'stats');
    }

    /**
     * Helper untuk format JSON data_ekskul snapshot
     */
    private function formatEkskul($jsonEkskul)
    {
        if (!$jsonEkskul) return '-';
        $data = json_decode($jsonEkskul, true);
        if (!is_array($data)) return '-';

        $res = [];
        foreach ($data as $e) {
            $res[] = "• <b>{$e['nama']}</b> ({$e['predikat']})<br><span class='text-muted text-xxs'>{$e['keterangan']}</span>";
        }
        return implode('<br>', $res);
    }
}