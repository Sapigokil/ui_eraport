<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan migration untuk mengubah ENUM menjadi VARCHAR.
     */
    public function up(): void
    {
        // Menggunakan Raw SQL adalah cara paling stabil untuk merombak kolom ENUM
        DB::statement("ALTER TABLE riwayat_kenaikan_kelas MODIFY COLUMN status VARCHAR(50) DEFAULT NULL");
    }

    /**
     * Batalkan migration jika diperlukan (Kembali ke ENUM).
     */
    public function down(): void
    {
        // Jika dikembalikan ke ENUM, pastikan daftar status lama tercantum
        DB::statement("ALTER TABLE riwayat_kenaikan_kelas MODIFY COLUMN status ENUM('naik_kelas', 'tinggal_kelas', 'lulus') DEFAULT NULL");
    }
};