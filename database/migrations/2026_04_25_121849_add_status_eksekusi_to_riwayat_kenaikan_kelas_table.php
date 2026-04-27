<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration untuk menambahkan kolom status_eksekusi.
     */
    public function up(): void
    {
        Schema::table('riwayat_kenaikan_kelas', function (Blueprint $table) {
            // Kita letakkan setelah kolom user_admin agar struktur tabel tetap rapi
            // Default adalah 'draft' karena data yang baru masuk dari proses input 
            // kenaikan/kelulusan belum bersifat permanen di tabel Master Siswa.
            $table->string('status_eksekusi', 20)->default('draft')->after('user_admin');
            
            // Tambahkan index agar pencarian di halaman riwayat (yang memfilter status 'final') menjadi sangat cepat
            $table->index('status_eksekusi');
        });
    }

    /**
     * Batalkan migration jika diperlukan.
     */
    public function down(): void
    {
        Schema::table('riwayat_kenaikan_kelas', function (Blueprint $table) {
            $table->dropColumn('status_eksekusi');
        });
    }
};