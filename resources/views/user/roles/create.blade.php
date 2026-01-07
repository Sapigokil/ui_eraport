@extends('layouts.app') 

@section('page-title', 'Tambah Role Baru')

@section('content')
    {{-- START: Pembungkus Main Content --}}
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        {{-- Panggil Navbar agar tampil --}}
        <x-app.navbar />
        
        {{-- Konten Formulir dimulai --}}
        <div class="container-fluid py-4 px-5"> 
            
            <div class="row">
                <div class="col-lg-10 col-md-10 mx-auto">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Formulir Tambah Role Baru</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Form menargetkan route roles.store dengan metode POST --}}
                            <form method="POST" action="{{ route('master.roles.store') }}">
                                @csrf
                                
                                <h6 class="text-sm font-weight-bolder mb-3 text-info">Detail Role</h6>
                                
                                {{-- 1. Input Nama Role --}}
                                <div class="mb-3">
                                    <label for="inputRoleName" class="form-label">Nama Role</label>
                                    <input type="text" id="inputRoleName" name="name" 
                                           value="{{ old('name') }}" 
                                           class="form-control rounded-pill py-2 @error('name') is-invalid @enderror">
                                    <small class="text-muted">Contoh: Kepala Sekolah, Guru BK, Wali Kelas.</small>
                                    @error('name')
                                        <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <hr class="my-4">

                                <h6 class="text-sm font-weight-bolder mb-3 text-danger">Pilih Izin (Permissions)</h6>

                                {{-- Daftar Permissions --}}
                                <div class="row">
                                    @foreach ($permissions->chunk(3) as $chunk) {{-- Bagi Permissions menjadi 3 kolom --}}
                                        <div class="col-md-4 mb-3">
                                            @foreach ($chunk as $permission)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                           value="{{ $permission->name }}" id="perm-{{ $permission->id }}" 
                                                           {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                                    
                                                    <label class="custom-control-label" for="perm-{{ $permission->id }}">
                                                        {{ Str::title(str_replace('-', ' ', $permission->name)) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="text-end mt-4">
                                    <a href="{{ route('master.roles.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                                    <button type="submit" class="btn bg-gradient-primary">Simpan Role Baru</button>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Panggil Footer agar tampil --}}
            <x-app.footer />
        </div>
        
    </main>
    {{-- END: Pembungkus Main Content --}}
@endsection