<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujis', function (Blueprint $table) { // sesuaikan 'uji' atau 'ujis'
            $table->boolean('open_book')->default(false)->after('acak_soal');
        });
    }

    public function down(): void
    {
        Schema::table('ujis', function (Blueprint $table) {
            $table->dropColumn('open_book');
        });
    }
};
