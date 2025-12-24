<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\Project;
use App\Models\Pembelajaran;
use App\Exports\ProjectTemplateExport; // 🛑 ASUMSI CLASS BARU
use App\Imports\ProjectImport;         // 🛑 ASUMSI CLASS BARU
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class ProjectController extends Controller
{
    // Helper untuk menentukan Capaian Pembelajaran (Opsional, tapi dipertahankan)
    private function tujuanPembelajaran(int $nilai): string
    {
        return match (true) {
            $nilai < 78      => 'Belum Berkembang',
            $nilai <= 85     => 'Layak',
            $nilai <= 92     => 'Cakap',
            default          => 'Mahir',
        };
    }

    // Helper untuk menghitung Nilai Bobot (60%)
    private function nilaiBobotProject(int $nilai): float
    {
        return round($nilai * 0.6, 2);
    }
    
    // 🛑 NEW HELPER: MAPPING SEMESTER STRING KE INTEGER 🛑
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
            $mapel = Pembelajaran::with('mapel')
                ->where('id_kelas', $request->id_kelas)
                ->get()
                ->map(fn($p) => $p->mapel)
                ->filter()
                ->values();
        }

        $siswa = collect();
        $rapor = collect();

        if ($request->id_kelas && $request->id_mapel && $request->semester && $request->tahun_ajaran) {

            // 🛑 KOREKSI 1: Ambil dan konversi semester string ke integer untuk Query 🛑
            $semesterDB = $this->mapSemesterToInt($request->semester);

            // Jika mapping gagal, hentikan proses loading data
            if (is_null($semesterDB)) {
                return view('nilai.project_index', compact('kelas', 'mapel', 'siswa', 'rapor'))->with('error', 'Semester yang dipilih tidak valid.');
            }
            
            $selectedMapel = MataPelajaran::find($request->id_mapel);

            $siswa = Siswa::with('detail')
                ->where('id_kelas', $request->id_kelas);

            if ($selectedMapel && $selectedMapel->agama_khusus) {
                $siswa->whereHas('detail', function ($q) use ($selectedMapel) {
                    $q->where('agama', $selectedMapel->agama_khusus);
                });
            }

            $siswa = $siswa->orderBy('nama_siswa')->get();


            $rapor = Project::where([
                'id_kelas' => $request->id_kelas,
                'id_mapel' => $request->id_mapel,
                'semester' => $semesterDB, // <-- GUNAKAN NILAI INTEGER
                'tahun_ajaran' => $request->tahun_ajaran,
            ])
            ->get()
            ->keyBy('id_siswa');
        }

        // Koreksi view name jika Anda menggunakan 'nilai.project_index'
        return view('nilai.project_index', compact('kelas', 'mapel', 'siswa', 'rapor'));
    }

    public function simpan(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'id_kelas'              => 'required|exists:kelas,id_kelas',
            'id_mapel'              => 'required|exists:mata_pelajaran,id_mapel',
            'semester'              => 'required',
            'tahun_ajaran'          => 'required',
            'id_siswa'              => 'required|array',
            'nilai'                 => 'required|array',
            'tujuan_pembelajaran'   => 'nullable|array',
        ]);
        
        // 🛑 KOREKSI 2: Konversi Semester string ke integer untuk Penyimpanan 🛑
        $semesterDB = $this->mapSemesterToInt($request->semester);

        if (is_null($semesterDB)) {
             return back()->withInput()->with('error', 'Gagal menyimpan: Semester tidak valid.');
        }

        // 2. Loop dan Simpan Data
        foreach ($request->id_siswa as $i => $idSiswa) {

            $nilai = (int) $request->nilai[$i];
            $tp = $request->tujuan_pembelajaran[$i] ?? null;

            Project::updateOrCreate(
                [
                    'id_siswa'      => $idSiswa,
                    'id_mapel'      => $request->id_mapel,
                    'id_kelas'      => $request->id_kelas,
                    'semester'      => $semesterDB, // <-- GUNAKAN NILAI INTEGER
                    'tahun_ajaran'  => $request->tahun_ajaran,
                ],
                [
                    'nilai'               => $nilai,
                    'nilai_bobot'         => $this->nilaiBobotProject($nilai), // 60%
                    'tujuan_pembelajaran' => $tp,
                ]
            );
        }

        return back()->with('success', 'Nilai project berhasil disimpan!');
    }

    // === METHOD DOWNLOAD TEMPLATE PROJECT ===
    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);

        $kelas = Kelas::find($request->id_kelas);
        $mapel = MataPelajaran::find($request->id_mapel);
        
        $siswa = Siswa::where('id_kelas', $request->id_kelas)->orderBy('nama_siswa')->get();
        
        // Cek Agama Khusus
        if ($mapel && $mapel->agama_khusus) {
            $siswa = $siswa->filter(fn($s) => optional($s->detail)->agama == $mapel->agama_khusus);
        }

        if ($siswa->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang ditemukan untuk filter ini. Template tidak dapat dibuat.');
        }

        $fileName = 'Template_Project_' . $kelas->nama_kelas . '_' . $mapel->nama_mapel . '.xlsx';
        
        // 🛑 Panggil Class Export Project yang baru
        return Excel::download(new ProjectTemplateExport(
            $request->all(),
            $siswa,
            $kelas,
            $mapel
        ), $fileName);
    }
    
    // === METHOD IMPORT NILAI PROJECT ===
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls',
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);
        
        $filters = $request->only(['id_kelas', 'id_mapel', 'semester', 'tahun_ajaran']);

        try {
            // 🛑 Panggil Class Import Project yang baru
            $import = new ProjectImport($filters);
            
            // Lakukan import
            Excel::import($import, $request->file('file_excel'));

            $totalStored = $import->getStoredCount();
            $totalSkipped = $import->getSkippedCount();
            
            $message = "Import Project selesai. Berhasil disimpan: **{$totalStored} baris**. Dilewati (Nama Siswa tidak ditemukan/Nilai kosong): **{$totalSkipped} baris**.";

            // Redirect kembali ke halaman index Project
            return redirect()->route('master.project.index', $request->query())->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach (array_slice($failures, 0, 3) as $failure) {
                 $errors[] = "Baris " . $failure->row() . ": " . implode(", ", $failure->errors());
            }
            return back()->withInput()->with('error', 'Validasi Gagal (Template atau Data Input): ' . implode(' | ', $errors));

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Import Gagal: Terjadi kesalahan internal: ' . $e->getMessage());
        }
    }

    public function getMapelByKelas($id_kelas)
    {
        // Mengambil mapel yang hanya terdaftar di kelas tersebut melalui tabel pembelajaran (pivot)
        $mapel = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('pembelajaran.id_kelas', $id_kelas)
            ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
            ->get();

        return response()->json($mapel);
    }
}