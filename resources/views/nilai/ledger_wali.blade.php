{{-- File: resources/views/nilai/ledger_wali.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Ledger Kelas (Live Data)')

@section('content')
<style>
    .table-responsive { max-height: 80vh; overflow: auto; border-radius: 3px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    .table-ledger { border-collapse: collapse !important; border-spacing: 0; width: 100%; }
    .table-ledger tbody tr:hover { background-color: #f9fafb; }
    .table-ledger thead th { position: sticky; top: 0; z-index: 20; border: none !important; border-bottom: 0 !important; padding: 6px 4px !important; text-align: center; vertical-align: middle; font-size: 0.8rem !important; font-weight: 700 !important; line-height: 1.2; }
    .table-ledger thead tr:first-child th { top: 0; height: 32px; color: #fff; font-weight: 700; z-index: 30; }
    .table-ledger thead tr:nth-child(2) th.kategori-sub { background-color: #f8f9fa !important; color: #495057; font-weight: 600; border-bottom: 2px solid transparent; top: 27px; z-index: 25; }
    
    .kategori-1 { background-color: #b0bec5 !important; } .kategori-2 { background-color: #b0bec5 !important; }
    .kategori-3 { background-color: #b0bec5 !important; } .kategori-4 { background-color: #b0bec5 !important; }
    .kategori-5 { background-color: #b0bec5 !important; } .kategori-7 { background-color: #fb8c00 !important; }
    .table-ledger thead tr:nth-child(2) th.kategori-1 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-5 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-7 { background-color: #ffe0b2 !important; color: #e65100; }

    .sticky-col { position: sticky; left: 0; z-index: 10; background-color: #ffffff !important; border-right: 1px solid #d0d7de !important; border-bottom: 1px solid #e9ecef !important; }
    .sticky-col-header { position: sticky; top: 0; left: 0; z-index: 40 !important; background-color: #37474f !important; color: #fff !important; }

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
        
        {{-- ALERT HYBRID / LIVE DATA --}}
        <div class="alert alert-info text-dark shadow-sm border-0 d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <strong>Dashboard Monitoring Ledger (Live Data)</strong><br>
                <span class="text-sm">Halaman ini menampilkan nilai siswa secara <i>real-time</i> berdasarkan input terbaru dari guru mata pelajaran. Tanda strip (-) merah menandakan nilai belum diisi/disimpan oleh guru bersangkutan.</span>
            </div>
        </div>

        <div class="card shadow-xs border mb-5">
            <div class="card-header bg-gradient-info py-3 d-flex justify-content-between align-items-center">
                <h6 class="text-white mb-0"><i class="fas fa-desktop me-2"></i> Pantauan Ledger Kelas</h6>
                <span class="badge bg-white text-info"><i class="fas fa-circle text-success text-xxs me-1 animate-pulse"></i> LIVE</span>
            </div>
            
            <div class="card-body">
                <div class="p-4 border-bottom bg-gray-50">
                    <form action="{{ route('walikelas.ledger.index') }}" method="GET" id="filterForm">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-secondary mb-1">Pilih Kelas:</label>
                                <select name="id_kelas" class="form-select border px-2 py-1" onchange="this.form.submit()">
                                    @if($kelasList->isEmpty())
                                        <option value="">-- Tidak ada kelas tersedia --</option>
                                    @else
                                        @foreach($kelasList as $k)
                                            <option value="{{ $k->id_kelas }}" {{ request('id_kelas', $id_kelas) == $k->id_kelas ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-secondary mb-1">Semester:</label>
                                <select name="semester" class="form-select border px-2 py-1" onchange="this.form.submit()">
                                    @foreach($semesterList as $sem)
                                        <option value="{{ $sem }}" {{ request('semester', $semesterRaw) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-secondary mb-1">Tahun Ajaran:</label>
                                <select name="tahun_ajaran" class="form-select border px-2 py-1" onchange="this.form.submit()">
                                    @foreach($tahunAjaranList as $ta)
                                        <option value="{{ $ta }}" {{ request('tahun_ajaran', $tahun_ajaran) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-secondary mb-1">Tampilkan Rank:</label>
                                <select name="show_ranking" id="selectShowRanking" class="form-select border px-2 py-1" onchange="handleFilterChange()">
                                    <option value="0" {{ $showRanking == '0' ? 'selected' : '' }}>Sembunyikan</option>
                                    <option value="1" {{ $showRanking == '1' ? 'selected' : '' }}>Tampilkan</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-secondary mb-1">Urutkan Data:</label>
                                <select name="sort_by" id="selectSortBy" class="form-select border px-2 py-1" onchange="this.form.submit()">
                                    <option value="absen" {{ $sortBy == 'absen' ? 'selected' : '' }}>Berdasarkan Nama Siswa</option>
                                    <option value="ranking" {{ $sortBy == 'ranking' ? 'selected' : '' }}>Berdasarkan Ranking</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                @if(!empty($dataLedger) && $dataLedger->isNotEmpty())
                @php
                    $catLabels = [1 => 'Umum', 2 => 'Kejuruan', 3 => 'Pilihan', 4 => 'Mulok'];
                    $groupedMapel = $daftarMapel->groupBy('kategori');
                @endphp

                <div class="table-responsive p-0 mt-3">
                    <table id="ledgerTable" class="table table-ledger align-items-center mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-col-header" style="width: 45px;">No</th>
                                <th rowspan="2" class="sticky-col-header col-nama">Nama Siswa</th>
                                <th rowspan="2" class="sticky-col-header col-id">NIS</th>
                                @foreach($groupedMapel as $catId => $mapels)
                                    <th colspan="{{ count($mapels)}}" class="kategori-header kategori-{{ $catId }}">{{ $catLabels[$catId] ?? 'Lainnya' }}</th>
                                @endforeach
                                <th colspan="2" class="kategori-header kategori-5">REKAP</th>
                                <th colspan="3" class="kategori-header kategori-5">ABSENSI</th>
                                @if(strtoupper($semesterRaw) == 'GENAP')
                                    <th rowspan="2" class="kategori-header kategori-5" style="width: 8%;">KENAIKAN</th>
                                @endif
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
                                <th class="kategori-sub kategori-5">S</th>
                                <th class="kategori-sub kategori-5">I</th>
                                <th class="kategori-sub kategori-5">A</th>
                            </tr>
                        </thead>
                        <tbody id="ledgerBody">
                            @foreach($dataLedger as $idx => $row)
                            <tr>
                                <td class="text-center text-sm sticky-col">{{ $loop->iteration }}</td>
                                <td class="text-sm sticky-col col-nama font-weight-bold text-dark" data-bs-toggle="tooltip" title="{{ $row->nama_siswa }}">{{ $row->nama_siswa }}</td>
                                <td class="text-sm text-center col-id sticky-col">{{ $row->nipd ?? '-' }}</td>
                                @foreach($groupedMapel as $catId => $mapels)
                                    @foreach($mapels as $mp)
                                        @php 
                                            // Membaca nilai dari array. Jika tidak ada isinya berarti $val = null.
                                            $val = $row->scores[$mp->id_mapel] ?? null; 
                                        @endphp
                                        {{-- Mengecek murni menggunakan is_numeric agar 0 terbaca sebagai nilai --}}
                                        <td class="col-nilai text-sm {{ !is_numeric($val) ? 'bg-light-danger' : '' }}">
                                            {{ is_numeric($val) ? (int)$val : '-' }}
                                        </td>
                                    @endforeach
                                @endforeach
                                <td class="col-nilai text-sm font-weight-bold bg-rekap">{{ (int)$row->total }}</td>
                                <td class="col-nilai text-sm font-weight-bold text-primary bg-rekap">{{ number_format($row->rata_rata, 1) }}</td>
                                
                                {{-- Absensi dari tabel nilai_akhir_rapor --}}
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->sakit }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->izin }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->alpha }}</td>

                                @if(strtoupper($semesterRaw) == 'GENAP')
                                    <td class="align-middle text-center text-sm font-weight-bold">
                                        @if($row->status_kenaikan == 'naik_kelas')
                                            <span class="badge badge-sm bg-gradient-success">NAIK</span>
                                        @elseif($row->status_kenaikan == 'tinggal_kelas')
                                            <span class="badge badge-sm bg-gradient-danger">TINGGAL</span>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                @endif

                                @if($showRanking == '1')
                                    <td class="col-nilai text-sm text-center bg-ranking">{{ $row->ranking_no }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-secondary opacity-5 mb-3"></i>
                    <h6 class="text-secondary">Data Ledger Tidak Ditemukan</h6>
                    <p class="text-sm text-muted">Belum ada siswa aktif atau mapel yang terdaftar pada kelas dan semester ini.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectShowRanking = document.getElementById('selectShowRanking');
    const selectSortBy = document.getElementById('selectSortBy');
    const filterForm = document.getElementById('filterForm');

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