@extends('layouts.app')

@section('page-title', 'Kelola Peserta Ekskul')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-4">
        
        {{-- 1. HEADER SECTION --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary shadow-primary border-radius-xl">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8 d-flex align-items-center">
                                <div class="icon icon-lg icon-shape bg-white shadow text-center border-radius-xl">
                                    {{-- REVISI: Ganti Icon Bola jadi Icon Universal (Shapes/Layer) --}}
                                    <i class="fas fa-shapes text-primary text-xl opacity-10" aria-hidden="true"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="text-white mb-1 font-weight-bolder">
                                        {{ $ekskul->nama_ekskul }}
                                    </h5>
                                    <div class="d-flex align-items-center text-white text-sm opacity-8">
                                        <i class="fas fa-user-tie me-2"></i> 
                                        <span>Pembimbing: <b>{{ $ekskul->guru->nama_guru ?? 'Belum ditentukan' }}</b></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-inline-block bg-white-20 rounded-3 px-3 py-2 border border-white-50">
                                    <p class="text-xs text-uppercase font-weight-bold text-white mb-0 opacity-8">Total Peserta</p>
                                    <h3 class="text-white font-weight-bolder mb-0">
                                        {{ $total_peserta }} <span class="text-sm font-weight-normal">Siswa</span>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- 2. KOLOM KIRI: LIST KELAS (SIDEBAR) --}}
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="card border-radius-xl h-100 shadow-sm border border-light">
                    <div class="card-header bg-gray-100 border-bottom p-3 border-radius-top-xl">
                        <h6 class="mb-0 text-dark font-weight-bold">
                            <i class="fas fa-list-ul me-2 text-primary"></i> Pilih Kelas
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="list-group list-group-flush">
                            @foreach($kelas as $k)
                                @php
                                    $isActive = $filter_id_kelas == $k->id_kelas;
                                    $jumlahPesertaKelas = $peserta_per_kelas[$k->id_kelas] ?? 0;
                                @endphp
                                
                                <a href="{{ route('ekskul.peserta.edit', ['id' => $ekskul->id_ekskul, 'id_kelas' => $k->id_kelas]) }}" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-3 py-3 border-bottom mb-1 rounded-3 {{ $isActive ? 'bg-primary-soft border-primary-left' : '' }}">
                                    
                                    <div class="d-flex align-items-center" style="max-width: 75%;">
                                        @if($isActive)
                                            <i class="fas fa-circle text-primary me-2" style="font-size: 8px;"></i>
                                        @endif
                                        <span class="text-sm font-weight-bold text-truncate {{ $isActive ? 'text-primary' : 'text-secondary' }}">
                                            {{ $k->nama_kelas }}
                                        </span>
                                    </div>
                                    
                                    @if($jumlahPesertaKelas > 0)
                                        <span class="badge {{ $isActive ? 'bg-primary text-white shadow-sm' : 'bg-gray-200 text-dark' }} border-radius-md">
                                            {{ $jumlahPesertaKelas }}
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. KOLOM KANAN: DAFTAR SISWA & FORM --}}
            <div class="col-lg-9 col-md-8">
                @if($filter_id_kelas)
                    <form action="{{ route('ekskul.peserta.update', $ekskul->id_ekskul) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id_kelas_filter" value="{{ $filter_id_kelas }}">

                        <div class="card border-radius-xl shadow-sm border border-light">
                            {{-- HEADER CARD --}}
                            <div class="card-header p-3 border-bottom bg-white border-radius-top-xl sticky-top" style="z-index: 10; top: 0;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-md bg-gradient-success shadow-success text-center border-radius-md me-3">
                                            <i class="fas fa-users text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-dark font-weight-bold">Daftar Siswa {{ $kelas->find($filter_id_kelas)->nama_kelas }}</h6>
                                            <p class="text-xs text-secondary mb-0">Kelola keikutsertaan siswa di kelas ini.</p>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn bg-gradient-primary btn-sm mb-0 shadow-md">
                                        <i class="fas fa-save me-1"></i> Simpan
                                    </button>
                                </div>
                            </div>
                            
                            {{-- BODY: TABEL --}}
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0 table-hover">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                {{-- REVISI: Tambah Kolom No --}}
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4 py-3 text-center" width="5%">No</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center" width="60px">Pilih</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Siswa</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NIS / NISN</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($siswa_list as $index => $siswa)
                                                @php
                                                    $isChecked = in_array($siswa->id_siswa, $registered_ids);
                                                @endphp
                                                <tr class="border-bottom {{ $isChecked ? 'bg-success-subtle' : '' }} transition-all">
                                                    {{-- Kolom No --}}
                                                    <td class="text-center text-secondary text-xs font-weight-bold">
                                                        {{ $loop->iteration }}
                                                    </td>
                                                    
                                                    {{-- Checkbox --}}
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center">
                                                            <input class="form-check-input border border-secondary cursor-pointer" 
                                                                   type="checkbox" 
                                                                   name="siswa_ids[]" 
                                                                   value="{{ $siswa->id_siswa }}"
                                                                   id="check_{{ $siswa->id_siswa }}"
                                                                   style="width: 18px; height: 18px;"
                                                                   {{ $isChecked ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    
                                                    {{-- Nama Siswa (Klik nama trigger checkbox) --}}
                                                    <td onclick="document.getElementById('check_{{ $siswa->id_siswa }}').click();" class="cursor-pointer">
                                                        <div class="d-flex flex-column justify-content-center py-1">
                                                            <h6 class="mb-0 text-sm {{ $isChecked ? 'text-success font-weight-bolder' : 'text-dark font-weight-normal' }} text-nowrap">
                                                                {{ $siswa->nama_siswa }}
                                                            </h6>
                                                            @if($isChecked)
                                                                <span class="text-xxs text-success font-weight-bold mt-1">
                                                                    <i class="fas fa-check-circle me-1"></i> Terdaftar
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    
                                                    {{-- REVISI: NIS / NISN Tanpa Border --}}
                                                    <td class="text-sm text-secondary text-nowrap">
                                                        <span>{{ $siswa->nipd }}</span> 
                                                        / {{ $siswa->nisn }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-5">
                                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                                            <div class="icon icon-lg bg-gray-100 rounded-circle mb-3 text-secondary">
                                                                <i class="fas fa-user-slash fa-lg"></i>
                                                            </div>
                                                            <h6 class="text-secondary">Tidak ada siswa di kelas ini.</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                @else
                    {{-- EMPTY STATE --}}
                    <div class="card h-100 border-radius-xl shadow-sm border-dashed border-2 border-light d-flex justify-content-center align-items-center text-center p-5 bg-white">
                        <div class="bg-gradient-light shadow-sm rounded-circle p-4 mb-3" style="width: 80px; height: 80px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-mouse-pointer fa-2x text-primary opacity-6"></i>
                        </div>
                        <h4 class="font-weight-bold text-dark">Pilih Kelas</h4>
                        <p class="text-secondary mx-auto" style="max-width: 300px;">
                            Silakan klik salah satu kelas di menu sebelah kiri untuk melihat daftar siswa dan mengelola peserta.
                        </p>
                    </div>
                @endif
            </div>
        </div>
        
        <x-app.footer />
    </div>
</main>

{{-- Custom CSS --}}
<style>
    .bg-primary-soft { background-color: #f0f2f5 !important; }
    .border-primary-left { border-left: 4px solid #cb0c9f !important; }
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
    .bg-success-subtle { background-color: rgba(130, 214, 22, 0.08) !important; }
    .transition-all { transition: all 0.2s ease-in-out; }
    .bg-gray-100 { background-color: #f8f9fa !important; }
    .bg-gray-200 { background-color: #e9ecef !important; }
    .bg-white-20 { background-color: rgba(255, 255, 255, 0.2); }
    .border-white-50 { border-color: rgba(255, 255, 255, 0.5) !important; }
</style>
@endsection