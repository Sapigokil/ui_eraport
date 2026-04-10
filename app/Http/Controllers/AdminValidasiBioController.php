<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanBiodata;
use App\Models\Siswa;
use App\Models\DetailSiswa;

class AdminValidasiBioController extends Controller
{
    // Menampilkan Tabel Antrean (To-Do List)
    public function index(Request $request)
    {
        // 1. Ambil Parameter dari Request
        $search = $request->input('search');
        $statusFilter = $request->input('status', 'pending'); // Default menampilkan yang pending
        $perPage = $request->input('per_page', 10); // Default 10 baris

        // 2. Siapkan Query Dasar
        $query = PengajuanBiodata::with(['siswa.kelas']);

        // 3. Filter berdasarkan Status (Pending, Disetujui, Ditolak, atau Semua)
        if ($statusFilter !== 'semua') {
            $query->where('status', $statusFilter);
        }

        // 4. Filter Pencarian Text (Nama Siswa atau NISN)
        if (!empty($search)) {
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // 5. Eksekusi Query dengan Pagination Dinamis
        // Jika All (-1), get semua tanpa pagination. Jika tidak, gunakan paginate()
        if ($perPage == 'all' || $perPage == -1) {
            // Gunakan pagination dengan angka sangat besar sebagai trik "all"
            $pengajuans = $query->orderBy('created_at', 'desc')->paginate(100000); 
        } else {
            $pengajuans = $query->orderBy('created_at', 'desc')->paginate($perPage);
        }
        
        // Append parameter URL agar ketika pindah halaman (page=2), filter tidak hilang
        $pengajuans->appends($request->all());

        return view('siswa.validasi_bio_index', compact('pengajuans', 'statusFilter', 'perPage', 'search'));
    }

    // ... Method show() dan proses() tetap sama persis seperti sebelumnya ...
    public function show($id)
    {
        // PERBAIKAN: Tambahkan relasi detail dan ekskul agar halaman full detail bisa dirender
        $pengajuan = PengajuanBiodata::with(['siswa.kelas', 'siswa.detail', 'siswa.ekskul'])->findOrFail($id);
        
        if ($pengajuan->status !== 'pending') {
            return redirect()->route('master.validasi_bio.index')->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $data_perubahan = $pengajuan->data_perubahan;

        return view('siswa.validasi_bio', compact('pengajuan', 'data_perubahan'));
    }

    // ==============================================================================
    // EKSEKUSI PENYIMPANAN DATA (TERIMA / TOLAK)
    // ==============================================================================
    public function proses(Request $request, $id)
    {
        // 1. Cari data pengajuan
        $pengajuan = PengajuanBiodata::findOrFail($id);

        if ($pengajuan->status !== 'pending') {
            return redirect()->route('master.validasi_bio.index')->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        // 2. Ambil array keputusan dari Radio Button form
        // Bentuknya: ['tempat_lahir' => 'terima', 'alamat' => 'tolak', ...]
        $keputusan = $request->input('keputusan', []); 
        $data_perubahan = $pengajuan->data_perubahan;

        $keranjangUpdate = [];
        $jumlahDiterima = 0;
        $jumlahDitolak = 0;

        // 3. Looping data JSON dan bandingkan dengan keputusan Admin
        foreach ($data_perubahan as $kolom => $nilai) {
            
            // Default tolak jika admin tidak sengaja tidak memilih (meski di UI sudah dicegah)
            $status_keputusan = $keputusan[$kolom] ?? 'tolak'; 

            if ($status_keputusan === 'terima') {
                // Jika data barunya adalah '-', berarti siswa mengosongkan form tersebut.
                // Kita harus mengembalikannya menjadi null agar rapi di database.
                $nilai_final = ($nilai['baru'] === '-' || $nilai['baru'] === '') ? null : $nilai['baru'];
                
                // Masukkan ke keranjang update
                $keranjangUpdate[$kolom] = $nilai_final;
                $jumlahDiterima++;
            } else {
                $jumlahDitolak++;
            }
        }

        // 4. Eksekusi Update ke tabel 'detail_siswa'
        // Jika ada minimal 1 kolom yang diterima, lakukan update database
        if (count($keranjangUpdate) > 0) {
            DetailSiswa::where('id_siswa', $pengajuan->id_siswa)->update($keranjangUpdate);
        }

        // 5. Tentukan Status Akhir Pengajuan
        // Jika semua ditolak -> 'ditolak'
        // Jika semua/sebagian diterima -> 'disetujui'
        $status_akhir = ($jumlahDiterima > 0) ? 'disetujui' : 'ditolak';

        // 6. Update Status dan Catatan di tabel 'pengajuan_biodata'
        $pengajuan->update([
            'status'           => $status_akhir,
            'keterangan_admin' => $request->input('keterangan_admin')
        ]);

        // 7. Berikan pesan sukses yang detail ke Admin
        $pesan = "Validasi selesai! {$jumlahDiterima} data diperbarui, dan {$jumlahDitolak} data ditolak.";
        
        return redirect()->route('master.validasi_bio.index')->with('success', $pesan);
    }
}