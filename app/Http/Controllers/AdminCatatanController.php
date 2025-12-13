<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Rapor;
use App\Models\Ekskul;
use App\Models\EkskulSiswa;
use Illuminate\Support\Facades\DB;

class AdminCatatanController extends Controller
{
    /**
     * Tampilkan halaman input catatan
     */
    public function inputCatatan(Request $request)
    {
        $kelas = Kelas::all();
        $siswa = Siswa::when($request->id_kelas, function($q) use ($request) {
            $q->where('id_kelas', $request->id_kelas);
        })->get();

        // Daftar statis tahun ajaran dan semester (Tidak diubah, tetap di controller)
        $tahunAjaranList = ["2025/2026", "2026/2027", "2027/2028", "2028/2029", "2029/2030"];
        $semesterList = ["Ganjil", "Genap"];

        // Ambil data rapor siswa jika sudah pilih filter
        $rapor = null;
        $ekskul = collect();
        $siswaTerpilih = null;
        // INISIALISASI DATA EKSKUL YANG SUDAH TERSIMPAN (Perbaikan Utama)
        $dataEkskulTersimpan = []; 

        if ($request->id_kelas && $request->id_siswa && $request->tahun_ajaran && $request->semester) {
            $siswaTerpilih = Siswa::find($request->id_siswa);

            // Ambil data catatan (rapor) utama dari DB
            $rapor = \DB::table('catatan')
                ->where('id_kelas', $request->id_kelas)
                ->where('id_siswa', $request->id_siswa)
                ->where('tahun_ajaran', $request->tahun_ajaran)
                ->where('semester', $request->semester)
                ->first();

            // LOGIKA PEMROSESAN EKSKUL YANG TERSIMPAN (Perbaikan Utama)
            if ($rapor && !empty($rapor->id_ekskul)) {
                $ids = explode(',', $rapor->id_ekskul);
                $keterangan = explode(' | ', $rapor->keterangan); // Delimiter ' | ' sesuai saat simpan

                // Mapping ke format array yang diharapkan oleh Alpine.js
                foreach ($ids as $index => $id) {
                    // Pastikan ID ada dan bukan string kosong
                    if (!empty(trim($id))) {
                        $dataEkskulTersimpan[] = [
                            'id_ekskul' => trim($id),
                            // Jika index keterangan tidak ada (misal: hanya ada ID), beri string kosong
                            'keterangan' => $keterangan[$index] ?? '', 
                        ];
                    }
                }
            }
            
            // Jika tidak ada data tersimpan, pastikan array berisi 1 baris kosong
            if (empty($dataEkskulTersimpan)) {
                 $dataEkskulTersimpan = [['id_ekskul' => '', 'keterangan' => '']];
            }


            // Ambil list ekskul yang tersedia untuk dropdown
            $ekskul = Ekskul::all();
        } else {
             // Jika belum ada filter, inisialisasi Alpine.js dengan 1 baris kosong
             $dataEkskulTersimpan = [['id_ekskul' => '', 'keterangan' => '']];
        }


        return view('input.catatan', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'request' => $request,
            'rapor' => $rapor,
            'ekskul' => $ekskul,
            'siswaTerpilih' => $siswaTerpilih,
            'tahunAjaranList' => $tahunAjaranList,
            'semesterList' => $semesterList,
            'dataEkskulTersimpanJson' => json_encode($dataEkskulTersimpan), // Kirim sebagai JSON string
        ]);
    }

    /**
     * AJAX get siswa berdasarkan kelas
     */
    public function getSiswa($id_kelas)
    {
        try {
            return Siswa::where('id_kelas', $id_kelas)->get();
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Simpan Catatan Rapor
     */
    public function simpanCatatan(Request $request)
    {
        // Ubah ekskul menjadi string
        $idEkskul = [];
        $keteranganEkskul = [];

        if ($request->has('ekskul')) {
            foreach ($request->ekskul as $e) {
                if (!empty($e['id_ekskul'])) {
                    $idEkskul[] = $e['id_ekskul'];
                    $keteranganEkskul[] = $e['keterangan'] ?? '';
                }
            }
        }

        // Simpan catatan utama
        \DB::table('catatan')->updateOrInsert(
            [
                'id_kelas' => $request->id_kelas,
                'id_siswa' => $request->id_siswa,
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester' => $request->semester,
            ],
            [
                'kokurikuler' => $request->kokurikuler,
                'id_ekskul'   => implode(',', $idEkskul),          // SIMPAN BANYAK id ekskul
                'keterangan'  => implode(' | ', $keteranganEkskul), // SIMPAN BANYAK keterangan
                'sakit' => $request->sakit,
                'ijin' => $request->ijin,
                'alpha' => $request->alpha,
                'catatan_wali_kelas' => $request->catatan_wali_kelas,
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Berhasil disimpan!');
    }

    public function indexProgressCatatan(Request $request)
    {
        // --- Data Pendukung ---
        $kelasList = Kelas::all(); 
        
        // Logika untuk menampilkan progres hanya berdasarkan Kelas, TA, dan Semester
        $query = DB::table('catatan')->select([
            'id_kelas', 
            'tahun_ajaran', 
            'semester',
            DB::raw('COUNT(id_siswa) as total_siswa_dicatat') // Menghitung baris unik siswa yang memiliki catatan
        ])
        ->groupBy('id_kelas', 'tahun_ajaran', 'semester');
        
        // --- Terapkan Filter ---
        if ($request->id_kelas) {
            $query->where('id_kelas', $request->id_kelas);
        } 
        if ($request->tahun_ajaran) {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        } 
        if ($request->semester) {
            $query->where('semester', $request->semester);
        } 
        
        $progressData = $query->get();

        // Mapping Data untuk View
        $kelasMap = Kelas::pluck('nama_kelas', 'id_kelas');
        $siswaPerKelas = Siswa::select('id_kelas', DB::raw('COUNT(*) as total'))->groupBy('id_kelas')->get()->keyBy('id_kelas');

        $progress = $progressData->map(function ($item) use ($kelasMap, $siswaPerKelas) {
            $totalSiswaDiKelas = $siswaPerKelas[$item->id_kelas]->total ?? 0;
            
            return [
                'nama_kelas' => $kelasMap[$item->id_kelas] ?? 'Kelas Dihapus',
                'tahun_ajaran' => $item->tahun_ajaran,
                'semester' => $item->semester,
                'dicatat' => $item->total_siswa_dicatat,
                'total_siswa' => $totalSiswaDiKelas,
                'persen' => $totalSiswaDiKelas > 0 ? round(($item->total_siswa_dicatat / $totalSiswaDiKelas) * 100) : 0,
                'id_kelas' => $item->id_kelas, 
            ];
        });

        return view('input.dbcatatan', compact('progress', 'kelasList'));
    }
}
