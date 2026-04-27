<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengumumanSiswa extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'pengumuman_siswa';

    // Mengizinkan mass assignment untuk semua kolom
    protected $guarded = ['id'];

    // Casting tipe data
    protected $casts = [
        'has_seen'      => 'boolean', // Menerjemahkan 0/1 menjadi false/true
        'waktu_dilihat' => 'datetime', // Menjadikan kolom ini sebagai instance Carbon
    ];

    /**
     * Relasi ke tabel Siswa
     */
    public function siswa()
    {
        // Parameter: (NamaModel::class, 'foreign_key', 'owner_key')
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    /**
     * Fungsi Helper untuk menandai pengumuman sudah dibaca
     */
    public function tandaiSudahDibaca()
    {
        $this->update([
            'has_seen'      => 1,
            'waktu_dilihat' => now(),
        ]);
    }
}