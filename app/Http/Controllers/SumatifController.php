<?php

// File: app/Http/Controllers/SumatifController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Sumatif;
use App\Models\MataPelajaran;
use App\Models\Pembelajaran;
use App\Exports\SumatifTemplateExport;
use App\Imports\SumatifImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;


class SumatifController extends Controller
{
    // Method Helper (tujuanPembelajaran)
    private function tujuanPembelajaran(int $nilai): string
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

    /**
     * Method inti untuk memuat data berdasarkan filter dan tipe Sumatif.
     */
    private function loadSumatifData(Request $request, int $sumatifId): array
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $mapel = collect();

        // 1. Ambil Mata Pelajaran berdasarkan Kelas
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
        
        // Memuat view sum1_index.blade.php
        return view('nilai.sum1_index', $data); 
    }

    public function sumatif2(Request $request)
    {
        // Parameter Sumatif ID = 2
        $data = $this->loadSumatifData($request, 2);
        $data['sumatifId'] = 2;
        return view('nilai.sum2_index', $data); 
    }

    public function sumatif3(Request $request)
    {
        // Parameter Sumatif ID = 3
        $data = $this->loadSumatifData($request, 3);
        $data['sumatifId'] = 3;
        return view('nilai.sum3_index', $data); 
    }

    public function project(Request $request)
    {
        // Parameter Sumatif ID = 4 (asumsi Project)
        $data = $this->loadSumatifData($request, 4); 
        $data['sumatifId'] = 4;
        return view('nilai.project_index', $data);
    }
    
    // === METHOD SIMPAN (STORE) ===
    public function simpan(Request $request)
    {
        $request->validate([
            'id_kelas'              => 'required',
            'id_mapel'              => 'required',
            'sumatif'               => 'required|in:1,2,3',
            'semester'              => 'required',
            'tahun_ajaran'          => 'required',
            'id_siswa'              => 'required|array',
            'nilai'                 => 'required|array',
            'tujuan_pembelajaran'   => 'nullable|array',
        ]);
        
        // 🛑 MAPPING SEMESTER STRING KE INTEGER UNTUK PENYIMPANAN 🛑
        $semesterDB = $this->mapSemesterToInt($request->semester);

        if (is_null($semesterDB)) {
             return back()->withInput()->with('error', 'Gagal menyimpan: Semester tidak valid.');
        }

        foreach ($request->id_siswa as $i => $id_siswa) {

            $nilai = (int) $request->nilai[$i];
            
            $tujuanPembelajaran = $request->tujuan_pembelajaran[$i] 
                                         ? $request->tujuan_pembelajaran[$i] 
                                         : 'Belum ditentukan'; 

            Sumatif::updateOrCreate(
                [
                    'id_kelas' => $request->id_kelas,
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $request->id_mapel,
                    'sumatif' => $request->sumatif,
                    'semester' => $semesterDB, // MENGGUNAKAN INT DI SINI
                    'tahun_ajaran' => $request->tahun_ajaran,
                ],
                [
                    'nilai' => $nilai,
                    'tujuan_pembelajaran' => $tujuanPembelajaran,
                ]
            );
        }

        return back()->with('success', 'Nilai sumatif berhasil disimpan.');
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
            'sumatif' => 'required|in:1,2,3,4'
        ]);

        // 2. Ambil data Kelas, Mapel, Siswa untuk template
        $kelas = Kelas::find($request->id_kelas);
        $mapel = MataPelajaran::find($request->id_mapel);
        $siswa = Siswa::where('id_kelas', $request->id_kelas)
            ->orderBy('nama_siswa')
            ->get();
        
        // 3. Cek Agama Khusus (jika ada di Mapel, filter siswa)
        if ($mapel && $mapel->agama_khusus) {
            $siswa = $siswa->filter(function($s) use ($mapel) {
                return optional($s->detail)->agama == $mapel->agama_khusus;
            });
        }

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
    
    // === METHOD IMPORT NILAI ===
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls',
            'sumatif' => 'required|in:1,2,3',
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);
        
        $filters = $request->only(['id_kelas', 'id_mapel', 'semester', 'tahun_ajaran', 'sumatif']);
        $sumatifId = $filters['sumatif'];

        try {
            // Panggil Class Import dan kirim data filter
            $import = new SumatifImport($filters);
            
            // Lakukan import
            // Note: SumatifImport class sudah menangani mapping semester ke integer
            Excel::import($import, $request->file('file_excel'));

            // Hasil import
            $totalStored = $import->getStoredCount();
            $totalSkipped = $import->getSkippedCount();
            
            $message = "Import selesai. Berhasil disimpan: **{$totalStored} baris**. Dilewati (Nama Siswa tidak ditemukan/Nilai kosong): **{$totalSkipped} baris**.";

            return redirect()->route('master.sumatif.s' . $sumatifId, $request->query())->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Tangani error validasi
            $failures = $e->failures();
            $errors = [];
            foreach (array_slice($failures, 0, 3) as $failure) {
                 $errors[] = "Baris " . $failure->row() . ": " . implode(", ", $failure->errors());
            }
            return back()->withInput()->with('error', 'Validasi Gagal (Template atau Data Input): ' . implode(' | ', $errors));

        } catch (\Exception $e) {
            // Tangani error umum
            return back()->withInput()->with('error', 'Import Gagal: Terjadi kesalahan internal: ' . $e->getMessage());
        }
    }
}