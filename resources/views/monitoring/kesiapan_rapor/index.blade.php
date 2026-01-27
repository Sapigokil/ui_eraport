@extends('layouts.app') 

@section('page-title', 'Monitoring Kesiapan Rapor')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- HEADER STATISTIK --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Rombel</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['total_rombel'] }} <span class="text-xs font-weight-normal">Kelas</span></h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-dark shadow text-center border-radius-md">
                                    <i class="fas fa-school text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Kesiapan Rapor</p>
                                    <h5 class="font-weight-bolder mb-0">
                                        {{ $stats['persen_global'] }}% 
                                        <span class="text-xs font-weight-normal text-secondary">({{ $stats['mapel_final'] }}/{{ $stats['mapel_total'] }} Mapel)</span>
                                    </h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                @if($stats['persen_global'] == 100)
                                    <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                        <i class="fas fa-check-double text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                @else
                                    <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                        <i class="fas fa-exclamation-circle text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILTER --}}
            <div class="col-xl-5 col-sm-12">
                <form action="{{ route('rapornilai.monitoring.index') }}" method="GET" class="card shadow-sm h-100">
                    <div class="card-body p-3 d-flex align-items-center justify-content-end gap-2">
                         <span class="text-xs fw-bold text-uppercase text-secondary me-2">Filter Periode:</span>
                         <select name="semester" class="form-select form-select-sm fw-bold border" style="max-width: 120px;" onchange="this.form.submit()">
                            <option value="Ganjil" {{ $semester == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ $semester == 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                        <select name="tahun_ajaran" class="form-select form-select-sm fw-bold border" style="max-width: 140px;" onchange="this.form.submit()">
                            @foreach($tahunAjaranList as $ta)
                                <option value="{{ $ta }}" {{ $tahun_ajaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        {{-- LIST PER KELAS --}}
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-lg border-0">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3 mb-0">
                                <i class="fas fa-tasks me-2"></i> Monitoring Kesiapan Per Kelas
                            </h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        
                        <div class="accordion accordion-flush px-3" id="accordionRapor">
                            @foreach($monitoringData as $index => $data)
                                <div class="accordion-item mb-3 border rounded shadow-xs">
                                    <h2 class="accordion-header" id="head{{ $index }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#clps{{ $index }}">
                                            <div class="d-flex w-100 align-items-center justify-content-between pe-3">
                                                
                                                {{-- INFO KELAS --}}
                                                <div class="d-flex align-items-center" style="min-width: 220px;">
                                                    <div class="icon icon-sm shadow border-radius-md bg-white text-center me-3 d-flex align-items-center justify-content-center border">
                                                        <i class="fas fa-chalkboard-teacher text-dark text-xs"></i>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="font-weight-bold text-dark">{{ $data->kelas->nama_kelas }}</span>
                                                        <span class="text-xs text-secondary">Wali: {{ $data->wali_kelas }}</span>
                                                    </div>
                                                </div>

                                                {{-- PROGRESS BAR NILAI --}}
                                                <div class="flex-grow-1 mx-4 d-none d-lg-block">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-xxs font-weight-bold text-uppercase text-secondary">Nilai Mapel</span>
                                                        <span class="text-xxs font-weight-bold text-dark">{{ $data->mapel_selesai }} / {{ $data->jml_mapel }} Mapel</span>
                                                    </div>
                                                    <div class="progress w-100" style="height: 6px; background-color: #e9ecef;">
                                                        <div class="progress-bar bg-gradient-{{ $data->persen == 100 ? 'success' : 'info' }}" 
                                                             role="progressbar" style="width: {{ $data->persen }}%"></div>
                                                    </div>
                                                </div>

                                                {{-- PROGRESS BAR CATATAN --}}
                                                <div class="flex-grow-1 mx-4 d-none d-lg-block">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-xxs font-weight-bold text-uppercase text-secondary">Catatan Wali</span>
                                                        <span class="text-xxs font-weight-bold text-dark">{{ $data->persen_catatan }}% Siswa</span>
                                                    </div>
                                                    <div class="progress w-100" style="height: 6px; background-color: #e9ecef;">
                                                        <div class="progress-bar bg-gradient-{{ $data->persen_catatan == 100 ? 'success' : 'warning' }}" 
                                                             role="progressbar" style="width: {{ $data->persen_catatan }}%"></div>
                                                    </div>
                                                </div>

                                                {{-- BADGE STATUS --}}
                                                <div style="min-width: 100px; text-align: right;">
                                                    @if($data->persen == 100 && $data->persen_catatan == 100)
                                                        <span class="badge bg-gradient-success">SIAP CETAK</span>
                                                    @else
                                                        <span class="badge bg-gradient-secondary">BELUM LENGKAP</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </button>
                                    </h2>

                                    <div id="clps{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#accordionRapor">
                                        <div class="accordion-body p-3 bg-gray-50">
                                            
                                            {{-- TAB NAVIGASI --}}
                                            <ul class="nav nav-tabs mb-3" id="tab{{ $index }}" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="nilai-tab-{{ $index }}" data-bs-toggle="tab" href="#nilai-{{ $index }}" role="tab">
                                                        <i class="fas fa-book me-1"></i> Nilai Mapel
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="catatan-tab-{{ $index }}" data-bs-toggle="tab" href="#catatan-{{ $index }}" role="tab">
                                                        <i class="fas fa-user-edit me-1"></i> Catatan Wali Kelas
                                                    </a>
                                                </li>
                                            </ul>

                                            <div class="tab-content" id="content{{ $index }}">
                                                
                                                {{-- 1. TAB NILAI MAPEL --}}
                                                <div class="tab-pane fade show active" id="nilai-{{ $index }}" role="tabpanel">
                                                    <div class="table-responsive p-0">
                                                        <table class="table align-items-center mb-0 bg-white border-radius-md">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mapel</th>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Guru</th>
                                                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Progress Input</th>
                                                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                                    <th class="text-secondary opacity-7"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($data->detail as $m)
                                                                <tr>
                                                                    <td class="px-3 py-2">
                                                                        <h6 class="mb-0 text-sm text-dark">{{ $m['mapel'] }}</h6>
                                                                    </td>
                                                                    <td class="py-2">
                                                                        <p class="text-xs font-weight-bold mb-0 text-secondary">{{ $m['guru'] }}</p>
                                                                    </td>
                                                                    <td class="align-middle text-center py-2">
                                                                        <span class="text-secondary text-xs font-weight-bold">
                                                                            {{ $m['progress'] }} / {{ $m['total'] }} Siswa
                                                                        </span>
                                                                    </td>
                                                                    <td class="align-middle text-center py-2">
                                                                        @if($m['status'] == 'lengkap')
                                                                            <span class="badge badge-sm bg-gradient-success">FINAL</span>
                                                                        @elseif($m['status'] == 'proses')
                                                                            <span class="badge badge-sm bg-gradient-warning">PROSES ({{ $m['persen'] }}%)</span>
                                                                        @else
                                                                            <span class="badge badge-sm bg-gradient-danger">KOSONG</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="align-middle text-center py-2">
                                                                        <a href="{{ route('master.rekap.index', [
                                                                            'id_kelas' => $data->kelas->id_kelas, 
                                                                            'id_mapel' => $m['id_mapel'], 
                                                                            'semester' => $semester, 
                                                                            'tahun_ajaran' => $tahun_ajaran
                                                                        ]) }}" target="_blank" class="btn btn-link text-primary text-gradient px-3 mb-0">
                                                                            <i class="fas fa-eye me-1"></i> Cek
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                {{-- 2. TAB CATATAN WALI KELAS --}}
                                                <div class="tab-pane fade" id="catatan-{{ $index }}" role="tabpanel">
                                                    <div class="table-responsive p-0">
                                                        <table class="table align-items-center mb-0 bg-white border-radius-md">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 20%">Nama Siswa</th>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="width: 15%">Kokurikuler</th>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="width: 25%">Ekstrakurikuler</th>
                                                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 15%">Absensi (S/I/A)</th>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Catatan Wali</th>
                                                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($data->detail_catatan as $cat)
                                                                <tr>
                                                                    <td class="px-3 py-2">
                                                                        <h6 class="mb-0 text-sm text-dark">{{ $cat['nama_siswa'] }}</h6>
                                                                        <span class="text-xs text-secondary">{{ $cat['nisn'] }}</span>
                                                                    </td>
                                                                    <td class="py-2 align-top">
                                                                        <span class="text-xs text-dark d-block text-wrap" style="max-width: 150px;">{{ $cat['kokurikuler'] }}</span>
                                                                    </td>
                                                                    <td class="py-2 align-top">
                                                                        <span class="text-xs text-dark d-block text-wrap">{!! $cat['ekskul_html'] !!}</span>
                                                                    </td>
                                                                    <td class="align-middle text-center py-2">
                                                                        <span class="badge badge-sm bg-light text-dark border">
                                                                            S:{{ $cat['sakit'] }} / I:{{ $cat['ijin'] }} / A:{{ $cat['alpha'] }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="py-2 align-top">
                                                                        <span class="text-xs font-weight-bold text-dark" title="{{ $cat['catatan_full'] }}" data-bs-toggle="tooltip">
                                                                            {{ $cat['catatan_short'] }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="align-middle text-center py-2">
                                                                        @if($cat['status'] == 'ada')
                                                                            <i class="fas fa-check-circle text-success text-lg"></i>
                                                                        @else
                                                                            <i class="fas fa-times-circle text-danger text-lg"></i>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                            </div> {{-- End Tab Content --}}

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

<script>
    // Aktifkan Tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
@endsection