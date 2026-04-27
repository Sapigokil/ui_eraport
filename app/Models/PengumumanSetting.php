<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengumumanSetting extends Model
{
    use HasFactory;

    protected $table = 'pengumuman_setting';

    protected $guarded = ['id'];

    protected $casts = [
        'waktu_buka' => 'datetime',
        'waktu_tutup' => 'datetime',
        'is_aktif' => 'boolean',
    ];
}