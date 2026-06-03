<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SiswaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Siswa::with(['detail', 'kelas']);

        $statusFilter = $this->request->get('status', 'aktif');
        if ($statusFilter !== 'semua') {
            $query->where('status', $statusFilter);
        }

        if ($this->request->has('id_kelas')) {
            $filterKelas = $this->request->id_kelas;
            if ($filterKelas == 'no_class') {
                $query->whereNull('id_kelas');
            } elseif ($filterKelas != 'all' && $filterKelas != '') {
                $query->where('id_kelas', $filterKelas);
            }
        }

        if ($this->request->has('search') && $this->request->search != '') {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%')
                  ->orWhere('nipd', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('nama_siswa', 'asc');
    }

    public function headings(): array
    {
        return [
            'NIPD', 'NISN', 'Nama Siswa', 'JK', 'Tingkat', 'Status', 'Kelas',
            'Kelas Awal', 'Tanggal Masuk', // Kolom Baru
            'NIK', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Alamat', 
            'RT', 'RW', 'Dusun', 'Kelurahan', 'Kecamatan', 'Kode Pos',
            'Telepon', 'HP', 'Email', 'SKHUN', 'Penerima KPS', 'No KPS',
            'No Peserta UN', 'No Seri Ijazah', 'Penerima KIP', 'No KIP', 'Nama KIP',
            'Nomor KKS', 'No Regis Akta', 'Bank', 'No Rekening', 'Rek Atas Nama',
            'Layak PIP', 'Alasan PIP', 'Kebutuhan Khusus', 'Sekolah Asal',
            'Anak Ke', 'Lintang', 'Bujur', 'No KK', 'BB', 'TB', 'Lingkar Kepala',
            'Jml Saudara', 'Jarak Rumah', 
            'Nama Ayah', 'Thn Lahir Ayah', 'Pendidikan Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah', 'NIK Ayah',
            'Nama Ibu', 'Thn Lahir Ibu', 'Pendidikan Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu', 'NIK Ibu',
            'Nama Wali', 'Thn Lahir Wali', 'Pendidikan Wali', 'Pekerjaan Wali', 'Penghasilan Wali', 'NIK Wali',
            'Telp Wali' // Kolom Baru
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NIPD
            'B' => NumberFormat::FORMAT_TEXT, // NISN
            'J' => NumberFormat::FORMAT_TEXT, // NIK (Geser karena ada 2 kolom baru sebelumnya)
            'AR' => NumberFormat::FORMAT_TEXT, // No KK
        ];
    }

    public function map($siswa): array
    {
        $d = $siswa->detail;

        // Helper untuk mencegah Scientific Number di Excel
        $forceString = fn($val) => !empty($val) ? "'" . $val : '';

        return [
            $forceString($siswa->nipd),
            $forceString($siswa->nisn),
            $siswa->nama_siswa,
            $siswa->jenis_kelamin,
            $siswa->tingkat,
            $siswa->status,
            optional($siswa->kelas)->nama_kelas,
            
            // KOLOM BARU 1 & 2
            $d->kelas_awal ?? '',
            $d->tgl_masuk ?? '',

            // DETAIL SISWA
            $forceString($d->nik ?? ''),
            $d->tempat_lahir ?? '',
            $d->tanggal_lahir ?? '',
            $d->agama ?? '',
            $d->alamat ?? '',
            $d->rt ?? '',
            $d->rw ?? '',
            $d->dusun ?? '',
            $d->kelurahan ?? '',
            $d->kecamatan ?? '',
            $forceString($d->kode_pos ?? ''),
            $forceString($d->telepon ?? ''),
            $forceString($d->no_hp ?? ''),
            $d->email ?? '',
            $forceString($d->skhun ?? ''),
            $d->penerima_kps ?? '',
            $forceString($d->no_kps ?? ''),
            $forceString($d->no_peserta_ujian_nasional ?? ''),
            $forceString($d->no_seri_ijazah ?? ''),
            $d->penerima_kip ?? '',
            $forceString($d->no_kip ?? ''),
            $d->nama_kip ?? '',
            $forceString($d->no_kks ?? ''),
            $forceString($d->no_regis_akta_lahir ?? ''),
            $d->bank ?? '',
            $forceString($d->no_rek_bank ?? ''),
            $d->rek_atas_nama ?? '',
            $d->layak_pip_usulan ?? '',
            $d->alasan_layak_pip ?? '',
            $d->kebutuhan_khusus ?? '',
            $d->sekolah_asal ?? '',
            $d->anak_ke_berapa ?? '',
            $d->lintang ?? '',
            $d->bujur ?? '',
            $forceString($d->no_kk ?? ''),
            $d->bb ?? '',
            $d->tb ?? '',
            $d->lingkar_kepala ?? '',
            $d->jml_saudara_kandung ?? '',
            $d->jarak_rumah ?? '',
            
            // DATA AYAH
            $d->nama_ayah ?? '',
            $d->tahun_lahir_ayah ?? '',
            $d->jenjang_pendidikan_ayah ?? '',
            $d->pekerjaan_ayah ?? '',
            $d->penghasilan_ayah ?? '',
            $forceString($d->nik_ayah ?? ''),

            // DATA IBU
            $d->nama_ibu ?? '',
            $d->tahun_lahir_ibu ?? '',
            $d->jenjang_pendidikan_ibu ?? '',
            $d->pekerjaan_ibu ?? '',
            $d->penghasilan_ibu ?? '',
            $forceString($d->nik_ibu ?? ''),

            // DATA WALI
            $d->nama_wali ?? '',
            $d->tahun_lahir_wali ?? '',
            $d->jenjang_pendidikan_wali ?? '',
            $d->pekerjaan_wali ?? '',
            $d->penghasilan_wali ?? '',
            $forceString($d->nik_wali ?? ''),

            // KOLOM BARU 3
            $forceString($d->telp_wali ?? ''),
        ];
    }
}