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
            Data di bawah ini adalah data Anda saat ini. Silakan isi kolom yang masih kosong atau perbaiki data yang salah. Jika sudah benar, biarkan saja isinya.
        </div>

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
                                    <label class="form-label text-xs font-weight-bold">Tempat Lahir</label>
                                    <input type="text" class="form-control" name="tempat_lahir" value="{{ $siswa->detail->tempat_lahir ?? '' }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Tanggal Lahir</label>
                                    <input type="date" class="form-control" name="tanggal_lahir" value="{{ $siswa->detail->tanggal_lahir ?? '' }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Agama</label>
                                    <select class="form-control" name="agama">
                                        <option value="" {{ empty($siswa->detail->agama) ? 'selected' : '' }}>-- Pilih Agama --</option>
                                        @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm)
                                            <option value="{{ $agm }}" {{ ($siswa->detail->agama ?? '') == $agm ? 'selected' : '' }}>{{ $agm }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Sekolah Asal</label>
                                    <input type="text" class="form-control" name="sekolah_asal" value="{{ $siswa->detail->sekolah_asal ?? '' }}" placeholder="Contoh: SMPN 1 Jakarta">
                                </div>

                                {{-- Baris 2: Kelengkapan Dokumen (NIK, KK, Akta) --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">NIK (Nomor Induk Kependudukan)</label>
                                    <input type="text" class="form-control" name="nik" value="{{ $siswa->detail->nik ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">No. KK (Kartu Keluarga)</label>
                                    <input type="text" class="form-control" name="no_kk" value="{{ $siswa->detail->no_kk ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">No. Registrasi Akta Lahir</label>
                                    <input type="text" class="form-control" name="no_regis_akta_lahir" value="{{ $siswa->detail->no_regis_akta_lahir ?? '' }}">
                                </div>

                                {{-- Baris 3: Fisik & Keluarga --}}
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Berat Badan (Kg)</label>
                                    <input type="number" class="form-control" name="bb" value="{{ $siswa->detail->bb ?? '' }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Tinggi Badan (Cm)</label>
                                    <input type="number" class="form-control" name="tb" value="{{ $siswa->detail->tb ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Lingkar Kepala (Cm)</label>
                                    <input type="number" class="form-control" name="lingkar_kepala" value="{{ $siswa->detail->lingkar_kepala ?? '' }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Anak Ke-</label>
                                    <input type="number" class="form-control" name="anak_ke_berapa" value="{{ $siswa->detail->anak_ke_berapa ?? '' }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Jml Saudara</label>
                                    <input type="number" class="form-control" name="jml_saudara_kandung" value="{{ $siswa->detail->jml_saudara_kandung ?? '' }}">
                                </div>

                                {{-- Baris 4: Kontak --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bold">No. Handphone / WA Aktif</label>
                                    <input type="text" class="form-control" name="no_hp" value="{{ $siswa->detail->no_hp ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Alamat Email Aktif</label>
                                    <input type="email" class="form-control" name="email" value="{{ $siswa->detail->email ?? '' }}">
                                </div>

                                <div class="col-md-12 mb-3 mt-2">
                                    <hr class="horizontal dark">
                                    <h6 class="text-sm font-weight-bold text-dark mt-2">Alamat Tempat Tinggal</h6>
                                </div>

                                {{-- Baris 5: Alamat --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Jalan / Gg / Blok</label>
                                    <input type="text" class="form-control" name="alamat" value="{{ $siswa->detail->alamat ?? '' }}">
                                </div>
                                
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">RT</label>
                                    <input type="text" class="form-control" name="rt" value="{{ $siswa->detail->rt ?? '' }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">RW</label>
                                    <input type="text" class="form-control" name="rw" value="{{ $siswa->detail->rw ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Dusun / Kampung</label>
                                    <input type="text" class="form-control" name="dusun" value="{{ $siswa->detail->dusun ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Kelurahan / Desa</label>
                                    <input type="text" class="form-control" name="kelurahan" value="{{ $siswa->detail->kelurahan ?? '' }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Kecamatan</label>
                                    <input type="text" class="form-control" name="kecamatan" value="{{ $siswa->detail->kecamatan ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Kode Pos</label>
                                    <input type="text" class="form-control" name="kode_pos" value="{{ $siswa->detail->kode_pos ?? '' }}">
                                </div>

                                {{-- Baris 6: Transportasi & Tinggal --}}
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Jenis Tinggal</label>
                                    <select class="form-control" name="jenis_tinggal">
                                        <option value="">-- Pilih --</option>
                                        @foreach(['Bersama Orang Tua', 'Wali', 'Kos', 'Asrama', 'Panti Asuhan', 'Lainnya'] as $jt)
                                            <option value="{{ $jt }}" {{ ($siswa->detail->jenis_tinggal ?? '') == $jt ? 'selected' : '' }}>{{ $jt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Transportasi</label>
                                    <input type="text" class="form-control" name="alat_transportasi" value="{{ $siswa->detail->alat_transportasi ?? '' }}" placeholder="Contoh: Sepeda Motor">
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
                                <label class="form-label text-xs font-weight-bold">Nama Ayah</label>
                                <input type="text" class="form-control" name="nama_ayah" value="{{ $siswa->detail->nama_ayah ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">NIK Ayah</label>
                                <input type="text" class="form-control" name="nik_ayah" value="{{ $siswa->detail->nik_ayah ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Tahun Lahir Ayah (Contoh: 1980)</label>
                                <input type="number" class="form-control" name="tahun_lahir_ayah" value="{{ $siswa->detail->tahun_lahir_ayah ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pendidikan Terakhir Ayah</label>
                                <select class="form-control" name="jenjang_pendidikan_ayah">
                                    <option value="">-- Pilih Pendidikan --</option>
                                    @foreach($opsi_pendidikan as $pendidikan)
                                        <option value="{{ $pendidikan }}" {{ ($siswa->detail->jenjang_pendidikan_ayah ?? '') == $pendidikan ? 'selected' : '' }}>{{ $pendidikan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pekerjaan Ayah</label>
                                <select class="form-control" name="pekerjaan_ayah">
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    @foreach($opsi_pekerjaan as $grup => $list_pekerjaan)
                                        <optgroup label="{{ $grup }}">
                                            @foreach($list_pekerjaan as $pkj)
                                                <option value="{{ $pkj }}" {{ ($siswa->detail->pekerjaan_ayah ?? '') == $pkj ? 'selected' : '' }}>{{ $pkj }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Penghasilan Ayah Per Bulan</label>
                                <select class="form-control" name="penghasilan_ayah">
                                    <option value="">-- Pilih Penghasilan --</option>
                                    @foreach($opsi_penghasilan as $penghasilan)
                                        <option value="{{ $penghasilan }}" {{ ($siswa->detail->penghasilan_ayah ?? '') == $penghasilan ? 'selected' : '' }}>{{ $penghasilan }}</option>
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
                                <label class="form-label text-xs font-weight-bold">Nama Ibu</label>
                                <input type="text" class="form-control" name="nama_ibu" value="{{ $siswa->detail->nama_ibu ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">NIK Ibu</label>
                                <input type="text" class="form-control" name="nik_ibu" value="{{ $siswa->detail->nik_ibu ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Tahun Lahir Ibu (Contoh: 1982)</label>
                                <input type="number" class="form-control" name="tahun_lahir_ibu" value="{{ $siswa->detail->tahun_lahir_ibu ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pendidikan Terakhir Ibu</label>
                                <select class="form-control" name="jenjang_pendidikan_ibu">
                                    <option value="">-- Pilih Pendidikan --</option>
                                    @foreach($opsi_pendidikan as $pendidikan)
                                        <option value="{{ $pendidikan }}" {{ ($siswa->detail->jenjang_pendidikan_ibu ?? '') == $pendidikan ? 'selected' : '' }}>{{ $pendidikan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Pekerjaan Ibu</label>
                                <select class="form-control" name="pekerjaan_ibu">
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    @foreach($opsi_pekerjaan as $grup => $list_pekerjaan)
                                        <optgroup label="{{ $grup }}">
                                            @foreach($list_pekerjaan as $pkj)
                                                <option value="{{ $pkj }}" {{ ($siswa->detail->pekerjaan_ibu ?? '') == $pkj ? 'selected' : '' }}>{{ $pkj }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold">Penghasilan Ibu Per Bulan</label>
                                <select class="form-control" name="penghasilan_ibu">
                                    <option value="">-- Pilih Penghasilan --</option>
                                    @foreach($opsi_penghasilan as $penghasilan)
                                        <option value="{{ $penghasilan }}" {{ ($siswa->detail->penghasilan_ibu ?? '') == $penghasilan ? 'selected' : '' }}>{{ $penghasilan }}</option>
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
                                    <input type="text" class="form-control" name="nama_wali" value="{{ $siswa->detail->nama_wali ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">NIK Wali</label>
                                    <input type="text" class="form-control" name="nik_wali" value="{{ $siswa->detail->nik_wali ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Tahun Lahir Wali</label>
                                    <input type="number" class="form-control" name="tahun_lahir_wali" value="{{ $siswa->detail->tahun_lahir_wali ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bold">Pendidikan Terakhir Wali</label>
                                    <select class="form-control" name="jenjang_pendidikan_wali">
                                        <option value="">-- Pilih Pendidikan --</option>
                                        @foreach($opsi_pendidikan as $pendidikan)
                                            <option value="{{ $pendidikan }}" {{ ($siswa->detail->jenjang_pendidikan_wali ?? '') == $pendidikan ? 'selected' : '' }}>{{ $pendidikan }}</option>
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
                                                    <option value="{{ $pkj }}" {{ ($siswa->detail->pekerjaan_wali ?? '') == $pkj ? 'selected' : '' }}>{{ $pkj }}</option>
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
                                            <option value="{{ $penghasilan }}" {{ ($siswa->detail->penghasilan_wali ?? '') == $penghasilan ? 'selected' : '' }}>{{ $penghasilan }}</option>
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