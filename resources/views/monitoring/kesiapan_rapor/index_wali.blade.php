@extends('layouts.app') 

@section('page-title', 'Rekap Kesiapan Rapor')

@section('content')

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- CARD FILTER --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-body p-3">
                <form action="{{ route('walikelas.monitoring.wali') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Pilih Kelas</label>
                        <select name="id_kelas" class="form-select border-secondary" onchange="this.form.submit()">
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id_kelas }}" {{ $selected_kelas_id == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }} ({{ $k->wali_kelas ?? 'Tanpa Wali' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Semester</label>
                        <select name="semester" class="form-select border-secondary" onchange="this.form.submit()">
                            <option value="Ganjil" {{ $semester == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ $semester == 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Tahun Ajaran</label>
                        <select name="tahun_ajaran" class="form-select border-secondary" onchange="this.form.submit()">
                            @foreach($tahunAjaranList as $ta)
                                <option value="{{ $ta }}" {{ $tahun_ajaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="submit" class="btn btn-primary w-100 mb-0"><i class="fas fa-sync-alt me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        @if(!$dataKelas)
            <div class="alert alert-warning text-white font-weight-bold" role="alert"><i class="fas fa-exclamation-triangle me-2"></i> Data kelas tidak ditemukan.</div>
        @else

        {{-- HEADER BANNER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-primary overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                        <i class="fas fa-clipboard-check text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="row align-items-center text-white">
                            <div class="col-md-7">
                                <h3 class="text-white font-weight-bold mb-1">{{ $dataKelas->kelas->nama_kelas }}</h3>
                                <p class="text-white opacity-8 mb-2"><i class="fas fa-user-tie me-2"></i> Wali Kelas: {{ $dataKelas->wali_kelas }}</p>
                                
                                <span class="badge border border-white text-white fw-bold bg-transparent">
                                    Semester {{ $semester }} - {{ $tahun_ajaran }}
                                </span>
                            </div>
                            <div class="col-md-5 text-end mt-4 mt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-between gap-4">
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Nilai Mapel</span>
                                        <h4 class="text-white mb-0">{{ $dataKelas->mapel_selesai }} <span class="text-sm fw-normal opacity-8">/ {{ $dataKelas->jml_mapel }} Mapel</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $dataKelas->persen }}%"></div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Catatan Wali</span>
                                        <h4 class="text-white mb-0">{{ $dataKelas->persen_catatan }}% <span class="text-sm fw-normal opacity-8">Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $dataKelas->persen_catatan }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- AREA AKSI / TRIGGER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border border-start-5 border-{{ $gate['color'] ?? 'primary' }}">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div class="pe-4">
                            <h5 class="mb-1 text-dark font-weight-bold"><i class="fas fa-file-signature me-2 text-primary"></i> Finalisasi Data Rapor</h5>
                            @if($gate['allowed'])
                                <p class="text-sm text-{{ $gate['color'] }} font-weight-bold mb-0"><i class="{{ $gate['icon'] }} me-1"></i> {{ $gate['message'] }}</p>
                            @else
                                <p class="text-sm text-danger font-weight-bold mb-0"><i class="{{ $gate['icon'] }} me-1"></i> {{ $gate['message'] }}</p>
                            @endif
                        </div>
                        <div>
                            {{-- FORM GENERATE --}}
                            <form id="formGenerateRapor" action="{{ route('walikelas.generate.rapor.walikelas') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id_kelas" value="{{ $dataKelas->kelas->id_kelas }}">
                                <input type="hidden" name="tahun_ajaran" value="{{ $tahun_ajaran }}">
                                <input type="hidden" name="semester" value="{{ $semester }}">
                                
                                @if($gate['allowed'])
                                    {{-- GANTI TYPE KE BUTTON DAN TAMBAH ONCLICK --}}
                                    <button type="button" onclick="prosesGenerate()" class="btn btn-{{ $gate['color'] }} bg-gradient-{{ $gate['color'] }} btn-lg mb-0 shadow-sm">
                                        <i class="fas fa-check-double me-2"></i> GENERATE RAPOR
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary btn-lg mb-0 cursor-not-allowed" disabled><i class="fas fa-lock me-2"></i> GENERATE TERKUNCI</button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KONTEN UTAMA --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border">
                    <div class="card-header p-3 pb-0">
                        <ul class="nav nav-tabs border-bottom-0" id="waliTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active font-weight-bold" id="nilai-tab" data-bs-toggle="tab" href="#nilai" role="tab"><i class="fas fa-book me-1 text-primary"></i> Status Nilai Mapel</a></li>
                            <li class="nav-item"><a class="nav-link font-weight-bold" id="catatan-tab" data-bs-toggle="tab" href="#catatan" role="tab"><i class="fas fa-user-edit me-1 text-warning"></i> Status Catatan & Absen</a></li>
                        </ul>
                    </div>

                    <div class="card-body p-0">
                        <div class="tab-content" id="waliTabContent">
                            <div class="tab-pane fade show active" id="nilai" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-items-center mb-0">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="ps-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mata Pelajaran</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Guru Pengampu</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Progress Input</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dataKelas->detail as $m)
                                            <tr>
                                                <td class="ps-3 py-3">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm text-dark">{{ $m['mapel'] }}</h6>
                                                        <span class="text-xs text-secondary">Kelompok {{ $m['kategori'] ?? '-' }}</span>
                                                    </div>
                                                </td>
                                                <td><p class="text-xs font-weight-bold mb-0 text-dark">{{ $m['guru'] }}</p></td>
                                                <td class="align-middle text-center">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <span class="me-2 text-xs font-weight-bold">{{ $m['persen'] }}%</span>
                                                        <div class="progress" style="width: 80px; height: 4px;">
                                                            <div class="progress-bar bg-gradient-{{ $m['persen'] >= 100 ? 'success' : 'info' }}" role="progressbar" style="width: {{ $m['persen'] }}%"></div>
                                                        </div>
                                                    </div>
                                                    <span class="text-xxs text-secondary">({{ $m['progress'] }} / {{ $m['total'] }} Siswa)</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    @if($m['status'] == 'final') <span class="badge badge-sm bg-gradient-success">FINAL</span>
                                                    @elseif($m['status'] == 'update') <span class="badge badge-sm bg-gradient-warning text-dark">UPDATE</span>
                                                    @elseif($m['status'] == 'ready') <span class="badge badge-sm bg-gradient-info">SIAP</span>
                                                    @elseif($m['status'] == 'proses') <span class="badge badge-sm bg-gradient-secondary">PROSES</span>
                                                    @else <span class="badge badge-sm bg-gradient-danger">KOSONG</span> @endif
                                                </td>
                                                <td class="align-middle text-center">
                                                    <a href="{{ route('master.rekap.index', ['id_kelas' => $dataKelas->kelas->id_kelas, 'id_mapel' => $m['id_mapel'], 'semester' => $semester, 'tahun_ajaran' => $tahun_ajaran]) }}" target="_blank" class="btn btn-xs btn-outline-primary mb-0" title="Lihat Detail"><i class="fas fa-search"></i></a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="catatan" role="tabpanel">
                                <div class="table-responsive p-0">
                                    <table class="table table-hover align-items-center mb-0">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="ps-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 20%;">Siswa</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 20%;">Kokurikuler</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 25%;">Ekstrakurikuler</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 15%;">Absensi</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Catatan</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 10%;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dataKelas->detail_catatan as $cat)
                                            <tr class="{{ $cat['status'] == 'kosong' ? 'bg-white' : '' }}">
                                                <td class="ps-3 py-3 align-top">
                                                    <div class="d-flex flex-column">
                                                        <h6 class="mb-0 text-sm {{ $cat['status'] == 'kosong' ? 'text-danger' : 'text-dark' }}">{{ $cat['nama_siswa'] }}</h6>
                                                        <span class="text-xs text-secondary">{{ $cat['nisn'] }}</span>
                                                    </div>
                                                </td>
                                                {{-- <td class="align-top py-3"><p class="text-xs font-weight-bold mb-0 text-dark text-wrap" style="max-width: 200px;">{{ $cat['kokurikuler'] }}</p></td> --}}
                                                <td class="align-top py-3"><span class="text-xs text-secondary text-wrap d-block" style="max-width: 200px;" data-bs-toggle="tooltip" title="{{ $cat['kokurikuler_full'] }}">{{ $cat['kokurikuler_short'] }}</span></td>
                                                <td class="align-top py-3"><span class="text-xs text-secondary d-block text-wrap">{!! $cat['ekskul_html'] !!}</span></td>
                                                <td class="align-middle text-center align-top py-3">
                                                    <span class="badge badge-sm bg-light text-dark border">S: <b class="text-danger">{{ $cat['sakit'] }}</b> | I: <b class="text-warning">{{ $cat['ijin'] }}</b> | A: <b class="text-dark">{{ $cat['alpha'] }}</b></span>
                                                </td>
                                                <td class="align-top py-3"><span class="text-xs text-secondary text-wrap d-block" style="max-width: 200px;" data-bs-toggle="tooltip" title="{{ $cat['catatan_full'] }}">{{ $cat['catatan_short'] }}</span></td>
                                                <td class="align-middle text-center align-top py-3">
                                                    @if($cat['status'] == 'final') <span class="badge badge-sm bg-gradient-success">FINAL</span>
                                                    @elseif($cat['status'] == 'update') <span class="badge badge-sm bg-gradient-warning text-dark">UPDATE</span>
                                                    @elseif($cat['status'] == 'ready') <span class="badge badge-sm bg-gradient-info">SIAP</span>
                                                    @elseif($cat['status'] == 'proses') <span class="badge badge-sm bg-gradient-secondary">PROSES</span>
                                                    @else <span class="badge badge-sm bg-gradient-danger">KOSONG</span> @endif
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
            </div>
        </div>
        @endif
    </div>
    <x-app.footer />
</main>

{{-- OVERLAY LOADING (DI LUAR MAIN) --}}
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; color: white; font-size: 1.5rem; z-index: 999999;">
    <div class="d-flex flex-column align-items-center">
        <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status"></div> 
        <span>Sedang memproses...</span>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Tooltip Init
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) })

    // FUNGSI MANUAL UNTUK GENERATE
    function prosesGenerate() {
        if (confirm('Apakah Anda yakin data sudah lengkap? Data Rapor akan digenerate dan dikunci.')) {
            // 1. Tampilkan Overlay (Pakai display flex agar center)
            $('#loadingOverlay').attr('style', 'display: flex !important; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; color: white; font-size: 1.5rem; z-index: 999999;');
            
            // 2. Submit Form Secara Programmatik
            setTimeout(function() {
                document.getElementById('formGenerateRapor').submit();
            }, 100); // Delay kecil agar browser sempat render overlay
        }
    }

    $(document).ready(function() {
        // Overlay untuk form lain (selain generate) jika ada
        $('form').on('submit', function() {
            if($(this).attr('method') === 'POST' && $(this).attr('id') !== 'formGenerateRapor'){
                $('#loadingOverlay').attr('style', 'display: flex !important; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; color: white; font-size: 1.5rem; z-index: 999999;');
            }
        });
    });
</script>

@endsection