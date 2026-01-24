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
        Schema::create('nilai_akhir_rapor', function (Blueprint $table) {
            $table->id();

            // 1. Kunci Utama (Identitas Siswa & Waktu)
            $table->foreignId('id_siswa')->index();
            $table->string('tahun_ajaran', 10); // Contoh: "2025/2026"
            $table->tinyInteger('semester');    // 1 (Ganjil) atau 2 (Genap)

            // 2. Snapshot Identitas Kelas (History Protection)
            $table->foreignId('id_kelas')->nullable(); // Tetap simpan ID untuk relasi jika perlu
            $table->string('nama_kelas_snapshot', 50)->nullable()->comment('Nama kelas saat rapor dicetak (ex: X RPL 1)');
            $table->tinyInteger('tingkat')->nullable()->comment('10, 11, atau 12');
            $table->char('fase', 1)->nullable()->comment('E atau F');

            // 3. Snapshot Pejabat Penandatangan (History Protection)
            $table->string('wali_kelas_snapshot', 150)->nullable();
            $table->string('nip_wali_snapshot', 50)->nullable();
            $table->string('kepsek_snapshot', 150)->nullable();
            $table->string('nip_kepsek_snapshot', 50)->nullable();
            $table->date('tanggal_cetak')->nullable()->comment('Titi mangsa rapor');

            // 4. Data Absensi (Snapshot)
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alpha')->default(0);
            $table->integer('dispensasi')->default(0)->comment('Opsional');

            // 5. Data Catatan & Keputusan
            $table->text('catatan_akademik')->nullable()->comment('Keputusan Naik/Lulus');
            $table->text('catatan_wali_kelas')->nullable()->comment('Saran/Motivasi');
            $table->text('catatan_p5')->nullable()->comment('Deskripsi Project Penguatan Profil Pelajar Pancasila');
            
            // 6. Data Ekstrakurikuler (JSON SNAPSHOT)
            // Menyimpan array object: [{"nama": "Pramuka", "nilai": "A", "ket": "Baik"}]
            // Agar aman walaupun master ekskul dihapus.
            $table->json('data_ekskul')->nullable(); 

            // 7. Status & Meta
            $table->enum('status_kenaikan', ['naik', 'tinggal', 'lulus', 'tidak_lulus', 'proses'])->default('proses');
            $table->enum('status_data', ['draft', 'final', 'arsip_mutasi'])->default('draft')->comment('Penanda jika siswa mutasi, data ini jadi arsip');

            $table->timestamps();

            // Indexing agar pencarian rapor per siswa per semester cepat
            $table->unique(['id_siswa', 'tahun_ajaran', 'semester'], 'unique_rapor_per_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_akhir_rapor');
    }
};