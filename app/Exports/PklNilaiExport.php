<?php

namespace App\Exports;

use App\Models\PklTp;
use App\Models\PklTpIndikator;
use App\Models\PklCatatanSiswa;
use App\Models\PklNilaiSiswa;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// REVISI: ShouldAutoSize dihapus dari implements agar kita bisa mengatur lebar manual di AfterSheet
class PklNilaiExport implements FromCollection, WithHeadings, WithEvents
{
    protected $id_guru;
    protected $tahun_ajaran;
    protected $semester;
    protected $orderedIndikators;
    protected $tpSpans;

    public function __construct($id_guru, $tahun_ajaran, $semester)
    {
        $this->id_guru = $id_guru;
        $this->tahun_ajaran = $tahun_ajaran;
        $this->semester = $semester;
        
        $this->orderedIndikators = collect();
        $this->tpSpans = [];

        // 1. Ambil TP yang aktif dan susun urutannya
        $tps = PklTp::where('is_active', 1)->orderBy('no_urut', 'asc')->get();
        
        // 2. Ambil semua indikator dan kelompokkan berdasarkan ID TP
        $indikatorsGrouped = PklTpIndikator::orderBy('no_urut', 'asc')->get()->groupBy('id_pkl_tp');

        // 3. Kalkulasi letak Kolom (Mulai dari kolom ke-14 / Kolom N)
        $currentColIndex = 14; 

        foreach ($tps as $tp) {
            $inds = $indikatorsGrouped->get($tp->id, collect());
            $indCount = $inds->count();
            
            if ($indCount > 0) {
                // Catat di mana Merge Cell untuk TP ini dimulai dan diakhiri
                $this->tpSpans[] = [
                    'nama_tp' => $tp->nama_tp,
                    'start_col' => $currentColIndex,
                    'end_col' => $currentColIndex + $indCount - 1
                ];

                // Simpan indikator yang sudah berurutan untuk dipanggil datanya nanti
                foreach ($inds as $ind) {
                    $this->orderedIndikators->push($ind);
                    $currentColIndex++;
                }
            }
        }
    }

    public function collection()
    {
        $dataSiswa = DB::table('pkl_penempatan')
            ->join('pkl_gurusiswa', function($join) {
                $join->on('pkl_penempatan.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                     ->where('pkl_gurusiswa.tahun_ajaran', '=', $this->tahun_ajaran)
                     ->where('pkl_gurusiswa.semester', '=', $this->semester);
            })
            ->leftJoin('pkl_tempat', 'pkl_penempatan.id_pkltempat', '=', 'pkl_tempat.id')
            ->where('pkl_gurusiswa.id_guru', $this->id_guru)
            ->select(
                'pkl_penempatan.id as id_penempatan',
                'pkl_gurusiswa.nama_siswa',
                'pkl_gurusiswa.nama_kelas',
                'pkl_tempat.nama_perusahaan'
            )
            ->orderBy('pkl_gurusiswa.nama_kelas')
            ->orderBy('pkl_gurusiswa.nama_siswa')
            ->get();

        $rows = collect();

        foreach ($dataSiswa as $siswa) {
            $catatan = PklCatatanSiswa::where('id_penempatan', $siswa->id_penempatan)->first();
            $nilaiRaw = PklNilaiSiswa::where('id_penempatan', $siswa->id_penempatan)->get()->keyBy('id_pkl_tp');

            // Baris dasar (Tanpa kolom Status)
            $row = [
                'ID_SISTEM'            => $siswa->id_penempatan,
                'Nama Siswa'           => $siswa->nama_siswa,
                'Kelas'                => $siswa->nama_kelas,
                'Tempat PKL'           => $siswa->nama_perusahaan,
                'Program Keahlian'     => $catatan->program_keahlian ?? '',
                'Konsentrasi Keahlian' => $catatan->konsentrasi_keahlian ?? '',
                'Nama Instruktur'      => $catatan->nama_instruktur ?? '',
                'Tgl Mulai'            => $catatan->tanggal_mulai ?? '',
                'Tgl Selesai'          => $catatan->tanggal_selesai ?? '',
                'Sakit'                => $catatan->sakit ?? 0,
                'Izin'                 => $catatan->izin ?? 0,
                'Alpa'                 => $catatan->alpa ?? 0,
                'Catatan Pembimbing'   => $catatan->catatan_pembimbing ?? '',
            ];

            // Mapping Nilai per Indikator
            foreach ($this->orderedIndikators as $ind) {
                $nilaiAngka = '';
                if ($nilaiRaw->has($ind->id_pkl_tp)) {
                    $dataInd = $nilaiRaw->get($ind->id_pkl_tp)->data_indikator;
                    if (isset($dataInd[$ind->id])) {
                        $nilaiAngka = $dataInd[$ind->id]['nilai'];
                    }
                }
                $row['IND_' . $ind->id] = $nilaiAngka;
            }

            $rows->push((object) $row);
        }

        return $rows;
    }

    public function headings(): array
    {
        // BARIS 1: Header Statis (A - M)
        $row1 = [
            'ID_SISTEM (JANGAN DIUBAH)', 'Nama Siswa', 'Kelas', 'Tempat PKL', 
            'Program Keahlian', 'Konsentrasi Keahlian', 'Nama Instruktur (Perusahaan)', 
            'Tgl Mulai (YYYY-MM-DD)', 'Tgl Selesai (YYYY-MM-DD)', 
            'Sakit', 'Izin', 'Alpa', 'Catatan Pembimbing'
        ];

        // BARIS 2: Tempat bernaung Indikator (Statisnya dikosongkan karena akan di-merge)
        $row2 = [
            '', '', '', '', '', '', '', '', '', '', '', '', ''
        ];

        // Memasukkan Judul TP di Baris 1
        foreach ($this->tpSpans as $span) {
            $row1[] = $span['nama_tp']; 
            for ($i = $span['start_col'] + 1; $i <= $span['end_col']; $i++) {
                $row1[] = '';
            }
        }

        // Memasukkan Judul Indikator di Baris 2
        foreach ($this->orderedIndikators as $ind) {
            $row2[] = 'IND_' . $ind->id . "\n" . $ind->nama_indikator; // Ditambah enter agar ID dan Nama terpisah baris
        }

        return [$row1, $row2]; 
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // 1. Pengaturan Kolom Statis (Auto Size, Warna Kuning + Merge Baris 1-2)
                $staticCols = ['A','B','C','D','E','F','G','H','I','J','K','L','M'];
                foreach ($staticCols as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true); // Auto size diaktifkan manual
                    $sheet->mergeCells("{$col}1:{$col}2");
                    $sheet->getStyle("{$col}1:{$col}2")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("{$col}1:{$col}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("{$col}1:{$col}2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00'); 
                }

                // Palet Warna Pastel untuk TP
                $tpColors = ['FFD9EAD3', 'FFC9DAF8', 'FFFCE5CD', 'FFF4CCCC', 'FFE2D0F9', 'FFD0E0E3'];
                $colorIndex = 0;
                $lastColIndex = 13; 

                // 2. Pengaturan Kolom TP & Indikator (Merge Horizontal & Warna)
                foreach ($this->tpSpans as $span) {
                    $bgColor = $tpColors[$colorIndex % count($tpColors)];
                    $startLetter = Coordinate::stringFromColumnIndex($span['start_col']);
                    $endLetter = Coordinate::stringFromColumnIndex($span['end_col']);
                    
                    if ($span['start_col'] < $span['end_col']) {
                        $sheet->mergeCells("{$startLetter}1:{$endLetter}1");
                    }
                    
                    $sheet->getStyle("{$startLetter}1:{$endLetter}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("{$startLetter}1:{$endLetter}2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bgColor);

                    $lastColIndex = $span['end_col'];
                    $colorIndex++;
                }

                // 3. Atur Fixed Width (Lebar Tetap) & Wrap Text untuk Kolom Indikator
                $startIndCol = 14; 
                for ($c = $startIndCol; $c <= $lastColIndex; $c++) {
                    $colLetter = Coordinate::stringFromColumnIndex($c);
                    $sheet->getColumnDimension($colLetter)->setAutoSize(false);
                    $sheet->getColumnDimension($colLetter)->setWidth(18); // Lebar dikurangi menjadi 18
                }

                // Wrap Text di baris ke-2 (Nama Indikator) dan atur alignment
                $lastLetter = Coordinate::stringFromColumnIndex($lastColIndex);
                $sheet->getStyle("N2:{$lastLetter}2")->getAlignment()->setWrapText(true);
                $sheet->getStyle("N2:{$lastLetter}2")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("N2:{$lastLetter}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getRowDimension(2)->setRowHeight(60); // Tinggikan baris ke-2 agar teks bisa terbaca

                // 4. Tambahkan Garis Tepi (Borders) pada seluruh Header
                $headerRange = "A1:{$lastLetter}2";
                
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}