@extends('layouts.app') 

@section('page-title', 'List Pengguna Sistem')

@section('content')
    {{-- START: Pembungkus Main Content agar Navbar tampil konsisten --}}
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        {{-- Panggil Navbar agar tampil --}}
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            {{-- Konten Utama Users Index Dimulai di Sini --}}
            
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3">Daftar Akun Pengguna E-Rapor</h6>
                                {{-- Hanya Admin yang berhak menambah pengguna --}}
                                @can('pengaturan-manage-users')
                                <a href="{{ route('master.users.create') }}" class="btn btn-white me-3 mb-0">
                                    <i class="fas fa-plus me-1"></i> Tambah Pengguna Baru
                                </a>
                                @endcan
                            </div>
                        </div>
                        
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pengguna</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role Saat Ini</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Login</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Terakhir Login</th>
                                            <th class="text-secondary opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Looping Data User --}}
                                        @forelse ($users as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{-- Menampilkan Role --}}
                                                @foreach ($user->roles as $role)
                                                    <span class="badge badge-sm {{ $role->name == 'admin' ? 'bg-gradient-warning' : 'bg-gradient-info' }}">{{ Str::title($role->name) }}</span>
                                                @endforeach
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                {{-- Status Aktif/Nonaktif --}}
                                                @if ($user->is_active)
                                                    <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum Pernah' }}
                                            </td>
                                            <td class="align-middle">
                                                @can('pengaturan-manage-users')
                                                
                                                {{-- Aksi Edit --}}
                                                <a href="{{ route('master.users.edit', $user->id) }}" class="text-primary font-weight-bold text-xs" data-toggle="tooltip">
                                                    <i class="fas fa-edit me-2"></i> Edit
                                                </a>
                                                
                                                {{-- Aksi Delete --}}
                                                {{-- Cek Ganda: 1. Bukan diri sendiri, DAN 2. Tidak memiliki role 'admin' --}}
                                                @if ($user->id !== Auth::id() && !$user->hasRole('admin')) 
                                                    <form action="{{ route('master.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link text-danger p-0 m-0 ms-3 text-xs" 
                                                                onclick="return confirm('Yakin hapus pengguna {{ $user->name }}?')">
                                                            <i class="fas fa-trash-alt"></i> Hapus
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- Tampilkan pesan jika tidak bisa dihapus --}}
                                                    <span class="text-secondary text-xs ms-3">
                                                        {{ $user->id === Auth::id() ? 'Tidak dapat hapus diri sendiri' : 'Role Admin dilindungi' }}
                                                    </span>
                                                @endif
                                                
                                                @endcan
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada data pengguna yang terdaftar.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination --}}
                            {{-- <div class="p-3">
                                {{ $users->links() }}
                            </div> --}}
                            <div class="p-3 text-center">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12 d-flex justify-content-center">
                                        {{ $users->links('vendor.pagination.soft-ui') }}
                                    </div>
                                </div>
                            </div>
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