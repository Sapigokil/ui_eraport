<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pkl_catatansiswa', function (Blueprint $table) {
            $table->unsignedBigInteger('id_guru')->nullable()->after('id_penempatan');
            $table->string('program_keahlian')->nullable()->after('alpa');
            $table->string('konsentrasi_keahlian')->nullable()->after('program_keahlian');
            $table->date('tanggal_mulai')->nullable()->after('konsentrasi_keahlian');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            $table->string('nama_instruktur')->nullable()->after('tanggal_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('pkl_catatansiswa', function (Blueprint $table) {
            $table->dropColumn([
                'id_guru',
                'program_keahlian',
                'konsentrasi_keahlian',
                'tanggal_mulai',
                'tanggal_selesai',
                'nama_instruktur'
            ]);
        });
    }
};