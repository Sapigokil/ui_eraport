<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PengumumanSiswa;
use App\Models\PengumumanSetting; 
use Carbon\Carbon;

class PengumumanController extends Controller
{
    /**
     * Menampilkan Halaman Pengumuman Siswa
     */
    public function index()
    {
        // 1. Ambil data user yang sedang login
        $user = Auth::user(); 
        $id_siswa = $user->id_siswa ?? $user->id; 

        $siswa = DB::table('siswa')->where('id_siswa', $id_siswa)->first();

        if (!$siswa) {
            return redirect()->back()->with('error', 'Data identitas siswa tidak ditemukan.');
        }

        // 2. Ambil Data pengumuman terbaru (Tanpa memfilter 'published' dulu agar bisa kita seleksi)
        $pengumuman = PengumumanSiswa::where('id_siswa', $id_siswa)
                        ->orderBy('created_at', 'desc')
                        ->first();

        // Variabel Default untuk Gatekeeper
        $isAktif = false;
        $isWaktuBuka = false;
        $waktuPengumuman = null;
        $status = null;
        $pesan = '';
        $jenis = 'kenaikan';
        $catatanTambahan = '';
        $fileSkl = null;

        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        $defaultTA = ($bulanSekarang >= 7) ? $tahunSekarang . '/' . ($tahunSekarang + 1) : ($tahunSekarang - 1) . '/' . $tahunSekarang;

        // 👇 CEK PERLINDUNGAN STATUS "HOLD" VS "HAK ISTIMEWA ALUMNI" 👇
        if ($pengumuman) {
            $statusRaw = strtolower($pengumuman->status_hasil);
            $isAlumni = ($pengumuman->has_seen == 1 && $statusRaw == 'lulus');

            // Jika status pengumuman ditarik kembali (hold) karena jadwal dihapus admin,
            // DAN dia BUKAN alumni yang sudah membuka surat, maka kita sembunyikan datanya.
            if ($pengumuman->status !== 'published' && !$isAlumni) {
                $pengumuman = null; 
            }
        }

        // 3. PROSES DATA JIKA LOLOS SELEKSI
        if ($pengumuman) {
            $jenis = $pengumuman->jenis;
            $catatanTambahan = $pengumuman->catatan;
            $statusRaw = strtolower($pengumuman->status_hasil); 

            // Cek Jadwal di tabel Setting
            $setting = PengumumanSetting::where('jenis', $pengumuman->jenis)
                        ->where('tahun_ajaran', $pengumuman->tahun_ajaran)
                        ->first();

            if ($setting) {
                $isAktif = $setting->is_aktif;
                $waktuPengumuman = $setting->waktu_buka; 
                $waktuTutup = $setting->waktu_tutup;
                $sekarang = Carbon::now();

                // Hitung rentang waktu buka & tutup
                if ($sekarang->between($waktuPengumuman, $waktuTutup)) {
                    $isWaktuBuka = true;
                } elseif ($sekarang->greaterThan($waktuTutup)) {
                    $isAktif = false; 
                } else {
                    $isWaktuBuka = false; 
                }
            }

            // Menentukan status visual (Lulus/Naik/Gagal)
            if (in_array($statusRaw, ['naik', 'naik kelas', 'y'])) {
                $status = 'sukses';
                $pesan = 'NAIK KELAS';
            } elseif (in_array($statusRaw, ['tinggal', 'tinggal kelas', 'n'])) {
                $status = 'gagal';
                $pesan = 'TIDAK NAIK KELAS';
            } elseif ($statusRaw == 'lulus') {
                $status = 'sukses';
                $pesan = 'L U L U S';
                
                // Cek ketersediaan file SKL di tabel riwayat
                $riwayat = DB::table('riwayat_kenaikan_kelas')
                    ->where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran_lama', $pengumuman->tahun_ajaran)
                    ->whereNotNull('file_skl')
                    ->first();
                    
                if ($riwayat) {
                    $fileSkl = $riwayat->file_skl;
                }
                
            } elseif (in_array($statusRaw, ['tidak lulus', 'tidak_lulus'])) {
                $status = 'gagal';
                $pesan = 'TIDAK LULUS';
            }

            // ====================================================================
            // BYPASS GATEKEEPER KHUSUS SISWA LULUS YANG SUDAH BUKA AMPLOP 
            // Hak istimewa agar alumni tetap bisa melihat SKL selamanya
            // ====================================================================
            if ($pengumuman->has_seen == 1 && $statusRaw == 'lulus') {
                $isAktif = true;
                $isWaktuBuka = true;
            }
        }

        return view('sismenu.pengumuman', compact(
            'siswa', 'waktuPengumuman', 'isWaktuBuka', 'isAktif', 
            'status', 'pesan', 'jenis', 'pengumuman', 'catatanTambahan', 'defaultTA', 'fileSkl'
        ));
    }

    /**
     * API untuk mencatat bahwa siswa sudah mengklik tombol "Buka Surat"
     */
    public function tandaiDibaca(Request $request)
    {
        $user = Auth::user(); 
        $id_siswa = $user->id_siswa ?? $user->id; 

        // Untuk menandai dibaca, kita tetap harus memastikan bahwa statusnya memang sedang published
        // agar siswa tidak bisa menembak API saat jadwal masih 'hold'
        $pengumuman = PengumumanSiswa::where('id_siswa', $id_siswa)
                        ->where('status', 'published')
                        ->orderBy('created_at', 'desc')
                        ->first();

        if ($pengumuman && $pengumuman->has_seen == 0) {
            $pengumuman->tandaiSudahDibaca(); 
            return response()->json(['success' => true, 'message' => 'Status dibaca berhasil diupdate.']);
        }

        return response()->json(['success' => true, 'message' => 'Sudah pernah dibaca sebelumnya atau data tidak valid.']);
    }

    /**
     * Download SKL Siswa
     */
    public function downloadSkl()
    {
        $user = Auth::user(); 
        $id_siswa = $user->id_siswa ?? $user->id; 

        $riwayat = DB::table('riwayat_kenaikan_kelas')
            ->where('id_siswa', $id_siswa)
            ->where('status', 'lulus') 
            ->whereNotNull('file_skl')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$riwayat) {
            return abort(404, 'File SKL tidak tersedia atau Anda belum dinyatakan Lulus.');
        }

        $path = storage_path('app/skl/' . $riwayat->file_skl);

        if (!file_exists($path)) {
            return abort(404, 'File PDF fisik tidak ditemukan di server. Silakan hubungi Wali Kelas.');
        }

        // Return file sebagai force download
        return response()->download($path, 'Surat_Keterangan_Lulus.pdf');
    }
}