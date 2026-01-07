<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BobotNilai extends Model
{
    protected $table = 'set_bobot';

    protected $fillable = [
        'jumlah_sumatif',
        'bobot_sumatif',
        'bobot_project',
        'tahun_ajaran',
        'semester',
    ];
}
