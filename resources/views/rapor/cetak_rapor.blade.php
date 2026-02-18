@extends('layouts.app') 

@section('page-title', 'Cetak Rapor Siswa')

@section('content')

@php
    // --- 1. LOGIKA PERIODE (TETAP) ---
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

    // --- 2. HITUNG STATISTIK DI VIEW ---
    $totalSiswa = isset($finalSiswaList) ? count($finalSiswaList) : 0;
    
    // Hitung yang statusnya 'final' atau 'cetak'
    $finalCount = isset($finalSiswaList) ? $finalSiswaList->whereIn('status_rapor', ['final', 'cetak'])->count() : 0;
    
    // Hitung yang sudah ada datanya (draft/final/cetak) - selain belum_generate
    $rawCount   = isset($finalSiswaList) ? $finalSiswaList->where('status_rapor', '!=', 'belum_generate')->count() : 0;

    $persenFinal = $totalSiswa > 0 ? round(($finalCount / $totalSiswa) * 100) : 0;
    $persenRaw   = $totalSiswa > 0 ? round(($rawCount / $totalSiswa) * 100) : 0;
@endphp

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- CARD FILTER --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-body p-3">
                {{-- FIX ROUTE: rapornilai.cetak --}}
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
                                <h3 class="text-white font-weight-bold mb-1">{{ $kelasAktif->nama_kelas }}</h3>
                                <p class="text-white opacity-8 mb-2"><i class="fas fa-user-tie me-2"></i> Wali Kelas: {{ $kelasAktif->wali_kelas }}</p>
                                
                                <span class="badge border border-white text-white fw-bold bg-transparent">
                                    Semester {{ $selectedSemester }} - {{ $selectedTA }}
                                </span>
                            </div>
                            <div class="col-md-5 text-end mt-4 mt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-between gap-4">
                                    
                                    {{-- STAT 1: DATA MASUK --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Data Masuk</span>
                                        <h4 class="text-white mb-0">{{ $rawCount }} <span class="text-sm fw-normal opacity-8">/ {{ $totalSiswa }} Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $persenRaw }}%"></div>
                                        </div>
                                    </div>

                                    {{-- STAT 2: SIAP CETAK --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Siap Cetak</span>
                                        <h4 class="text-white mb-0">{{ $finalCount }} <span class="text-sm fw-normal opacity-8">/ {{ $totalSiswa }} Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $persenFinal }}%"></div>
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
                            
                            {{-- TOMBOL DOWNLOAD MASSAL --}}
                            @if($finalCount > 0)
                            {{-- FIX ROUTE: rapornilai.download_massal_merge --}}
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
                                        <th class="ps-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 30%">Nama Siswa</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Data</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Terakhir Update</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($finalSiswaList as $idx => $s)
                                    <tr>
                                        <td class="text-center text-sm text-secondary">{{ $idx + 1 }}</td>
                                        <td class="ps-3">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $s->nama_siswa }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $s->nisn }}</p>
                                                
                                                @if($s->status_siswa == 'history_moved')
                                                    <span class="badge badge-xxs bg-gradient-secondary mt-1 w-auto" style="width: fit-content;">
                                                        <i class="fas fa-history me-1"></i> Data Arsip (Mutasi/Alumni)
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- STATUS DATA --}}
                                        <td class="text-center align-middle">
                                            @if($s->status_rapor == 'belum_generate')
                                                <span class="badge badge-sm bg-gradient-light text-secondary border">BELUM ADA</span>
                                            @elseif($s->status_rapor == 'draft')
                                                <span class="badge badge-sm bg-gradient-info">DRAFT</span>
                                            @elseif($s->status_rapor == 'final')
                                                <span class="badge badge-sm bg-gradient-success">SIAP CETAK</span>
                                            @elseif($s->status_rapor == 'cetak')
                                                <span class="badge badge-sm bg-gradient-dark">SUDAH DICETAK</span>
                                            @endif
                                        </td>

                                        {{-- TANGGAL UPDATE --}}
                                        <td class="text-center align-middle">
                                            @if($s->last_update)
                                                <span class="text-xs font-weight-bold d-block">{{ \Carbon\Carbon::parse($s->last_update)->format('d M Y') }}</span>
                                                <span class="text-xxs text-secondary">{{ \Carbon\Carbon::parse($s->last_update)->format('H:i') }} WIB</span>
                                            @else
                                                <span class="text-xs text-secondary">-</span>
                                            @endif
                                        </td>

                                        {{-- AKSI --}}
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                
                                                @if($s->is_ready_print)
                                                    {{-- FIX ROUTE: rapornilai.cetak_proses --}}
                                                    <a href="{{ route('rapornilai.cetak_proses', $s->id_siswa) }}?semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}" 
                                                       target="_blank" class="btn btn-xs bg-gradient-primary mb-0 px-3" data-bs-toggle="tooltip" title="Cetak PDF">
                                                        <i class="fas fa-print me-1"></i> Cetak
                                                    </a>
                                                    
                                                    <button onclick="unlockRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" 
                                                            class="btn btn-xs btn-outline-danger mb-0 px-3" data-bs-toggle="tooltip" title="Buka Kunci (Kembali ke Draft)">
                                                        <i class="fas fa-lock-open"></i>
                                                    </button>

                                                    <button onclick="regenerateRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" 
                                                            class="btn btn-link text-warning text-xs mb-0 px-2" data-bs-toggle="tooltip" title="Update Nilai Terbaru">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>

                                                @elseif($s->status_rapor == 'draft')
                                                    
                                                    <button onclick="finalisasiRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" 
                                                            class="btn btn-xs bg-gradient-info mb-0 px-3">
                                                        <i class="fas fa-check-circle me-1"></i> Finalisasi
                                                    </button>

                                                @else
                                                    
                                                    <button onclick="generateRaporAdmin('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" 
                                                            class="btn btn-xs bg-gradient-secondary mb-0 px-3">
                                                        <i class="fas fa-cog me-1"></i> Generate
                                                    </button>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-secondary">
                                            <i class="fas fa-folder-open fa-2x mb-3 opacity-5"></i><br>
                                            Tidak ada data siswa ditemukan untuk periode ini.
                                        </td>
                                    </tr>
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
            <p class="text-sm text-secondary">Silakan gunakan filter di atas untuk menampilkan daftar siswa.</p>
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
                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
            }
        });
    }

    // 1. GENERATE ADMIN (FIX ROUTE: rapornilai.generate_rapor)
    function generateRaporAdmin(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Generate Rapor?',
            text: `Sistem akan menarik data nilai terbaru untuk ${namaSiswa}.`,
            icon: 'info', showCancelButton: true, confirmButtonText: 'Ya, Generate!', confirmButtonColor: '#344767'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('rapornilai.generate_rapor') }}", idSiswa); });
    }

    // 2. RE-GENERATE (FIX ROUTE: rapornilai.generate_rapor)
    function regenerateRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Update Nilai?',
            text: `Rapor ${namaSiswa} sudah Final. Update nilai akan menimpa data rapor yang ada. Lanjutkan?`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Update!', confirmButtonColor: '#fb8c00'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('rapornilai.generate_rapor') }}", idSiswa); });
    }

    // 3. UNLOCK RAPOR (FIX ROUTE: rapornilai.unlock_rapor)
    function unlockRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Buka Kunci?',
            text: `Status rapor ${namaSiswa} akan dikembalikan ke DRAFT agar bisa diedit kembali.`,
            icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Buka Kunci!', confirmButtonColor: '#ea0606'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('rapornilai.unlock_rapor') }}", idSiswa); });
    }

    // 4. FINALISASI RAPOR (FIX ROUTE: rapornilai.generate_rapor)
    function finalisasiRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Finalisasi Rapor?',
            text: `Pastikan nilai sudah benar. Status ${namaSiswa} akan diubah menjadi SIAP CETAK.`,
            icon: 'success', showCancelButton: true, confirmButtonText: 'Ya, Finalisasi!', confirmButtonColor: '#17ad37'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('rapornilai.generate_rapor') }}", idSiswa); });
    }
</script>
@endsection