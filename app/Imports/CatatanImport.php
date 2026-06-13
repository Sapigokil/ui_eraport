<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Ekskul;
use App\Models\Kelas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CatatanImport implements ToCollection, WithStartRow
{
    protected $filters;
    protected $isGenapDanBawah = false;

    public function __construct(array $filters)
    {
        $this->filters = $filters;

        // Cek Semester dan Tingkat Kelas untuk menentukan apakah ini momen kenaikan kelas
        $semester = strtoupper(trim($this->filters['semester']));
        $semuaKelas = Kelas::all()->map(function($k) {
            preg_match('/^\d+/', $k->nama_kelas, $matches);
            $k->tingkat = !empty($matches) ? (int)$matches[0] : 0;
            return $k;
        });
        
        $maxTingkat = $semuaKelas->max('tingkat');
        $tingkatKelasIni = $semuaKelas->firstWhere('id_kelas', $this->filters['id_kelas'])->tingkat ?? 0;

        if ($semester === 'GENAP' && $tingkatKelasIni > 0 && $tingkatKelasIni < $maxTingkat) {
            $this->isGenapDanBawah = true;
        }
    }

    public function startRow(): int { return 7; }

    public function collection(Collection $rows)
    {
        $semesterDB = $this->mapSemesterToInt($this->filters['semester']);

        foreach ($rows as $row) {
            if (empty($row[1])) continue;

            $siswa = Siswa::where('nama_siswa', $row[1])
                          ->where('id_kelas', $this->filters['id_kelas'])
                          ->first();

            if ($siswa) {
                // Data dasar yang akan di-update/insert
                // Catatan: Karena kolom Ekskul di-comment di Template Export, 
                // logika penarikan Ekskul via Excel juga ditiadakan agar tidak bentrok.
                $dataToUpdate = [
                    'sakit' => $row[2] ?? 0,
                    'ijin' => $row[3] ?? 0,
                    'alpha' => $row[4] ?? 0,
                    'kokurikuler' => $row[5] ?? '-',
                    'catatan_wali_kelas' => $row[6] ?? '-',
                    'updated_at' => now(),
                ];

                // Proses Smart Scanner untuk Status Kenaikan (Berada di Kolom H / Index 7)
                if ($this->isGenapDanBawah) {
                    $dataToUpdate['status_kenaikan'] = 'proses'; // Nilai Default
                    
                    if (isset($row[7])) {
                        $statusExcel = trim(strtolower($row[7]));
                        
                        // Regex Smart Detection
                        if (preg_match('/\b(tinggal|tidak|tdk|gagal)\b/', $statusExcel)) {
                            // Jika ada kata 'tinggal', 'tidak naik', 'tdk naik' -> Tinggal Kelas
                            $dataToUpdate['status_kenaikan'] = 'tinggal_kelas';
                        } elseif (preg_match('/\b(naik|lulus|lanjut)\b/', $statusExcel)) {
                            // Jika ada kata 'naik', 'lulus' -> Naik Kelas
                            $dataToUpdate['status_kenaikan'] = 'naik_kelas';
                        }
                    }
                }

                DB::table('catatan')->updateOrInsert(
                    [
                        'id_siswa' => $siswa->id_siswa,
                        'id_kelas' => $this->filters['id_kelas'],
                        'tahun_ajaran' => $this->filters['tahun_ajaran'],
                        'semester' => $semesterDB,
                    ],
                    $dataToUpdate
                );
            }
        }
    }

    private function mapSemesterToInt(string $semester): int
    {
        return (strtoupper($semester) === 'GANJIL') ? 1 : 2;
    }
}