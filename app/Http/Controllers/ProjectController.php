<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\Project;
use App\Models\Pembelajaran;
use App\Models\Season;
use App\Models\Guru; // Tambahan Master Guru
use App\Exports\ProjectTemplateExport;
use App\Imports\ProjectImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Tambahan Auth

class ProjectController extends Controller
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

    private function nilaiBobotProject(int $nilai): float
    {
        return round($nilai * 0.6, 2);
    }
    
    private function mapSemesterToInt(string $semester): ?int
    {
        $map = [
            'GANJIL' => 1,
            'GENAP' => 2,
        ];
        return $map[strtoupper($semester)] ?? null;
    }

    public function index(Request $request)
    {
        // ==================================================
        // 1. IDENTIFIKASI ROLE & GURU FILTER (RBAC)
        // ==================================================
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']); // True jika hanya guru
        
        if ($isGuru) {
            $id_guru_filter = $user->id_guru;
        } else {
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
            $mapel = Pembelajaran::where('id_guru', $id_guru_filter)
                ->whereHas('mapel', function ($q) { $q->where('is_active', 1); })
                ->with(['mapel' => function ($q) { $q->where('is_active', 1); }])
                ->get()
                ->map(fn($p) => $p->mapel)
                ->filter()
                ->unique('id_mapel')
                ->sortBy([['kategori', 'asc'], ['urutan', 'asc']])
                ->values();

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
            // ---> ALUR ADMIN: KELAS DULU BARU MAPEL <---
            $kelas = Kelas::orderBy('nama_kelas')->get();

            if ($request->id_kelas) {
                $queryMapel = Pembelajaran::where('id_kelas', $request->id_kelas);
                
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

            if (is_null($semesterDB)) {
                return view('nilai.project_index', compact('kelas', 'mapel', 'siswa', 'rapor', 'seasonOpen', 'isGuru', 'id_guru_filter', 'guruList'))->with('error', 'Semester tidak valid.');
            }
            
            $selectedMapel = MataPelajaran::find($request->id_mapel);
            $siswaQuery = Siswa::with('detail')->where('id_kelas', $request->id_kelas);

            // === IMPLEMENTASI KAMUS ALIAS DI SINI ===
            if ($selectedMapel && $selectedMapel->agama_khusus) {
                $agamaList = $this->getAgamaAliases($selectedMapel->agama_khusus);
                
                $siswaQuery->whereHas('detail', function ($q) use ($agamaList) {
                    // DB::raw digunakan untuk mengabaikan spasi berlebih dan case-sensitive di database
                    $q->whereIn(DB::raw('LOWER(TRIM(agama))'), $agamaList);
                });
            }

            $siswa = $siswaQuery->orderBy('nama_siswa')->get();

            $rapor = Project::where([
                'id_kelas' => $request->id_kelas,
                'id_mapel' => $request->id_mapel,
                'semester' => $semesterDB,
                'tahun_ajaran' => $request->tahun_ajaran,
            ])
            ->get()
            ->keyBy('id_siswa');
        }

        return view('nilai.project_index', compact('kelas', 'mapel', 'siswa', 'rapor', 'seasonOpen', 'isGuru', 'id_guru_filter', 'guruList'));
    }

    public function checkPrerequisite(Request $request)
    {
        $season = Season::where('is_active', 1)->first();
        
        if (!$season || $season->is_open == 0) {
            return response()->json([
                'status' => 'locked_season',
                'message' => 'Input nilai <b>Project</b> dikunci. Tidak ada Season yang aktif atau sedang ditutup oleh Admin.',
                'season' => null
            ]);
        }

        $seasonData = [
            'semester' => $season->semester == 1 ? 'Ganjil' : 'Genap',
            'tahun' => $season->tahun_ajaran,
            'status' => 'Terbuka',
            'start' => date('d/m/Y', strtotime($season->start_date)),
            'end' => date('d/m/Y', strtotime($season->end_date))
        ];

        $inputSemesterInt = $this->mapSemesterToInt($request->semester);

        if ($inputSemesterInt != $season->semester || $request->tahun_ajaran != $season->tahun_ajaran) {
            return response()->json([
                'status' => 'locked_season',
                'message' => 'Anda sedang melihat data di luar Season aktif. Input/Import hanya diperbolehkan pada <b>Semester ' . $seasonData['semester'] . ' Tahun Ajaran ' . $seasonData['tahun'] . '</b>.',
                'season' => $seasonData
            ]);
        }

        $today = date('Y-m-d');
        if ($today < $season->start_date || $today > $season->end_date) {
            return response()->json([
                'status' => 'locked_season',
                'message' => 'Akses ditutup karena di luar jadwal input nilai yang telah ditentukan.',
                'season' => $seasonData
            ]);
        }

        return response()->json([
            'status' => 'safe',
            'season' => $seasonData
        ]);
    }

    public function simpan(Request $request)
    {
        if (!Season::currentOpen()) {
            return back()->withInput()->withErrors('Aksi ditolak: Season input sudah ditutup.');
        }

        $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
            'id_siswa' => 'required|array',
            'nilai' => 'required|array',
            'tujuan_pembelajaran' => 'nullable|array',
        ]);
        
        $semesterDB = $this->mapSemesterToInt($request->semester);

        if (is_null($semesterDB)) {
             return back()->withInput()->with('error', 'Gagal menyimpan: Semester tidak valid.');
        }

        foreach ($request->id_siswa as $i => $idSiswa) {
            $nilai = (int) $request->nilai[$i];
            $tp = $request->tujuan_pembelajaran[$i] ?? null;

            Project::updateOrCreate(
                [
                    'id_siswa' => $idSiswa,
                    'id_mapel' => $request->id_mapel,
                    'id_kelas' => $request->id_kelas,
                    'semester' => $semesterDB,
                    'tahun_ajaran' => $request->tahun_ajaran,
                ],
                [
                    'nilai' => $nilai,
                    'nilai_bobot' => $this->nilaiBobotProject($nilai),
                    'tujuan_pembelajaran' => $tp,
                ]
            );
        }

        return back()->with('success', 'Nilai project berhasil disimpan!');
    }

    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);

        $kelas = Kelas::find($request->id_kelas);
        $mapel = MataPelajaran::where('id_mapel', $request->id_mapel)->where('is_active', 1)->first();

        if (!$mapel) {
            return back()->with('error', 'Mapel sudah tidak aktif atau tidak tersedia.');
        }

        $siswaQuery = Siswa::with('detail')->where('id_kelas', $request->id_kelas);
        
        // === IMPLEMENTASI KAMUS ALIAS DI SINI ===
        if ($mapel && $mapel->agama_khusus) {
            $agamaList = $this->getAgamaAliases($mapel->agama_khusus);
            
            $siswaQuery->whereHas('detail', function ($q) use ($agamaList) {
                $q->whereIn(DB::raw('LOWER(TRIM(agama))'), $agamaList);
            });
        }

        $siswa = $siswaQuery->orderBy('nama_siswa')->get();

        if ($siswa->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa ditemukan.');
        }

        $fileName = 'Template_Project_' . $kelas->nama_kelas . '_' . $mapel->nama_mapel . '.xlsx';
        
        return Excel::download(new ProjectTemplateExport($request->all(), $siswa, $kelas, $mapel), $fileName);
    }
    
    public function import(Request $request)
    {
        if (!Season::currentOpen()) {
            return back()->withInput()->with('error', 'Aksi ditolak: Season input sudah ditutup.');
        }

        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls',
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);
        
        $filters = $request->only(['id_kelas', 'id_mapel', 'semester', 'tahun_ajaran']);

        try {
            $import = new ProjectImport($filters);
            Excel::import($import, $request->file('file_excel'));

            $totalStored = $import->getStoredCount();
            $totalSkipped = $import->getSkippedCount();
            
            $message = "Import selesai. Berhasil: {$totalStored}. Dilewati: {$totalSkipped}.";
            return redirect()->route('nilai.project.index', $request->query())->with('success', $message);

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Import Gagal: ' . $e->getMessage());
        }
    }

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