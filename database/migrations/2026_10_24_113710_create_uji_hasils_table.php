<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uji_hasils', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uji_id');
            
            // asesi_id diisi user_id jika login, null jika peserta publik
            $table->unsignedBigInteger('asesi_id')->nullable()->comment('ID Peserta terdaftar (nullable)');
            $table->string('nama_peserta_publik')->nullable()->comment('Nama lengkap peserta jalur publik');
            
            $table->integer('percobaan_ke')->default(1);
            
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            
            $table->decimal('skor_otomatis', 5, 2)->default(0);
            $table->decimal('skor_esai', 5, 2)->default(0);
            $table->decimal('total_skor', 5, 2)->default(0);
            
            $table->enum('status_pengerjaan', ['berlangsung', 'selesai', 'gagal_pelanggaran'])->default('berlangsung');
            $table->integer('jumlah_pelanggaran')->default(0);
            $table->text('catatan_pelanggaran')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uji_hasils');
    }
};