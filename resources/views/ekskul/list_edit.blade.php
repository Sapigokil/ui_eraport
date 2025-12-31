{{-- File: resources/views/ekskul/list_edit.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Edit Data Ekstrakurikuler')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

        <x-app.navbar />

        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 shadow-xs border">
                        {{-- KONTROL ATAS: HEADER --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-edit me-2"></i> Edit Data Ekstrakurikuler</h6>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            {{-- NOTIFIKASI ERRORS --}}
                            @if ($errors->any())
                                <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm">
                                        Data gagal disimpan karena ada kesalahan input:
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <form action="{{ route('master.ekskul.list.update', $ekskul->id_ekskul) }}" method="POST" class="p-4">
                                @csrf
                                @method('PUT')

                                {{-- Field: Nama Ekskul (TETAP REQUIRED) --}}
                                <div class="mb-3">
                                    <label for="nama_ekskul" class="form-label">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_ekskul" name="nama_ekskul" value="{{ old('nama_ekskul', $ekskul->nama_ekskul) }}" required>
                                    @error('nama_ekskul')
                                        <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Field: Jadwal Ekskul (OPSIONAL) --}}
                                <div class="mb-3">
                                    <label for="jadwal_ekskul" class="form-label">Jadwal Ekskul (Contoh: Senin, 14:00 - 16:00)</label>
                                    <input type="text" class="form-control" id="jadwal_ekskul" name="jadwal_ekskul" value="{{ old('jadwal_ekskul', $ekskul->jadwal_ekskul) }}">
                                    @error('jadwal_ekskul')
                                        <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Field: Pembina (SEKARANG OPSIONAL) --}}
                                <div class="mb-3">
                                    {{-- 🛑 PERUBAHAN 1: Hapus tanda bintang merah dari label --}}
                                    <label for="id_guru" class="form-label">Pembina</label> 
                                    
                                    {{-- 🛑 PERUBAHAN 2: Hapus atribut 'required' dari tag select --}}
                                    <select class="form-select" id="id_guru" name="id_guru"> 
                                        
                                        <option value="" 
                                            {{ old('id_guru', $ekskul->id_guru) == null ? 'selected' : '' }}>
                                            -- Pilih Guru Pembina --
                                        </option>
                                        
                                        @foreach ($gurus as $guru)
                                            <option value="{{ $guru->id_guru }}" 
                                                {{ old('id_guru', $ekskul->id_guru) == $guru->id_guru ? 'selected' : '' }}>
                                                {{ $guru->nama_guru }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_guru')
                                        <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    {{-- Tombol Batal --}}
                                    <a href="{{ route('master.ekskul.list.index') }}" class="btn btn-secondary me-2">Batal</a>
                                    {{-- Tombol Simpan --}}
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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