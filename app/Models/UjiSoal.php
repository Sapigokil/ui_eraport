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
        'media', // Menggunakan kolom media existing untuk path file gambar
        'opsi_jawaban',
        'kunci_jawaban',
        'bobot_nilai',
    ];

    protected $casts = [
        'opsi_jawaban' => 'array',
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
     * Lifecycle Management Pembersihan File Sampah (Anti-Junk File)
     */
    protected static function booted()
    {
        // 1. Otomatis hapus file media fisik dari storage server saat soal dihapus
        static::deleting(function ($soal) {
            if ($soal->media && Storage::disk('public')->exists($soal->media)) {
                Storage::disk('public')->delete($soal->media);
            }
        });

        // 2. Otomatis hapus file media lama dari storage server jika gambar di-edit / diganti / dihapus
        static::updating(function ($soal) {
            if ($soal->isDirty('media')) {
                $mediaLama = $soal->getOriginal('media');
                if ($mediaLama && Storage::disk('public')->exists($mediaLama)) {
                    Storage::disk('public')->delete($mediaLama);
                }
            }
        });
    }
}