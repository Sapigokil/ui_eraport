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
            // Menambahkan telp_ortu setelah telp_wali
            $table->string('telp_ortu', 30)->nullable()->after('telp_wali');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_siswa', function (Blueprint $table) {
            $table->dropColumn(['kelas_awal', 'tgl_masuk', 'telp_ortu']);
        });
    }
};