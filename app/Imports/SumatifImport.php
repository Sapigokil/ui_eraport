<?php

// File: app/Imports/SumatifImport.php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Sumatif;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SumatifImport implements ToCollection, WithHeadingRow, SkipsUnknownSheets
{
    use Importable;
    
    protected $filters;
    protected $storedCount = 0;
    protected $skippedCount = 0;
    protected $siswaCache;

    // 🛑 DATA MAPPING SEMESTER 🛑
    protected $semesterMap = [
        'GANJIL' => 1,
        'GENAP' => 2,
    ];

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        
        $this->siswaCache = Siswa::where('id_kelas', $filters['id_kelas'])
            ->get()
            ->keyBy(function ($siswa) {
                return strtoupper(trim($siswa->nama_siswa));
            });
    }

    public function onUnknownSheet($sheetName)
    {
        // Abaikan sheet yang tidak terduga
    }
    
    public function collection(Collection $rows)
    {
        $dataToStore = [];
        $filterData = $this->filters;

        Log::info('Sumatif Import Started', [
            'filters' => $filterData, 
            'rows_count' => $rows->count(),
        ]);

        // 🛑 MAPPING SEMESTER 🛑
        // Kita petakan nilai semester dari string ke integer (1 atau 2)
        $semesterString = strtoupper($filterData['semester']);
        $semesterInt = $this->semesterMap[$semesterString] ?? null;

        if (is_null($semesterInt)) {
            throw new \Exception("Nilai semester ('".$filterData['semester']."') tidak valid untuk database (harus Ganjil/Genap).");
        }


        foreach ($rows as $row) {
            
            $excelNamaSiswa = trim($row['nama_siswa'] ?? null);
            $excelNilai = (int) ($row['nilai_sumatif'] ?? null);
            $excelTujuanPembelajaran = trim($row['tujuan_pembelajaran'] ?? null);
            
            // 1. Validasi Nilai & Nama Siswa Kosong
            if (empty($excelNamaSiswa) || empty($excelNilai) || $excelNilai < 0 || $excelNilai > 100) {
                $this->skippedCount++;
                continue;
            }

            // 2. Pencocokan Nama Siswa (Ignore Case & Trim)
            $upperNamaSiswa = strtoupper($excelNamaSiswa);
            $siswaMatch = $this->siswaCache->get($upperNamaSiswa);

            if (!$siswaMatch) {
                $this->skippedCount++;
                Log::warning('Import Skipped: Siswa not found in cache for current Class filter', ['name' => $excelNamaSiswa, 'upper_name' => $upperNamaSiswa]);
                continue;
            }

            // 3. Persiapan Data Store
            $dataToStore[] = [
                'id_siswa' => $siswaMatch->id_siswa,
                'id_kelas' => $filterData['id_kelas'],
                'id_mapel' => $filterData['id_mapel'],
                'sumatif' => $filterData['sumatif'],
                'tahun_ajaran' => $filterData['tahun_ajaran'],
                
                // 🛑 MENGGUNAKAN NILAI NUMERIK 🛑
                'semester' => $semesterInt, 
                'nilai' => $excelNilai,
                'tujuan_pembelajaran' => $excelTujuanPembelajaran ?: 'Diimport',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        } // End Foreach Rows

        // 4. Proses Penyimpanan (UpdateOrCreate)
        DB::beginTransaction();
        try {
            $currentStoredCount = 0;
            foreach ($dataToStore as $data) {
                 Sumatif::updateOrCreate(
                    [
                        'id_siswa' => $data['id_siswa'],
                        'id_mapel' => $data['id_mapel'],
                        'sumatif' => $data['sumatif'],
                        'semester' => $data['semester'], // Numerik (1/2)
                        'tahun_ajaran' => $data['tahun_ajaran'],
                    ],
                    [
                        'id_kelas' => $data['id_kelas'],
                        'nilai' => $data['nilai'],
                        'tujuan_pembelajaran' => $data['tujuan_pembelajaran'],
                    ]
                );
                $currentStoredCount++; 
            }
            
            $this->storedCount = $currentStoredCount;
            DB::commit();
            
            Log::info('Import Success (Transaction Committed)', ['stored' => $this->storedCount, 'skipped' => $this->skippedCount]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Database Storage Failed', ['error' => $e->getMessage()]);
            throw new \Exception("Penyimpanan data ke database gagal: " . $e->getMessage());
        }
    }
    
    public function headingRow(): int
    {
        return 6;
    }

    public function getStoredCount(): int
    {
        return $this->storedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}