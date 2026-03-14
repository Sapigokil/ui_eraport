<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\NilaiAkhir;
use App\Models\SetKokurikuler;
use App\Models\Season;
use App\Models\NilaiEkskul; 
use App\Exports\CatatanTemplateExport; 
use App\Imports\CatatanImport;         
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CatatanController extends Controller
{
    /**
     * Helper: Menentukan Kategori Nilai untuk Template Catatan
     */
    private function getKategoriNilai($nilai)
    {
        if ($nilai === null) return null;
        if ($nilai < 78) return 'belum_berkembang';
        if ($nilai <= 85) return 'layak';
        if ($nilai <= 92) return 'cakap';
        return 'mahir';
    }

    /**
     * Helper: Mapping Semester String ke Integer
     */
    private function mapSemesterToInt(?string $semester): ?int
    {
        if (!$semester) return null;
        $map = ['GANJIL' => 1, 'GENAP' => 2];
        return $map[strtoupper(trim($semester))] ?? null;
    }

    /**
     * ==========================================
     * 1. AJAX PREREQUISITE CHECK (Lock Season)
     * ==========================================
     */
    public function checkPrerequisite(Request $request)
    {
        $activeSeason = Season::where('is_active', 1)->first();

        if (!$activeSeason) {
            return response()->json([
                'status' => 'locked_season',
                'message' => '<strong>AKSES DITUTUP:</strong> Tidak ada Season (Tahun Ajaran) yang aktif di sistem.',
                'season' => null
            ]);
        }

        $seasonData = [
            'semester' => $activeSeason->semester == 1 ? 'Ganjil' : 'Genap',
            'tahun' => $activeSeason->tahun_ajaran,
            'status' => $activeSeason->is_open ? 'Terbuka' : 'Tertutup',
            'is_open' => (bool)$activeSeason->is_open,
            'start' => date('d/m/Y', strtotime($activeSeason->start_date)),
            'end' => date('d/m/Y', strtotime($activeSeason->end_date))
        ];

        if ($activeSeason->is_open == 0) {
            return response()->json([
                'status' => 'locked_season',
                'message' => '<strong>AKSES DITUTUP SEMENTARA:</strong> Input catatan sedang dinonaktifkan oleh Administrator.',
                'season' => $seasonData
            ]);
        }

        $today = now()->format('Y-m-d');
        if ($today < $activeSeason->start_date) {
            return response()->json([
                'status' => 'locked_season',
                'message' => "<strong>BELUM DIMULAI:</strong> Periode input catatan baru akan dibuka pada tanggal <strong>{$seasonData['start']}</strong>.",
                'season' => $seasonData
            ]);
        }
        if ($today > $activeSeason->end_date) {
            return response()->json([
                'status' => 'locked_season',
                'message' => "<strong>PERIODE BERAKHIR:</strong> Batas waktu input catatan telah berakhir pada tanggal <strong>{$seasonData['end']}</strong>.",
                'season' => $seasonData
            ]);
        }

        if ($request->tahun_ajaran != $activeSeason->tahun_ajaran) {
            return response()->json([
                'status' => 'locked_season',
                'message' => "<strong>TAHUN AJARAN TIDAK SESUAI:</strong> Sistem aktif untuk <strong>{$activeSeason->tahun_ajaran}</strong>. Anda memilih <strong>{$request->tahun_ajaran}</strong>.",
                'season' => $seasonData
            ]);
        }

        $semesterInputInt = $this->mapSemesterToInt($request->semester);
        if ($semesterInputInt != $activeSeason->semester) {
            return response()->json([
                'status' => 'locked_season',
                'message' => "<strong>SEMESTER TIDAK SESUAI:</strong> Sistem aktif untuk Semester <strong>{$seasonData['semester']}</strong>. Anda memilih <strong>{$request->semester}</strong>.",
                'season' => $seasonData
            ]);
        }

        return response()->json([
            'status' => 'safe',
            'season' => $seasonData
        ]);
    }

    /**
     * ==========================================
     * 2. HALAMAN UTAMA INPUT CATATAN
     * ==========================================
     */
    public function inputCatatan(Request $request)
    {
        // ==================================================
        // 1. IDENTIFIKASI ROLE (RBAC)
        // ==================================================
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']);

        // 2. QUERY KELAS BERDASARKAN WALI KELAS
        $queryKelas = Kelas::orderBy('nama_kelas');
        if ($isGuru) {
            // Jika Guru, cukup tampilkan kelas yang id_guru nya adalah dia sendiri
            $queryKelas->where('id_guru', $user->id_guru);
        }
        $kelas = $queryKelas->get();

        // Security Lock: Mencegah guru iseng masuk ke kelas guru lain via URL
        if ($isGuru && $request->id_kelas) {
            $cekKelasValid = Kelas::where('id_kelas', $request->id_kelas)->where('id_guru', $user->id_guru)->exists();
            if (!$cekKelasValid) {
                return redirect()->route('walikelas.catatan.input')->with('error', 'Akses Ditolak! Anda bukan Wali Kelas untuk kelas tersebut.');
            }
        }
        
        // Inisialisasi variabel view
        $set_kokurikuler = collect(); 
        $siswa = collect();
        $rapor = null;
        $siswaTerpilih = null;
        $templateKokurikuler = '';
        $dataEkskulTersimpan = []; 

        $semesterInt = $this->mapSemesterToInt($request->semester);

        // 3. Load List Siswa (Untuk Sidebar Monitoring)
        if ($request->id_kelas) {
            $siswa = Siswa::where('id_kelas', $request->id_kelas)
                ->orderBy('nama_siswa');
            
            if($request->tahun_ajaran && $semesterInt) {
                $siswa->with(['catatan' => function($q) use ($semesterInt, $request) {
                    $q->where('semester', $semesterInt)
                      ->where('tahun_ajaran', $request->tahun_ajaran);
                }]);
            }
            $siswa = $siswa->get();
        }

        // 4. Jika Siswa Dipilih -> Load Data Detail untuk Form Input
        if ($request->id_kelas && $request->id_siswa && $request->tahun_ajaran && $semesterInt) {
            
            $siswaTerpilih = Siswa::with('kelas')->find($request->id_siswa);
            $kelasTerpilih = Kelas::find($request->id_kelas);
            
            // A. Ambil Template Kokurikuler Sesuai Tingkat Kelas
            if ($siswaTerpilih && $siswaTerpilih->kelas) {
                $id_guru_login = auth()->user()->id_guru ?? -1;
                $set_kokurikuler = SetKokurikuler::where('tingkat', $siswaTerpilih->kelas->tingkat)
                    ->where('aktif', 1)
                    ->where(function($query) use ($id_guru_login) {
                        $query->where('id_guru', 0)                 
                              ->orWhere('id_guru', $id_guru_login); 
                    })
                    ->get();
            }

            // B. Logic Template Otomatis berdasarkan Rata-rata Nilai Akhir
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

            // C. Ambil Data Catatan yang sudah tersimpan (Absensi & Narasi)
            $rapor = DB::table('catatan')
                ->where('id_kelas', $request->id_kelas)
                ->where('id_siswa', $request->id_siswa)
                ->where('tahun_ajaran', $request->tahun_ajaran)
                ->where('semester', $semesterInt)
                ->first();

            // D. Ambil Data Ekskul (READ ONLY dari nilai_ekskul)
            $dataEkskulTersimpan = DB::table('nilai_ekskul')
                ->join('ekskul', 'nilai_ekskul.id_ekskul', '=', 'ekskul.id_ekskul')
                ->where('nilai_ekskul.id_siswa', $request->id_siswa)
                ->where('nilai_ekskul.semester', $semesterInt)
                ->where('nilai_ekskul.tahun_ajaran', $request->tahun_ajaran)
                ->select('ekskul.nama_ekskul', 'nilai_ekskul.predikat', 'nilai_ekskul.keterangan')
                ->get()
                ->map(function($item) {
                    return [
                        'nama_ekskul' => $item->nama_ekskul,
                        'predikat'    => $item->predikat,
                        'keterangan'  => $item->keterangan
                    ];
                })
                ->toArray();
        } 

        return view('nilai.catatan', compact(
            'kelas', 
            'siswa', 
            'rapor', 
            'siswaTerpilih', 
            'dataEkskulTersimpan',
            'templateKokurikuler', 
            'set_kokurikuler',
            'isGuru'
        ));
    }

    public function getSiswa($id_kelas)
    {
        return Siswa::where('id_kelas', $id_kelas)->orderBy('nama_siswa')->get();
    }

    /**
     * ==========================================
     * 3. SIMPAN CATATAN
     * ==========================================
     */
    public function simpanCatatan(Request $request)
    {
        if (!Season::currentOpen()) {
            return back()->with('error', '⛔ Gagal Simpan: Season Input Data Sedang Ditutup atau Tidak Sesuai.');
        }

        $semesterInt = $this->mapSemesterToInt($request->semester);
        
        DB::table('catatan')->updateOrInsert(
            [
                'id_siswa'     => $request->id_siswa,
                'id_kelas'     => $request->id_kelas,
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester'     => $semesterInt,
            ],
            [
                'kokurikuler'        => $request->kokurikuler,
                'sakit'              => $request->sakit ?? 0,
                'ijin'               => $request->ijin ?? 0,
                'alpha'              => $request->alpha ?? 0,
                'catatan_wali_kelas' => $request->catatan_wali_kelas,
                'updated_at'         => now(),
            ]
        );

        return back()->with('success', 'Data catatan dan absensi berhasil disimpan!');
    }

    /**
     * ==========================================
     * 4. MONITORING PROGRESS
     * ==========================================
     */
    public function indexProgressCatatan(Request $request)
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        
        $query = DB::table('catatan')->select([
            'id_kelas', 
            'tahun_ajaran', 
            'semester',
            DB::raw('COUNT(id_siswa) as total_siswa_dicatat')
        ])
        ->groupBy('id_kelas', 'tahun_ajaran', 'semester');
        
        if ($request->id_kelas) $query->where('id_kelas', $request->id_kelas);
        if ($request->tahun_ajaran) $query->where('tahun_ajaran', $request->tahun_ajaran);
        if ($request->semester) {
            $semInt = $this->mapSemesterToInt($request->semester);
            if ($semInt) $query->where('semester', $semInt);
        }
        
        $progressData = $query->get();

        $kelasMap = Kelas::pluck('nama_kelas', 'id_kelas');
        $siswaPerKelas = Siswa::select('id_kelas', DB::raw('COUNT(*) as total'))
                              ->groupBy('id_kelas')
                              ->get()
                              ->keyBy('id_kelas');

        $progress = $progressData->map(function ($item) use ($kelasMap, $siswaPerKelas) {
            $totalSiswa = $siswaPerKelas[$item->id_kelas]->total ?? 0;
            return [
                'nama_kelas' => $kelasMap[$item->id_kelas] ?? 'Kelas Dihapus',
                'tahun_ajaran' => $item->tahun_ajaran,
                'semester' => $item->semester == 1 ? 'Ganjil' : 'Genap',
                'dicatat' => $item->total_siswa_dicatat,
                'total_siswa' => $totalSiswa,
                'persen' => $totalSiswa > 0 ? round(($item->total_siswa_dicatat / $totalSiswa) * 100) : 0,
                'id_kelas' => $item->id_kelas, 
            ];
        });

        return view('nilai.catatan_progress', compact('progress', 'kelasList'));
    }

    /**
     * ==========================================
     * 5. DOWNLOAD TEMPLATE
     * ==========================================
     */
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
        
        return Excel::download(new CatatanTemplateExport(
            $request->all(),
            $siswa,
            $kelas
        ), $fileName);
    }
    
    /**
     * ==========================================
     * 6. IMPORT EXCEL
     * ==========================================
     */
    public function importExcel(Request $request)
    {
        if (!Season::currentOpen()) {
            return back()->with('error', '⛔ Gagal Import: Season Input Data Sedang Ditutup.');
        }

        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls',
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);
        
        $filters = $request->only(['id_kelas', 'semester', 'tahun_ajaran']);

        try {
            Excel::import(new CatatanImport($filters), $request->file('file_excel'));
            return redirect()->route('walikelas.catatan.input', $request->query())
                             ->with('success', 'Import Catatan Wali Kelas berhasil diproses!');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Import Gagal: ' . $e->getMessage());
        }
    }
}