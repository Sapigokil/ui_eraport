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

    public function __construct(array $filters, $siswa, $kelas)
    {
        $this->filters = $filters;
        $this->siswa = $siswa;
        $this->kelas = $kelas;
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
                // $sheet->getColumnDimension('H')->setWidth(20);  // Ekskul 1
                // $sheet->getColumnDimension('I')->setWidth(12);  // Pred 1
                // $sheet->getColumnDimension('J')->setWidth(20);  // Ket 1
                // ... kolom K-M untuk Ekskul 2 & 3 bisa auto-size atau manual

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
                    'No', 'Nama Siswa', 'Sakit', 'Ijin', 'Alpha', 'Kokurikuler', 'Catatan Wali Kelas',
                    // 'Ekskul 1', 'Pred 1', 'Ket 1',
                    // 'Ekskul 2', 'Pred 2', 'Ket 2',
                    // 'Ekskul 3', 'Pred 3', 'Ket 3'
                ];
                $sheet->fromArray($headers, null, 'A6');
                
                // Style Header Warna Biru Tegas
                $sheet->getStyle('A6:G6')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A6:G6')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A6:G6')->getFill()
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

                // // =========================================================
                // // 4. VALIDASI & DROPDOWN
                // // =========================================================
                // $lastRow = $sheet->getHighestRow();
                // $lastRow = $lastRow < 7 ? 100 : $lastRow + 50;

                // // --- Dropdown Predikat (Sangat Baik, Baik, dll) ---
                // $predList = ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'];
                // $this->writeDropdownSource($sheet, 'Z', $predList, 100);
                // $rangePred = '=$Z$100:$Z$103';

                // // --- Dropdown Nama Ekskul ---
                // $ekskulList = Ekskul::pluck('nama_ekskul')->toArray();
                // $this->writeDropdownSource($sheet, 'AA', $ekskulList, 100);
                // $rangeEkskul = '=$AA$100:$AA$'.(100 + count($ekskulList) - 1);

                // for ($r = 7; $r <= $lastRow; $r++) {
                //     // Validasi Dropdown Ekskul (Kolom H, K, N)
                //     $this->applyDropdownValidation($sheet, 'H'.$r, $rangeEkskul);
                //     $this->applyDropdownValidation($sheet, 'K'.$r, $rangeEkskul);
                //     $this->applyDropdownValidation($sheet, 'N'.$r, $rangeEkskul);

                //     // Validasi Dropdown Predikat (Kolom I, L, O)
                //     $this->applyDropdownValidation($sheet, 'I'.$r, $rangePred);
                //     $this->applyDropdownValidation($sheet, 'L'.$r, $rangePred);
                //     $this->applyDropdownValidation($sheet, 'O'.$r, $rangePred);
                // }

                // $sheet->getColumnDimension('Z')->setVisible(false);
                // $sheet->getColumnDimension('AA')->setVisible(false);
            },
        ];
    }

    // protected function writeDropdownSource(Worksheet $sheet, $col, array $data, $startRow)
    // {
    //     foreach ($data as $i => $item) {
    //         $sheet->setCellValue($col . ($startRow + $i), $item);
    //     }
    // }

    // protected function applyDropdownValidation(Worksheet $sheet, $cell, $formula)
    // {
    //     $validation = $sheet->getCell($cell)->getDataValidation();
    //     $validation->setType(DataValidation::TYPE_LIST);
    //     $validation->setAllowBlank(false);
    //     $validation->setShowDropDown(true);
    //     $validation->setFormula1($formula);
    // }
}