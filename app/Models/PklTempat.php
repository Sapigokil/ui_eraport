<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklTempat extends Model
{
    use HasFactory;

    /**
     * Menentukan nama tabel secara eksplisit
     *
     * @var string
     */
    protected $table = 'pkl_tempat';

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignment)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'guru_id',
        'nama_perusahaan',
        'bidang_usaha',
        'nama_pimpinan',
        'alamat_perusahaan',
        'kota',
        'no_telp_perusahaan',
        'email_perusahaan',
        'no_surat_mou',
        'tanggal_mou',
        'nama_instruktur',
        'no_telp_instruktur',
        'is_active',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data native
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_mou' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi Logis (Soft Relation) ke model Guru
     * Parameter ke-3 ('id_guru') secara spesifik merujuk ke Primary Key di tabel gurus
     */
    public function guru()
    {
        // Pastikan 'App\Models\Guru' sesuai dengan namespace Model Guru di aplikasi Anda
        return $this->belongsTo(Guru::class, 'guru_id', 'id_guru');
    }
}