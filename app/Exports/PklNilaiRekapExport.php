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

class PklNilaiRekapExport implements FromCollection, WithHeadings, WithEvents
{
    protected $filters;
    protected $orderedIndikators;
    protected $tpSpans;

    public function __construct($filters)
    {
        $this->filters = (object) $filters;
        
        $this->orderedIndikators = collect();
        $this->tpSpans = [];

        // 1. Ambil TP yang aktif
        $tps = PklTp::where('is_active', 1)->orderBy('no_urut', 'asc')->get();
        
        // 2. Kelompokkan indikator berdasarkan TP
        $indikatorsGrouped = PklTpIndikator::orderBy('no_urut', 'asc')->get()->groupBy('id_pkl_tp');

        // 3. Kalkulasi letak Kolom (Mulai dari kolom ke-8 / Kolom H, setelah 7 kolom identitas)
        $currentColIndex = 8; 

        foreach ($tps as $tp) {
            $inds = $indikatorsGrouped->get($tp->id, collect());
            $indCount = $inds->count();
            
            if ($indCount > 0) {
                $this->tpSpans[] = [
                    'nama_tp' => $tp->nama_tp,
                    'start_col' => $currentColIndex,
                    'end_col' => $currentColIndex + $indCount - 1
                ];

                foreach ($inds as $ind) {
                    $this->orderedIndikators->push($ind);
                    $currentColIndex++;
                }
            }
        }
    }

    public function collection()
    {
        // Query data berdasarkan filter
        $query = DB::table('pkl_penempatan')
            ->join('siswa', 'pkl_penempatan.id_siswa', '=', 'siswa.id_siswa')
            ->join('pkl_gurusiswa', function($join) {
                $join->on('pkl_penempatan.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                     ->where('pkl_gurusiswa.tahun_ajaran', '=', $this->filters->tahun_ajaran)
                     ->where('pkl_gurusiswa.semester', '=', $this->filters->semester);
            })
            ->leftJoin('pkl_tempat', 'pkl_penempatan.id_pkltempat', '=', 'pkl_tempat.id')
            ->leftJoin('pkl_catatansiswa', 'pkl_penempatan.id', '=', 'pkl_catatansiswa.id_penempatan');

        if (!empty($this->filters->id_kelas)) $query->where('pkl_gurusiswa.id_kelas', $this->filters->id_kelas);
        if (!empty($this->filters->id_guru)) $query->where('pkl_gurusiswa.id_guru', $this->filters->id_guru);
        if (!empty($this->filters->id_tempat)) $query->where('pkl_penempatan.id_pkltempat', $this->filters->id_tempat);
        
        if ($this->filters->status_penilaian !== null && $this->filters->status_penilaian !== '') {
            if ($this->filters->status_penilaian === 'belum') {
                $query->whereNull('pkl_catatansiswa.status_penilaian');
            } elseif ($this->filters->status_penilaian === '0') {
                $query->where('pkl_catatansiswa.status_penilaian', 0);
            } elseif ($this->filters->status_penilaian === '1') {
                $query->where('pkl_catatansiswa.status_penilaian', 1);
            }
        }

        $dataSiswa = $query->select(
                'pkl_penempatan.id as id_penempatan',
                'siswa.nisn',
                'pkl_gurusiswa.nama_siswa',
                'pkl_gurusiswa.nama_kelas',
                'pkl_gurusiswa.nama_guru',
                'pkl_tempat.nama_perusahaan',
                'pkl_catatansiswa.status_penilaian'
            )
            ->orderBy('pkl_gurusiswa.nama_kelas')
            ->orderBy('pkl_gurusiswa.nama_siswa')
            ->get();

        $rows = collect();
        $no = 1;

        foreach ($dataSiswa as $siswa) {
            $nilaiRaw = PklNilaiSiswa::where('id_penempatan', $siswa->id_penempatan)->get()->keyBy('id_pkl_tp');

            $statusText = 'Belum Dinilai';
            if ($siswa->status_penilaian === 0) $statusText = 'Draft';
            elseif ($siswa->status_penilaian === 1) $statusText = 'Final';

            $row = [
                'No'                   => $no++,
                'Nama Siswa'           => $siswa->nama_siswa,
                'NISN'                 => $siswa->nisn ?? '-',
                'Kelas'                => $siswa->nama_kelas,
                'Guru Pembimbing'      => $siswa->nama_guru,
                'Tempat PKL'           => $siswa->nama_perusahaan ?? 'Belum Diatur',
                'Status Penilaian'     => $statusText,
            ];

            $totalRata = 0;
            $countRata = 0;
            $deskripsiAll = [];

            // Memasukkan Nilai per Indikator
            foreach ($this->orderedIndikators as $ind) {
                $nilaiAngka = '';
                if ($nilaiRaw->has($ind->id_pkl_tp)) {
                    $dataInd = $nilaiRaw->get($ind->id_pkl_tp)->data_indikator;
                    // Antisipasi jika data_indikator adalah string JSON
                    if (is_string($dataInd)) { $dataInd = json_decode($dataInd, true); }

                    if (isset($dataInd[$ind->id])) {
                        $nilaiAngka = $dataInd[$ind->id]['nilai'];
                    }
                }
                $row[$ind->nama_indikator] = $nilaiAngka;
            }

            // Mengumpulkan Rata-rata dan Deskripsi (Deskripsi sudah diproses dengan ";" saat simpan)
            foreach($nilaiRaw as $n) {
                if (!empty($n->deskripsi_gabungan)) {
                    $deskripsiAll[] = $n->deskripsi_gabungan;
                }
                $totalRata += $n->nilai_rata_rata;
                $countRata++;
            }

            $row['Rata-Rata Akhir'] = $countRata > 0 ? round($totalRata / $countRata, 2) : '';
            // Menggabungkan deskripsi antar TP dengan spasi (karena tiap deskripsi TP sudah diakhiri titik)
            $row['Deskripsi Rapor'] = implode(' ', array_filter($deskripsiAll));

            $rows->push((object) $row);
        }

        return $rows;
    }

    public function headings(): array
    {
        // BARIS 1
        $row1 = [ 'No', 'Nama Siswa', 'NISN', 'Kelas', 'Guru Pembimbing', 'Tempat PKL', 'Status Penilaian' ];
        // BARIS 2
        $row2 = [ '', '', '', '', '', '', '' ];

        // Judul TP di Baris 1
        foreach ($this->tpSpans as $span) {
            $row1[] = $span['nama_tp']; 
            for ($i = $span['start_col'] + 1; $i <= $span['end_col']; $i++) {
                $row1[] = '';
            }
        }

        // Judul Indikator di Baris 2
        foreach ($this->orderedIndikators as $ind) {
            $row2[] = $ind->nama_indikator;
        }

        // Kolom Akhir
        $row1[] = 'Rata-Rata Akhir';
        $row1[] = 'Deskripsi Rapor';
        $row2[] = '';
        $row2[] = '';

        return [$row1, $row2]; 
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // 1. Styling Kolom Identitas (A-G)
                $staticColsLeft = ['A','B','C','D','E','F','G'];
                foreach ($staticColsLeft as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->mergeCells("{$col}1:{$col}2");
                    $sheet->getStyle("{$col}1:{$col}2")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("{$col}1:{$col}2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
                }

                // 2. Styling Kolom TP & Indikator
                $tpColors = ['FFD9EAD3', 'FFC9DAF8', 'FFFCE5CD', 'FFF4CCCC', 'FFE2D0F9', 'FFD0E0E3'];
                $colorIndex = 0;
                $lastIndColIndex = 7; 

                foreach ($this->tpSpans as $span) {
                    $bgColor = $tpColors[$colorIndex % count($tpColors)];
                    $startLet = Coordinate::stringFromColumnIndex($span['start_col']);
                    $endLet = Coordinate::stringFromColumnIndex($span['end_col']);
                    
                    if ($span['start_col'] < $span['end_col']) {
                        $sheet->mergeCells("{$startLet}1:{$endLet}1");
                    }
                    
                    $sheet->getStyle("{$startLet}1:{$endLet}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("{$startLet}1:{$endLet}2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bgColor);

                    // Lebar Kolom Indikator
                    for ($c = $span['start_col']; $c <= $span['end_col']; $c++) {
                        $colLetter = Coordinate::stringFromColumnIndex($c);
                        $sheet->getColumnDimension($colLetter)->setAutoSize(false);
                        $sheet->getColumnDimension($colLetter)->setWidth(15);
                    }

                    $lastIndColIndex = $span['end_col'];
                    $colorIndex++;
                }

                // Wrap Text Indikator
                $firstInd = Coordinate::stringFromColumnIndex(8);
                $lastInd = Coordinate::stringFromColumnIndex($lastIndColIndex);
                $sheet->getStyle("{$firstInd}2:{$lastInd}2")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getRowDimension(2)->setRowHeight(50);

                // 3. Styling Kolom Laporan Akhir (Rata-rata & Deskripsi)
                $rataCol = $lastIndColIndex + 1;
                $descCol = $lastIndColIndex + 2;
                $rataLet = Coordinate::stringFromColumnIndex($rataCol);
                $descLet = Coordinate::stringFromColumnIndex($descCol);

                $sheet->mergeCells("{$rataLet}1:{$rataLet}2");
                $sheet->mergeCells("{$descLet}1:{$descLet}2");
                $sheet->getColumnDimension($descLet)->setWidth(60); // Deskripsi dibuat lebar

                $rightRange = "{$rataLet}1:{$descLet}2";
                $sheet->getStyle($rightRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($rightRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');

                // Wrap text isi deskripsi
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("{$descLet}3:{$descLet}{$highestRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

                // 4. Border & Bold
                $headerRange = "A1:{$descLet}2";
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                ]);
            },
        ];
    }
}