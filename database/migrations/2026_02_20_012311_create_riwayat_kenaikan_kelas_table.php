<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('riwayat_kenaikan_kelas')) {
            Schema::create('riwayat_kenaikan_kelas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_siswa');
                $table->unsignedBigInteger('id_kelas_lama');
                $table->unsignedBigInteger('id_kelas_baru');
                $table->string('tahun_ajaran_lama');
                $table->string('tahun_ajaran_baru');
                $table->enum('status', ['naik_kelas', 'tinggal_kelas', 'lulus'])->default('naik_kelas');
                $table->string('user_admin')->nullable(); // Siapa yang memproses
                $table->timestamps();

                // Opsional: Foreign keys
                // $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
                // $table->foreign('id_kelas_lama')->references('id_kelas')->on('kelas')->onDelete('cascade');
                // $table->foreign('id_kelas_baru')->references('id_kelas')->on('kelas')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kenaikan_kelas');
    }
};