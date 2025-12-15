<?php
// File: database/migrations/YYYY_MM_DD_add_foreign_keys_to_project_table.php

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
        Schema::table('project', function (Blueprint $table) {
            
            // Cek dan tambahkan FK untuk id_siswa -> siswa
            if (Schema::hasColumn('project', 'id_siswa')) {
                $table->foreign('id_siswa')
                      ->references('id_siswa')->on('siswa')
                      ->onDelete('cascade')
                      ->name('project_id_siswa_foreign');
            }

            // Cek dan tambahkan FK untuk id_mapel -> mata_pelajaran
            if (Schema::hasColumn('project', 'id_mapel')) {
                $table->foreign('id_mapel')
                      ->references('id_mapel')->on('mata_pelajaran')
                      ->onDelete('cascade')
                      ->name('project_id_mapel_foreign');
            }

            // Cek dan tambahkan FK untuk id_kelas -> kelas
            if (Schema::hasColumn('project', 'id_kelas')) {
                $table->foreign('id_kelas')
                      ->references('id_kelas')->on('kelas')
                      ->onDelete('cascade')
                      ->name('project_id_kelas_foreign');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project', function (Blueprint $table) {
            // Hapus FK
            $table->dropForeign('project_id_siswa_foreign');
            $table->dropForeign('project_id_mapel_foreign');
            $table->dropForeign('project_id_kelas_foreign');
        });
    }
};