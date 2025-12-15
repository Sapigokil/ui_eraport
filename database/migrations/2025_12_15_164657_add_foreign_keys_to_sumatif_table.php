<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan tabel sumatif sudah ada sebelum menambahkan FK
        if (Schema::hasTable('sumatif')) {
            Schema::table('sumatif', function (Blueprint $table) {
                // Relasi ke tabel Kelas
                $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
                
                // Relasi ke tabel Mata Pelajaran
                $table->foreign('id_mapel')->references('id_mapel')->on('mata_pelajaran')->onDelete('cascade');
                
                // Relasi ke tabel Siswa
                $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sumatif')) {
            Schema::table('sumatif', function (Blueprint $table) {
                // Hapus FK
                $table->dropForeign(['id_kelas']);
                $table->dropForeign(['id_mapel']);
                $table->dropForeign(['id_siswa']);
            });
        }
    }
};