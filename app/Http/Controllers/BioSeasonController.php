<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BioSeason;

class BioSeasonController extends Controller
{
    public function index()
    {
        // Ambil data pertama (satu-satunya baris). Jika belum ada, kembalikan null.
        $season = BioSeason::first();
        return view('siswa.bio_season', compact('season'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Form
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after:tanggal_mulai',
        ], [
            'tanggal_mulai.required' => 'Waktu Mulai wajib diisi.',
            'tanggal_akhir.required' => 'Waktu Selesai wajib diisi.',
            'tanggal_akhir.after'    => 'ERROR: Waktu Selesai harus setelah Waktu Mulai!',
        ]);

        // 2. Cari data konfigurasi pertama
        $season = BioSeason::first();

        $data = [
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_akhir' => $request->tanggal_akhir,
            'is_active'     => $request->has('is_active') ? 1 : 0,
        ];

        if ($season) {
            // Jika sudah ada, cukup update datanya
            $season->update($data);
        } else {
            // Jika database masih kosong (baru pertama kali diset), buat baris baru
            BioSeason::create(array_merge($data, ['nama_periode' => 'Setting Utama']));
        }

        return redirect()->back()->with('success', 'Jadwal presisi berhasil disimpan!');
    }

    // =========================================================
    // METHOD BARU UNTUK MERESET/MENGHAPUS JADWAL
    // =========================================================
    public function reset()
    {
        $season = BioSeason::first();
        
        if ($season) {
            $season->delete();
            return redirect()->back()->with('success', 'Jadwal berhasil direset. Status portal kembali menjadi BELUM DIATUR.');
        }

        return redirect()->back()->with('error', 'Data jadwal tidak ditemukan.');
    }
}