<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklRaporSiswa extends Model
{
    use HasFactory;

    protected $table = 'pkl_raporsiswa';
    protected $guarded = []; // Mengizinkan mass assignment untuk semua kolom

    public function detailNilai()
    {
        return $this->hasMany(PklRaporNilai::class, 'id_pkl_raporsiswa', 'id');
    }
}