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
            
            // 1. Cek & Rename 'catatan_akademik' ke 'kokurikuler'
            // Hanya rename jika 'catatan_akademik' ADA dan 'kokurikuler' BELUM ADA
            if (Schema::hasColumn('nilai_akhir_rapor', 'catatan_akademik') && !Schema::hasColumn('nilai_akhir_rapor', 'kokurikuler')) {
                $table->renameColumn('catatan_akademik', 'kokurikuler');
            } 
            // Opsional: Jika KEDUANYA ada (karena error sebelumnya), kita hapus yang lama agar bersih
            elseif (Schema::hasColumn('nilai_akhir_rapor', 'catatan_akademik') && Schema::hasColumn('nilai_akhir_rapor', 'kokurikuler')) {
                $table->dropColumn('catatan_akademik');
            }

            // 2. Cek & Rename 'izin' ke 'ijin'
            if (Schema::hasColumn('nilai_akhir_rapor', 'izin') && !Schema::hasColumn('nilai_akhir_rapor', 'ijin')) {
                $table->renameColumn('izin', 'ijin');
            }
            elseif (Schema::hasColumn('nilai_akhir_rapor', 'izin') && Schema::hasColumn('nilai_akhir_rapor', 'ijin')) {
                $table->dropColumn('izin');
            }

            // 3. Menghapus Kolom (Looping)
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