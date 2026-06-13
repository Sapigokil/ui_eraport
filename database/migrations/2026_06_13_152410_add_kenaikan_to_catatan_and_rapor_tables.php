<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Menambahkan kolom ke tabel catatan
        Schema::table('catatan', function (Blueprint $table) {
            $table->string('status_kenaikan')->nullable()->default('proses')->after('catatan_wali_kelas');
            $table->string('id_kelas_tujuan')->nullable()->after('status_kenaikan');
        });

        // Menambahkan kolom ke tabel nilai_akhir_rapor (header rapor)
        Schema::table('nilai_akhir_rapor', function (Blueprint $table) {
            // Karena status_kenaikan sudah ada sebelumnya, kita cek dulu agar tidak error
            if (!Schema::hasColumn('nilai_akhir_rapor', 'status_kenaikan')) {
                $table->string('status_kenaikan')->nullable()->default('proses')->after('catatan_wali_kelas');
            }
            $table->string('id_kelas_tujuan')->nullable()->after('status_kenaikan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('catatan', function (Blueprint $table) {
            $table->dropColumn(['status_kenaikan', 'id_kelas_tujuan']);
        });

        Schema::table('nilai_akhir_rapor', function (Blueprint $table) {
            $table->dropColumn(['id_kelas_tujuan']);
            // Drop status_kenaikan hanya jika ditambahkan oleh migration ini
            // $table->dropColumn('status_kenaikan'); 
        });
    }
};