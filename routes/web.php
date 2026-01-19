<?php

use Illuminate\Support\Facades\Route;

// ==============================================================================
// 1. IMPORT CONTROLLERS
// ==============================================================================
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

// Master Data
use App\Http\Controllers\InfoSekolahController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\PembelajaranController;
use App\Http\Controllers\MasterEkskulController;
use App\Http\Controllers\PesertaEkskulController;

// Nilai & Rapor
use App\Http\Controllers\SumatifController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\NilaiAkhirController;
use App\Http\Controllers\CatatanController;
use App\Http\Controllers\RaporController;
use App\Http\Controllers\LedgerController;

// Settings
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SetKokurikulerController;
use App\Http\Controllers\BobotNilaiController;
use App\Http\Controllers\InputController;
use App\Http\Controllers\SeasonController;

/*
|--------------------------------------------------------------------------
| Web Routes (E-RAPOR CORPORATE)
|--------------------------------------------------------------------------
*/

// ==============================================================================
// 1. AUTHENTICATION & GUEST ROUTES
// ==============================================================================
Route::group(['middleware' => 'guest'], function () {
    Route::get('/', fn () => redirect('/sign-in'));
    
    // Login & Register
    Route::get('/sign-in', [LoginController::class, 'create'])->name('sign-in');
    Route::post('/sign-in', [LoginController::class, 'store']);
    
    Route::get('/sign-up', [RegisterController::class, 'create'])->name('sign-up');
    Route::post('/sign-up/check', [RegisterController::class, 'checkUser'])->name('sign-up.check');
    Route::post('/sign-up', [RegisterController::class, 'store'])->name('sign-up.store');
    Route::put('/sign-up/update', [RegisterController::class, 'updateAccount'])->name('sign-up.update');

    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');


// ==============================================================================
// 2. MAIN APPLICATION ROUTES (Require Login)
// ==============================================================================
Route::middleware(['auth'])->group(function () {

    // --- DASHBOARD (All Authenticated Users) ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('can:dashboard.view');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Event Dashboard (Store/Update)
    Route::post('/dashboard/event/store', [DashboardController::class, 'storeEvent'])->name('dashboard.event.store');
    Route::put('/dashboard/event/{id}', [DashboardController::class, 'update'])->name('dashboard.event.update');
    Route::delete('/dashboard/event/{id}', [DashboardController::class, 'destroy'])->name('dashboard.event.destroy');


    // ==========================================================================
    // MODULE: DATA UTAMA / MASTER DATA 
    // Permission: master.view (Hanya Admin / Admin Erapor)
    // ==========================================================================
    Route::group(['prefix' => 'master/data', 'as' => 'master.', 'middleware' => ['can:master.view']], function () {
        
        // Sekolah
        Route::get('/sekolah', [InfoSekolahController::class, 'infoSekolah'])->name('sekolah.index');
        Route::post('/sekolah', [InfoSekolahController::class, 'update_info_sekolah'])->name('sekolah.update');
        
        // Guru
        Route::resource('guru', GuruController::class)->names('guru')->parameters(['guru' => 'guru']);
        Route::post('guru/import', [GuruController::class, 'importCsv'])->name('guru.import');
        Route::post('guru/import/xlsx', [GuruController::class, 'importXlsx'])->name('guru.import.xlsx');
        Route::get('guru/export/pdf', [GuruController::class, 'exportPdf'])->name('guru.export.pdf');
        Route::get('guru/export/csv', [GuruController::class, 'exportCsv'])->name('guru.export.csv');
        
        // Siswa
        Route::resource('siswa', SiswaController::class)->names('siswa')->parameters(['siswa' => 'siswa']);
        Route::get('siswa/export/pdf', [SiswaController::class, 'exportPdf'])->name('siswa.export.pdf');
        Route::get('siswa/export/csv', [SiswaController::class, 'exportCsv'])->name('siswa.export.csv');
        Route::post('siswa/import/csv', [SiswaController::class, 'importCsv'])->name('siswa.import.csv');
        Route::post('siswa/import/xlsx', [SiswaController::class, 'importXlsx'])->name('siswa.import.xlsx');

        // Kelas
        Route::resource('kelas', KelasController::class)->names('kelas')->parameters(['kelas' => 'id_kelas']); 
        Route::get('kelas/export/pdf', [KelasController::class, 'exportPdf'])->name('kelas.export.pdf');
        Route::get('kelas/export/csv', [KelasController::class, 'exportCsv'])->name('kelas.export.csv');
        Route::get('kelas/{id_kelas}/export/single', [KelasController::class, 'exportKelas'])->name('kelas.export.single');
        Route::get('kelas/{id_kelas}/anggota', [KelasController::class, 'anggota'])->name('kelas.anggota');
        Route::delete('kelas/anggota/{id_siswa}', [KelasController::class, 'hapusAnggota'])->name('kelas.anggota.delete');

        // Mapel
        Route::resource('mapel', MapelController::class)->names('mapel')->parameters(['mapel' => 'id_mapel']);

        // Pembelajaran
        Route::prefix('pembelajaran')->name('pembelajaran.')->group(function () {
            Route::get('/', [PembelajaranController::class, 'dataPembelajaran'])->name('index'); 
            Route::get('/create', [PembelajaranController::class, 'create'])->name('create');
            Route::get('/{id}/edit', [PembelajaranController::class, 'edit'])->name('edit');
            Route::post('/', [PembelajaranController::class, 'store'])->name('store');
            Route::match(['put', 'patch'], '/{id}', [PembelajaranController::class, 'update'])->name('update');
            Route::delete('/{id}', [PembelajaranController::class, 'destroy'])->name('destroy');
            Route::get('/export/pdf', [PembelajaranController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/csv', [PembelajaranController::class, 'exportCsv'])->name('export.csv');
        });

        // Ekstrakurikuler
        Route::prefix('ekskul')->group(function () {
            Route::prefix('list')->name('ekskul.list.')->group(function () {
                Route::get('/', [MasterEkskulController::class, 'index'])->name('index'); 
                Route::get('/create', [MasterEkskulController::class, 'create'])->name('create');
                Route::post('/', [MasterEkskulController::class, 'store'])->name('store');
                Route::get('/{id_ekskul}/edit', [MasterEkskulController::class, 'edit'])->name('edit');
                Route::match(['put', 'patch'], '/{id_ekskul}', [MasterEkskulController::class, 'update'])->name('update');
                Route::delete('/{id_ekskul}', [MasterEkskulController::class, 'destroy'])->name('destroy');
            });
            Route::prefix('peserta')->name('ekskul.siswa.')->group(function () {
                Route::get('/', [PesertaEkskulController::class, 'index'])->name('index');
                Route::get('/create', [PesertaEkskulController::class, 'create'])->name('create');
                Route::post('/', [PesertaEkskulController::class, 'store'])->name('store');
                Route::get('/{id_ekskul_siswa}/edit', [PesertaEkskulController::class, 'edit'])->name('edit');
                Route::match(['put', 'patch'], '/{id_ekskul_siswa}', [PesertaEkskulController::class, 'update'])->name('update');
                Route::delete('/{id_ekskul_siswa}', [PesertaEkskulController::class, 'destroy'])->name('destroy');
            });
        });
    });


    // ==========================================================================
    // MODULE: PENILAIAN / INPUT NILAI
    // Permission: nilai.view (Guru & Admin Erapor)
    // ==========================================================================
    Route::group(['prefix' => 'master/nilai', 'as' => 'master.', 'middleware' => ['can:nilai-view']], function () {
        
        // 1. Sumatif
        Route::group(['prefix' => 'sumatif', 'as' => 'sumatif.', 'controller' => SumatifController::class], function () {
            Route::get('s1', 'sumatif1')->name('s1'); 
            Route::get('s2', 'sumatif2')->name('s2'); 
            Route::get('s3', 'sumatif3')->name('s3'); 
            Route::get('s4', 'sumatif4')->name('s4'); 
            Route::get('s5', 'sumatif5')->name('s5'); 
            Route::get('get-mapel/{id_kelas}', 'getMapelByKelas')->name('get_mapel');
            Route::get('download-template', 'downloadTemplate')->name('download');
            
            // Aksi Simpan butuh permission 'nilai.input'
            Route::middleware('can:nilai-input')->group(function() {
                Route::post('simpan', 'simpan')->name('store');
                Route::post('import', 'import')->name('import'); 
            });
        });

        // 2. Project P5
        Route::group(['prefix' => 'project', 'as' => 'project.', 'controller' => ProjectController::class], function () {
            Route::get('/', 'index')->name('index'); 
            Route::get('/download-template', 'downloadTemplate')->name('download'); 
            Route::get('get-mapel/{id_kelas}', 'getMapelByKelas')->name('get_mapel');

            Route::middleware('can:nilai-input')->group(function() {
                Route::post('simpan', 'simpan')->name('store'); 
                Route::post('/import', 'import')->name('import');
            });
        });

        // 3. Nilai Akhir
        Route::group(['prefix' => 'akhir', 'as' => 'nilaiakhir.', 'controller' => NilaiAkhirController::class], function () {
            Route::get('/', 'index')->name('index');
            Route::post('hitung', 'hitung')->name('hitung')->middleware('can:nilai.input');
        });

        // 4. Catatan Wali Kelas
        Route::group(['prefix' => 'catatan', 'as' => 'catatan.', 'controller' => CatatanController::class], function () {
            Route::get('/input', 'inputCatatan')->name('input');
            Route::get('/template', 'downloadTemplate')->name('template');
            Route::get('/get-siswa/{id_kelas}', 'getSiswa')->name('getSiswa');
            
            Route::middleware('can:nilai.input')->group(function() {
                Route::post('/simpan', 'simpanCatatan')->name('simpan');
                Route::post('/import', 'importExcel')->name('import');
            });
        });
    });


    // ==========================================================================
    // MODULE: LAPORAN & RAPOR 
    // Permission: rapor.view (Guru & Admin Erapor)
    // ==========================================================================
    Route::group(['prefix' => 'rapor', 'as' => 'rapornilai.', 'middleware' => ['can:rapor.view']], function () {
        
        // Monitoring
        Route::post('/sinkronkan', [RaporController::class, 'sinkronkanKelas'])->name('sinkronkan');
        Route::post('/sinkronkan-kelas', [RaporController::class, 'sinkronkanKelas'])->name('sinkronkan_kelas');
        Route::get('/detail-siswa', [RaporController::class, 'getDetailSiswa'])->name('detail_siswa');
        Route::get('/detail-progress', [RaporController::class, 'getDetailProgress'])->name('detail_progress');
        
        // Cetak
        Route::get('/cetak', [RaporController::class, 'cetakIndex'])->name('cetak');
        
        // Aksi Download (Butuh permission cetak)
        Route::middleware('can:rapor.cetak')->group(function() {
            Route::get('/print/{id_siswa}', [RaporController::class, 'cetak_proses'])->name('cetak_proses');
            Route::get('/cetak-massal', [RaporController::class, 'cetak_massal'])->name('cetak_massal');
            Route::get('/download-satuan/{id_siswa}', [RaporController::class, 'download_satuan'])->name('download_satuan');
            Route::get('/download-massal', [RaporController::class, 'download_massal'])->name('download_massal');
            Route::get('/download-massal-pdf', [RaporController::class, 'download_massal_pdf'])->name('download_massal_pdf');
        });
    });

    // MODULE: LEDGER
    Route::group(['prefix' => 'ledger', 'as' => 'ledger.', 'middleware' => ['can:ledger.view']], function () {
        Route::get('/data-nilai', [LedgerController::class, 'index'])->name('ledger_index');
        
        Route::middleware('can:cetak-print-ledger')->group(function() {
            Route::get('/export/excel', [LedgerController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [LedgerController::class, 'exportPdf'])->name('export.pdf');
        });
    });


    // ==========================================================================
    // MODULE: PENGATURAN / SETTINGS
    // ==========================================================================
    Route::group(['prefix' => 'settings', 'as' => 'settings.', 'middleware' => ['auth']], function () {

        // A. SETTING SYSTEM (User & Permission - Admin Only)
        Route::group(['prefix' => 'system', 'as' => 'system.'], function () {
            
            // Users Granular
            Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
                Route::get('/', 'index')->name('index')->middleware('can:users.read');
                Route::get('/create', 'create')->name('create')->middleware('can:users.create');
                Route::post('/', 'store')->name('store')->middleware('can:users.create');
                Route::get('/{user}/edit', 'edit')->name('edit')->middleware('can:users.update');
                Route::put('/{user}', 'update')->name('update')->middleware('can:users.update');
                Route::delete('/{user}', 'destroy')->name('destroy')->middleware('can:users.delete');
            });

            // Roles
            Route::resource('roles', RoleController::class)->middleware('can:roles.read');
        });

        // B. SETTING ERAPOR (Akademik - Admin Erapor Only)
        Route::group(['prefix' => 'erapor', 'as' => 'erapor.', 'middleware' => ['can:settings.erapor.read']], function () {
            
            // Kokurikuler
            Route::controller(SetKokurikulerController::class)->prefix('kokurikuler')->name('kok.')->group(function () {
                Route::get('/', 'index')->name('index'); 
                Route::middleware('can:settings.erapor.update')->group(function() {
                    Route::post('/', 'store')->name('store');
                    Route::put('/{id}', 'update')->name('update');
                    Route::delete('/{id}', 'destroy')->name('destroy');
                    Route::patch('/{id}/toggle', 'toggleStatus')->name('toggle');
                });
            });

            // Bobot Nilai
            Route::controller(BobotNilaiController::class)->prefix('bobot-nilai')->name('bobot.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::middleware('can:settings.erapor.update')->group(function() {
                    Route::post('/', 'store')->name('store');
                    Route::put('/{id}', 'update')->name('update');
                    Route::delete('/{id}', 'destroy')->name('destroy');
                });
            });

            // MODULE: SEASON
            Route::prefix('season')->middleware(['auth'])->group(function () {
                Route::get('/', [SeasonController::class, 'index'])->name('season.index');
                Route::post('/store', [SeasonController::class, 'store'])->name('season.store');
                Route::put('/{id}', [SeasonController::class, 'update'])->name('season.update');
                Route::put('/settings/erapor/season/{id}', [SeasonController::class, 'update'])->name('settings.erapor.season.update');
                Route::delete('/{id}', [SeasonController::class, 'destroy'])->name('season.destroy');
            });

            // Input Event
            Route::controller(InputController::class)->prefix('event')->name('input.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::middleware('can:settings.erapor.update')->group(function() {
                    Route::post('/store', 'store')->name('store');
                    Route::put('/event/{id}', 'updateEvent')->name('event.update');
                    Route::put('/notifikasi/{id}', 'updateNotifikasi')->name('notifikasi.update');
                    Route::delete('/event/{id}', 'destroyEvent')->name('event.delete');
                    Route::delete('/notifikasi/{id}', 'destroyNotifikasi')->name('notifikasi.delete');
                });
            });
        });
    });

});