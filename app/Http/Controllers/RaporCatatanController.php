<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RaporCatatan; // Model untuk Catatan Wali Kelas

class RaporCatatanController extends Controller
{
    // === 1. Input Catatan (Route: nilai.rapornilai.wali.catatan) ===
    public function inputCatatan(Request $request) 
    {
        $kelasList = Kelas::all();
        $siswaData = collect();
        $catatan = collect();

        $idKelas = $request->id_kelas;
        $idTahunAjaranFilter = $request->id_tahun_ajaran; 
        $semesterFilter = $request->semester;
        
        $tahunAjaranList = ["2025/2026", "2026/2027", "2027/2028", "2028/2029", "2029/2030"];
        $semesterList = ["Ganjil", "Genap"];

        if ($idKelas && $idTahunAjaranFilter && $semesterFilter) {

            $semesterDB = ($semesterFilter == 'Ganjil') ? 1 : 2; 

            $siswaData = Siswa::where('id_kelas', $idKelas)->get();

            $catatan = RaporCatatan::where('id_kelas', $idKelas)
                ->where('id_tahun_ajaran', $idTahunAjaranFilter) 
                ->where('semester', $semesterDB) 
                ->get()
                ->keyBy('id_siswa');
        }

        return view('rapor.catatan_input', [
            'kelasList'         => $kelasList,
            'siswaData'         => $siswaData,
            'catatan'           => $catatan,
            'request'           => $request,
            'tahunAjaranList'   => $tahunAjaranList, 
            'semesterList'      => $semesterList,   
        ]);
    }

    // === 2. Simpan Catatan (Route: nilai.rapornilai.wali.simpan) ===
    public function simpanCatatan(Request $request)
    {
        $request->validate([
            'id_kelas'          => 'required',
            'id_tahun_ajaran'   => 'required',
            'semester'          => 'required',
            'id_siswa'          => 'required|array',
            'catatan_wali'      => 'nullable|array', // Diubah menjadi nullable karena bisa saja hanya input absensi
            'sakit'             => 'required|array',
            'izin'              => 'required|array',
            'alpha'             => 'required|array',
        ]);
        
        $semesterDB = ($request->semester == 'Ganjil') ? 1 : 2;

        foreach ($request->id_siswa as $index => $id_siswa) {

            // Pastikan menggunakan operator null coalescing pada input yang nullable
            $catatanWali = $request->catatan_wali[$index] ?? null; 
            $sakit = $request->sakit[$index] ?? 0;
            $izin = $request->izin[$index] ?? 0;
            $alpha = $request->alpha[$index] ?? 0;

            if ($catatanWali !== null || $sakit > 0 || $izin > 0 || $alpha > 0) {
                RaporCatatan::updateOrCreate(
                    [
                        'id_siswa'          => $id_siswa,
                        'id_kelas'          => $request->id_kelas,
                        'id_tahun_ajaran'   => $request->id_tahun_ajaran,
                        'semester'          => $semesterDB,
                    ],
                    [
                        'catatan_wali' => $catatanWali,
                        'sakit'        => $sakit,
                        'izin'         => $izin,
                        'alpha'        => $alpha,
                    ]
                );
            }
        }

        // Redirect kembali ke form input dengan filter yang sama
        return redirect()->route('nilai.rapornilai.wali.catatan', [ 
            'id_kelas'        => $request->id_kelas,
            'id_tahun_ajaran' => $request->id_tahun_ajaran,
            'semester'        => $request->semester, 
        ])->with('success', 'Catatan Wali Kelas dan Absensi berhasil disimpan!');
    }

    // === 3. Get Siswa (Route: nilai.rapornilai.wali.get_siswa) - Jika diperlukan AJAX ===
    public function getSiswa($id_kelas)
    {
        // Asumsi ini endpoint AJAX
        $siswa = Siswa::where('id_kelas', $id_kelas)->select('id_siswa', 'nama')->get();
        return response()->json($siswa);
    }
}