@extends('layouts.app')

@section('page-title', 'Ledger Nilai Siswa')

@section('content')
<style>
    .table-responsive {
        max-height: 90vh;          
        overflow: auto;
        border-radius: 3px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .table-ledger {
        border-collapse: collapse !important;
        border-spacing: 0;
        width: 100%;
    }
    .table-ledger tbody tr:hover {
        background-color: #f9fafb;
    }

    .table-ledger thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        border: none !important;
        border-bottom: 0 !important;  
        padding: 6px 4px !important;
        text-align: center;
        vertical-align: middle;
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        line-height: 1.2;
    }

    .table-ledger thead tr:first-child th {
        top: 0;
        height: 32px;
        color: #fff;
        font-weight: 700;
        z-index: 30;
    }

    .table-ledger thead tr:nth-child(2) th.kategori-sub {
        background-color: #f8f9fa !important;
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid transparent;
        top: 27px;
        z-index: 25;
    }

    .kategori-1 { background-color: #b0bec5 !important; }
    .kategori-2 { background-color: #b0bec5 !important; }
    .kategori-3 { background-color: #b0bec5 !important; }
    .kategori-4 { background-color: #b0bec5 !important; }
    .kategori-5 { background-color: #b0bec5 !important; }
    .kategori-6 { background-color: #b0bec5 !important; }
    .kategori-7 { background-color: #fb8c00 !important; }

    .table-ledger thead tr:nth-child(2) th.kategori-1 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-2 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-3 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-4 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-5 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-6 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-7 { background-color: #ffe0b2 !important; color: #e65100; }

    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 10;
        background-color: #ffffff !important; 
        border-right: 1px solid #d0d7de !important;
        border-bottom: 1px solid #e9ecef !important;
    }
    
    .sticky-col-header {
        position: sticky;
        top: 0;
        left: 0;
        z-index: 40 !important;
        background-color: #37474f !important;
        color: #fff !important;
    }

    .col-nama { width: 220px !important; min-width: 220px !important; max-width: 220px !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .col-id { width: 100px !important; min-width: 100px !important; text-align: center; }
    .col-nilai { width: 55px !important; min-width: 55px !important; text-align: center; font-size: 0.85rem; }
    .bg-light-danger { background-color: #fde8e8 !important; color: #c81e1e !important; font-weight: bold; }
    .bg-rekap { background-color: #fff8e1 !important; color: #344767; }
    .bg-absen { background-color: #e3f2fd !important; color: #344767; }
    .bg-ranking { background-color: #fff3e0 !important; font-weight: bold; color: #e65100; }

    .table-ledger tbody td { border: none !important; border-bottom: 1px solid #e0e0e0 !important; padding: 8px 6px; }
</style>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />
    
    <div class="container-fluid py-4 px-5">
        <div class="card shadow-xs border mb-5">
            <div class="card-header bg-gradient-primary py-3">
                <h6 class="text-white mb-0"><i class="fas fa-table me-2"></i> Ledger Nilai Siswa</h6>
            </div>
            <div class="card-body">
                <div class="p-4 border-bottom">
                    <form action="{{ route('ledger.ledger_index') }}" method="GET" id="filterForm">
                        <div class="row align-items-end mb-3">
                            <div class="col-md-2">
                                <label class="form-label">Mode Ledger:</label>
                                <select name="mode" class="form-select" onchange="this.form.submit()">
                                    <option value="kelas" {{ request('mode', 'kelas') == 'kelas' ? 'selected' : '' }}>Per Kelas</option>
                                    <option value="jurusan" {{ request('mode') == 'jurusan' ? 'selected' : '' }}>Per Jurusan</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Kelas:</label>
                                <select name="id_kelas" class="form-select" onchange="this.form.submit()"
                                    {{ request('mode', 'kelas') == 'jurusan' ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jurusan:</label>
                                <select name="jurusan" class="form-select" onchange="this.form.submit()"
                                    {{ request('mode', 'kelas') == 'kelas' ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach($jurusanList as $j)
                                        <option value="{{ $j }}" {{ request('jurusan') == $j ? 'selected' : '' }}>{{ $j }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tingkat:</label>
                                <select name="tingkat" class="form-select" onchange="this.form.submit()"
                                    {{ request('mode', 'kelas') == 'kelas' ? 'disabled' : '' }}>
                                    <option value="">-- Semua --</option>
                                    @foreach($tingkatList as $t)
                                        <option value="{{ $t }}" {{ request('tingkat') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Semester:</label>
                                <select name="semester" class="form-select" onchange="this.form.submit()">
                                    <option value="Ganjil" {{ request('semester') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                    <option value="Genap" {{ request('semester') == 'Genap' ? 'selected' : '' }}>Genap</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tahun Ajaran:</label>
                                <select name="tahun_ajaran" class="form-select" onchange="this.form.submit()">
                                    @foreach($tahunAjaranList as $ta)
                                        <option value="{{ $ta }}" {{ request('tahun_ajaran') == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Opsi Tampilan (Filter A)</label>
                                <select name="show_ranking" id="selectShowRanking" class="form-select" onchange="handleFilterChange()">
                                    <option value="0" {{ $showRanking == '0' ? 'selected' : '' }}>Sembunyikan Ranking</option>
                                    <option value="1" {{ $showRanking == '1' ? 'selected' : '' }}>Tampilkan Ranking</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Urutkan Data (Filter B)</label>
                                <select name="sort_by" id="selectSortBy" class="form-select" onchange="this.form.submit()">
                                    {{-- REVISI LABEL: Mempertegas urutan absen berdasarkan ID --}}
                                    <option value="absen" {{ $sortBy == 'absen' ? 'selected' : '' }}>Berdasarkan Absen (ID Siswa)</option>
                                    <option value="ranking" {{ $sortBy == 'ranking' ? 'selected' : '' }}>Berdasarkan Ranking (Nilai)</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                @if(!empty($dataLedger))
                @php
                    $catLabels = [1 => 'Umum', 2 => 'Kejuruan', 3 => 'Pilihan', 4 => 'Mulok'];
                    $groupedMapel = $daftarMapel->groupBy('kategori');
                @endphp

                <div class="table-responsive p-0">
                    <table id="ledgerTable" class="table table-ledger align-items-center mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-col-header" style="width: 45px;">No</th>
                                <th rowspan="2" class="sticky-col-header col-nama">Nama Siswa</th>
                                <th rowspan="2" class="sticky-col-header col-id">NIS</th>
                                <th rowspan="2" class="sticky-col-header col-id">NISN</th>
                                @foreach($groupedMapel as $catId => $mapels)
                                    <th colspan="{{ count($mapels)}}" class="kategori-header kategori-{{ $catId }}">{{ $catLabels[$catId] ?? 'Lainnya' }}</th>
                                @endforeach
                                <th colspan="2" class="kategori-header kategori-5">REKAP</th>
                                <th colspan="3" class="kategori-header kategori-6">ABSEN</th>
                                @if($showRanking == '1')
                                    <th rowspan="2" class="kategori-header kategori-7">RANK</th>
                                @endif
                            </tr>
                            <tr>
                                @foreach($groupedMapel as $catId => $mapels)
                                    @foreach($mapels as $mp)
                                        <th class="col-nilai kategori-sub kategori-{{ $catId }}" data-bs-toggle="tooltip" title="{{ $mp->nama_mapel }}">
                                            {{ substr($mp->nama_singkat ?? $mp->nama_mapel, 0, 5) }}
                                        </th>
                                    @endforeach
                                @endforeach
                                <th class="kategori-sub kategori-5">JML</th>
                                <th class="kategori-sub kategori-5">AVG</th>
                                <th class="kategori-sub kategori-6">S</th>
                                <th class="kategori-sub kategori-6">I</th>
                                <th class="kategori-sub kategori-6">A</th>
                            </tr>
                        </thead>
                        <tbody id="ledgerBody">
                            @forelse($dataLedger as $idx => $row)
                            <tr>
                                <td class="text-center text-sm sticky-col">{{ $loop->iteration }}</td>
                                <td class="text-sm sticky-col col-nama font-weight-bold text-dark" data-bs-toggle="tooltip" title="{{ $row->nama_siswa }}">{{ $row->nama_siswa }}</td>
                                <td class="text-sm text-center col-id sticky-col">{{ $row->nipd ?? '-' }}</td>
                                <td class="text-sm text-center col-id sticky-col">{{ $row->nisn ?? '-' }}</td>
                                @foreach($groupedMapel as $catId => $mapels)
                                    @foreach($mapels as $mp)
                                        @php $val = $row->scores[$mp->id_mapel] ?? 0; @endphp
                                        <td class="col-nilai text-sm {{ $val <= 0 ? 'bg-light-danger' : '' }}">{{ $val > 0 ? (int)$val : '-' }}</td>
                                    @endforeach
                                @endforeach
                                <td class="col-nilai text-sm font-weight-bold bg-rekap">{{ (int)$row->total }}</td>
                                <td class="col-nilai text-sm font-weight-bold text-primary bg-rekap">{{ number_format($row->rata_rata, 1) }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->sakit }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->izin }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->alpha }}</td>
                                @if($showRanking == '1')
                                    <td class="col-nilai text-sm text-center bg-ranking">{{ $row->ranking_no }}</td>
                                @endif
                            </tr>
                            @empty
                            <tr><td colspan="100%" class="text-center py-4">Data tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('ledger.export.excel', request()->query()) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Download Excel</a>
                    <a href="{{ route('ledger.export.pdf', request()->query()) }}" target="_blank" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> Download PDF</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeSelect = document.querySelector('select[name="mode"]');
    const tingkatSelect = document.querySelector('select[name="tingkat"]');
    const selectShowRanking = document.getElementById('selectShowRanking');
    const selectSortBy = document.getElementById('selectSortBy');
    const filterForm = document.getElementById('filterForm');

    function toggleTingkat() {
        if (modeSelect && modeSelect.value === 'jurusan') {
            tingkatSelect.removeAttribute('disabled');
        } else if (tingkatSelect) {
            tingkatSelect.setAttribute('disabled', 'disabled');
        }
    }

    if(modeSelect) {
        toggleTingkat();
        modeSelect.addEventListener('change', toggleTingkat);
    }

    if(selectShowRanking) {
        if(selectShowRanking.value == '0') {
            selectSortBy.setAttribute('disabled', 'disabled');
        } else {
            selectSortBy.removeAttribute('disabled');
        }
    }

    window.handleFilterChange = function() {
        if (selectShowRanking.value == '0') {
            selectSortBy.value = 'absen';
            selectSortBy.setAttribute('disabled', 'disabled');
            filterForm.submit();
        } else {
            selectSortBy.removeAttribute('disabled');
            filterForm.submit();
        }
    };
});
</script>
@endsection