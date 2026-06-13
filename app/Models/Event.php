<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'event';
    protected $primaryKey = 'id_event';
    public $timestamps = true;

    protected $fillable = [
        'judul',
        'deskripsi',
        'tanggal',
        'tanggal_selesai',
        'kategori',
        'target',
        'status',
        'lampiran',
        'created_at',
        'updated_at'
    ];
}