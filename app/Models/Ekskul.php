<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ekskul extends Model
{
    use HasFactory;

    protected $table = 'ekskul';
    protected $primaryKey = 'id_ekskul';
    public $timestamps = false;

    protected $fillable = ['nama_ekskul', 'jadwal_ekskul', 'id_guru'];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru')->withDefault([
            'nama_guru' => 'Belum Ditentukan',
        ]);
    }

    public function siswaEkskul()
    {
        return $this->hasMany(EkskulSiswa::class, 'id_ekskul', 'id_ekskul');
    }

    public function peserta()
    {
        // Parameter:
        // 1. Siswa::class (Model tujuan)
        // 2. 'ekskul_siswa' (Nama tabel pivot/tengah)
        // 3. 'id_ekskul' (FK Ekskul di tabel pivot)
        // 4. 'id_siswa' (FK Siswa di tabel pivot)
        return $this->belongsToMany(Siswa::class, 'ekskul_siswa', 'id_ekskul', 'id_siswa');
    }

    public function nilai()
    {
        return $this->hasMany(NilaiEkskul::class, 'id_ekskul', 'id_ekskul');
    }

}

