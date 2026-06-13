<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Models\Kelas;
use App\Models\Ekskul;

class CatatanTemplateExport implements WithTitle, ShouldAutoSize, WithEvents
{
    protected $filters;
    protected $siswa;
    protected $kelas;
    protected $startDataRow = 7;
    protected $isGenapDanBawah = false;

    public function __construct(array $filters, $siswa, $kelas)
    {
        $this->filters = $filters;
        $this->siswa = $siswa;
        $this->kelas = $kelas;

        // Cek Semester dan Tingkat Kelas
        $semester = strtoupper(trim($this->filters['semester']));
        $semuaKelas = Kelas::all()->map(function($k) {
            preg_match('/^\d+/', $k->nama_kelas, $matches);
            $k->tingkat = !empty($matches) ? (int)$matches[0] : 0;
            return $k;
        });
        
        $maxTingkat = $semuaKelas->max('tingkat');
        $tingkatKelasIni = $semuaKelas->firstWhere('id_kelas', $this->kelas->id_kelas)->tingkat ?? 0;

        if ($semester === 'GENAP' && $tingkatKelasIni > 0 && $tingkatKelasIni < $maxTingkat) {
            $this->isGenapDanBawah = true;
        }
    }

    public function title(): string
    {
        return 'Catatan Wali Kelas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // --- PENGATURAN LEBAR KOLOM ---
                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(30);  // Nama
                $sheet->getColumnDimension('C')->setWidth(8);   // S
                $sheet->getColumnDimension('D')->setWidth(8);   // I
                $sheet->getColumnDimension('E')->setWidth(8);   // A
                $sheet->getColumnDimension('F')->setWidth(40);  // Kokurikuler
                $sheet->getColumnDimension('G')->setWidth(40);  // Catatan Wali

                // =========================================================
                // 1. HEADER TEMPLATE (Info Filter)
                // =========================================================
                $sheet->setCellValue('A1', 'Kelas:');
                $sheet->setCellValue('B1', $this->kelas->nama_kelas);
                $sheet->setCellValue('A2', 'Semester:');
                $sheet->setCellValue('B2', $this->filters['semester']);
                $sheet->setCellValue('A3', 'Tahun Ajaran:');
                $sheet->setCellValue('B3', $this->filters['tahun_ajaran']);
                
                $sheet->getStyle('A1:A3')->getFont()->setBold(true);

                // =========================================================
                // 2. HEADER KOLOM DATA (Baris 6)
                // =========================================================
                $headers = [
                    'No', 'Nama Siswa', 'Sakit', 'Ijin', 'Alpha', 'Kokurikuler', 'Catatan Wali Kelas'
                ];

                if ($this->isGenapDanBawah) {
                    $headers[] = 'Status Kenaikan';
                    $sheet->getColumnDimension('H')->setWidth(20);
                }

                $sheet->fromArray($headers, null, 'A6');
                
                // Style Header Warna Biru Tegas
                $lastColumn = $this->isGenapDanBawah ? 'H' : 'G';
                $sheet->getStyle("A6:{$lastColumn}6")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle("A6:{$lastColumn}6")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("A6:{$lastColumn}6")->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FF4F81BD'); 

                // =========================================================
                // 3. TULIS DATA SISWA
                // =========================================================
                $dataSiswaArray = [];
                $i = 1;
                foreach ($this->siswa as $s) {
                    $dataSiswaArray[] = [$i++, $s->nama_siswa];
                }
                $sheet->fromArray($dataSiswaArray, null, 'A7', false);

                // =========================================================
                // 4. VALIDASI DROPDOWN KENAIKAN (Jika Diperlukan)
                // =========================================================
                if ($this->isGenapDanBawah) {
                    $lastRow = $sheet->getHighestRow();
                    $lastRow = $lastRow < 7 ? 100 : $lastRow + 50;

                    // Buat opsi dropdown di area tersembunyi
                    $opsiKenaikan = ['Naik Kelas', 'Tinggal Kelas'];
                    $this->writeDropdownSource($sheet, 'Z', $opsiKenaikan, 100);
                    $rangeKenaikan = '=$Z$100:$Z$101';

                    // Pasang validasi ke kolom H
                    for ($r = 7; $r <= $lastRow; $r++) {
                        $this->applyDropdownValidation($sheet, 'H'.$r, $rangeKenaikan);
                    }

                    $sheet->getColumnDimension('Z')->setVisible(false);
                }
            },
        ];
    }

    protected function writeDropdownSource(Worksheet $sheet, $col, array $data, $startRow)
    {
        foreach ($data as $i => $item) {
            $sheet->setCellValue($col . ($startRow + $i), $item);
        }
    }

    protected function applyDropdownValidation(Worksheet $sheet, $cell, $formula)
    {
        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1($formula);
    }
}