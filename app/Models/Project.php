<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'project';
    protected $primaryKey = 'id_project';

    protected $fillable = [
        'id_siswa',
        'id_mapel',
        'id_kelas',
        'tahun_ajaran',
        'semester',
        'nilai',
        'nilai_bobot',
        'tujuan_pembelajaran'
    ];

    public $timestamps = true;

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
