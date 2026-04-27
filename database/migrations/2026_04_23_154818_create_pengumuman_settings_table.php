<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengumuman_setting', function (Blueprint $table) {
            $table->id();
            $table->string('jenis'); // 'kelulusan' atau 'kenaikan'
            $table->string('tahun_ajaran');
            $table->dateTime('waktu_buka')->nullable();
            $table->dateTime('waktu_tutup')->nullable();
            $table->boolean('is_aktif')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengumuman_setting');
    }
};