@extends('layouts.app') 

@section('page-title', 'List Pengguna Sistem')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        
                        {{-- HEADER BANNER --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-users me-2"></i> Daftar Akun Pengguna E-Rapor</h6>
                                <div class="pe-3">
                                    <a href="{{ route('settings.system.users.create') }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-plus me-1"></i> Tambah Pengguna
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body pb-2 px-4 mt-2">
                            
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- TOGGLE MODE VIEW (Pills) --}}
                            <div class="nav-wrapper position-relative end-0 mb-4">
                                <ul class="nav nav-pills nav-fill p-1 bg-light border" role="tablist" style="border-radius: 0.5rem;">
                                    <li class="nav-item">
                                        <a class="nav-link mb-0 px-0 py-2 {{ $tab == 'admin' ? 'active bg-primary text-white shadow-sm' : 'text-dark' }}" 
                                           href="{{ route('settings.system.users.index', ['tab' => 'admin']) }}">
                                            <i class="fas fa-user-shield me-2"></i> Akun Admin & TU
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link mb-0 px-0 py-2 {{ $tab == 'guru' ? 'active bg-primary text-white shadow-sm' : 'text-dark' }}" 
                                           href="{{ route('settings.system.users.index', ['tab' => 'guru']) }}">
                                            <i class="fas fa-chalkboard-teacher me-2"></i> Akun Guru
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link mb-0 px-0 py-2 {{ $tab == 'siswa' ? 'active bg-primary text-white shadow-sm' : 'text-dark' }}" 
                                           href="{{ route('settings.system.users.index', ['tab' => 'siswa']) }}">
                                            <i class="fas fa-user-graduate me-2"></i> Akun Siswa
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            {{-- Form Filter Dinamis --}}
                            <div class="p-2 border rounded mb-4 bg-light">
                                <form action="{{ route('settings.system.users.index') }}" method="GET" class="mb-0">
                                    <input type="hidden" name="tab" value="{{ $tab }}">
                                    <div class="row g-2 align-items-center">
                                        
                                        {{-- Dropdown Data per Page --}}
                                        <div class="col-md-2">
                                            <select name="per_page" class="form-select form-select-sm px-2 border bg-white" style="height: 40px;" onchange="this.form.submit()">
                                                <option value="10" {{ $perPage == '10' ? 'selected' : '' }}>10 Baris</option>
                                                <option value="25" {{ $perPage == '25' ? 'selected' : '' }}>25 Baris</option>
                                                <option value="50" {{ $perPage == '50' ? 'selected' : '' }}>50 Baris</option>
                                                <option value="100" {{ $perPage == '100' ? 'selected' : '' }}>100 Baris</option>
                                                <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>Semua Data</option>
                                            </select>
                                        </div>

                                        {{-- Pencarian & Filter Kelas --}}
                                        <div class="col-md-{{ $tab == 'siswa' ? '4' : '7' }}">
                                            <input type="text" name="search" class="form-control form-control-sm border px-3 bg-white w-100" style="height: 40px;" placeholder="Cari Nama / Email / Username..." value="{{ $search ?? '' }}">
                                        </div>
                                        
                                        @if($tab == 'siswa')
                                            <div class="col-md-3">
                                                <select name="id_kelas" class="form-select form-select-sm px-2 border bg-white" style="height: 40px;" onchange="this.form.submit()">
                                                    <option value="">-- Semua Kelas --</option>
                                                    @foreach($kelas_list as $k)
                                                        <option value="{{ $k->id_kelas }}" {{ ($id_kelas ?? '') == $k->id_kelas ? 'selected' : '' }}>
                                                            {{ $k->nama_kelas }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        {{-- Tombol Aksi --}}
                                        <div class="col-md-3 d-flex gap-2">
                                            <button type="submit" class="btn btn-sm btn-info mb-0 w-100" style="height: 40px;"><i class="fas fa-search me-1"></i> Cari</button>
                                            
                                            @if(!empty($search) || !empty($id_kelas) || $perPage != 10)
                                                <a href="{{ route('settings.system.users.index', ['tab' => $tab]) }}" class="btn btn-icon btn-sm btn-outline-secondary w-100 mb-0" style="height: 40px; display: flex; align-items: center; justify-content: center;" title="Reset Filter">
                                                    <i class="fas fa-undo me-1"></i> Reset
                                                </a>
                                            @endif
                                        </div>

                                    </div>
                                </form>
                            </div>

                            {{-- TABEL DAFTAR PENGGUNA --}}
                            <div class="table-responsive p-0 border rounded mb-4">
                                <table class="table table-bordered align-items-center mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="5%">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4" width="30%">Pengguna</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Role Saat Ini</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Login</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Terakhir Login</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // Kalkulasi penomoran global (agar nomor lanjut di halaman berikutnya)
                                            // Jika perPage adalah 'all', offsetnya 0. Jika pagination biasa, hitung dari item pertama.
                                            $firstItem = method_exists($users, 'firstItem') ? $users->firstItem() : 1;
                                        @endphp

                                        @forelse ($users as $index => $user)
                                            @php
                                                $isProtected = $user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor', 'guru_ekskul']);
                                                $isSelf = Auth::id() === $user->id;
                                                $nomorUrut = $firstItem ? $firstItem + $index : $index + 1;
                                            @endphp

                                            <tr>
                                                <td class="align-middle text-center text-sm border-end font-weight-bold">
                                                    {{ $nomorUrut }}
                                                </td>
                                                <td class="ps-4 align-middle border-end">
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="avatar avatar-sm me-3 bg-gradient-secondary border-radius-lg shadow-sm d-flex align-items-center justify-content-center">
                                                            <span class="text-white font-weight-bold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $user->name }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center border-end">
                                                    @foreach ($user->roles as $role)
                                                        @php
                                                            $roleName = strtolower($role->name);
                                                            $badgeClass = match(true) {
                                                                $roleName == 'developer' => 'bg-gradient-dark',
                                                                \Illuminate\Support\Str::contains($roleName, 'admin') => 'bg-gradient-danger',
                                                                \Illuminate\Support\Str::contains($roleName, 'guru') => 'bg-gradient-warning',
                                                                default => 'bg-gradient-info',
                                                            };
                                                        @endphp
                                                        <span class="badge badge-sm {{ $badgeClass }}">{{ \Illuminate\Support\Str::title($role->name) }}</span>
                                                    @endforeach
                                                </td>
                                                <td class="align-middle text-center text-sm border-end">
                                                    @if ($user->is_active)
                                                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-secondary">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center border-end">
                                                    <span class="text-secondary text-xs font-weight-bold">
                                                        {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Belum Pernah' }}
                                                    </span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <a href="{{ route('settings.system.users.edit', $user->id) }}" class="btn btn-sm btn-outline-info mb-0 me-1" data-toggle="tooltip" title="Edit User">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    @if ($isSelf)
                                                        <span class="badge bg-light text-dark border">Saya</span>
                                                    @elseif ($isProtected)
                                                        {{-- JIKA SEEDER/PROTECTED: KOSONGKAN SAJA. --}}
                                                    @else
                                                        <form action="{{ route('settings.system.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger mb-0" 
                                                                    onclick="return confirm('PERINGATAN: Yakin hapus pengguna {{ $user->name }}? Data tidak bisa dikembalikan.')" data-toggle="tooltip" title="Hapus User">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <i class="fas fa-users-slash fa-3x mb-3 text-secondary opacity-5"></i>
                                                    <h6 class="text-secondary text-sm">Tidak ada data pengguna yang sesuai pada tab ini.</h6>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination --}}
                            @if(method_exists($users, 'links'))
                                <div class="p-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-sm text-secondary mb-0">
                                            Menampilkan {{ $users->firstItem() ?? 0 }} sampai {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} entri
                                        </p>
                                    </div>
                                    <div>
                                        {{ $users->links('vendor.pagination.bootstrap-5') }}
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection