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

        /* Perbaikan Tampilan Checkbox Tabel Hasil */
        .check-item-hasil, 
        #checkAllHasil {
            -webkit-appearance: checkbox !important;
            appearance: checkbox !important;
            width: 18px !important;
            height: 18px !important;
            border: 2px solid #8392ab !important; /* Border abu-abu tegas */
            background-color: #ffffff !important; /* Background putih agar kontras */
            border-radius: 4px !important;
            cursor: pointer !important;
            opacity: 1 !important;
            visibility: visible !important;
            accent-color: #5e72e4 !important; /* Warna centang sesuai tema primary */
        }

        .check-item-hasil:hover, 
        #checkAllHasil:hover {
            border-color: #5e72e4 !important; /* Efek hover border biru */
        }

        .check-item-hasil:checked, 
        #checkAllHasil:checked {
            background-color: #5e72e4 !important;
            border-color: #5e72e4 !important;
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

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-chart-bar me-2"></i> Hasil Asesmen: {{ $uji->judul }}
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Kode Uji: <strong>{{ strtoupper($uji->kode_uji) }}</strong> | Total Data Tampil: <strong>{{ $hasils->total() }} Peserta</strong>
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
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
                                            <li>
                                                <a class="dropdown-item text-xs py-2 fw-bold" href="{{ route('sba.hasil.export', array_merge(['uji_id' => $uji->id, 'format' => 'pdf'], request()->query())) }}" target="_blank">
                                                    <i class="fas fa-file-pdf text-danger me-2 fs-6"></i> Export to PDF (.pdf)
                                                </a>
                                            </li>
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

                        {{-- PANEL FILTER AUTO-SUBMIT ONCHANGE & TOMBOL RESET --}}
                        <form id="filterForm" action="{{ route('sba.hasil.index', $uji->id) }}" method="GET" class="row g-2 mb-3 align-items-center">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'total_skor') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">

                            {{-- Filter Status Pengerjaan (Auto-Submit) --}}
                            <div class="col-md-2">
                                <select name="status_pengerjaan" class="form-select form-select-sm border px-2 py-1" onchange="this.form.submit()">
                                    <option value="">-- Semua Status --</option>
                                    <option value="selesai" {{ request('status_pengerjaan') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="berlangsung" {{ request('status_pengerjaan') == 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                                    <option value="gagal_pelanggaran" {{ request('status_pengerjaan') == 'gagal_pelanggaran' ? 'selected' : '' }}>Diskualifikasi</option>
                                </select>
                            </div>

                            {{-- Filter Keysearch --}}
                            <div class="col-md-3">
                                <div class="input-group input-group-sm border rounded">
                                    <span class="input-group-text bg-transparent border-0 pe-1">
                                        <i class="fas fa-search text-secondary text-xs"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-0 ps-1" 
                                           placeholder="Cari ID Asesi / Nama..." 
                                           value="{{ request('search') }}"
                                           onkeypress="if(event.key === 'Enter') this.form.submit()">
                                </div>
                            </div>

                            {{-- FILTER RENTANG TANGGAL PENGUMPULAN --}}
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-1">
                                    <input type="date" name="tgl_mulai" class="form-control form-control-sm border px-2 py-1 text-xs" 
                                           value="{{ request('tgl_mulai') }}" title="Tanggal Pengumpulan Mulai" onchange="this.form.submit()">
                                    <span class="text-xs text-secondary">s/d</span>
                                    <input type="date" name="tgl_selesai" class="form-control form-control-sm border px-2 py-1 text-xs" 
                                           value="{{ request('tgl_selesai') }}" title="Tanggal Pengumpulan Selesai" onchange="this.form.submit()">
                                </div>
                            </div>

                            {{-- Checkbox Sembunyikan Identitas Ganda (Auto-Submit) --}}
                            <div class="col-md-2">
                                <div class="form-check m-0">
                                    <input class="form-check-input border-secondary" type="checkbox" name="hide_duplicates" value="1" id="hideDuplicates" 
                                           {{ request('hide_duplicates') == '1' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label text-xs font-weight-bold text-dark cursor-pointer mb-0" for="hideDuplicates">
                                        Identitas Ganda
                                    </label>
                                </div>
                            </div>

                            {{-- Tombol Reset Filter --}}
                            <div class="col-md-1 text-end">
                                <a href="{{ route('sba.hasil.index', $uji->id) }}" class="btn btn-sm btn-outline-secondary mb-0 w-100 px-2" title="Reset Filter">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </a>
                            </div>

                            {{-- Per Page Selector --}}
                            <div class="col-md-1 text-end">
                                <select name="per_page" class="form-select form-select-sm border px-2 py-1 w-auto" 
                                        style="padding-right: 1.8rem !important; min-width: 65px;" onchange="this.form.submit()">
                                    @foreach([10, 25, 50, 100] as $pageOption)
                                        <option value="{{ $pageOption }}" {{ request('per_page', 10) == $pageOption ? 'selected' : '' }}>
                                            {{ $pageOption }}
                                        </option>
                                    @endforeach
                                </select>
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
                                            
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-start ps-3" width="12%">
                                                <a href="{{ getSortUrlHasil('asesi_id') }}" class="sort-header">
                                                    Asesi ID {!! getSortIconHasil('asesi_id') !!}
                                                </a>
                                            </th>
                                            
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-start ps-3" width="24%">Nama Peserta</th>
                                            
                                            {{-- KOLOM DURASI PENGERJAAN --}}
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="18%">Waktu & Durasi</th>

                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="13%">
                                                <a href="{{ getSortUrlHasil('total_skor') }}" class="sort-header justify-content-center">
                                                    Skor Akhir {!! getSortIconHasil('total_skor') !!}
                                                </a>
                                            </th>
                                            
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="14%">
                                                <a href="{{ getSortUrlHasil('status_pengerjaan') }}" class="sort-header justify-content-center">
                                                    Status / Ke {!! getSortIconHasil('status_pengerjaan') !!}
                                                </a>
                                            </th>
                                            
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="12%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hasils as $index => $hasil)
                                        <tr>
                                            {{-- CHECKBOX PER BARIS --}}
                                            <td class="text-center align-middle">
                                                <input class="form-check-input border-secondary check-item-hasil" type="checkbox" name="ids[]" value="{{ $hasil->id }}" style="transform: scale(1.1); cursor: pointer;">
                                            </td>

                                            {{-- NO --}}
                                            <td class="text-start ps-2 text-sm align-middle">
                                                {{ ($hasils instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $hasils->firstItem() + $index : $loop->iteration }}
                                            </td>

                                            {{-- ASESI ID --}}
                                            <td class="text-start ps-3 align-middle">
                                                @if($hasil->asesi_id)
                                                    <span class="badge bg-gradient-dark text-xxs font-weight-bold">
                                                        USER #{{ $hasil->asesi_id }}
                                                    </span>
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
                                                @if($hasil->status_pengerjaan === 'selesai')
                                                    <span class="skor-badge bg-gradient-success text-white shadow-xs">
                                                        {{ number_format($hasil->total_skor, 0) }} Poin
                                                    </span>
                                                @elseif($hasil->status_pengerjaan === 'gagal_pelanggaran')
                                                    <span class="skor-badge bg-gradient-danger text-white">
                                                        0 (Gagal)
                                                    </span>
                                                @else
                                                    <span class="skor-badge bg-light text-dark border">
                                                        --
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- STATUS / PERCOBAAN KE --}}
                                            <td class="align-middle text-center text-sm">
                                                <div class="d-flex flex-column align-items-center">
                                                    @if($hasil->status_pengerjaan === 'selesai')
                                                        <span class="badge badge-sm bg-gradient-success mb-1">Selesai</span>
                                                    @elseif($hasil->status_pengerjaan === 'gagal_pelanggaran')
                                                        <span class="badge badge-sm bg-gradient-danger mb-1">Diskualifikasi</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-info mb-1">Berlangsung</span>
                                                    @endif

                                                    <span class="text-xxs text-secondary">
                                                        Percobaan ke-{{ $hasil->percobaan_ke }}
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- AKSI --}}
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <a href="{{ route('sba.hasil.detail', $hasil->id) }}" class="btn btn-outline-info btn-sm mb-0 px-2 shadow-sm me-1" title="Detail Jawaban">
                                                        <i class="fas fa-eye"></i> Detail
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

{{-- SCRIPT JAVASCRIPT UNTUK CHECKBOX & BULK DELETE --}}
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
            // Menggunakan helper route() Laravel dan mengganti placeholder ':id'
            let url = "{{ route('sba.hasil.destroy', ':id') }}";
            form.action = url.replace(':id', id);
            form.submit();
        }
    }
</script>
@endsection