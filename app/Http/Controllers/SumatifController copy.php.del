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
        return view('nilai.sum1_index', $data); 
    }

    public function sumatif2(Request $request)
    {
        // Parameter Sumatif ID = 2
        $data = $this->loadSumatifData($request, 2);
        $data['sumatifId'] = 2;
        $data['seasonOpen'] = Season::currentOpen(); // <=== TAMBAHKAN INI
        return view('nilai.sum2_index', $data); 
    }

    public function sumatif3(Request $request)
    {
        // Parameter Sumatif ID = 3
        $data = $this->loadSumatifData($request, 3);
        $data['sumatifId'] = 3;
        $data['seasonOpen'] = Season::currentOpen(); // <=== TAMBAHKAN INI
        return view('nilai.sum3_index', $data); 
    }

    public function sumatif4(Request $request)
    {
        // Parameter Sumatif ID = 4
        $data = $this->loadSumatifData($request, 4);
        $data['sumatifId'] = 4;
        $data['seasonOpen'] = Season::currentOpen(); // <=== TAMBAHKAN INI
        return view('nilai.sum4_index', $data); 
    }

    public function sumatif5(Request $request)
    {
        // Parameter Sumatif ID = 5
        $data = $this->loadSumatifData($request, 5);
        $data['sumatifId'] = 5;
        $data['seasonOpen'] = Season::currentOpen(); // <=== TAMBAHKAN INI
        return view('nilai.sum5_index', $data); 
    }

    public function project(Request $request)
    {
        // Parameter Project (asumsi Project)
        $data = $this->loadSumatifData($request, 4); 
        $data['sumatifId'] = 4;
        return view('nilai.project_index', $data);
    }
    
    // === METHOD SIMPAN (STORE) ===
    public function simpan(Request $request)
    {
        // DIPAA
        if (!Season::currentOpen()) {
        return back()->withErrors('Input nilai sedang dikunci oleh season.');
        }
        $request->validate([
            'id_kelas'              => 'required',
            'id_mapel'              => 'required',
            'sumatif'               => 'required|in:1,2,3,4,5',
            'semester'              => 'required',
            'tahun_ajaran'          => 'required',
            'id_siswa'              => 'required|array',
            'nilai'                 => 'required|array',
            'tujuan_pembelajaran'   => 'required|array',
            'tujuan_pembelajaran.*' => ['required'],

            [
                'tujuan_pembelajaran.*.required' =>
                    'Tujuan Pembelajaran wajib diisi dan tidak boleh kosong.',
                'tujuan_pembelajaran.*.regex' =>
                    'Tujuan Pembelajaran hanya boleh berisi huruf dan spasi. Tidak boleh angka atau tanda baca.',
            ]
        ]);

        foreach ($request->tujuan_pembelajaran as $tp) {
        $tp = trim($tp);

        // Tolak jika karakter TERAKHIR adalah tanda baca
        if (preg_match('/[[:punct:]]$/', $tp)) {
            return back()->withInput()->with(
                'error',
                'Tujuan Pembelajaran tidak boleh diakhiri tanda baca (.,!?:; dan sejenisnya).'
            );
        }
    }

        
        // 🛑 MAPPING SEMESTER STRING KE INTEGER UNTUK PENYIMPANAN 🛑
        $semesterDB = $this->mapSemesterToInt($request->semester);

        if (is_null($semesterDB)) {
             return back()->withInput()->with('error', 'Gagal menyimpan: Semester tidak valid.');
        }

        foreach ($request->id_siswa as $i => $id_siswa) {

        //Tambahan untuk aturan peraturan input nilai urut
        $sumatifSekarang = (int) $request->sumatif;

        // Abaikan pengecekan jika Sumatif 1
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
                return back()->with(
                    'error',
                    "Sumatif {$sumatifSekarang} tidak bisa diinput sebelum Sumatif {$sumatifSebelumnya} diisi."
                );
            }
        }

            $nilai = (int) $request->nilai[$i];
            
            $tujuanPembelajaran = trim(
                (string) ($request->tujuan_pembelajaran[$i] ?? '')
            );

            if ($tujuanPembelajaran === '') {
                return back()->with('error', 'Tujuan Pembelajaran wajib dipilih untuk setiap nilai.');
            }

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

        $nilaiAkhirCtrl = app(NilaiAkhirController::class);

        $nilaiAkhirCtrl->index(new Request([
            'id_kelas'     => $request->id_kelas,
            'id_mapel'     => $request->id_mapel,
            'semester'     => $request->semester,     // STRING (GANJIL/GENAP)
            'tahun_ajaran' => $request->tahun_ajaran,
        ]));

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
    
    // === METHOD IMPORT NILAI ===
    public function import(Request $request)
    {
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