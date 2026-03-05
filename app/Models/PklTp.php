<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklTp extends Model
{
    use HasFactory;
    protected $table = 'pkl_tp';
    protected $fillable = ['nama_tp', 'label_tp', 'no_urut', 'is_active'];
}