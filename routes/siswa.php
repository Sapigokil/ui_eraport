<?php
// File: routes/siswa.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiswaDashboardController;

// Semua route di dalam sini otomatis sudah melewati middleware 'auth' 
// dari file induknya (web.php).

Route::group(['prefix' => 'siswa', 'as' => 'siswa.'], function () {
    // Nanti route profil siswa, nilai siswa, dll diletakkan di sini...

    
});