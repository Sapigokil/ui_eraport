<?php
// File: app/Imports/ProjectImport.php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Project; // 🛑 PENTING: Gunakan Model Project
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectImport implements ToCollection, WithHeadingRow, SkipsUnknownSheets
{
    use Importable;
    
    protected $filters;
    protected $storedCount = 0;
    protected $skippedCount = 0;
    protected $siswaCache;
    
    // Data mapping semester (INT: 1=Ganjil, 2=Genap)
    protected $semesterMap = [
        'GANJIL' => 1,
        'GENAP' => 2,
    ];

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        
        // Cache Siswa berdasarkan Nama (metode pencocokan yang sama dengan Sumatif)
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
    $filterData = $this->filters;
    $semesterMap = ['GANJIL' => 1, 'GENAP' => 2];
    $semesterInt = $semesterMap[strtoupper($filterData['semester'])] ?? null;

    if (is_null($semesterInt)) {
        throw new \Exception("Semester tidak valid.");
    }

    foreach ($rows as $row) {
        // Ambil NISN dari kolom 'nisn' (sesuai header baris 6 ekspor Anda)
        $excelNisn = trim($row['nisn'] ?? null);
        $excelNilai = (int) ($row['nilai_project'] ?? null);
        $excelTujuan = trim($row['tujuan_pembelajaran'] ?? null);

        // 1. Validasi: Jika NISN atau Nilai kosong, lewati
        if (empty($excelNisn) || empty($excelNilai)) {
            $this->skippedCount++;
            continue;
        }

        // 2. Cari siswa berdasarkan NISN di database
        // Kita mencocokkan NISN melalui relasi 'detail'
        $siswaMatch = Siswa::whereHas('detail', function($q) use ($excelNisn) {
            $q->where('nisn', $excelNisn);
        })->where('id_kelas', $filterData['id_kelas'])->first();

        if (!$siswaMatch) {
            $this->skippedCount++;
            \Log::warning('Siswa tidak ditemukan untuk NISN: ' . $excelNisn);
            continue;
        }

        // 3. Simpan atau Update data ke tabel Project
        Project::updateOrCreate(
            [
                'id_siswa'     => $siswaMatch->id_siswa,
                'id_mapel'     => $filterData['id_mapel'],
                'semester'     => $semesterInt, 
                'tahun_ajaran' => $filterData['tahun_ajaran'],
            ],
            [
                'id_kelas'            => $filterData['id_kelas'],
                'nilai'               => $excelNilai,
                'nilai_bobot'         => round($excelNilai * 0.6, 2),
                'tujuan_pembelajaran' => $excelTujuan ?: 'Diimport',
            ]
        );
        $this->storedCount++;
    }
}
    
    // Heading Row di baris 6, sama dengan template Sumatif
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