<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiAkhir extends Model
{
    protected $table = 'nilai_akhir';

    protected $primaryKey = 'id_nilai_akhir';

    protected $fillable = [
        'id_kelas',
        'id_mapel',
        'id_siswa',
        'tahun_ajaran',
        'semester',
        'nilai_s1',
        'nilai_s2',
        'nilai_s3',
        'rata_sumatif',
        'bobot_sumatif',
        'nilai_project',
        'rata_project',
        'bobot_project',
        'nilai_akhir',
        'capaian_akhir',
    ];

    protected $casts = [
        'nilai_s1' => 'integer',
        'nilai_s2' => 'integer',
        'nilai_s3' => 'integer',
        'rata_sumatif' => 'decimal:2',
        'bobot_sumatif' => 'decimal:2',
        'nilai_project' => 'decimal:2',
        'rata_project' => 'decimal:2',
        'bobot_project' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
    ];

    public $timestamps = true;

    // ===================================
    // 🛑 RELASI BELONGS TO 🛑
    // ===================================

    /**
     * Relasi ke tabel Kelas
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    /**
     * Relasi ke tabel Mata Pelajaran
     */
    public function mapel(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    /**
     * Relasi ke tabel Siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }
}
