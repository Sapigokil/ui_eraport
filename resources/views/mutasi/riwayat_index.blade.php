@extends('layouts.app') 

@section('page-title', 'Riwayat Progress Akademik Siswa')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg pt-4">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- HEADER BANNER SESUAI TEMPLATE ANDA --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-primary overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-2 pe-3 pt-3">
                        <i class="fas fa-history text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <h3 class="text-white font-weight-bold mb-1">Riwayat Akademik Siswa</h3>
                        <p class="text-white opacity-8 mb-0">
                            <i class="fas fa-search me-1"></i> Pantau progress mutasi kenaikan, kelulusan, dan mutasi keluar per kelas.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- AREA FILTER PENCARIAN --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <form id="filterForm" action="{{ route('mutasi.riwayat.index') }}" method="GET" class="row g-3 align-items-end">
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Pencarian Siswa</label>
                        <div class="input-group">
                            <span class="input-group-text text-body bg-white"><i class="fas fa-search" aria-hidden="true"></i></span>
                            <input type="text" name="search" class="form-control ps-2" placeholder="Nama/NISN/NIPD..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- 👇 PERUBAHAN: Dari Kelas Asal menjadi Tingkat Kelas 👇 --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Tingkat Kelas</label>
                        <select name="tingkat" class="form-select ps-2" onchange="this.form.submit()">
                            <option value="">Semua Tingkat</option>
                            @foreach($tingkatList as $t)
                                <option value="{{ $t }}" {{ request('tingkat') == $t ? 'selected' : '' }}>
                                    Tingkat {{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Tahun Ajaran</label>
                        <select name="tahun_ajaran" class="form-select ps-2" onchange="this.form.submit()">
                            @foreach($tahunAjaranList as $item_ta)
                                <option value="{{ $item_ta }}" {{ $ta == $item_ta ? 'selected' : '' }}>{{ $item_ta }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Status Mutasi</label>
                        <select name="status" class="form-select ps-2" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="naik_kelas" {{ request('status') == 'naik_kelas' ? 'selected' : '' }}>Naik Kelas</option>
                            <option value="tinggal_kelas" {{ request('status') == 'tinggal_kelas' ? 'selected' : '' }}>Tinggal Kelas</option>
                            <option value="lulus" {{ request('status') == 'lulus' ? 'selected' : '' }}>Lulus / Alumni</option>
                            <option value="tidak_lulus" {{ request('status') == 'tidak_lulus' ? 'selected' : '' }}>Tidak Lulus</option>
                            <option value="mutasi_keluar" {{ request('status') == 'mutasi_keluar' ? 'selected' : '' }}>Mutasi Keluar</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 mb-0 shadow-sm">
                            Cari
                        </button>
                        <a href="{{ route('mutasi.riwayat.index') }}" class="btn btn-light border mb-0" data-bs-toggle="tooltip" title="Reset Semua Filter">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- AREA TABEL UTAMA (MINIMALIS & CLEAN) --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white pb-0 pt-4 border-bottom-0">
                <h6 class="mb-0 text-dark font-weight-bold">Daftar Kelas (Tahun Ajaran: {{ $ta }})</h6>
            </div>
            
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center justify-content-center mb-0 table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 5%;">No</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Nama Kelas</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 15%;">Jumlah Siswa</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Result Mutasi</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($groupedRiwayat as $index => $group)
                                
                                @php
                                    $isKelulusan = ($group['lulus'] > 0 || $group['tidak_lulus'] > 0);
                                @endphp

                                <tr class="border-bottom">
                                    <td class="text-center text-sm text-secondary font-weight-bold">
                                        {{ $loop->iteration }}
                                    </td>
                                    
                                    {{-- Kolom Nama Kelas --}}
                                    <td class="ps-4">
                                        <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $group['nama_kelas'] }}</h6>
                                    </td>
                                    
                                    {{-- Kolom Jumlah Siswa --}}
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $group['total_siswa'] }} Siswa</span>
                                    </td>
                                    
                                    {{-- Kolom Result Cerdas --}}
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                            @if($isKelulusan)
                                                <span class="badge bg-gradient-success text-white px-2 py-1 shadow-sm">
                                                    <i class="fas fa-check-circle me-1"></i> Lulus: {{ $group['lulus'] }}
                                                </span>
                                                @if($group['tidak_lulus'] > 0)
                                                    <span class="badge bg-gradient-danger text-white px-2 py-1 shadow-sm">
                                                        <i class="fas fa-times-circle me-1"></i> Tdk Lulus: {{ $group['tidak_lulus'] }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-gradient-info text-white px-2 py-1 shadow-sm">
                                                    <i class="fas fa-arrow-up me-1"></i> Naik: {{ $group['naik'] }}
                                                </span>
                                                @if($group['tinggal'] > 0)
                                                    <span class="badge bg-gradient-danger text-white px-2 py-1 shadow-sm">
                                                        <i class="fas fa-redo me-1"></i> Tinggal: {{ $group['tinggal'] }}
                                                    </span>
                                                @endif
                                            @endif

                                            @if($group['mutasi_keluar'] > 0)
                                                <span class="badge bg-gradient-warning text-white px-2 py-1 shadow-sm">
                                                    <i class="fas fa-sign-out-alt me-1"></i> Keluar: {{ $group['mutasi_keluar'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    {{-- Kolom Aksi --}}
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-0" data-bs-toggle="modal" data-bs-target="#modalDetail-{{ $index }}">
                                            <i class="fas fa-list me-1"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="text-center py-5">
                                            <i class="fas fa-box-open fa-3x text-secondary opacity-5 mb-3 d-block"></i>
                                            <h6 class="text-dark font-weight-bold mb-1">Belum Ada Data Riwayat</h6>
                                            <p class="text-secondary text-sm mb-0">Sistem tidak menemukan data untuk Tahun Ajaran atau kriteria pencarian ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 👇 AREA MODAL DIPISAHKAN KE LUAR TABEL AGAR HTML TIDAK BERANTAKAN 👇 --}}
    {{-- ========================================================================= --}}
    @foreach($groupedRiwayat as $index => $group)
        <div class="modal fade" id="modalDetail-{{ $index }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="modal-title font-weight-bolder text-dark mb-1">
                                <i class="fas fa-users text-primary me-2"></i> Rincian Siswa: {{ $group['nama_kelas'] }}
                            </h5>
                            <span class="text-xs text-secondary">Tahun Ajaran: {{ $ta }} | Total: {{ $group['total_siswa'] }} Siswa diproses</span>
                        </div>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-0">
                        {{-- Rekap Cepat di Dalam Modal --}}
                        <div class="bg-white border-bottom p-3">
                            <div class="d-flex gap-3 justify-content-center flex-wrap">
                                @if($group['lulus'] > 0 || $group['tidak_lulus'] > 0)
                                    <span class="badge bg-gradient-success text-white shadow-sm"><i class="fas fa-check-circle me-1"></i> Lulus: {{ $group['lulus'] }}</span>
                                    @if($group['tidak_lulus'] > 0)
                                        <span class="badge bg-gradient-danger text-white shadow-sm"><i class="fas fa-times-circle me-1"></i> Tdk Lulus: {{ $group['tidak_lulus'] }}</span>
                                    @endif
                                @endif
                                
                                @if($group['naik'] > 0 || $group['tinggal'] > 0)
                                    <span class="badge bg-gradient-info text-white shadow-sm"><i class="fas fa-arrow-up me-1"></i> Naik Kelas: {{ $group['naik'] }}</span>
                                    @if($group['tinggal'] > 0)
                                        <span class="badge bg-gradient-danger text-white shadow-sm"><i class="fas fa-redo me-1"></i> Tinggal Kelas: {{ $group['tinggal'] }}</span>
                                    @endif
                                @endif

                                @if($group['mutasi_keluar'] > 0)
                                    <span class="badge bg-gradient-warning text-white shadow-sm"><i class="fas fa-sign-out-alt me-1"></i> Mutasi Keluar: {{ $group['mutasi_keluar'] }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-items-center mb-0 table-hover table-striped">
                                <thead class="bg-light position-sticky top-0 shadow-sm" style="z-index: 1;">
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%;">No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Identitas Siswa</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu Proses</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mutasi Tujuan</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Akhir</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Eksekutor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group['detail_siswa'] as $i => $row)
                                    <tr>
                                        <td class="align-middle text-center text-sm font-weight-bold text-secondary">
                                            {{ $loop->iteration }}
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
                                                <span class="badge bg-white text-dark border">{{ $row->kelasLama->nama_kelas ?? 'Hapus' }}</span>
                                                <i class="fas fa-long-arrow-alt-right mx-2 text-secondary"></i>
                                                
                                                @if($row->status == 'lulus')
                                                    <span class="badge bg-dark text-white"><i class="fas fa-graduation-cap"></i> ALUMNI</span>
                                                @elseif($row->status == 'tidak_lulus')
                                                    <span class="badge bg-danger text-white"><i class="fas fa-times-circle"></i> TETAP</span>
                                                @elseif($row->status == 'mutasi_keluar')
                                                    <span class="badge bg-warning text-white"><i class="fas fa-sign-out-alt"></i> KELUAR</span>
                                                @else
                                                    <span class="badge {{ $row->status == 'tinggal_kelas' ? 'bg-danger text-white' : 'bg-white text-dark border' }}">
                                                        {{ $row->kelasBaru->nama_kelas ?? 'Hapus' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        
                                        <td class="align-middle text-center">
                                            @if($row->status == 'naik_kelas')
                                                <span class="badge bg-gradient-info text-white"><i class="fas fa-arrow-up me-1"></i> Naik Kelas</span>
                                            @elseif($row->status == 'tinggal_kelas')
                                                <span class="badge bg-gradient-danger text-white"><i class="fas fa-redo me-1"></i> Tinggal Kelas</span>
                                            @elseif($row->status == 'lulus')
                                                <span class="badge bg-gradient-success text-white"><i class="fas fa-medal me-1"></i> Lulus</span>
                                            @elseif($row->status == 'tidak_lulus')
                                                <span class="badge bg-gradient-danger text-white"><i class="fas fa-times me-1"></i> Tdk Lulus</span>
                                            @elseif($row->status == 'mutasi_keluar')
                                                <span class="badge bg-gradient-warning text-white"><i class="fas fa-sign-out-alt me-1"></i> Mutasi Keluar</span>
                                            @else
                                                <span class="badge bg-gradient-secondary text-white">{{ str_replace('_', ' ', $row->status) }}</span>
                                            @endif
                                        </td>

                                        <td class="align-middle text-center">
                                            <span class="text-xs text-secondary font-weight-bold">{{ $row->user_admin ?? '-' }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary shadow-sm mb-0" data-bs-dismiss="modal">Tutup Rincian</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    {{-- ========================================================================= --}}

    <x-app.footer />
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection