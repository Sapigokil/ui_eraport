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
        if (!Schema::hasTable('pkl_gurusiswa')) {
            Schema::create('pkl_gurusiswa', function (Blueprint $table) {
                $table->id();

                // ID Relasi (Menggunakan tipe Integer biasa tanpa Foreign Key Constraint)
                $table->unsignedBigInteger('id_guru');
                $table->unsignedBigInteger('id_siswa');
                $table->unsignedBigInteger('id_kelas');
                $table->unsignedBigInteger('id_pkl_tempat')->nullable(); // Nullable karena diset nanti oleh guru

                // Kolom Periode & Snapshot
                $table->string('tahun_ajaran', 20);
                $table->integer('semester');
                $table->string('nama_guru');
                $table->string('nama_siswa');
                $table->string('nama_kelas', 50);
                $table->string('tingkat', 20);
                $table->string('jurusan', 50);

                // Detail PKL
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                
                // Status: 0 = Menunggu/Draft, 1 = Aktif PKL, 2 = Selesai
                $table->integer('status')->default(0);

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pkl_gurusiswa');
    }
};