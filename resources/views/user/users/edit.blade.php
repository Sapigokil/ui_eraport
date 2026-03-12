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
                            
                            {{-- Notifikasi Error Validasi --}}
                            @if ($errors->any())
                                <div class="alert alert-danger text-white mb-4" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('settings.system.users.update', $user->id) }}">
                                @csrf
                                @method('PUT')
                                
                                <h6 class="text-sm font-weight-bolder mb-3 text-info">Informasi Dasar Akun</h6>
                                
                                {{-- 1. Input Nama --}}
                                <div class="mb-3">
                                    <label for="inputName" class="form-label">Nama Lengkap</label>
                                    <input type="text" id="inputName" name="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           class="form-control border px-3 py-2" required>
                                </div>
                                
                                {{-- 2. Input Email --}}
                                <div class="mb-3"> 
                                    <label for="inputEmail" class="form-label">Email</label>
                                    <input type="email" id="inputEmail" name="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           class="form-control border px-3 py-2" required>
                                </div>

                                {{-- TAMBAHAN: 3. Input Password (Opsional) --}}
                                <div class="mb-3">
                                    <label for="inputPassword" class="form-label">Password Baru <span class="text-xs text-muted fw-normal">(Kosongkan jika tidak ingin mengubah password)</span></label>
                                    <input type="password" id="inputPassword" name="password" 
                                           class="form-control border px-3 py-2" 
                                           placeholder="Minimal 8 Karakter">
                                </div>
                                
                                <hr class="my-4">

                                <h6 class="text-sm font-weight-bolder mb-3 text-danger">Pengaturan Role & Status</h6>

                                {{-- 4. PILIHAN ROLE (Select) --}}
                                <div class="mb-3">
                                    <label for="role_select" class="form-label">Role Pengguna</label>
                                    <select class="form-select border px-3 py-2" id="role_select" name="role_name" required>
                                        <option value="">-- Pilih Role --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}" 
                                                    {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                {{ Str::title(str_replace('_', ' ', $role->name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                {{-- 5. STATUS AKTIF/NONAKTIF --}}
                                <div class="form-check form-switch d-flex align-items-center mb-4 ps-0 mt-4">
                                    <input class="form-check-input ms-0" type="checkbox" id="is_active" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label mb-0 ms-3" for="is_active">
                                        Status Akun Aktif (Izinkan Login)
                                    </label>
                                </div>

                                <div class="text-end mt-4">
                                    <a href="{{ route('settings.system.users.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
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