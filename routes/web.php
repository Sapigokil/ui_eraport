<?php

// File: routes/web.php (KOREKSI FINAL DAN LENGKAP)

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\PdfController; 
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InfoSekolahController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\PembelajaranController;
use App\Http\Controllers\MasterEkskulController;
use App\Http\Controllers\PesertaEkskulController;
// use App\Http\Controllers\RaporNilaiController;
use App\Http\Controllers\RaporCatatanController;
use App\Http\Controllers\SumatifController;
use App\Http\Controllers\ProjectController; 
use App\Http\Controllers\NilaiAkhirController;
use App\Http\Controllers\CatatanController;
use App\Http\Controllers\SetKokurikulerController;
use App\Http\Controllers\RaporController;
use App\Http\Controllers\BobotNilaiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InputController;
use App\Http\Controllers\LedgerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// =========================================================
// 1. AUTH & DEFAULT ROUTES
// =========================================================

Route::get('/', fn () => redirect('/dashboard'))->middleware('auth');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');
Route::get('/tables', fn () => view('tables'))->name('tables')->middleware('auth');
Route::get('/wallet', fn () => view('wallet'))->name('wallet')->middleware('auth');
Route::get('/RTL', fn () => view('RTL'))->name('RTL')->middleware('auth');
// Route::get('/profile', fn () => view('account-pages.profile'))->name('profile')->middleware('auth');

//Profile User
Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

Route::get('/laravel-examples/users-management', [UserController::class, 'index'])->name('users-management.index')->middleware('auth');

Route::post('/dashboard/event/store', [DashboardController::class, 'storeEvent'])->name('dashboard.event.store');
Route::put('/dashboard/event/{id}', [DashboardController::class, 'update'])->name('dashboard.event.update');
Route::delete('/dashboard/event/{id}', [DashboardController::class, 'destroy'])->name('dashboard.event.destroy');

Route::group(['middleware' => 'guest'], function () {
    Route::get('/signin', fn () => view('account-pages.signin'))->name('signin');
    Route::get('/signup', fn () => view('account-pages.signup'))->name('signup');
    
    Route::get('/sign-up', [RegisterController::class, 'create'])->name('sign-up');
    Route::post('/sign-up', [RegisterController::class, 'store']);
    
    Route::get('/sign-in', [LoginController::class, 'create'])->name('sign-in');
    Route::post('/sign-in', [LoginController::class, 'store']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store']);
});

// 🛑 KOREKSI LOGOUT: Dipastikan ada dan global
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');


// =========================================================
// 2. GRUP ROUTE E-RAPOR: MASTER DATA & INPUT NILAI
// Prefix URL: /master-data, Nama Route: master.*
// =========================================================
Route::group(['prefix' => 'master', 'as' => 'master.', 'middleware' => 'auth'], function () {

    // A. MANAJEMEN PENGGUNA (users.index, roles.index)
    // 🛑 DIKEMBALIKAN: Ditempatkan di dalam master group 🛑
    Route::prefix('pengaturan')->middleware('can:pengaturan-manage-users')->group(function () {
        // Nama: master.roles.index
        Route::resource('roles', RoleController::class)->names('roles')->except(['show']);
        // Nama: master.users.index
        Route::resource('users', UserController::class)->names('users')->except(['show']); 
    });
    
    // B. MASTER DATA (URL: /master/data, NAMA ROUTE: master.*)
    Route::prefix('data')->middleware('can:manage-master')->group(function () {
        
        // INFO SEKOLAH (master.sekolah.index)
        Route::get('/sekolah', [InfoSekolahController::class, 'infoSekolah'])->name('sekolah.index');
        Route::post('/sekolah', [InfoSekolahController::class, 'update_info_sekolah'])->name('sekolah.update');
        
        // GURU (master.guru.*)
        Route::resource('guru', GuruController::class)->names('guru')->parameters(['guru' => 'guru']);
        Route::post('guru/import', [GuruController::class, 'importCsv'])->name('guru.import');
        Route::post('guru/import/xlsx', [GuruController::class, 'importXlsx'])->name('guru.import.xlsx');
        Route::get('guru/export/pdf', [GuruController::class, 'exportPdf'])->name('guru.export.pdf');
        Route::get('guru/export/csv', [GuruController::class, 'exportCsv'])->name('guru.export.csv');
        
        // SISWA (master.siswa.*)
        Route::resource('siswa', SiswaController::class)->names('siswa')->parameters(['siswa' => 'siswa']);
        Route::get('siswa/export/pdf', [SiswaController::class, 'exportPdf'])->name('siswa.export.pdf');
        Route::get('siswa/export/csv', [SiswaController::class, 'exportCsv'])->name('siswa.export.csv');
        Route::post('siswa/import/csv', [SiswaController::class, 'importCsv'])->name('siswa.import.csv');
        Route::post('siswa/import/xlsx', [SiswaController::class, 'importXlsx'])->name('siswa.import.xlsx');

        // KELAS (master.kelas.*)
        Route::resource('kelas', KelasController::class)->names('kelas')->parameters(['kelas' => 'id_kelas']); 
        Route::get('kelas/export/pdf', [KelasController::class, 'exportPdf'])->name('kelas.export.pdf');
        Route::get('kelas/export/csv', [KelasController::class, 'exportCsv'])->name('kelas.export.csv');
        Route::get('kelas/{id_kelas}/export/single', [KelasController::class, 'exportKelas'])->name('kelas.export.single');
        Route::get('kelas/{id_kelas}/anggota', [KelasController::class, 'anggota'])->name('kelas.anggota');
        Route::delete('kelas/anggota/{id_siswa}', [KelasController::class, 'hapusAnggota'])->name('kelas.anggota.delete');

        // MAPEL (master.mapel.*)
        Route::resource('mapel', MapelController::class)->names('mapel')->parameters(['mapel' => 'id_mapel']);

        // PEMBELAJARAN (master.pembelajaran.*)
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

        // EKSTRAKURIKULER (master.ekskul.*)
        Route::prefix('ekskul')->group(function () {
            // Master List Ekskul: master.ekskul.list.*
            Route::prefix('list')->name('ekskul.list.')->group(function () {
                Route::get('/', [MasterEkskulController::class, 'index'])->name('index'); 
                Route::get('/create', [MasterEkskulController::class, 'create'])->name('create');
                Route::post('/', [MasterEkskulController::class, 'store'])->name('store');
                Route::get('/{id_ekskul}/edit', [MasterEkskulController::class, 'edit'])->name('edit');
                Route::match(['put', 'patch'], '/{id_ekskul}', [MasterEkskulController::class, 'update'])->name('update');
                Route::delete('/{id_ekskul}', [MasterEkskulController::class, 'destroy'])->name('destroy');
            });

            // Peserta Ekskul: master.ekskul.siswa.*
            Route::prefix('peserta')->name('ekskul.siswa.')->group(function () {
                Route::get('/', [PesertaEkskulController::class, 'index'])->name('index');
                Route::get('/create', [PesertaEkskulController::class, 'create'])->name('create');
                Route::post('/', [PesertaEkskulController::class, 'store'])->name('store');
                Route::get('/{id_ekskul_siswa}/edit', [PesertaEkskulController::class, 'edit'])->name('edit');
                Route::match(['put', 'patch'], '/{id_ekskul_siswa}', [PesertaEkskulController::class, 'update'])->name('update');
                Route::delete('/{id_ekskul_siswa}', [PesertaEkskulController::class, 'destroy'])->name('destroy');
            });
        });

    }); // END GROUP MASTER DATA (URL: /master/data)


    // C. INPUT NILAI (URL: /master/nilai, NAMA ROUTE: master.*)
    Route::prefix('nilai')->middleware('can:manage-master')->group(function () {
        
        // 1. NILAI SUMATIF (master.sumatif.*)
        Route::group(['prefix' => 'sumatif', 'as' => 'sumatif.', 'controller' => SumatifController::class], function () {
            Route::get('s1', 'sumatif1')->name('s1'); 
            Route::get('s2', 'sumatif2')->name('s2'); 
            Route::get('s3', 'sumatif3')->name('s3'); 
            Route::get('get-mapel/{id_kelas}', [SumatifController::class, 'getMapelByKelas'])->name('get_mapel');
            Route::post('simpan', 'simpan')->name('store'); // master.sumatif.store
            Route::get('download-template', 'downloadTemplate')->name('download');
            Route::post('import', 'import')->name('import'); 
        });

        // 2. NILAI PROJECT (P5) (master.project.*)
        Route::group(['prefix' => 'project', 'as' => 'project.', 'controller' => ProjectController::class], function () {
            Route::get('/', 'index')->name('index'); // master.project.index
            Route::post('simpan', 'simpan')->name('store'); // master.project.simpan
            // 🛑 ROUTE BARU UNTUK IMPORT/EXPORT
            Route::get('/download-template', [ProjectController::class, 'downloadTemplate'])->name('download'); 
            Route::post('/import', [ProjectController::class, 'import'])->name('import');
            Route::get('get-mapel/{id_kelas}', [ProjectController::class, 'getMapelByKelas'])->name('get_mapel');
        });
        
        // 3. RAPOR NILAI & CATATAN WALI KELAS (master.rapornilai.*)
        // Route::prefix('rapor')->name('rapornilai.')->group(function () {
        //     Route::get('/', [RaporNilaiController::class, 'index'])->name('index'); 
        //     Route::get('/create', [RaporNilaiController::class, 'create'])->name('create');
        //     Route::post('/', [RaporNilaiController::class, 'store'])->name('store');
        //     Route::delete('/{id_rapor}', [RaporNilaiController::class, 'destroy'])->name('destroy'); 

        //     Route::prefix('wali')->name('wali.')->group(function () {
        //         Route::get('/catatan', [RaporCatatanController::class, 'inputCatatan'])->name('catatan');
        //         Route::post('/simpan', [RaporCatatanController::class, 'simpanCatatan'])->name('simpan');
        //         Route::get('/get-siswa/{id_kelas}', [RaporCatatanController::class, 'getSiswa'])->name('get_siswa'); 
        //     });
        // });

        // 4. NILAI AKHIR (master.nilaiakhir.*)
        Route::group(['prefix' => 'akhir', 'as' => 'nilaiakhir.', 'controller' => NilaiAkhirController::class], function () {
            Route::get('/', 'index')->name('index'); // master.nilaiakhir.index
            Route::post('hitung', 'hitung')->name('hitung'); // master.nilaiakhir.hitung (untuk proses generate/hitung ulang)
        });

        Route::group(['prefix' => 'catatan', 'as' => 'catatan.'], function () {
            // Route untuk halaman input utama
            Route::get('/input', [CatatanController::class, 'inputCatatan'])->name('input');
            
            // Route untuk proses simpan
            Route::post('/simpan', [CatatanController::class, 'simpanCatatan'])->name('simpan');
            
            // Route untuk Download Template Excel
            Route::get('/template', [CatatanController::class, 'downloadTemplate'])->name('template');
            
            // Route untuk Proses Import Excel
            Route::post('/import', [CatatanController::class, 'importExcel'])->name('import');

            // Route untuk dashboard progres
            // Route::get('/progress', [CatatanController::class, 'indexProgressCatatan'])->name('progress');
            
            // Route AJAX untuk ambil siswa (jika diperlukan oleh view)
            Route::get('/get-siswa/{id_kelas}', [CatatanController::class, 'getSiswa'])->name('getSiswa');
        });



    }); // END GROUP INPUT NILAI
    
    
}); // END GROUP MASTER (URL: /master)

Route::group(['prefix' => 'pengaturan', 'as' => 'pengaturan.'], function () {
    //kokurikuler
    Route::get('/kokurikuler', [SetKokurikulerController::class, 'index'])->name('kok.index');
    Route::post('/kokurikuler', [SetKokurikulerController::class, 'store'])->name('kok.store');
    Route::put('/kokurikuler/{id}', [SetKokurikulerController::class, 'update'])->name('kok.update');
    Route::delete('/kokurikuler/{id}', [SetKokurikulerController::class, 'destroy'])->name('kok.destroy');
    Route::patch('/kokurikuler/{id}/toggle', [SetKokurikulerController::class, 'toggleStatus'])->name('kok.toggle');

    //bobot nilai
    Route::get('/bobot-nilai', [BobotNilaiController::class, 'index'])->name('bobot.index');
    Route::post('/bobot-nilai', [BobotNilaiController::class, 'store'])->name('bobot.store');
    Route::put('/bobot-nilai/{id}', [BobotNilaiController::class, 'update'])->name('bobot.update');
    Route::delete('/bobot-nilai/{id}', [BobotNilaiController::class, 'destroy'])->name('bobot.destroy');


    // input event
    Route::prefix('input')->group(function () {
    // HALAMAN
    Route::get('/', [InputController::class, 'index'])->name('input.index');
    // SIMPAN (EVENT / NOTIFIKASI)
    Route::post('/store', [InputController::class, 'store'])->name('input.store');
    // UPDATE
    Route::put('/event/{id}', [InputController::class, 'updateEvent'])->name('input.event.update');
    Route::put('/notifikasi/{id}', [InputController::class, 'updateNotifikasi'])->name('input.notifikasi.update');
    // DELETE
    Route::delete('/event/{id}', [InputController::class, 'destroyEvent'])->name('input.event.delete');
    Route::delete('/notifikasi/{id}', [InputController::class, 'destroyNotifikasi'])->name('input.notifikasi.delete');

});


});

Route::group(['prefix' => 'rapor', 'as' => 'rapornilai.'], function () {
    // 1. Halaman Monitoring Utama
    // Route::get('/index', [RaporController::class, 'index'])->name('index');
    
    // 2. Route AJAX untuk Sinkronisasi Status
    Route::post('/sinkronkan', [RaporController::class, 'sinkronkanKelas'])->name('sinkronkan');
    Route::post('/sinkronkan-kelas', [RaporController::class, 'sinkronkanKelas'])->name('sinkronkan_kelas');

    // 3. Route AJAX untuk Modal Detail (Monitoring & Progress)
    Route::get('/detail-siswa', [RaporController::class, 'getDetailSiswa'])->name('detail_siswa');
    Route::get('/detail-progress', [RaporController::class, 'getDetailProgress'])->name('detail_progress');
    
    // 4. Proses Cetak Rapor
    Route::get('/cetak', [RaporController::class, 'cetakIndex'])->name('cetak');
    Route::get('/print/{id_siswa}', [RaporController::class, 'cetak_proses'])->name('cetak_proses');
    
    // --- TAMBAHKAN ROUTE BARU DI SINI ---
    Route::get('/cetak-massal', [RaporController::class, 'cetak_massal'])->name('cetak_massal');
    // 5. Rute Baru: Khusus Download (Tanpa merusak rute lama)
    Route::get('/download-satuan/{id_siswa}', [RaporController::class, 'download_satuan'])->name('download_satuan');
    Route::get('/download-massal', [RaporController::class, 'download_massal'])->name('download_massal');
    // 🛑 ROUTE BARU: DOWNLOAD PDF MASSAL (SATU FILE PANJANG)
    Route::get('/download-massal-pdf', [RaporController::class, 'download_massal_pdf'])->name('download_massal_pdf');
});

Route::group(['prefix' => 'ledger', 'as' => 'ledger.'], function () {
    Route::get('/data-nilai', [LedgerController::class, 'index'])->name('ledger_index');
    // Jika nanti butuh export excel, bisa ditambahkan di sini:
    Route::get('/export/excel', [LedgerController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf', [LedgerController::class, 'exportPdf'])->name('export.pdf');
    // Route::get('/export-excel', [LedgerController::class, 'exportExcel'])->name('export_excel');
});

// 3. RAPOR NILAI & CATATAN WALI KELAS (master.rapornilai.*)
// Route::prefix('rapor')->name('rapornilai.')->group(function () {
//     Route::get('/', [RaporNilaiController::class, 'index'])->name('index'); 
//     Route::get('/create', [RaporNilaiController::class, 'create'])->name('create');
//     Route::post('/', [RaporNilaiController::class, 'store'])->name('store');
//     Route::delete('/{id_rapor}', [RaporNilaiController::class, 'destroy'])->name('destroy'); 

//     Route::prefix('wali')->name('wali.')->group(function () {
//         Route::get('/catatan', [RaporCatatanController::class, 'inputCatatan'])->name('catatan');
//         Route::post('/simpan', [RaporCatatanController::class, 'simpanCatatan'])->name('simpan');
//         Route::get('/get-siswa/{id_kelas}', [RaporCatatanController::class, 'getSiswa'])->name('get_siswa'); 
//     });
// });

// Route::get('/rapor-nilai/detail-progress', [RaporNilaiController::class, 'detailProgress'])->name('rapornilai.detail_progress');