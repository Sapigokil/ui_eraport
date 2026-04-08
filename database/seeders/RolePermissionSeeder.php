<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset Cache Permission (WAJIB)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // JANGAN ADA TRUNCATE DI SINI AGAR DATA USER AMAN

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
                'setting.menu' => 'Akses Penuh Seluruh Menu Pengaturan (Erapor, PKL, User, Role)', 
            ],

            // H. SYSTEM SETTINGS (Siswa Only)
            '08. Menu Siswa' => [
                'siswa.menu' => 'Akses bagi siswa untuk melihat data diri, nilai, rapor, dll di portal siswa', 
            ],
        ];

        // Kumpulkan nama permission untuk verifikasi (Opsional: menghapus permission lama)
        $allPermissionNames = [];

        foreach ($permissions as $group => $perms) {
            foreach ($perms as $permName => $permLabel) {
                Permission::updateOrCreate(
                    ['name' => $permName], // Cek berdasarkan name
                    [
                        'group_name' => $group, // Simpan Group
                        'label'      => $permLabel // Simpan Label
                    ]
                );
                $allPermissionNames[] = $permName;
            }
        }

        // CLEANUP: Hapus permission di database yang sudah Anda hapus dari list array di atas
        Permission::whereNotIn('name', $allPermissionNames)->delete();

        // ====================================================
        // 4. BUAT / UPDATE ROLE SPESIAL (Gunakan firstOrCreate)
        // ====================================================
        
        $roleDev = Role::firstOrCreate(['name' => 'developer']);
        $roleDev->syncPermissions(Permission::all());
        
        // --- ROLE 1: ADMIN ERAPOR (FULL AKSES) ---
        $roleAdmin = Role::firstOrCreate(['name' => 'admin_erapor']);
        $roleAdmin->syncPermissions(Permission::all()); 

        // --- ROLE 2: GURU ERAPOR (BACKUP / SUPER GURU) ---
        $roleGuruErapor = Role::firstOrCreate(['name' => 'guru_erapor']);
        $roleGuruErapor->syncPermissions([
            'dashboard.view',
            'nilai.menu', 'nilai.input',
            'ekskul.menu', 
            'pkl.nilai.menu',
        ]);

        // --- Role 2a: GURU EKSTRAKURIKULER (Opsional) ---
        $roleGuruEkskul = Role::firstOrCreate(['name' => 'guru_ekskul']);
        $roleGuruEkskul->syncPermissions([
            'dashboard.view',
            'ekskul.menu',
        ]);

        // --- ROLE 3: GURU REGULER (Standar) ---
        $roleGuru = Role::firstOrCreate(['name' => 'guru']);
        $roleGuru->syncPermissions([
            'dashboard.view',
            'nilai.menu', 'nilai.input',
            'ekskul.menu', 
            'pkl.nilai.menu',
        ]);

        // --- ROLE 4: SISWA (BACKUP / SUPER SISWA) ---
        $roleSiswaErapor = Role::firstOrCreate(['name' => 'siswa_erapor']);
        $roleSiswaErapor->syncPermissions([
            'dashboard.view',
            'siswa.menu', 
        ]);
        
        // --- ROLE 4b: SISWA ---
        $roleSiswa = Role::firstOrCreate(['name' => 'siswa']);
        $roleSiswa->syncPermissions([
            'dashboard.view',
            'siswa.menu', 
        ]);


        // ====================================================
        // 5. BUAT USER SPESIAL OTOMATIS (firstOrCreate)
        // ====================================================
        
        // USER 0: DEVELOPER (AKUN DARURAT)
        $dev = User::firstOrCreate(
            ['username' => 'dev.campus'], 
            [
                'name'      => 'System Core', 
                'email'     => 'campus@dev.id',
                'password'  => Hash::make('campussolusi26#'), 
                'role'      => 'developer',
            ]
        );
        $dev->syncRoles([$roleDev]);
        
        $this->command->info('Akun Developer Hidden berhasil diupdate!');
        
        // USER 1: ADMIN ERAPOR
        $adminUser = User::firstOrCreate(
            ['username' => 'admin.erapor'], 
            [
                'name'      => 'Administrator E-Rapor',
                'email'     => 'admin@smkn1salatiga.sch.id',
                'password'  => Hash::make('adminerapor#'), 
                'role'      => 'admin_erapor', 
            ]
        );
        $adminUser->syncRoles([$roleAdmin]);

        // USER 2: GURU ERAPOR (BACKUP)
        $guruUser = User::firstOrCreate(
            ['username' => 'guru.erapor'], 
            [
                'name'      => 'Guru E-Rapor (Backup)',
                'email'     => 'guru@smkn1salatiga.sch.id',
                'password'  => Hash::make('gurusmkn1'),
                'role'      => 'guru_erapor',
            ]
        );
        $guruUser->syncRoles([$roleGuruErapor]);

         // USER 2b: GURU EKSKUL (BACKUP)
        $guruEkskulUser = User::firstOrCreate(
            ['username' => 'guru.ekskul'], 
            [
                'name'      => 'Guru Ekskul E-Rapor (Backup)',
                'email'     => 'ekskul@smkn1salatiga.sch.id',
                'password'  => Hash::make('ekskulsmkn1'),
                'role'      => 'guru_ekskul',
            ]
        );
        $guruEkskulUser->syncRoles([$roleGuruEkskul]);

        // USER 3: SISWA (BACKUP / SUPER SISWA)
        $siswaUser = User::firstOrCreate(
            ['username' => 'siswa.erapor'], 
            [
                'name'      => 'Siswa E-Rapor (Backup)',
                'email'     => 'siswa@smkn1salatiga.sch.id',
                'password'  => Hash::make('siswasmkn1'),
                'role'      => 'siswa_erapor',
            ]
        );
        $siswaUser->syncRoles([$roleSiswaErapor]);

        $this->command->info('SUKSES! Permission dan Role telah di-sync tanpa menghapus data user.');
    }
}