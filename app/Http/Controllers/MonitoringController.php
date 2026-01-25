<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\Siswa; // Tetap dipakai jika butuh model, tapi kita pakai Query Builder untuk Join
use App\Models\Pembelajaran;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        // 1. Setting Periode
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
        $smtInt       = ($semester == 'Ganjil' || $semester == '1') ? 1 : 2;

        // 2. Query Data
        $listKelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $monitoringData = [];
        
        $stats = [
            'total_rombel' => $listKelas->count(),
            'mapel_final'  => 0,
            'mapel_total'  => 0,
            'persen_global'=> 0
        ];

        foreach ($listKelas as $k) {
            
            // --- PERBAIKAN 1: JOIN KE DETAIL_SISWA ---
            // Kita ambil ID Siswa dan AGAMA dari tabel detail_siswa
            // Asumsi: Tabel 'siswa' menyimpan id_kelas siswa yang aktif
            $siswaCollection = DB::table('siswa')
                ->join('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa')
                ->where('siswa.id_kelas', $k->id_kelas)
                ->select('siswa.id_siswa', 'detail_siswa.agama') // Ambil Agama dari Detail
                ->get();

            $totalSiswaKelas = $siswaCollection->count();

            // Skip jika kelas kosong
            if ($totalSiswaKelas == 0) continue; 

            // Ambil Mapel (Eloquent + Sorting)
            $pembelajaran = Pembelajaran::with(['mapel' => function ($q) {
                    $q->where('is_active', 1);
                }, 'guru']) 
                ->where('id_kelas', $k->id_kelas)
                ->get();

            $mapelDiKelas = $pembelajaran->map(function ($item) {
                if (!$item->mapel) return null;
                $item->mapel->nama_guru_pengampu = $item->guru->nama_guru ?? 'Belum diset';
                return $item->mapel;
            })
            ->filter()
            ->sortBy([
                ['kategori', 'asc'], 
                ['urutan', 'asc'],   
            ]);

            $detailMapel = [];
            $kelasMapelSelesai = 0;
            $kelasMapelTotalHitung = 0;

            foreach ($mapelDiKelas as $m) {
                
                $targetSiswa = $totalSiswaKelas; 
                $namaMapel = $m->nama_mapel;
                
                // --- PERBAIKAN 2: FILTER DATA DARI HASIL JOIN ---
                // Menggunakan strtolower agar tidak sensitif huruf besar/kecil
                // Data Agama: Islam, Kristen, Katholik, Hindu, Budha, Khonghucu

                // 1. ISLAM
                if (stripos($namaMapel, 'Islam') !== false) {
                    $targetSiswa = $siswaCollection->filter(function($s) {
                        return strtolower($s->agama) == 'islam';
                    })->count();
                } 
                // 2. KRISTEN
                elseif (stripos($namaMapel, 'Kristen') !== false || stripos($namaMapel, 'Protestan') !== false) {
                    $targetSiswa = $siswaCollection->filter(function($s) {
                        return in_array(strtolower($s->agama), ['kristen', 'protestan']);
                    })->count();
                } 
                // 3. KATHOLIK (Handle ejaan Katholik / Katolik)
                elseif (stripos($namaMapel, 'Katholik') !== false || stripos($namaMapel, 'Katolik') !== false) {
                    $targetSiswa = $siswaCollection->filter(function($s) {
                        return in_array(strtolower($s->agama), ['katholik', 'katolik']);
                    })->count();
                } 
                // 4. HINDU
                elseif (stripos($namaMapel, 'Hindu') !== false) {
                    $targetSiswa = $siswaCollection->filter(function($s) {
                        return strtolower($s->agama) == 'hindu';
                    })->count();
                } 
                // 5. BUDHA (Handle ejaan Budha / Buddha)
                elseif (stripos($namaMapel, 'Buddha') !== false || stripos($namaMapel, 'Budha') !== false) {
                    $targetSiswa = $siswaCollection->filter(function($s) {
                        return in_array(strtolower($s->agama), ['buddha', 'budha']);
                    })->count();
                } 
                // 6. KHONGHUCU (Handle ejaan Khonghucu / Konghucu)
                elseif (stripos($namaMapel, 'Konghucu') !== false || stripos($namaMapel, 'Khonghucu') !== false) {
                    $targetSiswa = $siswaCollection->filter(function($s) {
                        return in_array(strtolower($s->agama), ['konghucu', 'khonghucu']);
                    })->count();
                }

                // SKIP JIKA TARGET SISWA 0
                // (Berarti tidak ada siswa beragama tsb di kelas ini, atau data agama kosong)
                if ($targetSiswa == 0) {
                    continue; 
                }

                // Hitung Nilai Masuk
                $sudahMasuk = DB::table('nilai_akhir')
                    ->where('id_kelas', $k->id_kelas)
                    ->where('id_mapel', $m->id_mapel)
                    ->where('semester', $smtInt)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->count();

                $status = 'kosong'; 
                if ($sudahMasuk >= $targetSiswa) {
                    $status = 'lengkap';
                    $kelasMapelSelesai++;
                    $stats['mapel_final']++;
                } elseif ($sudahMasuk > 0) {
                    $status = 'proses';
                }

                $detailMapel[] = [
                    'id_mapel'   => $m->id_mapel,
                    'mapel'      => $m->nama_mapel,
                    'guru'       => $m->nama_guru_pengampu,
                    'progress'   => $sudahMasuk,
                    'total'      => $targetSiswa,
                    'status'     => $status,
                    'persen'     => round(($sudahMasuk / $targetSiswa) * 100),
                    'kategori'   => $m->kategori
                ];
                
                $kelasMapelTotalHitung++;
                $stats['mapel_total']++;
            }

            // Hitung Persen Kelas
            $persenKelas = ($kelasMapelTotalHitung > 0) ? round(($kelasMapelSelesai / $kelasMapelTotalHitung) * 100) : 0;

            $monitoringData[] = (object) [
                'kelas'         => $k,
                'wali_kelas'    => $k->wali_kelas ?? '-',
                'jml_mapel'     => $kelasMapelTotalHitung,
                'mapel_selesai' => $kelasMapelSelesai,
                'persen'        => $persenKelas,
                'detail'        => $detailMapel
            ];
        }

        // Statistik Global
        if ($stats['mapel_total'] > 0) {
            // 1. Hitung pembulatan normal dulu
            $persen = round(($stats['mapel_final'] / $stats['mapel_total']) * 100);

            // 2. CEGAH 100% PALSU
            // Jika hasil round 100%, TAPI jumlah final < total, paksa turun ke 99%
            if ($persen >= 100 && $stats['mapel_final'] < $stats['mapel_total']) {
                $persen = 99;
            }

            // Simpan sebagai Integer (tanpa koma)
            $stats['persen_global'] = (int) $persen;
        }

        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 2; $t <= $tahunSekarang + 1; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }

        return view('monitoring.kesiapan_rapor.index', compact(
            'monitoringData', 'stats', 'tahun_ajaran', 'semester', 'tahunAjaranList'
        ));
    }
}