@extends('layouts.app') 

@section('page-title', 'Input Nilai Ekstrakurikuler')

@php
    // Helper Tahun
    $tahunSekarang = date('Y');
    $tahunAjaranList = [];
    for ($tahun = ($tahunSekarang + 1); $tahun >= ($tahunSekarang - 3); $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }
    $semesterList = ['Ganjil', 'Genap']; 
@endphp

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />
    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                
                {{-- HEADER UTAMA (Gaya Baru) --}}
                <div class="card my-4 border shadow-sm">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            {{-- Dekorasi Icon Besar --}}
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-shapes text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-shapes me-2"></i> Input Nilai Ekstrakurikuler
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Kelola penilaian aktivitas siswa
                                    </p>
                                </div>
                                
                                {{-- Badge Status Pojok Kanan Atas --}}
                                <div class="pe-3">
                                    @if($accessStatus == 'denied')
                                        <span class="badge bg-white text-danger"><i class="fas fa-ban me-1"></i> Akses Ditolak</span>
                                    @elseif($accessStatus == 'read_only')
                                        <span class="badge bg-white text-warning"><i class="fas fa-lock me-1"></i> Terkunci</span>
                                    @else
                                        <span class="badge bg-white text-success"><i class="fas fa-lock-open me-1"></i> Terbuka</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2">
                        @if (session('success'))
                            <div class="alert bg-gradient-success mx-4 text-white">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert bg-gradient-danger mx-4 text-white">{{ session('error') }}</div>
                        @endif

                        {{-- FILTER SECTION (POSISI KANAN) --}}
                        <div class="px-4 py-3 border-bottom">
                            <form action="{{ route('ekskul.nilai.index') }}" method="GET" class="d-flex justify-content-end align-items-center gap-3">
                                
                                <div class="d-flex flex-column">
                                    <label class="form-label font-weight-bold text-xs mb-1">Tahun Ajaran:</label>
                                    <select name="tahun_ajaran" class="form-select border ps-2 py-1 bg-white" style="min-width: 150px;" onchange="this.form.submit()">
                                        @foreach ($tahunAjaranList as $ta)
                                            <option value="{{ $ta }}" {{ $tahunAjaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex flex-column">
                                    <label class="form-label font-weight-bold text-xs mb-1">Semester:</label>
                                    <select name="semester" class="form-select border ps-2 py-1 bg-white" style="min-width: 150px;" onchange="this.form.submit()">
                                        @foreach($semesterList as $sem)
                                            <option value="{{ $sem }}" {{ $semesterRaw == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <button type="submit" class="d-none"></button>
                            </form>
                        </div>

                        {{-- INFO SEASON BAR (GAYA REFERENSI) --}}
                        @if($activeSeason)
                        <div class="px-4 my-3">
                            <div class="bg-gray-100 border-radius-lg p-3 d-flex align-items-center flex-wrap shadow-none border">
                                <span class="text-xs font-weight-bolder text-uppercase text-secondary me-3">DETAIL SEASON:</span>
                                
                                {{-- Badge Hitam untuk Info Season Aktif --}}
                                <span class="badge badge-sm bg-dark me-2">{{ $activeSeason->semester == 1 ? 'Ganjil' : 'Genap' }}</span>
                                <span class="badge badge-sm bg-dark me-2">{{ $activeSeason->tahun_ajaran }}</span>
                                
                                {{-- Status Season --}}
                                @if($activeSeason->is_open)
                                    <span class="badge badge-sm bg-gradient-success me-4">Terbuka</span>
                                @else
                                    <span class="badge badge-sm bg-gradient-danger me-4">Ditutup</span>
                                @endif

                                {{-- Tanggal --}}
                                <div class="d-flex align-items-center border-start border-secondary ps-4" style="border-color: #d1d5db !important;">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    <span class="text-xs font-weight-bold text-dark me-2">JADWAL INPUT:</span>
                                    <span class="text-xs text-primary font-weight-bolder">
                                        {{ date('d/m/Y', strtotime($activeSeason->start_date)) }} s/d {{ date('d/m/Y', strtotime($activeSeason->end_date)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- ALERT STATUS AKSES --}}
                        @if($isLocked)
                            <div class="px-4 pb-2">
                                <div class="alert {{ $accessStatus == 'denied' ? 'alert-danger text-dark bg-danger-soft' : 'alert-warning text-dark bg-warning-soft' }} d-flex align-items-start border-0 shadow-sm" role="alert">
                                    <i class="fas {{ $accessStatus == 'denied' ? 'fa-ban' : 'fa-lock' }} me-3 mt-1 text-lg"></i>
                                    <div>
                                        <h6 class="alert-heading font-weight-bolder mb-1 {{ $accessStatus == 'denied' ? 'text-danger' : 'text-warning' }}">
                                            {{ $accessStatus == 'denied' ? 'AKSES DITOLAK' : 'MODE TERKUNCI' }}
                                        </h6>
                                        <div class="text-sm">{!! $lockMessage !!}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- TABEL DATA (SELALU MUNCUL) --}}
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="5%">No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Ekstrakurikuler</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Guru Pembina</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" width="25%">Jumlah Penilaian</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ekskuls as $ekskul)
                                        @php
                                            $total = $ekskul->peserta_count;
                                            $graded = $ekskul->sudah_dinilai;
                                            $persen = $total > 0 ? round(($graded / $total) * 100) : 0;
                                            
                                            $color = $persen == 100 ? 'success' : ($persen >= 50 ? 'info' : 'warning');
                                        @endphp
                                        <tr>
                                            <td class="text-center text-sm font-weight-bold text-secondary">{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="icon icon-sm shadow border-radius-md bg-gradient-{{ $total > 0 ? 'dark' : 'secondary' }} text-center me-2 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-shapes text-white text-xs"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $ekskul->nama_ekskul }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-xs font-weight-bold text-dark">{{ $ekskul->guru->nama_guru ?? 'Belum Ditentukan' }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="w-100 me-3">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="text-xs font-weight-bold text-{{ $color }}">{{ $graded }} / {{ $total }} Siswa</span>
                                                        <span class="text-xs font-weight-bold text-secondary">{{ $persen }}%</span>
                                                    </div>
                                                    <div class="progress progress-sm" style="height: 4px;">
                                                        <div class="progress-bar bg-gradient-{{ $color }}" role="progressbar" style="width: {{ $persen }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($total == 0)
                                                    <span class="text-xs text-secondary">Kosong</span>
                                                @elseif($isLocked)
                                                    <button class="btn btn-xs btn-outline-secondary mb-0" disabled style="cursor: not-allowed; opacity: 0.6;">
                                                        <i class="fas fa-lock me-1"></i> Terkunci
                                                    </button>
                                                @else
                                                    <a href="{{ route('ekskul.nilai.input', ['id' => $ekskul->id_ekskul, 'semester' => $semesterRaw, 'tahun_ajaran' => $tahunAjaran]) }}" 
                                                       class="btn btn-xs btn-outline-primary mb-0">
                                                        <i class="fas fa-pen me-1"></i> Input Nilai
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-secondary">Tidak ada data ekstrakurikuler.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

<style>
    /* Styling khusus agar mirip referensi */
    .bg-gray-100 { background-color: #f3f4f6 !important; }
    
    /* Soft Red Alert */
    .bg-danger-soft { background-color: #fee2e2 !important; color: #b91c1c !important; }
    .text-danger { color: #dc2626 !important; }
    
    /* Soft Orange Alert */
    .bg-warning-soft { background-color: #ffedd5 !important; color: #c2410c !important; }
    .text-warning { color: #d97706 !important; }

    .badge.bg-dark { background-color: #1f2937 !important; color: white; }
</style>
@endsection