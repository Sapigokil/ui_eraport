@extends('layouts.app') 

@section('page-title', 'Daftar Master Data Guru')

@section('content')
    {{-- START: Pembungkus Main Content --}}
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        {{-- Panggil Navbar --}}
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 shadow-xs border">
                        
                        {{-- KONTROL ATAS: HEADER DENGAN TAMBAH & IMPORT/EXPORT --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3">
                                    <i class="fas fa-chalkboard-teacher me-2"></i> Daftar Guru
                                </h6>
                                
                                {{-- KELOMPOK TOMBOL AKSI: TAMBAH, IMPORT, EXPORT --}}
                                <div class="d-flex me-3">
                                    @can('master.view')
                                    
                                    {{-- Dropdown untuk Pilihan Import (Mengganti tombol CSV lama) --}}
                                    <div class="dropdown me-2">
                                        <button class="btn btn-white btn-sm mb-0 dropdown-toggle" type="button" id="dropdownImportGuru" data-bs-toggle="dropdown" aria-expanded="false" title="Pilih Format Import">
                                            <i class="fas fa-file-import me-1"></i> Import Data
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownImportGuru">
                                            <li>
                                                {{-- Opsi 1: Import Excel (.xlsx) --}}
                                                <a class="dropdown-item" href="#" onclick="document.getElementById('form_import_guru_xlsx').querySelector('input[type=file]').click(); return false;">
                                                    Import Excel (.xlsx/.xls)
                                                </a>
                                            </li>
                                            <li>
                                                {{-- Opsi 2: Import CSV (Menggunakan form lama) --}}
                                                <a class="dropdown-item" href="#" onclick="document.getElementById('form_import_guru_csv').querySelector('input[type=file]').click(); return false;">
                                                    Import CSV (.csv)
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    {{-- Tombol Tambah Guru --}}
                                    <a href="{{ route('master.guru.create') }}" class="btn btn-white btn-sm mb-0">
                                        <i class="fas fa-plus me-1"></i> Tambah Guru
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            {{-- Notifikasi Sukses --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success mx-4 alert-dismissible text-white" role="alert">
                                    <span class="text-sm">{{ session('success') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            
                            {{-- Notifikasi Error --}}
                            @if (session('error'))
                                <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white" role="alert">
                                    <span class="text-sm">{{ session('error') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            {{-- KONTROL ATAS: FILTERS DAN EXPORT/PENCARIAN --}}
                            <div class="row px-4 mb-3 d-flex align-items-center">
                                
                                {{-- KIRI: Tombol Export --}}
                                <div class="col-md-6 col-sm-12 d-flex align-items-center mb-3 mb-md-0">
                                    <span class="text-sm text-secondary me-3">Export:</span>
                                    <a href="{{ route('master.guru.export.pdf') }}" class="btn btn-sm btn-outline-danger me-2 mb-0" title="Export Data ke PDF">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                    
                                    <a href="{{ route('master.guru.export.csv') }}" class="btn btn-sm btn-outline-success mb-0" title="Export Data ke CSV">
                                        <i class="fas fa-file-csv"></i> CSV
                                    </a>
                                </div>
                                
                                {{-- KANAN: Form Pencarian --}}
                                <div class="col-md-6 col-sm-12">
                                    <form method="GET" action="{{ route('master.guru.index') }}" class="float-end d-flex align-items-center">
                                        <label class="form-label mb-0 me-2 text-sm text-secondary" for="search_input">Cari Guru:</label>
                                        <input type="text" id="search_input" name="search" class="form-control me-2" 
                                            placeholder="Nama/NIP/NUPTK" 
                                            style="width: 200px; height: 35px; border: 1px solid #dee2e6; padding: 0.5rem;"
                                            value="{{ request('search') }}">
                                        <button type="submit" class="btn btn-sm btn-primary mb-0">Cari</button>
                                    </form>
                                </div>
                                
                            </div>
                            
                            {{-- Tabel Data Guru --}}
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Guru</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NIP / NUPTK</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Jenis PTK</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                            <th class="text-secondary opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($gurus as $i => $guru)
                                        <tr>
                                            <td class="align-middle text-center text-sm">{{ $loop->iteration + $gurus->firstItem() - 1 }}</td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $guru->nama_guru }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $guru->jenis_kelamin }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="ps-2">
                                                <p class="text-xs font-weight-bold mb-0">NIP: {{ $guru->nip ?? '-' }}</p>
                                                <p class="text-xs text-secondary mb-0">NUPTK: {{ $guru->nuptk ?? '-' }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $guru->jenis_ptk }}</p>
                                                <span class="text-xs text-muted">{{ Str::title($guru->role) }}</span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @if ($guru->status == 'aktif')
                                                    <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">Non-Aktif</span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                
                                                <a href="{{ route('master.guru.show', $guru->id_guru) }}" class="text-info font-weight-bold text-xs me-2" data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="fas fa-eye me-1"></i> Lihat
                                                </a>
                                                
                                                @can('master.view')
                                                <a href="{{ route('master.guru.edit', $guru->id_guru) }}" class="text-primary font-weight-bold text-xs me-2" data-bs-toggle="tooltip" title="Edit Data">
                                                    <i class="fas fa-pencil-alt me-1"></i> Edit
                                                </a>

                                                <form action="{{ route('master.guru.destroy', $guru->id_guru) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 m-0 text-xs" 
                                                            onclick="return confirm('Yakin hapus data {{ $guru->nama_guru }}? Ini akan menghapus semua detail dan pembelajarannya.')" title="Hapus Data">
                                                        <i class="fas fa-trash-alt me-1"></i> Hapus
                                                    </button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">Data guru tidak ditemukan.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination --}}
                            <div class="p-3 text-center">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12 d-flex justify-content-center">
                                        {{ $gurus->links('vendor.pagination.soft-ui') }}
                                    </div>
                                </div>
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

    {{-- FORM TERSEMBUNYI UNTUK UPLOAD CSV (Menggunakan form lama yang diakses via dropdown) --}}
    <form id="form_import_guru_csv" action="{{ route('master.guru.import') }}" method="POST" enctype="multipart/form-data" class="d-none">
        @csrf
        <input type="file" name="file" accept=".csv, .txt" onchange="if(this.files.length > 0) { showProcessingAlert(); this.form.submit(); }">
    </form>

    {{-- FORM TERSEMBUNYI UNTUK UPLOAD EXCEL GURU (Menuju route baru) --}}
    <form id="form_import_guru_xlsx" action="{{ route('master.guru.import.xlsx') }}" method="POST" enctype="multipart/form-data" class="d-none">
        @csrf
        <input type="file" name="file" accept=".xlsx, .xls" onchange="if(this.files.length > 0) { showProcessingAlert(); this.form.submit(); }">
    </form>
    
    {{-- ========================================================== --}}
    {{-- SCRIPT JAVASCRIPT UNTUK POPUP PROGRESS --}}
    {{-- ========================================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Fungsi Pop-up Peringatan Progress
            window.showProcessingAlert = function() {
                const existingAlert = document.getElementById('processingAlert');
                if (existingAlert) return;

                const alertHtml = `
                    <div class="alert bg-gradient-warning text-white text-center shadow-lg" role="alert" id="processingAlert" style="position: fixed; top: 20%; left: 50%; transform: translate(-50%, -50%); z-index: 1050; padding: 20px; border-radius: 10px;">
                        <h4 class="alert-heading text-white">PROSES IMPORT SEDANG BERJALAN</h4>
                        <p>Mohon tunggu. Proses ini mungkin memakan waktu beberapa saat. **Jangan tutup atau refresh halaman browser ini!**</p>
                        <div class="spinner-border text-white" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', alertHtml);
            }
            
            // Fungsi Menghilangkan Pop-up Peringatan (tidak dipanggil karena page refresh)
            window.hideProcessingAlert = function() {
                const alert = document.getElementById('processingAlert');
                if (alert) {
                    alert.remove();
                }
            }
        });
    </script>
@endsection