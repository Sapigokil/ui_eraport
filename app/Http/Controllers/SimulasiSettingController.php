<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SimulasiSettingController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan Simulasi
     */
    public function index()
    {
        // Ambil data tanggal sinkronisasi terakhir dari Cache
        $lastSync = Cache::get('last_sync_simulasi', null);

        return view('settings.simulasi_index', compact('lastSync'));
    }

    /**
     * Eksekusi Sinkronisasi (Reset Database Simulasi dengan Data Utama)
     */
    public function syncDatabase(Request $request)
    {
        // 1. Ambil Nama Database dari Konfigurasi
        $mainDbName = config('database.connections.mysql.database');
        $simDbName  = config('database.connections.mysql_simulasi.database');

        if (!$mainDbName || !$simDbName) {
            return back()->with('error', 'Konfigurasi database tidak ditemukan di .env.');
        }

        try {
            // Set limit waktu dan memory agar tidak timeout saat DB besar
            set_time_limit(300);
            ini_set('memory_limit', '512M');

            // 2. Ambil semua daftar tabel dari Database Utama
            $tables = DB::connection('mysql')->select('SHOW TABLES');
            $tableKey = "Tables_in_" . $mainDbName;

            // 3. Matikan Foreign Key Check di DB Simulasi agar bisa Drop Table
            DB::connection('mysql_simulasi')->statement('SET FOREIGN_KEY_CHECKS=0;');

            // 4. Lakukan Cloning (Drop -> Create Structure -> Insert Data)
            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                // Hapus tabel lama di simulasi (jika ada)
                DB::connection('mysql_simulasi')->statement("DROP TABLE IF EXISTS `$simDbName`.`$tableName`");
                
                // Copy Struktur Tabel
                DB::connection('mysql_simulasi')->statement("CREATE TABLE `$simDbName`.`$tableName` LIKE `$mainDbName`.`$tableName`");
                
                // Copy Data Tabel
                DB::connection('mysql_simulasi')->statement("INSERT INTO `$simDbName`.`$tableName` SELECT * FROM `$mainDbName`.`$tableName`");
            }

            // 5. Hidupkan kembali Foreign Key Check
            DB::connection('mysql_simulasi')->statement('SET FOREIGN_KEY_CHECKS=1;');

            // 6. Simpan waktu sinkronisasi ke Cache
            Cache::forever('last_sync_simulasi', now());

            return back()->with('success', 'Database Simulasi berhasil di-reset dan disinkronkan dengan data asli terbaru!');

        } catch (\Exception $e) {
            // Pastikan foreign key tetap menyala meskipun terjadi error
            DB::connection('mysql_simulasi')->statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::error('Error Sync Simulasi: ' . $e->getMessage());
            
            return back()->with('error', 'Gagal menyinkronkan database! Pastikan user database memiliki izin akses ke kedua database. Error: ' . $e->getMessage());
        }
    }
}