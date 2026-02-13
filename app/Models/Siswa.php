<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'id_siswa';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nipd', 'nisn', 'nama_siswa', 
        'jenis_kelamin', 'tingkat', 'id_kelas', 'id_ekskul', 'status'
    ];

    public function detail()
    {
        return $this->hasOne(DetailSiswa::class, 'id_siswa');
    }

    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'id_ekskul');
    }
    
    public function nilai_akhir()
    {
        return $this->hasMany(NilaiAkhir::class, 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }
    
    public function nilai_ekskul()
    {
        return $this->hasMany(NilaiEkskul::class, 'id_siswa', 'id_siswa');
    }
    
    public function catatan()
    {
        // Parameter: Model Tujuan, Foreign Key di tabel tujuan, Primary Key di tabel siswa
        return $this->hasMany(Catatan::class, 'id_siswa', 'id_siswa');
    }

}


