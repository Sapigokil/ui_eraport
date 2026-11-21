@extends('layouts.app')

@section('page-title', 'Hasil Ujian - ' . $uji->judul)

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <style>
        .alert-warning {
            background-image: linear-gradient(310deg, #fb8c00 0%, #fdb03d 100%) !important;
            color: #fff !important; 
            border: none !important; 
        }
        .bg-gradient-danger {
            background-image: linear-gradient(310deg, #ea0606 0%, #ff667c 100%) !important;
            color: #fff !important;
        }
        .bg-gradient-success {
            background-image: linear-gradient(310deg, #17ad37 0%, #98ec2d 100%) !important;
            color: #fff !important;
        }
        .sort-header {
            color: #6c757d;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .sort-header:hover {
            color: #344767;
        }
        .skor-badge {
            font-size: 0.85rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 8px;
        }
        .skor-badge-sementara {
            font-size: 0.85rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 8px;
            background-color: #fff4e5;
            color: #b45309;
            border: 1px solid #f59e0b;
        }
        .badge-perlu-dinilai {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
            font-weight: 700;
        }
        .badge-pelanggaran-count {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            font-weight: 700;
        }
        .row-perlu-dinilai {
            background-color: #fffdf7 !important;
            border-left: 4px solid #fb8c00 !important;
        }
        .banner-perlu-dinilai {
            background-color: #fffaf0;
            border: 1px solid #fde68a;
            border-left: 4px solid #fb8c00 !important;
        }

        /* Perbaikan Tampilan Checkbox Tabel Hasil */
        .check-item-hasil, 
        #checkAllHasil {
            -webkit-appearance: checkbox !important;
            appearance: checkbox !important;
            width: 18px !important;
            height: 18px !important;
            border: 2px solid #8392ab !important;
            background-color: #ffffff !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            opacity: 1 !important;
            visibility: visible !important;
            accent-color: #5e72e4 !important;
        }

        .check-item-hasil:hover, 
        #checkAllHasil:hover {
            border-color: #5e72e4 !important;
        }

        .check-item-hasil:checked, 
        #checkAllHasil:checked {
            background-color: #5e72e4 !important;
            border-color: #5e72e4 !important;
        }

        /* Mencegah input date meluber pada layar sempit / half-desktop */
        .filter-date-input {
            min-width: 0 !important;
            width: 100%;
        }
    </style>

    <div class="container-fluid py-4 px-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- 1. HEADER BANNER UTAMA & DROPDOWN EXPORT --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3" style="z-index: 1050 !important;">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-visible position-relative">
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3 overflow-hidden" style="pointer-events: none;">
                                <i class="fas fa-poll-h text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-chart-bar me-2"></i> Hasil Asesmen: {{ $uji->judul }}
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Kode Uji: <strong>{{ strtoupper($uji->kode_uji) }}</strong> | Total Data Tampil: <strong>{{ $hasils->total() }} Peserta</strong>
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    {{-- DROPDOWN TOMBOL EXPORT REKAP DATA --}}
                                    <div class="dropdown" style="position: relative; z-index: 1060 !important;">
                                        <button class="btn btn-white text-primary dropdown-toggle btn-sm mb-0 shadow-sm" type="button" id="dropdownExport" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-file-export me-1"></i> Rekap Data
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" aria-labelledby="dropdownExport" style="z-index: 1070 !important;">
                                            <li>
                                                <a class="dropdown-item text-xs py-2 fw-bold" href="{{ route('sba.hasil.export', array_merge(['uji_id' => $uji->id, 'format' => 'excel'], request()->query())) }}">
                                                    <i class="fas fa-file-excel text-success me-2 fs-6"></i> Export to Excel (.xlsx/.csv)
                                                </a>
                                            </li>
                                            {{-- <li>
                                                <a class="dropdown-item text-xs py-2 fw-bold" href="{{ route('sba.hasil.export', array_merge(['uji_id' => $uji->id, 'format' => 'pdf'], request()->query())) }}" target="_blank">
                                                    <i class="fas fa-file-pdf text-danger me-2 fs-6"></i> Export to PDF (.pdf)
                                                </a>
                                            </li> --}}
                                        </ul>
                                    </div>

                                    <a href="{{ route('sba.index') }}" class="btn btn-outline-white text-white btn-sm mb-0">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-2 mt-2">
                        
                        {{-- ALERT SYSTEM --}}
                        @if (session('success'))
                            <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                <span class="text-sm"><strong>Sukses!</strong> {{ session('success') }}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        {{-- BANNER PENGINGAT PENILAIAN MANUAL (ESAI / PG TANPA KUNCI) --}}
                        @if($butuhPenilaianManual && $jumlahPerluDinilai > 0)
                            <div class="p-3 mb-3 rounded banner-perlu-dinilai d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 shadow-xs">
                                <div class="text-xs text-dark">
                                    <i class="fas fa-exclamation-triangle text-warning me-2 fs-6"></i>
                                    <strong>Perhatian Penilaian:</strong> Asesmen ini memiliki soal <strong>Esai</strong> atau <strong>PG/PGK tanpa kunci jawaban</strong>. Terdapat <span class="badge badge-perlu-dinilai">{{ $jumlahPerluDinilai }} peserta</span> yang skornya masih bersifat sementara dan menunggu penilaian manual guru.
                                </div>
                                <div class="flex-shrink-0">
                                    @if(request('status_pengerjaan') === 'selesai')
                                        <a href="{{ route('sba.hasil.index', $uji->id) }}" class="btn btn-xs btn-outline-secondary bg-white mb-0">
                                            <i class="fas fa-list me-1"></i> Tampilkan Semua Peserta
                                        </a>
                                    @else
                                        <a href="{{ route('sba.hasil.index', ['uji_id' => $uji->id, 'status_pengerjaan' => 'selesai']) }}" class="btn btn-xs bg-gradient-warning text-white fw-bold mb-0 shadow-xs">
                                            <i class="fas fa-filter me-1"></i> Filter Hanya yang Perlu Dinilai ({{ $jumlahPerluDinilai }})
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- PANEL FILTER AUTO-SUBMIT ONCHANGE & TOMBOL RESET (RESPONSIVE MOBILE & HALF-DESKTOP) --}}
                        <form id="filterForm" action="{{ route('sba.hasil.index', $uji->id) }}" method="GET" class="row g-2 mb-3 align-items-center">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'total_skor') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">

                            {{-- Filter Status Pengerjaan (Auto-Submit) --}}
                            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                                <select name="status_pengerjaan" class="form-select form-select-sm border px-2 py-1" onchange="this.form.submit()">
                                    <option value="">-- Semua Status --</option>
                                    <option value="berlangsung" {{ request('status_pengerjaan') == 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                                    <option value="selesai" {{ request('status_pengerjaan') == 'selesai' ? 'selected' : '' }}>
                                        {{ $butuhPenilaianManual ? 'Selesai (Perlu Dinilai)' : 'Selesai' }}
                                    </option>
                                    <option value="final" {{ request('status_pengerjaan') == 'final' ? 'selected' : '' }}>Final</option>
                                    <option value="gagal_pelanggaran" {{ request('status_pengerjaan') == 'gagal_pelanggaran' ? 'selected' : '' }}>Diskualifikasi</option>
                                </select>
                            </div>

                            {{-- Filter Kelas (Auto-Submit) --}}
                            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                                <select name="id_kelas" class="form-select form-select-sm border px-2 py-1" onchange="this.form.submit()">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($kelasList ?? [] as $kelasItem)
                                        <option value="{{ $kelasItem->id_kelas }}" {{ (string) request('id_kelas') === (string) $kelasItem->id_kelas ? 'selected' : '' }}>
                                            {{ $kelasItem->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Keysearch (NISN / ID Asesi / Nama) --}}
                            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                                <div class="input-group input-group-sm border rounded">
                                    <span class="input-group-text bg-transparent border-0 pe-1">
                                        <i class="fas fa-search text-secondary text-xs"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-0 ps-1" 
                                           placeholder="Cari NISN / Nama..." 
                                           value="{{ request('search') }}"
                                           onkeypress="if(event.key === 'Enter') this.form.submit()">
                                </div>
                            </div>

                            {{-- FILTER RENTANG TANGGAL PENGUMPULAN --}}
                            <div class="col-12 col-sm-6 col-lg-6 col-xl-3">
                                <div class="d-flex align-items-center gap-1">
                                    <input type="date" name="tgl_mulai" class="form-control form-control-sm border px-2 py-1 text-xs filter-date-input flex-grow-1" 
                                           value="{{ request('tgl_mulai') }}" title="Tanggal Pengumpulan Mulai" onchange="this.form.submit()">
                                    <span class="text-xs text-secondary flex-shrink-0 px-1">s/d</span>
                                    <input type="date" name="tgl_selesai" class="form-control form-control-sm border px-2 py-1 text-xs filter-date-input flex-grow-1" 
                                           value="{{ request('tgl_selesai') }}" title="Tanggal Pengumpulan Selesai" onchange="this.form.submit()">
                                </div>
                            </div>

                            {{-- Checkbox Identitas Ganda, Tombol Reset Filter & Per Page Selector --}}
                            <div class="col-12 col-lg-6 col-xl-3">
                                <div class="d-flex flex-wrap flex-sm-nowrap align-items-center justify-content-between justify-content-xl-end gap-2">
                                    <div class="form-check m-0 d-flex align-items-center flex-shrink-0">
                                        <input class="form-check-input border-secondary mt-0 me-1" type="checkbox" name="hide_duplicates" value="1" id="hideDuplicates" 
                                               {{ request('hide_duplicates') == '1' ? 'checked' : '' }} onchange="this.form.submit()">
                                        <label class="form-check-label text-xs font-weight-bold text-dark cursor-pointer mb-0 text-nowrap" for="hideDuplicates">
                                            Identitas Ganda
                                        </label>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <a href="{{ route('sba.hasil.index', $uji->id) }}" class="btn btn-sm btn-outline-secondary mb-0 px-2 text-nowrap" title="Reset Filter">
                                            <i class="fas fa-undo me-1"></i> Reset
                                        </a>

                                        <select name="per_page" class="form-select form-select-sm border px-2 py-1 w-auto flex-shrink-0" 
                                                style="padding-right: 1.8rem !important; min-width: 65px;" onchange="this.form.submit()">
                                            @foreach([10, 25, 50, 100] as $pageOption)
                                                <option value="{{ $pageOption }}" {{ request('per_page', 10) == $pageOption ? 'selected' : '' }}>
                                                    {{ $pageOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>

                        {{-- HELPER SORTING --}}
                        @php
                            function getSortUrlHasil($column) {
                                $currentSort = request('sort_by', 'total_skor');
                                $currentOrder = request('sort_order', 'desc');
                                $nextOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
                                return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_order' => $nextOrder]);
                            }

                            function getSortIconHasil($column) {
                                if (request('sort_by', 'total_skor') !== $column) {
                                    return '<i class="fas fa-sort opacity-4 ms-1 text-xxs"></i>';
                                }
                                return request('sort_order', 'desc') === 'asc' 
                                    ? '<i class="fas fa-sort-up ms-1 text-primary text-xs"></i>' 
                                    : '<i class="fas fa-sort-down ms-1 text-primary text-xs"></i>';
                            }
                        @endphp

                        {{-- FORM HAPUS MASSAL (AKSI MASSAL) --}}
                        <form id="formBulkDelete" action="{{ route('sba.hasil.bulk_delete', $uji->id) }}" method="POST">
                            @csrf
                            @method('DELETE')

                            {{-- TABEL HASIL PESERTA --}}
                            <div class="table-responsive p-0">
                                <table class="table align-items-center justify-content-center mb-0 border">
                                    <thead class="bg-light">
                                        <tr>
                                            {{-- CHECKBOX SELECT ALL MASSAL --}}
                                            <th class="text-center align-middle" width="3%">
                                                <input class="form-check-input border-secondary" type="checkbox" id="checkAllHasil" style="transform: scale(1.1); cursor: pointer;">
                                            </th>
                                            
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-start ps-2" width="4%">No</th>
                                            
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-start ps-3" width="13%">
                                                <a href="{{ getSortUrlHasil('asesi_id') }}" class="sort-header">
                                                    ID Asesi {!! getSortIconHasil('asesi_id') !!}
                                                </a>
                                            </th>
                                            
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-start ps-3" width="19%">Nama Peserta</th>
                                            
                                            {{-- KOLOM DURASI PENGERJAAN --}}
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="15%">Waktu & Durasi</th>

                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="14%">
                                                <a href="{{ getSortUrlHasil('total_skor') }}" class="sort-header justify-content-center">
                                                    Skor Akhir {!! getSortIconHasil('total_skor') !!}
                                                </a>
                                            </th>
                                            
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="15%">
                                                <a href="{{ getSortUrlHasil('status_pengerjaan') }}" class="sort-header justify-content-center">
                                                    Status / Ke {!! getSortIconHasil('status_pengerjaan') !!}
                                                </a>
                                            </th>
                                            
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="17%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hasils as $index => $hasil)
                                        @php
                                            $perluDinilai = $butuhPenilaianManual && ($hasil->status_pengerjaan === 'selesai');
                                            $jumlahPelanggaran = (int) ($hasil->jumlah_pelanggaran ?? 0);
                                        @endphp
                                        <tr class="{{ $perluDinilai ? 'row-perlu-dinilai' : '' }}">
                                            {{-- CHECKBOX PER BARIS --}}
                                            <td class="text-center align-middle">
                                                <input class="form-check-input border-secondary check-item-hasil" type="checkbox" name="ids[]" value="{{ $hasil->id }}" style="transform: scale(1.1); cursor: pointer;">
                                            </td>

                                            {{-- NO --}}
                                            <td class="text-start ps-2 text-sm align-middle">
                                                {{ ($hasils instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $hasils->firstItem() + $index : $loop->iteration }}
                                            </td>

                                            {{-- NISN / ID ASESI & KELAS --}}
                                            <td class="text-start ps-3 align-middle">
                                                @if($hasil->asesi_id)
                                                    <div class="d-flex flex-column align-items-start gap-1">
                                                        @if(!empty($hasil->nisn_display))
                                                            <span class="badge bg-gradient-dark text-xxs font-weight-bold" title="NISN Siswa">
                                                                NISN: {{ $hasil->nisn_display }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-gradient-dark text-xxs font-weight-bold">
                                                                USER #{{ $hasil->asesi_id }}
                                                            </span>
                                                        @endif

                                                        @if(!empty($hasil->nama_kelas_display))
                                                            <span class="text-xxs text-secondary font-weight-bold">
                                                                <i class="fas fa-chalkboard text-primary me-1"></i>{{ $hasil->nama_kelas_display }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary text-xxs font-weight-bold">
                                                        PUBLIK / GUEST
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- NAMA PESERTA --}}
                                            <td class="text-start ps-3 align-middle">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-user-circle text-primary me-2 text-sm"></i>
                                                    <div>
                                                        <h6 class="mb-0 text-sm font-weight-bold text-dark">
                                                            {{ $hasil->asesi ? $hasil->asesi->name : ($hasil->nama_peserta_publik ?? 'Peserta Tanpa Nama') }}
                                                        </h6>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- WAKTU & DURASI --}}
                                            <td class="align-middle text-center text-sm">
                                                <span class="text-xs text-dark font-weight-bold d-block">
                                                    {{ $hasil->waktu_mulai ? $hasil->waktu_mulai->format('d/m/Y H:i') : '-' }}
                                                </span>
                                                <span class="text-xxs text-secondary">
                                                    <i class="far fa-clock me-1 text-info"></i> Durasi: 
                                                    <strong>{{ $hasil->durasi_formatted }}</strong>
                                                </span>
                                            </td>

                                            {{-- SKOR AKHIR --}}
                                            <td class="align-middle text-center">
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    @if($hasil->status_pengerjaan === 'final')
                                                        <span class="skor-badge bg-gradient-primary text-white shadow-xs">
                                                            {{ number_format($hasil->total_skor, 0) }} Poin
                                                        </span>
                                                    @elseif($hasil->status_pengerjaan === 'selesai')
                                                        @if($perluDinilai)
                                                            <span class="skor-badge-sementara shadow-xs" title="Skor sementara (belum termasuk penilaian manual)">
                                                                {{ number_format($hasil->total_skor, 0) }} Poin*
                                                            </span>
                                                            <span class="text-xxs fw-bold" style="color: #b45309;">
                                                                <i class="fas fa-hourglass-half me-1"></i> Skor Sementara
                                                            </span>
                                                        @else
                                                            <span class="skor-badge bg-gradient-success text-white shadow-xs">
                                                                {{ number_format($hasil->total_skor, 0) }} Poin
                                                            </span>
                                                        @endif
                                                    @elseif($hasil->status_pengerjaan === 'gagal_pelanggaran')
                                                        <span class="skor-badge bg-gradient-danger text-white shadow-xs">
                                                            {{ number_format($hasil->total_skor, 0) }} Poin
                                                        </span>
                                                    @else
                                                        <span class="skor-badge bg-light text-dark border">
                                                            --
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- STATUS / PERCOBAAN KE / JUMLAH PELANGGARAN --}}
                                            <td class="align-middle text-center text-sm">
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    @if($hasil->status_pengerjaan === 'final')
                                                        <span class="badge badge-sm bg-gradient-primary">Final</span>
                                                    @elseif($hasil->status_pengerjaan === 'selesai')
                                                        <span class="badge badge-sm bg-gradient-success">Selesai</span>
                                                        @if($perluDinilai)
                                                            <span class="badge badge-perlu-dinilai text-xxs" title="Klik Nilai untuk memberikan skor esai / PG tanpa kunci">
                                                                <i class="fas fa-pen-alt me-1"></i> Perlu Dinilai
                                                            </span>
                                                        @endif
                                                    @elseif($hasil->status_pengerjaan === 'gagal_pelanggaran')
                                                        <span class="badge badge-sm bg-gradient-danger">Diskualifikasi</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-info">Berlangsung</span>
                                                    @endif

                                                    <span class="text-xxs text-secondary">
                                                        Percobaan ke-{{ $hasil->percobaan_ke }}
                                                    </span>

                                                    @if($jumlahPelanggaran > 0)
                                                        <span class="badge badge-pelanggaran-count text-xxs"
                                                              title="{{ $hasil->catatan_pelanggaran ?? 'Tercatat melakukan pelanggaran selama ujian' }}">
                                                            <i class="fas fa-exclamation-triangle me-1"></i> Pelanggaran: {{ $jumlahPelanggaran }}x
                                                        </span>
                                                    @else
                                                        <span class="text-xxs text-secondary">
                                                            <i class="fas fa-shield-alt text-success me-1"></i> Pelanggaran: 0
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- AKSI --}}
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center align-items-center gap-1">
                                                    {{-- TOMBOL FINALISASI PAKSA (Khusus status berlangsung yang ditinggal tutup browser) --}}
                                                    @if($hasil->status_pengerjaan === 'berlangsung')
                                                        <button type="button" class="btn btn-outline-success btn-sm mb-0 px-2 shadow-sm" title="Finalisasi Paksa & Hitung Skor" onclick="finalisasiPaksaPeserta({{ $hasil->id }})">
                                                            <i class="fas fa-check-double"></i> Finalisasi
                                                        </button>
                                                    @endif

                                                    {{-- TOMBOL UNLOCK / LANJUTKAN (Khusus status gagal_pelanggaran) --}}
                                                    @if($hasil->status_pengerjaan === 'gagal_pelanggaran')
                                                        <button type="button" class="btn btn-outline-warning btn-sm mb-0 px-2 shadow-sm" title="Buka Kunci / Izinkan Melanjutkan" onclick="unlockSesiPeserta({{ $hasil->id }})">
                                                            <i class="fas fa-unlock"></i> Lanjutkan
                                                        </button>
                                                    @endif

                                                    <a href="{{ route('sba.hasil.detail', $hasil->id) }}" 
                                                       class="btn {{ $perluDinilai ? 'bg-gradient-primary text-white fw-bold' : 'btn-outline-info' }} btn-sm mb-0 px-2 shadow-sm" 
                                                       title="{{ $perluDinilai ? 'Beri Nilai & Finalisasi Jawaban' : 'Detail Jawaban & Penilaian' }}">
                                                        <i class="fas {{ $perluDinilai ? 'fa-edit' : 'fa-eye' }}"></i> {{ $perluDinilai ? 'Nilai' : 'Detail' }}
                                                    </a>

                                                    <button type="button" class="btn btn-outline-danger btn-sm mb-0 px-2 shadow-sm" title="Hapus Hasil" onclick="hapusSingleHasil({{ $hasil->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-users-slash fa-3x text-secondary mb-3 opacity-5"></i>
                                                    <h6 class="text-secondary font-weight-normal">Belum ada data peserta yang cocok dengan filter.</h6>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- PANEL TOMBOL AKSI MASSAL (DITARUH DI BAWAH AKSI DELETE TABEL) --}}
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <div>
                                    <button type="button" id="btnBulkDelete" class="btn btn-danger btn-sm mb-0 shadow-sm" disabled onclick="konfirmasiBulkDelete()">
                                        <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                                    </button>
                                </div>
                                
                                {{-- INFORMASI PAGINATION (KAPASITAS HALAMAN) --}}
                                @if($hasils instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                    <div class="text-xs text-secondary">
                                        Menampilkan {{ $hasils->firstItem() ?? 0 }} sampai {{ $hasils->lastItem() ?? 0 }} dari total {{ $hasils->total() }} data
                                    </div>
                                @endif
                            </div>
                        </form>

                        {{-- FORM TERPISAH KHUSUS SINGLE DELETE --}}
                        <form id="formSingleDelete" action="" method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>

                        {{-- FORM TERPISAH KHUSUS UNLOCK SESI PESERTA --}}
                        <form id="formUnlockSesi" action="" method="POST" class="d-none">
                            @csrf
                        </form>

                        {{-- FORM TERPISAH KHUSUS FINALISASI PAKSA --}}
                        <form id="formFinalisasiPaksa" action="" method="POST" class="d-none">
                            @csrf
                        </form>

                        {{-- PAGINATION --}}
                        @if($hasils instanceof \Illuminate\Pagination\LengthAwarePaginator && $hasils->hasPages())
                            <div class="d-flex justify-content-end align-items-center mt-3">
                                <div>
                                    {{ $hasils->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

{{-- SCRIPT JAVASCRIPT UNTUK CHECKBOX, BULK DELETE, UNLOCK, & FINALISASI --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAllHasil');
        const checkItems = document.querySelectorAll('.check-item-hasil');
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const selectedCountEl = document.getElementById('selectedCount');

        function updateBulkDeleteState() {
            const checkedCount = document.querySelectorAll('.check-item-hasil:checked').length;
            selectedCountEl.innerText = checkedCount;

            if (checkedCount > 0) {
                btnBulkDelete.removeAttribute('disabled');
            } else {
                btnBulkDelete.setAttribute('disabled', 'true');
            }

            if (checkItems.length > 0 && checkedCount === checkItems.length) {
                checkAll.checked = true;
                checkAll.indeterminate = false;
            } else if (checkedCount > 0) {
                checkAll.checked = false;
                checkAll.indeterminate = true;
            } else {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function() {
                checkItems.forEach(item => {
                    item.checked = this.checked;
                });
                updateBulkDeleteState();
            });
        }

        checkItems.forEach(item => {
            item.addEventListener('change', updateBulkDeleteState);
        });
    });

    // Fungsi konfirmasi hapus massal
    function konfirmasiBulkDelete() {
        const checkedCount = document.querySelectorAll('.check-item-hasil:checked').length;
        if (checkedCount === 0) return;

        if (confirm(`Apakah Anda yakin ingin menghapus ${checkedCount} data hasil ujian yang dipilih secara permanen?`)) {
            document.getElementById('formBulkDelete').submit();
        }
    }

    // Fungsi single delete via JS
    function hapusSingleHasil(id) {
        if (confirm('Apakah Anda yakin ingin menghapus hasil pengerjaan peserta ini?')) {
            const form = document.getElementById('formSingleDelete');
            let url = "{{ route('sba.hasil.destroy', ':id') }}";
            form.action = url.replace(':id', id);
            form.submit();
        }
    }

    // Fungsi unlock sesi peserta via JS
    function unlockSesiPeserta(id) {
        if (confirm('Buka kunci (Unlock) sesi peserta ini agar dapat melanjutkan ujian dari sisa waktu yang tersisa?')) {
            const form = document.getElementById('formUnlockSesi');
            let url = "{{ route('sba.hasil.unlock', ':id') }}";
            form.action = url.replace(':id', id);
            form.submit();
        }
    }

    // Fungsi finalisasi paksa peserta yang stuck berlangsung via JS
    function finalisasiPaksaPeserta(id) {
        if (confirm('Finalisasi paksa sesi peserta ini? Sistem akan menghitung skor dari jawaban yang sudah masuk dan mengakhiri sesi ujian.')) {
            const form = document.getElementById('formFinalisasiPaksa');
            let url = "{{ route('sba.hasil.finalisasi', ':id') }}";
            form.action = url.replace(':id', id);
            form.submit();
        }
    }
</script>
@endsection