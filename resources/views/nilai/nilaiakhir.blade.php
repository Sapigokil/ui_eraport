@extends('layouts.app') 

@section('page-title', 'Rekapitulasi Nilai Akhir')

@php
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
    $tahunMulai = $tahunSekarang - 3; 
    $tahunAkhir = $tahunSekarang + 3; 

    $tahunAjaranList = [];
    for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }
    $semesterList = ['Ganjil', 'Genap']; 
    $error = $error ?? session('error');
@endphp

@section('content')
<style>
    /* Style untuk memotong teks capaian agar tidak memenuhi layar */
    .truncate-text {
        max-width: 150px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: help;
    }
    /* Sedikit memperkecil font tabel agar muat banyak kolom */
    .table-sm-custom th, .table-sm-custom td {
        font-size: 0.75rem !important;
        padding: 0.5rem 0.4rem !important;
    }
</style>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- HEADER: KEMBALI KE DESAIN LAMA (INFO) --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-white text-capitalize ps-3 mb-0">
                                <i class="fas fa-calculator me-2"></i> Rekapitulasi Nilai Akhir Rapor
                            </h6>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2">
                        
                        {{-- ALERT / NOTIFIKASI --}}
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
                        
                        {{-- FILTER FORM --}}
                        <div class="p-4 border-bottom">
                            <form action="{{ route('rapornilai.nilaiakhir.index') }}" method="GET" class="row align-items-end mb-0">
                                <div class="col-md-3 mb-3">
                                    <label for="id_kelas" class="form-label">Kelas:</label>
                                    <select name="id_kelas" id="id_kelas" required class="form-select border px-2" onchange="this.form.submit()">
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="id_mapel" class="form-label">Mata Pelajaran:</label>
                                    <select name="id_mapel" id="mapel_filter" required class="form-select border px-2" {{ !request('id_kelas') ? 'disabled' : '' }} onchange="this.form.submit()">
                                        <option value="">Pilih Mapel</option>
                                        @foreach ($mapel as $m)
                                            <option value="{{ $m->id_mapel }}" {{ request('id_mapel') == $m->id_mapel ? 'selected' : '' }}>
                                                {{ $m->nama_mapel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="semester" class="form-label">Semester:</label>
                                    <select name="semester" id="input_semester" required class="form-select border px-2" onchange="this.form.submit()">
                                        @foreach($semesterList as $sem)
                                            <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>
                                                {{ $sem }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="tahun_ajaran" class="form-label">Tahun Ajaran:</label>
                                    <select name="tahun_ajaran" id="input_tahun_ajaran" required class="form-select border px-2" onchange="this.form.submit()">
                                        @foreach ($tahunAjaranList as $ta)
                                            <option value="{{ $ta }}" {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                                                {{ $ta }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="d-none"></button>
                            </form>
                        </div>

                        {{-- KONTEN REKAPITULASI --}}
                        <div class="p-4 pt-0"> 
                            
                            @if(!$request->id_kelas || !$request->id_mapel || !$request->tahun_ajaran || !$request->semester)
                                <p class="text-secondary mt-3 p-3 text-center border rounded bg-gray-100">
                                    Silakan pilih **Kelas, Mata Pelajaran, Semester, dan Tahun Ajaran** di atas.
                                </p>

                            @elseif($siswa->isEmpty())
                                <p class="text-danger mt-3 p-3 text-center border rounded bg-gray-100">
                                    Tidak ada siswa ditemukan di kelas yang dipilih.
                                </p>
                            
                            @else
                                {{-- ALERT: KEMBALI KE DESAIN LAMA (ORANGE) --}}
                                <div style="background-color: #ff7b00 !important;" class="alert text-white text-sm shadow-sm mt-3">
                                    <i class="fas fa-info-circle me-1"></i> <strong>Informasi Bobot Nilai:</strong><br>
                                    @if($bobotInfo)
                                        Min <strong>{{ $bobotInfo->jumlah_sumatif ?? '0' }}</strong> Nilai Sumatif | 
                                        Bobot Sumatif: <strong>{{ $bobotInfo->bobot_sumatif }}%</strong> | 
                                        Bobot Project: <strong>{{ $bobotInfo->bobot_project }}%</strong>.
                                    @else
                                        Pengaturan Bobot belum tersedia.
                                    @endif
                                </div>
                                
                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0 table-sm-custom">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 ps-3 text-center bg-secondary" style="width: 5%">No</th>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 bg-secondary" style="width: 20%">Nama Siswa</th>
                                                
                                                {{-- SUMATIF --}}
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">S1</th>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">S2</th>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">S3</th>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">S4</th>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">S5</th>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">Rata</th>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-primary">Nilai</th>

                                                {{-- PROJECT --}}
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-success">Proj</th>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-success">Nilai</th>
                                                
                                                {{-- AKHIR --}}
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-danger">NA</th>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 ps-2 bg-secondary">Capaian Akhir</th>
                                                <th class="text-uppercase text-white text-xxs font-weight-bolder opacity-9 text-center bg-secondary">Status</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($siswa as $i => $s)
                                            @php
                                                $rekapSiswa = $rekap[$s->id_siswa] ?? [];
                                                $nilaiAkhir = $rekapSiswa['nilai_akhir'] ?? '-';
                                                $status = $rekapSiswa['status_data'] ?? 'draft';
                                                $capaian = $rekapSiswa['capaian_akhir'] ?? 'Belum tersedia.';
                                            @endphp
                                            <tr class="border-b hover:bg-gray-50">

                                                <td class="text-center">{{ $i + 1 }}</td>
                                                <td class="fw-bold text-dark">{{ $s->nama_siswa }}</td>

                                                {{-- Data Sumatif --}}
                                                <td class="text-center">{{ $rekapSiswa['s1'] ?? '-' }}</td>
                                                <td class="text-center">{{ $rekapSiswa['s2'] ?? '-' }}</td>
                                                <td class="text-center">{{ $rekapSiswa['s3'] ?? '-' }}</td>
                                                <td class="text-center">{{ $rekapSiswa['s4'] ?? '-' }}</td>
                                                <td class="text-center">{{ $rekapSiswa['s5'] ?? '-' }}</td>
                                                <td class="text-center font-weight-bold">{{ $rekapSiswa['rata_sumatif'] ?? '-' }}</td>
                                                <td class="text-center font-weight-bold text-primary">{{ $rekapSiswa['bobot_sumatif'] ?? '-' }}</td>

                                                {{-- Data Project --}}
                                                <td class="text-center">{{ $rekapSiswa['nilai_project'] ?? '-' }}</td>
                                                <td class="text-center font-weight-bold text-success">{{ $rekapSiswa['bobot_project'] ?? '-' }}</td>
                                                
                                                {{-- Nilai Akhir --}}
                                                <td class="text-center">
                                                    <span class="badge bg-gradient-danger text-xs">{{ $nilaiAkhir }}</span>
                                                </td>
                                                
                                                {{-- Capaian Akhir (Truncate + Tooltip) --}}
                                                {{-- PENTING: data-bs-container="body" agar tooltip tidak terpotong tabel --}}
                                                <td class="truncate-text" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-container="body" 
                                                    title="{{ $capaian }}">
                                                    {{ $capaian }}
                                                </td>

                                                {{-- Status Data --}}
                                                <td class="text-center">
                                                    @if($status == 'final')
                                                        <span class="badge badge-sm bg-gradient-success">FINAL</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-secondary">DRAFT</span>
                                                    @endif
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

{{-- SCRIPT INISIALISASI TOOLTIP BOOTSTRAP 5 --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection