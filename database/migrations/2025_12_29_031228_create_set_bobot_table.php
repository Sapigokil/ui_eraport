<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('set_bobot', function (Blueprint $table) {
            $table->id();

            $table->integer('jumlah_sumatif'); // 1–5
            $table->integer('bobot_sumatif');  // %
            $table->integer('bobot_project');  // %

            $table->string('tahun_ajaran');    // contoh: 2025/2026
            $table->enum('semester', ['GANJIL', 'GENAP']);

            $table->timestamps();

            // Optional: cegah duplikasi
            $table->unique(['tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('set_bobot');
    }
};
