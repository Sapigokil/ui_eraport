<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uji extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_uji',
        'judul',
        'kategori_uji',
        'id_mapel',
        'deskripsi',
        'user_id',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit',
        'acak_soal',
        'open_book',
        'tampilkan_nilai',
        'buka_untuk_umum',
        'kelas_ids',
        'toleransi_pelanggaran',
        'maksimal_percobaan',
        'ulangi_jika_gagal',
        'status',
    ];

    protected $casts = [
        'id_mapel' => 'integer',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'acak_soal' => 'boolean',
        'open_book' => 'boolean',
        'tampilkan_nilai' => 'boolean',
        'buka_untuk_umum' => 'boolean',
        'kelas_ids' => 'array',
        'ulangi_jika_gagal' => 'boolean',
        'toleransi_pelanggaran' => 'integer',
        'maksimal_percobaan' => 'integer',
    ];

    public function setDurasiMenitAttribute($value)
    {
        $this->attributes['durasi_menit'] = empty($value) ? 0 : $value;
    }

    public function soals()
    {
        return $this->hasMany(UjiSoal::class, 'uji_id');
    }

    public function hasils()
    {
        return $this->hasMany(UjiHasil::class, 'uji_id');
    }

    // Relasi hasil peserta yang berstatus 'selesai' (belum difinalisasi penguji)
    public function hasilsSelesai()
    {
        return $this->hasMany(UjiHasil::class, 'uji_id')->where('status_pengerjaan', 'selesai');
    }

    // Relasi ke Pembuat Asesmen (Author)
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi Eloquent opsional ke MataPelajaran (tanpa foreign key DB)
    public function mapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    /**
     * Cek apakah asesmen memiliki soal manual (esai tanpa kunci atau soal objektif tanpa kunci)
     */
    public function butuhPenilaianManual(): bool
    {
        $soals = $this->relationLoaded('soals') ? $this->soals : $this->soals()->get();

        foreach ($soals as $soal) {
            $kunci = is_array($soal->kunci_jawaban)
                ? array_filter($soal->kunci_jawaban, fn($v) => trim((string) $v) !== '')
                : [];

            if ($soal->tipe === 'esai' && empty($kunci)) {
                return true;
            }

            if (in_array($soal->tipe, ['pg', 'pg_kompleks'], true) && empty($kunci)) {
                return true;
            }
        }

        return false;
    }
}