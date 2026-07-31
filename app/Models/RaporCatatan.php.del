<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RaporCatatan extends Model // 🛑 Renamed
{
    protected $table = 'catatan'; // Nama tabel tetap 'catatan'
    protected $primaryKey = 'id_catatan';
    public $timestamps = true;

    protected $fillable = [
        'id_siswa',
        'id_kelas',
        'id_ekskul',
        'konkurikuler',
        'keterangan',
        'sakit',
        'ijin',
        'alpha',
        'catatan_wali_kelas',
        'tahun_ajaran', 
        'semester'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'id_ekskul', 'id_ekskul');
    }
}