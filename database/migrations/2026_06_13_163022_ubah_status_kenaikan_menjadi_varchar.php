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
        // Ubah kolom di tabel nilai_akhir_rapor menjadi VARCHAR (String)
        Schema::table('nilai_akhir_rapor', function (Blueprint $table) {
            $table->string('status_kenaikan')->nullable()->default('proses')->change();
        });

        // Ubah kolom di tabel catatan menjadi VARCHAR (String) jika kolomnya ada
        if (Schema::hasColumn('catatan', 'status_kenaikan')) {
            Schema::table('catatan', function (Blueprint $table) {
                $table->string('status_kenaikan')->nullable()->default('proses')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Kembalikan ke ENUM jika di-rollback
        Schema::table('nilai_akhir_rapor', function (Blueprint $table) {
            $table->enum('status_kenaikan', ['naik', 'tinggal', 'lulus', 'tidak_lulus', 'proses'])->default('proses')->change();
        });

        if (Schema::hasColumn('catatan', 'status_kenaikan')) {
            Schema::table('catatan', function (Blueprint $table) {
                $table->enum('status_kenaikan', ['naik', 'tinggal', 'lulus', 'tidak_lulus', 'proses'])->default('proses')->change();
            });
        }
    }
};