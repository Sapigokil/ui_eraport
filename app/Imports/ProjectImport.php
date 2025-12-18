<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Project;
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
    
    protected $semesterMap = [
        'GANJIL' => 1,
        'GENAP' => 2,
    ];

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        
        // Cache Siswa: Normalisasi Nama (Hapus spasi ganda, trim, dan Uppercase)
        $this->siswaCache = Siswa::where('id_kelas', $filters['id_kelas'])
            ->get()
            ->keyBy(function ($siswa) {
                return $this->normalizeName($siswa->nama_siswa);
            });
    }

    /**
     * Helper untuk membersihkan string nama agar pencocokan identik
     */
    private function normalizeName($name)
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $name)));
    }

    public function onUnknownSheet($sheetName)
    {
        // Abaikan sheet tidak dikenal
    }
    
    public function collection(Collection $rows)
    {
        $filterData = $this->filters;
        $semesterInt = $this->semesterMap[strtoupper($filterData['semester'])] ?? null;

        if (is_null($semesterInt)) {
            throw new \Exception("Semester tidak valid. Pastikan memilih Ganjil atau Genap.");
        }

        foreach ($rows as $row) {
            // Pencocokan berdasarkan Nama Siswa (Sesuai kolom di Template)
            $excelNamaSiswa = $row['nama_siswa'] ?? null;
            $excelNilai = (int) ($row['nilai_project'] ?? null);
            $excelTujuan = trim($row['tujuan_pembelajaran'] ?? null);

            // 1. Validasi: Jika Nama atau Nilai kosong/tidak valid, lewati
            if (empty($excelNamaSiswa) || is_null($row['nilai_project']) || $excelNilai < 0) {
                $this->skippedCount++;
                continue;
            }

            // 2. Normalisasi nama dari Excel dan cari di Cache
            $cleanName = $this->normalizeName($excelNamaSiswa);
            $siswaMatch = $this->siswaCache->get($cleanName);

            if (!$siswaMatch) {
                $this->skippedCount++;
                Log::warning('Siswa tidak ditemukan untuk nama: ' . $excelNamaSiswa);
                continue;
            }

            // 3. Simpan atau Update ke tabel Project
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
                    'tujuan_pembelajaran' => $excelTujuan ?: 'Diimport melalui Excel',
                ]
            );
            $this->storedCount++;
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