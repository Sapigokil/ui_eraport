@extends('layouts.app')

@section('page-title', 'Mutasi Siswa Keluar')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <style>
        .alert-warning {
            background-image: linear-gradient(310deg, #fb8c00 0%, #fdb03d 100%) !important;
            color: #fff !important; 
            border: none !important; 
        }
        .bg-gradient-danger {
            background-image: linear-gradient(310deg, #ea0606 0%, #ff667c 100%) !important;
            color: #fff !important;
        }
        .bg-gradient-success {
            background-image: linear-gradient(310deg, #17ad37 0%, #98ec2d 100%) !important;
            color: #fff !important;
        }
    </style>

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- 1. HEADER UTAMA (Gaya Banner) --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-sign-out-alt text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-history me-2"></i> Riwayat Mutasi Keluar
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Daftar rekapitulasi siswa yang telah pindah, mengundurkan diri, atau putus sekolah.
                                    </p>
                                </div>
                                <div>
                                    <a href="{{ route('mutasi.keluar.create') }}" class="btn btn-white text-primary mb-0 me-3 shadow-sm">
                                        <i class="fas fa-plus me-2"></i> Proses Mutasi Baru
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-2 mt-2">
                        
                        {{-- ALERT SYSTEM --}}
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

                        {{-- FILTER & PENCARIAN --}}
                        <div class="bg-gray-50 p-3 border-radius-md border mb-4">
                            <form action="{{ route('mutasi.keluar.index') }}" method="GET" id="filterForm">
                                <div class="row align-items-end">
                                    <div class="col-md-2 mb-3 mb-md-0">
                                        <label class="form-label text-xs font-weight-bold text-dark">Baris / Halaman</label>
                                        <select name="per_page" class="form-control form-select bg-white" onchange="document.getElementById('filterForm').submit();">
                                            <option value="5" {{ request('per_page') == '5' ? 'selected' : '' }}>5 Baris</option>
                                            <option value="10" {{ request('per_page', '10') == '10' ? 'selected' : '' }}>10 Baris</option>
                                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 Baris</option>
                                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 Baris</option>
                                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 Baris</option>
                                            <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua Data</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3 mb-md-0">
                                        <label class="form-label text-xs font-weight-bold text-dark">Dari Tanggal</label>
                                        <input type="date" name="start_date" class="form-control bg-white" value="{{ request('start_date') }}">
                                    </div>
                                    <div class="col-md-3 mb-3 mb-md-0">
                                        <label class="form-label text-xs font-weight-bold text-dark">Sampai Tanggal</label>
                                        <input type="date" name="end_date" class="form-control bg-white" value="{{ request('end_date') }}">
                                    </div>
                                    <div class="col-md-3 mb-3 mb-md-0">
                                        <label class="form-label text-xs font-weight-bold text-dark">Filter Asal Kelas</label>
                                        <select name="id_kelas" class="form-control form-select bg-white">
                                            <option value="">-- Semua Kelas --</option>
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="submit" class="btn btn-dark w-100 mb-0 shadow-sm" title="Terapkan Filter">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- TABEL RIWAYAT MUTASI --}}
                        <div class="table-responsive p-0">
                            <table class="table align-items-center justify-content-center mb-0 border">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="5%">No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="15%">Tgl Mutasi</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="25%">Nama Siswa</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="15%">Asal Kelas</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="30%">Jenis & Alasan</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($riwayat as $index => $r)
                                        <tr>
                                            <td class="text-center text-sm">
                                                {{ ($riwayat instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $riwayat->firstItem() + $index : $loop->iteration }}
                                            </td>
                                            <td class="text-sm">
                                                <span class="font-weight-bold text-dark">
                                                    {{ \Carbon\Carbon::parse($r->tgl_mutasi)->locale('id')->isoFormat('D MMMM YYYY') }}
                                                </span>
                                            </td>
                                            <td>
                                                <h6 class="mb-0 text-sm">{{ $r->siswa->nama_siswa ?? 'Data Siswa Terhapus' }}</h6>
                                                <p class="text-xs text-secondary mb-0">NISN: {{ $r->siswa->nisn ?? '-' }}</p>
                                            </td>
                                            <td class="text-sm">
                                                <span class="badge badge-sm bg-gradient-secondary">
                                                    {{ $r->kelasTerakhir->nama_kelas ?? 'Tanpa Kelas' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm bg-gradient-info mb-1">{{ $r->jenis_mutasi }}</span>
                                                <p class="text-xs text-dark mb-0 text-wrap" style="max-width: 300px;">
                                                    "{{ $r->alasan }}"
                                                    @if($r->sekolah_tujuan)
                                                        <br><strong class="text-secondary">Tujuan:</strong> {{ $r->sekolah_tujuan }}
                                                    @endif
                                                </p>
                                            </td>
                                            <td class="text-center align-middle">
                                                <form action="{{ route('mutasi.keluar.destroy', $r->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Membatalkan mutasi akan mengembalikan status siswa menjadi AKTIF. Lanjutkan?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    
                                                    {{-- Tombol Cetak PDF --}}
                                                    <button type="button" class="btn btn-outline-info btn-sm mb-0 px-2 shadow-sm me-1 btn-cetak" 
                                                            data-id="{{ $r->id }}" 
                                                            title="Cetak Surat Mutasi" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalCetak">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                    
                                                    <a href="{{ route('mutasi.keluar.edit', $r->id) }}" class="btn btn-outline-warning btn-sm mb-0 px-2 shadow-sm me-1" title="Edit Mutasi">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <button type="submit" class="btn btn-outline-danger btn-sm mb-0 px-2 shadow-sm" title="Batalkan Mutasi">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-folder-open fa-3x text-secondary mb-3 opacity-5"></i>
                                                    <h6 class="text-secondary font-weight-normal">Tidak ada data riwayat mutasi yang ditemukan.</h6>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINATION --}}
                        @if($riwayat instanceof \Illuminate\Pagination\LengthAwarePaginator && $riwayat->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-sm text-secondary">
                                    Menampilkan {{ $riwayat->firstItem() }} sampai {{ $riwayat->lastItem() }} dari total {{ $riwayat->total() }} data
                                </div>
                                <div>
                                    {{ $riwayat->appends(request()->query())->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

{{-- MODAL SET TANGGAL CETAK MUTASI --}}
<div class="modal fade" id="modalCetak" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gray-100">
                <h6 class="modal-title font-weight-bolder text-dark">
                    <i class="fas fa-print text-info me-2"></i> Pengaturan Cetak Mutasi
                </h6>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Target _blank akan membuka PDF di tab baru --}}
            <form id="formCetakMutasi" method="POST" target="_blank">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-xs font-weight-bold text-uppercase text-dark">Tanggal Tanda Tangan <span class="text-danger">*</span></label>
                        <div class="input-group input-group-outline is-filled">
                            <input type="date" name="tanggal_ttd" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <small class="text-muted text-xs">Tanggal ini akan muncul di atas nama Kepala Sekolah pada dokumen PDF.</small>
                    </div>
                </div>
                <div class="modal-footer bg-gray-50">
                    <button type="button" class="btn btn-sm btn-white mb-0 shadow-sm border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm bg-gradient-info mb-0 shadow-sm" onclick="setTimeout(function(){ $('#modalCetak').modal('hide'); }, 500);">
                        <i class="fas fa-file-pdf me-1"></i> Proses & Buka PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Logika untuk menyuntikkan ID mutasi ke URL Form Modal saat tombol cetak diklik
        const btnCetaks = document.querySelectorAll('.btn-cetak');
        const formCetak = document.getElementById('formCetakMutasi');
        
        // Base route url yang akan di replace ID-nya
        const baseRoute = "{{ route('mutasi.keluar.cetak', ':id') }}";

        btnCetaks.forEach(btn => {
            btn.addEventListener('click', function() {
                const mutasiId = this.getAttribute('data-id');
                // Ganti parameter :id dengan ID mutasi yang sesungguhnya
                formCetak.action = baseRoute.replace(':id', mutasiId);
            });
        });
    });
</script>
@endsection