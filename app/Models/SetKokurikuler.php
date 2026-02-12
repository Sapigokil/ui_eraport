<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetKokurikuler extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'set_kokurikuler';

    // Primary Key
    protected $primaryKey = 'id_kok';

    // Kolom yang dapat diisi
    protected $fillable = [
        'tingkat',
        'judul',
        'deskripsi',
        'aktif',
        'id_guru',
        'user',
    ];

    /**
     * Jika Anda ingin menghubungkan dengan model User
     * (Asumsi kolom 'user' menyimpan ID pengguna)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user');
    }
}