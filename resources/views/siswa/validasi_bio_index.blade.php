@extends('layouts.app') 

@section('page-title', 'Antrean Validasi Biodata')

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
                                <i class="fas fa-tasks text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-clipboard-check me-2"></i> Validasi Perubahan Biodata
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Antrean persetujuan pembaruan data diri yang diajukan oleh siswa
                                    </p>
                                </div>
                                
                                <div class="pe-3 d-flex align-items-center">
                                    <span class="badge bg-white text-dark shadow-sm">
                                        <i class="fas fa-clock text-warning me-1"></i> {{ \App\Models\PengajuanBiodata::where('status', 'pending')->count() }} Menunggu
                                    </span>
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
                            <form method="GET" action="{{ route('master.validasi_bio.index') }}">
                                <div class="row align-items-end g-3">
                                    
                                    {{-- BAGIAN KIRI: FILTER GROUP --}}
                                    <div class="col-lg-8 col-md-12">
                                        <div class="row g-3">
                                            {{-- Filter Tampilkan Baris --}}
                                            <div class="col-md-3">
                                                <label class="form-label text-xs font-weight-bold text-uppercase mb-1 text-secondary">Tampilkan Baris</label>
                                                <div class="input-group input-group-outline bg-white rounded-2">
                                                    <select name="per_page" class="form-control text-sm px-2" onchange="this.form.submit()">
                                                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 Baris</option>
                                                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 Baris</option>
                                                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 Baris</option>
                                                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 Baris</option>
                                                        <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>Semua Data</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Filter Status Pengajuan --}}
                                            <div class="col-md-4">
                                                <label class="form-label text-xs font-weight-bold text-uppercase mb-1 text-secondary">Status Pengajuan</label>
                                                <div class="input-group input-group-outline bg-white rounded-2">
                                                    <select name="status" class="form-control text-sm px-2" onchange="this.form.submit()">
                                                        <option value="pending" {{ $statusFilter == 'pending' ? 'selected' : '' }}>Sedang Pending</option>
                                                        <option value="disetujui" {{ $statusFilter == 'disetujui' ? 'selected' : '' }}>Telah Disetujui</option>
                                                        <option value="ditolak" {{ $statusFilter == 'ditolak' ? 'selected' : '' }}>Telah Ditolak</option>
                                                        <option value="semua" {{ $statusFilter == 'semua' ? 'selected' : '' }}>Semua Status</option>
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
                                                   placeholder="Cari Nama Siswa atau NISN..." 
                                                   value="{{ $search }}">
                                            <button class="btn btn-primary mb-0 px-3 z-index-2" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>
                        
                        {{-- TABEL ANTREAN PENGAJUAN --}}
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="5%">No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Siswa Pemohon</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Waktu Pengajuan</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Item Berubah</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-secondary opacity-7 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pengajuans as $i => $req)
                                    <tr>
                                        <td class="align-middle text-center text-sm">{{ $loop->iteration + $pengajuans->firstItem() - 1 }}</td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $req->siswa->nama_siswa ?? 'Siswa Terhapus' }}</h6>
                                                    <p class="text-xs text-secondary mb-0">
                                                        <i class="fas fa-id-card me-1"></i> {{ $req->siswa->nisn ?? '-' }} | Kelas: {{ $req->siswa->kelas->nama_kelas ?? '-' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <p class="text-xs font-weight-bold mb-0 text-dark">{{ \Carbon\Carbon::parse($req->created_at)->format('d M Y') }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ \Carbon\Carbon::parse($req->created_at)->format('H:i') }} WIB</p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge bg-light text-dark border px-3 py-1">
                                                {{ is_array($req->data_perubahan) ? count($req->data_perubahan) : 0 }} Kolom
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            @if($req->status == 'pending')
                                                <span class="badge badge-sm bg-gradient-warning">Pending Review</span>
                                            @elseif($req->status == 'disetujui')
                                                <span class="badge badge-sm bg-gradient-success">Disetujui</span>
                                            @elseif($req->status == 'ditolak')
                                                <span class="badge badge-sm bg-gradient-danger">Ditolak</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            @if($req->status == 'pending')
                                                <a href="{{ route('master.validasi_bio.show', $req->id_pengajuan) }}" class="btn btn-sm bg-gradient-info mb-0 px-3 shadow-sm">
                                                    <i class="fas fa-search me-1"></i> Review
                                                </a>
                                            @else
                                                {{-- Jika sudah diproses, tombol dinonaktifkan --}}
                                                <button class="btn btn-sm btn-secondary mb-0 px-3 opacity-6" disabled>
                                                    <i class="fas fa-check-double me-1"></i> Selesai
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="fas fa-clipboard-check fa-3x text-secondary mb-3 opacity-5"></i>
                                                <h6 class="text-secondary">Tidak ada pengajuan biodata</h6>
                                                <p class="text-xs text-muted">Belum ada siswa yang mengajukan pembaruan data pada status ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Pagination Dinamis --}}
                        <div class="px-4 py-3 border-top">
                            {{ $pengajuans->links('vendor.pagination.soft-ui') }}
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
        <x-app.footer />
    </div>
    
</main>
@endsection