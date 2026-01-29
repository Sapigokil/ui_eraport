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
        Schema::table('nilai_akhir_rapor', function (Blueprint $table) {
            // 1. Merubah Nama Kolom
            // Pastikan kolom asal ada sebelum di-rename
            if (Schema::hasColumn('nilai_akhir_rapor', 'catatan_akademik')) {
                $table->renameColumn('catatan_akademik', 'kokurikuler');
            }

            if (Schema::hasColumn('nilai_akhir_rapor', 'izin')) {
                $table->renameColumn('izin', 'ijin');
            }

            // 2. Menghapus Kolom
            $columnsToDrop = ['fase_snapshot', 'dispensasi', 'catatan_p5', 'data_ekskul_snapshot'];
            
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('nilai_akhir_rapor', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_akhir_rapor', function (Blueprint $table) {
            // Rollback Nama Kolom
            if (Schema::hasColumn('nilai_akhir_rapor', 'kokurikuler')) {
                $table->renameColumn('kokurikuler', 'catatan_akademik');
            }

            if (Schema::hasColumn('nilai_akhir_rapor', 'ijin')) {
                $table->renameColumn('ijin', 'izin');
            }

            // Rollback Kolom yang Dihapus (Opsional: Tambahkan kembali jika perlu)
            $table->char('fase_snapshot', 1)->nullable();
            $table->integer('dispensasi')->default(0);
            $table->text('catatan_p5')->nullable();
            $table->json('data_ekskul_snapshot')->nullable();
        });
    }
};