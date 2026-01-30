@extends('layouts.app') 

@section('page-title', 'Cetak Rapor Siswa')

@section('content')

@php
    $tahunSekarang = date('Y');
    $bulanSekarang = date('n');
    if ($bulanSekarang < 7) {
        $defaultTA1 = $tahunSekarang - 1; $defaultTA2 = $tahunSekarang; $defaultSemester = 'Genap';
    } else {
        $defaultTA1 = $tahunSekarang; $defaultTA2 = $tahunSekarang + 1; $defaultSemester = 'Ganjil';
    }
    $defaultTahunAjaran = $defaultTA1 . '/' . $defaultTA2;
    $selectedTA = $tahun_ajaran ?? $defaultTahunAjaran;
    $selectedSemester = $semesterRaw ?? $defaultSemester;
    
    $tahunAjaranList = [];
    for ($tahun = $tahunSekarang + 1; $tahun >= $tahunSekarang - 3; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }
    $semesterList = ['Ganjil', 'Genap']; 
@endphp

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- CARD FILTER --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-body p-3">
                <form action="{{ route('rapornilai.cetak') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Pilih Kelas</label>
                        <select name="id_kelas" class="form-select border-secondary" required onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas', $id_kelas) == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }} ({{ $k->wali_kelas ?? 'Tanpa Wali' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Semester</label>
                        <select name="semester" class="form-select border-secondary" onchange="this.form.submit()">
                            @foreach($semesterList as $smt)
                                <option value="{{ $smt }}" {{ request('semester', $selectedSemester) == $smt ? 'selected' : '' }}>{{ $smt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Tahun Ajaran</label>
                        <select name="tahun_ajaran" class="form-select border-secondary" onchange="this.form.submit()">
                            @foreach($tahunAjaranList as $ta)
                                <option value="{{ $ta }}" {{ request('tahun_ajaran', $selectedTA) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="submit" class="btn btn-primary w-100 mb-0"><i class="fas fa-sync-alt me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        @if($id_kelas && $kelasAktif)
        
        {{-- HEADER BANNER (HASIL COPY DARI MONITORING WALI) --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-primary overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                        <i class="fas fa-clipboard-check text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="row align-items-center text-white">
                            <div class="col-md-7">
                                <h3 class="text-white font-weight-bold mb-1">{{ $kelasAktif->nama_kelas }}</h3>
                                <p class="text-white opacity-8 mb-2"><i class="fas fa-user-tie me-2"></i> Wali Kelas: {{ $kelasAktif->wali_kelas }}</p>
                                
                                <span class="badge border border-white text-white fw-bold bg-transparent">
                                    Semester {{ $semesterRaw }} - {{ $tahun_ajaran }}
                                </span>
                            </div>
                            <div class="col-md-5 text-end mt-4 mt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-between gap-4">
                                    
                                    {{-- STAT 1: INPUTAN GURU (RAW) --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Data Lengkap (Raw)</span>
                                        <h4 class="text-white mb-0">{{ $stats['raw_count'] }} <span class="text-sm fw-normal opacity-8">/ {{ $stats['total_siswa'] }} Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $stats['persen_raw'] }}%"></div>
                                        </div>
                                    </div>

                                    {{-- STAT 2: SIAP CETAK (SNAPSHOT) --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Siap Cetak (Final)</span>
                                        <h4 class="text-white mb-0">{{ $stats['final_count'] }} <span class="text-sm fw-normal opacity-8">/ {{ $stats['total_siswa'] }} Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $stats['persen_final'] }}%"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KONTEN TABEL SISWA --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border">
                    <div class="card-header p-3 bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-list-ul me-2"></i> Daftar Siswa</h6>
                            
                            {{-- TOMBOL DOWNLOAD MASSAL (MUNCUL JIKA ADA DATA FINAL) --}}
                            @if($stats['final_count'] > 0)
                            {{-- <a href="{{ route('rapornilai.download_massal_pdf') }}?id_kelas={{ $id_kelas }}&semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}" 
                               class="btn btn-sm btn-outline-primary mb-0" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> Download Semua PDF
                            </a> --}}
                            <a href="{{ route('rapornilai.download_massal_merge') }}?id_kelas={{ $id_kelas }}&semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}" 
                               class="btn btn-sm btn-outline-primary mb-0" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> Download & Merge PDF
                            </a>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                        <th class="ps-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 25%">Nama Siswa</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kelengkapan Data</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal Cetak</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Rapor</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($siswaList as $idx => $s)
                                    <tr>
                                        <td class="text-center text-sm text-secondary">{{ $idx + 1 }}</td>
                                        <td class="ps-3">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $s->nama_siswa }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $s->nisn ?? $s->nipd }}</p>
                                                {{-- LINK DETAIL --}}
                                                <a class="text-xs text-primary font-weight-bold cursor-pointer mt-1" 
                                                   data-bs-toggle="collapse" href="#detail-{{ $s->id_siswa }}">
                                                    <i class="fas fa-chevron-down me-1"></i> Detail
                                                </a>
                                            </div>
                                        </td>

                                        {{-- KOLOM KELENGKAPAN --}}
                                        <td class="text-center align-middle">
                                            @if($s->snapshot_status != 'kosong')
                                                <span class="badge badge-sm bg-gradient-success">LENGKAP</span>
                                            @elseif($s->raw_status == 'lengkap')
                                                <span class="badge badge-sm bg-gradient-warning text-dark">BELUM REKAP</span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-danger">BELUM ADA</span>
                                            @endif
                                        </td>

                                        {{-- KOLOM TANGGAL CETAK --}}
                                        <td class="text-center align-middle">
                                            @if($s->snapshot_status == 'cetak' && $s->tanggal_cetak)
                                                <span class="text-xs font-weight-bold d-block">{{ \Carbon\Carbon::parse($s->tanggal_cetak)->format('d M Y') }}</span>
                                                {{-- <span class="text-xxs text-secondary">{{ \Carbon\Carbon::parse($s->tanggal_cetak)->format('H:i') }} WIB</span> --}}
                                            @else
                                                <span class="text-xs text-secondary">-</span>
                                            @endif
                                        </td>

                                        {{-- KOLOM STATUS --}}
                                        <td class="text-center align-middle">
                                            @if($s->snapshot_status == 'cetak')
                                                <span class="badge badge-sm bg-gradient-dark">SUDAH DICETAK</span>
                                            @elseif($s->snapshot_status == 'final')
                                                <span class="badge badge-sm bg-gradient-primary">SIAP CETAK</span>
                                            @elseif($s->snapshot_status == 'draft')
                                                <span class="badge badge-sm bg-gradient-secondary">DRAFT</span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-light text-secondary border">BELUM GENERATE</span>
                                            @endif
                                        </td>

                                        {{-- KOLOM AKSI --}}
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                @if($s->can_print_unlock)
                                                    {{-- CETAK --}}
                                                    <a href="{{ route('rapornilai.cetak_proses', $s->id_siswa) }}?semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}" 
                                                       target="_blank" class="btn btn-xs bg-gradient-info mb-0 px-3" data-bs-toggle="tooltip" title="Cetak PDF">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    {{-- UNLOCK --}}
                                                    <button onclick="unlockRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" 
                                                            class="btn btn-xs btn-outline-danger mb-0 px-3" data-bs-toggle="tooltip" title="Buka Kunci">
                                                        <i class="fas fa-lock-open"></i>
                                                    </button>
                                                @elseif($s->is_draft)
                                                    {{-- FINALISASI --}}
                                                    <button onclick="finalisasiRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" 
                                                            class="btn btn-xs bg-gradient-primary mb-0 px-3">
                                                        <i class="fas fa-check-circle me-1"></i> Finalisasi
                                                    </button>
                                                @elseif($s->can_generate)
                                                    {{-- GENERATE ADMIN --}}
                                                    <button onclick="generateRaporAdmin('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" 
                                                            class="btn btn-xs bg-gradient-warning mb-0 px-3">
                                                        <i class="fas fa-bolt me-1"></i> Generate
                                                    </button>
                                                @else
                                                    <span class="text-xs text-secondary fst-italic">Tunggu Wali Kelas</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- ROW DETAIL (COLLAPSE) --}}
                                    <tr>
                                        <td colspan="6" class="p-0 border-0">
                                            <div class="collapse bg-gray-50" id="detail-{{ $s->id_siswa }}">
                                                <div class="row p-3">
                                                    {{-- DETAIL MAPEL --}}
                                                    <div class="col-md-7 border-end">
                                                        <h6 class="text-xs font-weight-bold text-uppercase text-secondary mb-2 ms-2">Detail Mapel ({{ count($s->detail_mapel) }})</h6>
                                                        <div class="table-responsive bg-white border-radius-md shadow-xs mx-2" style="max-height: 300px; overflow-y: auto;">
                                                            <table class="table table-sm mb-0 align-middle">
                                                                <thead class="bg-light sticky-top">
                                                                    <tr>
                                                                        <th class="text-xs ps-3 text-secondary font-weight-bold">Mapel</th>
                                                                        <th class="text-center text-xs text-secondary font-weight-bold">Angka</th>
                                                                        <th class="text-center text-xs text-secondary font-weight-bold">Nilai</th>
                                                                        <th class="text-center text-xs text-secondary font-weight-bold">Rekap</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($s->detail_mapel as $dm)
                                                                    <tr>
                                                                        {{-- Nama Mapel --}}
                                                                        <td class="text-xs ps-3 text-dark">{{ $dm['mapel'] }}</td>
                                                                        
                                                                        {{-- Nilai (Angka Bulat) --}}
                                                                        <td class="text-center text-xs font-weight-bold text-dark">
                                                                            {{ $dm['nilai_score'] }}
                                                                        </td>

                                                                        {{-- Status Nilai (Raw) --}}
                                                                        <td class="text-center">
                                                                            @if($dm['ada_nilai']) 
                                                                                <span class="badge badge-xxs bg-success"><i class="fas fa-check"></i></span> 
                                                                            @else 
                                                                                <span class="badge badge-xxs bg-danger"><i class="fas fa-times"></i></span> 
                                                                            @endif
                                                                        </td>

                                                                        {{-- Status Rekap (Snap) --}}
                                                                        <td class="text-center">
                                                                            @if($dm['ada_rekap']) 
                                                                                <span class="badge badge-xxs bg-success"><i class="fas fa-check"></i></span> 
                                                                            @else 
                                                                                <span class="badge badge-xxs bg-secondary"><i class="fas fa-minus"></i></span> 
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    {{-- DETAIL NON AKADEMIK --}}
                                                    <div class="col-md-5">
                                                        <h6 class="text-xs font-weight-bold text-uppercase text-secondary mb-2 ms-2">Info Non-Akademik</h6>
                                                        <div class="card card-body p-3 bg-white shadow-xs mx-2">
                                                            
                                                            {{-- Status Data --}}
                                                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                                                <span class="text-xs font-weight-bold text-secondary">Status Data:</span>
                                                                <div>
                                                                    <span class="badge badge-xxs {{ $s->detail_non_akademik['raw'] ? 'bg-success' : 'bg-danger' }}">Nilai</span>
                                                                    <span class="badge badge-xxs {{ $s->detail_non_akademik['snap'] ? 'bg-success' : 'bg-secondary' }}">Rekap</span>
                                                                </div>
                                                            </div>

                                                            {{-- Kokurikuler --}}
                                                            <div class="mb-2">
                                                                <span class="text-xs text-secondary d-block">Kokurikuler:</span>
                                                                <span class="text-xs text-dark font-weight-bold" 
                                                                      data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $s->detail_non_akademik['kokurikuler_full'] }}" style="cursor: pointer;">
                                                                    {{ $s->detail_non_akademik['kokurikuler_short'] }}
                                                                </span>
                                                            </div>

                                                            {{-- Ekstrakurikuler --}}
                                                            <div class="mb-2">
                                                                <span class="text-xs text-secondary d-block">Ekstrakurikuler:</span>
                                                                <ul class="mb-0 ps-3 text-xs text-dark font-weight-bold">
                                                                    @foreach($s->detail_non_akademik['ekskul_list'] as $eks)
                                                                        <li>{{ $eks }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>

                                                            {{-- Catatan Wali --}}
                                                            <div class="mb-2">
                                                                <span class="text-xs text-secondary d-block">Catatan Wali Kelas:</span>
                                                                <span class="text-xs text-dark font-weight-bold" 
                                                                      data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $s->detail_non_akademik['catatan_full'] }}" style="cursor: pointer;">
                                                                    {{ $s->detail_non_akademik['catatan_short'] }}
                                                                </span>
                                                            </div>

                                                            {{-- Absensi --}}
                                                            <div class="mt-2 pt-2 border-top text-center">
                                                                <span class="text-xs text-secondary">Sakit: <b class="text-dark">{{ $s->detail_non_akademik['sakit'] }}</b></span> | 
                                                                <span class="text-xs text-secondary">Ijin: <b class="text-dark">{{ $s->detail_non_akademik['izin'] }}</b></span> | 
                                                                <span class="text-xs text-secondary">Alpha: <b class="text-danger">{{ $s->detail_non_akademik['alpha'] }}</b></span>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center py-5">Tidak ada data siswa.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-6">
            <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-xl mb-3">
                <i class="fas fa-search fa-lg opacity-10" aria-hidden="true"></i>
            </div>
            <h5 class="mt-2">Pilih Kelas Terlebih Dahulu</h5>
        </div>
        @endif

    </div>
    <x-app.footer />
</main>

{{-- SCRIPT JAVASCRIPT --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) })

    // AJAX ACTION HELPER
    function actionAjax(url, idSiswa) {
        Swal.fire({title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
        $.ajax({
            url: url,
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id_siswa: idSiswa,
                id_kelas: "{{ $id_kelas }}", 
                semester: "{{ $selectedSemester }}",
                tahun_ajaran: "{{ $selectedTA }}"
            },
            success: function(res) {
                Swal.fire('Berhasil!', res.message || 'Sukses.', 'success').then(() => { location.reload(); });
            },
            error: function(xhr) {
                Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error');
            }
        });
    }

    // 1. GENERATE ADMIN
    function generateRaporAdmin(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Generate Admin?',
            text: `Anda akan mengambil alih generate rapor untuk ${namaSiswa}.`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Generate!', confirmButtonColor: '#fb8c00'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('rapornilai.generate_rapor') }}", idSiswa); });
    }

    // 2. UNLOCK RAPOR
    function unlockRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Buka Kunci?',
            text: `Rapor ${namaSiswa} akan kembali ke DRAFT.`,
            icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Buka!', confirmButtonColor: '#d33'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('rapornilai.unlock_rapor') }}", idSiswa); });
    }

    // 3. FINALISASI RAPOR
    function finalisasiRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Finalisasi?',
            text: `Ubah status ${namaSiswa} menjadi FINAL (Siap Cetak).`,
            icon: 'info', showCancelButton: true, confirmButtonText: 'Ya, Finalisasi!'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('rapornilai.generate_rapor') }}", idSiswa); });
    }
</script>
@endsection