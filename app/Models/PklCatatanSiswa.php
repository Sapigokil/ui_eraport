<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklCatatanSiswa extends Model
{
    use HasFactory;

    protected $table = 'pkl_catatansiswa';

    protected $fillable = [
        'id_penempatan',
        'id_guru', // Tambahan Baru
        'sakit',
        'izin',
        'alpa',
        'program_keahlian',     // Tambahan Baru
        'konsentrasi_keahlian', // Tambahan Baru
        'tanggal_mulai',        // Tambahan Baru
        'tanggal_selesai',      // Tambahan Baru
        'nama_instruktur',      // Tambahan Baru
        'catatan_pembimbing',
        'status_penilaian',
        'created_by'
    ];

    protected $casts = [
        'status_penilaian' => 'integer',
        'sakit' => 'integer',
        'izin' => 'integer',
        'alpa' => 'integer',
    ];
}