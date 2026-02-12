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
        // Ambil season yang sedang aktif
        $activeSeason = Season::where('is_active', 1)->first();

        // 1. Cek Keberadaan Season
        if (!$activeSeason) {
            return response()->json([
                'status' => 'locked_season',
                'message' => '<strong>AKSES DITUTUP:</strong> Tidak ada Season (Tahun Ajaran) yang aktif di sistem.',
                'season' => null
            ]);
        }

        // Data Season untuk ditampilkan di Frontend
        $seasonData = [
            'semester' => $activeSeason->semester == 1 ? 'Ganjil' : 'Genap',
            'tahun' => $activeSeason->tahun_ajaran,
            'status' => $activeSeason->is_open ? 'Terbuka' : 'Tertutup',
            'is_open' => (bool)$activeSeason->is_open,
            'start' => date('d/m/Y', strtotime($activeSeason->start_date)),
            'end' => date('d/m/Y', strtotime($activeSeason->end_date))
        ];

        // 2. Cek Status Open Manual (Switch Admin)
        if ($activeSeason->is_open == 0) {
            return response()->json([
                'status' => 'locked_season',
                'message' => '<strong>AKSES DITUTUP SEMENTARA:</strong> Input catatan sedang dinonaktifkan oleh Administrator.',
                'season' => $seasonData
            ]);
        }

        // 3. Cek Rentang Tanggal (Start - End)
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

        // 4. Cek Kesesuaian Input User dengan Season Aktif
        // Validasi Tahun Ajaran
        if ($request->tahun_ajaran != $activeSeason->tahun_ajaran) {
            return response()->json([
                'status' => 'locked_season',
                'message' => "<strong>TAHUN AJARAN TIDAK SESUAI:</strong> Sistem aktif untuk <strong>{$activeSeason->tahun_ajaran}</strong>. Anda memilih <strong>{$request->tahun_ajaran}</strong>.",
                'season' => $seasonData
            ]);
        }

        // Validasi Semester
        $semesterInputInt = $this->mapSemesterToInt($request->semester);
        if ($semesterInputInt != $activeSeason->semester) {
            return response()->json([
                'status' => 'locked_season',
                'message' => "<strong>SEMESTER TIDAK SESUAI:</strong> Sistem aktif untuk Semester <strong>{$seasonData['semester']}</strong>. Anda memilih <strong>{$request->semester}</strong>.",
                'season' => $seasonData
            ]);
        }

        // Jika semua lolos, status Safe
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
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        // Inisialisasi variabel view
        $set_kokurikuler = collect(); 
        $siswa = collect();
        $rapor = null;
        $siswaTerpilih = null;
        $templateKokurikuler = '';
        $dataEkskulTersimpan = []; 

        $semesterInt = $this->mapSemesterToInt($request->semester);

        // 1. Load List Siswa (Untuk Sidebar Monitoring)
        if ($request->id_kelas) {
            $siswa = Siswa::where('id_kelas', $request->id_kelas)
                ->orderBy('nama_siswa');
            
            // Jika ada filter tahun/semester, load relasi untuk cek status sudah diisi/belum
            if($request->tahun_ajaran && $semesterInt) {
                $siswa->with(['catatan' => function($q) use ($semesterInt, $request) {
                    $q->where('semester', $semesterInt)
                      ->where('tahun_ajaran', $request->tahun_ajaran);
                }]);
            }
            $siswa = $siswa->get();
        }

        // 2. Jika Siswa Dipilih -> Load Data Detail untuk Form Input
        if ($request->id_kelas && $request->id_siswa && $request->tahun_ajaran && $semesterInt) {
            
            $siswaTerpilih = Siswa::with('kelas')->find($request->id_siswa);
            $kelasTerpilih = Kelas::find($request->id_kelas);
            
            // A. Ambil Template Kokurikuler Sesuai Tingkat Kelas
            if ($siswaTerpilih && $siswaTerpilih->kelas) {
                $id_guru_login = auth()->user()->guru->id_guru ?? -1;
                $set_kokurikuler = SetKokurikuler::where('tingkat', $siswaTerpilih->kelas->tingkat)
                    ->where('aktif', 1)
                    ->where(function($query) use ($id_guru_login) {
                        $query->where('id_guru', 0)                 // Ambil punya Admin (Global)
                              ->orWhere('id_guru', $id_guru_login); // Ambil punya Guru yang login
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
            // Mengambil data yang diinput oleh Guru Ekskul
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

        // Kembali ke view asli 'nilai.catatan'
        return view('nilai.catatan', compact(
            'kelas', 
            'siswa', 
            'rapor', 
            'siswaTerpilih', 
            'dataEkskulTersimpan',
            'templateKokurikuler', 
            'set_kokurikuler'
        ));
    }

    /**
     * AJAX Helper: Get Siswa (Opsional jika masih dipakai ajax select)
     */
    public function getSiswa($id_kelas)
    {
        return Siswa::where('id_kelas', $id_kelas)->orderBy('nama_siswa')->get();
    }

    /**
     * ==========================================
     * 3. SIMPAN CATATAN (MURNI ABSENSI & NARASI)
     * ==========================================
     */
    public function simpanCatatan(Request $request)
    {
        // 🔒 VALIDASI SEASON KETAT (Backend Guard)
        if (!Season::currentOpen()) {
            return back()->with('error', '⛔ Gagal Simpan: Season Input Data Sedang Ditutup atau Tidak Sesuai.');
        }

        $semesterInt = $this->mapSemesterToInt($request->semester);
        
        // Simpan ke DB (Tabel 'catatan')
        // Hanya update/insert kolom Absensi, Kokurikuler, dan Catatan Wali Kelas.
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
     * 4. MONITORING PROGRESS (Dashboard Progress)
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
     * 6. IMPORT EXCEL (SERVER-SIDE LOCK)
     * ==========================================
     */
    public function importExcel(Request $request)
    {
        // 🔒 VALIDASI SEASON SERVER-SIDE
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

            // Redirect kembali ke route yang benar (master.catatan.input)
            // Sesuai kode asli Anda di bagian view: route('walikelas.catatan.input') atau master.catatan.input
            // Pastikan nama route ini sesuai dengan web.php Anda. 
            // Defaultnya saya pakai 'master.catatan.input' mengikuti return view('nilai.catatan')
            return redirect()->route('walikelas.catatan.input', $request->query())
                             ->with('success', 'Import Catatan Wali Kelas berhasil diproses!');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Import Gagal: ' . $e->getMessage());
        }
    }
}