<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE uji_soals MODIFY COLUMN tipe ENUM('pg', 'pg_kompleks', 'benar_salah', 'esai') NOT NULL COMMENT 'pg = 1 jawaban, pg_kompleks = >1 jawaban, benar_salah = pernyataan benar/salah, esai = uraian'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE uji_soals MODIFY COLUMN tipe ENUM('pg', 'pg_kompleks', 'esai') NOT NULL COMMENT 'pg = 1 jawaban, pg_kompleks = >1 jawaban, esai = uraian'");
    }
};