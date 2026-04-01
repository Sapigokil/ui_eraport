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
        Schema::table('kelas', function (Blueprint $table) {
            // Menambahkan kolom baru setelah kolom jurusan (nullable agar data lama tidak error)
            $table->string('prog_keahlian')->nullable()->after('jurusan');
            $table->string('kons_keahlian')->nullable()->after('prog_keahlian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            // Menghapus kolom jika migration di-rollback
            $table->dropColumn(['prog_keahlian', 'kons_keahlian']);
        });
    }
};