<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\Project;
use App\Models\Pembelajaran;
use App\Models\Season;
use App\Exports\ProjectTemplateExport;
use App\Imports\ProjectImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
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
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $mapel = collect();

        if ($request->id_kelas) {
           $mapel = Pembelajaran::with(['mapel' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->where('id_kelas', $request->id_kelas)
            ->get()
            ->map(fn($p) => $p->mapel)
            ->filter()
            ->sortBy([
                ['kategori', 'asc'],
                ['urutan', 'asc'],
            ])
            ->values();
        }

        $siswa = collect();
        $rapor = collect();
        $seasonOpen = Season::currentOpen();

        if ($request->id_kelas && $request->id_mapel && $request->semester && $request->tahun_ajaran) {
            $semesterDB = $this->mapSemesterToInt($request->semester);

            if (is_null($semesterDB)) {
                return view('nilai.project_index', compact('kelas', 'mapel', 'siswa', 'rapor', 'seasonOpen'))->with('error', 'Semester tidak valid.');
            }
            
            $selectedMapel = MataPelajaran::find($request->id_mapel);

            $siswa = Siswa::with('detail')->where('id_kelas', $request->id_kelas);

            if ($selectedMapel && $selectedMapel->agama_khusus) {
                $siswa->whereHas('detail', function ($q) use ($selectedMapel) {
                    $q->where('agama', $selectedMapel->agama_khusus);
                });
            }

            $siswa = $siswa->orderBy('nama_siswa')->get();

            $rapor = Project::where([
                'id_kelas' => $request->id_kelas,
                'id_mapel' => $request->id_mapel,
                'semester' => $semesterDB,
                'tahun_ajaran' => $request->tahun_ajaran,
            ])
            ->get()
            ->keyBy('id_siswa');
        }

        return view('nilai.project_index', compact('kelas', 'mapel', 'siswa', 'rapor', 'seasonOpen'));
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

        // Siapkan data detail season untuk dikirim ke frontend
        $seasonData = [
            'semester' => $season->semester == 1 ? 'Ganjil' : 'Genap',
            'tahun' => $season->tahun_ajaran,
            'status' => 'Terbuka',
            // Format tanggal Indonesia: d-m-Y
            'start' => date('d/m/Y', strtotime($season->start_date)),
            'end' => date('d/m/Y', strtotime($season->end_date))
        ];

        // Konversi input semester user ("Ganjil"/"Genap") ke Integer (1/2)
        $inputSemesterInt = $this->mapSemesterToInt($request->semester);

        // Bandingkan Data Input dengan Data Database
        if ($inputSemesterInt != $season->semester || $request->tahun_ajaran != $season->tahun_ajaran) {
            return response()->json([
                'status' => 'locked_season',
                'message' => 'Anda sedang melihat data di luar Season aktif. Input/Import hanya diperbolehkan pada <b>Semester ' . $seasonData['semester'] . ' Tahun Ajaran ' . $seasonData['tahun'] . '</b>.',
                'season' => $seasonData
            ]);
        }

        // Cek jika tanggal hari ini di luar rentang start/end
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

        $siswa = Siswa::where('id_kelas', $request->id_kelas)->orderBy('nama_siswa')->get();
        
        if ($mapel->agama_khusus) {
            $siswa = $siswa->filter(fn($s) => optional($s->detail)->agama == $mapel->agama_khusus);
        }

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
            return redirect()->route('master.project.index', $request->query())->with('success', $message);

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
            ->get();

        return response()->json($mapel);
    }
}