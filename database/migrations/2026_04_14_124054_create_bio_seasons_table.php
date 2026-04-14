<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('bio_seasons', function (Blueprint $table) {
            $table->id('id_bio_season');
            $table->string('nama_periode'); // Contoh: "Pemutakhiran Data Ganjil 2025/2026"
            $table->datetime('tanggal_mulai');
            $table->datetime('tanggal_akhir');
            $table->boolean('is_active')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bio_seasons');
    }
};
