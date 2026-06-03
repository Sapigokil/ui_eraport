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
            // Menambahkan kelas_awal dan tgl_masuk setelah id_kelas
            $table->string('kelas_awal', 50)->nullable()->after('id_kelas');
            $table->date('tgl_masuk')->nullable()->after('kelas_awal');
            
            // Menambahkan telp_wali setelah nik_wali
            $table->string('telp_wali', 30)->nullable()->after('nik_wali');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_siswa', function (Blueprint $table) {
            $table->dropColumn(['kelas_awal', 'tgl_masuk', 'telp_wali']);
        });
    }
};