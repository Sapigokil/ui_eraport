<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pkl_penempatan')) {
            Schema::create('pkl_penempatan', function (Blueprint $table) {
                $table->id();
                
                // Kolom Relasi (Integer murni tanpa FK constraint sesuai kesepakatan)
                $table->unsignedBigInteger('id_siswa');
                $table->unsignedBigInteger('id_guru')->nullable();
                $table->unsignedBigInteger('id_gurusiswa')->nullable();
                $table->unsignedBigInteger('id_pkltempat');
                
                // Kolom Periode (Ditambahkan agar filter tahun & semester berjalan lancar)
                $table->string('tahun_ajaran', 20);
                $table->integer('semester');

                // Detail Penempatan
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->integer('status')->default(0); // 0=Draft/Proses, 1=Aktif, 2=Selesai

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pkl_penempatan');
    }
};