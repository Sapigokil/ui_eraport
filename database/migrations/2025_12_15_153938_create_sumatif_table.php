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
        Schema::create('sumatif', function (Blueprint $table) {
            $table->id('id_sumatif'); // Primary Key

            // Kolom untuk Relasi (Tanpa Foreign Key)
            $table->unsignedBigInteger('id_kelas');
            $table->unsignedBigInteger('id_mapel');
            $table->unsignedBigInteger('id_siswa');
            
            // Data Sumatif
            $table->integer('nilai')->nullable();
            $table->string('tujuan_pembelajaran', 255)->comment('Kode/deskripsi tujuan pembelajaran yang diuji');
            $table->string('sumatif', 50)->comment('Nama/Kode sumatif, contoh: Sumatif 1, STS, SAS');
            
            // Konteks Waktu
            $table->string('tahun_ajaran', 9); // Contoh: 2024/2025
            $table->tinyInteger('semester')->comment('1: Ganjil, 2: Genap');
            
            $table->timestamps();
            
            // Opsional: Menambahkan unique constraint untuk mencegah duplikasi data sumatif yang sama
            $table->unique([
                'id_kelas',
                'id_mapel',
                'id_siswa', 
                'tujuan_pembelajaran', 
                'sumatif', 
                'tahun_ajaran', 
                'semester'
            ], 'unique_sumatif_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sumatif');
    }
};