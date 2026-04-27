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
        Schema::create('pengumuman_siswa', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel siswa
            $table->unsignedBigInteger('id_siswa');
            
            // Tipe pengumuman: 'kenaikan' atau 'kelulusan'
            $table->string('jenis', 50);
            
            // Tahun ajaran pengumuman diterbitkan (contoh: '2025/2026')
            $table->string('tahun_ajaran', 20);
            
            // Hasil keputusan (contoh: 'naik', 'tinggal', 'lulus', 'tidak_lulus')
            $table->string('status_hasil', 50);
            
            // Kolom untuk pesan tambahan/motivasi khusus untuk siswa tersebut (opsional)
            $table->text('catatan')->nullable();
            
            // Indikator utama apakah siswa sudah melihat pengumuman (0 = Belum, 1 = Sudah)
            $table->tinyInteger('has_seen')->default(0);
            
            // Waktu spesifik kapan siswa menekan tombol "Buka Surat"
            $table->timestamp('waktu_dilihat')->nullable();
            
            // Mencatat siapa admin/wali kelas yang meng-generate pengumuman ini
            $table->string('user_input')->nullable();

            $table->timestamps();

            // Constraint Foreign Key (Silakan di-uncomment jika tipe data id_siswa Anda adalah unsignedBigInteger)
            // $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumuman_siswa');
    }
};