<?php

namespace App\Exports;

use App\Models\Kelas;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Http\Controllers\LedgerController;
use Illuminate\Http\Request;

class LedgerTemplateExport implements FromView, WithEvents, WithCustomStartCell
{
    private array $data;
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->data = app(LedgerController::class)
            ->buildLedgerData($request);
    }

     public function startCell(): string
    {
        // Data tabel mulai dari baris 6
        return 'A1';
    }
 
    public function view(): View
    {
        return view('rapor.ledger_excel', $this->data);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $kelas = Kelas::find($this->request->id_kelas);

                // 🔥 INI WAJIB — geser tabel ke bawah
                $sheet->insertNewRowBefore(1, 5);

                // Header
                $sheet->setCellValue('A1', $this->data['namaSekolah']);
                $sheet->setCellValue('A2', $this->data['alamatSekolah'] ?? '');
                $sheet->getStyle('A2')->getFont()->setItalic(false);

                $sheet->setCellValue('A3', 'Kelas');
                $sheet->setCellValue('A4', 'Semester');
                $sheet->setCellValue('A5', 'Tahun Ajaran');

                $sheet->setCellValue('C3', $kelas->nama_kelas ?? '-');
                $sheet->setCellValue('C4', $this->request->semester);
                $sheet->setCellValue('C5', $this->request->tahun_ajaran);

                // Merge judul (sesuai jumlah kolom, jangan O doang)
                $sheet->mergeCells('A1:U1');
                $sheet->mergeCells('A2:U2');

                // Styling
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getFont()->setItalic(false)->setSize(10);
                $sheet->getStyle('A3:A5')->getFont()->setBold(true);
            }
        ];
    }

}
