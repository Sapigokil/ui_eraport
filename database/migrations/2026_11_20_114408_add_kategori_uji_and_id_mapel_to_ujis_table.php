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
        Schema::table('ujis', function (Blueprint $table) {
            $table->string('kategori_uji', 20)->default('Umum')->after('judul');
            $table->unsignedBigInteger('id_mapel')->nullable()->after('kategori_uji');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujis', function (Blueprint $table) {
            $table->dropColumn(['kategori_uji', 'id_mapel']);
        });
    }
};