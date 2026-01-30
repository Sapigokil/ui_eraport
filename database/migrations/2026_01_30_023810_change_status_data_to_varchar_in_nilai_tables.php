<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah nilai_akhir_rapor menjadi VARCHAR(20)
        DB::statement("ALTER TABLE `nilai_akhir_rapor` MODIFY COLUMN `status_data` VARCHAR(20) DEFAULT 'draft'");

        // Ubah nilai_akhir menjadi VARCHAR(20) (untuk memastikan konsistensi)
        DB::statement("ALTER TABLE `nilai_akhir` MODIFY COLUMN `status_data` VARCHAR(20) DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke ENUM jika di-rollback 
        // NOTE: Rollback akan gagal jika ada data 'cetak' di database karena tidak ada di list ENUM ini.
        // Sebaiknya hati-hati melakukan rollback.
        
        DB::statement("ALTER TABLE `nilai_akhir_rapor` MODIFY COLUMN `status_data` ENUM('draft', 'final') DEFAULT 'draft'");
        DB::statement("ALTER TABLE `nilai_akhir` MODIFY COLUMN `status_data` ENUM('draft', 'final') DEFAULT 'draft'");
    }
};