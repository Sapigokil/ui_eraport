<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pkl_tp')) {
            // 1. TABEL MASTER: TP (Tujuan Pembelajaran)
            Schema::create('pkl_tp', function (Blueprint $table) {
                $table->id();
                $table->string('nama_tp'); // cth: Soft Skills Dunia Kerja
                $table->string('label_tp')->nullable(); // cth: SS-DK (opsional, bisa diisi untuk memudahkan referensi)
                $table->integer('no_urut')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        
        // 2. TABEL MASTER: INDIKATOR
        if (!Schema::hasTable('pkl_tp_indikator')) {
            Schema::create('pkl_tp_indikator', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pkl_tp'); // Relasi Int ke pkl_tp
                $table->string('nama_indikator'); // cth: Disiplin & tanggung jawab
                $table->integer('no_urut')->default(1);
                $table->timestamps();
            });
        }

        // 3. TABEL MASTER: RUBRIK (Deskripsi dan Range Nilai)
        if (!Schema::hasTable('pkl_tp_rubrik')) {
            Schema::create('pkl_tp_rubrik', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pkl_tp_indikator'); // Relasi Int ke pkl_tp_indikator
                $table->string('predikat', 50); // cth: Sangat Baik
                $table->integer('min_nilai'); // cth: 90
                $table->integer('max_nilai'); // cth: 100
                $table->text('deskripsi_rubrik'); // Panduan untuk Guru di form penilaian
                $table->text('teks_untuk_rapor'); // Teks rakitan otomatis sistem (cth: "menunjukkan disiplin yang sangat prima")
                $table->timestamps();
            });
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('pkl_tp_rubrik');
        Schema::dropIfExists('pkl_tp_indikator');
        Schema::dropIfExists('pkl_tp');
    }
};