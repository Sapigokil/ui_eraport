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
            // Cek dulu biar aman kalau dijalankan ulang
            if (!Schema::hasColumn('nilai_akhir', 'kategori_mapel_snapshot')) {
                $table->string('kategori_mapel_snapshot', 50)->nullable()
                      ->after('nama_mapel_snapshot') // Ditaruh setelah nama mapel
                      ->comment('Snapshot Kategori: Wajib/Peminatan/Mulok');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_akhir', function (Blueprint $table) {
            if (Schema::hasColumn('nilai_akhir', 'kategori_mapel_snapshot')) {
                $table->dropColumn('kategori_mapel_snapshot');
            }
        });
    }
};