<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujis', function (Blueprint $table) {
            if (!Schema::hasColumn('ujis', 'kelas_ids')) {
                $table->json('kelas_ids')->nullable()->after('buka_untuk_umum');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ujis', function (Blueprint $table) {
            if (Schema::hasColumn('ujis', 'kelas_ids')) {
                $table->dropColumn('kelas_ids');
            }
        });
    }
};