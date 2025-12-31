@extends('layouts.app') 

@section('page-title', 'Master Data Sekolah')

@section('content')
    {{-- START: Pembungkus Main Content agar Navbar tampil konsisten --}}
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        {{-- Panggil Navbar --}}
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-lg-10 col-md-10 mx-auto">
                    
                    {{-- Card Utama --}}
                    <div class="card my-4 shadow-xs border">

                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">
                                    <i class="fas fa-university me-2"></i> Formulir Informasi Sekolah
                                </h6>
                            </div>
                        </div>

                        <div class="card-body pb-2 px-4">
                            
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible text-white" role="alert">
                                    <span class="text-sm">{{ session('success') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible text-white" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            
                            {{-- FORMULIR --}}
                            <form method="POST" action="{{ route('master.sekolah.update') }}" autocomplete="off">
                                @csrf
                                
                                <input type="hidden" name="id_infosekolah" value="{{ $infoSekolah->id_infosekolah ?? '' }}">

                                {{-- ================================================= --}}
                                {{-- 1. IDENTITAS SEKOLAH --}}
                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-info">1. Identitas Utama</h6>
                                
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        {{-- Nama Sekolah --}}
                                        <label class="form-label" for="nama_sekolah">Nama Sekolah *</label>
                                        <input type="text" id="nama_sekolah" class="form-control" name="nama_sekolah" 
                                            value="{{ old('nama_sekolah', $infoSekolah->nama_sekolah ?? '') }}" required>
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        {{-- Jenjang Pendidikan --}}
                                        <label class="form-label" for="jenjang">Jenjang Pendidikan</label>
                                        <input type="text" id="jenjang" class="form-control" name="jenjang" 
                                            value="{{ old('jenjang', $infoSekolah->jenjang ?? '') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        {{-- NISN --}}
                                        <label class="form-label" for="nisn">NISN</label>
                                        <input type="text" id="nisn" class="form-control" name="nisn" 
                                            value="{{ old('nisn', $infoSekolah->nisn ?? '') }}">
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        {{-- NPSN --}}
                                        <label class="form-label" for="npsn">NPSN *</label>
                                        <input type="text" id="npsn" class="form-control" name="npsn" 
                                            value="{{ old('npsn', $infoSekolah->npsn ?? '') }}" required>
                                    </div>
                                </div>

                                <hr class="my-4">
                                
                                {{-- ================================================= --}}
                                {{-- 2. ALAMAT FISIK --}}
                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-danger">2. Alamat Fisik</h6>

                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        {{-- Alamat Jalan --}}
                                        <label class="form-label" for="jalan">Alamat Jalan</label>
                                        <input type="text" id="jalan" class="form-control" name="jalan" 
                                            value="{{ old('jalan', $infoSekolah->jalan ?? '') }}">
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        {{-- Kelurahan/Desa --}}
                                        <label class="form-label" for="kelurahan">Desa / Kelurahan</label>
                                        <input type="text" id="kelurahan" class="form-control" name="kelurahan" 
                                            value="{{ old('kelurahan', $infoSekolah->kelurahan ?? '') }}">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        {{-- Kecamatan --}}
                                        <label class="form-label" for="kecamatan">Kecamatan</label>
                                        <input type="text" id="kecamatan" class="form-control" name="kecamatan" 
                                            value="{{ old('kecamatan', $infoSekolah->kecamatan ?? '') }}">
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        {{-- Kabupaten / Kota --}}
                                        <label class="form-label" for="kota_kab">Kabupaten / Kota</label>
                                        <input type="text" id="kota_kab" class="form-control" name="kota_kab" 
                                            value="{{ old('kota_kab', $infoSekolah->kota_kab ?? '') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        {{-- Provinsi --}}
                                        <label class="form-label" for="provinsi">Provinsi</label>
                                        <input type="text" id="provinsi" class="form-control" name="provinsi" 
                                            value="{{ old('provinsi', $infoSekolah->provinsi ?? '') }}">
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        {{-- Kode Pos --}}
                                        <label class="form-label" for="kode_pos">Kode Pos</label>
                                        <input type="text" id="kode_pos" class="form-control" name="kode_pos" 
                                            value="{{ old('kode_pos', $infoSekolah->kode_pos ?? '') }}">
                                    </div>
                                </div>

                                <hr class="my-4">

                                {{-- ================================================= --}}
                                {{-- 3. KONTAK & WEBSITE --}}
                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-warning">3. Kontak Sekolah</h6>
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        {{-- Telepon/Fax --}}
                                        <label class="form-label" for="telp_fax">Telepon/Fax</label>
                                        <input type="text" id="telp_fax" class="form-control" name="telp_fax" 
                                            value="{{ old('telp_fax', $infoSekolah->telp_fax ?? '') }}">
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        {{-- Email --}}
                                        <label class="form-label" for="email">Email</label>
                                        <input type="email" id="email" class="form-control" name="email" 
                                            value="{{ old('email', $infoSekolah->email ?? '') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        {{-- Website --}}
                                        <label class="form-label" for="website">Website</label>
                                        <input type="url" id="website" class="form-control" name="website" 
                                            value="{{ old('website', $infoSekolah->website ?? '') }}">
                                    </div>
                                </div>

                                <hr class="my-4">

                                {{-- ================================================= --}}
                                {{-- 4. KEPALA SEKOLAH --}}
                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-success">4. Kepala Sekolah</h6>
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        {{-- Nama Kepsek --}}
                                        <label class="form-label" for="nama_kepsek">Nama Kepala Sekolah</label>
                                        <input type="text" id="nama_kepsek" class="form-control" name="nama_kepsek" 
                                            value="{{ old('nama_kepsek', $infoSekolah->nama_kepsek ?? '') }}">
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        {{-- NIP Kepsek --}}
                                        <label class="form-label" for="nip_kepsek">NIP Kepala Sekolah</label>
                                        <input type="text" id="nip_kepsek" class="form-control" name="nip_kepsek" 
                                            value="{{ old('nip_kepsek', $infoSekolah->nip_kepsek ?? '') }}">
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn bg-gradient-primary">
                                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                            
                        </div> {{-- End card-body --}}
                    </div> {{-- End card --}}
                </div> {{-- End col --}}
            </div> {{-- End row --}}
            
            {{-- Panggil Footer --}}
            <x-app.footer />
        </div>
        
    </main>
    {{-- END: Pembungkus Main Content --}}
@endsection