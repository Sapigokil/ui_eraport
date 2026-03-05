<?php

namespace App\Http\Controllers;

use App\Models\PklTp;
use App\Models\PklTpIndikator;
use App\Models\PklTpRubrik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PklSettingController extends Controller
{
    public function index()
    {
        $tpData = PklTp::orderBy('no_urut', 'asc')->get();
        $indikatorData = PklTpIndikator::orderBy('no_urut', 'asc')->get()->groupBy('id_pkl_tp');
        $rubrikData = PklTpRubrik::all()->groupBy('id_pkl_tp_indikator');

        return view('pkl.setting.tp_index', compact('tpData', 'indikatorData', 'rubrikData'));
    }

    public function storeMassal(Request $request)
    {
        if (!$request->has('tp') || empty($request->tp)) {
            return redirect()->back()->with('error', 'Tidak ada data Tujuan Pembelajaran yang dikirim.');
        }

        DB::beginTransaction();
        try {
            $urutanTp = 1;

            foreach ($request->tp as $uidTp => $dataTp) {
                $tp = PklTp::updateOrCreate(
                    is_numeric($uidTp) ? ['id' => $uidTp] : ['nama_tp' => $dataTp['nama_tp']],
                    [
                        'nama_tp' => $dataTp['nama_tp'],
                        'label_tp' => $dataTp['label_tp'] ?? null,
                        'no_urut' => $urutanTp++
                    ]
                );

                $idTpAsli = $tp->id;
                $urutanIndikator = 1;
                $rangeTp = $dataTp['range'] ?? [];

                if (isset($dataTp['indikator'])) {
                    foreach ($dataTp['indikator'] as $uidIndikator => $dataIndikator) {
                        $indikator = PklTpIndikator::updateOrCreate(
                            is_numeric($uidIndikator) ? ['id' => $uidIndikator] : ['id_pkl_tp' => $idTpAsli, 'nama_indikator' => $dataIndikator['nama']],
                            [
                                'id_pkl_tp' => $idTpAsli,
                                'nama_indikator' => $dataIndikator['nama'],
                                'no_urut' => $urutanIndikator++
                            ]
                        );

                        $idIndikatorAsli = $indikator->id;
                        $predikatKeys = ['sangat_baik', 'baik', 'cukup', 'kurang'];
                        $namaPredikat = ['Sangat Baik', 'Baik', 'Cukup', 'Perlu Bimbingan'];

                        foreach ($predikatKeys as $idx => $key) {
                            if (isset($dataIndikator['rubrik'][$key])) {
                                $r = $dataIndikator['rubrik'][$key];
                                $minNilai = $rangeTp[$key]['min'] ?? 0;
                                $maxNilai = $rangeTp[$key]['max'] ?? 100;

                                PklTpRubrik::updateOrCreate(
                                    ['id_pkl_tp_indikator' => $idIndikatorAsli, 'predikat' => $namaPredikat[$idx]],
                                    [
                                        'max_nilai' => $maxNilai,
                                        'min_nilai' => $minNilai,
                                        'deskripsi_rubrik' => $r['deskripsi_rubrik'] ?? '-',
                                        'teks_untuk_rapor' => '-' 
                                    ]
                                );
                            }
                        }
                    }
                }
            }
            DB::commit();
            return redirect()->route('settings.pkl.index')->with('success', 'Rubrik Penilaian PKL berhasil disimpan massal!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function destroyTp($id_tp)
    {
        DB::beginTransaction();
        try {
            $indikatorIds = PklTpIndikator::where('id_pkl_tp', $id_tp)->pluck('id')->toArray();
            if (count($indikatorIds) > 0) {
                PklTpRubrik::whereIn('id_pkl_tp_indikator', $indikatorIds)->delete();
            }
            PklTpIndikator::where('id_pkl_tp', $id_tp)->delete();
            PklTp::where('id', $id_tp)->delete();

            DB::commit();
            return redirect()->route('settings.pkl.index')->with('success', 'Tujuan Pembelajaran beserta rubriknya berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus TP: ' . $e->getMessage());
        }
    }

    // ==============================================================
    // FUNGSI: DOWNLOAD TEMPLATE EXCEL (STATIS 6 TABEL x 6 BARIS)
    // ==============================================================
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(35);

        $tpData = PklTp::orderBy('no_urut', 'asc')->get();
        $indikatorData = PklTpIndikator::orderBy('no_urut', 'asc')->get()->groupBy('id_pkl_tp');
        $rubrikData = PklTpRubrik::all()->groupBy('id_pkl_tp_indikator');

        $styleHeader = ['font' => ['bold' => true]];
        
        // KITA PAKSA MENCETAK 6 BLOK TABEL
        for ($i = 0; $i < 6; $i++) {
            
            // Rumus Titik Awal Baris Setiap Blok: Blok 1=1, Blok 2=13, Blok 3=25, dst.
            $startRow = ($i * 12) + 1;
            
            // Ambil data eksisting dari database jika ada
            $tp = $tpData->get($i);
            
            // Baris 1: Judul
            $sheet->setCellValue('A'.$startRow, 'JUDUL TUJUAN PEMBELAJARAN ' . ($i+1) . ':');
            $sheet->setCellValue('B'.$startRow, $tp ? $tp->nama_tp : '');
            $sheet->getStyle('A'.$startRow.':B'.$startRow)->applyFromArray($styleHeader);

            // Baris 2: Label
            $sheet->setCellValue('A'.($startRow+1), 'LABEL DALAM RAPOR:');
            $sheet->setCellValue('B'.($startRow+1), $tp ? $tp->label_tp : '');
            $sheet->getStyle('A'.($startRow+1).':B'.($startRow+1))->applyFromArray($styleHeader);

            // Baris 3: Header Tabel Warna-Warni
            $sheet->setCellValue('A'.($startRow+2), 'Indikator');
            $sheet->setCellValue('B'.($startRow+2), 'Sangat Baik');
            $sheet->setCellValue('C'.($startRow+2), 'Baik');
            $sheet->setCellValue('D'.($startRow+2), 'Cukup');
            $sheet->setCellValue('E'.($startRow+2), 'Perlu Bimbingan');
            
            $sheet->getStyle('A'.($startRow+2).':E'.($startRow+2))->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
            $sheet->getStyle('A'.($startRow+2).':E'.($startRow+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $sheet->getStyle('A'.($startRow+2))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('6C757D');
            $sheet->getStyle('B'.($startRow+2))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('4CAF50');
            $sheet->getStyle('C'.($startRow+2))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('2196F3');
            $sheet->getStyle('D'.($startRow+2))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF9800');
            $sheet->getStyle('E'.($startRow+2))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F44336');

            // Baris 4: Aturan Range
            $ranges = ['sb' => '100-90', 'b' => '89-80', 'c' => '79-70', 'k' => '69-0'];
            $indikators = $tp ? $indikatorData->get($tp->id, collect()) : collect();
            
            if ($indikators->isNotEmpty()) {
                $firstIndData = $rubrikData->get($indikators->first()->id, collect())->keyBy('predikat');
                if($firstIndData->has('Sangat Baik')) $ranges['sb'] = $firstIndData['Sangat Baik']->max_nilai . '-' . $firstIndData['Sangat Baik']->min_nilai;
                if($firstIndData->has('Baik')) $ranges['b'] = $firstIndData['Baik']->max_nilai . '-' . $firstIndData['Baik']->min_nilai;
                if($firstIndData->has('Cukup')) $ranges['c'] = $firstIndData['Cukup']->max_nilai . '-' . $firstIndData['Cukup']->min_nilai;
                if($firstIndData->has('Perlu Bimbingan')) $ranges['k'] = $firstIndData['Perlu Bimbingan']->max_nilai . '-' . $firstIndData['Perlu Bimbingan']->min_nilai;
            }

            $sheet->setCellValue('A'.($startRow+3), 'RANGE (MAX-MIN):');
            $sheet->setCellValue('B'.($startRow+3), $ranges['sb']);
            $sheet->setCellValue('C'.($startRow+3), $ranges['b']);
            $sheet->setCellValue('D'.($startRow+3), $ranges['c']);
            $sheet->setCellValue('E'.($startRow+3), $ranges['k']);
            
            $sheet->getStyle('A'.($startRow+3).':E'.($startRow+3))->getFont()->setBold(true);
            $sheet->getStyle('A'.($startRow+3).':E'.($startRow+3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A'.($startRow+3))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E9ECEF');

            // Baris 5 sampai 10: (Selalu cetak 6 Baris Indikator)
            for ($j = 0; $j < 6; $j++) {
                $rowInd = $startRow + 4 + $j;
                $ind = $indikators->get($j);
                
                if ($ind) {
                    $rData = $rubrikData->get($ind->id, collect())->keyBy('predikat');
                    $sheet->setCellValue('A'.$rowInd, $ind->nama_indikator);
                    $sheet->setCellValue('B'.$rowInd, $rData['Sangat Baik']->deskripsi_rubrik ?? '-');
                    $sheet->setCellValue('C'.$rowInd, $rData['Baik']->deskripsi_rubrik ?? '-');
                    $sheet->setCellValue('D'.$rowInd, $rData['Cukup']->deskripsi_rubrik ?? '-');
                    $sheet->setCellValue('E'.$rowInd, $rData['Perlu Bimbingan']->deskripsi_rubrik ?? '-');
                } else {
                    // Jika database total kosong, cetak contoh 1 baris saja di Blok 1 agar User paham
                    if ($tpData->isEmpty() && $i == 0 && $j == 0) {
                        $sheet->setCellValue('B'.$startRow, 'SOFT SKILLS DUNIA KERJA');
                        $sheet->setCellValue('B'.($startRow+1), 'Menerapkan soft skills dunia kerja');
                        $sheet->setCellValue('A'.$rowInd, 'Disiplin & Tanggung Jawab');
                        $sheet->setCellValue('B'.$rowInd, 'Selalu tepat waktu...');
                        $sheet->setCellValue('C'.$rowInd, 'Hampir selalu...');
                        $sheet->setCellValue('D'.$rowInd, 'Kadang terlambat...');
                        $sheet->setCellValue('E'.$rowInd, 'Sering terlambat...');
                    }
                }
                
                $sheet->getStyle('A'.$rowInd.':E'.$rowInd)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A'.$rowInd.':E'.$rowInd)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            }

            // Beri bingkai tabel dari baris Indikator sampai baris 6 data (Row 3 ke Row 10)
            $sheet->getStyle('A'.($startRow+2).':E'.($startRow+9))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="Template_Rubrik_PKL.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    // ==============================================================
    // FUNGSI: IMPORT EXCEL (PEMBACA KOORDINAT STATIS 100% AMAN)
    // ==============================================================
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('file_excel');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            
            $urutanTp = 1;
            $tpBerhasilDiproses = 0;
            $indBerhasilDiproses = 0;

            // FUNGSI HELPER: Memecah "100-90"
            $parseRange = function($str, $defMax, $defMin) {
                if(empty($str)) return ['max' => $defMax, 'min' => $defMin];
                $parts = explode('-', str_replace(' ', '', $str));
                return [
                    'max' => isset($parts[0]) && is_numeric($parts[0]) ? (int)$parts[0] : $defMax,
                    'min' => isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : $defMin
                ];
            };

            // LOOP 6 BLOK TABEL SESUAI TEMPLATE
            for ($i = 0; $i < 6; $i++) {
                
                // Kalkulasi Koordinat Baris
                $startRow = ($i * 12) + 1;
                
                // Ambil Judul & Label (Murni melihat sel B1, B13, B25, dst)
                $namaTp = trim($sheet->getCell('B' . $startRow)->getFormattedValue() ?? '');
                $labelTp = trim($sheet->getCell('B' . ($startRow + 1))->getFormattedValue() ?? '');

                // JIKA JUDUL KOSONG = ABAIKAN SELURUH TABEL INI
                if (empty($namaTp)) {
                    continue; 
                }

                $tpModel = PklTp::updateOrCreate(
                    ['nama_tp' => $namaTp], 
                    [
                        'label_tp' => $labelTp,
                        'no_urut' => $urutanTp++
                    ]
                );
                
                $tpBerhasilDiproses++;
                $urutanIndikator = 1;

                // Ambil Range Nilai (Baris ke 4 dari startRow)
                $rSB = trim($sheet->getCell('B' . ($startRow + 3))->getFormattedValue() ?? '');
                $rB  = trim($sheet->getCell('C' . ($startRow + 3))->getFormattedValue() ?? '');
                $rC  = trim($sheet->getCell('D' . ($startRow + 3))->getFormattedValue() ?? '');
                $rK  = trim($sheet->getCell('E' . ($startRow + 3))->getFormattedValue() ?? '');

                $ranges = [
                    'Sangat Baik'     => $parseRange($rSB, 100, 90),
                    'Baik'            => $parseRange($rB, 89, 80),
                    'Cukup'           => $parseRange($rC, 79, 70),
                    'Perlu Bimbingan' => $parseRange($rK, 69, 0),
                ];

                // LOOP 6 BARIS INDIKATOR (Baris ke 5 sampai 10 dari startRow)
                for ($j = 0; $j < 6; $j++) {
                    $rowInd = $startRow + 4 + $j;
                    $namaIndikator = trim($sheet->getCell('A' . $rowInd)->getFormattedValue() ?? '');
                    
                    // JIKA NAMA INDIKATOR KOSONG = ABAIKAN BARIS INI
                    if (empty($namaIndikator)) {
                        continue; 
                    }

                    $indikatorModel = PklTpIndikator::updateOrCreate(
                        ['id_pkl_tp' => $tpModel->id, 'nama_indikator' => $namaIndikator],
                        ['no_urut' => $urutanIndikator++]
                    );

                    // Tarik Deskripsi
                    $descs = [
                        'Sangat Baik'     => trim($sheet->getCell('B' . $rowInd)->getFormattedValue() ?? '-'),
                        'Baik'            => trim($sheet->getCell('C' . $rowInd)->getFormattedValue() ?? '-'),
                        'Cukup'           => trim($sheet->getCell('D' . $rowInd)->getFormattedValue() ?? '-'),
                        'Perlu Bimbingan' => trim($sheet->getCell('E' . $rowInd)->getFormattedValue() ?? '-')
                    ];

                    foreach ($descs as $predikat => $deskripsi) {
                        PklTpRubrik::updateOrCreate(
                            ['id_pkl_tp_indikator' => $indikatorModel->id, 'predikat' => $predikat],
                            [
                                'max_nilai' => $ranges[$predikat]['max'],
                                'min_nilai' => $ranges[$predikat]['min'],
                                'deskripsi_rubrik' => empty($deskripsi) ? '-' : $deskripsi,
                                'teks_untuk_rapor' => '-'
                            ]
                        );
                    }
                    $indBerhasilDiproses++;
                }
            }

            if ($tpBerhasilDiproses === 0) {
                throw new \Exception("Tidak ada data Tujuan Pembelajaran yang terdeteksi. Pastikan Anda mengisi Judul TP di Cell B1, B13, dsb sesuai template.");
            }

            DB::commit();
            return redirect()->route('settings.pkl.index')->with('success', "Berhasil! Sebanyak $tpBerhasilDiproses TP dan $indBerhasilDiproses Indikator telah tersimpan.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import Gagal: ' . $e->getMessage());
        }
    }
}