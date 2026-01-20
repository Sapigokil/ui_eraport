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
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->boolean('is_active')->default(1)->after('nama_mapel');
        });
    }

    public function down()
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

};
