<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uji extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_uji',
        'judul',
        'deskripsi',
        'user_id',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit',
        'acak_soal',
        'tampilkan_nilai',
        'buka_untuk_umum',
        'toleransi_pelanggaran',
        'maksimal_percobaan',
        'ulangi_jika_gagal',
        'status',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'acak_soal' => 'boolean',
        'tampilkan_nilai' => 'boolean',
        'buka_untuk_umum' => 'boolean',
        'ulangi_jika_gagal' => 'boolean',
        'toleransi_pelanggaran' => 'integer',
        'maksimal_percobaan' => 'integer',
    ];

    public function setDurasiMenitAttribute($value)
    {
        $this->attributes['durasi_menit'] = empty($value) ? 0 : $value;
    }

    public function soals()
    {
        return $this->hasMany(UjiSoal::class, 'uji_id');
    }

    public function hasils()
    {
        return $this->hasMany(UjiHasil::class, 'uji_id');
    }

    // Relasi ke Pembuat Asesmen (Author)
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}