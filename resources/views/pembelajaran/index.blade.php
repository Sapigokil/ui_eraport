{{-- File: resources/views/pembelajaran/index.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Data Pembelajaran Mata Pelajaran per Kelas')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-chalkboard-teacher me-2"></i> Data Pembelajaran</h6>
                                <div class="pe-3">
                                    <a href="{{ route('master.pembelajaran.create') }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-plus me-1"></i> Tambah Pembelajaran
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- 🛑 FORM FILTER RAPAT 🛑 --}}
                            <div class="p-2 border rounded mb-2 bg-light">
                                <form action="{{ route('master.pembelajaran.index') }}" method="GET" class="mb-0">
                                    <div class="row g-2 align-items-center"> {{-- g-2 untuk jarak antar kolom lebih rapat --}}
                                        
                                        {{-- 1. Filter Mapel --}}
                                        <div class="col-md-4">
                                            <select name="id_mapel" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">-- Filter Mapel --</option>
                                                @foreach($mapel_list as $m)
                                                    <option value="{{ $m->id_mapel }}" {{ request('id_mapel') == $m->id_mapel ? 'selected' : '' }}>
                                                        {{ $m->nama_mapel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- 2. Filter Kelas --}}
                                        <div class="col-md-4">
                                            <select name="id_kelas" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">-- Filter Kelas --</option>
                                                @foreach($kelas_list as $k)
                                                    <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                        {{ $k->nama_kelas }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- 3. Filter Guru & Reset --}}
                                        <div class="col-md-4">
                                            <div class="d-flex gap-2">
                                                <select name="id_guru" class="form-select form-select-sm w-100" onchange="this.form.submit()">
                                                    <option value="">-- Filter Guru --</option>
                                                    <option value="0" {{ request('id_guru') === '0' ? 'selected' : '' }}>Belum Ada Guru</option>
                                                    @foreach($guru_list as $g)
                                                        <option value="{{ $g->id_guru }}" {{ request('id_guru') == $g->id_guru ? 'selected' : '' }}>
                                                            {{ $g->nama_guru }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                @if(request()->hasAny(['id_mapel', 'id_kelas', 'id_guru']))
                                                    <a href="{{ route('master.pembelajaran.index') }}" class="btn btn-icon btn-sm btn-outline-secondary mb-0" title="Reset Filter">
                                                        <i class="fas fa-undo"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>
                            
                            {{-- Tombol Export (Rata Kanan, Rapat ke atas) --}}
                            <div class="d-flex justify-content-end mb-3 mt-2">
                                <a href="{{ route('master.pembelajaran.export.pdf', request()->query()) }}" class="btn btn-sm btn-info me-2 text-white mb-0">
                                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                                </a>
                                <a href="{{ route('master.pembelajaran.export.csv', request()->query()) }}" class="btn btn-sm btn-secondary text-white mb-0">
                                    <i class="fas fa-file-csv me-1"></i> Export CSV
                                </a>
                            </div>

                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mata Pelajaran</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Kelas Terdampak</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Guru Pengampu</th>
                                            {{-- <th class="text-secondary opacity-7" style="min-width: 150px;">Aksi</th> --}}
                                        </tr>
                                    </thead>
                                    @php
                                        // Grouping data pembelajaran berdasarkan ID Mapel
                                        $groupedPembelajaran = $pembelajaran->groupBy('id_mapel');
                                    @endphp

                                    <tbody>
                                        @foreach ($groupedPembelajaran as $id_mapel => $items)
                                            @php
                                                $firstItem = $items->first();
                                                // Grouping lagi berdasarkan Guru di dalam mapel tersebut
                                                $byGuru = $items->groupBy('id_guru');
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $firstItem->mapel->nama_mapel }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $items->count() }} Kelas Terdaftar</p>
                                                    </div>
                                                </td>
                                                
                                                {{-- Kolom Distribusi Kelas --}}
                                                <td class="align-middle">
                                                    <div class="d-flex flex-column gap-2">
                                                        @foreach ($byGuru as $guruId => $kelasItems)
                                                            @php
                                                                // AMBIL DATA GURU DENGAN AMAN
                                                                // Cek apakah relasi guru ada?
                                                                $firstItem = $kelasItems->first();
                                                                $namaGuru = $firstItem->guru ? $firstItem->guru->nama_guru : 'Belum Ada Guru';
                                                                $statusColor = $firstItem->guru ? 'text-dark' : 'text-danger fst-italic';
                                                            @endphp

                                                            <div class="border rounded p-2 mb-1 bg-light">
                                                                {{-- TAMPILKAN NAMA GURU (SAFE MODE) --}}
                                                                <strong class="text-xs {{ $statusColor }}">
                                                                    <i class="fas fa-user-tie me-1"></i> {{ $namaGuru }}
                                                                </strong>
                                                                
                                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                                    @foreach ($kelasItems as $item)
                                                                        <span class="badge bg-gradient-info">
                                                                            {{ $item->kelas->nama_kelas ?? '-' }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>

                                                <td class="align-middle text-center">
                                                    {{-- Tombol Edit Massal (Link ke fungsi Edit yang sudah kita buat sebelumnya) --}}
                                                    <a href="{{ route('master.pembelajaran.edit', $firstItem->id_pembelajaran) }}" class="btn btn-sm btn-outline-info">
                                                        Atur Guru
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
        
    </main>
@endsection