<?php
// File: database/migrations/YYYY_MM_DD_create_nilai_akhir_table.php

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
        // Pastikan tabel tidak ada sebelum dibuat
        if (!Schema::hasTable('nilai_akhir')) {
            Schema::create('nilai_akhir', function (Blueprint $table) {
                // Primary Key
                $table->id('id_nilai_akhir');

                // Kolom Kunci (Tanpa FK)
                $table->unsignedBigInteger('id_kelas'); 
                $table->unsignedBigInteger('id_mapel'); 
                $table->unsignedBigInteger('id_siswa'); 
                
                $table->string('tahun_ajaran', 10);
                $table->unsignedTinyInteger('semester')->comment('1: Ganjil, 2: Genap');
                
                // Kolom Nilai Sumatif (S1, S2, S3)
                $table->integer('nilai_s1')->nullable();
                $table->integer('nilai_s2')->nullable();
                $table->integer('nilai_s3')->nullable();
                
                // Kolom Perhitungan Sumatif
                $table->decimal('rata_sumatif', 5, 2)->nullable();
                $table->decimal('bobot_sumatif', 5, 2)->nullable();
                
                // Kolom Nilai Project
                $table->decimal('nilai_project', 5, 2)->nullable();
                $table->decimal('rata_project', 5, 2)->nullable();
                $table->decimal('bobot_project', 5, 2)->nullable();
                
                // Nilai Akhir dan Capaian
                $table->decimal('nilai_akhir', 5, 2)->nullable();
                $table->text('capaian_akhir')->nullable();

                // Timestamps
                $table->timestamps();

                // Unique constraint (Memastikan kombinasi KELAS+MAPEL+SISWA+TAHUN+SEMESTER hanya ada 1)
                $table->unique(['id_kelas', 'id_mapel', 'id_siswa', 'tahun_ajaran', 'semester'], 'nilai_akhir_unique_keys');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_akhir');
    }
};