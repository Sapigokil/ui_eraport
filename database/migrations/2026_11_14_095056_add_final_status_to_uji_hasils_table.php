<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `uji_hasils` MODIFY COLUMN `status_pengerjaan` ENUM('berlangsung', 'selesai', 'gagal_pelanggaran', 'final') NOT NULL DEFAULT 'berlangsung'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `uji_hasils` MODIFY COLUMN `status_pengerjaan` ENUM('berlangsung', 'selesai', 'gagal_pelanggaran') NOT NULL DEFAULT 'berlangsung'");
    }
};