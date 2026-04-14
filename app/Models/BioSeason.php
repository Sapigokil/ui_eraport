<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BioSeason extends Model
{
    use HasFactory;

    protected $table = 'bio_seasons';
    protected $primaryKey = 'id_bio_season';
    
    protected $fillable = [
        'nama_periode',
        'tanggal_mulai',
        'tanggal_akhir',
        'is_active',
    ];
}