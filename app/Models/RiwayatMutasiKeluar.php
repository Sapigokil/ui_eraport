<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatMutasiKeluar extends Model
{
    use HasFactory;
    protected $table = 'riwayat_mutasi_keluar';
    protected $guarded = [];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
    
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas_terakhir', 'id_kelas');
    }

    // Tambahkan ini di dalam class RiwayatMutasiKeluar
    public function kelasTerakhir()
    {
        // Parameter: (Nama Model, 'foreign_key', 'owner_key')
        return $this->belongsTo(Kelas::class, 'id_kelas_terakhir', 'id_kelas');
    }
}