<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Season extends Model
{

    use HasFactory;

    // Nama tabel & primary key
    protected $table = 'season';
    // protected $primaryKey = 'id_season';

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'is_open',
        'is_active',
        'start_date', // tanggal mulai input nilai
        'end_date'    // tanggal akhir input nilai
    ];

    // Gunakan timestamps
    public $timestamps = true;

    // ========================
    // METHOD YANG PERLU DITAMBAH
    // ========================

    /**
     * Ambil season aktif yang bisa dibuka (untuk input nilai)
     */
    public static function currentOpen(): ?self
    {
        $season = self::where('is_active', 1)->first();

        if ($season && $season->isOpen()) {
            return $season;
        }

        return null;
    }

    /**
     * Cek apakah input nilai bisa dibuka
     * 
     * Logikanya:
     * 1. Kalau manual dikunci (is_open = 0), dikunci
     * 2. Kalau season aktif & is_open manual = 1, bisa buka
     * 3. Kalau ada start & end date, cek hari ini termasuk rentang tersebut
     */
    public function isOpen(): bool
    {
        $today = Carbon::today();

        // Manual dikunci = false
        if ($this->is_open == 0) {
            return false;
        }

        // Season aktif & manual dibuka = true
        if ($this->is_active == 1 && $this->is_open == 1) {
            // Cek juga tanggal kalau tersedia
            if ($this->start_date && $this->end_date) {
                return $today->between(Carbon::parse($this->start_date), Carbon::parse($this->end_date));
            }
            return true;
        }

        // Jika ada tanggal tapi is_active = 0 atau is_open = 1, tetap cek tanggal
        if ($this->start_date && $this->end_date) {
            return $today->between(Carbon::parse($this->start_date), Carbon::parse($this->end_date));
        }

        // Default false
        return false;
    }

    /**
     * Helper untuk menampilkan semester dalam bentuk teks
     */
    public function getSemesterTextAttribute(): string
    {
        return $this->semester == 1 ? 'Ganjil' : 'Genap';
    }
}
