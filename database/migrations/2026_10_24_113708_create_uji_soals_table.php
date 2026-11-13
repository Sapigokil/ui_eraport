<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uji_soals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uji_id')->comment('ID dari tabel ujis');
            
            // Tipe soal menentukan bagaimana sistem mengoreksi
            $table->enum('tipe', ['pg', 'pg_kompleks', 'esai'])->comment('pg = 1 jawaban, pg_kompleks = >1 jawaban, esai = uraian');
            $table->text('pertanyaan');
            $table->string('media')->nullable()->comment('Path gambar/audio file pendukung soal');
            
            // Menggunakan JSON agar dinamis, bisa menyimpan banyak opsi (misal A, B, C, D, E) tanpa membuang kolom
            $table->json('opsi_jawaban')->nullable()->comment('Menyimpan array pilihan untuk PG/PGK');
            
            // JSON juga digunakan untuk kunci, untuk PG Kompleks kuncinya bisa array ["A", "C"]
            $table->json('kunci_jawaban')->nullable()->comment('Kosong jika tipe esai');
            
            $table->integer('bobot_nilai')->default(10)->comment('Bobot/poin jika soal ini dijawab benar');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uji_soals');
    }
};