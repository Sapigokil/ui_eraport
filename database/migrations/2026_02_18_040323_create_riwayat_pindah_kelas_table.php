<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pindah_kelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_siswa');
            $table->unsignedBigInteger('id_kelas_lama');
            $table->unsignedBigInteger('id_kelas_baru');
            $table->date('tgl_pindah');
            $table->string('alasan')->nullable(); // Rolling, Salah Input, dll
            $table->string('user_input')->nullable(); // Admin yg memproses
            $table->timestamps();

            // Opsional: Foreign Key
            // $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pindah_kelas');
    }
};