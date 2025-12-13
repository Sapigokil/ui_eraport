<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailGuru extends Model
{
    protected $table = 'detail_guru';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_guru',
        'id_pembelajaran',
        'tempat_lahir',
        'tanggal_lahir',
        'status_kepegawaian',
        'agama',
        'alamat',
        'rt',
        'rw',
        'dusun',
        'kelurahan',
        'kecamatan',
        'kode_pos',
        'no_telp',
        'no_hp',
        'email',
        'tugas_tambahan',
        'sk_cpns',
        'tgl_cpns',
        'sk_pengangkatan',
        'tmt_pengangkatan',
        'lembaga_pengangkatan',
        'pangkat_gol',
        'sumber_gaji',
        'nama_ibu_kandung',
        'status_perkawinan',
        'nama_suami_istri',
        'nip_suami_istri',
        'pekerjaan_suami_istri',
        'tmt_pns',
        'lisensi_kepsek',
        'diklat_kepengawasan',
        'keahlian_braille',
        'keahlian_isyarat',
        'npwp',
        'nama_wajib_pajak',
        'kewarganegaraan',
        'bank',
        'norek_bank',
        'nama_rek',
        'nik',
        'no_kk',
        'karpeg',
        'karis_karsu',
        'lintang',
        'bujur',
        'nuks'
    ];
    public function guru()
{
    return $this->belongsTo(Guru::class, 'guru_id', 'id');
}
}

