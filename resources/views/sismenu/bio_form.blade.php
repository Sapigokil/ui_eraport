@extends('layouts.app') 

@section('page-title', 'Form Pembaruan Biodata')

@section('content')

{{-- Inisialisasi Variabel Opsi Dropdown --}}
@php
    $opsi_penghasilan = [
        'Tidak Berpenghasilan',
        'Kurang dari Rp. 500,000',
        'Rp. 500,000 - Rp. 999.000',
        'Rp. 1,000,000 - Rp. 1,999,999',
        'Rp. 2,000,000 - Rp. 4,999,999',
        'Rp. 5,000,000 - Rp. 20,000,000',
        'Lebih dari Rp. 20,000,000'
    ];

    $opsi_pendidikan = [
        'Tidak sekolah',
        'PAUD',
        'Putus SD',
        'SD / sederajat',
        'SMP / sederajat',
        'SMA / sederajat',
        'D1',
        'D2',
        'D3',
        'D4',
        'S1',
        'S2',
        'S3'
    ];

    // Array Pekerjaan Standar Dapodik Berkelompok
    $opsi_pekerjaan = [
        'Sektor ASN/Pemerintahan/TNI/Polri' => [
            'TNI', 
            'POLRI', 
            'PNS', 
            'ASN PPPK'
        ],
        'Sektor Profesional & Bisnis' => [
            'Karyawan Swasta', 
            'Wiraswasta', 
            'Wirausaha', 
            'Buruh', 
            'Pedagang Kecil', 
            'Pedagang Besar', 
            'Petani', 
            'Peternak', 
            'Nelayan', 
            'Tukang', 
            'Tenaga Kerja Indonesia (TKI)'
        ],
        'Sektor Lainnya & Profesional Khusus' => [
            'Pensiunan', 
            'Tenaga Medis', 
            'Guru/Dosen', 
            'Pengacara/Jaksa/Hakim', 
            'Seniman/Pelukis/Artis/Sejenis', 
            'Sopir/Driver', 
            'Karyawan BUMN/BUMD', 
            'Ibu Rumah Tangga',
            'Orang Tidak Bekerja'
        ],
        'Opsi Tambahan/Khusus' => [
            'Tidak Dapat Ditemukan', 
            'Sudah Meninggal'
        ]
    ];

    $tahunSekarang = date('Y');
@endphp

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="text-dark font-weight-bolder mb-0">Lengkapi Biodata Diri</h5>
            <a href="{{ route('sis.biodata') }}" class="btn btn-sm btn-outline-secondary mb-0">
                <i class="fas fa-arrow-left me-1"></i> Batal
            </a>
        </div>

        <div class="alert alert-secondary text-dark text-sm shadow-sm border-0 mb-4" role="alert">
            <i class="fas fa-lightbulb text-warning me-2"></i> 
            Data di bawah ini adalah data Anda saat ini. Kolom dengan tanda bintang merah (<span class="text-danger">*</span>) <strong>wajib diisi</strong>. Pastikan format isian (seperti NIK 16 digit dan Tahun Lahir 4 digit) sudah sesuai sebelum mengirim.
        </div>

        {{-- BLOCK PENAMPIL ERROR DARI CONTROLLER --}}
        @if ($errors->any())
            <div class="alert bg-gradient-danger alert-dismissible text-white fade show mb-4" role="alert" id="alertValidationErrors">
                <span class="text-sm"><i class="fas fa-exclamation-triangle me-2"></i><strong>Gagal Menyimpan:</strong> Periksa kembali isian berikut:</span>
                <ul class="mb-0 text-sm ps-4 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
            </div>
        @endif

        <form action="{{ route('sis.biodata.ajukan') }}" method="POST" id="formBiodataSiswa">
            @csrf

            <div class="row">
                {{-- ======================================================== --}}
                {{-- CARD 1: DATA PRIBADI, DOKUMEN & ALAMAT --}}
                {{-- ======================================================== --}}
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-bottom p-3">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-address-card me-2"></i> Identitas Diri, Dokumen & Fisik</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                {{-- Baris 1: Identitas Dasar --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Tempat Lahir <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" name="tempat_lahir" maxlength="100" value="{{ old('tempat_lahir', $siswa->detail->tempat_lahir ?? '') }}" required>
                                    @error('tempat_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{ old('tanggal_lahir', $siswa->detail->tanggal_lahir ?? '') }}" required>
                                    @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Agama <span class="text-danger">*</span></label>
                                    <select class="form-control @error('agama') is-invalid @enderror" name="agama" required>
                                        <option value="">-- Pilih Agama --</option>
                                        @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm)
                                            <option value="{{ $agm }}" {{ old('agama', $siswa->detail->agama ?? '') == $agm ? 'selected' : '' }}>{{ $agm }}</option>
                                        @endforeach
                                    </select>
                                    @error('agama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Sekolah Asal <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('sekolah_asal') is-invalid @enderror" name="sekolah_asal" maxlength="150" value="{{ old('sekolah_asal', $siswa->detail->sekolah_asal ?? '') }}" placeholder="Contoh: SMPN 1 Salatiga" required>
                                    @error('sekolah_asal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Baris 2: Kelengkapan Dokumen --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">NIK (16 Digit Angka) <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="numeric" pattern="\d{16}" maxlength="16" class="form-control only-digits @error('nik') is-invalid @enderror" name="nik" value="{{ old('nik', $siswa->detail->nik ?? '') }}" placeholder="16 digit angka NIK" required>
                                    @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">No. KK (16 Digit Angka) <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="numeric" pattern="\d{16}" maxlength="16" class="form-control only-digits @error('no_kk') is-invalid @enderror" name="no_kk" value="{{ old('no_kk', $siswa->detail->no_kk ?? '') }}" placeholder="16 digit angka No. KK" required>
                                    @error('no_kk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">No. Registrasi Akta Lahir <span class="text-danger">*</span></label>
                                    <input type="text" maxlength="100" class="form-control @error('no_regis_akta_lahir') is-invalid @enderror" name="no_regis_akta_lahir" value="{{ old('no_regis_akta_lahir', $siswa->detail->no_regis_akta_lahir ?? '') }}" required>
                                    @error('no_regis_akta_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Baris 3: Fisik & Keluarga --}}
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Berat Badan (Kg) <span class="text-danger">*</span></label>
                                    <input type="number" min="10" max="300" step="any" class="form-control @error('bb') is-invalid @enderror" name="bb" value="{{ old('bb', $siswa->detail->bb ?? '') }}" required>
                                    @error('bb') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Tinggi Badan (Cm) <span class="text-danger">*</span></label>
                                    <input type="number" min="50" max="250" step="any" class="form-control @error('tb') is-invalid @enderror" name="tb" value="{{ old('tb', $siswa->detail->tb ?? '') }}" required>
                                    @error('tb') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Lingkar Kepala (Cm) <span class="text-danger">*</span></label>
                                    <input type="number" min="20" max="100" step="any" class="form-control @error('lingkar_kepala') is-invalid @enderror" name="lingkar_kepala" value="{{ old('lingkar_kepala', $siswa->detail->lingkar_kepala ?? '') }}" required>
                                    @error('lingkar_kepala') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Anak Ke- <span class="text-danger">*</span></label>
                                    <input type="number" min="1" max="30" class="form-control @error('anak_ke_berapa') is-invalid @enderror" name="anak_ke_berapa" value="{{ old('anak_ke_berapa', $siswa->detail->anak_ke_berapa ?? '') }}" required>
                                    @error('anak_ke_berapa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Jml Saudara <span class="text-danger">*</span></label>
                                    <input type="number" min="0" max="30" class="form-control @error('jml_saudara_kandung') is-invalid @enderror" name="jml_saudara_kandung" value="{{ old('jml_saudara_kandung', $siswa->detail->jml_saudara_kandung ?? '') }}" required>
                                    @error('jml_saudara_kandung') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Baris 4: Kontak --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bold">No. Handphone / WA Aktif <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="numeric" maxlength="15" class="form-control only-digits @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ old('no_hp', $siswa->detail->no_hp ?? '') }}" placeholder="Contoh: 081234567890" required>
                                    @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Alamat Email Aktif <span class="text-danger">*</span></label>
                                    <input type="email" maxlength="100" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $siswa->detail->email ?? '') }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12 mb-3 mt-2">
                                    <hr class="horizontal dark">
                                    <h6 class="text-sm font-weight-bold text-dark mt-2">Alamat Tempat Tinggal</h6>
                                </div>

                                {{-- Baris 5: Alamat --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Jalan / Gg / Blok <span class="text-danger">*</span></label>
                                    <input type="text" maxlength="255" class="form-control @error('alamat') is-invalid @enderror" name="alamat" value="{{ old('alamat', $siswa->detail->alamat ?? '') }}" required>
                                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">RT <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="numeric" maxlength="3" class="form-control only-digits @error('rt') is-invalid @enderror" name="rt" value="{{ old('rt', $siswa->detail->rt ?? '') }}" placeholder="Contoh: 001" required>
                                    @error('rt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">RW <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="numeric" maxlength="3" class="form-control only-digits @error('rw') is-invalid @enderror" name="rw" value="{{ old('rw', $siswa->detail->rw ?? '') }}" placeholder="Contoh: 002" required>
                                    @error('rw') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Dusun / Kampung <span class="text-danger">*</span></label>
                                    <input type="text" maxlength="100" class="form-control @error('dusun') is-invalid @enderror" name="dusun" value="{{ old('dusun', $siswa->detail->dusun ?? '') }}" required>
                                    @error('dusun') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Kelurahan / Desa <span class="text-danger">*</span></label>
                                    <input type="text" maxlength="100" class="form-control @error('kelurahan') is-invalid @enderror" name="kelurahan" value="{{ old('kelurahan', $siswa->detail->kelurahan ?? '') }}" required>
                                    @error('kelurahan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Kecamatan <span class="text-danger">*</span></label>
                                    <input type="text" maxlength="100" class="form-control @error('kecamatan') is-invalid @enderror" name="kecamatan" value="{{ old('kecamatan', $siswa->detail->kecamatan ?? '') }}" required>
                                    @error('kecamatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Kode Pos (Wilayah) <span class="text-danger">*</span></label>
                                    <select class="form-control @error('kode_pos') is-invalid @enderror" name="kode_pos" required>
                                        <option value="">-- Pilih Wilayah --</option>
                                        <option value="Dalam Kota" {{ old('kode_pos', $siswa->detail->kode_pos ?? '') == 'Dalam Kota' ? 'selected' : '' }}>Dalam Kota</option>
                                        <option value="Luar Kota" {{ old('kode_pos', $siswa->detail->kode_pos ?? '') == 'Luar Kota' ? 'selected' : '' }}>Luar Kota</option>
                                    </select>
                                    @error('kode_pos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12 mb-3 mt-2">
                                    <hr class="horizontal dark">
                                    <h6 class="text-sm font-weight-bold text-dark mt-2">Status Tinggal</h6>
                                </div>

                                {{-- Baris 6: Transportasi & Tinggal --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Jenis Tinggal <span class="text-danger">*</span></label>
                                    <select class="form-control @error('jenis_tinggal') is-invalid @enderror" name="jenis_tinggal" id="jenis_tinggal" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach(['Bersama Orang Tua', 'Wali', 'Pesantren', 'Asrama', 'Kost', 'Lainnya'] as $jt)
                                            <option value="{{ $jt }}" {{ old('jenis_tinggal', $siswa->detail->jenis_tinggal ?? '') == $jt ? 'selected' : '' }}>{{ $jt }}</option>
                                        @endforeach
                                    </select>
                                    @error('jenis_tinggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Transportasi <span class="text-danger">*</span></label>
                                    <select class="form-control @error('alat_transportasi') is-invalid @enderror" name="alat_transportasi" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach(['Sepeda motor', 'Angkutan umum/bus/pete-pete', 'Mobil/bus antar jemput', 'Mobil pribadi', 'Sepeda', 'Jalan kaki', 'Lainnya']  as $transport)
                                            <option value="{{ $transport }}" {{ old('alat_transportasi', $siswa->detail->alat_transportasi ?? '') == $transport ? 'selected' : '' }}>{{ $transport }}</option>
                                        @endforeach
                                    </select>
                                    @error('alat_transportasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bold">No. Handphone Orang Tua</label>
                                    <input type="text" inputmode="numeric" maxlength="15" class="form-control only-digits @error('telp_ortu') is-invalid @enderror" name="telp_ortu" value="{{ old('telp_ortu', $siswa->detail->telp_ortu ?? '') }}" placeholder="Contoh: 081234567890">
                                    @error('telp_ortu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                {{-- ALAMAT ORANG TUA (Selalu Tampil) --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Alamat Orang Tua</label>
                                    <input type="text" maxlength="255" class="form-control @error('alamat_ortu') is-invalid @enderror" name="alamat_ortu" value="{{ old('alamat_ortu', $siswa->detail->alamat_ortu ?? '') }}">
                                    @error('alamat_ortu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3" id="wrap_telp_wali">
                                    <label class="form-label text-xs font-weight-bold">No. Handphone Wali</label>
                                    <input type="text" inputmode="numeric" maxlength="15" class="form-control only-digits @error('telp_wali') is-invalid @enderror" name="telp_wali" value="{{ old('telp_wali', $siswa->detail->telp_wali ?? '') }}" placeholder="Isi jika tinggal bersama Wali / Pesantren / Asrama / Kost">
                                    @error('telp_wali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                {{-- ALAMAT WALI (Dinamis: Disembunyikan jika pilih 'Bersama Orang Tua') --}}
                                <div class="col-md-12 mb-3" id="wrap_alamat_wali">
                                    <label class="form-label text-xs font-weight-bold text-info">Alamat Wali / Pesantren / Kost</label>
                                    <input type="text" maxlength="255" class="form-control @error('alamat_wali') is-invalid @enderror" name="alamat_wali" value="{{ old('alamat_wali', $siswa->detail->alamat_wali ?? '') }}" placeholder="Isi jika tidak tinggal bersama orang tua">
                                    @error('alamat_wali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- CARD 2 & 3: DATA ORANG TUA (AYAH & IBU) --}}
                {{-- ======================================================== --}}
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-light border-bottom p-3">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-male me-2"></i> Data Ayah Kandung</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Nama Ayah <span class="text-danger">*</span></label>
                                <input type="text" maxlength="150" class="form-control @error('nama_ayah') is-invalid @enderror" name="nama_ayah" value="{{ old('nama_ayah', $siswa->detail->nama_ayah ?? '') }}" required>
                                @error('nama_ayah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">NIK Ayah (16 Digit Angka) <span class="text-danger">*</span></label>
                                <input type="text" inputmode="numeric" pattern="\d{16}" maxlength="16" class="form-control only-digits @error('nik_ayah') is-invalid @enderror" name="nik_ayah" value="{{ old('nik_ayah', $siswa->detail->nik_ayah ?? '') }}" placeholder="16 digit angka NIK Ayah" required>
                                @error('nik_ayah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Tahun Lahir Ayah (4 Digit Angka, Contoh: 1980) <span class="text-danger">*</span></label>
                                <input type="text" inputmode="numeric" pattern="\d{4}" maxlength="4" class="form-control only-digits @error('tahun_lahir_ayah') is-invalid @enderror" name="tahun_lahir_ayah" value="{{ old('tahun_lahir_ayah', $siswa->detail->tahun_lahir_ayah ?? '') }}" placeholder="Contoh: 1980" required>
                                @error('tahun_lahir_ayah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pendidikan Terakhir Ayah <span class="text-danger">*</span></label>
                                <select class="form-control @error('jenjang_pendidikan_ayah') is-invalid @enderror" name="jenjang_pendidikan_ayah" required>
                                    <option value="">-- Pilih Pendidikan --</option>
                                    @foreach($opsi_pendidikan as $pendidikan)
                                        <option value="{{ $pendidikan }}" {{ old('jenjang_pendidikan_ayah', $siswa->detail->jenjang_pendidikan_ayah ?? '') == $pendidikan ? 'selected' : '' }}>{{ $pendidikan }}</option>
                                    @endforeach
                                </select>
                                @error('jenjang_pendidikan_ayah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pekerjaan Ayah <span class="text-danger">*</span></label>
                                <select class="form-control @error('pekerjaan_ayah') is-invalid @enderror" name="pekerjaan_ayah" required>
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    @foreach($opsi_pekerjaan as $grup => $list_pekerjaan)
                                        <optgroup label="{{ $grup }}">
                                            @foreach($list_pekerjaan as $pkj)
                                                <option value="{{ $pkj }}" {{ old('pekerjaan_ayah', $siswa->detail->pekerjaan_ayah ?? '') == $pkj ? 'selected' : '' }}>{{ $pkj }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('pekerjaan_ayah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Penghasilan Ayah Per Bulan <span class="text-danger">*</span></label>
                                <select class="form-control @error('penghasilan_ayah') is-invalid @enderror" name="penghasilan_ayah" required>
                                    <option value="">-- Pilih Penghasilan --</option>
                                    @foreach($opsi_penghasilan as $penghasilan)
                                        <option value="{{ $penghasilan }}" {{ old('penghasilan_ayah', $siswa->detail->penghasilan_ayah ?? '') == $penghasilan ? 'selected' : '' }}>{{ $penghasilan }}</option>
                                    @endforeach
                                </select>
                                @error('penghasilan_ayah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-light border-bottom p-3">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-female me-2"></i> Data Ibu Kandung</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Nama Ibu <span class="text-danger">*</span></label>
                                <input type="text" maxlength="150" class="form-control @error('nama_ibu') is-invalid @enderror" name="nama_ibu" value="{{ old('nama_ibu', $siswa->detail->nama_ibu ?? '') }}" required>
                                @error('nama_ibu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">NIK Ibu (16 Digit Angka) <span class="text-danger">*</span></label>
                                <input type="text" inputmode="numeric" pattern="\d{16}" maxlength="16" class="form-control only-digits @error('nik_ibu') is-invalid @enderror" name="nik_ibu" value="{{ old('nik_ibu', $siswa->detail->nik_ibu ?? '') }}" placeholder="16 digit angka NIK Ibu" required>
                                @error('nik_ibu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Tahun Lahir Ibu (4 Digit Angka, Contoh: 1982) <span class="text-danger">*</span></label>
                                <input type="text" inputmode="numeric" pattern="\d{4}" maxlength="4" class="form-control only-digits @error('tahun_lahir_ibu') is-invalid @enderror" name="tahun_lahir_ibu" value="{{ old('tahun_lahir_ibu', $siswa->detail->tahun_lahir_ibu ?? '') }}" placeholder="Contoh: 1982" required>
                                @error('tahun_lahir_ibu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pendidikan Terakhir Ibu <span class="text-danger">*</span></label>
                                <select class="form-control @error('jenjang_pendidikan_ibu') is-invalid @enderror" name="jenjang_pendidikan_ibu" required>
                                    <option value="">-- Pilih Pendidikan --</option>
                                    @foreach($opsi_pendidikan as $pendidikan)
                                        <option value="{{ $pendidikan }}" {{ old('jenjang_pendidikan_ibu', $siswa->detail->jenjang_pendidikan_ibu ?? '') == $pendidikan ? 'selected' : '' }}>{{ $pendidikan }}</option>
                                    @endforeach
                                </select>
                                @error('jenjang_pendidikan_ibu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pekerjaan Ibu <span class="text-danger">*</span></label>
                                <select class="form-control @error('pekerjaan_ibu') is-invalid @enderror" name="pekerjaan_ibu" required>
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    @foreach($opsi_pekerjaan as $grup => $list_pekerjaan)
                                        <optgroup label="{{ $grup }}">
                                            @foreach($list_pekerjaan as $pkj)
                                                <option value="{{ $pkj }}" {{ old('pekerjaan_ibu', $siswa->detail->pekerjaan_ibu ?? '') == $pkj ? 'selected' : '' }}>{{ $pkj }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('pekerjaan_ibu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Penghasilan Ibu Per Bulan <span class="text-danger">*</span></label>
                                <select class="form-control @error('penghasilan_ibu') is-invalid @enderror" name="penghasilan_ibu" required>
                                    <option value="">-- Pilih Penghasilan --</option>
                                    @foreach($opsi_penghasilan as $penghasilan)
                                        <option value="{{ $penghasilan }}" {{ old('penghasilan_ibu', $siswa->detail->penghasilan_ibu ?? '') == $penghasilan ? 'selected' : '' }}>{{ $penghasilan }}</option>
                                    @endforeach
                                </select>
                                @error('penghasilan_ibu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- CARD 4: DATA WALI (OPSIONAL) --}}
                {{-- ======================================================== --}}
                <div class="col-lg-12 mb-4" id="wrap_data_wali">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-bottom p-3">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-user-shield me-2"></i> Data Wali Siswa (Opsional)</h6>
                            <p class="text-xs mb-0 text-secondary">Hanya diisi jika Anda tinggal dan dibiayai oleh Wali (Bukan orang tua kandung).</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Nama Wali</label>
                                <input type="text" maxlength="150" class="form-control @error('nama_wali') is-invalid @enderror" name="nama_wali" value="{{ old('nama_wali', $siswa->detail->nama_wali ?? '') }}">
                                @error('nama_wali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">NIK Wali (16 Digit Angka)</label>
                                <input type="text" inputmode="numeric" pattern="\d{16}" maxlength="16" class="form-control only-digits @error('nik_wali') is-invalid @enderror" name="nik_wali" value="{{ old('nik_wali', $siswa->detail->nik_wali ?? '') }}" placeholder="16 digit angka NIK Wali">
                                @error('nik_wali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Tahun Lahir Wali (4 Digit Angka)</label>
                                <input type="text" inputmode="numeric" pattern="\d{4}" maxlength="4" class="form-control only-digits @error('tahun_lahir_wali') is-invalid @enderror" name="tahun_lahir_wali" value="{{ old('tahun_lahir_wali', $siswa->detail->tahun_lahir_wali ?? '') }}" placeholder="Contoh: 1975">
                                @error('tahun_lahir_wali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pendidikan Terakhir Wali</label>
                                <select class="form-control @error('jenjang_pendidikan_wali') is-invalid @enderror" name="jenjang_pendidikan_wali">
                                    <option value="">-- Pilih Pendidikan --</option>
                                    @foreach($opsi_pendidikan as $pendidikan)
                                        <option value="{{ $pendidikan }}" {{ old('jenjang_pendidikan_wali', $siswa->detail->jenjang_pendidikan_wali ?? '') == $pendidikan ? 'selected' : '' }}>{{ $pendidikan }}</option>
                                    @endforeach
                                </select>
                                @error('jenjang_pendidikan_wali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pekerjaan Wali</label>
                                <select class="form-control @error('pekerjaan_wali') is-invalid @enderror" name="pekerjaan_wali">
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    @foreach($opsi_pekerjaan as $grup => $list_pekerjaan)
                                        <optgroup label="{{ $grup }}">
                                            @foreach($list_pekerjaan as $pkj)
                                                <option value="{{ $pkj }}" {{ old('pekerjaan_wali', $siswa->detail->pekerjaan_wali ?? '') == $pkj ? 'selected' : '' }}>{{ $pkj }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('pekerjaan_wali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Penghasilan Wali Per Bulan</label>
                                <select class="form-control @error('penghasilan_wali') is-invalid @enderror" name="penghasilan_wali">
                                    <option value="">-- Pilih Penghasilan --</option>
                                    @foreach($opsi_penghasilan as $penghasilan)
                                        <option value="{{ $penghasilan }}" {{ old('penghasilan_wali', $siswa->detail->penghasilan_wali ?? '') == $penghasilan ? 'selected' : '' }}>{{ $penghasilan }}</option>
                                    @endforeach
                                </select>
                                @error('penghasilan_wali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ACTION BUTTONS --}}
            <div class="d-flex justify-content-end mb-5 mt-2 bg-white p-3 rounded shadow-sm border">
                <a href="{{ route('sis.biodata') }}" class="btn btn-secondary mb-0 me-2">Batal</a>
                <button type="submit" class="btn bg-gradient-primary mb-0">Kirim Pengajuan Pembaruan</button>
            </div>

        </form>

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jenisTinggal = document.getElementById('jenis_tinggal');
            const wrapTelpWali = document.getElementById('wrap_telp_wali');
            const wrapAlamatWali = document.getElementById('wrap_alamat_wali');

            function toggleWali() {
                if (jenisTinggal.value === 'Bersama Orang Tua' || jenisTinggal.value === '') {
                    wrapAlamatWali.style.display = 'none';
                    wrapTelpWali.style.display = 'none';
                } else {
                    wrapAlamatWali.style.display = 'block';
                    wrapTelpWali.style.display = 'block';
                }
            }

            toggleWali();
            jenisTinggal.addEventListener('change', toggleWali);

            // Filter otomatis hanya angka & batasi sesuai maxlength (untuk NIK, Tahun Lahir, RT, RW, No HP)
            document.querySelectorAll('.only-digits').forEach(function(input) {
                input.addEventListener('input', function() {
                    const maxLen = parseInt(this.getAttribute('maxlength') || '0', 10);
                    let cleaned = this.value.replace(/\D/g, '');
                    if (maxLen > 0 && cleaned.length > maxLen) {
                        cleaned = cleaned.slice(0, maxLen);
                    }
                    this.value = cleaned;
                });
            });

            // Jika ada error validasi dari server, gulir otomatis ke alert error / kolom bermasalah pertama
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
        });
    </script>
    <x-app.footer />
</main>
@endsection