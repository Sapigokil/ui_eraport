<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PklSeason extends Model
{
    use HasFactory;

    protected $table = 'pkl_season';

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'is_open',
        'is_active',
        'start_date',
        'end_date'
    ];

    public $timestamps = true;

    /**
     * Ambil season aktif yang sedang berjalan dan bisa digunakan untuk input nilai
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
     * Cek apakah saat ini Guru diizinkan melakukan Input/Edit nilai PKL
     */
    public function isOpen(): bool
    {
        $today = Carbon::today();

        // Jika manual dikunci oleh Admin
        if ($this->is_open == 0) {
            return false;
        }

        // Jika season aktif & tombol dibuka manual
        if ($this->is_active == 1 && $this->is_open == 1) {
            // Validasi tambahan jika tanggal ditentukan
            if ($this->start_date && $this->end_date) {
                return $today->between(Carbon::parse($this->start_date), Carbon::parse($this->end_date));
            }
            return true;
        }

        // Jika season tidak diset "is_active", tapi masih dalam rentang waktu yang diperbolehkan
        if ($this->start_date && $this->end_date) {
            return $today->between(Carbon::parse($this->start_date), Carbon::parse($this->end_date));
        }

        return false;
    }

    /**
     * Helper Mutator untuk nama Semester
     */
    public function getSemesterTextAttribute(): string
    {
        return $this->semester == 1 ? 'Ganjil' : 'Genap';
    }
}