<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatKenaikanKelas extends Model
{
    protected $table = 'riwayat_kenaikan_kelas';

    protected $fillable = [
        'id_siswa',
        'id_kelas_lama',
        'id_kelas_baru',
        'tahun_ajaran_lama',
        'tahun_ajaran_baru',
        'status',
        'user_admin'
    ];

    // Relasi ke tabel Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    // Relasi ke tabel Kelas (Lama)
    public function kelasLama()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas_lama', 'id_kelas');
    }

    // Relasi ke tabel Kelas (Baru)
    public function kelasBaru()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas_baru', 'id_kelas');
    }
}