<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('siswa')) {
            Schema::table('siswa', function (Blueprint $table) {
                // Menambahkan kolom status setelah nipd/nisn
                $table->enum('status', ['aktif', 'keluar', 'lulus', 'meninggal', 'lainnya'])
                      ->default('aktif')
                      ->after('id_kelas'); // Sesuaikan posisi kolom
            });
        }
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};