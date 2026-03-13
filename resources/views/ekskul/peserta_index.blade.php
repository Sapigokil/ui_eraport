@extends('layouts.app')

@section('page-title', 'Data Peserta Ekstrakurikuler')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- 1. HEADER BANNER (Updated Style) --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            {{-- Dekorasi Icon Besar --}}
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-users text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-running me-2"></i> Rekapitulasi Peserta Ekstrakurikuler
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Kelola anggota dan peserta kegiatan ekstrakurikuler
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2">
                        
                        {{-- 2. ALERT SYSTEM --}}
                        @if(session('success'))
                            <div class="alert bg-gradient-success alert-dismissible text-white mx-4 mt-3 fade show" role="alert">
                                <span class="text-sm"><strong>Sukses!</strong> {{ session('success') }}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert bg-gradient-danger alert-dismissible text-white mx-4 mt-3 fade show" role="alert">
                                <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                            </div>
                        @endif

                        {{-- 3. FORM FILTER GURU (RBAC) --}}
                        {{-- Ubah 'd-block' ke 'd-none' untuk menyembunyikan --}}
                        <div class="px-4 py-3 border-bottom bg-gray-100 d-none">
                            <form action="{{ route('ekskul.peserta.index') }}" method="GET" class="mb-0">
                                <div class="row align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label text-primary font-weight-bolder text-uppercase text-xs"><i class="fas fa-bug"></i> Filter Guru Pembimbing</label>
                                        <select name="id_guru" class="form-select border ps-2 bg-white border-primary" onchange="this.form.submit()" {{ $isGuru ? 'disabled' : '' }}>
                                            <option value="">Semua Guru</option>
                                            @foreach($guruList as $g)
                                                <option value="{{ $g->id_guru }}" {{ $id_guru_filter == $g->id_guru ? 'selected' : '' }}>{{ $g->nama_guru }}</option>
                                            @endforeach
                                        </select>
                                        @if($isGuru) <input type="hidden" name="id_guru" value="{{ $id_guru_filter }}"> @endif
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- 4. TABEL DATA --}}
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="5%">No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Ekstrakurikuler</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Guru Pembimbing</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Jumlah Peserta</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ekskuls as $item)
                                    <tr>
                                        <td class="text-center text-secondary text-xs font-weight-bold">
                                            {{ $loop->iteration + $ekskuls->firstItem() - 1 }}
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $item->nama_ekskul }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-xs font-weight-bold text-secondary">
                                                <i class="fas fa-user-tie me-1 text-xs"></i> {{ $item->guru->nama_guru ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-sm bg-gradient-info">
                                                {{ $item->peserta_count }} Siswa
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('ekskul.peserta.edit', $item->id_ekskul) }}" class="btn btn-sm btn-outline-warning mb-0">
                                                <i class="fas fa-user-edit me-1"></i> Kelola Peserta
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-sm text-secondary py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-folder-open fa-3x opacity-5 mb-2"></i>
                                                <h6 class="text-dark">Belum ada data ekstrakurikuler.</h6>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- 5. PAGINATION --}}
                        <div class="px-4 py-3 d-flex justify-content-end">
                            {{ $ekskuls->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-app.footer />
    </div>
</main>
@endsection