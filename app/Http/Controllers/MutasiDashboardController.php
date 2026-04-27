<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RiwayatKenaikanKelas;
use Illuminate\Support\Facades\DB;

class MutasiDashboardController extends Controller
{
    /**
     * DASHBOARD KENAIKAN KELAS (Khusus Kelas 10 & 11)
     */
    public function index(Request $request)
    {
        $debug = true; 
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        
        if ($bulanSekarang >= 7) {
            $taAktif = $tahunSekarang . '/' . ($tahunSekarang + 1);
        } else {
            $taAktif = ($tahunSekarang - 1) . '/' . $tahunSekarang;
        }

        // Terapkan Filter Tahun Ajaran (Jika ada, jika tidak gunakan default)
        $taLama = $request->input('tahun_ajaran', $taAktif);
        
        $kelasMaster = Kelas::orderBy('nama_kelas', 'asc')->get()->map(function($k) {
            preg_match('/^\d+/', $k->nama_kelas, $matches);
            $k->tingkat = !empty($matches) ? (int)$matches[0] : 0;
            
            $words = explode(' ', trim($k->nama_kelas));
            $k->jurusan = $words[1] ?? 'UMUM';
            
            return $k;
        });

        $maxTingkat = $kelasMaster->max('tingkat');
        $dashboardData = [];
        $tingkatStatus = []; 

        foreach ($kelasMaster as $k) {
            $siswaAktif = Siswa::where('id_kelas', $k->id_kelas)->where('status', 'aktif')->count();
            
            $raporCetak = DB::table('nilai_akhir_rapor')
                ->where('id_kelas', $k->id_kelas)
                ->where('semester', 2)
                ->where('tahun_ajaran', $taLama)
                ->where('status_data', 'cetak')
                ->count();
                
            // 👇 HITUNG DETAIL STATUS KENAIKAN DARI TABEL RIWAYAT 👇
            $riwayatNaik = DB::table('riwayat_kenaikan_kelas')
                ->where('id_kelas_lama', $k->id_kelas)
                ->where('tahun_ajaran_lama', $taLama)
                ->where('status', 'naik_kelas')
                ->count();

            $riwayatTinggal = DB::table('riwayat_kenaikan_kelas')
                ->where('id_kelas_lama', $k->id_kelas)
                ->where('tahun_ajaran_lama', $taLama)
                ->where('status', 'tinggal_kelas')
                ->count();

            $totalSudahProses = $riwayatNaik + $riwayatTinggal;
            $belumProses = max(0, $siswaAktif - $totalSudahProses);

            $isSelesai = ($siswaAktif > 0 && $belumProses == 0);

            if (!isset($tingkatStatus[$k->tingkat])) {
                $tingkatStatus[$k->tingkat] = ['total_kelas' => 0, 'kelas_selesai' => 0];
            }
            $tingkatStatus[$k->tingkat]['total_kelas']++;
            
            if ($isSelesai || $debug) {
                $tingkatStatus[$k->tingkat]['kelas_selesai']++;
            }

            $hue = crc32($k->jurusan) % 360;
            if ($k->tingkat == $maxTingkat - 1) {
                $l1 = 65; $l2 = 50; 
            } else {
                $l1 = 75; $l2 = 60; 
            }
            
            $k->bg_gradient = "linear-gradient(135deg, hsl({$hue}, 75%, {$l1}%), hsl(".($hue + 25).", 75%, {$l2}%))";
            $k->siswa_aktif = $siswaAktif;
            $k->rapor_cetak = $raporCetak;
            
            // Simpan detail untuk ditampilkan di View
            $k->stat_naik = $riwayatNaik;
            $k->stat_tinggal = $riwayatTinggal;
            $k->stat_belum = $belumProses;
            $k->sudah_proses = $totalSudahProses;

            $k->is_selesai = $debug ? false : $isSelesai;
            if($siswaAktif == 0) $k->is_selesai = true; 
            
            $dashboardData[] = $k;
        }

        foreach ($dashboardData as $k) {
            $k->is_terkunci = false;
            $k->pesan_kunci = '';

            // Cek hierarki, kelas di bawahnya tidak bisa diproses jika kelas di atasnya belum selesai
            if ($k->tingkat < $maxTingkat) {
                $tingkatAtas = $k->tingkat + 1;
                if (isset($tingkatStatus[$tingkatAtas])) {
                    $cekAtas = $tingkatStatus[$tingkatAtas];
                    if ($cekAtas['kelas_selesai'] < $cekAtas['total_kelas']) {
                        $k->is_terkunci = true;
                        $k->pesan_kunci = "Menunggu Tingkat $tingkatAtas selesai diproses sepenuhnya.";
                    }
                }
            }

            $k->rapor_aman = $debug ? true : ($k->rapor_cetak >= $k->siswa_aktif) && ($k->siswa_aktif > 0);
        }

        // Tampilkan hanya kelas non-kelulusan, group by TINGKAT
        $groupedData = collect($dashboardData)
            ->where('tingkat', '<', $maxTingkat)
            ->sortByDesc('tingkat')
            ->groupBy('tingkat');

        // Untuk Dropdown Filter TA
        $listTA = RiwayatKenaikanKelas::select('tahun_ajaran_lama')->distinct()->orderBy('tahun_ajaran_lama', 'desc')->pluck('tahun_ajaran_lama')->toArray();
        if(!in_array($taAktif, $listTA)) array_unshift($listTA, $taAktif);

        return view('mutasi.kenaikan_dashboard', compact('groupedData', 'maxTingkat', 'taLama', 'listTA'));
    }

    /**
     * DASHBOARD KELULUSAN (Khusus Kelas 12)
     */
    public function kelulusanIndex(Request $request)
    {
        $debug = true; 
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        
        if ($bulanSekarang >= 7) {
            $taAktif = $tahunSekarang . '/' . ($tahunSekarang + 1);
        } else {
            $taAktif = ($tahunSekarang - 1) . '/' . $tahunSekarang;
        }

        // Terapkan Filter Tahun Ajaran
        $taLama = $request->input('tahun_ajaran', $taAktif);
        
        $kelasMaster = Kelas::orderBy('nama_kelas', 'asc')->get()->map(function($k) {
            preg_match('/^\d+/', $k->nama_kelas, $matches);
            $k->tingkat = !empty($matches) ? (int)$matches[0] : 0;
            
            $words = explode(' ', trim($k->nama_kelas));
            $k->jurusan = $words[1] ?? 'UMUM';
            
            return $k;
        });

        $maxTingkat = $kelasMaster->max('tingkat');
        $dashboardData = [];

        $kelasKelulusan = collect($kelasMaster)->where('tingkat', $maxTingkat);

        foreach ($kelasKelulusan as $k) {
            // Jumlah asli anak yang aktif di kelas ini sekarang
            $siswaAktif = Siswa::where('id_kelas', $k->id_kelas)->where('status', 'aktif')->count();
            
            $raporCetak = DB::table('nilai_akhir_rapor')
                ->where('id_kelas', $k->id_kelas)
                ->where('semester', 2)
                ->where('tahun_ajaran', $taLama)
                ->where('status_data', 'cetak')
                ->count();
                
            // 👇 HITUNG DETAIL STATUS KELULUSAN DARI TABEL RIWAYAT 👇
            $riwayatLulus = DB::table('riwayat_kenaikan_kelas')
                ->where('id_kelas_lama', $k->id_kelas)
                ->where('tahun_ajaran_lama', $taLama)
                ->where('status', 'lulus')
                ->count();

            // 👇 PERBAIKAN: Ubah query untuk mencari 'tidak_lulus' 👇
            $riwayatTidakLulus = DB::table('riwayat_kenaikan_kelas')
                ->where('id_kelas_lama', $k->id_kelas)
                ->where('tahun_ajaran_lama', $taLama)
                ->where('status', 'tidak_lulus') 
                ->count();
            
            $totalSudahProses = $riwayatLulus + $riwayatTidakLulus;
            $belumProses = max(0, $siswaAktif - $totalSudahProses);

            // Is Selesai jika semua anak di kelas tersebut sudah diproses
            $isSelesai = ($siswaAktif > 0 && $belumProses == 0);

            $hue = crc32($k->jurusan) % 360;
            $l1 = 55; $l2 = 40; 
            
            $k->bg_gradient = "linear-gradient(135deg, hsl({$hue}, 75%, {$l1}%), hsl(".($hue + 25).", 75%, {$l2}%))";
            $k->siswa_aktif = $siswaAktif;
            $k->rapor_cetak = $raporCetak;
            
            // Simpan detail untuk ditampilkan di View
            $k->stat_lulus = $riwayatLulus;
            // Tetap menggunakan nama properti stat_tinggal agar View tidak rusak
            $k->stat_tinggal = $riwayatTidakLulus; 
            $k->stat_belum = $belumProses;
            $k->sudah_proses = $totalSudahProses;

            $k->is_selesai = $debug ? false : $isSelesai;
            if($siswaAktif == 0) $k->is_selesai = true; 

            $k->is_terkunci = false;
            $k->pesan_kunci = '';
            
            $k->rapor_aman = $debug ? true : ($k->rapor_cetak >= $k->siswa_aktif) && ($k->siswa_aktif > 0);
            
            $dashboardData[] = $k;
        }

        $groupedData = collect($dashboardData)
            ->sortBy('jurusan')
            ->groupBy('jurusan');

        // Untuk Dropdown Filter TA
        $listTA = RiwayatKenaikanKelas::select('tahun_ajaran_lama')->distinct()->orderBy('tahun_ajaran_lama', 'desc')->pluck('tahun_ajaran_lama')->toArray();
        if(!in_array($taAktif, $listTA)) array_unshift($listTA, $taAktif);

        return view('mutasi.kelulusan_dashboard', compact('groupedData', 'maxTingkat', 'taLama', 'listTA'));
    }
}