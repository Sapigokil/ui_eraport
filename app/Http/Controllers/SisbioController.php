<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Siswa;

class SisbioController extends Controller
{
    /**
     * Halaman Profil / Biodata Siswa (Read-Only)
     */
    public function index()
    {
        // Mengambil ID Siswa dari sesi user yang sedang login
        // Sesuaikan 'id_siswa' jika nama kolom foreign key di tabel users Anda berbeda
        $id_siswa = Auth::user()->id_siswa;

        if (!$id_siswa) {
            abort(403, 'Akun Anda tidak tertaut dengan data Siswa.');
        }

        // Ambil data siswa beserta relasi detail dan kelasnya
        $siswa = Siswa::with(['detail', 'kelas'])->findOrFail($id_siswa);

        return view('sismenu.bio_index', compact('siswa'));
    }
}