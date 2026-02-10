@extends('layouts.app') 

@section('page-title', 'Manajemen Role & Izin') 

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3">Daftar Role Pengguna E-Rapor</h6>
                                @can('roles.menu')
                                <a href="{{ route('settings.system.roles.create') }}" class="btn btn-white me-3 mb-0">
                                    <i class="fas fa-plus me-1"></i> Tambah Role Baru
                                </a>
                                @endcan
                            </div>
                        </div>
                        
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Role Name</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Izin (Permissions)</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pengguna Aktif</th>
                                            <th class="text-secondary opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($roles as $role)
                                        
                                        {{-- REVISI DISINI: Tambahkan 'guru' dan 'siswa' ke dalam array --}}
                                        @php
                                            $isSystemRole = in_array(strtolower($role->name), ['developer', 'admin_erapor', 'guru_erapor', 'guru_ekskul', 'guru', 'siswa']);
                                        @endphp

                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <div class="d-flex align-items-center">
                                                            <h6 class="mb-0 text-sm">{{ Str::title(str_replace('_', ' ', $role->name)) }}</h6>
                                                            
                                                            {{-- Badge SYSTEM akan muncul untuk Developer, Admin_Erapor, Guru_Erapor, Guru, dan Siswa --}}
                                                            @if($isSystemRole)
                                                                <span class="badge badge-sm bg-gradient-secondary ms-2" style="font-size: 0.6rem;">SYSTEM</span>
                                                            @endif
                                                        </div>
                                                        <p class="text-xs text-secondary mb-0">Dibuat: {{ $role->created_at->format('d/m/Y') }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    {{ $role->permissions->count() }} Izin Akses
                                                </p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-success">{{ $role->users->count() }} User</span>
                                            </td>
                                            <td class="align-middle">
                                                @can('roles.menu')
                                                <a href="{{ route('settings.system.roles.edit', $role->id) }}" class="text-primary font-weight-bold text-xs me-3" data-toggle="tooltip" title="Atur Izin">
                                                    <i class="fas fa-pencil-alt me-1"></i> Edit
                                                </a>
                                                @endcan
                                                
                                                @can('roles.menu')
                                                    @if ($isSystemRole)
                                                        {{-- Role Default/Sistem TERKUNCI --}}
                                                        <span class="text-secondary text-xs" data-toggle="tooltip" title="Role Sistem/Default tidak dapat dihapus">
                                                            <i class="fas fa-lock me-1"></i> Terkunci
                                                        </span>
                                                    @else
                                                        {{-- Role Tambahan Lainnya (misal: Ekskul, TU) BISA DIHAPUS --}}
                                                        <form action="{{ route('settings.system.roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link text-danger p-0 m-0 text-xs" 
                                                                    onclick="return confirm('PERINGATAN: Menghapus role ini akan menghilangkan akses bagi semua user yang terkait. Lanjutkan?')">
                                                                <i class="fas fa-trash-alt me-1"></i> Hapus
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <h6 class="text-secondary text-sm">Belum ada Role yang terdaftar.</h6>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>
@endsection