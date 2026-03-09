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
        // 1. TABEL NILAI PER TUJUAN PEMBELAJARAN (TP)
        if (!Schema::hasTable('pkl_nilaisiswa')) {
            Schema::create('pkl_nilaisiswa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_penempatan'); // Identitas komplit (Siswa, Guru, Tempat, TA, Smt)
                $table->unsignedBigInteger('id_pkl_tp'); // Relasi ke Tujuan Pembelajaran
                
                // Menggunakan tipe JSON untuk menampung array dinamis indikator (id_indikator, nilai, deskripsi)
                $table->json('data_indikator')->nullable(); 
                
                $table->decimal('nilai_rata_rata', 5, 2)->default(0); // Contoh: 85.50
                $table->text('deskripsi_gabungan')->nullable(); // Rakitan deskripsi otomatis dari JS
                
                $table->unsignedBigInteger('created_by')->nullable(); // ID User Guru yang menilai
                $table->timestamps();
            });
        }

        // 2. TABEL CATATAN, ABSENSI, & STATUS FINAL SISWA
        if (!Schema::hasTable('pkl_catatansiswa')) {
            Schema::create('pkl_catatansiswa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_penempatan'); // Identitas komplit (Siswa, Guru, Tempat, TA, Smt)
                
                $table->integer('sakit')->default(0);
                $table->integer('izin')->default(0);
                $table->integer('alpa')->default(0);
                $table->text('catatan_pembimbing')->nullable();
                
                // Status Penilaian: 0 = Draft/Belum Selesai, 1 = Final/Siap Cetak
                $table->boolean('status_penilaian')->default(false); 
                
                $table->unsignedBigInteger('created_by')->nullable(); // ID User Guru yang menilai
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pkl_catatansiswa');
        Schema::dropIfExists('pkl_nilaisiswa');
    }
};