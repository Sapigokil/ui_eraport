@extends('layouts.app') 

@section('page-title', 'Edit Pengguna: ' . $user->name)

@section('content')
    {{-- START: Pembungkus Main Content --}}
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        {{-- Panggil Navbar agar tampil di halaman ini --}}
        <x-app.navbar />
        
        {{-- Konten Formulir dimulai --}}
        <div class="container-fluid py-4 px-5"> {{-- Tambahkan px-5 agar padding horizontal konsisten dengan Dashboard --}}
            
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Formulir Edit Role & Status Pengguna</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            <form method="POST" action="{{ route('master.users.update', $user->id) }}">
                                @csrf
                                @method('PUT')
                                
                                <h6 class="text-sm font-weight-bolder mb-3 text-info">Informasi Dasar Akun</h6>
                                
                                {{-- 1. Input Nama --}}
                                <div class="mb-3">
                                    <label for="inputName" class="form-label">Nama Lengkap</label>
                                    <input type="text" id="inputName" name="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           class="form-control rounded-pill py-2">
                                </div>
                                
                                {{-- 2. Input Email (BISA DIUBAH) --}}
                                <div class="mb-3"> 
                                    <label for="inputEmail" class="form-label">Email</label>
                                    <input type="email" id="inputEmail" name="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           class="form-control rounded-pill py-2">
                                </div>
                                
                                <hr class="my-4">

                                <h6 class="text-sm font-weight-bolder mb-3 text-danger">Pengaturan Role & Status</h6>

                                {{-- 3. PILIHAN ROLE (Select) --}}
                                <div class="mb-3">
                                    <label for="role_select" class="form-label">Role Pengguna</label>
                                    <select class="form-select rounded-pill py-2" id="role_select" name="role_name">
                                        <option value="">-- Pilih Role --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}" 
                                                    {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                {{ Str::title($role->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                {{-- 4. STATUS AKTIF/NONAKTIF --}}
                                <div class="form-check form-switch d-flex align-items-center mb-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label mb-0 ms-3" for="is_active">
                                        Status Akun Aktif (Izinkan Login)
                                    </label>
                                </div>

                                <div class="text-end mt-4">
                                    <a href="{{ route('master.users.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                                    <button type="submit" class="btn bg-gradient-primary">Simpan Perubahan</button>
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