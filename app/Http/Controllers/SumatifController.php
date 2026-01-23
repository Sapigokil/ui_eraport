<?php

// File: app/Http/Controllers/SumatifController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Sumatif;
use App\Models\MataPelajaran;
use App\Models\Pembelajaran;
use App\Models\Season;
use App\Exports\SumatifTemplateExport;
use App\Imports\SumatifImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NilaiAkhirController;


class SumatifController extends Controller
{
    // Method Helper (tujuanPembelajaran)
    private function DeskripsiSumatif(int $nilai): string
    {
        if ($nilai < 78) return 'Belum Berkembang';
        if ($nilai <= 85) return 'Layak';
        if ($nilai <= 92) return 'Cakap';
        return 'Mahir';
    }

    // Helper untuk mapping semester (STRING dari Request -> INT untuk DB: 1=Ganjil, 2=Genap)
    private function mapSemesterToInt(string $semester): ?int
    {
        $map = [
            'GANJIL' => 1,
            'GENAP' => 2,
        ];
        return $map[strtoupper($semester)] ?? null;
    }

    // === VALIDASI AJAX (REAL-TIME) ===
    public function checkPrerequisite(Request $request)
    {
        $id_kelas = $request->id_kelas;
        $id_mapel = $request->id_mapel;
        $semesterStr = $request->semester;
        $tahun_ajaran = $request->tahun_ajaran;
        $sumatifSekarang = (int) $request->sumatif;

        // --- LAYER 1: VALIDASI SEASON KETAT ---
        
        // 1. Ambil Season yang sedang di-set sebagai "Active"
        $activeSeason = Season::where('is_active', 1)->first();

        // 2. Cek Keberadaan Season Aktif
        if (!$activeSeason) {
            return response()->json([
                'status' => 'locked_season',
                'message' => '<strong>AKSES DITUTUP:</strong> Tidak ada Season yang diset aktif oleh Administrator.',
                'season' => null
            ]);
        }

        // Siapkan data detail season untuk Info Box di frontend
        $seasonData = [
            'semester' => $activeSeason->semester == 1 ? 'Ganjil' : 'Genap',
            'tahun' => $activeSeason->tahun_ajaran,
            'status' => $activeSeason->is_open ? 'Terbuka' : 'Tertutup',
            'is_open' => (bool)$activeSeason->is_open,
            'start' => date('d/m/Y', strtotime($activeSeason->start_date)),
            'end' => date('d/m/Y', strtotime($activeSeason->end_date))
        ];

        // 3. Cek Status OPEN (Switch Manual Admin)
        if ($activeSeason->is_open == 0) {
            return response()->json([
                'status' => 'locked_season',
                'message' => '<strong>AKSES DITUTUP SEMENTARA:</strong> Input nilai sedang dinonaktifkan oleh Administrator.',
                'season' => $seasonData
            ]);
        }

        // 4. Cek Rentang Tanggal (Start - End)
        $today = now()->format('Y-m-d');
        if ($today < $activeSeason->start_date) {
            return response()->json([
                'status' => 'locked_season',
                'message' => "<strong>BELUM DIMULAI:</strong> Periode input nilai baru akan dibuka pada tanggal <strong>" . $seasonData['start'] . "</strong>.",
                'season' => $seasonData
            ]);
        }
        if ($today > $activeSeason->end_date) {
            return response()->json([
                'status' => 'locked_season',
                'message' => "<strong>PERIODE BERAKHIR:</strong> Batas waktu input nilai telah berakhir pada tanggal <strong>" . $seasonData['end'] . "</strong>.",
                'season' => $seasonData
            ]);
        }

        // 5. Cek Kesesuaian Data Input dengan Season Aktif
        if ($tahun_ajaran != $activeSeason->tahun_ajaran) {
            return response()->json([
                'status' => 'locked_season',
                'message' => "<strong>TAHUN AJARAN TIDAK SESUAI:</strong> Sistem aktif untuk Tahun Ajaran <strong>{$activeSeason->tahun_ajaran}</strong>. Anda memilih <strong>{$tahun_ajaran}</strong>.",
                'season' => $seasonData
            ]);
        }

        $semesterInputInt = $this->mapSemesterToInt($semesterStr); 
        if ($semesterInputInt != $activeSeason->semester) {
            $semAktifStr = $activeSeason->semester == 1 ? 'Ganjil' : 'Genap';
            return response()->json([
                'status' => 'locked_season',
                'message' => "<strong>SEMESTER TIDAK SESUAI:</strong> Sistem aktif untuk Semester <strong>{$semAktifStr}</strong>. Anda memilih Semester <strong>{$semesterStr}</strong>.",
                'season' => $seasonData
            ]);
        }

        // --- LAYER 2: VALIDASI PRASYARAT SUMATIF (Urutan 1, 2, 3...) ---
        
        // Jika filter belum lengkap, kirim status safe tapi tetap sertakan data season
        if (!$id_kelas || !$id_mapel || !$semesterInputInt || !$tahun_ajaran) {
            return response()->json([
                'status' => 'safe',
                'season' => $seasonData
            ]);
        }

        // Cek Urutan Sumatif (Kecuali Sumatif 1)
        if ($sumatifSekarang > 1) {
            $sumatifSebelumnya = $sumatifSekarang - 1;
            
            $cekData = Sumatif::where([
                'id_kelas'     => $id_kelas,
                'id_mapel'     => $id_mapel,
                'sumatif'      => $sumatifSebelumnya,
                'semester'     => $semesterInputInt,
                'tahun_ajaran' => $tahun_ajaran,
            ])->exists();

            if (!$cekData) {
                return response()->json([
                    'status' => 'warning',
                    'message' => "<strong>PERHATIAN:</strong> Nilai <strong>Sumatif {$sumatifSebelumnya}</strong> belum ditemukan. Harap input nilai Sumatif {$sumatifSebelumnya} terlebih dahulu sebelum mengisi Sumatif {$sumatifSekarang}.",
                    'season' => $seasonData
                ]);
            }
        }

        // Jika semua lolos (Safe)
        return response()->json([
            'status' => 'safe',
            'season' => $seasonData
        ]);
    }
    
    /**
     * Method inti untuk memuat data berdasarkan filter dan tipe Sumatif.
     */
    private function loadSumatifData(Request $request, int $sumatifId): array
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $mapel = collect();

        // 🔽 TAMBAHKAN INI (DIPA)
        $seasonOpen = Season::currentOpen();

        // 1. Ambil Mata Pelajaran berdasarkan Kelas
        if ($request->id_kelas) {
            // $mapel = Pembelajaran::with('mapel')
            //     ->where('id_kelas', $request->id_kelas)
            //     ->get()
            //     ->map(fn($p) => $p->mapel)
            //     ->filter()
            //     ->values();
            $mapel = Pembelajaran::where('id_kelas', $request->id_kelas) //menonaktifkan mapel master agama
            ->whereHas('mapel', function ($q) {
                $q->where('is_active', 1);
            })
            ->with(['mapel' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->get()
            ->map(fn($p) => $p->mapel)
            ->filter()
            ->sortBy([
                ['kategori', 'asc'], // Prioritas 1
                ['urutan', 'asc'],   // Prioritas 2
            ])
            ->values();
        }

        $siswa = collect();
        $rapor = collect();

        if (
            $request->id_kelas &&
            $request->id_mapel &&
            $request->semester &&
            $request->tahun_ajaran
        ) {
            
            // 🛑 KOREKSI KRUSIAL: MAPPING SEMESTER STRING KE INTEGER 🛑
            $semesterDB = $this->mapSemesterToInt($request->semester);
            
            // Jika mapping gagal, kita anggap filter tidak valid
            if (is_null($semesterDB)) {
                return compact('kelas', 'mapel', 'siswa', 'rapor');
            }
            
            // Filter Siswa berdasarkan Kelas dan Agama Khusus (jika ada)
            $siswaQuery = Siswa::with('detail')
                ->where('id_kelas', $request->id_kelas);
            
            $selectedMapel = MataPelajaran::find($request->id_mapel);
            if ($selectedMapel && $selectedMapel->agama_khusus) {
                $siswaQuery->whereHas('detail', function ($q) use ($selectedMapel) {
                    $q->where('agama', $selectedMapel->agama_khusus);
                });
            }

            $siswa = $siswaQuery->orderBy('nama_siswa')->get();

            // === 🛑 MULAI DEBUGGING DI SINI (COPY DARI SINI) 🛑 ===
            // Uncomment baris di bawah ini untuk melihat data apa yang sedang dicari
            
            
            // dd([
            //     'DEBUG_INFO' => 'Mengecek Parameter Query ke Database',
            //     'INPUT_REQUEST' => [
            //         'id_kelas' => $request->id_kelas,
            //         'id_mapel' => $request->id_mapel,
            //         'semester_pilihan' => $request->semester,
            //         'tahun_ajaran' => $request->tahun_ajaran,
            //     ],
            //     'PARAMETER_DATABASE' => [
            //         'sumatif' => $sumatifId,      // Harus 2
            //         'semester_db' => $semesterDB, // Harus 1 (Jika Ganjil) atau 2 (Jika Genap)
            //     ],
            //     'CEK_DATA_LANGSUNG' => Sumatif::where([
            //         'id_kelas' => $request->id_kelas,
            //         'id_mapel' => $request->id_mapel,
            //         'sumatif' => $sumatifId,
            //     ])->get()->toArray() // Ini akan menampilkan semua data Sumatif 2 di mapel/kelas tersebut (tanpa filter semester/tahun)
            // ]);
            
            // === 🛑 AKHIR DEBUGGING 🛑 ===

            // Ambil data Sumatif yang sudah tersimpan
            $rapor = Sumatif::where([
                'id_kelas' => $request->id_kelas,
                'id_mapel' => $request->id_mapel,
                'sumatif' => $sumatifId, 
                'semester' => $semesterDB, // <-- MENGGUNAKAN NILAI INT (1 atau 2)
                'tahun_ajaran' => $request->tahun_ajaran,
            ])->get()->keyBy('id_siswa');
        }

        return compact('kelas', 'mapel', 'siswa', 'rapor');
    }
    
    // === METHOD UNTUK ROUTE SIDEBAR ===
    public function sumatif1(Request $request)
    {
        // Parameter Sumatif ID = 1
        $data = $this->loadSumatifData($request, 1);
        $data['sumatifId'] = 1; 
        $data['seasonOpen'] = Season::currentOpen(); // <=== TAMBAHKAN INI DIPAA
        
        // Memuat view sum1_index.blade.php
        return view('nilai.sumatif_general', $data); 
    }

    public function sumatif2(Request $request)
    {
        // Parameter Sumatif ID = 2
        $data = $this->loadSumatifData($request, 2);
        $data['sumatifId'] = 2;
        $data['seasonOpen'] = Season::currentOpen(); // <=== TAMBAHKAN INI
        return view('nilai.sumatif_general', $data); 
    }

    public function sumatif3(Request $request)
    {
        // Parameter Sumatif ID = 3
        $data = $this->loadSumatifData($request, 3);
        $data['sumatifId'] = 3;
        $data['seasonOpen'] = Season::currentOpen(); // <=== TAMBAHKAN INI
        return view('nilai.sumatif_general', $data); 
    }

    public function sumatif4(Request $request)
    {
        // Parameter Sumatif ID = 4
        $data = $this->loadSumatifData($request, 4);
        $data['sumatifId'] = 4;
        $data['seasonOpen'] = Season::currentOpen(); // <=== TAMBAHKAN INI
        return view('nilai.sumatif_general', $data); 
    }

    public function sumatif5(Request $request)
    {
        // Parameter Sumatif ID = 5
        $data = $this->loadSumatifData($request, 5);
        $data['sumatifId'] = 5;
        $data['seasonOpen'] = Season::currentOpen(); // <=== TAMBAHKAN INI
        return view('nilai.sumatif_general', $data); 
    }

    public function project(Request $request)
    {
        // Parameter Project (asumsi Project)
        $data = $this->loadSumatifData($request, 4); 
        $data['sumatifId'] = 4;
        return view('nilai.project_index', $data);
    }
    
    // === METHOD SIMPAN (STORE) - FULL VALIDASI (SEASON & PARTIAL) ===
    public function simpan(Request $request)
    {
        // ---------------------------------------------------------
        // 1. VALIDASI SEASON KETAT (BACKEND SECURITY)
        // ---------------------------------------------------------
        
        // Ambil Season yang Aktif (is_active = 1)
        $activeSeason = Season::where('is_active', 1)->first();
        
        // Cek 1: Apakah ada season aktif?
        if (!$activeSeason) {
            return back()->withErrors('Gagal menyimpan: Tidak ada Tahun Ajaran/Season yang aktif di sistem.');
        }

        // Cek 2: Apakah admin membuka gerbang input? (is_open)
        if ($activeSeason->is_open == 0) {
            return back()->withErrors('Gagal menyimpan: Input nilai sedang ditutup sementara oleh Administrator.');
        }

        // Cek 3: Validasi Tanggal (Start & End Date)
        $today = now()->format('Y-m-d');
        if ($today < $activeSeason->start_date) {
             return back()->withErrors('Gagal menyimpan: Periode input nilai belum dimulai. (Mulai: ' . date('d-m-Y', strtotime($activeSeason->start_date)) . ')');
        }
        if ($today > $activeSeason->end_date) {
             return back()->withErrors('Gagal menyimpan: Periode input nilai telah berakhir. (Selesai: ' . date('d-m-Y', strtotime($activeSeason->end_date)) . ')');
        }

        // Cek 4: Kesesuaian Data Input dengan Season Aktif
        // Pastikan semester string dari input diubah ke int dulu untuk dicocokkan
        $semesterInputInt = $this->mapSemesterToInt($request->semester);
        
        if ($request->tahun_ajaran != $activeSeason->tahun_ajaran) {
             return back()->withErrors("Gagal menyimpan: Tahun Ajaran yang Anda pilih ({$request->tahun_ajaran}) tidak sesuai dengan Season Aktif ({$activeSeason->tahun_ajaran}).");
        }
        
        if ($semesterInputInt != $activeSeason->semester) {
             $semAktifStr = $activeSeason->semester == 1 ? 'Ganjil' : 'Genap';
             return back()->withErrors("Gagal menyimpan: Semester yang Anda pilih ({$request->semester}) tidak sesuai dengan Season Aktif ({$semAktifStr}).");
        }

        // ---------------------------------------------------------
        // 2. VALIDASI INPUT FORM
        // ---------------------------------------------------------
        $request->validate([
            'id_kelas'          => 'required',
            'id_mapel'          => 'required',
            'sumatif'           => 'required|in:1,2,3,4,5',
            'semester'          => 'required',
            'tahun_ajaran'      => 'required',
            'id_siswa'          => 'required|array',
            'nilai'             => 'array',           // Boleh kosong (partial save)
            'tujuan_pembelajaran' => 'array',         // Boleh kosong (partial save)
        ]);

        $semesterDB = $this->mapSemesterToInt($request->semester);
        if (is_null($semesterDB)) {
             return back()->withInput()->with('error', 'Gagal menyimpan: Format semester tidak valid.');
        }

        // ---------------------------------------------------------
        // 3. PROSES PENYIMPANAN DATA (LOOP)
        // ---------------------------------------------------------
        $savedCount = 0; 

        foreach ($request->id_siswa as $i => $id_siswa) {

            // Ambil Input Nilai & TP
            $rawNilai = $request->nilai[$i] ?? null;
            $rawTP    = $request->tujuan_pembelajaran[$i] ?? '';

            // SKIP jika nilai kosong (Partial Save Logic)
            if ($rawNilai === null || $rawNilai === '') {
                continue; 
            }

            // --- Validasi Per Baris (Hanya yang diisi) ---

            // A. Cek Urutan Sumatif (Kecuali Sumatif 1)
            $sumatifSekarang = (int) $request->sumatif;
            if ($sumatifSekarang > 1) {
                $sumatifSebelumnya = $sumatifSekarang - 1;
                
                $cekSumatifSebelumnya = Sumatif::where([
                    'id_kelas'     => $request->id_kelas,
                    'id_siswa'     => $id_siswa,
                    'id_mapel'     => $request->id_mapel,
                    'sumatif'      => $sumatifSebelumnya,
                    'semester'     => $semesterDB,
                    'tahun_ajaran' => $request->tahun_ajaran,
                ])->exists();

                if (!$cekSumatifSebelumnya) {
                    return back()->with('error', "Gagal pada Siswa ID {$id_siswa}: Sumatif {$sumatifSekarang} tidak bisa diinput karena Sumatif {$sumatifSebelumnya} belum ada.");
                }
            }

            // B. Validasi & Sanitasi Tujuan Pembelajaran
            $tujuanPembelajaran = trim((string) $rawTP);

            if ($tujuanPembelajaran === '') {
                return back()->with('error', 'Gagal: Nilai diisi tetapi Tujuan Pembelajaran kosong pada baris ke-' . ($i + 1));
            }

            if (preg_match('/[[:punct:]]$/', $tujuanPembelajaran)) {
                return back()->withInput()->with('error', 'Gagal pada Baris ' . ($i + 1) . ': Tujuan Pembelajaran tidak boleh diakhiri tanda baca.');
            }

            // C. Eksekusi Simpan ke DB
            Sumatif::updateOrCreate(
                [
                    'id_kelas'      => $request->id_kelas,
                    'id_siswa'      => $id_siswa,
                    'id_mapel'      => $request->id_mapel,
                    'sumatif'       => $request->sumatif,
                    'semester'      => $semesterDB,
                    'tahun_ajaran'  => $request->tahun_ajaran,
                ],
                [
                    'nilai'               => (int) $rawNilai,
                    'tujuan_pembelajaran' => $tujuanPembelajaran,
                ]
            );
            
            $savedCount++;
        }

        // ---------------------------------------------------------
        // 4. UPDATE NILAI AKHIR & RETURN
        // ---------------------------------------------------------
        if ($savedCount > 0) {
            // Trigger hitung ulang Nilai Akhir otomatis
            $nilaiAkhirCtrl = app(NilaiAkhirController::class);
            $nilaiAkhirCtrl->index(new Request([
                'id_kelas'     => $request->id_kelas,
                'id_mapel'     => $request->id_mapel,
                'semester'     => $request->semester,
                'tahun_ajaran' => $request->tahun_ajaran,
            ]));
            
            return back()->with('success', "Berhasil menyimpan nilai untuk {$savedCount} siswa.");
        }

        return back()->with('warning', 'Tidak ada data yang disimpan. Pastikan Anda mengisi kolom Nilai pada setidaknya satu siswa.');
    }

    // === METHOD DOWNLOAD TEMPLATE ===
    public function downloadTemplate(Request $request)
    {
        // 1. Validasi Input Modal
        $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
            'sumatif' => 'required|in:1,2,3,4,5'
        ]);

        // 2. Ambil data Kelas, Mapel, Siswa untuk template
        $kelas = Kelas::find($request->id_kelas);
        $mapel = MataPelajaran::where('id_mapel', $request->id_mapel) //agar mapel agama master dinonaktifkan
            ->where('is_active', 1)
            ->first();

        if (!$mapel) {
            return back()->with('error', 'Mapel sudah tidak aktif atau tidak tersedia.');
        }

        // $mapel = MataPelajaran::find($request->id_mapel);
        // $siswa = Siswa::where('id_kelas', $request->id_kelas)
        //     ->orderBy('nama_siswa')
        //     ->get();

        $siswaQuery = Siswa::with('detail')
            ->where('id_kelas', $request->id_kelas);

        if ($mapel && $mapel->agama_khusus) {
            $agama = trim(strtolower($mapel->agama_khusus));

            $siswaQuery->whereHas('detail', function ($q) use ($agama) {
                $q->whereRaw('LOWER(TRIM(agama)) = ?', [$agama]);
            });
        }

        $siswa = $siswaQuery
            ->orderBy('nama_siswa')
            ->get();


        if ($siswa->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang ditemukan untuk filter ini. Template tidak dapat dibuat.');
        }

        $fileName = 'Template_Nilai_S' . $request->sumatif . '_' . $kelas->nama_kelas . '_' . $mapel->nama_mapel . '.xlsx';
        
        // 4. Panggil Class Export untuk membuat Excel
        return Excel::download(new SumatifTemplateExport(
            $request->all(),
            $siswa,
            $kelas,
            $mapel
        ), $fileName);
    }
    
    // === METHOD IMPORT NILAI (FULL SEASON VALIDATION) ===
    public function import(Request $request)
    {
        // 1. Validasi Season Ketat (Backend Security)
        $activeSeason = Season::where('is_active', 1)->first();
        if (!$activeSeason || $activeSeason->is_open == 0) {
            return back()->withErrors('Gagal Import: Season sedang ditutup.');
        }

        $today = now()->format('Y-m-d');
        if ($today < $activeSeason->start_date || $today > $activeSeason->end_date) {
             return back()->withErrors('Gagal Import: Di luar jadwal aktif.');
        }

        $semesterInputInt = $this->mapSemesterToInt($request->semester);
        if ($request->tahun_ajaran != $activeSeason->tahun_ajaran || $semesterInputInt != $activeSeason->semester) {
             return back()->withErrors('Gagal Import: Filter tidak sesuai dengan Season Aktif.');
        }

        // 2. Lanjutkan Validasi Request
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls',
            'sumatif' => 'required|in:1,2,3,4,5',
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);
        
        $filters = $request->only(['id_kelas', 'id_mapel', 'semester', 'tahun_ajaran', 'sumatif']);
        $sumatifId = $filters['sumatif'];

        try {
            $import = new SumatifImport($filters);
            Excel::import($import, $request->file('file_excel'));

            $totalStored = $import->getStoredCount();
            $totalSkipped = $import->getSkippedCount();
            
            $message = "Import selesai. Berhasil disimpan: **{$totalStored} baris**. Dilewati: **{$totalSkipped} baris**.";
            return redirect()->route('master.sumatif.s' . $sumatifId, $request->query())->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach (array_slice($failures, 0, 3) as $failure) {
                 $errors[] = "Baris " . $failure->row() . ": " . implode(", ", $failure->errors());
            }
            return back()->withInput()->with('error', 'Validasi Gagal: ' . implode(' | ', $errors));
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Import Gagal: ' . $e->getMessage());
        }
    }

    public function getMapelByKelas($id_kelas)
    {
        // Mengambil mapel yang terdaftar di kelas tersebut melalui tabel pembelajaran
        $mapel = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('pembelajaran.id_kelas', $id_kelas)
            ->where('mata_pelajaran.is_active', 1)
            ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
            ->get();

        return response()->json($mapel);
    }
}