<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\InfoSekolah;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RaporCoverController extends Controller
{
    /**
     * Menampilkan Halaman Index Cetak Cover
     */
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        
        $siswaList = collect();
        $kelasAktif = null;

        if ($id_kelas) {
            $kelasAktif = Kelas::find($id_kelas);
            // Ambil data siswa yang aktif di kelas tersebut
            $siswaList = Siswa::where('id_kelas', $id_kelas)
                ->where('status', 'aktif')
                ->orderBy('nama_siswa', 'asc')
                ->get();
        }

        return view('rapor.index_cover', compact('kelas', 'id_kelas', 'siswaList', 'kelasAktif'));
    }

    /**
     * Cetak Cover Satuan (1 Siswa)
     */
    public function cetak_satuan($id_siswa)
    {
        // Masukkan ke dalam array collection agar bisa di-looping di 1 template yang sama
        // Tambahkan 'detail' ke dalam method with()
        $siswaList = Siswa::with(['kelas', 'detail'])->where('id_siswa', $id_siswa)->get();
        
        if ($siswaList->isEmpty()) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        return $this->generatePdfCover($siswaList, 'Cover_Rapor_' . $siswaList->first()->nama_siswa . '.pdf');
    }

    /**
     * Cetak Cover Massal (1 Kelas)
     */
    public function cetak_massal(Request $request)
    {
        $id_kelas = $request->id_kelas;
        
        if (!$id_kelas) {
            return redirect()->back()->with('error', 'Silakan pilih kelas terlebih dahulu.');
        }

        // Tambahkan 'detail' ke dalam method with()
        $siswaList = Siswa::with(['kelas', 'detail'])
            ->where('id_kelas', $id_kelas)
            ->where('status', 'aktif')
            ->orderBy('nama_siswa', 'asc')
            ->get();

        if ($siswaList->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa di kelas ini.');
        }

        $namaKelas = $siswaList->first()->kelas->nama_kelas ?? 'Kelas';
        return $this->generatePdfCover($siswaList, 'Cover_Rapor_Massal_' . $namaKelas . '.pdf');
    }

    /**
     * PRIVATE HELPER: Render & Stream PDF
     */
    private function generatePdfCover($siswaList, $filename)
    {
        $infoSekolah = InfoSekolah::first();
        if (!$infoSekolah) {
            $infoSekolah = (object) [
                'nama_sekolah' => 'SMKN 1 SALATIGA',
                'npsn' => '-', 'nss' => '-', 'jalan' => '-', 'kelurahan' => '-',
                'kecamatan' => '-', 'kota_kab' => 'Salatiga', 'provinsi' => 'Jawa Tengah',
                'website' => '-', 'email' => '-', 'nama_kepsek' => 'Nama Kepsek', 'nip_kepsek' => '-'
            ];
        }

        $pdf = Pdf::loadView('rapor.pdf_cover_template', compact('siswaList', 'infoSekolah'))
                ->setPaper('a4', 'portrait')
                ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->stream($filename);
    }
}