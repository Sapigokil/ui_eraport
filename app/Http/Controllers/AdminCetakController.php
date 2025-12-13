<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Rapor;

class AdminCetakController extends Controller
{
    // Halaman Cetak Nilai
    public function cetakNilai(Request $request)
    {
        // Ambil semua kelas
        $kelas = Kelas::all();

        // Jika kelas dipilih → ambil siswa
        $siswa = collect([]);
        if ($request->id_kelas) {
            $siswa = Siswa::where('id_kelas', $request->id_kelas)->get();
        }

        // Jika siswa dipilih → ambil nilai rapor siswa tsb
        $rapor = collect([]);
        if ($request->id_siswa) {
            $rapor = Rapor::where('id_siswa', $request->id_siswa)
                          ->with('mapel')  
                          ->get();
        }

        return view('input.cetak', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'rapor' => $rapor,
            'request' => $request
        ]);
    }
}
