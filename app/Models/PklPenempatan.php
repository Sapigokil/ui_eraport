<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklPenempatan extends Model
{
    use HasFactory;

    // Definisikan nama tabel secara eksplisit
    protected $table = 'pkl_penempatan';

    // Izinkan mass-assignment untuk kolom-kolom ini
    protected $fillable = [
        'id_siswa',
        'id_guru',
        'id_gurusiswa',
        'id_pkltempat',
        'tahun_ajaran',
        'semester',
        'start_date',
        'end_date',
        'status',
    ];
}