@extends('layouts.app') 

@section('page-title', 'List Pengguna Sistem')

@section('content')
    {{-- START: Pembungkus Main Content --}}
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        {{-- Panggil Navbar --}}
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            {{-- Konten Utama Users Index --}}
            
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3">Daftar Akun Pengguna E-Rapor</h6>
                                
                                {{-- Hanya Admin/Developer yang berhak menambah pengguna --}}
                                @can('users.edit')
                                <a href="{{ route('settings.system.users.create') }}" class="btn btn-white me-3 mb-0">
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
                                            {{-- LOGIKA PROTEKSI DI VIEW --}}
                                            @php
                                                // UPDATE PENTING: Sesuaikan nama role dengan yang ada di screenshot Anda
                                                // Tambahkan 'admin_erapor' dan 'guru_erapor' agar terdeteksi
                                                $isProtected = $user->hasAnyRole(['developer', 'admin', 'guru', 'admin_erapor', 'guru_erapor']);
                                                
                                                // Cek apakah ini akun sendiri
                                                $isSelf = Auth::id() === $user->id;
                                            @endphp

                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="avatar avatar-sm me-3 bg-gradient-secondary border-radius-lg shadow-sm">
                                                            <span class="text-white font-weight-bold">{{ substr($user->name, 0, 1) }}</span>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @foreach ($user->roles as $role)
                                                        @php
                                                            // Update logika warna badge agar sesuai dengan nama role di database Anda
                                                            $roleName = strtolower($role->name);
                                                            $badgeClass = match(true) {
                                                                $roleName == 'developer' => 'bg-gradient-dark',
                                                                Str::contains($roleName, 'admin') => 'bg-gradient-danger', // Menangkap admin & admin_erapor
                                                                Str::contains($roleName, 'guru') => 'bg-gradient-warning', // Menangkap guru & guru_erapor
                                                                default => 'bg-gradient-info',
                                                            };
                                                        @endphp
                                                        <span class="badge badge-sm {{ $badgeClass }}">{{ Str::title($role->name) }}</span>
                                                    @endforeach
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    @if ($user->is_active)
                                                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-secondary">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold">
                                                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum Pernah' }}
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    @can('users.update')
                                                        {{-- Tombol Edit (Selalu Muncul) --}}
                                                        <a href="{{ route('settings.system.users.edit', $user->id) }}" class="text-primary font-weight-bold text-xs me-3" data-toggle="tooltip" title="Edit User">
                                                            <i class="fas fa-edit me-1"></i> Edit
                                                        </a>
                                                        
                                                        {{-- LOGIKA TOMBOL HAPUS --}}
                                                        @if ($isSelf)
                                                            {{-- Jika akun sendiri --}}
                                                            <span class="text-secondary text-xs font-weight-bold text-dark">
                                                                <i class="fas fa-user mb-0"></i> Saya
                                                            </span>
                                                        @elseif ($isProtected)
                                                            {{-- 
                                                            JIKA SEEDER/PROTECTED: KOSONGKAN SAJA.
                                                            User meminta tombol hapus dihilangkan, bukan dikunci.
                                                            --}}
                                                        @else
                                                            {{-- Jika User Biasa: Tampilkan Tombol Hapus --}}
                                                            <form action="{{ route('settings.system.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-link text-danger p-0 m-0 text-xs" 
                                                                        onclick="return confirm('PERINGATAN: Yakin hapus pengguna {{ $user->name }}? Data tidak bisa dikembalikan.')">
                                                                    <i class="fas fa-trash-alt me-1"></i> Hapus
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <h6 class="text-secondary text-sm">Belum ada data pengguna yang terdaftar.</h6>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination --}}
                            <div class="p-3 d-flex justify-content-center">
                                {{ $users->links('vendor.pagination.soft-ui') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Panggil Footer --}}
            <x-app.footer />
        </div>
    </main>
    {{-- END: Pembungkus Main Content --}}
@endsection