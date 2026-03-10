<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Snapshot Induk (Data Siswa & Catatan PKL)
        if (!Schema::hasTable('pkl_raporsiswa')) {
            Schema::create('pkl_raporsiswa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_siswa');
                $table->string('tahun_ajaran');
                $table->integer('semester');
                $table->unsignedBigInteger('id_kelas')->nullable(); // ID kelas saat dicetak
                
                // Rekaman Teks (Snapshot) agar kebal dari perubahan master data
                $table->string('nama_siswa_snapshot')->nullable();
                $table->string('nisn_snapshot')->nullable();
                $table->string('kelas_snapshot')->nullable();
                $table->string('wali_kelas_snapshot')->nullable();
                $table->string('nama_guru_snapshot')->nullable(); // Guru Pembimbing
                $table->string('nama_instruktur_snapshot')->nullable(); // Instruktur DU/DI
                $table->string('tempat_pkl_snapshot')->nullable();
                $table->date('tanggal_mulai_snapshot')->nullable();
                $table->date('tanggal_selesai_snapshot')->nullable();
                $table->string('program_keahlian_snapshot')->nullable();
                $table->string('konsentrasi_keahlian_snapshot')->nullable();
                
                // Kehadiran & Kesimpulan
                $table->integer('sakit')->default(0);
                $table->integer('izin')->default(0);
                $table->integer('alpa')->default(0);
                $table->text('catatan_pembimbing')->nullable();
                
                // Status Rapor (draft, final, cetak)
                $table->string('status_data')->default('draft'); 
                $table->timestamp('last_update')->nullable();
                
                $table->timestamps();
            });
        }

        // Tabel Snapshot Detail Nilai per Tujuan Pembelajaran
        if (!Schema::hasTable('pkl_rapornilai')) {
            Schema::create('pkl_rapornilai', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pkl_raporsiswa'); // Relasi ke tabel induk di atas
                $table->unsignedBigInteger('id_pkl_tp')->nullable();
                
                $table->string('nama_tp_snapshot')->nullable(); // Merekam nama TP saat itu
                $table->decimal('nilai_rata_rata', 5, 2)->default(0);
                $table->text('deskripsi_gabungan')->nullable();
                
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pkl_rapornilai');
        Schema::dropIfExists('pkl_raporsiswa');
    }
};