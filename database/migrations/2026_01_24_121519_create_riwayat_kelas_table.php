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
        // 1. Buat tabel jika belum ada sama sekali
        if (!Schema::hasTable('riwayat_kelas')) {
            Schema::create('riwayat_kelas', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        // 2. Tambahkan atau update kolom satu per satu
        Schema::table('riwayat_kelas', function (Blueprint $table) {
            
            // Relasi ke Siswa
            if (!Schema::hasColumn('riwayat_kelas', 'id_siswa')) {
                $table->foreignId('id_siswa')->index()->after('id');
            }

            // Kelas Awal (Snapshot sebelum pindah/naik)
            if (!Schema::hasColumn('riwayat_kelas', 'id_kelas_lama')) {
                $table->foreignId('id_kelas_lama')->nullable()->comment('ID Kelas asal')->after('id_siswa');
            }
            if (!Schema::hasColumn('riwayat_kelas', 'nama_kelas_lama_snapshot')) {
                $table->string('nama_kelas_lama_snapshot', 50)->nullable()->comment('Nama kelas asal (Snapshot)')->after('id_kelas_lama');
            }

            // Kelas Tujuan (Snapshot setelah pindah/naik)
            if (!Schema::hasColumn('riwayat_kelas', 'id_kelas_baru')) {
                $table->foreignId('id_kelas_baru')->nullable()->comment('ID Kelas tujuan (Null jika Lulus/Keluar)')->after('nama_kelas_lama_snapshot');
            }
            if (!Schema::hasColumn('riwayat_kelas', 'nama_kelas_baru_snapshot')) {
                $table->string('nama_kelas_baru_snapshot', 50)->nullable()->comment('Nama kelas tujuan (Snapshot)')->after('id_kelas_baru');
            }

            // Detail Waktu & Tahun Ajaran
            if (!Schema::hasColumn('riwayat_kelas', 'tahun_ajaran')) {
                $table->string('tahun_ajaran', 10)->comment('Tahun Ajaran saat mutasi terjadi')->after('nama_kelas_baru_snapshot');
            }

            // Jenis Mutasi (Enum)
            if (!Schema::hasColumn('riwayat_kelas', 'jenis_mutasi')) {
                $table->enum('jenis_mutasi', [
                    'naik_kelas',      // Proses Kenaikan Kelas Biasa
                    'tinggal_kelas',    // Tidak Naik Kelas
                    'pindah_kelas',     // Pindah Jurusan/Kelas di tengah semester
                    'mutasi_keluar',    // Pindah Sekolah
                    'lulus',            // Lulus Sekolah
                    'masuk_baru'        // Siswa Baru
                ])->default('pindah_kelas')->after('tahun_ajaran');
            }

            if (!Schema::hasColumn('riwayat_kelas', 'tanggal_mutasi')) {
                $table->date('tanggal_mutasi')->after('jenis_mutasi');
            }

            if (!Schema::hasColumn('riwayat_kelas', 'keterangan')) {
                $table->text('keterangan')->nullable()->comment('Alasan pindah atau No SK')->after('tanggal_mutasi');
            }

            // Log Admin
            if (!Schema::hasColumn('riwayat_kelas', 'id_user_admin')) {
                $table->foreignId('id_user_admin')->nullable()->comment('ID Admin yang memproses')->after('keterangan');
            }
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