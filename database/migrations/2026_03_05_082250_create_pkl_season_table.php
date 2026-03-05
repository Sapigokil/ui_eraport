<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pkl_season')) {
            Schema::create('pkl_season', function (Blueprint $table) {
                $table->id();
                $table->string('tahun_ajaran', 20); // Contoh: "2024/2025"
                $table->integer('semester'); // 1 = Ganjil, 2 = Genap
                $table->boolean('is_open')->default(true); // Toggle manual dari Admin (Aktif/Tutup)
                $table->boolean('is_active')->default(true); // Penanda season mana yang sedang berjalan
                $table->date('start_date')->nullable(); // Mulai input nilai PKL
                $table->date('end_date')->nullable(); // Batas akhir input nilai PKL
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pkl_season');
    }
};