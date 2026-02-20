@extends('layouts.app') 

@section('page-title', 'Edit Mata Pelajaran')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-edit me-2"></i> Edit Mata Pelajaran</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Notifikasi Error --}}
                            @if ($errors->any())
                                <div class="alert alert-danger text-dark">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Form Edit --}}
                            <form action="{{ route('master.mapel.update', $mapel->id_mapel) }}" method="POST">
                                @csrf
                                @method('PUT')

                                {{-- I. Informasi Pokok Mata Pelajaran --}}
                                <h6 class="text-sm font-weight-bolder my-4 text-info"><i class="fas fa-info-circle me-1"></i> Data Mata Pelajaran</h6>
                                <div class="row">
                                    
                                    {{-- Nama Mapel Lengkap --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_mapel" class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama_mapel" name="nama_mapel" value="{{ old('nama_mapel', $mapel->nama_mapel) }}" required>
                                    </div>
                                    
                                    {{-- Nama Singkat --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_singkat" class="form-label">Nama Singkat (Maks 10 Karakter) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama_singkat" name="nama_singkat" value="{{ old('nama_singkat', $mapel->nama_singkat) }}" required maxlength="10">
                                    </div>
                                    
                                    {{-- Kategori --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                                        <select class="form-select" id="kategori" name="kategori" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($kategoriList as $key => $value)
                                                <option value="{{ $key }}" {{ old('kategori', $mapel->kategori) == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Urutan --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="urutan" class="form-label">Urutan Tampilan Rapor <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="urutan" name="urutan" value="{{ old('urutan', $mapel->urutan) }}" required min="1">
                                        <small class="text-muted text-xs">Angka urutan mapel saat dicetak di rapor.</small>
                                    </div>

                                </div>
                                
                                <hr class="my-4">

                                {{-- II. Konfigurasi Lanjutan --}}
                                <h6 class="text-sm font-weight-bolder my-4 text-warning"><i class="fas fa-cogs me-1"></i> Konfigurasi Lanjutan</h6>
                                
                                <div class="row">
                                    {{-- Status Aktif (Menggantikan Guru Pengampu) --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="is_active" class="form-label">Status Mata Pelajaran</label>
                                        <select class="form-select" id="is_active" name="is_active" required>
                                            <option value="1" {{ old('is_active', $mapel->is_active) == 1 ? 'selected' : '' }}>Aktif (Tampil di Rapor)</option>
                                            <option value="0" {{ old('is_active', $mapel->is_active) == 0 ? 'selected' : '' }}>Non-Aktif (Disembunyikan)</option>
                                        </select>
                                        <small class="text-muted text-xs">Mapel non-aktif tidak akan muncul di input nilai maupun cetak rapor.</small>
                                    </div>

                                    {{-- Agama Khusus --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="agama_khusus" class="form-label">Agama Khusus (Auto Filter)</label>
                                        <select class="form-select" id="agama_khusus" name="agama_khusus">
                                            <option value="">-- Umum / Semua Agama --</option>
                                            @php
                                                $agamaList = ['Islam', 'Kristen', 'Katholik', 'Hindu', 'Budha', 'Khonghucu'];
                                            @endphp
                                            @foreach ($agamaList as $agama)
                                                <option value="{{ $agama }}" {{ old('agama_khusus', $mapel->agama_khusus) == $agama ? 'selected' : '' }}>
                                                    {{ $agama }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted text-xs">
                                            <i class="fas fa-info-circle text-info"></i> Jika dipilih, mapel ini hanya muncul untuk siswa agama tersebut.
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-2 border-top">
                                    <button type="submit" class="btn bg-gradient-info me-2">
                                        <i class="fas fa-save me-1"></i> Perbarui Data
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