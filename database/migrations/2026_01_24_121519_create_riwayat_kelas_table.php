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
        Schema::create('riwayat_kelas', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Siswa
            // Pastikan 'siswa' dan 'id_siswa' sesuai dengan nama tabel/PK Anda
            $table->foreignId('id_siswa')->index(); 
            
            // Kelas Awal (Snapshot sebelum pindah/naik)
            $table->foreignId('id_kelas_lama')->nullable()->comment('ID Kelas asal');
            $table->string('nama_kelas_lama_snapshot', 50)->nullable()->comment('Nama kelas asal (Snapshot)');
            
            // Kelas Tujuan (Snapshot setelah pindah/naik)
            $table->foreignId('id_kelas_baru')->nullable()->comment('ID Kelas tujuan (Null jika Lulus/Keluar)');
            $table->string('nama_kelas_baru_snapshot', 50)->nullable()->comment('Nama kelas tujuan (Snapshot)');

            $table->string('tahun_ajaran', 10)->comment('Tahun Ajaran saat mutasi terjadi');
            
            // Jenis Mutasi (Enum untuk mempermudah filter)
            $table->enum('jenis_mutasi', [
                'naik_kelas',       // Proses Kenaikan Kelas Biasa
                'tinggal_kelas',    // Tidak Naik Kelas
                'pindah_kelas',     // Pindah Jurusan/Kelas di tengah semester
                'mutasi_keluar',    // Pindah Sekolah
                'lulus',            // Lulus Sekolah
                'masuk_baru'        // Siswa Baru
            ])->default('pindah_kelas');
            
            $table->date('tanggal_mutasi');
            $table->text('keterangan')->nullable()->comment('Alasan pindah atau No SK');
            
            // Log Admin (Opsional, sesuaikan jika tabel user Anda bukan 'users')
            $table->foreignId('id_user_admin')->nullable()->comment('ID Admin yang memproses');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas');
    }
};