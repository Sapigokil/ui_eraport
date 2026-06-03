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
        Schema::table('detail_siswa', function (Blueprint $table) {
            // Menambahkan kolom alamat_ortu dan alamat_wali
            $table->text('alamat_ortu')->nullable()->after('alamat');
            $table->text('alamat_wali')->nullable()->after('alamat_ortu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_siswa', function (Blueprint $table) {
            $table->dropColumn(['alamat_ortu', 'alamat_wali']);
        });
    }
};