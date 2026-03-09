<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PklTempat;
use App\Models\Guru;
use Carbon\Carbon;

class PklTempatSeeder extends Seeder
{
    /**
     * Run the database seeds. with php artisan db:seed --class=PklTempatSeeder
     */
    public function run(): void
    {
        // Mencari id_guru yang paling kecil (pertama) di tabel guru
        $guru_id_terkecil = Guru::orderBy('id_guru', 'asc')->value('id_guru');
        
        // Jika tabel guru kebetulan kosong, gunakan angka 1 sebagai default
        $guruId = $guru_id_terkecil ?? 1;

        $data_dummy = [
            [
                'guru_id'            => $guruId,
                'nama_perusahaan'    => 'PT. Telkom Indonesia (Witel Semarang)',
                'bidang_usaha'       => 'Telekomunikasi & Jaringan',
                'nama_pimpinan'      => 'Budi Santoso, S.T.',
                'alamat_perusahaan'  => 'Jl. Pahlawan No. 10, Kota Semarang',
                'kota'               => 'Semarang',
                'no_telp_perusahaan' => '0241234567',
                'email_perusahaan'   => 'hrd@telkom.semarang.co.id',
                'no_surat_mou'       => 'MOU/TLK/2026/001',
                'tanggal_mou'        => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'nama_instruktur'    => 'Agus Pratama, M.Kom.',
                'no_telp_instruktur' => '081234567890',
                'is_active'          => true,
                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ],
            [
                'guru_id'            => $guruId,
                'nama_perusahaan'    => 'CV. Lensa Creative Studio',
                'bidang_usaha'       => 'Multimedia & Broadcasting',
                'nama_pimpinan'      => 'Ahmad Zaki',
                'alamat_perusahaan'  => 'Jl. Imam Bonjol No. 45, Kota Semarang',
                'kota'               => 'Semarang',
                'no_telp_perusahaan' => '0247654321',
                'email_perusahaan'   => 'info@lensacreative.com',
                'no_surat_mou'       => 'MOU/LC/2026/012',
                'tanggal_mou'        => Carbon::now()->subMonths(5)->format('Y-m-d'),
                'nama_instruktur'    => 'Rina Melati',
                'no_telp_instruktur' => '082345678901',
                'is_active'          => true,
                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ],
            [
                'guru_id'            => $guruId,
                'nama_perusahaan'    => 'PT. Astra Honda Motor Cabang',
                'bidang_usaha'       => 'Otomotif',
                'nama_pimpinan'      => 'Ir. Hendra Wijaya',
                'alamat_perusahaan'  => 'Jl. Jenderal Sudirman No. 102, Kota Semarang',
                'kota'               => 'Semarang',
                'no_telp_perusahaan' => '0243344556',
                'email_perusahaan'   => 'contact@astra.honda.smg.id',
                'no_surat_mou'       => 'MOU/AHM/2026/045',
                'tanggal_mou'        => Carbon::now()->subMonths(10)->format('Y-m-d'),
                'nama_instruktur'    => 'Dedi Saputra',
                'no_telp_instruktur' => '083456789012',
                'is_active'          => true,
                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ],
            [
                'guru_id'            => $guruId,
                'nama_perusahaan'    => 'Bank Jateng Cabang Utama',
                'bidang_usaha'       => 'Perbankan & Akuntansi',
                'nama_pimpinan'      => 'Supriyono, S.E., M.Si.',
                'alamat_perusahaan'  => 'Jl. Pemuda No. 142, Kota Semarang',
                'kota'               => 'Semarang',
                'no_telp_perusahaan' => '0245566778',
                'email_perusahaan'   => 'magang@bankjateng.co.id',
                'no_surat_mou'       => 'MOU/BJT/2026/088',
                'tanggal_mou'        => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'nama_instruktur'    => 'Siti Aminah, S.Ak.',
                'no_telp_instruktur' => '084567890123',
                'is_active'          => true,
                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ],
            [
                'guru_id'            => $guruId,
                'nama_perusahaan'    => 'Hotel Ciputra Semarang',
                'bidang_usaha'       => 'Perhotelan & Pariwisata',
                'nama_pimpinan'      => 'Michael Smith',
                'alamat_perusahaan'  => 'Simpang Lima, PO BOX 1288, Kota Semarang',
                'kota'               => 'Semarang',
                'no_telp_perusahaan' => '0248449888',
                'email_perusahaan'   => 'hrd.semarang@hotelciputra.com',
                'no_surat_mou'       => 'MOU/HCS/2026/112',
                'tanggal_mou'        => Carbon::now()->subMonths(12)->format('Y-m-d'),
                'nama_instruktur'    => 'Chef Junaedi',
                'no_telp_instruktur' => '085678901234',
                'is_active'          => true,
                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ],
            [
                'guru_id'            => $guruId,
                'nama_perusahaan'    => 'Campus Creative Digital',
                'bidang_usaha'       => 'Web Development & SEO',
                'nama_pimpinan'      => 'CEO Campus Creative',
                'alamat_perusahaan'  => 'Kawasan Undip Tembalang, Kota Semarang',
                'kota'               => 'Semarang',
                'no_telp_perusahaan' => '0249988776',
                'email_perusahaan'   => 'hello@campuscreative.id',
                'no_surat_mou'       => 'MOU/CC/2026/001',
                'tanggal_mou'        => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'nama_instruktur'    => 'Developer Senior',
                'no_telp_instruktur' => '086789012345',
                'is_active'          => true,
                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ]
        ];

        // Masukkan data ke dalam database
        PklTempat::insert($data_dummy);

        $this->command->info('Berhasil menambahkan 6 data dummy Tempat PKL!');
    }
}