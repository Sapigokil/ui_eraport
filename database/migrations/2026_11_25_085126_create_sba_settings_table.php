<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sba_settings')) {
            Schema::create('sba_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->json('value')->nullable();
                $table->timestamps();
            });

            // Insert konfigurasi default Background Portal
            DB::table('sba_settings')->insert([
                'key' => 'portal_background',
                'value' => json_encode([
                    'source_type'     => 'unsplash_preset', // unsplash_preset | static_color | unsplash_live | image_url | upload
                    'preset_category' => 'workspace',
                    'image_url'       => 'https://images.unsplash.com/photo-1497294815431-9365093b7331?auto=format&fit=crop&w=1950&q=80',
                    'upload_path'     => null,
                    'color_mode'      => 'solid', // solid | gradient
                    'solid_color'     => '#eef2f7',
                    'gradient_start'  => '#6f42c1',
                    'gradient_end'    => '#3b1d7a',
                    'gradient_angle'  => '135deg',
                    'overlay_opacity' => 0.60,
                    'failover_color'  => '#eef2f7',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sba_settings');
    }
};