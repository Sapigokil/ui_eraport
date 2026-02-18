<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPindahKelas extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pindah_kelas';
    
    protected $fillable = [
        'id_siswa',
        'id_kelas_lama',
        'id_kelas_baru',
        'tgl_pindah',
        'alasan',
        'user_input',
    ];

    /**
     * Relasi ke Siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    /**
     * Relasi ke Kelas Lama
     */
    public function kelasLama()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas_lama', 'id_kelas');
    }

    /**
     * Relasi ke Kelas Baru
     */
    public function kelasBaru()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas_baru', 'id_kelas');
    }
}