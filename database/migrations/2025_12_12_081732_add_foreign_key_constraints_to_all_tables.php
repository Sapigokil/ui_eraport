<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------------
        // 1. Relasi ke USERS
        // ---------------------------------------------------------------------
        Schema::table('guru', function (Blueprint $table) {
            $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
        });
        Schema::table('siswa', function (Blueprint $table) {
            $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
        });

        // ---------------------------------------------------------------------
        // 2. Relasi Kelas dan Guru (Struktur Dasar)
        // ---------------------------------------------------------------------
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreign('id_guru')->references('id_guru')->on('guru')->onDelete('set null');
        });
        Schema::table('anggota_kelas', function (Blueprint $table) {
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
        });
        Schema::table('wali_kelas', function (Blueprint $table) {
            $table->foreign('id_guru')->references('id_guru')->on('guru')->onDelete('cascade');
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
        });

        // ---------------------------------------------------------------------
        // 3. Relasi Siswa
        // ---------------------------------------------------------------------
        Schema::table('siswa', function (Blueprint $table) {
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('set null');
            $table->foreign('id_ekskul')->references('id_ekskul')->on('ekskul')->onDelete('set null');
        });
        Schema::table('detail_siswa', function (Blueprint $table) {
            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
        });

        // ---------------------------------------------------------------------
        // 4. Relasi Pembelajaran & Mata Pelajaran
        // ---------------------------------------------------------------------
        Schema::table('pembelajaran', function (Blueprint $table) {
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
            $table->foreign('id_mapel')->references('id_mapel')->on('mata_pelajaran')->onDelete('cascade');
            $table->foreign('id_guru')->references('id_guru')->on('guru')->onDelete('cascade');
        });
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->foreign('id_guru')->references('id_guru')->on('guru')->onDelete('set null');
            $table->foreign('id_pembelajaran')->references('id_pembelajaran')->on('pembelajaran')->onDelete('set null');
        });
        
        // ---------------------------------------------------------------------
        // 5. Relasi Guru Tambahan
        // ---------------------------------------------------------------------
        // Schema::table('ekskul', function (Blueprint $table) {
        //     $table->foreign('id_guru')->references('id_guru')->on('guru')->onDelete('set null');
        // });
        Schema::table('detail_guru', function (Blueprint $table) {
            $table->foreign('id_guru')->references('id_guru')->on('guru')->onDelete('cascade');
            $table->foreign('id_pembelajaran')->references('id_pembelajaran')->on('pembelajaran')->onDelete('set null');
        });

        // ---------------------------------------------------------------------
        // 6. Relasi Catatan dan Ekskul Siswa
        // ---------------------------------------------------------------------
        Schema::table('catatan', function (Blueprint $table) {
            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
            $table->foreign('id_ekskul')->references('id_ekskul')->on('ekskul')->onDelete('set null');
        });
        Schema::table('catatan_rapor', function (Blueprint $table) {
            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
        });
        Schema::table('ekskul_siswa', function (Blueprint $table) {
            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
            $table->foreign('id_ekskul')->references('id_ekskul')->on('ekskul')->onDelete('cascade');
            $table->foreign('id_catatan')->references('id_catatan')->on('catatan')->onDelete('set null');
        });
        
        // ---------------------------------------------------------------------
        // 7. Relasi Rapor
        // ---------------------------------------------------------------------
        Schema::table('rapor', function (Blueprint $table) {
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
            $table->foreign('id_mapel')->references('id_mapel')->on('mata_pelajaran')->onDelete('cascade');
            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
        });
        
        // =====================================================================
        // 8. Relasi Sumatif (BARU)
        // =====================================================================
        // Schema::table('sumatif', function (Blueprint $table) {
        //     $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
        //     $table->foreign('id_mapel')->references('id_mapel')->on('mata_pelajaran')->onDelete('cascade');
        //     $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
        // });
    }

    public function down(): void
    {
        // // =====================================================================
        // // 8. Relasi Sumatif (BARU)
        // // =====================================================================
        // Schema::table('sumatif', function (Blueprint $table) {
        //     $table->dropForeign(['id_kelas']);
        //     $table->dropForeign(['id_mapel']);
        //     $table->dropForeign(['id_siswa']);
        // });
        
        // ---------------------------------------------------------------------
        // 7. Relasi Rapor
        // ---------------------------------------------------------------------
        Schema::table('rapor', function (Blueprint $table) {
            $table->dropForeign(['id_kelas']);
            $table->dropForeign(['id_mapel']);
            $table->dropForeign(['id_siswa']);
        });

        // ---------------------------------------------------------------------
        // 6. Relasi Catatan dan Ekskul Siswa
        // ---------------------------------------------------------------------
        Schema::table('ekskul_siswa', function (Blueprint $table) {
            $table->dropForeign(['id_siswa']);
            $table->dropForeign(['id_ekskul']);
            $table->dropForeign(['id_catatan']);
        });
        Schema::table('catatan_rapor', function (Blueprint $table) {
            $table->dropForeign(['id_siswa']);
            $table->dropForeign(['id_kelas']);
        });
        Schema::table('catatan', function (Blueprint $table) {
            $table->dropForeign(['id_siswa']);
            $table->dropForeign(['id_kelas']);
            $table->dropForeign(['id_ekskul']);
        });

        // ---------------------------------------------------------------------
        // 5. Relasi Guru Tambahan
        // ---------------------------------------------------------------------
        Schema::table('detail_guru', function (Blueprint $table) {
            $table->dropForeign(['id_guru']);
            $table->dropForeign(['id_pembelajaran']);
        });
        // Schema::table('ekskul', function (Blueprint $table) {
        //     $table->dropForeign(['id_guru']);
        // });

        // ---------------------------------------------------------------------
        // 4. Relasi Pembelajaran & Mata Pelajaran
        // ---------------------------------------------------------------------
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropForeign(['id_guru']);
            $table->dropForeign(['id_pembelajaran']);
        });
        Schema::table('pembelajaran', function (Blueprint $table) {
            $table->dropForeign(['id_kelas']);
            $table->dropForeign(['id_mapel']);
            $table->dropForeign(['id_guru']);
        });
        
        // ---------------------------------------------------------------------
        // 3. Relasi Siswa
        // ---------------------------------------------------------------------
        Schema::table('detail_siswa', function (Blueprint $table) {
            $table->dropForeign(['id_siswa']);
        });
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropForeign(['id_kelas']);
            $table->dropForeign(['id_ekskul']);
        });

        // ---------------------------------------------------------------------
        // 2. Relasi Kelas dan Guru (Struktur Dasar)
        // ---------------------------------------------------------------------
        Schema::table('wali_kelas', function (Blueprint $table) {
            $table->dropForeign(['id_guru']);
            $table->dropForeign(['id_kelas']);
        });
        Schema::table('anggota_kelas', function (Blueprint $table) {
            $table->dropForeign(['id_kelas']);
        });
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['id_guru']);
        });

        // ---------------------------------------------------------------------
        // 1. Relasi ke USERS
        // ---------------------------------------------------------------------
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });
        Schema::table('guru', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });
    }
};