{{-- File: resources/views/ekskul/list_create.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Tambah Master Ekstrakurikuler')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-plus me-2"></i> Tambah Ekskul Baru</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-4 px-4">
                            <form method="POST" action="{{ route('master.ekskul.list.store') }}">
                                @csrf
                                
                                {{-- Input Nama Ekskul --}}
                                <div class="mb-3">
                                    <label for="nama_ekskul" class="form-label">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_ekskul') is-invalid @enderror" id="nama_ekskul" name="nama_ekskul" value="{{ old('nama_ekskul') }}" required>
                                    @error('nama_ekskul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                {{-- Input Jadwal Ekskul --}}
                                <div class="mb-3">
                                    <label for="jadwal_ekskul" class="form-label">Jadwal Ekskul (Contoh: Setiap Rabu, 15.00 - 17.00)</label>
                                    <input type="text" class="form-control @error('jadwal_ekskul') is-invalid @enderror" id="jadwal_ekskul" name="jadwal_ekskul" value="{{ old('jadwal_ekskul') }}">
                                    @error('jadwal_ekskul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                {{-- Dropdown Pembina (Guru) --}}
                                <div class="mb-4">
                                    <label for="id_guru" class="form-label">Guru Pembina <span class="text-danger">*</span></label>
                                    <select class="form-select @error('id_guru') is-invalid @enderror" id="id_guru" name="id_guru" required>
                                        <option value="0" {{ old('id_guru') == '0' ? 'selected' : '' }}>-- Belum Ditentukan --</option> 
                                        
                                        {{-- Loop menggunakan $gurus --}}
                                        @foreach ($gurus as $guru)
                                            <option value="{{ $guru->id_guru }}" {{ old('id_guru') == $guru->id_guru ? 'selected' : '' }}>
                                                {{ $guru->nama_guru }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_guru')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    @if ($gurus->isEmpty())
                                        <small class="text-warning d-block mt-2">
                                            <i class="fas fa-exclamation-triangle"></i> Belum ada data Guru. Mohon tambahkan data Guru terlebih dahulu.
                                        </small>
                                    @endif
                                </div>
                                
                                {{-- Tombol Aksi --}}
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('master.ekskul.list.index') }}" class="btn btn-secondary me-2">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Ekskul</button>
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