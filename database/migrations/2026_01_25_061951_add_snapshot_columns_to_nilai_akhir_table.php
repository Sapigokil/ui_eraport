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
            // Kolom Status Data (Wajib ada untuk menghindari error logic model)
            if (!Schema::hasColumn('nilai_akhir', 'status_data')) {
                $table->string('status_data', 20)->default('aktif')->after('capaian_akhir')
                    ->comment('Status data: aktif/history/hapus');
            }

            // Kolom Snapshot (Untuk membekukan data rapor saat dicetak)
            if (!Schema::hasColumn('nilai_akhir', 'tingkat')) {
                $table->string('tingkat', 10)->nullable()->after('status_data');
            }
            if (!Schema::hasColumn('nilai_akhir', 'fase')) {
                $table->string('fase', 5)->nullable()->after('tingkat');
            }
            
            // Snapshot Identitas (Agar rapor tetap valid walau nama kelas/guru berubah di masa depan)
            if (!Schema::hasColumn('nilai_akhir', 'nama_kelas_snapshot')) {
                $table->string('nama_kelas_snapshot', 100)->nullable()->after('fase');
            }
            if (!Schema::hasColumn('nilai_akhir', 'nama_mapel_snapshot')) {
                $table->string('nama_mapel_snapshot', 100)->nullable()->after('nama_kelas_snapshot');
            }
            if (!Schema::hasColumn('nilai_akhir', 'kode_mapel_snapshot')) {
                $table->string('kode_mapel_snapshot', 50)->nullable()->after('nama_mapel_snapshot');
            }
            if (!Schema::hasColumn('nilai_akhir', 'nama_guru_snapshot')) {
                $table->string('nama_guru_snapshot', 100)->nullable()->after('kode_mapel_snapshot');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_akhir', function (Blueprint $table) {
            $table->dropColumn([
                'status_data',
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