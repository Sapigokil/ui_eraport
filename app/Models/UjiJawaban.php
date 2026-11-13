<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjiJawaban extends Model
{
    use HasFactory;

    protected $fillable = [
        'uji_hasil_id',
        'uji_soal_id',
        'jawaban',
        'skor_didapat',
        'ragu_ragu',
    ];

    protected $casts = [
        'ragu_ragu' => 'boolean',
    ];

    public function ujiHasil()
    {
        return $this->belongsTo(UjiHasil::class, 'uji_hasil_id');
    }

    public function ujiSoal()
    {
        return $this->belongsTo(UjiSoal::class, 'uji_soal_id');
    }
}