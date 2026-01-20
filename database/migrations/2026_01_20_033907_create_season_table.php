<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('tahun_ajaran', 20);
            $table->unsignedTinyInteger('semester');

            $table->unsignedTinyInteger('is_open')->default(0);
            $table->unsignedTinyInteger('is_active')->default(1);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();

            $table->index(['tahun_ajaran', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season');
    }
};
