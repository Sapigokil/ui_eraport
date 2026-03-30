<?php

namespace App\Imports;

use App\Models\PklTempat;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PklTempatImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // PENTING: Maatwebsite Excel otomatis mengubah Heading 'Nama Perusahaan' menjadi 'nama_perusahaan'
        
        // Skip baris jika nama perusahaan kosong
        if (!isset($row['nama_perusahaan']) || empty(trim($row['nama_perusahaan']))) {
            return null;
        }

        // Penanganan Format Tanggal Excel dari 'Tanggal MOU (YYYY-MM-DD)' menjadi 'tanggal_mou_yyyy_mm_dd'
        $tanggalMou = null;
        $tanggalKey = isset($row['tanggal_mou_yyyy_mm_dd']) ? 'tanggal_mou_yyyy_mm_dd' : 'tanggal_mou';
        
        if (isset($row[$tanggalKey]) && !empty(trim($row[$tanggalKey]))) {
            if (is_numeric($row[$tanggalKey])) {
                $tanggalMou = Date::excelToDateTimeObject($row[$tanggalKey])->format('Y-m-d');
            } else {
                $tanggalMou = date('Y-m-d', strtotime($row[$tanggalKey]));
            }
        }

        // Penanganan Status Aktif dari 'Status Aktif (1/0)' menjadi 'status_aktif_10'
        $statusKey = isset($row['status_aktif_10']) ? 'status_aktif_10' : 'status_aktif';
        $isActive = true; // Default aktif
        if (isset($row[$statusKey])) {
            $isActive = in_array(trim((string)$row[$statusKey]), ['1', 'true', 'yes', 'aktif']);
        } elseif (isset($row['is_active'])) {
             $isActive = in_array(trim((string)$row['is_active']), ['1', 'true', 'yes', 'aktif']);
        }

        // Simpan Data
        return new PklTempat([
            // ID Guru tidak ada di file Export, maka biarkan null (admin bisa set manual nanti jika perlu)
            'guru_id'            => null,
            'nama_perusahaan'    => trim($row['nama_perusahaan']),
            'bidang_usaha'       => isset($row['bidang_usaha']) ? trim($row['bidang_usaha']) : null,
            'nama_pimpinan'      => isset($row['nama_pimpinan']) ? trim($row['nama_pimpinan']) : null,
            'alamat_perusahaan'  => isset($row['alamat_lengkap']) ? trim($row['alamat_lengkap']) : (isset($row['alamat_perusahaan']) ? trim($row['alamat_perusahaan']) : '-'),
            'kota'               => isset($row['kota']) ? trim($row['kota']) : null,
            'no_telp_perusahaan' => isset($row['no_telp_perusahaan']) ? trim($row['no_telp_perusahaan']) : null,
            'email_perusahaan'   => isset($row['email']) ? trim($row['email']) : (isset($row['email_perusahaan']) ? trim($row['email_perusahaan']) : null),
            'no_surat_mou'       => isset($row['no_surat_mou']) ? trim($row['no_surat_mou']) : null,
            'tanggal_mou'        => $tanggalMou,
            'nama_instruktur'    => isset($row['nama_instruktur']) ? trim($row['nama_instruktur']) : '-',
            'no_telp_instruktur' => isset($row['no_telp_instruktur']) ? trim($row['no_telp_instruktur']) : null,
            'is_active'          => $isActive,
        ]);
    }
}