<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset Cache Permission (WAJIB)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. BERSIHKAN DATA LAMA (TRUNCATE)
        Schema::disableForeignKeyConstraints();
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        // ====================================================
        // 3. DEFINISI PERMISSION (HCMS STYLE - MODULAR)
        // ====================================================
        $permissions = [
            // A. DASHBOARD
            '01. Dashboard (Semua)' => [
                'dashboard.view' => 'Melihat Halaman Dashboard Utama',
            ],

            // B. DATA POKOK (Admin Only)
            '02. Master Data Pokok' => [
                'master.menu'    => 'Akses Menu Master Data (Sekolah, Guru, Siswa, dll)',
                'pkl.data.menu'  => 'Akses Menu Data PKL (Tempat, Pembimbing, Penempatan)',
                'mutasi.menu'    => 'Akses Menu Mutasi & Kenaikan Kelas',
            ],

            // C. AKADEMIK - INPUT NILAI (Guru & Admin)
            '03. Akademik: Input Nilai' => [
                'nilai.menu'   => 'Akses Menu Input Nilai & Tugas Wali Kelas', 
                'nilai.input'  => 'Hak Aksi (Create/Update/Delete) Data Nilai', 
            ],

            // D. AKADEMIK - EKSTRAKURIKULER (Pembina & Admin)
            '04. Akademik: Ekstrakurikuler' => [
                'ekskul.menu' => 'Akses Menu Ekstrakurikuler (Peserta & Nilai)', 
            ],
            
            // E. AKADEMIK - PENILAIAN PKL (Guru Pembimbing & Admin)
            '05. Akademik: Penilaian PKL' => [
                'pkl.nilai.menu' => 'Akses Menu Input Penilaian PKL', 
            ],

            // F. LAPORAN & RAPOR 
            '06. Laporan & Rapor' => [
                'rapor.menu'   => 'Akses Menu Rapor (Reguler & PKL)', 
                'rapor.cetak'  => 'Mencetak / Download Rapor Siswa', 
                'ledger.menu'  => 'Akses Menu Ledger Nilai', 
                'ledger.cetak' => 'Mencetak / Download Ledger Nilai', 
            ],

            // G. SYSTEM SETTINGS (Admin Only)
            '07. Pengaturan Sistem' => [
                // Menggunakan 1 Pintu sesuai permintaan
                'setting.menu' => 'Akses Penuh Seluruh Menu Pengaturan (Erapor, PKL, User, Role)', 
            ],

            // H. SYSTEM SETTINGS (Siswa Only)
            '08. Menu Siswa' => [
                // Menggunakan 1 Pintu sesuai permintaan
                'siswa.menu' => 'Akses bagi siswa untuk melihat data diri, nilai, rapor, dll di portal siswa', 
            ],
        ];

        foreach ($permissions as $group => $perms) {
            foreach ($perms as $permName => $permLabel) {
                Permission::updateOrCreate(
                    ['name' => $permName], // Cek berdasarkan name
                    [
                        'group_name' => $group, // Simpan Group
                        'label'      => $permLabel // Simpan Label
                    ]
                );
            }
        }

        // ====================================================
        // 4. BUAT ROLE SPESIAL
        // ====================================================
        
        $roleDev = Role::create(['name' => 'developer']);
        $roleDev->givePermissionTo(Permission::all());
        
        // --- ROLE 1: ADMIN ERAPOR (FULL AKSES) ---
        $roleAdmin = Role::create(['name' => 'admin_erapor']);
        $roleAdmin->givePermissionTo(Permission::all()); // Sakti mandraguna

        // --- ROLE 2: GURU ERAPOR (BACKUP / SUPER GURU) ---
        $roleGuruErapor = Role::create(['name' => 'guru_erapor']);
        $roleGuruErapor->givePermissionTo([
            'dashboard.view',
            // Data Akademik
            'nilai.menu', 'nilai.input',
            'ekskul.menu', 
            'pkl.nilai.menu',
        ]);

        // --- Role 2a: GURU EKSTRAKURIKULER (Opsional) ---
        $roleGuruEkskul = Role::create(['name' => 'guru_ekskul']);
        $roleGuruEkskul->givePermissionTo([
            'dashboard.view',
            'ekskul.menu',
        ]);

        // --- ROLE 3: GURU REGULER (Standar) ---
        $roleGuru = Role::create(['name' => 'guru']);
        $roleGuru->givePermissionTo([
            'dashboard.view',
            'nilai.menu', 'nilai.input',
            'ekskul.menu', 
            'pkl.nilai.menu',
        ]);

        // --- ROLE 4: SISWA (BACKUP / SUPER SISWA) ---
        $roleSiswa = Role::create(['name' => 'siswa_erapor']);
        $roleSiswa->givePermissionTo([
            'dashboard.view',
            'siswa.menu', // Nanti untuk akses portal siswa (profil, nilai, rapor, dll)
        ]);
        
        // --- ROLE 4b: SISWA ---
        $roleSiswa = Role::create(['name' => 'siswa']);
        $roleSiswa->givePermissionTo([
            'dashboard.view',
            'siswa.menu', // Nanti untuk akses portal siswa (profil, nilai, rapor, dll)
        ]);


        // ====================================================
        // 5. BUAT USER SPESIAL OTOMATIS
        // ====================================================
        
        // USER 0: DEVELOPER (AKUN DARURAT)
        $dev = User::firstOrCreate(
            ['username' => 'dev.campus'], // Username rahasia
            [
                'name'      => 'System Core', // Nama samaran agar terlihat teknis
                'email'     => 'campus@dev.id',
                'password'  => Hash::make('campussolusi26#'), // Password Kuat
                'role'      => 'developer',
            ]
        );
        $dev->assignRole($roleDev);
        
        $this->command->info('Akun Developer Hidden berhasil dibuat!');
        
        // USER 1: ADMIN ERAPOR
        $adminUser = User::firstOrCreate(
            ['username' => 'admin.erapor'], // Cek berdasarkan username
            [
                'name'      => 'Administrator E-Rapor',
                'username'  => 'admin.erapor',
                'email'     => 'admin@smkn1salatiga.sch.id',
                'password'  => Hash::make('adminerapor#'), // Password Default
                'role'      => 'admin_erapor', // Kolom manual (backup)
            ]
        );
        $adminUser->assignRole($roleAdmin);

        // USER 2: GURU ERAPOR (BACKUP)
        $guruUser = User::firstOrCreate(
            ['username' => 'guru.erapor'], 
            [
                'name'      => 'Guru E-Rapor (Backup)',
                'username'  => 'guru.erapor',
                'email'     => 'guru@smkn1salatiga.sch.id',
                'password'  => Hash::make('gurusmkn1'),
                'role'      => 'guru_erapor',
            ]
        );
        $guruUser->assignRole($roleGuruErapor);

         // USER 2b: GURU EKSKUL (BACKUP)
        $guruEkskulUser = User::firstOrCreate(
            ['username' => 'guru.ekskul'], 
            [
                'name'      => 'Guru Ekskul E-Rapor (Backup)',
                'username'  => 'guru.ekskul',
                'email'     => 'ekskul@smkn1salatiga.sch.id',
                'password'  => Hash::make('ekskulsmkn1'),
                'role'      => 'guru_ekskul',
            ]
        );
        $guruEkskulUser->assignRole($roleGuruEkskul);

        // USER 3: SISWA (BACKUP / SUPER SISWA)
        $siswaUser = User::firstOrCreate(
            ['username' => 'siswa.erapor'], 
            [
                'name'      => 'Siswa E-Rapor (Backup)',
                'username'  => 'siswa.erapor',
                'email'     => 'siswa@smkn1salatiga.sch.id',
                'password'  => Hash::make('siswasmkn1'),
                'role'      => 'siswa_erapor',
            ]
        );
        $siswaUser->assignRole($roleSiswa);

        $this->command->info('SUKSES! User Spesial telah dibuat:');
        $this->command->info('1. Admin: admin.erapor / adminerapor#');
        $this->command->info('2. Guru: guru.erapor / gurusmkn1');
        $this->command->info('3. Guru Ekskul: guru.ekskul / ekskulsmkn1');
        $this->command->info('4. Siswa: siswa.erapor / siswasmkn1');
    }
}