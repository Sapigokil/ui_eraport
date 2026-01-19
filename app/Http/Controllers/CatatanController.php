<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Ekskul;
use App\Models\NilaiAkhir;
use App\Models\SetKokurikuler;
use App\Models\Season;
use App\Exports\CatatanTemplateExport; 
use App\Imports\CatatanImport;         
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\RaporController;


class CatatanController extends Controller
{

/**
     * ==============================
     * FUNCTION BANTU KATEGORI NILAI
     * ==============================
     */
    private function getKategoriNilai($nilai)
    {
        if ($nilai === null) {
            return null;
        }

        if ($nilai < 78) {
            return 'belum_berkembang';
        } elseif ($nilai >= 78 && $nilai <= 85) {
            return 'layak';
        } elseif ($nilai >= 86 && $nilai <= 92) {
            return 'cakap';
        } else {
            return 'mahir';
        }
    }

    private function mapSemesterToInt(?string $semester): ?int
    {
        if (!$semester) return null;

        $map = [
            'GANJIL' => 1,
            'GENAP' => 2,
        ];

        return $map[strtoupper(trim($semester))] ?? null;
    }

    /**
     * Tampilkan halaman input catatan
     */
    public function inputCatatan(Request $request)
    {
        $seasonOpen = \App\Models\Season::currentOpen(); // 🔒 cek season aktif

        // Ambil semua kelas untuk filter dropdown
        $kelas = Kelas::all();
        
        // Inisialisasi variabel awal agar tidak error saat view dimuat pertama kali
        $set_kokurikuler = collect(); 
        $siswa = collect();
        $rapor = null;
        $ekskul = Ekskul::all(); 
        $siswaTerpilih = null;
        $dataEkskulTersimpan = []; 
        $templateKokurikuler = '';

        // Load siswa jika kelas dipilih (untuk dropdown siswa)
        if ($request->id_kelas) {
            $siswa = Siswa::where('id_kelas', $request->id_kelas)->get();
        }

        // Eksekusi logika utama jika filter lengkap
        if ($request->id_kelas && $request->id_siswa && $request->tahun_ajaran && $request->semester) {
            // Gunakan Eager Loading 'kelas' untuk mendapatkan data 'tingkat'
            $siswaTerpilih = Siswa::with('kelas')->find($request->id_siswa);
            $kelasTerpilih = Kelas::find($request->id_kelas);
            
            if ($siswaTerpilih && $siswaTerpilih->kelas) {
                // Ambil template kokurikuler berdasarkan TINGKAT (Angka: 10, 11, 12)
                $set_kokurikuler = SetKokurikuler::where('tingkat', $siswaTerpilih->kelas->tingkat)
                                    ->where('aktif', 1)
                                    ->get();
            }

            $semesterInt = $this->mapSemesterToInt($request->semester);

            // 1. Ambil Nilai Akhir & Template Fallback (dari Config jika ada)
            $dataNilai = NilaiAkhir::where('id_siswa', $request->id_siswa)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $request->tahun_ajaran)
                ->first();
                
            $nilaiAkhir = $dataNilai ? $dataNilai->nilai_akhir : null;
            $kategori = $this->getKategoriNilai($nilaiAkhir);
            $kelasLevel = $kelasTerpilih->tingkat ?? null;

            if ($kelasLevel && $kategori) {
                $templateKokurikuler = config("catatan.template_kokurikuler.$kelasLevel.$kategori") ?? '';
            }    

            // 2. Ambil data catatan dari tabel 'catatan'
            $rapor = \DB::table('catatan')
                ->where('id_kelas', $request->id_kelas)
                ->where('id_siswa', $request->id_siswa)
                ->where('tahun_ajaran', $request->tahun_ajaran)
                ->where('semester', $semesterInt)
                ->first();

            // 3. Parsing data ekskul, predikat, dan keterangan
            if ($rapor && !empty($rapor->ekskul)) {
                $ids = explode(',', $rapor->ekskul);
                $preds = explode(',', $rapor->predikat ?? ''); 
                $kets = explode(' | ', $rapor->keterangan ?? ''); 

                foreach ($ids as $index => $id) {
                    if (!empty(trim($id))) {
                        $dataEkskulTersimpan[] = [
                            'id_ekskul' => trim($id),
                            'predikat'  => $preds[$index] ?? '',
                            'keterangan' => $kets[$index] ?? '', 
                        ];
                    }
                }
            }
        } 

        return view('nilai.catatan', compact(
            'kelas', 
            'siswa', 
            'rapor', 
            'ekskul', 
            'siswaTerpilih', 
            'dataEkskulTersimpan', 
            'templateKokurikuler',
            'set_kokurikuler',
            'seasonOpen'
        ));
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
        $seasonOpen = \App\Models\Season::currentOpen();
        if (!$seasonOpen) {
            return back()->with('error', '🔒 Input catatan dikunci karena season tidak aktif.');
        }
        $semesterInt = $this->mapSemesterToInt($request->semester);
        
        $validIds = [];
        $validPredikats = [];
        $validKets = [];

        if ($request->has('ekskul')) {
            foreach ($request->ekskul as $item) {
                if (!empty($item['id_ekskul'])) {
                    $validIds[] = $item['id_ekskul'];
                    $validPredikats[] = $item['predikat'] ?? '-';
                    $validKets[] = $item['keterangan'] ?? '-';
                }
            }
        }

        \DB::table('catatan')->updateOrInsert(
            [
                'id_siswa'     => $request->id_siswa,
                'id_kelas'     => $request->id_kelas,
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester'     => $semesterInt,
            ],
            [
                'ekskul'             => !empty($validIds) ? implode(',', $validIds) : null,
                'predikat'           => !empty($validPredikats) ? implode(',', $validPredikats) : null,
                'keterangan'         => !empty($validKets) ? implode(' | ', $validKets) : null,
                'kokurikuler'        => $request->kokurikuler,
                'sakit'              => $request->sakit ?? 0,
                'ijin'               => $request->ijin ?? 0,
                'alpha'              => $request->alpha ?? 0,
                'catatan_wali_kelas' => $request->catatan_wali_kelas,
                'updated_at'         => now(),
            ]
        );

        // Panggil mesin penghitung status rapor
        app(RaporController::class)->perbaruiStatusRapor(
            $request->id_siswa, 
            $request->semester, 
            $request->tahun_ajaran
        );

        return back()->with('success', 'Data berhasil disimpan!');
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

        return view('nilai.catatan', compact('progress', 'kelasList'));
    }

    // === METHOD DOWNLOAD TEMPLATE CATATAN ===
    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);

        $kelas = Kelas::find($request->id_kelas);
        $siswa = Siswa::where('id_kelas', $request->id_kelas)->orderBy('nama_siswa')->get();

        if ($siswa->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa ditemukan. Template tidak dapat dibuat.');
        }

        $fileName = 'Template_Catatan_Wali_' . str_replace(' ', '_', $kelas->nama_kelas) . '.xlsx';
        
        // Kirim data filter, koleksi siswa, dan objek kelas ke Export Class
        return Excel::download(new CatatanTemplateExport(
            $request->all(),
            $siswa,
            $kelas
        ), $fileName);
    }
    
    // === METHOD IMPORT CATATAN WALI KELAS ===
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls',
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);
        
        $filters = $request->only(['id_kelas', 'semester', 'tahun_ajaran']);

        try {
            // Inisialisasi Import Class dengan filter
            Excel::import(new CatatanImport($filters), $request->file('file_excel'));

            return redirect()->route('master.catatan.input', $request->query())
                             ->with('success', 'Import Catatan Wali Kelas berhasil diproses!');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Import Gagal: ' . $e->getMessage());
        }
    }
}
