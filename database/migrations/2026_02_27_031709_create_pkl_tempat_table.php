<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('pkl_tempat')) {
            Schema::create('pkl_tempat', function (Blueprint $table) {
                $table->id();
                
                // Soft Relation: Hanya menampung angka ID dari tabel gurus
                // Tidak menggunakan constraint foreign key agar tidak memicu error 150
                $table->unsignedBigInteger('guru_id')->nullable()->comment('ID dari tabel gurus (PIC Sekolah)');
                
                // Data Perusahaan
                $table->string('nama_perusahaan');
                $table->string('bidang_usaha')->nullable();
                $table->string('nama_pimpinan')->nullable();
                $table->text('alamat_perusahaan');
                $table->string('kota')->nullable();
                $table->string('no_telp_perusahaan')->nullable();
                $table->string('email_perusahaan')->nullable();
                
                // Data Legalitas / MOU
                $table->string('no_surat_mou')->nullable();
                $table->date('tanggal_mou')->nullable();
                
                // Data Instruktur Lapangan
                $table->string('nama_instruktur');
                $table->string('no_telp_instruktur')->nullable();
                
                // Status aktif tempat PKL
                $table->boolean('is_active')->default(true);
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pkl_tempat');
    }
};