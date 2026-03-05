<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklTpIndikator extends Model
{
    use HasFactory;
    protected $table = 'pkl_tp_indikator';
    protected $fillable = ['id_pkl_tp', 'nama_indikator', 'no_urut'];
}