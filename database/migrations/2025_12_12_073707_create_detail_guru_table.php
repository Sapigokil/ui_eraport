<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_guru', function (Blueprint $table) {
            $table->id('id_detail'); 
            
            // Kolom Foreign Key (FK) - Merujuk ke Primary Key 'id_guru' dan 'id_pembelajaran'
            $table->unsignedBigInteger('id_guru')->unique(); 
            $table->unsignedBigInteger('id_pembelajaran')->nullable(); 

            // Data Pribadi Dasar
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('nik', 20)->unique()->nullable();
            $table->string('agama', 20)->nullable();
            
            // Data Alamat Rinci
            $table->string('alamat', 255)->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('dusun', 100)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('lintang', 20)->nullable();
            $table->string('bujur', 20)->nullable();
            
            // Data Kontak
            $table->string('no_telp', 20)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('email', 100)->nullable();

            // Data Kepegawaian
            $table->string('status_kepegawaian', 50)->nullable(); 
            $table->string('tugas_tambahan', 100)->nullable(); 
            $table->string('pangkat_gol', 20)->nullable(); 
            $table->string('sumber_gaji', 50)->nullable();
            
            // Dokumen Kepegawaian & Tanggal
            $table->string('sk_cpns', 100)->nullable();
            $table->date('tgl_cpns')->nullable();
            $table->string('sk_pengangkatan', 100)->nullable();
            $table->date('tmt_pengangkatan')->nullable(); 
            $table->string('lembaga_pengangkatan', 100)->nullable();
            $table->date('tmt_pns')->nullable(); 
            $table->string('karpeg', 50)->nullable();
            $table->string('karis_karsu', 50)->nullable();
            $table->string('nuks', 50)->nullable(); 

            // Data Keluarga
            $table->string('nama_ibu_kandung', 150)->nullable();
            $table->string('status_perkawinan', 15)->nullable();
            $table->string('nama_suami_istri', 150)->nullable();
            $table->string('nip_suami_istri', 30)->nullable();
            $table->string('pekerjaan_suami_istri', 100)->nullable();
            $table->string('no_kk', 20)->nullable();

            // Data Bank & Pajak
            $table->string('npwp', 30)->nullable();
            $table->string('nama_wajib_pajak', 150)->nullable();
            $table->string('bank', 50)->nullable();
            $table->string('norek_bank', 50)->nullable();
            $table->string('nama_rek', 150)->nullable();
            
            // Data Kompetensi
            $table->string('lisensi_kepsek', 100)->nullable();
            $table->string('diklat_kepengawasan', 100)->nullable();
            $table->string('keahlian_braille',100)->nullable();
            $table->string('keahlian_isyarat',100)->nullable();
            
            $table->string('kewarganegaraan', 50)->nullable(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_guru');
    }
};