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
        Schema::table('event', function (Blueprint $table) {
            // Menambahkan kolom 'jadwalkan' bertipe string setelah kolom 'tanggal'
            // Menggunakan nullable() agar aman jika tabel sudah berisi data sebelumnya
            $table->string('jadwalkan')->nullable()->after('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->dropColumn('jadwalkan');
        });
    }
};