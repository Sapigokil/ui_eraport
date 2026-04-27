@extends('layouts.app') 

@section('page-title', 'Monitoring Pengumuman Siswa')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg pt-4">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- HEADER BANNER STATISTIK --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-info overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                        <i class="fas fa-bullhorn text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="row align-items-center text-white">
                            <div class="col-md-5">
                                <h3 class="text-white font-weight-bold mb-1">Monitoring Pengumuman</h3>
                                <p class="text-white opacity-8 mb-2">
                                    Pantau aktivitas siswa dalam melihat hasil kenaikan kelas atau kelulusan mereka.
                                </p>
                            </div>
                            <div class="col-md-7 text-end mt-4 mt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-between gap-4">
                                    
                                    {{-- STAT 1: TOTAL PENGUMUMAN --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Total Diterbitkan</span>
                                        <h4 class="text-white mb-0">{{ $statTotal }} <span class="text-sm fw-normal opacity-8">Siswa</span></h4>
                                    </div>

                                    {{-- STAT 2: BELUM DIBACA --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Belum Dibaca</span>
                                        <h4 class="text-white mb-0">{{ $statBelumBaca }}</h4>
                                    </div>

                                    {{-- STAT 3: SUDAH DIBACA --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Sudah Dibaca</span>
                                        <h4 class="text-white mb-0">{{ $statSudahBaca }}</h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $persenBaca }}%" aria-valuenow="{{ $persenBaca }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD FILTER --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-body p-3">
                <form action="{{ route('pengumuman.naiklulus_index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Filter Kelas</label>
                        <select name="id_kelas" class="form-select border-secondary" onchange="this.form.submit()">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }} ({{ $k->wali_kelas ?? 'Tanpa Wali' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Status Baca</label>
                        <select name="status_baca" class="form-select border-secondary" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="sudah" {{ request('status_baca') == 'sudah' ? 'selected' : '' }}>Sudah Dibaca</option>
                            <option value="belum" {{ request('status_baca') == 'belum' ? 'selected' : '' }}>Belum Dibaca</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        {{-- Placeholder jika ke depannya butuh filter Tahun Ajaran --}}
                    </div>
                    <div class="col-md-2 text-end">
                        <a href="{{ route('pengumuman.naiklulus_index') }}" class="btn btn-outline-secondary w-100 mb-0"><i class="fas fa-undo me-1"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABEL DATA PENGUMUMAN --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border">
                    <div class="card-header p-3 bg-white border-bottom">
                        <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-list-ul me-2"></i> Daftar Status Pengumuman Siswa</h6>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                        <th class="ps-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 25%">Nama Siswa</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kelas</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jenis & Hasil</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu Dibaca</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pengumumanList as $idx => $p)
                                    <tr>
                                        <td class="text-center text-sm text-secondary">
                                            {{ $pengumumanList->firstItem() + $idx }}
                                        </td>
                                        
                                        <td class="ps-3">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $p->siswa->nama_siswa ?? 'Siswa Terhapus' }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $p->siswa->nisn ?? '-' }}</p>
                                            </div>
                                        </td>

                                        <td class="text-center align-middle">
                                            <span class="text-xs font-weight-bold text-dark">
                                                {{ $p->siswa->kelas->nama_kelas ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="text-center align-middle">
                                            <span class="text-xs font-weight-bold text-uppercase d-block mb-1">{{ $p->jenis }}</span>
                                            
                                            @if(in_array(strtolower($p->status_hasil), ['naik', 'naik kelas', 'y', 'lulus']))
                                                <span class="badge badge-sm bg-gradient-success">{{ $p->status_hasil }}</span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-danger">{{ $p->status_hasil }}</span>
                                            @endif
                                        </td>

                                        <td class="text-center align-middle">
                                            @if($p->has_seen && $p->waktu_dilihat)
                                                <span class="text-xs font-weight-bold text-dark d-block">{{ \Carbon\Carbon::parse($p->waktu_dilihat)->format('d M Y') }}</span>
                                                <span class="text-xxs text-secondary">{{ \Carbon\Carbon::parse($p->waktu_dilihat)->format('H:i:s') }} WIB</span>
                                            @else
                                                <span class="text-xs text-secondary italic">-</span>
                                            @endif
                                        </td>

                                        <td class="text-center align-middle">
                                            @if($p->has_seen)
                                                <span class="badge badge-sm bg-gradient-info"><i class="fas fa-check-double me-1"></i> Sudah Dibaca</span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-secondary"><i class="fas fa-envelope me-1"></i> Belum Dibaca</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-secondary">
                                            <i class="fas fa-folder-open fa-3x mb-3 opacity-5"></i><br>
                                            <h6 class="text-secondary font-weight-normal mb-0">Belum ada data pengumuman yang diterbitkan.</h6>
                                            <p class="text-sm">Data akan muncul setelah Anda memfinalisasi Kenaikan/Kelulusan siswa.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- PAGINATION --}}
                        @if($pengumumanList->hasPages())
                            <div class="card-footer px-3 border-0 d-flex flex-column flex-lg-row align-items-center justify-content-between">
                                <p class="text-sm text-secondary mb-0">
                                    Menampilkan {{ $pengumumanList->firstItem() }} hingga {{ $pengumumanList->lastItem() }} dari {{ $pengumumanList->total() }} data
                                </p>
                                <div class="mt-3 mt-lg-0">
                                    {{ $pengumumanList->appends(request()->query())->links('pagination::bootstrap-5') }}
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
@endsection