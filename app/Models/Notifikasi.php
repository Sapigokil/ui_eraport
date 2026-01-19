<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';

    protected $fillable = [
        'deskripsi',
        'tanggal',
        'kategori',
        'created_at',
        'updated_at'
    ];
}
