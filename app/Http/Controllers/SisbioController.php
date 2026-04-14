<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Siswa;
use App\Models\DetailSiswa;
use App\Models\PengajuanBiodata;
use App\Models\BioSeason;
use Carbon\Carbon;

class SisbioController extends Controller
{
    // Helper Method (Private) untuk mengecek status BioSeason
    private function checkBiodataSeason()
    {
        $season = BioSeason::first();
        $isOpen = false;

        if ($season && $season->is_active) {
            $now = Carbon::now();
            $start = Carbon::parse($season->tanggal_mulai);
            $end = Carbon::parse($season->tanggal_akhir);
            
            $isOpen = $now->between($start, $end);
        }

        return [
            'season' => $season,
            'isOpen' => $isOpen
        ];
    }    

    public function index()
    {
        $id_siswa = Auth::user()->id_siswa;

        if (!$id_siswa) {
            abort(403, 'Akun Anda tidak tertaut dengan data Siswa.');
        }

        $siswa = Siswa::with(['detail', 'kelas'])->findOrFail($id_siswa);
        
        $pengajuanPending = PengajuanBiodata::where('id_siswa', $id_siswa)
                                            ->where('status', 'pending')
                                            ->first();

        $riwayatTerakhir = PengajuanBiodata::where('id_siswa', $id_siswa)
                                            ->whereIn('status', ['disetujui', 'ditolak'])
                                            ->orderBy('updated_at', 'desc')
                                            ->first();

        // Cek Season
        $seasonCheck = $this->checkBiodataSeason();
        $bioSeason = $seasonCheck['season'];
        $isBiodataOpen = $seasonCheck['isOpen'];

        return view('sismenu.bio_index', compact('siswa', 'pengajuanPending', 'riwayatTerakhir', 'bioSeason', 'isBiodataOpen'));
    }

    // Method Baru untuk menampilkan Halaman Form Lengkap
    public function edit()
    {
        // 1. Proteksi Halaman Form
        $seasonCheck = $this->checkBiodataSeason();
        if (!$seasonCheck['isOpen']) {
            return redirect()->route('sis.biodata')->with('error', 'Akses ditolak: Periode pembaruan biodata saat ini sedang ditutup.');
        }

        // Lanjut render halaman edit Anda...
        $id_siswa = Auth::user()->id_siswa;
        $siswa = Siswa::with(['detail', 'kelas'])->findOrFail($id_siswa);
        
        return view('sismenu.bio_form', compact('siswa'));
    }

    public function ajukanPerubahan(Request $request)
    {
        // 1. Proteksi Jendela Waktu
        $seasonCheck = $this->checkBiodataSeason();
        if (!$seasonCheck['isOpen']) {
            return redirect()->route('sis.biodata')->with('error', 'Aksi ditolak: Periode pembaruan biodata saat ini sedang ditutup. Data Anda tidak disimpan.');
        }    

        // 2. Proteksi Dobel Pengajuan
        $id_siswa = Auth::user()->id_siswa;
        $cekPending = PengajuanBiodata::where('id_siswa', $id_siswa)->where('status', 'pending')->first();
        if ($cekPending) {
            return redirect()->route('sis.biodata')->with('error', 'Anda masih memiliki pengajuan perubahan yang sedang menunggu persetujuan Admin.');
        }

        // 3. VALIDASI BACKEND (Wajib Isi)
        $request->validate([
            'tempat_lahir'        => 'required|string',
            'tanggal_lahir'       => 'required|date',
            'agama'               => 'required|string',
            'sekolah_asal'        => 'required|string',
            'nik'                 => 'required|string',
            'no_kk'               => 'required|string',
            'no_regis_akta_lahir' => 'required|string',
            'bb'                  => 'required|numeric',
            'tb'                  => 'required|numeric',
            'lingkar_kepala'      => 'required|numeric',
            'anak_ke_berapa'      => 'required|numeric',
            'jml_saudara_kandung' => 'required|numeric',
            'no_hp'               => 'required|string',
            'email'               => 'required|email',
            'alamat'              => 'required|string',
            'rt'                  => 'required|string',
            'rw'                  => 'required|string',
            'dusun'               => 'required|string',
            'kelurahan'           => 'required|string',
            'kecamatan'           => 'required|string',
            'kode_pos'            => 'required|string',
            'jenis_tinggal'       => 'required|string',
            'alat_transportasi'   => 'required|string',
            
            'nama_ayah'               => 'required|string',
            'nik_ayah'                => 'required|string',
            'tahun_lahir_ayah'        => 'required|numeric',
            'jenjang_pendidikan_ayah' => 'required|string',
            'pekerjaan_ayah'          => 'required|string',
            'penghasilan_ayah'        => 'required|string',
            
            'nama_ibu'               => 'required|string',
            'nik_ibu'                => 'required|string',
            'tahun_lahir_ibu'        => 'required|numeric',
            'jenjang_pendidikan_ibu' => 'required|string',
            'pekerjaan_ibu'          => 'required|string',
            'penghasilan_ibu'        => 'required|string',
        ], [
            'required' => 'Kolom :attribute wajib diisi.',
            'numeric'  => 'Kolom :attribute harus berupa angka.',
            'email'    => 'Format email tidak valid.'
        ]);

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

    // Script AJAX Penanda Sudah Dibaca
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