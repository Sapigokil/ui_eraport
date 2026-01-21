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

                            {{-- 🛑 FORM FILTER BARU 🛑 --}}
                            <div class="p-3 border rounded mb-3">
                                <form action="{{ route('master.pembelajaran.index') }}" method="GET" class="row align-items-end">
                                    
                                    {{-- Filter Mapel --}}
                                    <div class="col-md-4 mb-3">
                                        <label for="id_mapel" class="form-label">Mata Pelajaran:</label>
                                        <select name="id_mapel" id="id_mapel" class="form-select">
                                            <option value="">Semua Mapel</option>
                                            @foreach($mapel_list as $m)
                                                <option value="{{ $m->id_mapel }}" 
                                                    {{ request('id_mapel') == $m->id_mapel ? 'selected' : '' }}>
                                                    {{ $m->nama_mapel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Filter Kelas --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_kelas" class="form-label">Kelas:</label>
                                        <select name="id_kelas" id="id_kelas" class="form-select">
                                            <option value="">Semua Kelas</option>
                                            @foreach($kelas_list as $k)
                                                <option value="{{ $k->id_kelas }}" 
                                                    {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Filter Guru --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_guru" class="form-label">Guru Pengampu:</label>
                                        <select name="id_guru" id="id_guru" class="form-select" onchange="this.form.submit()">
                                            <option value="">Semua Guru</option>
                                            @foreach($guru_list as $g)
                                                <option value="{{ $g->id_guru }}" 
                                                    {{ request('id_guru') == $g->id_guru ? 'selected' : '' }}>
                                                    {{ $g->nama_guru }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2 mb-3 text-end">
                                        <button type="submit" class="btn bg-gradient-info btn-sm w-100 mb-0">Filter</button>
                                    </div>
                                </form>
                            </div>
                            {{-- 🛑 END FORM FILTER 🛑 --}}


                            {{-- Tombol Export --}}
                            <div class="d-flex justify-content-end mb-3">
                                <a href="{{ route('master.pembelajaran.export.pdf', request()->query()) }}" class="btn btn-sm btn-info me-2 text-white" title="Export data yang difilter">
                                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                                </a>
                                <a href="{{ route('master.pembelajaran.export.csv', request()->query()) }}" class="btn btn-sm btn-secondary text-white" title="Export data yang difilter">
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
                                                                $guruName = ($guruId == 1 || $guruId == 0) ? 'Belum Ditentukan' : $kelasItems->first()->guru->nama_guru;
                                                                $badgeColor = ($guruId == 1 || $guruId == 0) ? 'bg-gradient-danger' : 'bg-gradient-success';
                                                                $textColor = ($guruId == 1 || $guruId == 0) ? 'text-danger' : 'text-dark';
                                                            @endphp
                                                            
                                                            <div class="d-flex align-items-start mb-2">
                                                                {{-- Nama Guru --}}
                                                                <span class="text-xs font-weight-bold {{ $textColor }} me-2" style="min-width: 150px;">
                                                                    <i class="fas fa-user-tie me-1"></i> {{ $guruName }}
                                                                </span>

                                                                {{-- List Kelas (Badges) --}}
                                                                <div class="d-flex flex-wrap gap-1">
                                                                    @foreach ($kelasItems as $item)
                                                                        <span class="badge badge-sm {{ $badgeColor }}" style="margin-right: 2px; margin-bottom: 2px;">
                                                                            {{ $item->kelas->nama_kelas }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                            {{-- Garis pemisah tipis antar guru --}}
                                                            @if(!$loop->last) <hr class="horizontal dark my-1"> @endif
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