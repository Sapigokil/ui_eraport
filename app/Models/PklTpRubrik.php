<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklTpRubrik extends Model
{
    use HasFactory;
    protected $table = 'pkl_tp_rubrik';
    protected $fillable = [
        'id_pkl_tp_indikator', 'predikat', 'min_nilai', 'max_nilai', 
        'deskripsi_rubrik', 'teks_untuk_rapor'
    ];
}