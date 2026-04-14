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
            Data di bawah ini adalah data Anda saat ini. Kolom dengan tanda bintang merah (<span class="text-danger">*</span>) <strong>wajib diisi</strong>. Silakan isi atau perbaiki data yang salah. Jika sudah benar, biarkan saja isinya.
        </div>

        {{-- BLOCK PENAMPIL ERROR DARI CONTROLLER --}}
        @if ($errors->any())
            <div class="alert bg-gradient-danger alert-dismissible text-white fade show mb-4" role="alert">
                <span class="text-sm"><i class="fas fa-exclamation-triangle me-2"></i><strong>Gagal Menyimpan:</strong> Terdapat data wajib yang belum Anda isi.</span>
                <ul class="mb-0 text-sm ps-4 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
            </div>
        @endif

        <form action="{{ route('sis.biodata.ajukan') }}" method="POST">
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
                                    <input type="text" class="form-control" name="tempat_lahir" value="{{ old('tempat_lahir', $siswa->detail->tempat_lahir ?? '') }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal_lahir" value="{{ old('tanggal_lahir', $siswa->detail->tanggal_lahir ?? '') }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Agama <span class="text-danger">*</span></label>
                                    <select class="form-control" name="agama" required>
                                        <option value="">-- Pilih Agama --</option>
                                        @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm)
                                            <option value="{{ $agm }}" {{ old('agama', $siswa->detail->agama ?? '') == $agm ? 'selected' : '' }}>{{ $agm }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Sekolah Asal <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="sekolah_asal" value="{{ old('sekolah_asal', $siswa->detail->sekolah_asal ?? '') }}" placeholder="Contoh: SMPN 1 Jakarta" required>
                                </div>

                                {{-- Baris 2: Kelengkapan Dokumen --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">NIK (Nomor Induk Kependudukan) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nik" value="{{ old('nik', $siswa->detail->nik ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">No. KK (Kartu Keluarga) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="no_kk" value="{{ old('no_kk', $siswa->detail->no_kk ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">No. Registrasi Akta Lahir <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="no_regis_akta_lahir" value="{{ old('no_regis_akta_lahir', $siswa->detail->no_regis_akta_lahir ?? '') }}" required>
                                </div>

                                {{-- Baris 3: Fisik & Keluarga --}}
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Berat Badan (Kg) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="bb" value="{{ old('bb', $siswa->detail->bb ?? '') }}" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Tinggi Badan (Cm) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="tb" value="{{ old('tb', $siswa->detail->tb ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Lingkar Kepala (Cm) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="lingkar_kepala" value="{{ old('lingkar_kepala', $siswa->detail->lingkar_kepala ?? '') }}" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Anak Ke- <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="anak_ke_berapa" value="{{ old('anak_ke_berapa', $siswa->detail->anak_ke_berapa ?? '') }}" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Jml Saudara <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="jml_saudara_kandung" value="{{ old('jml_saudara_kandung', $siswa->detail->jml_saudara_kandung ?? '') }}" required>
                                </div>

                                {{-- Baris 4: Kontak --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bold">No. Handphone / WA Aktif <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="no_hp" value="{{ old('no_hp', $siswa->detail->no_hp ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Alamat Email Aktif <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email', $siswa->detail->email ?? '') }}" required>
                                </div>

                                <div class="col-md-12 mb-3 mt-2">
                                    <hr class="horizontal dark">
                                    <h6 class="text-sm font-weight-bold text-dark mt-2">Alamat Tempat Tinggal</h6>
                                </div>

                                {{-- Baris 5: Alamat --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Jalan / Gg / Blok <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="alamat" value="{{ old('alamat', $siswa->detail->alamat ?? '') }}" required>
                                </div>
                                
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">RT <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="rt" value="{{ old('rt', $siswa->detail->rt ?? '') }}" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">RW <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="rw" value="{{ old('rw', $siswa->detail->rw ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Dusun / Kampung <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="dusun" value="{{ old('dusun', $siswa->detail->dusun ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Kelurahan / Desa <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="kelurahan" value="{{ old('kelurahan', $siswa->detail->kelurahan ?? '') }}" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Kecamatan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="kecamatan" value="{{ old('kecamatan', $siswa->detail->kecamatan ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Kode Pos <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="kode_pos" value="{{ old('kode_pos', $siswa->detail->kode_pos ?? '') }}" required>
                                </div>

                                {{-- Baris 6: Transportasi & Tinggal --}}
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Jenis Tinggal <span class="text-danger">*</span></label>
                                    <select class="form-control" name="jenis_tinggal" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach(['Bersama Orang Tua', 'Wali', 'Kos', 'Asrama', 'Panti Asuhan', 'Lainnya'] as $jt)
                                            <option value="{{ $jt }}" {{ old('jenis_tinggal', $siswa->detail->jenis_tinggal ?? '') == $jt ? 'selected' : '' }}>{{ $jt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Transportasi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="alat_transportasi" value="{{ old('alat_transportasi', $siswa->detail->alat_transportasi ?? '') }}" placeholder="Contoh: Sepeda Motor" required>
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
                                <input type="text" class="form-control" name="nama_ayah" value="{{ old('nama_ayah', $siswa->detail->nama_ayah ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">NIK Ayah <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nik_ayah" value="{{ old('nik_ayah', $siswa->detail->nik_ayah ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Tahun Lahir Ayah (Contoh: 1980) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="tahun_lahir_ayah" value="{{ old('tahun_lahir_ayah', $siswa->detail->tahun_lahir_ayah ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pendidikan Terakhir Ayah <span class="text-danger">*</span></label>
                                <select class="form-control" name="jenjang_pendidikan_ayah" required>
                                    <option value="">-- Pilih Pendidikan --</option>
                                    @foreach($opsi_pendidikan as $pendidikan)
                                        <option value="{{ $pendidikan }}" {{ old('jenjang_pendidikan_ayah', $siswa->detail->jenjang_pendidikan_ayah ?? '') == $pendidikan ? 'selected' : '' }}>{{ $pendidikan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pekerjaan Ayah <span class="text-danger">*</span></label>
                                <select class="form-control" name="pekerjaan_ayah" required>
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    @foreach($opsi_pekerjaan as $grup => $list_pekerjaan)
                                        <optgroup label="{{ $grup }}">
                                            @foreach($list_pekerjaan as $pkj)
                                                <option value="{{ $pkj }}" {{ old('pekerjaan_ayah', $siswa->detail->pekerjaan_ayah ?? '') == $pkj ? 'selected' : '' }}>{{ $pkj }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Penghasilan Ayah Per Bulan <span class="text-danger">*</span></label>
                                <select class="form-control" name="penghasilan_ayah" required>
                                    <option value="">-- Pilih Penghasilan --</option>
                                    @foreach($opsi_penghasilan as $penghasilan)
                                        <option value="{{ $penghasilan }}" {{ old('penghasilan_ayah', $siswa->detail->penghasilan_ayah ?? '') == $penghasilan ? 'selected' : '' }}>{{ $penghasilan }}</option>
                                    @endforeach
                                </select>
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
                                <input type="text" class="form-control" name="nama_ibu" value="{{ old('nama_ibu', $siswa->detail->nama_ibu ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">NIK Ibu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nik_ibu" value="{{ old('nik_ibu', $siswa->detail->nik_ibu ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Tahun Lahir Ibu (Contoh: 1982) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="tahun_lahir_ibu" value="{{ old('tahun_lahir_ibu', $siswa->detail->tahun_lahir_ibu ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pendidikan Terakhir Ibu <span class="text-danger">*</span></label>
                                <select class="form-control" name="jenjang_pendidikan_ibu" required>
                                    <option value="">-- Pilih Pendidikan --</option>
                                    @foreach($opsi_pendidikan as $pendidikan)
                                        <option value="{{ $pendidikan }}" {{ old('jenjang_pendidikan_ibu', $siswa->detail->jenjang_pendidikan_ibu ?? '') == $pendidikan ? 'selected' : '' }}>{{ $pendidikan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pekerjaan Ibu <span class="text-danger">*</span></label>
                                <select class="form-control" name="pekerjaan_ibu" required>
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    @foreach($opsi_pekerjaan as $grup => $list_pekerjaan)
                                        <optgroup label="{{ $grup }}">
                                            @foreach($list_pekerjaan as $pkj)
                                                <option value="{{ $pkj }}" {{ old('pekerjaan_ibu', $siswa->detail->pekerjaan_ibu ?? '') == $pkj ? 'selected' : '' }}>{{ $pkj }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Penghasilan Ibu Per Bulan <span class="text-danger">*</span></label>
                                <select class="form-control" name="penghasilan_ibu" required>
                                    <option value="">-- Pilih Penghasilan --</option>
                                    @foreach($opsi_penghasilan as $penghasilan)
                                        <option value="{{ $penghasilan }}" {{ old('penghasilan_ibu', $siswa->detail->penghasilan_ibu ?? '') == $penghasilan ? 'selected' : '' }}>{{ $penghasilan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- CARD 4: DATA WALI (OPSIONAL) --}}
                {{-- ======================================================== --}}
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-bottom p-3">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-user-shield me-2"></i> Data Wali Siswa (Opsional)</h6>
                            <p class="text-xs mb-0 text-secondary">Hanya diisi jika Anda tinggal dan dibiayai oleh Wali (Bukan orang tua kandung).</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Nama Wali</label>
                                    <input type="text" class="form-control" name="nama_wali" value="{{ old('nama_wali', $siswa->detail->nama_wali ?? '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">NIK Wali</label>
                                    <input type="text" class="form-control" name="nik_wali" value="{{ old('nik_wali', $siswa->detail->nik_wali ?? '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Tahun Lahir Wali</label>
                                    <input type="number" class="form-control" name="tahun_lahir_wali" value="{{ old('tahun_lahir_wali', $siswa->detail->tahun_lahir_wali ?? '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Pendidikan Terakhir Wali</label>
                                    <select class="form-control" name="jenjang_pendidikan_wali">
                                        <option value="">-- Pilih Pendidikan --</option>
                                        @foreach($opsi_pendidikan as $pendidikan)
                                            <option value="{{ $pendidikan }}" {{ old('jenjang_pendidikan_wali', $siswa->detail->jenjang_pendidikan_wali ?? '') == $pendidikan ? 'selected' : '' }}>{{ $pendidikan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Pekerjaan Wali</label>
                                    <select class="form-control" name="pekerjaan_wali">
                                        <option value="">-- Pilih Pekerjaan --</option>
                                        @foreach($opsi_pekerjaan as $grup => $list_pekerjaan)
                                            <optgroup label="{{ $grup }}">
                                                @foreach($list_pekerjaan as $pkj)
                                                    <option value="{{ $pkj }}" {{ old('pekerjaan_wali', $siswa->detail->pekerjaan_wali ?? '') == $pkj ? 'selected' : '' }}>{{ $pkj }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Penghasilan Wali Per Bulan</label>
                                    <select class="form-control" name="penghasilan_wali">
                                        <option value="">-- Pilih Penghasilan --</option>
                                        @foreach($opsi_penghasilan as $penghasilan)
                                            <option value="{{ $penghasilan }}" {{ old('penghasilan_wali', $siswa->detail->penghasilan_wali ?? '') == $penghasilan ? 'selected' : '' }}>{{ $penghasilan }}</option>
                                        @endforeach
                                    </select>
                                </div>
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
    <x-app.footer />
</main>
@endsection