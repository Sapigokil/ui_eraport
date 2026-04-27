<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PengumumanSiswa;
use App\Models\PengumumanSetting; // Import Model Baru
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

        // 2. Ambil Data dari tabel pengumuman_siswa (Hanya yang sudah Published)
        $pengumuman = PengumumanSiswa::where('id_siswa', $id_siswa)
                        ->where('status', 'published') 
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

        // Tentukan Tahun Ajaran Default (untuk tampilan jika data draf belum ada)
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        $defaultTA = ($bulanSekarang >= 7) ? $tahunSekarang . '/' . ($tahunSekarang + 1) : ($tahunSekarang - 1) . '/' . $tahunSekarang;

        if ($pengumuman) {
            $jenis = $pengumuman->jenis;
            $catatanTambahan = $pengumuman->catatan;
            $statusRaw = strtolower($pengumuman->status_hasil);

            // 3. LOGIKA GATEKEEPER JADWAL (Membaca tabel pengumuman_setting)
            $setting = PengumumanSetting::where('jenis', $pengumuman->jenis)
                        ->where('tahun_ajaran', $pengumuman->tahun_ajaran)
                        ->first();

            if ($setting) {
                $isAktif = $setting->is_aktif;
                $waktuPengumuman = $setting->waktu_buka; // Sudah otomatis Carbon dari casting model
                $waktuTutup = $setting->waktu_tutup;
                $sekarang = Carbon::now();

                // Cek apakah sekarang berada di dalam rentang waktu buka & tutup
                if ($sekarang->between($waktuPengumuman, $waktuTutup)) {
                    $isWaktuBuka = true;
                } elseif ($sekarang->greaterThan($waktuTutup)) {
                    $isAktif = false; // Otomatis tutup jika waktu sudah lewat
                } else {
                    $isWaktuBuka = false; // Belum waktunya (akan muncul countdown)
                }
            }

            // 4. Menentukan status visual (Lulus/Naik/Gagal)
            if (in_array($statusRaw, ['naik', 'naik kelas', 'y'])) {
                $status = 'sukses';
                $pesan = 'NAIK KELAS';
            } elseif (in_array($statusRaw, ['tinggal', 'tinggal kelas', 'n'])) {
                $status = 'gagal';
                $pesan = 'TIDAK NAIK KELAS';
            } elseif ($statusRaw == 'lulus') {
                $status = 'sukses';
                $pesan = 'L U L U S';
            } elseif (in_array($statusRaw, ['tidak lulus', 'tidak_lulus'])) {
                $status = 'gagal';
                $pesan = 'TIDAK LULUS';
            }
        }

        return view('sismenu.pengumuman', compact(
            'siswa', 'waktuPengumuman', 'isWaktuBuka', 'isAktif', 
            'status', 'pesan', 'jenis', 'pengumuman', 'catatanTambahan', 'defaultTA'
        ));
    }

    /**
     * API untuk mencatat bahwa siswa sudah mengklik tombol "Buka Surat"
     */
    public function tandaiDibaca(Request $request)
    {
        $user = Auth::user(); 
        $id_siswa = $user->id_siswa ?? $user->id; 

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
}