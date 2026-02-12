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
        Schema::table('set_kokurikuler', function (Blueprint $table) {
            // Menambahkan kolom id_guru
            $table->unsignedBigInteger('id_guru')
                  ->nullable()       // Disarankan nullable agar data lama tidak error
                  ->after('aktif');  // Menempatkan kolom setelah 'aktif'

            // OPSIONAL: Jika ingin langsung membuat relasi (Foreign Key) ke tabel guru
            // Pastikan nama tabel referensinya benar (misal: 'guru' atau 'users')
            // $table->foreign('id_guru')->references('id')->on('guru')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('set_kokurikuler', function (Blueprint $table) {
            // Jika tadi menambahkan foreign key, hapus dulu index-nya
            // $table->dropForeign(['id_guru']);
            
            // Hapus kolom id_guru
            $table->dropColumn('id_guru');
        });
    }
};