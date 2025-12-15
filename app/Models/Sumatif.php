<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sumatif extends Model
{
    use HasFactory;

    protected $table = 'sumatif'; // nama tabel
    protected $primaryKey = 'id_sumatif'; // primary key

    public $timestamps = true; // karena ada created_at & updated_at

    protected $fillable = [
        'id_kelas',
        'id_mapel',
        'id_siswa',
        'nilai',
        'tujuan_pembelajaran',
        'sumatif',
        'tahun_ajaran',
        'semester'
    ];

    /**
     * Relasi ke tabel Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    /**
     * Relasi ke tabel Mapel
     */
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel');
    }

    /**
     * Relasi ke tabel Siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }


}
