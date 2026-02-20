<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class MutasiDashboardController extends Controller
{
    public function index(Request $request)
    {
        $debug = true;
        // 1. Tentukan Tahun Ajaran (Bisa disesuaikan dengan helper/global setting Anda)
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        
        // Asumsi akhir tahun ajaran (Semester Genap) adalah bulan sebelum Juli
        if ($bulanSekarang >= 7) {
            $taLama = $tahunSekarang . '/' . ($tahunSekarang + 1);
        } else {
            $taLama = ($tahunSekarang - 1) . '/' . $tahunSekarang;
        }
        
        // 2. Ambil Semua Kelas & Mapping Data Dasar
        $kelasMaster = Kelas::orderBy('nama_kelas', 'asc')->get()->map(function($k) {
            // Ekstrak Tingkat (Angka di depan)
            preg_match('/^\d+/', $k->nama_kelas, $matches);
            $k->tingkat = !empty($matches) ? (int)$matches[0] : 0;
            
            // Ekstrak Jurusan (Kata ke-2)
            $words = explode(' ', trim($k->nama_kelas));
            $k->jurusan = $words[1] ?? 'UMUM';
            
            return $k;
        });

        $maxTingkat = $kelasMaster->max('tingkat');
        $dashboardData = [];
        $tingkatStatus = []; // Untuk mengecek apakah sebuah tingkat sudah selesai semua

        // 3. Kalkulasi per Kelas
        foreach ($kelasMaster as $k) {
            // Jumlah Siswa Aktif
            $siswaAktif = Siswa::where('id_kelas', $k->id_kelas)->where('status', 'aktif')->count();
            
            // Cek Rapor Cetak (Semester 2 di Tahun Ajaran Lama)
            $raporCetak = DB::table('nilai_akhir_rapor')
                ->where('id_kelas', $k->id_kelas)
                ->where('semester', 2)
                ->where('tahun_ajaran', $taLama)
                ->where('status_data', 'cetak')
                ->count();
                
            // Cek Siswa yang sudah diproses (masuk riwayat kenaikan/kelulusan)
            $sudahProses = DB::table('riwayat_kenaikan_kelas')
                ->where('id_kelas_lama', $k->id_kelas)
                ->where('tahun_ajaran_lama', $taLama)
                ->count();

            // Status Penyelesaian Kelas Ini
            // Jika tidak ada siswa aktif, otomatis dianggap selesai. Jika ada, pastikan semua sudah diproses.
            $isSelesai = ($siswaAktif == 0) || ($sudahProses >= $siswaAktif);

            // Simpan status kelas ke dalam array tingkat untuk pengecekan hierarki nanti
            if (!isset($tingkatStatus[$k->tingkat])) {
                $tingkatStatus[$k->tingkat] = ['total_kelas' => 0, 'kelas_selesai' => 0];
            }
            $tingkatStatus[$k->tingkat]['total_kelas']++;
            
            if ($isSelesai || $debug) {
                $tingkatStatus[$k->tingkat]['kelas_selesai']++;
            }

            // --- PEWARNAAN GRADIENT HSL DINAMIS ---
            // Hue (0-360) didapat dari Hash nama jurusan.
            $hue = crc32($k->jurusan) % 360;
            
            // Kita atur Lightness untuk menciptakan 2 titik gradasi
            if ($k->tingkat == $maxTingkat) {
                $l1 = 55; $l2 = 40; // Solid / Gelap (Warna berani)
            } elseif ($k->tingkat == $maxTingkat - 1) {
                $l1 = 65; $l2 = 50; // Medium (Warna pertengahan)
            } else {
                $l1 = 75; $l2 = 60; // Pastel / Terang (Warna soft)
            }
            
            // Buat Gradient linear (Sudut 135 derajat, warna dasar -> warna dengan Hue bergeser 25 derajat)
            $k->bg_gradient = "linear-gradient(135deg, hsl({$hue}, 75%, {$l1}%), hsl(".($hue + 25).", 75%, {$l2}%))";
            
            // Karena background-nya sekarang rich gradient, teks kita buat putih semua agar modern & kontras
            $k->text_color = 'text-white';
            $k->siswa_aktif = $siswaAktif;
            $k->rapor_cetak = $raporCetak;
            $k->sudah_proses = $sudahProses;
            $k->is_selesai = $debug ? false : $isSelesai;

            if($siswaAktif == 0) $k->is_selesai = true; // Override: Jika tidak ada siswa aktif, otomatis selesai
            
            $dashboardData[] = $k;
        }

        // 4. Validasi Hierarki Piramida Terbalik (Top-Down)
        // Kelas bisa diproses JIKA tingkat di atasnya sudah selesai semua
        foreach ($dashboardData as $k) {
            $k->is_terkunci = false;
            $k->pesan_kunci = '';

            // Jika dia bukan tingkat tertinggi, cek tingkat di atasnya (Tingkat + 1)
            if ($k->tingkat < $maxTingkat) {
                $tingkatAtas = $k->tingkat + 1;
                // Pastikan tingkat atas ada dan apakah sudah selesai semua?
                if (isset($tingkatStatus[$tingkatAtas])) {
                    $cekAtas = $tingkatStatus[$tingkatAtas];
                    if ($cekAtas['kelas_selesai'] < $cekAtas['total_kelas']) {
                        $k->is_terkunci = true;
                        $k->pesan_kunci = "Menunggu Kelas $tingkatAtas selesai diproses sepenuhnya.";
                    }
                }
            }

            // Validasi Gatekeeper Rapor
            $k->rapor_aman = $debug ? true : ($k->rapor_cetak >= $k->siswa_aktif) && ($k->siswa_aktif > 0);
        }

        // Urutkan data: Tingkat tertinggi di atas, lalu berdasarkan nama kelas
        // Urutkan data: Tingkat tertinggi di atas, lalu kelompokkan berdasarkan tingkat
        $groupedData = collect($dashboardData)
            ->sortByDesc('tingkat')
            ->groupBy('tingkat');

        return view('mutasi.kenaikan_dashboard', compact('groupedData', 'maxTingkat', 'taLama'));
    }
}