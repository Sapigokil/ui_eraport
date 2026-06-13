<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanRapor extends Model
{
    use HasFactory;

    protected $table = 'catatan_rapor';
    protected $primaryKey = 'id_catatan';

    protected $fillable = [
        'id_kelas',
        'id_siswa',
        'kokurikuler',
        'ekskul', // akan disimpan dalam format JSON
    ];

    protected $casts = [
        'ekskul' => 'array', // otomatis decode JSON jadi array
    ];

    // Relasi ke model Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    // Relasi ke model Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }
}
