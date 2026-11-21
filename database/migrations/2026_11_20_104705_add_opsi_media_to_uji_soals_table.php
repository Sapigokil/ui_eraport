<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uji_soals', function (Blueprint $table) {
            $table->json('opsi_media')
                ->nullable()
                ->after('opsi_jawaban')
                ->comment('Menyimpan array path gambar untuk masing-masing opsi pilihan jawaban PG/PGK');
        });
    }

    public function down(): void
    {
        Schema::table('uji_soals', function (Blueprint $table) {
            $table->dropColumn('opsi_media');
        });
    }
};