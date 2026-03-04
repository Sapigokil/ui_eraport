<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklGuruSiswa extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'pkl_gurusiswa';

    // Mengizinkan mass-assignment untuk kolom-kolom berikut
    protected $fillable = [
        'id_guru',
        'id_siswa',
        'id_kelas',
        'id_pkl_tempat',
        'tahun_ajaran',
        'semester',
        'nama_guru',
        'nama_siswa',
        'nama_kelas',
        'tingkat',
        'jurusan',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * Opsional: Relasi Eloquent untuk memudahkan pemanggilan di Controller.
     * Ini TIDAK membuat constraint di database, murni fitur Laravel.
     * Jika Primary Key di tabel master Anda bukan 'id', silakan sesuaikan argumen ke-3.
     */
    
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru'); 
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function tempatPkl()
    {
        return $this->belongsTo(PklTempat::class, 'id_pkl_tempat', 'id');
    }
}