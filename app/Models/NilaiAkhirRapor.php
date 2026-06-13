<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiAkhirRapor extends Model
{
    use HasFactory;

    protected $table = 'nilai_akhir_rapor';
    public $timestamps = true;

    protected $fillable = [
        'id_siswa',
        'id_kelas',
        'semester',
        'tahun_ajaran',
        'nama_siswa_snapshot',
        'nisn_snapshot',
        'nipd_snapshot',
        'nama_kelas_snapshot',
        'tingkat',
        'fase',
        'wali_kelas_snapshot',
        'nip_wali_snapshot',
        'kepsek_snapshot',
        'nip_kepsek_snapshot',
        'sakit',
        'ijin',
        'alpha',
        'kokurikuler',
        'catatan_wali_kelas',
        'status_kenaikan', // Penentu Mutasi
        'id_kelas_tujuan', // Tujuan Mutasi
        'data_ekskul',
        'tanggal_cetak',
        'status_data'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }
}