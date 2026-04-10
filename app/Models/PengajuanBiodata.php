<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanBiodata extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_biodata';
    protected $primaryKey = 'id_pengajuan';

    protected $fillable = [
        'id_siswa',
        'data_perubahan',
        'status',
        'keterangan_admin'
    ];

    // Mengubah JSON dari database menjadi Array otomatis di PHP
    protected $casts = [
        'data_perubahan' => 'array', 
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }
}