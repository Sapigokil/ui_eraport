<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UjiSoal extends Model
{
    protected $table = 'uji_soals';

    protected $fillable = [
        'uji_id',
        'tipe',
        'pertanyaan',
        'media', // Menggunakan kolom media existing untuk path file gambar soal
        'opsi_jawaban',
        'opsi_media', // Menyimpan array path gambar per indeks opsi PG/PGK
        'kunci_jawaban',
        'bobot_nilai',
    ];

    protected $casts = [
        'opsi_jawaban' => 'array',
        'opsi_media' => 'array',
        'kunci_jawaban' => 'array',
    ];

    public function uji()
    {
        return $this->belongsTo(Uji::class, 'uji_id');
    }

    /**
     * Accessor untuk mendapatkan URL Gambar/Media Soal secara langsung
     */
    public function getMediaUrlAttribute()
    {
        if ($this->media && Storage::disk('public')->exists($this->media)) {
            return asset('storage/' . $this->media);
        }
        return null;
    }

    /**
     * Accessor untuk mendapatkan array URL Gambar pada masing-masing Opsi Jawaban
     */
    public function getOpsiMediaUrlsAttribute(): array
    {
        $opsiMedia = is_array($this->opsi_media) ? $this->opsi_media : [];
        $urls = [];

        foreach ($opsiMedia as $index => $path) {
            if (!empty($path) && Storage::disk('public')->exists($path)) {
                $urls[$index] = asset('storage/' . $path);
            } else {
                $urls[$index] = null;
            }
        }

        return $urls;
    }

    /**
     * Lifecycle Management Pembersihan File Sampah (Anti-Junk File)
     */
    protected static function booted()
    {
        // 1. Otomatis hapus file media fisik soal & seluruh gambar opsi dari storage saat soal dihapus
        static::deleting(function ($soal) {
            if ($soal->media && Storage::disk('public')->exists($soal->media)) {
                Storage::disk('public')->delete($soal->media);
            }

            $opsiMedia = is_array($soal->opsi_media) ? $soal->opsi_media : [];
            foreach ($opsiMedia as $path) {
                if (!empty($path) && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        });

        // 2. Otomatis hapus file media lama dari storage jika gambar soal atau gambar opsi di-edit / diganti / dihapus
        static::updating(function ($soal) {
            if ($soal->isDirty('media')) {
                $mediaLama = $soal->getOriginal('media');
                if ($mediaLama && Storage::disk('public')->exists($mediaLama)) {
                    Storage::disk('public')->delete($mediaLama);
                }
            }

            if ($soal->isDirty('opsi_media')) {
                $originalOpsiMedia = $soal->getOriginal('opsi_media');
                if (is_string($originalOpsiMedia)) {
                    $originalOpsiMedia = json_decode($originalOpsiMedia, true) ?? [];
                }
                $originalOpsiMedia = is_array($originalOpsiMedia) ? $originalOpsiMedia : [];
                $newOpsiMedia = is_array($soal->opsi_media) ? $soal->opsi_media : [];

                foreach ($originalOpsiMedia as $oldPath) {
                    if (!empty($oldPath) && !in_array($oldPath, $newOpsiMedia, true) && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
            }
        });
    }
}