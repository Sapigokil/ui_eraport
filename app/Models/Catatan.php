<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catatan extends Model
{
    use HasFactory;

    protected $table = 'catatan';
    protected $primaryKey = 'id_catatan';
    public $timestamps = true;

    protected $fillable = [
        'id_siswa',
        'id_kelas',
        'id_ekskul',
        'kokurikuler',
        'ekskul',
        'predikat',
        'keterangan',
        'sakit',
        'ijin',
        'alpha',
        'catatan_wali_kelas',
        'status_kenaikan', // Tambahan baru
        'id_kelas_tujuan', // Tambahan baru
        'tahun_ajaran',
        'semester',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }
}