<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ujis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_uji', 20)->unique(); 
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            
            // Menggunakan user_id sebagai author/pembuat asesmen
            $table->unsignedBigInteger('user_id')->comment('ID Pembuat/Author Asesmen (User Login)'); 
            
            // Pengaturan Waktu
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->integer('durasi_menit')->nullable()->default(0);
            
            // Pengaturan Fitur & Keamanan
            $table->boolean('acak_soal')->default(true);
            $table->boolean('tampilkan_nilai')->default(false);
            $table->boolean('buka_untuk_umum')->default(false);
            $table->integer('toleransi_pelanggaran')->default(3);
            $table->integer('maksimal_percobaan')->default(1);
            $table->boolean('ulangi_jika_gagal')->default(false);
            
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ujis');
    }
};