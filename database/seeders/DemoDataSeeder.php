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
            
            // 1. Tentukan Gender Faker agar nama sinkron
            $fakerGender = $siswa->jenis_kelamin == 'L' ? 'male' : 'female';
            
            $namaSamaran = $faker->name($fakerGender);
            $nisnSamaran = $faker->unique()->numerify('00########');
            $nipdSamaran = $faker->unique()->numerify('#####');

            // 2. Update Tabel Siswa Induk
            DB::table('siswa')
                ->where('id_siswa', $siswa->id_siswa)
                ->update([
                    'nama_siswa' => $namaSamaran,
                    'nisn'       => $nisnSamaran,
                    'nipd'       => $nipdSamaran,
                ]);

            // 3. Update Tabel Detail Siswa (Dihanguskan untuk data finansial/sensitif)
            DB::table('detail_siswa')
                ->where('id_siswa', $siswa->id_siswa)
                ->update([
                    // Data Diacak
                    'tempat_lahir'              => $faker->city(),
                    'tanggal_lahir'             => $faker->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
                    'alamat'                    => $faker->streetAddress(),
                    'kelurahan'                 => 'Kel. ' . $faker->word(),
                    'kecamatan'                 => 'Kec. ' . $faker->word(),
                    'rt'                        => $faker->numerify('0#'),
                    'rw'                        => $faker->numerify('0#'),
                    'dusun'                     => 'Dusun ' . $faker->word(),
                    
                    // Data Orang Tua Diacak
                    'nama_ayah'                 => $faker->name('male'),
                    'pekerjaan_ayah'            => $faker->randomElement(['Wiraswasta', 'PNS', 'Karyawan Swasta', 'Buruh']),
                    'nama_ibu'                  => $faker->name('female'),
                    'pekerjaan_ibu'             => $faker->randomElement(['Ibu Rumah Tangga', 'Guru', 'Wiraswasta']),
                    'nama_wali'                 => '-', // Dihanguskan
                    
                    // Data Sensitif Dihanguskan (Diisi tanda strip atau dinullkan)
                    'nik'                       => '-',
                    'no_kk'                     => '-',
                    'skhun'                     => '-',
                    'no_peserta_ujian_nasional' => '-',
                    'no_seri_ijazah'            => '-',
                    'no_regis_akta_lahir'       => '-',
                    'bank'                      => '-',
                    'no_rek_bank'               => '-',
                    'rek_atas_nama'             => '-',
                    'penerima_kps'              => 'Tidak',
                    'no_kps'                    => '-',
                    'penerima_kip'              => 'Tidak',
                    'no_kip'                    => '-',
                    'nama_kip'                  => '-',
                    'no_kks'                    => '-',
                    'nik_ayah'                  => '-',
                    'nik_ibu'                   => '-',
                    'nik_wali'                  => '-',
                    'penghasilan_ayah'          => '-',
                    'penghasilan_ibu'           => '-',
                    'penghasilan_wali'          => '-',
                ]);

            // 4. Update Akun User terkait
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
            
            // 1. Tentukan Gender Faker
            $fakerGender = $guru->jenis_kelamin == 'L' ? 'male' : 'female';
            $gelar = $faker->randomElement(['S.Pd.', 'S.Kom.', 'S.T.', 'M.Pd.']);
            $namaSamaran = $faker->firstName($fakerGender) . ' ' . $faker->lastName($fakerGender) . ', ' . $gelar;
            
            $nipSamaran = $faker->unique()->numerify('198#######200#####');
            $nuptkSamaran = $faker->unique()->numerify('################');

            // 2. Update Tabel Guru Induk
            DB::table('guru')
                ->where('id_guru', $guru->id_guru)
                ->update([
                    'nama_guru' => $namaSamaran,
                    'nip'       => $nipSamaran,
                    'nuptk'     => $nuptkSamaran,
                ]);

            // 3. Update Tabel Detail Guru
            DB::table('detail_guru')
                ->where('id_guru', $guru->id_guru)
                ->update([
                    // Data Diacak
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
                    'email'                 => $faker->unique()->safeEmail(),
                    
                    // Data Sensitif Dihanguskan
                    'nik'                   => '-',
                    'no_kk'                 => '-',
                    'bank'                  => '-',
                    'norek_bank'            => '-',
                    'nama_rek'              => '-',
                    'npwp'                  => '-',
                    'nama_wajib_pajak'      => '-',
                    'nama_ibu_kandung'      => '-',
                    'status_perkawinan'     => '-',
                    'nama_suami_istri'      => '-',
                    'nip_suami_istri'       => '-',
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
                    'nuks'                  => '-',
                    'lisensi_kepsek'        => '-',
                    'diklat_kepengawasan'   => '-',
                    'keahlian_braille'      => '-',
                    'keahlian_isyarat'      => '-',
                ]);

            // 4. Update Akun User terkait
            if (isset($guru->id_user) && $guru->id_user != null) {
                DB::table('users')
                    ->where('id', $guru->id_user)
                    ->update([
                        'name'  => $namaSamaran,
                    ]);
            }
        }
        $this->command->info('Data Guru selesai!');
        $this->command->info('Semua proses Data Masking berhasil dieksekusi!');
    }
}