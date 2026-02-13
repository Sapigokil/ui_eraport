<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('riwayat_mutasi_keluar')){    

            Schema::create('riwayat_mutasi_keluar', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_siswa');
                $table->unsignedBigInteger('id_kelas_terakhir')->nullable(); // Disimpan untuk history
                
                $table->string('jenis_mutasi', 50); // Pindah Sekolah, Mengundurkan Diri, dll
                $table->date('tgl_mutasi');
                $table->text('alasan')->nullable();
                $table->string('sekolah_tujuan')->nullable();
                $table->string('file_surat_pindah')->nullable(); // Path file
                $table->string('user_input')->nullable(); // Nama Admin/Operator yang memproses
                
                $table->timestamps();

                // Foreign Key (Opsional, tapi disarankan)
                $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_mutasi_keluar');
    }
};