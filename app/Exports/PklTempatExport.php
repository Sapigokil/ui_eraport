<?php

namespace App\Exports;

use App\Models\PklTempat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PklTempatExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $isTemplate;

    /**
     * Konstruktor menerima parameter boolean.
     * Jika true = Unduh Template Kosong.
     * Jika false = Export Data Asli.
     */
    public function __construct($isTemplate = false)
    {
        $this->isTemplate = $isTemplate;
    }

    public function collection()
    {
        // Jika mode template, kirimkan 1 baris data contoh (dummy)
        if ($this->isTemplate) {
            return collect([
                (object)[
                    'nama_perusahaan' => 'PT. Contoh Abadi',
                    'bidang_usaha' => 'Teknologi',
                    'nama_pimpinan' => 'Budi Santoso',
                    'alamat_perusahaan' => 'Jl. Merdeka No 1',
                    'kota' => 'Salatiga',
                    'no_telp_perusahaan' => '0298-123456',
                    'email_perusahaan' => 'info@contoh.com',
                    'no_surat_mou' => '001/MOU/2024',
                    'tanggal_mou' => '2024-01-01',
                    'nama_instruktur' => 'Agus',
                    'no_telp_instruktur' => '08123456789',
                    'is_active' => '1',
                ]
            ]);
        }

        // Jika mode export biasa, ambil semua data dari database
        return PklTempat::all();
    }

    public function headings(): array
    {
        return [
            'Nama Perusahaan', 
            'Bidang Usaha', 
            'Nama Pimpinan', 
            'Alamat Lengkap', 
            'Kota', 
            'No Telp Perusahaan', 
            'Email', 
            'No Surat MOU', 
            'Tanggal MOU (YYYY-MM-DD)', 
            'Nama Instruktur', 
            'No Telp Instruktur', 
            'Status Aktif (1/0)'
        ];
    }

    public function map($row): array
    {
        if ($this->isTemplate) {
            return [
                $row->nama_perusahaan,
                $row->bidang_usaha,
                $row->nama_pimpinan,
                $row->alamat_perusahaan,
                $row->kota,
                $row->no_telp_perusahaan,
                $row->email_perusahaan,
                $row->no_surat_mou,
                $row->tanggal_mou,
                $row->nama_instruktur,
                $row->no_telp_instruktur,
                $row->is_active,
            ];
        }

        return [
            $row->nama_perusahaan,
            $row->bidang_usaha,
            $row->nama_pimpinan,
            $row->alamat_perusahaan,
            $row->kota,
            $row->no_telp_perusahaan,
            $row->email_perusahaan,
            $row->no_surat_mou,
            $row->tanggal_mou ? $row->tanggal_mou->format('Y-m-d') : '',
            $row->nama_instruktur,
            $row->no_telp_instruktur,
            $row->is_active ? '1' : '0'
        ];
    }

    /**
     * Styling otomatis agar baris pertama (Heading) menjadi tebal (Bold)
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}