<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $this->command->info('Memulai Data Masking (Penyamaran Data) untuk Lingkungan Demo...');

        // ==========================================
        // TAHAP 1: DATA SISWA & DETAIL SISWA
        // ==========================================
        $this->command->info('Memproses tabel Siswa dan Detail Siswa...');
        
        $siswas = DB::table('siswa')->get();
        
        foreach ($siswas as $siswa) {
            
            $fakerGender = $siswa->jenis_kelamin == 'L' ? 'male' : 'female';
            $namaSamaran = $faker->name($fakerGender);
            
            // ✅ PERBAIKAN: Formula Deterministik agar dijamin 100% Unik & tidak nabrak data lama
            // Menggunakan awalan 99 + padding ID siswa
            $nisnSamaran = '99' . str_pad($siswa->id_siswa, 8, '0', STR_PAD_LEFT); // 10 digit
            $nipdSamaran = '9' . str_pad($siswa->id_siswa, 4, '0', STR_PAD_LEFT);  // 5 digit
            
            DB::table('siswa')
                ->where('id_siswa', $siswa->id_siswa)
                ->update([
                    'nama_siswa' => $namaSamaran,
                    'nisn'       => $nisnSamaran,
                    'nipd'       => $nipdSamaran,
                ]);

            DB::table('detail_siswa')
                ->where('id_siswa', $siswa->id_siswa)
                ->update([
                    'tempat_lahir'              => $faker->city(),
                    'tanggal_lahir'             => $faker->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
                    'alamat'                    => $faker->streetAddress(),
                    'kelurahan'                 => 'Kel. ' . $faker->word(),
                    'kecamatan'                 => 'Kec. ' . $faker->word(),
                    'rt'                        => $faker->numerify('0#'),
                    'rw'                        => $faker->numerify('0#'),
                    'dusun'                     => 'Dusun ' . $faker->word(),
                    
                    'nama_ayah'                 => $faker->name('male'),
                    'pekerjaan_ayah'            => $faker->randomElement(['Wiraswasta', 'PNS', 'Karyawan Swasta', 'Buruh']),
                    'nama_ibu'                  => $faker->name('female'),
                    'pekerjaan_ibu'             => $faker->randomElement(['Ibu Rumah Tangga', 'Guru', 'Wiraswasta']),
                    'nama_wali'                 => '-',
                    
                    // ✅ PERBAIKAN: Semua identitas unik di detail_siswa diamankan dengan ID
                    'nik'                       => '9999999999' . str_pad($siswa->id_siswa, 6, '0', STR_PAD_LEFT),
                    'no_kk'                     => '9999999999' . str_pad($siswa->id_siswa, 6, '0', STR_PAD_LEFT),
                    'skhun'                     => 'DN-99 ' . str_pad($siswa->id_siswa, 7, '0', STR_PAD_LEFT),
                    'no_peserta_ujian_nasional' => '9-99-99-99-9999-9999-' . substr($siswa->id_siswa, -1),
                    'no_seri_ijazah'            => 'M-M/99/' . str_pad($siswa->id_siswa, 7, '0', STR_PAD_LEFT),
                    'no_regis_akta_lahir'       => 'AL-9999' . str_pad($siswa->id_siswa, 4, '0', STR_PAD_LEFT),
                    
                    'bank'                      => '-',
                    'no_rek_bank'               => '9999' . str_pad($siswa->id_siswa, 7, '0', STR_PAD_LEFT),
                    'rek_atas_nama'             => '-',
                    
                    'penerima_kps'              => 'Tidak',
                    'no_kps'                    => 'KPS' . str_pad($siswa->id_siswa, 5, '0', STR_PAD_LEFT),
                    'penerima_kip'              => 'Tidak',
                    'no_kip'                    => 'KIP' . str_pad($siswa->id_siswa, 5, '0', STR_PAD_LEFT),
                    'nama_kip'                  => '-',
                    'no_kks'                    => 'KKS' . str_pad($siswa->id_siswa, 5, '0', STR_PAD_LEFT),
                    
                    'nik_ayah'                  => '9999999998' . str_pad($siswa->id_siswa, 6, '0', STR_PAD_LEFT),
                    'nik_ibu'                   => '9999999997' . str_pad($siswa->id_siswa, 6, '0', STR_PAD_LEFT),
                    'nik_wali'                  => '9999999996' . str_pad($siswa->id_siswa, 6, '0', STR_PAD_LEFT),
                    
                    'penghasilan_ayah'          => '-',
                    'penghasilan_ibu'           => '-',
                    'penghasilan_wali'          => '-',
                ]);

            if (isset($siswa->id_user) && $siswa->id_user != null) {
                DB::table('users')
                    ->where('id', $siswa->id_user)
                    ->update([
                        'name'     => $namaSamaran,
                        'email'    => $nisnSamaran . '@siswa.local',
                        'username' => $nisnSamaran,
                    ]);
            }
        }
        $this->command->info('Data Siswa selesai!');


        // ==========================================
        // TAHAP 2: DATA GURU & DETAIL GURU
        // ==========================================
        $this->command->info('Memproses tabel Guru dan Detail Guru...');

        $gurus = DB::table('guru')->get();
        
        foreach ($gurus as $guru) {
            
            $fakerGender = $guru->jenis_kelamin == 'L' ? 'male' : 'female';
            $gelar = $faker->randomElement(['S.Pd.', 'S.Kom.', 'S.T.', 'M.Pd.']);
            $namaSamaran = $faker->firstName($fakerGender) . ' ' . $faker->lastName($fakerGender) . ', ' . $gelar;
            
            // ✅ PERBAIKAN: Formula Deterministik untuk Guru
            $nipSamaran = '199901012026' . str_pad($guru->id_guru, 4, '0', STR_PAD_LEFT);
            $nuptkSamaran = '999999999999' . str_pad($guru->id_guru, 4, '0', STR_PAD_LEFT);

            DB::table('guru')
                ->where('id_guru', $guru->id_guru)
                ->update([
                    'nama_guru' => $namaSamaran,
                    'nip'       => $nipSamaran,
                    'nuptk'     => $nuptkSamaran,
                ]);

            DB::table('detail_guru')
                ->where('id_guru', $guru->id_guru)
                ->update([
                    'tempat_lahir'          => $faker->city(),
                    'tanggal_lahir'         => $faker->dateTimeBetween('-50 years', '-25 years')->format('Y-m-d'),
                    'alamat'                => $faker->streetAddress(),
                    'rt'                    => $faker->numerify('0#'),
                    'rw'                    => $faker->numerify('0#'),
                    'kelurahan'             => 'Kel. ' . $faker->word(),
                    'kecamatan'             => 'Kec. ' . $faker->word(),
                    'kode_pos'              => $faker->postcode(),
                    'no_hp'                 => $faker->phoneNumber(),
                    'no_telp'               => '-',
                    'email'                 => 'guru' . $guru->id_guru . '@demo.local',
                    
                    // ✅ PERBAIKAN: Kolom Unik Faker diamankan dengan ID
                    'nik'                   => '9999999999' . str_pad($guru->id_guru, 6, '0', STR_PAD_LEFT),
                    'no_kk'                 => '9999999999' . str_pad($guru->id_guru, 6, '0', STR_PAD_LEFT),
                    'norek_bank'            => '9999' . str_pad($guru->id_guru, 7, '0', STR_PAD_LEFT),
                    'npwp'                  => '99.999.999.9-999.' . str_pad($guru->id_guru, 3, '0', STR_PAD_LEFT),
                    'nip_suami_istri'       => '199901012025' . str_pad($guru->id_guru, 4, '0', STR_PAD_LEFT),
                    'nuks'                  => '9999999999' . str_pad($guru->id_guru, 6, '0', STR_PAD_LEFT),
                    
                    'bank'                  => '-',
                    'nama_rek'              => '-',
                    'nama_wajib_pajak'      => '-',
                    'nama_ibu_kandung'      => '-',
                    'status_perkawinan'     => '-',
                    'nama_suami_istri'      => '-',
                    'pekerjaan_suami_istri' => '-',
                    'sk_cpns'               => '-',
                    'sk_pengangkatan'       => '-',
                    'lembaga_pengangkatan'  => '-',
                    'pangkat_gol'           => '-',
                    'sumber_gaji'           => '-',
                    'karpeg'                => '-',
                    'karis_karsu'           => '-',
                    'lintang'               => '-',
                    'bujur'                 => '-',
                    'lisensi_kepsek'        => '-',
                    'diklat_kepengawasan'   => '-',
                    'keahlian_braille'      => '-',
                    'keahlian_isyarat'      => '-',
                ]);

            if (isset($guru->id_user) && $guru->id_user != null) {
                DB::table('users')
                    ->where('id', $guru->id_user)
                    ->update([
                        'name'  => $namaSamaran,
                    ]);
            }
        }
        $this->command->info('Data Guru selesai!');
        $this->command->info('Semua proses Data Masking berhasil dieksekusi tanpa tabrakan data!');
    }
}