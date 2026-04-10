<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_biodata', function (Blueprint $table) {
            $table->id('id_pengajuan');
            $table->integer('id_siswa')->index(); // Mengikuti tipe data id_siswa Anda
            
            // Kolom ajaib kita untuk menyimpan field apa saja yang diubah
            $table->json('data_perubahan'); 
            
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('keterangan_admin')->nullable(); // Alasan jika ditolak
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_biodata');
    }
};