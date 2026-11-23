<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SbaController;
use App\Http\Controllers\SbaUjiController;

/*
|--------------------------------------------------------------------------
| SMART BASED ASSESSMENT (SBA) ADD-ON ROUTES
|--------------------------------------------------------------------------
| Modul Add-on Premium untuk Manajemen Bank Soal, Pembuatan Ujian, 
| CBT Engine, & Analytics Hasil Ujian.
|
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
        // B. MANAJEMEN UJIAN & BANK SOAL (CRUD)
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
        // C. HASIL UJIAN & REKAP DATA (ANALYTICS & EXPORT)
        // ----------------------------------------------------------------------
        Route::get('/{uji_id}/hasil', [SbaController::class, 'hasilIndex'])->name('hasil.index');
        Route::get('/{uji_id}/hasil/export', [SbaController::class, 'exportHasil'])->name('hasil.export');

        // ----------------------------------------------------------------------
        // D. DETAIL ASESI & PENILAIAN ESAI
        // ----------------------------------------------------------------------
        Route::get('/hasil/{hasil_id}/detail', [SbaController::class, 'hasilDetail'])->name('hasil.detail');
        Route::post('/hasil/{hasil_id}/simpan-nilai', [SbaController::class, 'simpanPenilaianEsai'])->name('hasil.simpan_nilai');
        Route::post('/hasil/{hasil_id}/refresh-nilai', [SbaController::class, 'refreshNilaiHasil'])->name('hasil.refresh_nilai');
        Route::get('/hasil/{hasil_id}/export-pdf', [SbaController::class, 'exportHasilDetailPdf'])->name('hasil.detail.pdf');
        Route::delete('/hasil/{hasil_id}', [SbaController::class, 'hasilDestroy'])->name('hasil.destroy');
        
        // Unlock Sesi Gagal Pelanggaran & Finalisasi Paksa
        Route::post('/hasil/{hasil_id}/unlock', [SbaController::class, 'unlockSesi'])->name('hasil.unlock');
        Route::post('/hasil/{hasil_id}/finalisasi', [SbaController::class, 'finalisasiPaksa'])->name('hasil.finalisasi');

        // Action Massal (Bulk Delete & Bulk Refresh Nilai)
        Route::delete('/uji/{uji_id}/hasil/bulk-delete', [SbaController::class, 'bulkDelete'])->name('hasil.bulk_delete');
        Route::post('/uji/{uji_id}/hasil/bulk-refresh-nilai', [SbaController::class, 'bulkRefreshNilai'])->name('hasil.bulk_refresh_nilai');

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