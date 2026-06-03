<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailSiswa extends Model
{
    use HasFactory;

    protected $table = 'detail_siswa';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'id_siswa',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'alamat',
        'kelurahan',
        'kecamatan',
        'kode_pos',
        'no_hp',
        'email',
        'nik',
        'rt',
        'rw',
        'dusun',
        'jenis_tinggal',
        'alat_transportasi',
        'telepon',
        'skhun',
        'penerima_kps',
        'no_kps',
        'rombel',
        'no_peserta_ujian_nasional',
        'no_seri_ijazah',
        'penerima_kip',
        'no_kip',
        'nama_kip',
        'no_kks',
        'no_regis_akta_lahir',
        'bank',
        'no_rek_bank',
        'rek_atas_nama',
        'layak_pip_usulan',
        'alasan_layak_pip',
        'kebutuhan_khusus',
        'sekolah_asal',
        'anak_ke_berapa',
        'lintang',
        'bujur',
        'no_kk',
        'bb',
        'tb',
        'lingkar_kepala',
        'jml_saudara_kandung',
        'jarak_rumah',

        //data ayah
        'nama_ayah',
        'pekerjaan_ayah',
        'tahun_lahir_ayah',
        'jenjang_pendidikan_ayah',
        'penghasilan_ayah',
        'nik_ayah',

        //data ibu
        'nama_ibu',
        'pekerjaan_ibu',
        'tahun_lahir_ibu',
        'jenjang_pendidikan_ibu',
        'penghasilan_ibu',
        'nik_ibu',

        //data wali
        'nama_wali',
        'pekerjaan_wali',
        'tahun_lahir_wali',
        'jenjang_pendidikan_wali',
        'penghasilan_wali',
        'nik_wali',
        'telp_wali',

        // Kode kelas (foreign key)
        'id_kelas',
        'kelas_awal',
        'tgl_masuk',
    ];

    // public function siswa()
    // {
    //     return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    // }
}