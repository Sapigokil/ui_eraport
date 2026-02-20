@extends('layouts.app') 

@section('page-title', 'Riwayat Progress Akademik Siswa')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- HEADER BANNER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-primary overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-2 pe-3 pt-3">
                        <i class="fas fa-history text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <h3 class="text-white font-weight-bold mb-1">Riwayat Akademik Siswa</h3>
                        <p class="text-white opacity-8 mb-0">
                            <i class="fas fa-search me-1"></i> Pantau progress kenaikan kelas dan kelulusan seluruh siswa.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- AREA FILTER PENCARIAN --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-body p-3">
                <form id="filterForm" action="{{ route('mutasi.riwayat.index') }}" method="GET" class="row g-3 align-items-end">
                    
                    {{-- 1. Input Text (Manual Submit: Harus Enter / Klik Tombol) --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Pencarian Siswa</label>
                        <div class="input-group">
                            <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                            <input type="text" name="search" class="form-control border-secondary ps-2" placeholder="Nama/NISN/NIPD..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- 2. Dropdown Kelas Asal (Auto Submit) --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Kelas Asal</label>
                        <select name="id_kelas_lama" class="form-select border-secondary ps-2" onchange="this.form.submit()">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas_lama') == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. Dropdown Tahun Ajaran (Auto Submit) --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Tahun Ajaran</label>
                        <select name="tahun_ajaran" class="form-select border-secondary ps-2" onchange="this.form.submit()">
                            <option value="">Semua Tahun</option>
                            @foreach($tahunAjaranList as $ta)
                                <option value="{{ $ta }}" {{ request('tahun_ajaran') == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 4. Dropdown Status (Auto Submit) --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Status Mutasi</label>
                        <select name="status" class="form-select border-secondary ps-2" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="naik_kelas" {{ request('status') == 'naik_kelas' ? 'selected' : '' }}>Naik Kelas</option>
                            <option value="tinggal_kelas" {{ request('status') == 'tinggal_kelas' ? 'selected' : '' }}>Tinggal Kelas</option>
                            <option value="lulus" {{ request('status') == 'lulus' ? 'selected' : '' }}>Lulus / Alumni</option>
                        </select>
                    </div>

                    {{-- 5. Tombol Aksi --}}
                    <div class="col-md-2 d-flex gap-2">
                        {{-- Tombol cari hanya memproses inputan text secara manual --}}
                        <button type="submit" class="btn btn-primary w-100 mb-0 shadow-sm">
                            <i class="fas fa-search me-1"></i> Cari
                        </button>
                        {{-- Tombol Reset --}}
                        <a href="{{ route('mutasi.riwayat.index') }}" class="btn btn-light border mb-0" data-bs-toggle="tooltip" title="Reset Semua Filter">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- AREA TABEL DATA --}}
        <div class="card shadow-sm border">
            
            {{-- HEADER BARU (Ada Dropdown Tampilkan 10/25/50/100) --}}
            <div class="card-header bg-light pb-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <h6 class="mb-0 text-dark font-weight-bold">Data Riwayat Progress</h6>
                    
                    {{-- Dropdown Pagination --}}
                    <div class="d-flex align-items-center">
                        <span class="text-xs text-secondary fw-bold me-2">Tampilkan:</span>
                        <select name="per_page" form="filterForm" class="form-select form-select-sm border-secondary text-xs fw-bold cursor-pointer" style="width: 80px;" onchange="document.getElementById('filterForm').submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
                <span class="badge bg-dark shadow-sm">Total: {{ $dataRiwayat->total() }} Data</span>
            </div>
            
            {{-- ISI TABEL --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0 table-hover">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%;">No</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Siswa</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Momen Waktu</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Perpindahan Kelas</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Eksekutor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataRiwayat as $i => $row)
                            <tr class="border-bottom">
                                <td class="align-middle text-center text-sm font-weight-bold text-secondary">
                                    {{ $dataRiwayat->firstItem() + $i }}
                                </td>
                                
                                <td class="align-middle px-3">
                                    <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $row->siswa->nama_siswa ?? 'Siswa Dihapus' }}</h6>
                                    <p class="text-xs text-secondary mb-0">NISN: {{ $row->siswa->nisn ?? '-' }}</p>
                                </td>
                                
                                <td class="align-middle text-center">
                                    <span class="text-xs font-weight-bold text-dark d-block">TA: {{ $row->tahun_ajaran_lama }}</span>
                                    <span class="text-xxs text-secondary">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, H:i') }}</span>
                                </td>
                                
                                <td class="align-middle text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="badge bg-light text-dark border">{{ $row->kelasLama->nama_kelas ?? 'Hapus' }}</span>
                                        <i class="fas fa-long-arrow-alt-right mx-2 text-secondary"></i>
                                        @if($row->status == 'lulus')
                                            <span class="badge bg-dark text-white"><i class="fas fa-graduation-cap"></i> ALUMNI</span>
                                        @else
                                            <span class="badge {{ $row->status == 'tinggal_kelas' ? 'bg-danger text-white' : 'bg-light text-dark border' }}">
                                                {{ $row->kelasBaru->nama_kelas ?? 'Hapus' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                
                                <td class="align-middle text-center">
                                    @if($row->status == 'naik_kelas')
                                        <span class="badge bg-gradient-info"><i class="fas fa-arrow-up me-1"></i> Naik Kelas</span>
                                    @elseif($row->status == 'tinggal_kelas')
                                        <span class="badge bg-gradient-danger"><i class="fas fa-redo me-1"></i> Tinggal Kelas</span>
                                    @elseif($row->status == 'lulus')
                                        <span class="badge bg-gradient-success"><i class="fas fa-medal me-1"></i> Lulus</span>
                                    @else
                                        <span class="badge bg-gradient-secondary">{{ $row->status }}</span>
                                    @endif
                                </td>

                                <td class="align-middle text-center">
                                    <span class="text-xs text-secondary font-weight-bold">{{ $row->user_admin ?? '-' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="alert alert-warning text-dark font-weight-bold shadow-sm m-4 text-center" role="alert">
                                        <i class="fas fa-search-minus me-2"></i> Belum ada data riwayat yang ditemukan atau kriteria pencarian tidak cocok.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- PAGINATION --}}
            @if($dataRiwayat->hasPages())
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-center">
                    {{ $dataRiwayat->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>

    </div>
    <x-app.footer />
</main>
@endsection