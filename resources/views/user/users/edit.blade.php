@extends('layouts.app') 

@section('page-title', 'Edit Pengguna Sistem')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-12">
                    
                    <div class="card my-4 border shadow-xs">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-4"><i class="fas fa-user-edit me-2"></i> Edit Data Pengguna</h6>
                                <div class="pe-3">
                                    <a href="{{ route('settings.system.users.index') }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body px-4 pt-4 pb-4">
                            
                            @if ($errors->any())
                                <div class="alert bg-gradient-danger text-white alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert bg-gradient-danger text-white alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('settings.system.users.update', $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                {{-- OPSI PENAUTAN AKUN (READ-ONLY) --}}
                                <div class="p-4 bg-light border rounded mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-dark font-weight-bold mb-0"><i class="fas fa-link me-1 text-primary"></i> Status Penautan Akun</h6>
                                        <span class="badge bg-gradient-secondary shadow-sm"><i class="fas fa-lock me-1"></i> Terkunci</span>
                                    </div>
                                    
                                    <div class="alert alert-info text-dark text-sm mb-0 border-0" role="alert">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-info-circle fa-2x me-3"></i>
                                            <div>
                                                <strong>Informasi Integritas Data:</strong><br>
                                                Akun ini telah terdaftar secara permanen sebagai:
                                            </div>
                                        </div>
                                        <hr class="horizontal light my-2">
                                        <div class="ps-5">
                                            @if($jenis_akun == 'guru')
                                                <ul class="mb-2">
                                                    <li>Tipe: <b>Data Guru</b></li>
                                                    <li>Tertaut dengan: <b>{{ $user->guru->nama_guru ?? 'Data Induk Guru Telah Terhapus' }}</b></li>
                                                </ul>
                                            @elseif($jenis_akun == 'siswa')
                                                <ul class="mb-2">
                                                    <li>Tipe: <b>Data Siswa</b></li>
                                                    <li>Tertaut dengan: <b>{{ $user->siswa->nama_siswa ?? 'Data Induk Siswa Telah Terhapus' }}</b></li>
                                                </ul>
                                            @else
                                                <ul class="mb-2">
                                                    <li>Tipe: <b>Admin / Non-Staff (Manual)</b></li>
                                                    <li>Status: <i>Tidak ada penautan ke data fisik Guru maupun Siswa.</i></li>
                                                </ul>
                                            @endif
                                            
                                            <i class="opacity-8 text-xs">*SOP Sistem: Jika terjadi kesalahan penautan di masa lalu, mohon jangan diubah di sini. Silakan <b>Hapus</b> akun ini dan buat akun baru. Pengubahan Nama Lengkap tidak merubah pada data induk.</i>
                                        </div>
                                    </div>
                                </div>

                                <hr class="horizontal dark my-4">

                                {{-- FORM UTAMA --}}
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="name" class="form-label font-weight-bold text-secondary text-xs">Nama Lengkap Pengguna <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control border px-3" style="height: 40px;" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Contoh: Budi Santoso" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label font-weight-bold text-secondary text-xs">Alamat Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control border px-3" style="height: 40px;" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Contoh: budi@sekolah.com" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="password" class="form-label font-weight-bold text-secondary text-xs">Password Baru (Opsional)</label>
                                        <input type="password" class="form-control border px-3" style="height: 40px;" id="password" name="password" placeholder="Kosongkan jika tidak mengubah password">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="role_name" class="form-label font-weight-bold text-secondary text-xs">Hak Akses (Role) <span class="text-danger">*</span></label>
                                        <select name="role_name" id="role_name" class="form-select border px-3" style="height: 40px;" required>
                                            <option value="">-- Pilih Hak Akses --</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ old('role_name', $user->roles->first()->name ?? '') == $role->name ? 'selected' : '' }}>
                                                    {{ \Illuminate\Support\Str::title($role->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check form-switch d-flex align-items-center px-0">
                                            <input class="form-check-input ms-0 me-3" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label mb-0 font-weight-bold text-secondary text-sm" for="is_active">Status Akun Aktif</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn bg-gradient-primary">
                                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                                    </button>
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