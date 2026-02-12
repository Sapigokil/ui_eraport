<?php

namespace App\Http\Controllers;

use App\Models\SetKokurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatatanKokurikulerController extends Controller
{
    /**
     * Helper untuk mendapatkan ID Guru yang sedang login
     * Sesuaikan 'guru' dengan nama relasi di model User Anda
     */
    private function getCurrentGuruId()
    {
        // Asumsi: User memiliki relasi ke tabel guru, atau Auth::id() adalah id_guru
        // Jika struktur DB Anda berbeda, sesuaikan baris ini.
        return Auth::user()->guru->id_guru ?? Auth::user()->guru->id ?? 0; 
    }

    /**
     * Menampilkan daftar template kokurikuler
     * Logic: Tampilkan milik sendiri (id_guru = user) ATAU milik Admin (id_guru = 0)
     */
    public function index()
    {
        $id_guru = $this->getCurrentGuruId();

        $data = SetKokurikuler::where(function($query) use ($id_guru) {
                    $query->where('id_guru', $id_guru)
                          ->orWhere('id_guru', 0);
                })
                ->orderBy('created_at', 'desc')
                ->get();

        return view('nilai.catatan_kok_index', compact('data'));
    }

    /**
     * Menyimpan template baru ke database
     * Logic: Simpan dengan id_guru yang sedang login
     */
    public function store(Request $request)
    {
        $request->validate([
            'tingkat'   => 'required',
            'judul'     => 'required|string|max:150',
            'deskripsi' => 'required|string',
        ]);

        $id_guru = $this->getCurrentGuruId();

        SetKokurikuler::create([
            'tingkat'   => $request->tingkat,
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'aktif'     => $request->has('aktif') ? 1 : 0,
            'id_guru'   => $id_guru, // Simpan ID Guru asli
            'user'      => Auth::user()->name,
        ]);

        return back()->with('success', 'Template Kokurikuler berhasil ditambahkan!');
    }

    /**
     * Memperbarui data template
     * Logic: Hanya bisa update jika id_guru sesuai (milik sendiri). Admin (0) Read Only.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tingkat'   => 'required',
            'judul'     => 'required|string|max:150',
            'deskripsi' => 'required|string',
        ]);

        $kok = SetKokurikuler::findOrFail($id);
        $id_guru = $this->getCurrentGuruId();

        // 1. Proteksi Template Admin
        if ($kok->id_guru == 0) {
            return back()->with('error', 'Template bawaan Admin tidak dapat diubah (Read Only).');
        }

        // 2. Proteksi Template Guru Lain (Hanya pemilik yang bisa edit)
        if ($kok->id_guru != $id_guru) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah template ini.');
        }

        $kok->update([
            'tingkat'   => $request->tingkat,
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'aktif'     => $request->has('aktif') ? 1 : 0,
            // id_guru tidak perlu di-update agar kepemilikan tetap
            'user'      => Auth::user()->name, // Update nama pengubah terakhir
        ]);

        return back()->with('success', 'Template Kokurikuler berhasil diperbarui!');
    }

    /**
     * Menghapus template
     * Logic: Hanya bisa hapus milik sendiri.
     */
    public function destroy($id)
    {
        $kok = SetKokurikuler::findOrFail($id);
        $id_guru = $this->getCurrentGuruId();

        // Proteksi
        if ($kok->id_guru == 0) {
            return back()->with('error', 'Template bawaan Admin tidak dapat dihapus.');
        }

        if ($kok->id_guru != $id_guru) {
            return back()->with('error', 'Anda tidak berhak menghapus template ini.');
        }

        $kok->delete();

        return back()->with('success', 'Template Kokurikuler berhasil dihapus!');
    }

    /**
     * Mengubah status aktif
     * Logic: Hanya bisa ubah milik sendiri.
     */
    public function toggleStatus($id)
    {
        $kok = SetKokurikuler::findOrFail($id);
        $id_guru = $this->getCurrentGuruId();

        // Proteksi
        if ($kok->id_guru == 0) {
            return back()->with('error', 'Status Template Admin tidak dapat diubah.');
        }

        if ($kok->id_guru != $id_guru) {
            return back()->with('error', 'Akses ditolak.');
        }

        $kok->aktif = !$kok->aktif;
        $kok->save();

        return back()->with('success', 'Status berhasil diubah!');
    }
}