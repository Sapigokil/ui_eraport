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
        Schema::create('set_bobot', function (Blueprint $table) {
            $table->id();
            $table->integer('jumlah_sumatif');     // 1–5
            $table->integer('bobot_sumatif');      // %
            $table->integer('bobot_project');      // %

            // Opsional tapi SANGAT disarankan
            $table->string('tahun_ajaran')->nullable();
            $table->string('semester')->nullable();

            $table->timestamps();
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
