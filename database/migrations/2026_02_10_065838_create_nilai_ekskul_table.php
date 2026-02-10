<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_ekskul', function (Blueprint $table) {
            $table->id('id_nilai_ekskul');
            $table->foreignId('id_ekskul')->constrained('ekskul', 'id_ekskul')->cascadeOnDelete();
            $table->foreignId('id_siswa')->constrained('siswa', 'id_siswa')->cascadeOnDelete();
            $table->foreignId('id_kelas')->nullable()->constrained('kelas', 'id_kelas'); // Snapshot kelas saat dinilai
            
            $table->string('tahun_ajaran', 9); // Contoh: 2025/2026
            $table->tinyInteger('semester'); // 1 = Ganjil, 2 = Genap
            
            $table->string('predikat')->nullable(); // Sangat Baik, Baik, Cukup, Kurang
            $table->text('keterangan')->nullable(); // Deskripsi singkat
            
            $table->timestamps();

            // Mencegah duplikasi nilai untuk siswa yang sama di ekskul & periode yang sama
            $table->unique(['id_ekskul', 'id_siswa', 'tahun_ajaran', 'semester'], 'unique_nilai_ekskul_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_ekskul');
    }
};