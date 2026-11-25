<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SbaController;
use App\Http\Controllers\SbaHasilController;
use App\Http\Controllers\SbaUjiController;
use App\Http\Controllers\SbaSettingController;

/*
|--------------------------------------------------------------------------
| SMART BASED ASSESSMENT (SBA) ADD-ON ROUTES
|--------------------------------------------------------------------------
*/

// ==============================================================================
// 1. ADMIN & GURU MANAGEMENT ROUTES (Memerlukan Login & Permission sba.menu)
// ==============================================================================
Route::middleware(['auth', 'can:sba.menu'])->group(function () {
    
    // Group Admin/Guru SBA (URL Prefix: /admin/sba, Route Name Prefix: sba.)
    Route::prefix('admin/sba')->name('sba.')->group(function () {
        
        // ----------------------------------------------------------------------
        // A. DASHBOARD & UTILITY
        // ----------------------------------------------------------------------
        Route::get('/', [SbaController::class, 'index'])->name('index');
        Route::get('/get-mapel-by-guru/{guru_id}', [SbaController::class, 'getMapelByGuru'])->name('get-mapel-by-guru');
        Route::get('/check-kode', [SbaController::class, 'checkKode'])->name('check_kode');

        // ----------------------------------------------------------------------
        // A2. SETTINGS MODUL SBA (URL Prefix: /admin/sba/settings)
        // ----------------------------------------------------------------------
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/background-portal', [SbaSettingController::class, 'backgroundPortal'])->name('background_portal');
            Route::post('/background-portal', [SbaSettingController::class, 'updateBackgroundPortal'])->name('background_portal.update');
            Route::get('/background-portal/unsplash-live', [SbaSettingController::class, 'fetchUnsplashLive'])->name('background_portal.unsplash_live');
        });

        // ----------------------------------------------------------------------
        // B. MANAJEMEN UJIAN & BANK SOAL (CRUD) -> SbaController
        // ----------------------------------------------------------------------
        Route::get('/create', [SbaController::class, 'create'])->name('create');
        Route::post('/store', [SbaController::class, 'store'])->name('store');
        Route::get('/{uji}/edit', [SbaController::class, 'edit'])->name('edit');
        Route::put('/{uji}/update', [SbaController::class, 'update'])->name('update');
        Route::delete('/{uji}/destroy', [SbaController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/duplicate', [SbaController::class, 'duplicate'])->name('duplicate');
        Route::get('/template-soal', [SbaController::class, 'downloadTemplateSoal'])->name('soal.template');
        Route::post('/import-soal-preview', [SbaController::class, 'importSoalPreview'])->name('soal.import_preview');
        Route::post('/soal/export-excel', [SbaController::class, 'exportSoalExcel'])->name('soal.export');

        // Endpoint AJAX Hapus Single Soal + Gambar Media
        Route::delete('/soal/{soal_id}', [SbaController::class, 'destroySoal'])->name('soal.destroy');
        
        // ----------------------------------------------------------------------
        // C. HASIL UJIAN & REKAP DATA (ANALYTICS & EXPORT) -> SbaHasilController
        // ----------------------------------------------------------------------
        Route::get('/{uji_id}/hasil', [SbaHasilController::class, 'hasilIndex'])->name('hasil.index');
        Route::get('/{uji_id}/hasil/export', [SbaHasilController::class, 'exportHasil'])->name('hasil.export');

        // ----------------------------------------------------------------------
        // D. DETAIL ASESI & PENILAIAN ESAI -> SbaHasilController
        // ----------------------------------------------------------------------
        Route::get('/hasil/{hasil_id}/detail', [SbaHasilController::class, 'hasilDetail'])->name('hasil.detail');
        Route::post('/hasil/{hasil_id}/simpan-nilai', [SbaHasilController::class, 'simpanPenilaianEsai'])->name('hasil.simpan_nilai');
        Route::post('/hasil/{hasil_id}/refresh-nilai', [SbaHasilController::class, 'refreshNilaiHasil'])->name('hasil.refresh_nilai');
        Route::get('/hasil/{hasil_id}/export-pdf', [SbaHasilController::class, 'exportHasilDetailPdf'])->name('hasil.detail.pdf');
        Route::delete('/hasil/{hasil_id}', [SbaHasilController::class, 'hasilDestroy'])->name('hasil.destroy');
        
        // Unlock Sesi Gagal Pelanggaran & Finalisasi Paksa
        Route::post('/hasil/{hasil_id}/unlock', [SbaHasilController::class, 'unlockSesi'])->name('hasil.unlock');
        Route::post('/hasil/{hasil_id}/finalisasi', [SbaHasilController::class, 'finalisasiPaksa'])->name('hasil.finalisasi');

        // Action Massal (Bulk Delete & Bulk Refresh Nilai)
        Route::delete('/uji/{uji_id}/hasil/bulk-delete', [SbaHasilController::class, 'bulkDelete'])->name('hasil.bulk_delete');
        Route::post('/uji/{uji_id}/hasil/bulk-refresh-nilai', [SbaHasilController::class, 'bulkRefreshNilai'])->name('hasil.bulk_refresh_nilai');

    });

});

// ==============================================================================
// 2. PESERTA UJIAN / CBT ENGINE ROUTES (Public & Protected Access)
// ==============================================================================
Route::prefix('sba')->group(function () {
    
    // A. Portal Masuk Asesmen (Input Kode Asesmen)
    Route::get('/', [SbaUjiController::class, 'portal'])->name('sba.portal');
    Route::post('/process', [SbaUjiController::class, 'processPortal'])->name('sba.portal.process');

    // B. Ruang CBT Engine & Quicklink (/sba/asesmen/KODEUJI tetap dipertahankan)
    Route::get('/asesmen/{kode_uji}', [SbaUjiController::class, 'halamanUji'])->name('sba.uji.halaman');

    // C. Endpoint AJAX In-Game (Menggunakan uji_id agar kebal saat kode_uji diubah di tengah sesi)
    Route::post('/asesmen/{uji_id}/simpan', [SbaUjiController::class, 'simpanJawaban'])->name('sba.uji.simpan');
    Route::post('/asesmen/{uji_id}/pelanggaran', [SbaUjiController::class, 'catatPelanggaran'])->name('sba.uji.pelanggaran');

    // D. Finalisasi Ujian & Output Hasil (Menggunakan uji_id)
    Route::post('/asesmen/{uji_id}/selesai', [SbaUjiController::class, 'selesaiUjian'])->name('sba.uji.selesai');
    Route::get('/asesmen/{uji_id}/selesai/{hasil_id}', [SbaUjiController::class, 'tampilkanHasil'])->name('sba.uji.selesai.tampil');

});