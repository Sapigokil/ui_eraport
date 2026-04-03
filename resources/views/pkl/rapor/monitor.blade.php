{{-- File: resources/views/pkl/rapor/monitor.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Monitoring Kesiapan Rapor PKL')

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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Kelas PKL</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['total_rombel'] }} <span class="text-xs font-weight-normal">Rombel</span></h5>
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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Kesiapan Rapor PKL</p>
                                    <h5 class="font-weight-bolder mb-0">
                                        {{ $stats['persen_global'] }}% 
                                        <span class="text-xs font-weight-normal text-secondary">({{ $stats['siswa_final'] }}/{{ $stats['siswa_total'] }} Siswa)</span>
                                    </h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                @if($stats['persen_global'] == 100 && $stats['siswa_total'] > 0)
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
                <form action="{{ route('pkl.rapor.monitoring.index') }}" method="GET" class="card shadow-sm h-100">
                    <div class="card-body p-3 d-flex align-items-center justify-content-end gap-2">
                         <span class="text-xs fw-bold text-uppercase text-secondary me-2">Filter Periode:</span>
                         <select name="semester" class="form-select form-select-sm fw-bold border" style="max-width: 120px;" onchange="this.form.submit()">
                            <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Ganjil</option>
                            <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Genap</option>
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
                                <i class="fas fa-tasks me-2"></i> Monitoring Penilaian PKL Per Kelas
                            </h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        
                        <div class="accordion accordion-flush px-3" id="accordionRapor">
                            @forelse($monitoringData as $index => $data)
                                <div class="accordion-item mb-3 border rounded shadow-xs">
                                    <h2 class="accordion-header" id="head{{ $index }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#clps{{ $index }}" data-kelas-id="{{ $data->kelas->id_kelas }}">
                                            
                                            {{-- ✅ PERBAIKAN: MENGGUNAKAN GRID ROW & COL AGAR STATIS DAN SEJAJAR --}}
                                            <div class="row w-100 align-items-center m-0 pe-3">
                                                
                                                {{-- INFO KELAS (Porsi 4/12) --}}
                                                <div class="col-12 col-lg-4 d-flex align-items-center px-0">
                                                    <div class="icon icon-sm shadow border-radius-md bg-white text-center me-3 d-flex align-items-center justify-content-center border flex-shrink-0">
                                                        <i class="fas fa-chalkboard-teacher text-dark text-xs"></i>
                                                    </div>
                                                    <div class="d-flex flex-column text-truncate">
                                                        <span class="font-weight-bold text-dark text-truncate">{{ $data->kelas->nama_kelas }}</span>
                                                        <span class="text-xs text-secondary text-truncate">Wali: {{ $data->wali_kelas }}</span>
                                                    </div>
                                                </div>

                                                {{-- PROGRESS BAR STACKED MULTICOLOR (Porsi 6/12 - Terkunci Lebarnya) --}}
                                                <div class="col-lg-6 d-none d-lg-block px-3">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-xxs font-weight-bold text-uppercase text-secondary">Progress Penilaian</span>
                                                        <span class="text-xxs font-weight-bold text-dark">
                                                            <span class="text-success">{{ $data->siswa_selesai }} Final</span> | 
                                                            <span class="text-warning">{{ $data->siswa_proses }} Draft</span> | 
                                                            <span class="text-secondary">{{ $data->siswa_kosong }} Kosong</span>
                                                        </span>
                                                    </div>
                                                    <div class="progress w-100" style="height: 8px; background-color: #e9ecef; overflow: hidden;">
                                                        @if($data->persen_selesai > 0)
                                                            <div class="progress-bar bg-gradient-success" role="progressbar" style="width: {{ $data->persen_selesai }}%" data-bs-toggle="tooltip" title="{{ $data->siswa_selesai }} Siswa Selesai ({{ $data->persen_selesai }}%)"></div>
                                                        @endif
                                                        @if($data->persen_proses > 0)
                                                            <div class="progress-bar bg-gradient-warning" role="progressbar" style="width: {{ $data->persen_proses }}%" data-bs-toggle="tooltip" title="{{ $data->siswa_proses }} Siswa Proses Draft ({{ $data->persen_proses }}%)"></div>
                                                        @endif
                                                        @if($data->persen_kosong > 0)
                                                            <div class="progress-bar bg-secondary opacity-4" role="progressbar" style="width: {{ $data->persen_kosong }}%" data-bs-toggle="tooltip" title="{{ $data->siswa_kosong }} Siswa Belum Dinilai ({{ $data->persen_kosong }}%)"></div>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- BADGE STATUS GLOBAL KELAS (Porsi 2/12) --}}
                                                <div class="col-12 col-lg-2 text-end px-0 mt-2 mt-lg-0">
                                                    @if($data->persen_selesai == 100)
                                                        <span class="badge bg-gradient-success w-100 w-lg-auto">SIAP CETAK</span>
                                                    @else
                                                        <span class="badge bg-gradient-secondary w-100 w-lg-auto">BELUM LENGKAP</span>
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
                                                        <i class="fas fa-book me-1"></i> Penilaian Pembimbing
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="catatan-tab-{{ $index }}" data-bs-toggle="tab" href="#catatan-{{ $index }}" role="tab">
                                                        <i class="fas fa-clipboard-check me-1"></i> Catatan & Kehadiran
                                                    </a>
                                                </li>
                                            </ul>

                                            <div class="tab-content" id="content{{ $index }}">
                                                
                                                {{-- 1. TAB NILAI PKL --}}
                                                <div class="tab-pane fade show active" id="nilai-{{ $index }}" role="tabpanel">
                                                    <div class="table-responsive p-0 border rounded">
                                                        <table class="table align-items-center mb-0 bg-white">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Nama Siswa</th>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Guru Pembimbing</th>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tempat Industri</th>
                                                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Nilai</th>
                                                                    <th class="text-secondary opacity-7"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($data->detail_siswa as $siswa)
                                                                <tr>
                                                                    <td class="px-3 py-2">
                                                                        <h6 class="mb-0 text-sm text-dark">{{ $siswa['nama_siswa'] }}</h6>
                                                                        <span class="text-xs text-secondary">{{ $siswa['nisn'] }}</span>
                                                                    </td>
                                                                    <td class="py-2">
                                                                        <span class="text-xs font-weight-bold text-dark">{{ $siswa['guru'] }}</span>
                                                                    </td>
                                                                    <td class="py-2">
                                                                        <span class="text-xs text-secondary d-block text-wrap" style="max-width: 200px;">{{ $siswa['tempat'] }}</span>
                                                                    </td>
                                                                    <td class="align-middle text-center py-2">
                                                                        @if($siswa['status'] == 'lengkap')
                                                                            <span class="badge badge-sm bg-gradient-success">FINAL</span>
                                                                        @elseif($siswa['status'] == 'proses')
                                                                            <span class="badge badge-sm bg-gradient-warning">DRAFT</span>
                                                                        @else
                                                                            <span class="badge badge-sm bg-gradient-danger">KOSONG</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="align-middle text-center py-2">
                                                                        <a href="{{ route('pkl.nilai.input', [
                                                                            'tahun_ajaran' => $tahun_ajaran, 
                                                                            'semester' => $semester, 
                                                                            'id_guru' => $siswa['id_guru'],
                                                                            'id_penempatan' => $siswa['id_penempatan']
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

                                                {{-- 2. TAB CATATAN & KEHADIRAN --}}
                                                <div class="tab-pane fade" id="catatan-{{ $index }}" role="tabpanel">
                                                    <div class="table-responsive p-0 border rounded">
                                                        <table class="table align-items-center mb-0 bg-white">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 25%">Nama Siswa</th>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="width: 25%">Tempat Industri</th>
                                                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 15%">Kehadiran (S/I/A)</th>
                                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Catatan Pembimbing</th>
                                                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Data</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($data->detail_siswa as $siswa)
                                                                <tr>
                                                                    <td class="px-3 py-2">
                                                                        <h6 class="mb-0 text-sm text-dark">{{ $siswa['nama_siswa'] }}</h6>
                                                                    </td>
                                                                    <td class="py-2 align-top">
                                                                        <span class="text-xs text-dark d-block text-wrap" style="max-width: 180px;">{{ $siswa['tempat'] }}</span>
                                                                    </td>
                                                                    <td class="align-middle text-center py-2">
                                                                        <span class="badge badge-sm bg-light text-dark border">
                                                                            S:{{ $siswa['sakit'] }} / I:{{ $siswa['ijin'] }} / A:{{ $siswa['alpha'] }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="py-2 align-top">
                                                                        <span class="text-xs font-weight-bold text-dark" title="{{ $siswa['catatan_full'] }}" data-bs-toggle="tooltip">
                                                                            {{ $siswa['catatan_short'] }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="align-middle text-center py-2">
                                                                        @if($siswa['status'] == 'lengkap')
                                                                            <i class="fas fa-check-circle text-success text-lg" title="Sudah Final"></i>
                                                                        @else
                                                                            <i class="fas fa-times-circle text-danger text-lg" title="Belum Lengkap"></i>
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
                            @empty
                                <div class="text-center py-5 text-secondary">
                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-5"></i><br>
                                    Tidak ada data penempatan PKL pada periode ini.
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

<script>
    // 1. Aktifkan Tooltip Bootstrap
    document.addEventListener("DOMContentLoaded", function(event) { 
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // 2. FITUR CERDAS: Tangkap parameter ?buka_kelas dari URL dan otomatis klik Accordion-nya!
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const bukaKelasId = urlParams.get('buka_kelas');

        if (bukaKelasId) {
            let targetButton = $('.accordion-button[data-kelas-id="' + bukaKelasId + '"]');
            if (targetButton.length > 0) {
                targetButton.click(); 
                setTimeout(() => {
                    targetButton[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        }
    });
</script>
@endsection