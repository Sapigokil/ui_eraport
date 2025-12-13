<?php

namespace App\Http\Controllers;

use App\Models\InfoSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Validation\Rule; // Diperlukan untuk Rule::unique yang lebih rapi

class InfoSekolahController extends Controller
{
    // 1. Menampilkan data sekolah (untuk halaman edit/show)
    public function infoSekolah()
    {
        // Cari data pertama, atau buat instance kosong jika belum ada
        $infoSekolah = InfoSekolah::first() ?? new InfoSekolah();
        
        // Pastikan path view sudah benar sesuai kesepakatan: sekolah.info
        return view('sekolah.index', compact('infoSekolah'));
    }

    // 2. Menyimpan/Memperbarui data sekolah
    public function update_info_sekolah(Request $request)
    {
        // Dapatkan ID dari request (bisa null jika data baru)
        $infoSekolahId = $request->id_infosekolah; 

        // Validasi input
        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:150',
            'jenjang' => 'nullable|string|max:50',
            // Gunakan Rule::unique untuk NPSN agar lebih bersih
            'npsn' => ['required', 'string', 'max:15', 
                        Rule::unique('info_sekolah', 'npsn')->ignore($infoSekolahId, 'id_infosekolah')
                      ], 
            'nisn' => 'nullable|string|max:15',
            'jalan' => 'nullable|string|max:255',
            
            // KOLOM TAMBAHAN DARI MODEL/VIEW
            'kelurahan' => 'nullable|string|max:100', // DITAMBAHKAN
            'kecamatan' => 'nullable|string|max:100', // DITAMBAHKAN
            'kode_pos' => 'nullable|string|max:10',    // DITAMBAHKAN
            'website' => 'nullable|url|max:100',      // DITAMBAHKAN (diberi validasi url)
            // END KOLOM TAMBAHAN
            
            'kota_kab' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100', // Diberi validasi email
            'telp_fax' => 'nullable|string|max:50',
            'nama_kepsek' => 'nullable|string|max:150',
            'nip_kepsek' => 'nullable|string|max:30',
        ]);

        // Karena data sekolah seharusnya tunggal, kita cari yang pertama
        $infoSekolah = InfoSekolah::first();

        try {
            DB::beginTransaction();

            if ($infoSekolah) {
                // Update data yang sudah ada
                $infoSekolah->update($validated);
                $message = 'Data Info Sekolah berhasil diperbarui!';
            } else {
                // Buat data baru jika belum ada
                InfoSekolah::create($validated);
                $message = 'Data Info Sekolah berhasil disimpan!';
            }

            DB::commit();
            
            // Redirect menggunakan route Admin yang sudah diamankan
            return redirect()->route('admin.info_sekolah')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            // Catat error ini ke log Laravel
            \Log::error('Gagal memperbarui info sekolah: ' . $e->getMessage()); 
            
            return redirect()->back()->with('error', 'Gagal memperbarui data. Silakan coba lagi atau cek log.');
        }
    }
}