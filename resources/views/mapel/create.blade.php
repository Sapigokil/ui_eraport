{{-- File: resources/views/mapel/create.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Tambah Data Mata Pelajaran Baru')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-plus me-2"></i> Tambah Mata Pelajaran Baru</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Notifikasi Error --}}
                            @if ($errors->any())
                                <div class="alert alert-danger text-white">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('master.mapel.store') }}" method="POST">
                                @csrf

                                {{-- I. Informasi Pokok Mata Pelajaran --}}
                                <h6 class="text-sm font-weight-bolder my-4 text-primary"><i class="fas fa-info-circle me-1"></i> Data Mata Pelajaran</h6>
                                <div class="row">
                                    
                                    {{-- Nama Mapel Lengkap --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_mapel" class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama_mapel" name="nama_mapel" value="{{ old('nama_mapel') }}" required>
                                    </div>
                                    
                                    {{-- Nama Singkat --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_singkat" class="form-label">Nama Singkat (Maks 10 Karakter) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama_singkat" name="nama_singkat" value="{{ old('nama_singkat') }}" required maxlength="10">
                                    </div>
                                    
                                    {{-- Kategori --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                                        <select class="form-select" id="kategori" name="kategori" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($kategoriList as $key => $value)
                                                <option value="{{ $key }}" {{ old('kategori') == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Urutan --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="urutan" class="form-label">Urutan Tampilan Rapor <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="urutan" name="urutan" value="{{ old('urutan') }}" required min="1">
                                        <small class="text-muted">Masukkan angka urutan untuk tampilan di rapor.</small>
                                    </div>

                                </div>
                                
                                <hr class="my-4">

                                {{-- II. Konfigurasi Pengampu --}}
                                <h6 class="text-sm font-weight-bolder my-4 text-info"><i class="fas fa-user-tie me-1"></i> Guru Pengampu</h6>
                                
                                <div class="row">
                                    {{-- Guru Pengampu --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="id_guru" class="form-label">Guru Pengampu (Utama)</label>
                                        <select class="form-select" id="id_guru" name="id_guru">
                                            <option value="">-- Pilih Guru --</option>
                                            @foreach ($guru as $g)
                                                <option value="{{ $g->id_guru }}" {{ old('id_guru') == $g->id_guru ? 'selected' : '' }}>
                                                    {{ $g->nama_guru }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Opsional, bisa ditambahkan nanti.</small>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-2 border-top">
                                    <button type="submit" class="btn bg-gradient-success me-2">
                                        <i class="fas fa-save me-1"></i> Simpan Mata Pelajaran
                                    </button>
                                    <a href="{{ route('master.mapel.index') }}" class="btn btn-outline-secondary">
                                        Batal
                                    </a>
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