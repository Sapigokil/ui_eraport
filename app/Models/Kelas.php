<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Pastikan Import Model lain ada
use App\Models\AnggotaKelas;
use App\Models\Siswa;
use App\Models\Guru; 

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';
    public $timestamps = false; // Tetap false sesuai code asli

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan',
        'wali_kelas',   // Legacy (String Nama) - TETAP ADA
        'id_guru',      // Baru (Relasi ID) - TETAP ADA
        'jumlah_siswa',
        'id_anggota',
    ];

    /**
     * ==========================================
     * 1. RELASI DATABASE (RELATIONSHIPS)
     * ==========================================
     */

    /**
     * Relasi ke Guru (Wali Kelas)
     * Menggunakan 'id_guru' sebagai foreign key
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    /**
     * Relasi ke Siswa
     */
    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'id_kelas', 'id_kelas');
    }

    /**
     * Relasi ke tabel AnggotaKelas (DIKEMBALIKAN)
     */
    public function anggotaKelas()
    {
        return $this->hasMany(AnggotaKelas::class, 'id_kelas', 'id_kelas');
    }

    /**
     * ==========================================
     * 2. SCOPES (FILTER QUERY)
     * ==========================================
     */

    /**
     * Scope untuk filter berdasarkan jurusan (DIKEMBALIKAN)
     */
    public function scopeJurusan($query, $jurusan)
    {
        return $query->where('jurusan', $jurusan);
    }

    /**
     * ==========================================
     * 3. ACCESSOR (ATRIBUT PINTAR)
     * ==========================================
     */

    /**
     * Mengambil Fase Kurikulum berdasarkan Tingkat
     */
    public function getFaseAttribute()
    {
        $tingkat = (int) $this->tingkat;

        return match ($tingkat) {
            10 => 'E',
            11, 12 => 'F',
            default => '-',
        };
    }

    /**
     * SMART ACCESSOR: NAMA WALI KELAS
     * Otomatis pilih: Data dari Tabel Guru (jika ada relasi) ATAU String Manual
     * Panggil di view: {{ $kelas->nama_wali }}
     */
    public function getNamaWaliAttribute()
    {
        // 1. Cek via Relasi (Prioritas Utama - Paling Akurat)
        if ($this->guru) {
            return $this->guru->nama_guru;
        }

        // 2. Fallback ke Kolom String Manual (Legacy Data)
        return $this->attributes['wali_kelas'] ?? '-';
    }
    
    /**
     * SMART ACCESSOR: NIP WALI KELAS
     * Panggil di view: {{ $kelas->nip_wali }}
     */
    public function getNipWaliAttribute()
    {
        // Jika ada relasi guru, ambil NIP-nya. Jika tidak, kosong.
        return $this->guru->nip ?? '-';
    }
}