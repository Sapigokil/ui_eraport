@extends('layouts.app') 

@section('page-title', 'Manajemen Role & Izin') 

@section('content')
    {{-- START: Pembungkus Main Content --}}
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        {{-- Panggil Navbar agar tampil --}}
        <x-app.navbar />
        
        {{-- Konten Role Index dimulai --}}
        <div class="container-fluid py-4 px-5"> {{-- Tambahkan px-5 --}}
            
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3">Daftar Role Pengguna E-Rapor</h6>
                                {{-- Hanya Admin yang berhak menambah role --}}
                                @can('pengaturan-manage-roles')
                                <a href="{{ route('master.roles.create') }}" class="btn btn-white me-3 mb-0">
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
                                        {{-- Looping Data Role --}}
                                        @forelse ($roles as $role)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ Str::title($role->name) }}</h6>
                                                        <p class="text-xs text-secondary mb-0">Role ini dibuat pada: {{ $role->created_at->format('d/m/Y') }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{-- Menampilkan jumlah permission (Asumsi Spatie) --}}
                                                <p class="text-xs font-weight-bold mb-0">{{ $role->permissions->count() }} Izin</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                {{-- Menampilkan jumlah user dengan role ini (Asumsi relasi users()->count() sudah ada di model Role) --}}
                                                <span class="badge badge-sm bg-gradient-success">{{ $role->users->count() }} User</span>
                                            </td>
                                            <td class="align-middle">
                                                {{-- Aksi Edit Izin --}}
                                                {{-- @can('pengaturan-manage-roles') --}}
                                                <a href="{{ route('master.roles.edit', $role->id) }}" class="text-primary font-weight-bold text-xs" data-toggle="tooltip">
                                                    <i class="fas fa-pencil-alt me-2"></i> Edit Izin
                                                </a>
                                                
                                                {{-- Aksi Delete --}}
                                                {{-- Aksi Delete (Tombol Hapus) --}}
                                                @if (!in_array(strtolower($role->name), ['admin', 'guru', 'wali murid'])) {{-- Lindungi role krusial --}}
                                                    <form action="{{ route('master.roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link text-danger p-0 m-0 ms-3 text-xs" 
                                                                onclick="return confirm('Yakin hapus role {{ Str::title($role->name) }}? Role ini akan hilang permanen.')">
                                                            <i class="fas fa-trash-alt"></i> Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                                {{-- @endcan --}}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center">Belum ada Role yang terdaftar.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination (Asumsi $roles adalah Paginator) --}}
                            <div class="p-3">
                                {{-- $roles->links() --}} {{-- Di-comment karena role biasanya tidak di-paginate --}}
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