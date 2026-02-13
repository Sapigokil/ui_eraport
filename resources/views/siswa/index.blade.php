{{-- File: resources/views/siswa/index.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Daftar Master Data Siswa')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 shadow-xs border">
                        
                        {{-- 1. HEADER UTAMA (Gaya Banner) --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                                {{-- Dekorasi Icon Besar --}}
                                <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                    <i class="fas fa-user-graduate text-white" style="font-size: 8rem;"></i>
                                </div>

                                <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                    <div>
                                        <h6 class="text-white text-capitalize mb-0">
                                            <i class="fas fa-users me-2"></i> Master Data Siswa
                                        </h6>
                                        <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                            Manajemen data induk siswa, import, dan export data
                                        </p>
                                    </div>
                                    
                                    {{-- KELOMPOK TOMBOL AKSI: EXPORT, IMPORT, TAMBAH --}}
                                    <div class="pe-3 d-flex align-items-center">
                                        
                                        {{-- 1. Dropdown Export (Baru Dipindah Kesini) --}}
                                        <div class="dropdown me-2">
                                            <button class="btn btn-outline-white btn-sm mb-0 dropdown-toggle" type="button" id="dropdownExport" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-file-export me-1"></i> Export
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownExport">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('master.siswa.export.pdf') }}">
                                                        <i class="fas fa-file-pdf text-danger me-2"></i> Export ke PDF
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('master.siswa.export.csv') }}">
                                                        <i class="fas fa-file-csv text-success me-2"></i> Export ke CSV
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                        @can('master.view')
                                        {{-- 2. Dropdown Import --}}
                                        <div class="dropdown me-2">
                                            <button class="btn btn-outline-white btn-sm mb-0 dropdown-toggle" type="button" id="dropdownImport" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-file-import me-1"></i> Import
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownImport">
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="document.getElementById('form_import_xlsx').querySelector('input[type=file]').click(); return false;">
                                                        <i class="fas fa-file-excel text-success me-2"></i> Import Excel (.xlsx)
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="document.getElementById('form_import_csv').querySelector('input[type=file]').click(); return false;">
                                                        <i class="fas fa-file-csv text-info me-2"></i> Import CSV (.csv)
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        {{-- 3. Tombol Tambah --}}
                                        <a href="{{ route('master.siswa.create') }}" class="btn btn-white text-primary btn-sm mb-0 shadow-sm">
                                            <i class="fas fa-plus me-1"></i> Baru
                                        </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            {{-- Notifikasi --}}
                            <div class="px-4 mt-2">
                                @if (session('success'))
                                    <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                        <span class="text-sm"><strong>Sukses!</strong> {{ session('success') }}</span>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                        <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                                    </div>
                                @endif
                            </div>

                            {{-- FILTER & SEARCH BAR --}}
                            <div class="p-4 border-bottom bg-gray-50">
                                <form method="GET" action="{{ route('master.siswa.index') }}">
                                    <div class="row align-items-end g-3">
                                        
                                        {{-- BAGIAN KIRI: FILTER GROUP --}}
                                        <div class="col-lg-8 col-md-12">
                                            <div class="row g-3">
                                                {{-- Filter Status --}}
                                                <div class="col-md-4">
                                                    <label class="form-label text-xs font-weight-bold text-uppercase mb-1 text-secondary">Status Siswa</label>
                                                    <div class="input-group input-group-outline bg-white rounded-2">
                                                        <select name="status" class="form-control text-sm px-2" onchange="this.form.submit()">
                                                            <option value="aktif" {{ request('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                            <option value="keluar" {{ request('status') == 'keluar' ? 'selected' : '' }}>Keluar / Pindah</option>
                                                            <option value="lulus" {{ request('status') == 'lulus' ? 'selected' : '' }}>Lulus</option>
                                                            <option value="semua" {{ request('status') == 'semua' ? 'selected' : '' }}>Semua Data</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Filter Kelas --}}
                                                <div class="col-md-5">
                                                    <label class="form-label text-xs font-weight-bold text-uppercase mb-1 text-secondary">Filter Kelas</label>
                                                    <div class="input-group input-group-outline bg-white rounded-2">
                                                        <select name="id_kelas" class="form-control text-sm px-2" onchange="this.form.submit()">
                                                            <option value="all" {{ request('id_kelas', 'all') == 'all' ? 'selected' : '' }}>-- Semua Kelas --</option>
                                                            <option value="no_class" {{ request('id_kelas') == 'no_class' ? 'selected' : '' }}>[ Belum Ada Kelas ]</option>
                                                            
                                                            @foreach($listKelas as $k)
                                                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                                    {{ $k->nama_kelas }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- BAGIAN KANAN: PENCARIAN --}}
                                        <div class="col-lg-4 col-md-12">
                                            <label class="form-label text-xs font-weight-bold text-uppercase mb-1 text-secondary">Cari Data</label>
                                            <div class="input-group input-group-outline bg-white rounded-2 is-filled">
                                                <input type="text" name="search" class="form-control ps-3 text-sm" 
                                                       placeholder="Nama / NISN / NIPD..." 
                                                       value="{{ request('search') }}">
                                                <button class="btn btn-primary mb-0 px-3 z-index-2" type="submit">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>
                            
                            {{-- TABEL DATA SISWA --}}
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0 table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="5%">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Siswa</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Identitas (NISN/NIPD)</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kelas</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                            <th class="text-secondary opacity-7 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($siswas as $i => $siswa)
                                        <tr>
                                            <td class="align-middle text-center text-sm">{{ $loop->iteration + $siswas->firstItem() - 1 }}</td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $siswa->nama_siswa }}</h6>
                                                        <p class="text-xs text-secondary mb-0">
                                                            <i class="fas fa-venus-mars me-1"></i> {{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <p class="text-xs font-weight-bold mb-0 text-dark">{{ $siswa->nisn ?? '-' }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $siswa->nipd ?? '-' }}</p>
                                            </td>
                                            <td class="align-middle">
                                                @if($siswa->kelas)
                                                    <span class="badge badge-sm border border-info text-info bg-transparent">
                                                        {{ $siswa->kelas->nama_kelas }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-secondary fst-italic">Tanpa Kelas</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($siswa->status == 'aktif')
                                                    <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                                @elseif($siswa->status == 'keluar')
                                                    <span class="badge badge-sm bg-gradient-danger">Keluar</span>
                                                @elseif($siswa->status == 'lulus')
                                                    <span class="badge badge-sm bg-gradient-info">Lulus</span>
                                                @else
                                                    <span class="badge badge-sm bg-secondary">{{ $siswa->status }}</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('master.siswa.show', $siswa->id_siswa) }}" class="btn btn-link text-info text-xs mb-0 px-2" title="Lihat Detail">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </a>
                                                
                                                @can('master.view')
                                                <a href="{{ route('master.siswa.edit', $siswa->id_siswa) }}" class="btn btn-link text-primary text-xs mb-0 px-2" title="Edit Data">
                                                    <i class="fas fa-pencil-alt text-sm"></i>
                                                </a>

                                                <form action="{{ route('master.siswa.destroy', $siswa->id_siswa) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger text-xs mb-0 px-2" 
                                                            onclick="return confirm('PERINGATAN: Menghapus data siswa ini akan menghapus permanen semua nilai dan riwayatnya. Lanjutkan?')" title="Hapus Permanen">
                                                        <i class="fas fa-trash-alt text-sm"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <i class="fas fa-user-slash fa-3x text-secondary mb-3 opacity-5"></i>
                                                    <h6 class="text-secondary">Data siswa tidak ditemukan</h6>
                                                    <p class="text-xs text-muted">Coba ubah filter status atau kata kunci pencarian.</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination --}}
                            <div class="px-4 py-3 border-top">
                                {{ $siswas->links('vendor.pagination.soft-ui') }}
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
        
    </main>

    {{-- FORM IMPORT (HIDDEN) --}}
    <form id="form_import_csv" action="{{ route('master.siswa.import.csv') }}" method="POST" enctype="multipart/form-data" class="d-none">
        @csrf
        <input type="file" name="file" accept=".csv, .txt" onchange="if(this.files.length > 0) { showProcessingAlert(); this.form.submit(); }">
    </form>
    
    <form id="form_import_xlsx" action="{{ route('master.siswa.import.xlsx') }}" method="POST" enctype="multipart/form-data" class="d-none">
        @csrf
        <input type="file" name="file" accept=".xlsx, .xls" onchange="if(this.files.length > 0) { showProcessingAlert(); this.form.submit(); }">
    </form>

    {{-- SCRIPT ALERT IMPORT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showProcessingAlert = function() {
                const existingAlert = document.getElementById('processingAlert');
                if (existingAlert) return;

                const alertHtml = `
                    <div class="alert bg-gradient-warning text-white text-center shadow-lg" role="alert" id="processingAlert" style="position: fixed; top: 20%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; padding: 20px; border-radius: 10px; width: 400px;">
                        <i class="fas fa-cog fa-spin fa-2x mb-3"></i>
                        <h5 class="text-white">Sedang Memproses Import...</h5>
                        <p class="text-xs mb-0">Mohon jangan tutup halaman ini sampai proses selesai.</p>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', alertHtml);
            }
        });
    </script>
@endsection