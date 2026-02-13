<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\DetailSiswa;
use App\Models\RiwayatMutasiKeluar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MutasiKeluarController extends Controller
{
    /**
     * Halaman Utama Mutasi Keluar
     */
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $siswaAktif = collect();
        
        // 1. Load Siswa Aktif berdasarkan Filter Kelas
        if ($request->has('id_kelas') && $request->id_kelas != '') {
            $siswaAktif = Siswa::where('status', 'aktif')
                        ->where('id_kelas', $request->id_kelas)
                        ->orderBy('nama_siswa')
                        ->get();
        }

        // 2. Load Riwayat Mutasi (History)
        $riwayat = RiwayatMutasiKeluar::with(['siswa', 'kelas'])
                    ->orderBy('tgl_mutasi', 'desc')
                    ->limit(50) // Batasi agar tidak berat
                    ->get();

        return view('mutasi.keluar_index', compact('kelas', 'siswaAktif', 'riwayat'));
    }

    /**
     * Proses Simpan Mutasi Keluar
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_siswa'       => 'required|exists:siswa,id_siswa',
            'jenis_mutasi'   => 'required|string',
            'tgl_mutasi'     => 'required|date',
            'alasan'         => 'required|string',
            'sekolah_tujuan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $siswa = Siswa::findOrFail($request->id_siswa);
            
            // 1. Simpan ke Riwayat
            RiwayatMutasiKeluar::create([
                'id_siswa'          => $siswa->id_siswa,
                'id_kelas_terakhir' => $siswa->id_kelas, // Simpan kelas terakhir sebelum keluar
                'jenis_mutasi'      => $request->jenis_mutasi,
                'tgl_mutasi'        => $request->tgl_mutasi,
                'alasan'            => $request->alasan,
                'sekolah_tujuan'    => $request->sekolah_tujuan,
                'user_input'        => Auth::user()->name ?? 'System',
            ]);

            // 2. Update Status Siswa di Tabel Utama
            $siswa->update([
                'status' => 'keluar',
                'id_kelas' => null // Lepas dari relasi kelas langsung di tabel siswa (jika kolom ada)
            ]);

            // 3. Lepas dari Detail Siswa (Jika pakai tabel detail_siswa untuk plotting kelas)
            DetailSiswa::where('id_siswa', $siswa->id_siswa)->update([
                'id_kelas' => null
            ]);

            DB::commit();
            return back()->with('success', 'Siswa berhasil diproses mutasi keluar.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan Mutasi (Kembalikan ke Aktif) - Opsional
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $riwayat = RiwayatMutasiKeluar::findOrFail($id);
            $siswa = Siswa::findOrFail($riwayat->id_siswa);

            // Kembalikan status siswa
            $siswa->update(['status' => 'aktif']);
            
            // Note: Kelas tidak dikembalikan otomatis, harus di-plotting ulang via menu Pindah Kelas/Anggota Kelas
            // Hapus data riwayat
            $riwayat->delete();

            DB::commit();
            return back()->with('success', 'Mutasi dibatalkan. Siswa kembali aktif (Tanpa Kelas). Silakan plotting ulang kelasnya.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan: ' . $e->getMessage());
        }
    }
}