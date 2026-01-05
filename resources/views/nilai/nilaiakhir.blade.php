{{-- File: resources/views/nilai/nilaiakhir.blade.php (Koreksi Penegasan Notifikasi) --}}

@extends('layouts.app') 

{{-- HEADER DINAMIS --}}
@section('page-title', 'Rekapitulasi Nilai Akhir')

@php
    // --- LOGIKA TAHUN AJARAN & SEMESTER ---
    $request = request();
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
    
    $tahunMulai = 2025; 
    $tahunAkhir = date('Y') + 5; 
    $tahunAjaranList = [];
    for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }
    $semesterList = ['Ganjil', 'Genap']; 
    
    // Ambil variabel $error dari controller/session
    $error = $error ?? session('error');
@endphp

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

        <x-app.navbar />

        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 shadow-xs border">
                        
                        {{-- ================================================================= --}}
                        {{-- HEADER DINAMIS --}}
                        {{-- ================================================================= --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas fa-calculator me-2"></i> Rekapitulasi Nilai Akhir Rapor
                                </h6>
                            </div>
                        </div>
                        {{-- ================================================================= --}}


                        <div class="card-body px-0 pb-2">
                            
                            {{-- 🛑 KOREKSI 1: NOTIFIKASI DIBUNGKUS DALAM PADDING 🛑 --}}
                            @if (session('success') || $error)
                                <div class="p-4 pt-0">
                                    @if (session('success'))
                                        <div class="alert bg-gradient-success alert-dismissible text-white fade show mb-0" role="alert">
                                            <span class="text-sm"><strong>Sukses!</strong> {!! session('success') !!}</span>
                                            <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    @if ($error)
                                        <div class="alert bg-gradient-danger alert-dismissible text-white fade show mb-0" role="alert">
                                            <span class="text-sm"><strong>Gagal!</strong> {{ $error }}</span>
                                            <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            
                            {{-- FORM FILTER --}}
                            <div class="p-4 border-bottom">
                                {{-- 🛑 Route Filter: master.nilaiakhir.index (Asumsi) 🛑 --}}
                                <form action="{{ route('master.nilaiakhir.index') }}" method="GET" class="row align-items-end">
                                    
                                    {{-- 1. Kelas --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_kelas" class="form-label">Kelas:</label>
                                        <select name="id_kelas" id="id_kelas" required class="form-select" onchange="this.form.submit()">
                                            <option value="">Pilih Kelas</option>
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->id_kelas }}" 
                                                    {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- 2. Mapel --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_mapel" class="form-label">Mata Pelajaran:</label>
                                        <select name="id_mapel" id="id_mapel" required class="form-select" {{ !request('id_kelas') ? 'disabled' : '' }} onchange="this.form.submit()">
                                            <option value="">Pilih Mapel</option>
                                            @foreach ($mapel as $m)
                                                <option value="{{ $m->id_mapel }}" 
                                                    {{ request('id_mapel') == $m->id_mapel ? 'selected' : '' }}>
                                                    {{ $m->nama_mapel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 3. Semester --}}
                                    <div class="col-md-2 mb-3">
                                        <label for="semester" class="form-label">Semester:</label>
                                        <select name="semester" id="semester" required class="form-select">
                                            @foreach($semesterList as $sem)
                                                <option value="{{ $sem }}" 
                                                    {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>
                                                    {{ $sem }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 4. Tahun Ajaran --}}
                                    <div class="col-md-2 mb-3">
                                        <label for="tahun_ajaran" class="form-label">Tahun Ajaran:</label>
                                        <select name="tahun_ajaran" id="tahun_ajaran" required class="form-select">
                                            @foreach ($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}" 
                                                    {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                                                    {{ $ta }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2 mb-3 text-end">
                                        <button type="submit" class="btn bg-gradient-primary w-100 mb-0">Tampilkan Data</button>
                                    </div>
                                </form>
                            </div>

                            {{-- KONTEN REKAPITULASI --}}
                            <div class="p-4 pt-0"> 
                                
                                @if(!$request->id_kelas || !$request->id_mapel || !$request->tahun_ajaran || !$request->semester)
                                    <p class="text-secondary mt-3 p-3 text-center border rounded">
                                        Silakan pilih **Kelas, Mata Pelajaran, Semester, dan Tahun Ajaran** di atas untuk menampilkan data.
                                    </p>

                                @elseif($siswa->isEmpty())
                                    <p class="text-danger mt-3 p-3 text-center border rounded">
                                        Tidak ada siswa ditemukan di kelas yang dipilih, atau filter Agama Khusus tidak cocok.
                                    </p>
                                
                                @else
                                    <div style="background-color: #ff7b00 !important;" class="alert text-white text-sm">
                                        **Catatan:** Wajib memasukan minimal 2 Nilai Sumatif, bila tidak Bobot tidak dapat dihitung.
                                    </div>
                                    
                                    <div class="table-responsive p-0">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    {{-- 1. INFORMASI SISWA (Abu Gelap) --}}
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 ps-3 text-center bg-secondary" style="width: 5%">No</th>
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-secondary" style="width: 25%">Nama Siswa</th>
                                                    
                                                    {{-- 2. SUMATIF (Biru Tua) --}}
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">Sumatif 1</th>
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">Sumatif 2</th>
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">Sumatif 3</th>
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">Rata-rata</th>
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">Bobot Sumatif</th>

                                                    {{-- 3. PROJECT (Hijau Tua) --}}
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-success">Project</th>
                                                    {{-- <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-success">Rata P</th> --}}
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-success">Bobot Project</th>
                                                    
                                                    {{-- 4. AKHIR (Warna Berbeda) --}}
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-danger text-bold">NILAI AKHIR</th>
                                                    <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 ps-2 bg-danger">CAPAIAN AKHIR</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                            @foreach($siswa as $i => $s)
                                                @php
                                                    $rekapSiswa = $rekap[$s->id_siswa] ?? [];
                                                    $nilaiAkhir = $rekapSiswa['nilai_akhir'] ?? '-';
                                                @endphp
                                                <tr class="border-b hover:bg-gray-50">

                                                    <td class="ps-3 py-2 text-sm text-center">{{ $i + 1 }}</td>
                                                    <td class="px-3 py-2 text-sm font-weight-bold">{{ $s->nama_siswa }}</td>

                                                    {{-- S1, S2, S3 --}}
                                                    <td class="px-3 py-2 text-sm text-center">{{ $rekapSiswa['s1'] ?? '-' }}</td>
                                                    <td class="px-3 py-2 text-sm text-center">{{ $rekapSiswa['s2'] ?? '-' }}</td>
                                                    <td class="px-3 py-2 text-sm text-center">{{ $rekapSiswa['s3'] ?? '-' }}</td>
                                                    
                                                    {{-- Rata & Bobot Sumatif --}}
                                                    <td class="px-3 py-2 text-sm text-center font-weight-bold">{{ $rekapSiswa['rata_sumatif'] ?? '-' }}</td>
                                                    <td class="px-3 py-2 text-sm text-center text-info font-weight-bolder">{{ $rekapSiswa['bobot_sumatif'] ?? '-' }}</td>

                                                    {{-- Nilai & Bobot Project --}}
                                                    <td class="px-3 py-2 text-sm text-center">{{ $rekapSiswa['nilai_project'] ?? '-' }}</td>
                                                    {{-- <td class="px-3 py-2 text-sm text-center font-weight-bold">{{ $rekapSiswa['rata_project'] ?? '-' }}</td> --}}
                                                    <td class="px-3 py-2 text-sm text-center text-success font-weight-bolder">{{ $rekapSiswa['bobot_project'] ?? '-' }}</td>
                                                    
                                                    {{-- NILAI AKHIR --}}
                                                    <td class="px-3 py-2 text-sm text-center">
                                                        <span class="badge bg-gradient-primary text-md font-weight-bolder">{{ $nilaiAkhir }}</span>
                                                    </td>
                                                    
                                                    {{-- CAPAIAN AKHIR --}}
                                                    <td class="px-3 py-2 text-xs">
                                                        {{ $rekapSiswa['capaian_akhir'] ?? 'Data belum dihitung/tersedia.' }}
                                                    </td>

                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
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