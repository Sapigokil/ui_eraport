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
        Schema::table('nilai_akhir', function (Blueprint $table) {
            
            // 1. Snapshot Identitas Kelas (Agar rapor lama tidak berubah walau siswa pindah kelas)
            // Ditaruh setelah id_kelas
            $table->tinyInteger('tingkat')->nullable()->after('id_kelas')
                  ->comment('Snapshot Tingkat (10/11/12) saat nilai dibuat');
            
            $table->char('fase', 1)->nullable()->after('tingkat')
                  ->comment('Snapshot Fase (E/F)');
            
            $table->string('nama_kelas_snapshot', 50)->nullable()->after('fase')
                  ->comment('Nama kelas saat itu (ex: X RPL 1)');

            // 2. Snapshot Identitas Mapel & Guru (Proteksi jika Mapel dihapus/rename)
            // Ditaruh setelah id_mapel
            $table->string('nama_mapel_snapshot', 100)->nullable()->after('id_mapel')
                  ->comment('Nama mapel teks murni saat nilai dicetak');
            
            $table->string('kode_mapel_snapshot', 20)->nullable()->after('nama_mapel_snapshot')
                  ->comment('Kode mapel jika ada');

            $table->string('nama_guru_snapshot', 150)->nullable()->after('kode_mapel_snapshot')
                  ->comment('Nama guru pengampu mapel saat itu');

            // 3. Indexing (PENTING) untuk mempercepat pencarian history di masa depan
            // Agar query: "Cari nilai Budi tahun 2023 semester 1" berjalan instan
            $table->index(['id_siswa', 'tahun_ajaran', 'semester'], 'idx_riwayat_nilai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_akhir', function (Blueprint $table) {
            // Hapus index dulu
            $table->dropIndex('idx_riwayat_nilai');

            // Hapus kolom
            $table->dropColumn([
                'tingkat', 
                'fase', 
                'nama_kelas_snapshot',
                'nama_mapel_snapshot',
                'kode_mapel_snapshot',
                'nama_guru_snapshot'
            ]);
        });
    }
};