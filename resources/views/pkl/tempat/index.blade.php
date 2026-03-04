@extends('layouts.app') 

@section('page-title', 'Daftar Tempat PKL')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 shadow-xs border">
                        
                        {{-- KONTROL ATAS: HEADER DENGAN TAMBAH & IMPORT/EXPORT --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas fa-building me-2"></i> Daftar Tempat PKL
                                </h6>
                                
                                <div class="d-flex me-3">
                                    @can('master.view')
                                    
                                    {{-- Tombol Export Excel --}}
                                    <a href="{{ route('pkl.tempat.export.excel') }}" class="btn btn-success btn-sm mb-0 me-2" title="Export ke Excel">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </a>

                                    {{-- Tombol Trigger Modal Import --}}
                                    <button type="button" class="btn btn-info btn-sm mb-0 me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="fas fa-file-import me-1"></i> Import Excel
                                    </button>
                                    
                                    {{-- Tombol Tambah Tempat PKL --}}
                                    <a href="{{ route('pkl.tempat.create') }}" class="btn btn-white btn-sm mb-0">
                                        <i class="fas fa-plus me-1"></i> Tambah Tempat
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success mx-4 alert-dismissible text-white" role="alert">
                                    <span class="text-sm">{{ session('success') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white" role="alert">
                                    <span class="text-sm">{{ session('error') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            {{-- KONTROL FILTER & PENCARIAN (Auto Submit on Change) --}}
                            <div class="px-4 py-3 bg-light border-bottom border-top mx-4 mt-3 rounded-3">
                                <form method="GET" action="{{ route('pkl.tempat.index') }}" id="filterForm">
                                    <div class="row align-items-end">
                                        
                                        {{-- 1. Pagination Limit --}}
                                        <div class="col-md-2 col-sm-6 mb-3 mb-md-0">
                                            <label class="form-label text-xs font-weight-bolder mb-1">Tampilkan</label>
                                            <select name="per_page" class="form-select form-control-sm border px-2" onchange="document.getElementById('filterForm').submit()">
                                                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 Baris</option>
                                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 Baris</option>
                                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 Baris</option>
                                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 Baris</option>
                                                <option value="all" {{ $perPage === 'all' ? 'selected' : '' }}>Semua Data</option>
                                            </select>
                                        </div>

                                        {{-- 2. Filter Nama Perusahaan --}}
                                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                                            <label class="form-label text-xs font-weight-bolder mb-1">Cari Perusahaan</label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="search_nama" class="form-control border px-2" placeholder="Ketik nama..." value="{{ request('search_nama') }}">
                                            </div>
                                        </div>

                                        {{-- 3. Filter Bidang Usaha --}}
                                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                                            <label class="form-label text-xs font-weight-bolder mb-1">Bidang Usaha</label>
                                            <select name="search_bidang" class="form-select form-control-sm border px-2" onchange="document.getElementById('filterForm').submit()">
                                                <option value="">-- Semua Bidang --</option>
                                                @foreach($listBidangUsaha as $bidang)
                                                    <option value="{{ $bidang }}" {{ request('search_bidang') == $bidang ? 'selected' : '' }}>{{ $bidang }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- 4. Filter Status Aktif --}}
                                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                                            <label class="form-label text-xs font-weight-bolder mb-1">Status</label>
                                            <select name="search_status" class="form-select form-control-sm border px-2" onchange="document.getElementById('filterForm').submit()">
                                                <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif Menerima</option>
                                                <option value="0" {{ $status === '0' ? 'selected' : '' }}>Non-Aktif</option>
                                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tampilkan Semua</option>
                                            </select>
                                        </div>

                                        {{-- Tombol Cari Manual (jika teks input diisi) --}}
                                        <div class="col-md-1 col-sm-12 text-md-end">
                                            <button type="submit" class="btn btn-sm btn-primary w-100 mb-0">Cari</button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                            
                            {{-- Tabel Data Tempat PKL --}}
                            <div class="table-responsive p-0 mt-3">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Perusahaan</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Bidang Usaha</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Alamat & Kontak</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Instruktur</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                            <th class="text-secondary opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($tempatPkl as $i => $tempat)
                                        <tr>
                                            {{-- Penomoran dinamis menyesuaikan tipe collection (paginate vs get) --}}
                                            <td class="align-middle text-center text-sm">
                                                {{ $perPage === 'all' ? $loop->iteration : ($tempatPkl->currentPage() - 1) * $tempatPkl->perPage() + $loop->iteration }}
                                            </td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $tempat->nama_perusahaan }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="ps-2">
                                                <span class="badge badge-sm bg-gradient-info">{{ $tempat->bidang_usaha ?? 'Umum' }}</span>
                                            </td>
                                            <td class="ps-2">
                                                <p class="text-xs font-weight-bold mb-0 text-truncate" style="max-width: 200px;" title="{{ $tempat->alamat_perusahaan }}">
                                                    {{ $tempat->alamat_perusahaan }}
                                                </p>
                                                <p class="text-xs text-secondary mb-0">
                                                    {{ $tempat->kota ? $tempat->kota . ' | ' : '' }} {{ $tempat->no_telp_perusahaan ?? '-' }}
                                                </p>
                                            </td>
                                            <td class="ps-2">
                                                <p class="text-sm font-weight-bold mb-0">{{ $tempat->nama_instruktur }}</p>
                                                <span class="text-xs text-muted">{{ $tempat->no_telp_instruktur ?? '-' }}</span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @if ($tempat->is_active)
                                                    <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">Non-Aktif</span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @can('master.view')
                                                <a href="{{ route('pkl.tempat.edit', $tempat->id) }}" class="text-primary font-weight-bold text-xs me-2" data-bs-toggle="tooltip" title="Edit Data">
                                                    <i class="fas fa-pencil-alt me-1"></i> Edit
                                                </a>

                                                <form action="{{ route('pkl.tempat.destroy', $tempat->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 m-0 text-xs" 
                                                            onclick="return confirm('Yakin hapus data perusahaan {{ $tempat->nama_perusahaan }}?')" title="Hapus Data">
                                                        <i class="fas fa-trash-alt me-1"></i> Hapus
                                                    </button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-sm text-secondary">Data tempat PKL belum tersedia / tidak ditemukan.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination Area (Hanya muncul jika bukan opsi 'Semua Data') --}}
                            @if($perPage !== 'all' && $tempatPkl->hasPages())
                                <div class="p-3 text-center border-top">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12 d-flex justify-content-center">
                                            {{-- appends(request()->query()) memastikan saat pindah halaman, filter tetap terbawa --}}
                                            {{ $tempatPkl->appends(request()->query())->links('vendor.pagination.soft-ui') }}
                                        </div>
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

    {{-- ========================================================== --}}
    {{-- MODAL IMPORT EXCEL --}}
    {{-- ========================================================== --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white" id="importModalLabel"><i class="fas fa-file-excel me-2"></i> Import Data Tempat PKL</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <form action="{{ route('pkl.tempat.import.excel') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        
                        {{-- Tombol Unduh Template --}}
                        <div class="alert alert-secondary text-dark text-sm border-0 mb-4" role="alert">
                            <i class="fas fa-info-circle me-1"></i> Pastikan format file sesuai dengan template standar.
                            <div class="mt-2 text-center">
                                <a href="{{ route('pkl.tempat.template') }}" class="btn btn-dark btn-sm mb-0">
                                    <i class="fas fa-download me-1"></i> Unduh Template Excel
                                </a>
                            </div>
                        </div>

                        {{-- Input File --}}
                        <div class="mb-3">
                            <label for="file_import" class="form-label font-weight-bold">Pilih File Excel <span class="text-danger">*</span></label>
                            <input class="form-control border px-2 py-1" type="file" id="file_import" name="file_import" accept=".xlsx, .xls, .csv" required>
                            <small class="text-muted text-xs">Hanya menerima file berformat .xlsx, .xls, atau .csv</small>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary mb-0" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn bg-gradient-primary mb-0" onclick="if(document.getElementById('file_import').value) { showProcessingAlert(); }">
                            <i class="fas fa-upload me-1"></i> Upload & Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPT JAVASCRIPT UNTUK POPUP PROGRESS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showProcessingAlert = function() {
                const existingAlert = document.getElementById('processingAlert');
                if (existingAlert) return;

                const alertHtml = `
                    <div class="alert bg-gradient-warning text-white text-center shadow-lg" role="alert" id="processingAlert" style="position: fixed; top: 20%; left: 50%; transform: translate(-50%, -50%); z-index: 1050; padding: 20px; border-radius: 10px;">
                        <h4 class="alert-heading text-white">PROSES UPLOAD SEDANG BERJALAN</h4>
                        <p>Mohon tunggu. Proses ini mungkin memakan waktu beberapa saat. **Jangan tutup atau refresh halaman browser ini!**</p>
                        <div class="spinner-border text-white" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', alertHtml);
            }
        });
    </script>
@endsection