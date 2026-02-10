@extends('layouts.app') 

@section('page-title', 'Edit Role: ' . $role->name) 

@section('content')
    {{-- START: Pembungkus Main Content agar Navbar tampil --}}
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
                                <h6 class="text-white text-capitalize ps-3">Formulir Edit Izin untuk Role: {{ Str::title($role->name) }}</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Form menargetkan route roles.update dengan metode PUT --}}
                            <form method="POST" action="{{ route('settings.system.roles.update', $role->id) }}">
                                @csrf
                                @method('PUT')
                                
                                <h6 class="text-sm font-weight-bolder mb-3 text-info">Detail Role</h6>
                                
                                {{-- 1. Input Nama Role --}}
                                <div class="mb-3">
                                    <label for="inputRoleName" class="form-label">Nama Role</label>
                                    <input type="text" id="inputRoleName" name="name" 
                                           value="{{ old('name', $role->name) }}" 
                                           class="form-control rounded-pill py-2 @error('name') is-invalid @enderror"
                                           {{ in_array($role->name, ['admin', 'guru', 'wali murid']) ? 'readonly' : '' }}>
                                    
                                    @if(in_array($role->name, ['admin', 'guru', 'wali murid']))
                                        <small class="text-danger">Nama role krusial tidak dapat diubah.</small>
                                    @endif
                                    @error('name')
                                        <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <hr class="my-4">

                                <h6 class="text-sm font-weight-bolder mb-3 text-danger">Atur Izin (Permissions)</h6>

                                <div class="row">
                                    @foreach($permissions as $group => $items)
                                        <div class="col-md-6 mb-4"> {{-- Menggunakan 2 kolom agar rapi --}}
                                            <div class="card h-100 border shadow-sm">
                                                
                                                {{-- HEADER: Menampilkan Nama Group --}}
                                                <div class="card-header bg-light py-2">
                                                    <h6 class="mb-0 text-dark font-weight-bold">
                                                        {{ $group ?? 'Permissions Lainnya' }}
                                                    </h6>
                                                </div>

                                                {{-- BODY: Menampilkan Checkbox dengan Label --}}
                                                <div class="card-body py-2">
                                                    @foreach($items as $permission)
                                                        <div class="form-check my-2">
                                                            {{-- Input tetap mengirimkan 'name' (misal: users.create) --}}
                                                            <input class="form-check-input" type="checkbox" 
                                                                name="permissions[]" 
                                                                value="{{ $permission->name }}" 
                                                                id="perm-{{ $permission->id }}"
                                                                {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}>
                                                            
                                                            {{-- Label menampilkan teks manusiawi (misal: Menambahkan Pengguna Baru) --}}
                                                            <label class="form-check-label text-sm cursor-pointer" for="perm-{{ $permission->id }}">
                                                                {{ $permission->label ?? $permission->name }} 
                                                                {{-- Fallback ke name jika label kosong --}}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="text-end mt-4">
                                    <a href="{{ route('settings.system.roles.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                                    <button type="submit" class="btn bg-gradient-primary">Simpan Perubahan Izin</button>
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