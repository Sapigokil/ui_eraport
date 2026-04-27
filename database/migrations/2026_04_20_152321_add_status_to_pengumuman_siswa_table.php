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
        Schema::table('pengumuman_siswa', function (Blueprint $table) {
            // Menambahkan kolom status dengan default 'hold'
            // Posisi diletakkan setelah kolom 'status_hasil' agar tabel rapi
            $table->string('status', 20)->default('hold')->after('status_hasil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengumuman_siswa', function (Blueprint $table) {
            // Menghapus kolom status jika migration di-rollback
            $table->dropColumn('status');
        });
    }
};