<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiEkskul extends Model
{
    use HasFactory;

    // 1. Konfigurasi Tabel
    protected $table = 'nilai_ekskul'; // Nama tabel di database
    protected $primaryKey = 'id_nilai_ekskul'; // Primary key custom
    public $incrementing = true; // Karena ID auto-increment
    protected $keyType = 'int';

    // 2. Kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'id_ekskul',
        'id_siswa',
        'id_kelas',      // Snapshot kelas saat nilai diberikan
        'tahun_ajaran',  // Contoh: 2025/2026
        'semester',      // 1 = Ganjil, 2 = Genap
        'predikat',      // Sangat Baik, Baik, Cukup, Kurang
        'keterangan',    // Deskripsi singkat
    ];

    // 3. Relasi ke Tabel Lain

    /**
     * Relasi ke Siswa (Milik Siapa nilai ini?)
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    /**
     * Relasi ke Ekskul (Nilai untuk ekskul apa?)
     */
    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'id_ekskul', 'id_ekskul');
    }

    /**
     * Relasi ke Kelas (Siswa berada di kelas mana saat dinilai?)
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    // 4. Accessor (Opsional / Helper)

    /**
     * Helper untuk menampilkan teks semester (Ganjil/Genap)
     * Cara pakai: $nilai->semester_label
     */
    public function getSemesterLabelAttribute()
    {
        return $this->semester == 1 ? 'Ganjil' : 'Genap';
    }
}