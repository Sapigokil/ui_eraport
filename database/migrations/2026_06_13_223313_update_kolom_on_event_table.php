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
        Schema::table('event', function (Blueprint $table) {
            // 1. Menambahkan kolom judul sebelum deskripsi
            $table->string('judul')->after('id_event');
            
            // 2. Menambahkan kolom tanggal_selesai setelah tanggal
            $table->date('tanggal_selesai')->nullable()->after('tanggal');
            
            // 3. Menambahkan kolom target setelah kategori
            $table->string('target')->default('semua')->after('kategori');

            // 4. (Saran Tambahan) Menambahkan status dan lampiran
            $table->string('status')->default('aktif')->after('target');
            $table->string('lampiran')->nullable()->after('status');

            // 5. Menghapus kolom jadwalkan karena sudah diganti tanggal_selesai
            if (Schema::hasColumn('event', 'jadwalkan')) {
                $table->dropColumn('jadwalkan');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event', function (Blueprint $table) {
            // Rollback penambahan kolom
            $table->dropColumn(['judul', 'tanggal_selesai', 'target', 'status', 'lampiran']);
            
            // Kembalikan kolom jadwalkan
            $table->string('jadwalkan')->nullable();
        });
    }
};