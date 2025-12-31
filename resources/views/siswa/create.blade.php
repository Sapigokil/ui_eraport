{{-- File: resources/views/siswa/create.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Tambah Data Siswa Baru')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-plus me-2"></i> Tambah Data Siswa Baru</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">

                            {{-- Form untuk CREATE (mengarah ke store) --}}
                            <form action="{{ route('master.siswa.store') }}" method="POST">
                                @csrf
                                
                                @php
                                    // Helper function untuk mengambil nilai lama atau kosong
                                    $getValue = fn($field) => old($field, '');
                                @endphp


                                {{-- Tombol Aksi Atas --}}
                                <div class="mb-4">
                                    <button type="submit" class="btn bg-gradient-primary me-2">
                                        <i class="fas fa-save"></i> Simpan Data Siswa Baru
                                    </button>
                                    <a href="{{ route('master.siswa.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Batal / Kembali
                                    </a>
                                </div>
                                
                                {{-- === NOTIFIKASI ERROR VALIDASI === --}}
                                @if ($errors->any())
                                    <div class="alert bg-gradient-danger text-white mt-4" role="alert">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                

                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder my-4 text-primary"><i class="fas fa-user-tag me-1"></i> I. Data Pokok & Kelas</h6>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_siswa" class="form-label">Nama Lengkap</label>
                                        <input type="text" name="nama_siswa" class="form-control rounded-pill py-2 @error('nama_siswa') is-invalid @enderror" 
                                               value="{{ $getValue('nama_siswa') }}" required>
                                        @error('nama_siswa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nisn" class="form-label">NISN</label>
                                        <input type="text" name="nisn" class="form-control rounded-pill py-2 @error('nisn') is-invalid @enderror" 
                                               value="{{ $getValue('nisn') }}" maxlength="10">
                                        @error('nisn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nipd" class="form-label">NIPD</label>
                                        <input type="text" name="nipd" class="form-control rounded-pill py-2 @error('nipd') is-invalid @enderror" 
                                               value="{{ $getValue('nipd') }}" maxlength="18">
                                        @error('nipd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="form-select rounded-pill py-2 @error('jenis_kelamin') is-invalid @enderror" required>
                                            @php $jk = $getValue('jenis_kelamin'); @endphp
                                            <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                            <option value="Laki-laki" {{ $jk == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="Perempuan" {{ $jk == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                        @error('jenis_kelamin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="id_kelas" class="form-label">Rombel/Kelas Saat Ini</label>
                                        <select name="id_kelas" class="form-select rounded-pill py-2 @error('id_kelas') is-invalid @enderror" required>
                                            <option value="" disabled selected>Pilih Kelas</option>
                                            @foreach ($kelasList as $kelas)
                                                <option value="{{ $kelas->id_kelas }}" 
                                                    {{ $getValue('id_kelas') == $kelas->id_kelas ? 'selected' : '' }}>
                                                    {{ $kelas->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_kelas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="id_ekskul" class="form-label">Ekskul</label>
                                        <select name="id_ekskul" class="form-select rounded-pill py-2 @error('id_ekskul') is-invalid @enderror">
                                            <option value="" selected>(Tidak Ada/Pilih Ekskul)</option>
                                            @if (!empty($ekskulList)) 
                                                @foreach ($ekskulList as $ekskulOption)
                                                    <option value="{{ $ekskulOption->id_ekskul }}" 
                                                        {{ $getValue('id_ekskul') == $ekskulOption->id_ekskul ? 'selected' : '' }}>
                                                        {{ $ekskulOption->nama_ekskul }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('id_ekskul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="rombel" class="form-label">Rombel Dapodik</label>
                                        <input type="text" name="rombel" class="form-control rounded-pill py-2" value="{{ $getValue('rombel') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="sekolah_asal" class="form-label">Sekolah Asal</label>
                                        <input type="text" name="sekolah_asal" class="form-control rounded-pill py-2" value="{{ $getValue('sekolah_asal') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="no_seri_ijazah" class="form-label">No. Seri Ijazah</label>
                                        <input type="text" name="no_seri_ijazah" class="form-control rounded-pill py-2" value="{{ $getValue('no_seri_ijazah') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="no_peserta_ujian_nasional" class="form-label">No. Peserta UN</label>
                                        <input type="text" name="no_peserta_ujian_nasional" class="form-control rounded-pill py-2" value="{{ $getValue('no_peserta_ujian_nasional') }}">
                                    </div>
                                </div>

                                <hr class="my-4">

                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-info"><i class="fas fa-birthday-cake me-1"></i> II. Detail Pribadi & Fisik</h6>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" class="form-control rounded-pill py-2" value="{{ $getValue('tempat_lahir') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" class="form-control rounded-pill py-2 @error('tanggal_lahir') is-invalid @enderror" 
                                               value="{{ $getValue('tanggal_lahir') }}">
                                        @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="agama" class="form-label">Agama</label>
                                        <input type="text" name="agama" class="form-control rounded-pill py-2" value="{{ $getValue('agama') }}">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="no_regis_akta_lahir" class="form-label">No. Reg. Akta Lahir</label>
                                        <input type="text" name="no_regis_akta_lahir" class="form-control rounded-pill py-2" value="{{ $getValue('no_regis_akta_lahir') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="kebutuhan_khusus" class="form-label">Kebutuhan Khusus</label>
                                        <input type="text" name="kebutuhan_khusus" class="form-control rounded-pill py-2" value="{{ $getValue('kebutuhan_khusus') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="anak_ke_berapa" class="form-label">Anak Ke-</label>
                                        <input type="number" name="anak_ke_berapa" class="form-control rounded-pill py-2" value="{{ $getValue('anak_ke_berapa') }}">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="bb" class="form-label">Berat Badan (kg)</label>
                                        <input type="text" name="bb" class="form-control rounded-pill py-2" value="{{ $getValue('bb') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="tb" class="form-label">Tinggi Badan (cm)</label>
                                        <input type="text" name="tb" class="form-control rounded-pill py-2" value="{{ $getValue('tb') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="lingkar_kepala" class="form-label">Lingkar Kepala (cm)</label>
                                        <input type="text" name="lingkar_kepala" class="form-control rounded-pill py-2" value="{{ $getValue('lingkar_kepala') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="jml_saudara_kandung" class="form-label">Jml Saudara Kandung</label>
                                        <input type="number" name="jml_saudara_kandung" class="form-control rounded-pill py-2" value="{{ $getValue('jml_saudara_kandung') }}">
                                    </div>
                                </div>

                                <hr class="my-4">

                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-danger"><i class="fas fa-map-marker-alt me-1"></i> III. Alamat & Kontak</h6>
                                <hr>
                                
                                {{-- Baris 1: Alamat --}}
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="alamat" class="form-label">Alamat Lengkap (Jalan)</label>
                                        <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror">{{ $getValue('alamat') }}</textarea>
                                        @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                
                                {{-- Baris 2: RT, RW, Kodepos --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="rt" class="form-label">RT</label>
                                        <input type="text" name="rt" class="form-control rounded-pill py-2" value="{{ $getValue('rt') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="rw" class="form-label">RW</label>
                                        <input type="text" name="rw" class="form-control rounded-pill py-2" value="{{ $getValue('rw') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="kode_pos" class="form-label">Kode Pos</label>
                                        <input type="text" name="kode_pos" class="form-control rounded-pill py-2" value="{{ $getValue('kode_pos') }}">
                                    </div>
                                </div>
                                
                                {{-- Baris 3: Dusun, Kelurahan, Kecamatan --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="dusun" class="form-label">Dusun</label>
                                        <input type="text" name="dusun" class="form-control rounded-pill py-2" value="{{ $getValue('dusun') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="kelurahan" class="form-label">Kelurahan</label>
                                        <input type="text" name="kelurahan" class="form-control rounded-pill py-2" value="{{ $getValue('kelurahan') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="kecamatan" class="form-label">Kecamatan</label>
                                        <input type="text" name="kecamatan" class="form-control rounded-pill py-2" value="{{ $getValue('kecamatan') }}">
                                    </div>
                                </div>
                                
                                {{-- Baris 4: No. HP, Email --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="no_hp" class="form-label">No. HP</label>
                                        <input type="text" name="no_hp" class="form-control rounded-pill py-2" value="{{ $getValue('no_hp') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control rounded-pill py-2" value="{{ $getValue('email') }}">
                                    </div>
                                </div>
                                
                                {{-- Baris 5: Jenis Tinggal, Alat Transportasi, Jarak Rumah --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="jenis_tinggal" class="form-label">Jenis Tinggal</label>
                                        <input type="text" name="jenis_tinggal" class="form-control rounded-pill py-2" value="{{ $getValue('jenis_tinggal') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="alat_transportasi" class="form-label">Alat Transportasi</label>
                                        <input type="text" name="alat_transportasi" class="form-control rounded-pill py-2" value="{{ $getValue('alat_transportasi') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="jarak_rumah" class="form-label">Jarak Rumah (km)</label>
                                        <input type="text" name="jarak_rumah" class="form-control rounded-pill py-2" value="{{ $getValue('jarak_rumah') }}">
                                    </div>
                                </div>
                                
                                {{-- Baris 6: Lintang, Bujur --}}
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="lintang" class="form-label">Lintang</label>
                                        <input type="text" name="lintang" class="form-control rounded-pill py-2" value="{{ $getValue('lintang') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="bujur" class="form-label">Bujur</label>
                                        <input type="text" name="bujur" class="form-control rounded-pill py-2" value="{{ $getValue('bujur') }}">
                                    </div>
                                </div>

                                <hr class="my-4">
                                
                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-success"><i class="fas fa-users me-1"></i> IV. Data Orang Tua/Wali</h6>
                                <hr>
                                
                                {{-- AYAH --}}
                                <h6 class="text-xs font-weight-bolder text-secondary mb-3">4A. Data Ayah</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_ayah" class="form-label">Nama Ayah</label>
                                        <input type="text" name="nama_ayah" class="form-control rounded-pill py-2" value="{{ $getValue('nama_ayah') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tahun_lahir_ayah" class="form-label">Tahun Lahir Ayah</label>
                                        <input type="number" name="tahun_lahir_ayah" class="form-control rounded-pill py-2" value="{{ $getValue('tahun_lahir_ayah') }}" maxlength="4">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nik_ayah" class="form-label">NIK Ayah</label>
                                        <input type="text" name="nik_ayah" class="form-control rounded-pill py-2" value="{{ $getValue('nik_ayah') }}" maxlength="20">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <label for="pekerjaan_ayah" class="form-label">Pekerjaan Ayah</label>
                                        <input type="text" name="pekerjaan_ayah" class="form-control rounded-pill py-2" value="{{ $getValue('pekerjaan_ayah') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="jenjang_pendidikan_ayah" class="form-label">Jenjang Pendidikan Ayah</label>
                                        <input type="text" name="jenjang_pendidikan_ayah" class="form-control rounded-pill py-2" value="{{ $getValue('jenjang_pendidikan_ayah') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="penghasilan_ayah" class="form-label">Penghasilan Ayah</label>
                                        <input type="text" name="penghasilan_ayah" class="form-control rounded-pill py-2" value="{{ $getValue('penghasilan_ayah') }}">
                                    </div>
                                </div>

                                {{-- IBU --}}
                                <h6 class="text-xs font-weight-bolder text-secondary mb-3">4B. Data Ibu</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_ibu" class="form-label">Nama Ibu</label>
                                        <input type="text" name="nama_ibu" class="form-control rounded-pill py-2" value="{{ $getValue('nama_ibu') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tahun_lahir_ibu" class="form-label">Tahun Lahir Ibu</label>
                                        <input type="number" name="tahun_lahir_ibu" class="form-control rounded-pill py-2" value="{{ $getValue('tahun_lahir_ibu') }}" maxlength="4">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nik_ibu" class="form-label">NIK Ibu</label>
                                        <input type="text" name="nik_ibu" class="form-control rounded-pill py-2" value="{{ $getValue('nik_ibu') }}" maxlength="20">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <label for="pekerjaan_ibu" class="form-label">Pekerjaan Ibu</label>
                                        <input type="text" name="pekerjaan_ibu" class="form-control rounded-pill py-2" value="{{ $getValue('pekerjaan_ibu') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="jenjang_pendidikan_ibu" class="form-label">Jenjang Pendidikan Ibu</label>
                                        <input type="text" name="jenjang_pendidikan_ibu" class="form-control rounded-pill py-2" value="{{ $getValue('jenjang_pendidikan_ibu') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="penghasilan_ibu" class="form-label">Penghasilan Ibu</label>
                                        <input type="text" name="penghasilan_ibu" class="form-control rounded-pill py-2" value="{{ $getValue('penghasilan_ibu') }}">
                                    </div>
                                </div>

                                {{-- WALI --}}
                                <h6 class="text-xs font-weight-bolder text-secondary mb-3">4C. Data Wali (Opsional)</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_wali" class="form-label">Nama Wali</label>
                                        <input type="text" name="nama_wali" class="form-control rounded-pill py-2" value="{{ $getValue('nama_wali') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tahun_lahir_wali" class="form-label">Tahun Lahir Wali</label>
                                        <input type="number" name="tahun_lahir_wali" class="form-control rounded-pill py-2" value="{{ $getValue('tahun_lahir_wali') }}" maxlength="4">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nik_wali" class="form-label">NIK Wali</label>
                                        <input type="text" name="nik_wali" class="form-control rounded-pill py-2" value="{{ $getValue('nik_wali') }}" maxlength="20">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <label for="pekerjaan_wali" class="form-label">Pekerjaan Wali</label>
                                        <input type="text" name="pekerjaan_wali" class="form-control rounded-pill py-2" value="{{ $getValue('pekerjaan_wali') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="jenjang_pendidikan_wali" class="form-label">Jenjang Pendidikan Wali</label>
                                        <input type="text" name="jenjang_pendidikan_wali" class="form-control rounded-pill py-2" value="{{ $getValue('jenjang_pendidikan_wali') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="penghasilan_wali" class="form-label">Penghasilan Wali</label>
                                        <input type="text" name="penghasilan_wali" class="form-control rounded-pill py-2" value="{{ $getValue('penghasilan_wali') }}">
                                    </div>
                                </div>

                                <hr class="my-4">

                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-warning"><i class="fas fa-hand-holding-usd me-1"></i> V. Data Beasiswa & Rekening</h6>
                                <hr>

                                <h6 class="text-xs font-weight-bolder text-secondary mb-3">5A. Bantuan Sosial & Program Khusus</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="penerima_kps" class="form-label">Penerima KPS</label>
                                        <input type="text" name="penerima_kps" class="form-control rounded-pill py-2" value="{{ $getValue('penerima_kps') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="no_kps" class="form-label">No. KPS</label>
                                        <input type="text" name="no_kps" class="form-control rounded-pill py-2" value="{{ $getValue('no_kps') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="no_kks" class="form-label">No. KKS</label>
                                        <input type="text" name="no_kks" class="form-control rounded-pill py-2" value="{{ $getValue('no_kks') }}">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-3 mb-3">
                                        <label for="penerima_kip" class="form-label">Penerima KIP</label>
                                        <input type="text" name="penerima_kip" class="form-control rounded-pill py-2" value="{{ $getValue('penerima_kip') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="no_kip" class="form-label">No. KIP</label>
                                        <input type="text" name="no_kip" class="form-control rounded-pill py-2" value="{{ $getValue('no_kip') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="nama_kip" class="form-label">Nama di KIP</label>
                                        <input type="text" name="nama_kip" class="form-control rounded-pill py-2" value="{{ $getValue('nama_kip') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="layak_pip_usulan" class="form-label">Layak PIP (Usulan)</label>
                                        <input type="text" name="layak_pip_usulan" class="form-control rounded-pill py-2" value="{{ $getValue('layak_pip_usulan') }}">
                                    </div>
                                </div>

                                <h6 class="text-xs font-weight-bolder text-secondary mb-3">5B. Informasi Rekening Bank</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="bank" class="form-label">Nama Bank</label>
                                        <input type="text" name="bank" class="form-control rounded-pill py-2" value="{{ $getValue('bank') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="no_rek_bank" class="form-label">Nomor Rekening</label>
                                        <input type="text" name="no_rek_bank" class="form-control rounded-pill py-2" value="{{ $getValue('no_rek_bank') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="rek_atas_nama" class="form-label">Nama Pemilik Rekening</label>
                                        <input type="text" name="rek_atas_nama" class="form-control rounded-pill py-2" value="{{ $getValue('rek_atas_nama') }}">
                                    </div>
                                </div>
                                
                                <div class="text-end mt-4">
                                    <a href="{{ route('master.siswa.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                                    <button type="submit" class="btn bg-gradient-primary">Simpan Data Siswa Baru</button>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
        
    </main>
@endsection