<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatKenaikanKelas extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'riwayat_kenaikan_kelas';

    // Sangat penting agar updateOrCreate berfungsi
    protected $primaryKey = 'id_riwayat';

    // Kolom yang diizinkan untuk diisi secara massal
    protected $fillable = [
        'id_siswa',
        'id_kelas_lama',
        'id_kelas_baru',
        'tahun_ajaran_lama',
        'tahun_ajaran_baru',
        'status',           
        'user_admin',
        'status_eksekusi',
        'file_skl', // Kolom baru untuk menyimpan nama file SKL
    ];

    /**
     * Relasi ke model Siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    /**
     * Relasi ke model Kelas (Kelas Asal)
     */
    public function kelasLama()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas_lama', 'id_kelas');
    }

    /**
     * Relasi ke model Kelas (Kelas Tujuan)
     * 👇 INI ADALAH FUNGSI YANG DICARI OLEH CONTROLLER 👇
     */
    public function kelasBaru()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas_baru', 'id_kelas');
    }

    /**
     * Scope untuk mempermudah filter data yang sudah final (Halaman Riwayat)
     */
    public function scopeFinal($query)
    {
        return $query->where('status_eksekusi', 'final');
    }

    /**
     * Scope untuk mempermudah filter data yang masih draft (Halaman Dashboard/Eksekusi)
     */
    public function scopeDraft($query)
    {
        return $query->where('status_eksekusi', 'draft');
    }
}