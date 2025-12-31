@extends('layouts.app') 

@section('page-title', 'Tambah Pengguna Baru')

@section('content')
    {{-- START: Pembungkus Main Content --}}
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        {{-- Panggil Navbar agar tampil di halaman ini --}}
        <x-app.navbar />
        
        {{-- Konten Formulir dimulai --}}
        <div class="container-fluid py-4 px-5"> {{-- Tambahkan px-5 agar padding horizontal konsisten --}}
            
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Formulir Tambah Akun Pengguna Baru</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            <form method="POST" action="{{ route('master.users.store') }}">
                                @csrf
                                
                                <h6 class="text-sm font-weight-bolder mb-3 text-info">Informasi Akun</h6>
                                
                                {{-- 1. Input Nama --}}
                                <div class="mb-3">
                                    <label for="inputName" class="form-label">Nama Lengkap</label>
                                    <input type="text" id="inputName" name="name" 
                                           value="{{ old('name') }}" 
                                           class="form-control rounded-pill py-2 @error('name') is-invalid @enderror">
                                    @error('name')
                                        <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                {{-- 2. Input Email --}}
                                <div class="mb-3"> 
                                    <label for="inputEmail" class="form-label">Email</label>
                                    <input type="email" id="inputEmail" name="email"
                                           value="{{ old('email') }}" 
                                           class="form-control rounded-pill py-2 @error('email') is-invalid @enderror">
                                    @error('email')
                                        <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- 3. Input Password --}}
                                <div class="mb-3"> 
                                    <label for="inputPassword" class="form-label">Password</label>
                                    <input type="password" id="inputPassword" name="password"
                                           class="form-control rounded-pill py-2 @error('password') is-invalid @enderror">
                                    <small class="text-muted">Minimal 8 karakter.</small>
                                    @error('password')
                                        <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <hr class="my-4">

                                <h6 class="text-sm font-weight-bolder mb-3 text-danger">Pengaturan Role & Status</h6>

                                {{-- 4. PILIHAN ROLE (Select) --}}
                                <div class="mb-3">
                                    <label for="role_select" class="form-label">Role Pengguna</label>
                                    <select class="form-select rounded-pill py-2 @error('role_name') is-invalid @enderror" id="role_select" name="role_name">
                                        <option value="">-- Pilih Role --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}" 
                                                    {{ old('role_name') == $role->name ? 'selected' : '' }}>
                                                {{ Str::title($role->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_name')
                                        <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                {{-- 5. STATUS AKTIF/NONAKTIF (Default Aktif) --}}
                                <div class="form-check form-switch d-flex align-items-center mb-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label mb-0 ms-3" for="is_active">
                                        Status Akun Aktif (Izinkan Login)
                                    </label>
                                </div>

                                <div class="text-end mt-4">
                                    <a href="{{ route('master.users.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                                    <button type="submit" class="btn bg-gradient-primary">Simpan Pengguna</button>
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