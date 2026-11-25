<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SbaSetting extends Model
{
    protected $table = 'sba_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Ambil nilai setting berdasarkan key dengan default fallback
     */
    public static function getValue(string $key, array $default = []): array
    {
        $record = static::where('key', $key)->first();
        if (!$record || !is_array($record->value)) {
            return $default;
        }

        return array_merge($default, $record->value);
    }

    /**
     * Simpan atau perbarui nilai setting berdasarkan key
     */
    public static function setValue(string $key, array $data): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $data]
        );
    }

    /**
     * Default konfigurasi Background Portal SBA
     */
    public static function defaultPortalBackground(): array
    {
        return [
            'source_type'     => 'unsplash_preset',
            'preset_category' => 'workspace',
            'image_url'       => 'https://images.unsplash.com/photo-1497294815431-9365093b7331?auto=format&fit=crop&w=1950&q=80',
            'upload_path'     => null,
            'color_mode'      => 'solid',
            'solid_color'     => '#eef2f7',
            'gradient_start'  => '#6f42c1',
            'gradient_end'    => '#3b1d7a',
            'gradient_angle'  => '135deg',
            'overlay_opacity' => 0.60,
            'failover_color'  => '#eef2f7',
        ];
    }

    /**
     * Ambil konfigurasi Background Portal beserta URL aktif yang siap dirender di view
     */
    public static function getPortalBackground(): array
    {
        $config = static::getValue('portal_background', static::defaultPortalBackground());

        $resolvedImageUrl = null;
        if ($config['source_type'] === 'upload' && !empty($config['upload_path'])) {
            if (Storage::disk('public')->exists($config['upload_path'])) {
                $resolvedImageUrl = asset('storage/' . $config['upload_path']);
            }
        } elseif (in_array($config['source_type'], ['unsplash_preset', 'unsplash_live', 'image_url'], true)) {
            $resolvedImageUrl = $config['image_url'] ?? null;
        }

        $config['resolved_image_url'] = $resolvedImageUrl;

        return $config;
    }

    /**
     * Lifecycle Management: Hapus otomatis file upload lama jika diganti file baru atau pindah mode
     */
    protected static function booted()
    {
        static::updating(function (self $setting) {
            if ($setting->key !== 'portal_background' || !$setting->isDirty('value')) {
                return;
            }

            $oldRaw = $setting->getOriginal('value');
            $oldVal = is_array($oldRaw) ? $oldRaw : (json_decode((string) $oldRaw, true) ?? []);
            $newVal = is_array($setting->value) ? $setting->value : [];

            $oldPath = $oldVal['upload_path'] ?? null;
            $newPath = $newVal['upload_path'] ?? null;

            if (!empty($oldPath) && $oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        });
    }
}