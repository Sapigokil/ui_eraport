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
     * Halaman Utama Mutasi Keluar (Menampilkan Riwayat dengan Pagination & Filter)
     */
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        // 👇 PERBAIKAN: Tambahkan relasi 'kelasTerakhir' di dalam with() 👇
        $query = RiwayatMutasiKeluar::with(['siswa', 'kelasTerakhir']); 

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_mutasi', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('tgl_mutasi', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('tgl_mutasi', '<=', $request->end_date);
        }

        if ($request->filled('id_kelas')) {
            $query->where('id_kelas_terakhir', $request->id_kelas); 
        }

        $query->orderBy('tgl_mutasi', 'desc');

        $perPage = $request->input('per_page', 10);
        if ($perPage === 'all') {
            $riwayat = $query->get(); 
        } else {
            $riwayat = $query->paginate((int)$perPage);
        }

        return view('mutasi.keluar_index', compact('kelas', 'riwayat'));
    }

    /**
     * Halaman Form Create Mutasi
     */
    public function create()
    {
        $kelas = Kelas::orderBy('nama_kelas', 'ASC')->get();
        return view('mutasi.keluar_form', compact('kelas'));
    }

    /**
     * Menangani Permintaan AJAX untuk Dropdown Siswa
     */
    public function getSiswaByKelas($id_kelas)
    {
        $siswa = Siswa::where('id_kelas', $id_kelas)
                      ->where('status', 'aktif')
                      ->select('id_siswa', 'nama_siswa', 'nisn', 'nipd')
                      ->orderBy('nama_siswa', 'ASC')
                      ->get();

        return response()->json(['data' => $siswa]);
    }

    /**
     * Halaman Edit Mutasi
     */
    public function edit($id)
    {
        $mutasi = RiwayatMutasiKeluar::with('siswa')->findOrFail($id);
        $kelas = Kelas::orderBy('nama_kelas', 'ASC')->get();

        return view('mutasi.keluar_form', compact('mutasi', 'kelas'));
    }

    /**
     * Proses Simpan Mutasi Keluar Baru
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
            
            RiwayatMutasiKeluar::create([
                'id_siswa'          => $siswa->id_siswa,
                'id_kelas_terakhir' => $siswa->id_kelas, 
                'jenis_mutasi'      => $request->jenis_mutasi,
                'tgl_mutasi'        => $request->tgl_mutasi,
                'alasan'            => $request->alasan,
                'sekolah_tujuan'    => $request->sekolah_tujuan,
                'user_input'        => Auth::user()->name ?? 'System',
            ]);

            $siswa->update([
                'status'   => 'keluar',
                'id_kelas' => null 
            ]);

            DetailSiswa::where('id_siswa', $siswa->id_siswa)->update([
                'id_kelas' => null
            ]);

            DB::commit();
            return redirect()->route('mutasi.keluar.index')->with('success', 'Siswa berhasil diproses mutasi keluar.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Proses Simpan Perubahan Edit Data Mutasi
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'jenis_mutasi'   => 'required|string',
            'tgl_mutasi'     => 'required|date',
            'alasan'         => 'required|string',
            'sekolah_tujuan' => 'nullable|string',
        ]);

        try {
            $mutasi = RiwayatMutasiKeluar::findOrFail($id);
            
            $mutasi->update([
                'jenis_mutasi'   => $request->jenis_mutasi,
                'tgl_mutasi'     => $request->tgl_mutasi,
                'alasan'         => $request->alasan,
                'sekolah_tujuan' => $request->sekolah_tujuan,
                'user_input'     => Auth::user()->name ?? 'System',
            ]);

            return redirect()->route('mutasi.keluar.index')->with('success', 'Catatan mutasi berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan Mutasi (Kembalikan ke Aktif)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $riwayat = RiwayatMutasiKeluar::findOrFail($id);
            $siswa = Siswa::findOrFail($riwayat->id_siswa);

            $siswa->update(['status' => 'aktif']);
            
            $riwayat->delete();

            DB::commit();
            return back()->with('success', 'Mutasi dibatalkan. Siswa kembali aktif (namun belum memiliki kelas). Silakan atur kelasnya di menu Anggota Kelas.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan mutasi: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Surat Mutasi Keluar (PDF)
     */
    public function cetakPdf(Request $request, $id)
    {
        $request->validate([
            'tanggal_ttd' => 'required|date'
        ]);

        // Ambil data mutasi beserta relasinya
        $mutasi = RiwayatMutasiKeluar::with(['siswa', 'kelasTerakhir'])->findOrFail($id);
        
        // Ambil profil/info sekolah (Sesuaikan dengan nama Model pengaturan sekolah Anda)
        $infoSekolah = \App\Models\InfoSekolah::first(); 
        
        // Tanggal TTD dari inputan Modal
        $tanggal_ttd = $request->tanggal_ttd;

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mutasi.pdf_mutasi_keluar', compact('mutasi', 'infoSekolah', 'tanggal_ttd'))
                ->setPaper('a4', 'portrait');

        // Buka di tab baru (stream)
        return $pdf->stream('Surat_Mutasi_' . ($mutasi->siswa->nama_siswa ?? 'Siswa') . '.pdf');
    }
}