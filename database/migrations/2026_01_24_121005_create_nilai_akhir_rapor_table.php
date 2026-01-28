<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cek apakah tabel sudah ada, jika belum buat tabelnya
        if (!Schema::hasTable('nilai_akhir_rapor')) {
            Schema::create('nilai_akhir_rapor', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        // 2. Modifikasi tabel untuk menambahkan kolom jika belum ada
        Schema::table('nilai_akhir_rapor', function (Blueprint $table) {
            
            // Identitas Siswa & Waktu
            if (!Schema::hasColumn('nilai_akhir_rapor', 'id_siswa')) {
                $table->foreignId('id_siswa')->index()->after('id');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'tahun_ajaran')) {
                $table->string('tahun_ajaran', 10)->after('id_siswa');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'semester')) {
                $table->tinyInteger('semester')->after('tahun_ajaran');
            }

            // Snapshot Identitas Kelas
            if (!Schema::hasColumn('nilai_akhir_rapor', 'id_kelas')) {
                $table->foreignId('id_kelas')->nullable()->after('semester');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'nama_kelas_snapshot')) {
                $table->string('nama_kelas_snapshot', 50)->nullable()->after('id_kelas');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'tingkat')) {
                $table->tinyInteger('tingkat')->nullable()->after('nama_kelas_snapshot');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'fase')) {
                $table->char('fase', 1)->nullable()->after('tingkat');
            }

            // Snapshot Pejabat
            if (!Schema::hasColumn('nilai_akhir_rapor', 'wali_kelas_snapshot')) {
                $table->string('wali_kelas_snapshot', 150)->nullable()->after('fase');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'nip_wali_snapshot')) {
                $table->string('nip_wali_snapshot', 50)->nullable()->after('wali_kelas_snapshot');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'kepsek_snapshot')) {
                $table->string('kepsek_snapshot', 150)->nullable()->after('nip_wali_snapshot');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'nip_kepsek_snapshot')) {
                $table->string('nip_kepsek_snapshot', 50)->nullable()->after('kepsek_snapshot');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'tanggal_cetak')) {
                $table->date('tanggal_cetak')->nullable()->after('nip_kepsek_snapshot');
            }

            // Data Absensi
            if (!Schema::hasColumn('nilai_akhir_rapor', 'sakit')) {
                $table->integer('sakit')->default(0)->after('tanggal_cetak');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'izin')) {
                $table->integer('izin')->default(0)->after('sakit');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'alpha')) {
                $table->integer('alpha')->default(0)->after('izin');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'dispensasi')) {
                $table->integer('dispensasi')->default(0)->after('alpha');
            }

            // Data Catatan
            if (!Schema::hasColumn('nilai_akhir_rapor', 'catatan_akademik')) {
                $table->text('catatan_akademik')->nullable()->after('dispensasi');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'catatan_wali_kelas')) {
                $table->text('catatan_wali_kelas')->nullable()->after('catatan_akademik');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'catatan_p5')) {
                $table->text('catatan_p5')->nullable()->after('catatan_wali_kelas');
            }
            
            // Data Ekstrakurikuler & Status
            if (!Schema::hasColumn('nilai_akhir_rapor', 'data_ekskul')) {
                $table->json('data_ekskul')->nullable()->after('catatan_p5');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'status_kenaikan')) {
                $table->enum('status_kenaikan', ['naik', 'tinggal', 'lulus', 'tidak_lulus', 'proses'])->default('proses')->after('data_ekskul');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'status_data')) {
                $table->enum('status_data', ['draft', 'final', 'arsip_mutasi'])->default('draft')->after('status_kenaikan');
            }

            // --- PROTEKSI UNIQUE INDEX ---
            // Kita gunakan raw query untuk mengecek apakah index sudah ada di database MySQL
            $conn = Schema::getConnection();
            $dbName = $conn->getDatabaseName();
            $indexExists = DB::select("
                SELECT INDEX_NAME 
                FROM INFORMATION_SCHEMA.STATISTICS 
                WHERE TABLE_SCHEMA = '$dbName' 
                AND TABLE_NAME = 'nilai_akhir_rapor' 
                AND INDEX_NAME = 'unique_rapor_per_semester'
            ");

            if (empty($indexExists)) {
                $table->unique(['id_siswa', 'tahun_ajaran', 'semester'], 'unique_rapor_per_semester');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_akhir_rapor');
    }
};