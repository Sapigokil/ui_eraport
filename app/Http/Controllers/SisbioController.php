<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Siswa;
use App\Models\DetailSiswa;
use App\Models\PengajuanBiodata;

class SisbioController extends Controller
{
    public function index()
    {
        $id_siswa = Auth::user()->id_siswa;

        if (!$id_siswa) {
            abort(403, 'Akun Anda tidak tertaut dengan data Siswa.');
        }

        $siswa = Siswa::with(['detail', 'kelas'])->findOrFail($id_siswa);
        
        // 1. Cek Pengajuan yang masih Pending
        $pengajuanPending = PengajuanBiodata::where('id_siswa', $id_siswa)
                                            ->where('status', 'pending')
                                            ->first();

        // 2. Cek Pengajuan Terakhir yang sudah diproses (Disetujui / Ditolak)
        $riwayatTerakhir = PengajuanBiodata::where('id_siswa', $id_siswa)
                                            ->whereIn('status', ['disetujui', 'ditolak'])
                                            ->orderBy('updated_at', 'desc')
                                            ->first();

        // Kirim ke view
        return view('sismenu.bio_index', compact('siswa', 'pengajuanPending', 'riwayatTerakhir'));
    }

    // Method Baru untuk menampilkan Halaman Form Lengkap
    public function edit()
    {
        $id_siswa = Auth::user()->id_siswa;
        
        $pengajuanPending = PengajuanBiodata::where('id_siswa', $id_siswa)->where('status', 'pending')->first();
        if ($pengajuanPending) {
            return redirect()->route('sis.biodata')->with('error', 'Anda tidak dapat mengedit data saat ini karena masih ada pengajuan yang menunggu persetujuan.');
        }

        $siswa = Siswa::with('detail')->findOrFail($id_siswa);
        return view('sismenu.bio_form', compact('siswa'));
    }

    public function ajukanPerubahan(Request $request)
    {
        $id_siswa = Auth::user()->id_siswa;

        $cekPending = PengajuanBiodata::where('id_siswa', $id_siswa)->where('status', 'pending')->first();
        if ($cekPending) {
            return redirect()->route('sis.biodata')->with('error', 'Anda masih memiliki pengajuan perubahan yang sedang menunggu persetujuan Admin.');
        }

        $detailSiswa = DetailSiswa::where('id_siswa', $id_siswa)->first();
        $inputs = $request->except(['_token', '_method']);
        $perubahan = [];

        foreach ($inputs as $kolom => $nilai_baru) {
            if (array_key_exists($kolom, $detailSiswa->getAttributes())) {
                
                $nilai_lama = $detailSiswa->$kolom;
                
                // Normalisasi string kosong menjadi null agar setara dengan database
                $input_bersih = $nilai_baru === "" ? null : $nilai_baru;

                if ((string)$input_bersih !== (string)$nilai_lama) {
                    $perubahan[$kolom] = [
                        'lama' => $nilai_lama ?? '-',
                        'baru' => $input_bersih ?? '-'
                    ];
                }
            }
        }

        if (count($perubahan) > 0) {
            PengajuanBiodata::create([
                'id_siswa'       => $id_siswa,
                'data_perubahan' => $perubahan, 
                'status'         => 'pending'
            ]);
            // Redirect kembali ke index (read-only) setelah sukses
            return redirect()->route('sis.biodata')->with('success', 'Pengajuan perubahan biodata berhasil dikirim! Silakan tunggu persetujuan Admin.');
        }

        return redirect()->route('sis.biodata.edit')->with('info', 'Tidak ada data yang Anda ubah.');
    }

    // Tambahkan di dalam class SisbioController
    public function markAsRead($id)
    {
        try {
            $id_siswa = Auth::user()->id_siswa;
            
            // Cari data tanpa firstOrFail agar tidak langsung error 404
            $pengajuan = \App\Models\PengajuanBiodata::where('id_pengajuan', $id)
                                         ->where('id_siswa', $id_siswa)
                                         ->first();

            if ($pengajuan) {
                $pengajuan->is_read = 1;
                $pengajuan->save();
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Berhasil ditandai sudah dibaca'
                ]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => 'Data pengajuan tidak ditemukan atau bukan milik siswa ini.'
                ]);
            }

        } catch (\Exception $e) {
            // Tangkap pesan error aslinya (misal: kolom is_read belum ada di database)
            return response()->json([
                'success' => false, 
                'message' => 'Error Server: ' . $e->getMessage()
            ]);
        }
    }
}