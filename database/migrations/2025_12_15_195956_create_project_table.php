<?php
// File: database/migrations/YYYY_MM_DD_create_project_table.php

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
        if (!Schema::hasTable('project')) {
            Schema::create('project', function (Blueprint $table) {
                // Primary Key
                $table->id('id_project');

                // Kolom Data (Tanpa FK)
                $table->unsignedBigInteger('id_siswa'); 
                $table->unsignedBigInteger('id_mapel'); 
                $table->unsignedBigInteger('id_kelas'); 
                
                $table->string('tahun_ajaran', 10);
                $table->unsignedTinyInteger('semester')->comment('1: Ganjil, 2: Genap');
                
                $table->integer('nilai')->nullable();
                $table->integer('nilai_bobot')->nullable();
                $table->text('tujuan_pembelajaran')->nullable();

                // Timestamps
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project');
    }
};