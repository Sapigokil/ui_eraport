<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklNilaiSiswa extends Model
{
    use HasFactory;

    protected $table = 'pkl_nilaisiswa';

    protected $fillable = [
        'id_penempatan',
        'id_pkl_tp',
        'data_indikator',
        'nilai_rata_rata',
        'deskripsi_gabungan',
        'created_by'
    ];

    // Casting otomatis JSON ke Array
    protected $casts = [
        'data_indikator' => 'array',
        'nilai_rata_rata' => 'decimal:2',
    ];
}