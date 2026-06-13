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
use App\Models\Guru; 
use App\Exports\SumatifTemplateExport;
use App\Imports\SumatifImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\NilaiAkhirController;

class SumatifController extends Controller
{
    // === METHOD HELPER BARU: KAMUS ALIAS AGAMA ===
    private function getAgamaAliases(?string $agama_khusus): array
    {
        if (!$agama_khusus) return [];

        // Bersihkan spasi dan ubah ke huruf kecil
        $agama = trim(strtolower($agama_khusus));

        // Kamus Alias: Key adalah agama dari Mapel, Valuenya adalah array ejaan yang diizinkan
        $aliases = [
            'katholik' => ['katholik', 'katolik'],
            'katolik'  => ['katholik', 'katolik'],
            'kristen'  => ['kristen', 'protestan', 'kristen protestan'],
            'konghucu' => ['konghucu', 'kong hucu', 'khonghucu'],
        ];

        // Jika ada di kamus, return array aliasnya. Jika tidak ada, return ejaan aslinya dalam bentuk array
        return $aliases[$agama] ?? [$agama];
    }

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
        $activeSeason = Season::where('is_active', 1)->first();

        if (!$activeSeason) {
            return response()->json([
                'status' => 'locked_season',
                'message' => '<strong>AKSES DITUTUP:</strong> Tidak ada Season yang diset aktif oleh Administrator.',
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
                'message' => '<strong>AKSES DITUTUP SEMENTARA:</strong> Input nilai sedang dinonaktifkan oleh Administrator.',
                'season' => $seasonData
            ]);
        }

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

        // --- LAYER 2: VALIDASI PRASYARAT SUMATIF ---
        if (!$id_kelas || !$id_mapel || !$semesterInputInt || !$tahun_ajaran) {
            return response()->json([
                'status' => 'safe',
                'season' => $seasonData
            ]);
        }

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
        // ==================================================
        // 1. IDENTIFIKASI ROLE & GURU FILTER (RBAC)
        // ==================================================
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']); // True jika hanya guru biasa
        
        if ($isGuru) {
            $id_guru_filter = $user->id_guru;
        } else {
            // Jika admin, bisa menggunakan filter id_guru dari request (untuk debug/filter advance)
            $id_guru_filter = $request->id_guru; 
        }

        $guruList = Guru::orderBy('nama_guru')->get();
        $seasonOpen = Season::currentOpen();
        
        $kelas = collect();
        $mapel = collect();

        // ==================================================
        // 2. LOGIKA CASCADING DROPDOWN BERDASARKAN ROLE
        // ==================================================
        if ($isGuru) {
            // ---> ALUR GURU: MAPEL DULU BARU KELAS <---
            
            // A. Ambil semua mapel yang DIAJAR oleh guru ini
            $mapel = Pembelajaran::where('id_guru', $id_guru_filter)
                ->whereHas('mapel', function ($q) { $q->where('is_active', 1); })
                ->with(['mapel' => function ($q) { $q->where('is_active', 1); }])
                ->get()
                ->map(fn($p) => $p->mapel)
                ->filter()
                ->unique('id_mapel')
                ->sortBy([['kategori', 'asc'], ['urutan', 'asc']])
                ->values();

            // B. Jika Mapel sudah dipilih, baru ambil kelas yang diajar guru ini di mapel tersebut
            if ($request->id_mapel) {
                $kelas = Pembelajaran::where('id_guru', $id_guru_filter)
                    ->where('id_mapel', $request->id_mapel)
                    ->with('kelas')
                    ->get()
                    ->map(fn($p) => $p->kelas)
                    ->filter()
                    ->unique('id_kelas')
                    ->sortBy('nama_kelas')
                    ->values();
            }
        } else {
            // ---> ALUR ADMIN: KELAS DULU BARU MAPEL (Normal Flow) <---
            $kelas = Kelas::orderBy('nama_kelas')->get();

            if ($request->id_kelas) {
                $queryMapel = Pembelajaran::where('id_kelas', $request->id_kelas);
                
                // Jika Admin menggunakan filter guru tambahan (debug)
                if ($id_guru_filter) {
                    $queryMapel->where('id_guru', $id_guru_filter);
                }

                $mapel = $queryMapel->whereHas('mapel', function ($q) { $q->where('is_active', 1); })
                    ->with(['mapel' => function ($q) { $q->where('is_active', 1); }])
                    ->get()
                    ->map(fn($p) => $p->mapel)
                    ->filter()
                    ->unique('id_mapel')
                    ->sortBy([['kategori', 'asc'], ['urutan', 'asc']])
                    ->values();
            }
        }

        // ==================================================
        // 3. TARIK DATA SISWA & RAPOR
        // ==================================================
        $siswa = collect();
        $rapor = collect();

        if ($request->id_kelas && $request->id_mapel && $request->semester && $request->tahun_ajaran) {
            $semesterDB = $this->mapSemesterToInt($request->semester);
            
            if (!is_null($semesterDB)) {
                $siswaQuery = Siswa::with('detail')->where('id_kelas', $request->id_kelas);
                
                $selectedMapel = MataPelajaran::find($request->id_mapel);
                
                // === IMPLEMENTASI KAMUS ALIAS DI SINI ===
                if ($selectedMapel && $selectedMapel->agama_khusus) {
                    $agamaList = $this->getAgamaAliases($selectedMapel->agama_khusus);
                    
                    $siswaQuery->whereHas('detail', function ($q) use ($agamaList) {
                        // DB::raw digunakan untuk mengabaikan spasi berlebih dan case-sensitive di database
                        $q->whereIn(DB::raw('LOWER(TRIM(agama))'), $agamaList);
                    });
                }

                $siswa = $siswaQuery->orderBy('nama_siswa')->get();

                $rapor = Sumatif::where([
                    'id_kelas' => $request->id_kelas,
                    'id_mapel' => $request->id_mapel,
                    'sumatif' => $sumatifId, 
                    'semester' => $semesterDB,
                    'tahun_ajaran' => $request->tahun_ajaran,
                ])->get()->keyBy('id_siswa');
            }
        }

        // Return Data Array 
        return compact('kelas', 'mapel', 'siswa', 'rapor', 'isGuru', 'id_guru_filter', 'guruList', 'seasonOpen');
    }
    
    // === METHOD UNTUK ROUTE SIDEBAR ===
    public function sumatif1(Request $request) {
        $data = $this->loadSumatifData($request, 1);
        $data['sumatifId'] = 1; 
        return view('nilai.sumatif_general', $data); 
    }

    public function sumatif2(Request $request) {
        $data = $this->loadSumatifData($request, 2);
        $data['sumatifId'] = 2;
        return view('nilai.sumatif_general', $data); 
    }

    public function sumatif3(Request $request) {
        $data = $this->loadSumatifData($request, 3);
        $data['sumatifId'] = 3;
        return view('nilai.sumatif_general', $data); 
    }

    public function sumatif4(Request $request) {
        $data = $this->loadSumatifData($request, 4);
        $data['sumatifId'] = 4;
        return view('nilai.sumatif_general', $data); 
    }

    public function sumatif5(Request $request) {
        $data = $this->loadSumatifData($request, 5);
        $data['sumatifId'] = 5;
        return view('nilai.sumatif_general', $data); 
    }

    public function project(Request $request) {
        $data = $this->loadSumatifData($request, 4); 
        $data['sumatifId'] = 4;
        return view('nilai.project_index', $data);
    }
    
    // === METHOD SIMPAN (STORE) - FULL VALIDASI (SEASON & PARTIAL) ===
    public function simpan(Request $request)
    {
        $activeSeason = Season::where('is_active', 1)->first();
        if (!$activeSeason) return back()->withErrors('Gagal menyimpan: Tidak ada Tahun Ajaran/Season yang aktif di sistem.');
        if ($activeSeason->is_open == 0) return back()->withErrors('Gagal menyimpan: Input nilai sedang ditutup sementara oleh Administrator.');

        $today = now()->format('Y-m-d');
        if ($today < $activeSeason->start_date) return back()->withErrors('Gagal menyimpan: Periode input nilai belum dimulai.');
        if ($today > $activeSeason->end_date) return back()->withErrors('Gagal menyimpan: Periode input nilai telah berakhir.');

        $semesterInputInt = $this->mapSemesterToInt($request->semester);
        if ($request->tahun_ajaran != $activeSeason->tahun_ajaran) return back()->withErrors("Gagal menyimpan: Tahun Ajaran tidak sesuai dengan Season Aktif.");
        if ($semesterInputInt != $activeSeason->semester) return back()->withErrors("Gagal menyimpan: Semester tidak sesuai dengan Season Aktif.");

        $request->validate([
            'id_kelas'          => 'required',
            'id_mapel'          => 'required',
            'sumatif'           => 'required|in:1,2,3,4,5',
            'semester'          => 'required',
            'tahun_ajaran'      => 'required',
            'id_siswa'          => 'required|array',
            'nilai'             => 'array',
            'tujuan_pembelajaran' => 'array', 
        ]);

        $semesterDB = $this->mapSemesterToInt($request->semester);
        if (is_null($semesterDB)) return back()->withInput()->with('error', 'Gagal menyimpan: Format semester tidak valid.');

        $savedCount = 0; 

        foreach ($request->id_siswa as $i => $id_siswa) {
            $rawNilai = $request->nilai[$i] ?? null;
            $rawTP    = $request->tujuan_pembelajaran[$i] ?? '';

            if ($rawNilai === null || $rawNilai === '') continue; 

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

                if (!$cekSumatifSebelumnya) return back()->with('error', "Gagal pada Siswa ID {$id_siswa}: Sumatif {$sumatifSekarang} tidak bisa diinput karena Sumatif {$sumatifSebelumnya} belum ada.");
            }

            $tujuanPembelajaran = trim((string) $rawTP);
            if ($tujuanPembelajaran === '') return back()->with('error', 'Gagal: Nilai diisi tetapi Tujuan Pembelajaran kosong pada baris ke-' . ($i + 1));
            if (preg_match('/[[:punct:]]$/', $tujuanPembelajaran)) return back()->withInput()->with('error', 'Gagal pada Baris ' . ($i + 1) . ': Tujuan Pembelajaran tidak boleh diakhiri tanda baca.');

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

        if ($savedCount > 0) {
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
        $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
            'sumatif' => 'required|in:1,2,3,4,5'
        ]);

        $kelas = Kelas::find($request->id_kelas);
        $mapel = MataPelajaran::where('id_mapel', $request->id_mapel)
            ->where('is_active', 1)
            ->first();

        if (!$mapel) return back()->with('error', 'Mapel sudah tidak aktif atau tidak tersedia.');

        $siswaQuery = Siswa::with('detail')->where('id_kelas', $request->id_kelas);

        // === IMPLEMENTASI KAMUS ALIAS DI SINI ===
        if ($mapel && $mapel->agama_khusus) {
            $agamaList = $this->getAgamaAliases($mapel->agama_khusus);
            
            $siswaQuery->whereHas('detail', function ($q) use ($agamaList) {
                $q->whereIn(DB::raw('LOWER(TRIM(agama))'), $agamaList);
            });
        }

        $siswa = $siswaQuery->orderBy('nama_siswa')->get();

        if ($siswa->isEmpty()) return back()->with('error', 'Tidak ada siswa yang ditemukan untuk filter ini. Template tidak dapat dibuat.');

        $fileName = 'Template_Nilai_S' . $request->sumatif . '_' . $kelas->nama_kelas . '_' . $mapel->nama_mapel . '.xlsx';
        
        return Excel::download(new SumatifTemplateExport($request->all(), $siswa, $kelas, $mapel), $fileName);
    }
    
    // === METHOD IMPORT NILAI ===
    public function import(Request $request)
    {
        $activeSeason = Season::where('is_active', 1)->first();
        if (!$activeSeason || $activeSeason->is_open == 0) return back()->withErrors('Gagal Import: Season sedang ditutup.');

        $today = now()->format('Y-m-d');
        if ($today < $activeSeason->start_date || $today > $activeSeason->end_date) return back()->withErrors('Gagal Import: Di luar jadwal aktif.');

        $semesterInputInt = $this->mapSemesterToInt($request->semester);
        if ($request->tahun_ajaran != $activeSeason->tahun_ajaran || $semesterInputInt != $activeSeason->semester) {
             return back()->withErrors('Gagal Import: Filter tidak sesuai dengan Season Aktif.');
        }

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
            return redirect()->route('nilai.sumatif.s' . $sumatifId, $request->query())->with('success', $message);

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

    // === API AJAX ADMIN (Kelas -> Mapel) ===
    public function getMapelByKelas($id_kelas)
    {
        $mapel = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('pembelajaran.id_kelas', $id_kelas)
            ->where('mata_pelajaran.is_active', 1)
            ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
            ->orderBy('mata_pelajaran.kategori', 'asc')
            ->orderBy('mata_pelajaran.urutan', 'asc')
            ->get();

        return response()->json($mapel);
    }

    // === API AJAX BARU GURU (Mapel -> Kelas) ===
    public function getKelasByMapelGuru($id_mapel, $id_guru)
    {
        $kelas = DB::table('pembelajaran')
            ->join('kelas', 'pembelajaran.id_kelas', '=', 'kelas.id_kelas')
            ->where('pembelajaran.id_mapel', $id_mapel)
            ->where('pembelajaran.id_guru', $id_guru)
            ->select('kelas.id_kelas', 'kelas.nama_kelas')
            ->orderBy('kelas.nama_kelas', 'asc')
            ->get();

        return response()->json($kelas);
    }
}