<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';
    protected $primaryKey = 'id_guru';
    public $timestamps = false;

    protected $fillable = [
        'nama_guru',
        'nip',
        'nuptk',
        'jenis_kelamin',
        'jenis_ptk',
        'role',
        'status',
    ];

    public function detailGuru()
    {
        return $this->hasOne(DetailGuru::class, 'id_guru', 'id_guru');
    }

    public function pembelajaran()
    {
        return $this->hasMany(Pembelajaran::class, 'id_guru', 'id_guru');
    }
    /**
     * Relasi ke tabel ekskul (One to Many)
     */
    public function ekskul()
    {
        return $this->hasMany(Ekskul::class, 'id_guru', 'id_guru');
    }

    /**
     * Accessor untuk format nama (opsional)
     */
    public function getNamaGuruUpperAttribute()
    {
        return strtoupper($this->nama_guru);
    }

    /**
     * Scope untuk filter guru aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function kelasBinaan()
    {
        return $this->hasOne(Kelas::class, 'id_guru', 'id_guru');
    }
}
