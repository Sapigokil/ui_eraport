<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UjiHasil extends Model
{
    protected $table = 'uji_hasils';

    protected $fillable = [
        'uji_id',
        'asesi_id',
        'nama_peserta_publik',
        'percobaan_ke',
        'waktu_mulai',
        'waktu_selesai',
        'skor_otomatis',
        'total_skor',
        'status_pengerjaan',
        'jumlah_pelanggaran',
        'catatan_pelanggaran',
    ];

    // PENTING: Cast kolom waktu menjadi datetime (Carbon)
    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function uji()
    {
        return $this->belongsTo(Uji::class, 'uji_id');
    }

    public function asesi()
    {
        return $this->belongsTo(User::class, 'asesi_id');
    }

    public function jawabans()
    {
        return $this->hasMany(UjiJawaban::class, 'uji_hasil_id');
    }

    /**
     * Helper Accessor Durasi Pengerjaan
     */
    public function getDurasiFormattedAttribute()
    {
        if (!$this->waktu_mulai || !$this->waktu_selesai) {
            return $this->status_pengerjaan === 'berlangsung' ? 'Sedang Berjalan' : '-';
        }

        // Kalkulasi total selisih detik secara presisi
        $totalDetik = $this->waktu_mulai->diffInSeconds($this->waktu_selesai);

        $jam = floor($totalDetik / 3600);
        $menit = floor(($totalDetik % 3600) / 60);
        $detik = $totalDetik % 60;

        if ($jam > 0) {
            return sprintf('%02d Jam %02d Menit %02d Detik', $jam, $menit, $detik);
        }

        return sprintf('%02d Menit %02d Detik', $menit, $detik);
    }
}