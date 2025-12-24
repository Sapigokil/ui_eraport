{{-- File: resources/views/rapor/nilai_index.blade.php --}}

@extends('layouts.app') {{-- Menggunakan base layout Corporate UI --}}

@section('title', 'Data Nilai Rapor (Dashboard Progress)')

@php
    // --- LOGIKA DEFAULT TA/SEMESTER (Untuk keperluan inisialisasi filter di view) ---
    // Logika ini diambil dari contoh proyek lama Anda
    $tahunSekarang = date('Y');
    $bulanSekarang = date('n');

    if ($bulanSekarang < 7) {
        $defaultTA1 = $tahunSekarang - 1;
        $defaultTA2 = $tahunSekarang;
        $defaultSemester = 'Genap'; 
    } else {
        $defaultTA1 = $tahunSekarang;
        $defaultTA2 = $tahunSekarang + 1;
        $defaultSemester = 'Ganjil'; 
    }

    $defaultTahunAjaran = $defaultTA1 . '/' . $defaultTA2;
    
    // Generate daftar TA (dari terbaru ke terlama)
    $tahunMulai = 2025; 
    $tahunAkhir = date('Y') + 5; 
    $tahunAjaranList = [];
    for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }
    
    $semesterList = ['Ganjil', 'Genap']; // List untuk dropdown
@endphp

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

        <x-app.navbar />

        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 shadow-xs border">
                        
                        {{-- HEADER --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas fa-chart-line me-2"></i> Dashboard Progress Pengisian Nilai Rapor
                                </h6>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            {{-- NOTIFIKASI --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm">{{ session('success') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            
                            {{-- FORM FILTER --}}
                            <div class="p-4 border-bottom">
                                <form action="{{ route('master.rapornilai.index') }}" method="GET" class="row align-items-end">
                                    
                                    {{-- Filter Kelas --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_kelas" class="form-label">Kelas:</label>
                                        <select class="form-select" id="id_kelas" name="id_kelas">
                                            <option value="">-- Semua Kelas --</option>
                                            @foreach ($kelasList as $k)
                                                <option value="{{ $k->id_kelas }}" 
                                                    {{ $request->id_kelas == $k->id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Filter Mata Pelajaran --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_mapel" class="form-label">Mata Pelajaran:</label>
                                        <select class="form-select" id="id_mapel" name="id_mapel">
                                            <option value="">-- Semua Mapel --</option>
                                            @foreach ($mapelList as $m)
                                                <option value="{{ $m->id_mapel }}" 
                                                    {{ $request->id_mapel == $m->id_mapel ? 'selected' : '' }}>
                                                    {{ $m->nama_mapel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Filter Tahun Ajaran --}}
                                    <div class="col-md-2 mb-3">
                                        <label for="id_tahun_ajaran" class="form-label">Tahun Ajaran:</label>
                                        <select class="form-select" id="id_tahun_ajaran" name="id_tahun_ajaran">
                                            <option value="">-- Semua TA --</option>
                                            @foreach ($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}" 
                                                    {{ request('id_tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                                                    {{ $ta }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Filter Semester --}}
                                    <div class="col-md-2 mb-3">
                                        <label for="semester" class="form-label">Semester:</label>
                                        <select class="form-select" id="semester" name="semester">
                                            <option value="">-- Semua Semester --</option>
                                            @foreach ($semesterList as $sem)
                                                <option value="{{ $sem }}" 
                                                    {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>
                                                    {{ $sem }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <button type="submit" class="btn btn-info w-100 mb-0">Tampilkan Data</button>
                                    </div>
                                </form>
                                <a href="{{ route('master.rapornilai.index') }}" class="btn btn-secondary mt-2 mb-0">Reset Filter</a>
                            </div>

                            {{-- TABEL PROGRESS NILAI --}}
                            <div class="table-responsive p-0 mt-3">
                                @if ($progress->isEmpty())
                                    <p class="text-secondary text-center text-sm my-3">
                                        Tidak ada data progres pengisian nilai ditemukan.
                                    </p>
                                @else
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 5%">No</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tahun Ajaran / Semester</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kelas</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mata Pelajaran</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Siswa</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Progres (%)</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                            @foreach ($progress as $i => $item)
                                                <tr>
                                                    <td class="ps-3 text-sm font-weight-bold">{{ $i + 1 }}</td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">{{ $item['tahun_ajaran'] }}</p>
                                                        <p class="text-xs text-secondary mb-0">Semester: {{ $item['semester'] == 1 ? 'Ganjil' : 'Genap' }}</p>
                                                    </td>
                                                    <td>
                                                        <h6 class="mb-0 text-sm">{{ $item['nama_kelas'] }}</h6>
                                                    </td>
                                                    <td>
                                                        <p class="text-sm font-weight-bold mb-0">{{ $item['nama_mapel'] }}</p>
                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <span class="badge badge-sm bg-gradient-{{ $item['persen'] < 100 ? 'danger' : 'success' }}">
                                                            {{ $item['dinilai'] }} / {{ $item['total_siswa'] }} Siswa
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="me-2 text-sm font-weight-bold">{{ $item['persen'] }}%</span>
                                                        <div class="progress mx-auto" style="width: 80px;">
                                                            @php
                                                                $progressColor = $item['persen'] == 100 ? 'success' : ($item['persen'] > 50 ? 'warning' : 'danger');
                                                            @endphp
                                                            <div class="progress-bar bg-gradient-{{ $progressColor }}" 
                                                                 role="progressbar" 
                                                                 aria-valuenow="{{ $item['persen'] }}" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100" 
                                                                 style="width: {{ $item['persen'] }}%;">
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        {{-- Tombol untuk menuju form input/edit massal --}}
                                                        <a href="{{ route('rapornilai.create', [ // Menggunakan route rapornilai.create
                                                            'id_kelas' => $item['id_kelas'], 
                                                            'id_mapel' => $item['id_mapel'],
                                                            'id_tahun_ajaran' => $item['tahun_ajaran'],
                                                            'semester' => $item['semester'] == 1 ? 'Ganjil' : 'Genap' // Kirim string Ganjil/Genap
                                                        ]) }}" class="btn btn-sm btn-outline-primary mb-0">
                                                            Lanjut Input
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            
                                        </tbody>
                                    </table>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <x-app.footer />
        </div>
    </main>
@endsection