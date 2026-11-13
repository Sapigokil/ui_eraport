<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uji_jawabans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uji_hasil_id');
            $table->unsignedBigInteger('uji_soal_id');
            
            // Menyimpan jawaban teks untuk esai, atau opsi/index untuk pilihan ganda
            $table->text('jawaban')->nullable(); 
            
            // Menyimpan nilai spesifik untuk soal ini (bisa diproses di akhir atau real-time)
            $table->decimal('skor_didapat', 5, 2)->default(0); 
            
            $table->boolean('ragu_ragu')->default(false);
            
            $table->timestamps();

            // Mencegah duplikasi data: 1 sesi hanya punya 1 baris untuk 1 soal
            $table->unique(['uji_hasil_id', 'uji_soal_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('uji_jawabans');
    }
};