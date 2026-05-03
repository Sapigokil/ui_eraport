<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration untuk menambah kolom file_skl.
     */
    public function up(): void
    {
        Schema::table('riwayat_kenaikan_kelas', function (Blueprint $table) {
            // Menambahkan kolom file_skl setelah kolom status_eksekusi
            // Kolom ini boleh kosong (nullable) karena tidak semua siswa langsung diunggah SKL-nya
            $table->string('file_skl')->nullable()->after('status_eksekusi');
        });
    }

    /**
     * Batalkan migration jika di-rollback.
     */
    public function down(): void
    {
        Schema::table('riwayat_kenaikan_kelas', function (Blueprint $table) {
            $table->dropColumn('file_skl');
        });
    }
};