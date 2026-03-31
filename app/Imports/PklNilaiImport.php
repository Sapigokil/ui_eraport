<?php

namespace App\Imports;

use App\Models\PklCatatanSiswa;
use App\Models\PklNilaiSiswa;
use App\Models\PklTpIndikator;
use App\Models\PklTpRubrik;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PklNilaiImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // 1. Ambil semua indikator yang aktif di sistem
        $activeIndikators = PklTpIndikator::whereHas('tp', function($q) {
            $q->where('is_active', 1);
        })->get();

        $totalIndikatorSistem = $activeIndikators->count();
        $indikatorMap = $activeIndikators->pluck('id_pkl_tp', 'id')->toArray();
        $semuaRubrik = PklTpRubrik::all()->groupBy('id_pkl_tp_indikator');

        $colMap = []; // Memetakan Index Kolom Excel ke ID Indikator
        
        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                // Baris 1: Header TP -> Skip
                if ($rowIndex === 0) continue;

                // Baris 2: Header Indikator -> Mapping Kolom Dinamis
                if ($rowIndex === 1) {
                    foreach ($row as $colIndex => $cellValue) {
                        if (is_string($cellValue) && str_contains($cellValue, 'IND_')) {
                            if (preg_match('/IND_(\d+)/', $cellValue, $matches)) {
                                $colMap[$colIndex] = $matches[1];
                            }
                        }
                    }
                    continue;
                }

                // Baris 3 dst: Eksekusi Data Siswa
                $id_penempatan = $row[0] ?? null;
                if (!$id_penempatan || !is_numeric($id_penempatan)) continue; 

                $tglMulai = $this->parseDate($row[7] ?? null);
                $tglSelesai = $this->parseDate($row[8] ?? null);

                // --- LOGIKA VALIDASI KELENGKAPAN NILAI ---
                $nilaiPerTp = [];
                $jumlahNilaiTerisi = 0;

                foreach ($colMap as $colIndex => $id_ind) {
                    $value = $row[$colIndex] ?? null;
                    
                    if ($id_ind && isset($indikatorMap[$id_ind]) && $value !== null && $value !== '') {
                        $id_tp = $indikatorMap[$id_ind];
                        $nilaiPerTp[$id_tp][$id_ind] = (int) $value;
                        $jumlahNilaiTerisi++;
                    }
                }

                // Tentukan Status: 1 (Final) jika SEMUA indikator terisi, 0 (Draft) jika ada yang kosong
                $statusOtomatis = ($jumlahNilaiTerisi >= $totalIndikatorSistem) ? 1 : 0;
                if ($jumlahNilaiTerisi === 0) $statusOtomatis = 0;

                // 2. Simpan Catatan & Identitas PKL
                PklCatatanSiswa::updateOrCreate(
                    ['id_penempatan' => $id_penempatan],
                    [
                        'program_keahlian'     => $row[4] ?? null,
                        'konsentrasi_keahlian' => $row[5] ?? null,
                        'tanggal_mulai'        => $tglMulai,
                        'tanggal_selesai'      => $tglSelesai,
                        'nama_instruktur'      => $row[6] ?? null,
                        'sakit'                => $row[9] ?? 0,
                        'izin'                 => $row[10] ?? 0,
                        'alpa'                 => $row[11] ?? 0,
                        'catatan_pembimbing'   => $row[12] ?? null,
                        'status_penilaian'     => $statusOtomatis, 
                        'created_by'           => auth()->user()->id ?? null
                    ]
                );

                // 3. Proses Rubrik Otomatis (Murni menggunakan Titik Koma)
                foreach ($nilaiPerTp as $id_tp => $dataIndikatorInput) {
                    $nilaiArray = [];
                    $validInputs = []; 
                    $totalNilai = 0;

                    foreach ($dataIndikatorInput as $id_ind => $val_angka) {
                        $totalNilai += $val_angka;
                        $rubrikInd = $semuaRubrik->get($id_ind, collect());
                        $deskripsiDapat = '';

                        foreach ($rubrikInd as $r) {
                            if ($val_angka >= $r->min_nilai && $val_angka <= $r->max_nilai) {
                                $deskripsiDapat = $r->deskripsi_rubrik;
                                break;
                            }
                        }

                        $nilaiArray[$id_ind] = ['nilai' => $val_angka, 'deskripsi' => $deskripsiDapat];
                        $validInputs[] = ['nilai' => $val_angka, 'deskripsi' => $deskripsiDapat];
                    }

                    $countNilai = count($validInputs);
                    if ($countNilai == 0) continue; 

                    $rataRata = $totalNilai / $countNilai;
                    $maxVal = -1; $minVal = 101;
                    $descMax = ''; $descMin = '';

                    foreach ($validInputs as $item) {
                        if ($item['nilai'] > $maxVal) {
                            $maxVal = $item['nilai'];
                            $descMax = $item['deskripsi'];
                        }
                        if ($item['nilai'] < $minVal) {
                            $minVal = $item['nilai'];
                            $descMin = $item['deskripsi'];
                        }
                    }

                    $gabungan = "";
                    $dMax = lcfirst(trim($descMax));
                    $dMin = lcfirst(trim($descMin));

                    if ($maxVal == $minVal) {
                        if ($countNilai >= 2) {
                            $keys = array_rand($validInputs, 2);
                            $d1 = lcfirst(trim($validInputs[$keys[0]]['deskripsi']));
                            $d2 = lcfirst(trim($validInputs[$keys[1]]['deskripsi']));
                            $gabungan = "Ananda $d1; $d2.";
                        } else {
                            $d1 = lcfirst(trim($validInputs[0]['deskripsi']));
                            $gabungan = "Ananda $d1.";
                        }
                    } else {
                        // FIX: Menggunakan titik koma secara absolut
                        $gabungan = "Ananda $dMax; $dMin.";
                    }

                    PklNilaiSiswa::updateOrCreate(
                        ['id_penempatan' => $id_penempatan, 'id_pkl_tp' => $id_tp],
                        [
                            'data_indikator' => $nilaiArray,
                            'nilai_rata_rata' => $rataRata,
                            'deskripsi_gabungan' => $gabungan,
                            'created_by' => auth()->user()->id ?? null
                        ]
                    );
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; 
        }
    }

    private function parseDate($dateString)
    {
        if (empty($dateString)) return null;
        if (is_numeric($dateString)) {
            return Date::excelToDateTimeObject($dateString)->format('Y-m-d');
        }
        return date('Y-m-d', strtotime($dateString));
    }
}