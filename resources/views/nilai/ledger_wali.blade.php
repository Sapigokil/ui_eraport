{{-- File: resources/views/nilai/ledger_wali.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Ledger Kelas')

@section('content')
<style>
    .table-responsive { max-height: 75vh; overflow: auto; border-radius: 3px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    .table-ledger { border-collapse: separate !important; border-spacing: 0; width: 100%; min-width: max-content; }
    .table-ledger tbody tr:hover td { background-color: #f8f9fa !important; }
    
    /* Header Styling */
    .table-ledger thead th { position: sticky; top: 0; z-index: 20; border: none !important; border-bottom: 0 !important; padding: 6px 4px !important; text-align: center; vertical-align: middle; font-size: 0.8rem !important; font-weight: 700 !important; line-height: 1.2; }
    
    /* Baris 1: Kategori Besar */
    .table-ledger thead tr:first-child th { top: 0; height: 35px; color: #fff; font-weight: 700; z-index: 30; }
    
    /* Baris 2: Sub Kategori (Mapel) - Diperbaiki agar tidak menggulung */
    .table-ledger thead tr:nth-child(2) th.kategori-sub { background-color: #f8f9fa !important; color: #495057; font-weight: 600; border-bottom: 2px solid #e0e0e0; top: 35px; z-index: 25; }
    
    /* Kategori Warna */
    .kategori-1 { background-color: #b0bec5 !important; } .kategori-2 { background-color: #b0bec5 !important; }
    .kategori-3 { background-color: #b0bec5 !important; } .kategori-4 { background-color: #b0bec5 !important; }
    .kategori-5 { background-color: #b0bec5 !important; } .kategori-7 { background-color: #fb8c00 !important; }
    .table-ledger thead tr:nth-child(2) th.kategori-1 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-5 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-7 { background-color: #ffe0b2 !important; color: #e65100; }

    /* Pengaturan Lebar Kolom Tetap */
    .col-no { width: 50px !important; min-width: 50px !important; max-width: 50px !important; }
    .col-nama { width: 230px !important; min-width: 230px !important; max-width: 230px !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .col-id { width: 90px !important; min-width: 90px !important; max-width: 90px !important; text-align: center; }
    .col-nilai { width: 55px !important; min-width: 55px !important; max-width: 55px !important; text-align: center; font-size: 0.85rem; }

    /* Header khusus NIS agar tidak transparan */
    .header-id { background-color: #37474f !important; color: #fff !important; border-right: 2px solid #202b30 !important; }

    /* LOGIKA STICKY FIX BERTUMPUK (Kiri) - NIS dilepas dari sticky */
    .sticky-col-no { position: sticky !important; left: 0 !important; z-index: 10; background-color: #ffffff !important; border-right: 1px solid #e0e0e0 !important; border-bottom: 1px solid #e9ecef !important; }
    .sticky-col-nama { position: sticky !important; left: 50px !important; z-index: 10; background-color: #ffffff !important; border-right: 2px solid #d0d7de !important; border-bottom: 1px solid #e9ecef !important; }
    
    /* Header Sticky Kiri + Atas */
    .sticky-header-no { position: sticky !important; top: 0 !important; left: 0 !important; z-index: 50 !important; background-color: #37474f !important; color: #fff !important; border-right: 1px solid #4f636e !important; }
    .sticky-header-nama { position: sticky !important; top: 0 !important; left: 50px !important; z-index: 50 !important; background-color: #37474f !important; color: #fff !important; border-right: 2px solid #202b30 !important; }

    /* Styling Sel */
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
                <strong>Dashboard Monitoring Ledger</strong><br>
                <span class="text-sm">Halaman ini menampilkan nilai siswa secara <i>real-time</i> berdasarkan input terbaru dari guru mata pelajaran. Tanda strip (-) merah menandakan nilai belum diisi/disimpan oleh guru bersangkutan.</span>
            </div>
        </div>

        <div><br></div>

        <div class="card shadow-xs border mb-5">
            {{-- HEADER BANNER --}}
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 position-relative" style="overflow: visible;">
                    
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; border-radius: inherit; pointer-events: none;">
                        <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                            <i class="fas fa-chart-line text-white" style="font-size: 8rem;"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-4">
                        <div>
                            <h4 class="text-white font-weight-bold mb-1">
                                <i class="fas fa-desktop me-2"></i> Pantauan Ledger Kelas
                            </h4>
                            <p class="text-white text-sm opacity-8 mb-0 ms-4 ps-2">
                                Rekapitulasi nilai dan capaian siswa
                            </p>
                        </div>
                        
                        {{-- TOMBOL EXPORT DROPDOWN --}}
                        <div class="dropdown">
                            <button class="btn bg-white text-primary mb-0 dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false" {{ empty($id_kelas) ? 'disabled' : '' }}>
                                <i class="fas fa-print me-1"></i> Cetak Ledger
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="exportDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('walikelas.ledger.export_excel', request()->all()) }}">
                                        <i class="fas fa-file-excel text-success me-2"></i> Export ke Excel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('walikelas.ledger.export_pdf', request()->all()) }}" target="_blank">
                                        <i class="fas fa-file-pdf text-danger me-2"></i> Export ke PDF
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body mt-2">
                <div class="p-4 border-bottom bg-gray-50 rounded-top">
                    <form action="{{ route('walikelas.ledger.index') }}" method="GET" id="filterForm">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-secondary mb-1">Pilih Kelas:</label>
                                <select name="id_kelas" class="form-select border px-2 py-1 bg-white" onchange="this.form.submit()">
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
                                <select name="semester" class="form-select border px-2 py-1 bg-white" onchange="this.form.submit()">
                                    @foreach($semesterList as $sem)
                                        <option value="{{ $sem }}" {{ request('semester', $semesterRaw) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-secondary mb-1">Tahun Ajaran:</label>
                                <select name="tahun_ajaran" class="form-select border px-2 py-1 bg-white" onchange="this.form.submit()">
                                    @foreach($tahunAjaranList as $ta)
                                        <option value="{{ $ta }}" {{ request('tahun_ajaran', $tahun_ajaran) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-secondary mb-1">Tampilkan Rank:</label>
                                <select name="show_ranking" id="selectShowRanking" class="form-select border px-2 py-1 bg-white" onchange="handleFilterChange()">
                                    <option value="0" {{ $showRanking == '0' ? 'selected' : '' }}>Sembunyikan</option>
                                    <option value="1" {{ $showRanking == '1' ? 'selected' : '' }}>Tampilkan</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-secondary mb-1">Urutkan Data:</label>
                                <select name="sort_by" id="selectSortBy" class="form-select border px-2 py-1 bg-white" onchange="this.form.submit()">
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
                                <th rowspan="2" class="sticky-header-no col-no">No</th>
                                <th rowspan="2" class="sticky-header-nama col-nama">Nama Siswa</th>
                                {{-- NIS dilepas dari class sticky-header --}}
                                {{-- Terapkan class header-id di sini --}}
                                <th rowspan="2" class="header-id col-id">NIS</th>
                                @foreach($groupedMapel as $catId => $mapels)
                                    <th colspan="{{ count($mapels)}}" class="kategori-header kategori-{{ $catId }}">{{ $catLabels[$catId] ?? 'Lainnya' }}</th>
                                @endforeach
                                <th colspan="2" class="kategori-header kategori-5">REKAP</th>
                                <th colspan="3" class="kategori-header kategori-5">ABSENSI</th>
                                @if(strtoupper($semesterRaw) == 'GENAP')
                                    <th rowspan="2" class="kategori-header kategori-5" style="width: 80px;">KENAIKAN</th>
                                @endif
                                @if($showRanking == '1')
                                    <th rowspan="2" class="kategori-header kategori-7" style="width: 60px;">RANK</th>
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
                                <td class="text-center text-sm sticky-col-no col-no">{{ $loop->iteration }}</td>
                                <td class="text-sm sticky-col-nama col-nama font-weight-bold text-dark" data-bs-toggle="tooltip" title="{{ $row->nama_siswa }}">{{ $row->nama_siswa }}</td>
                                {{-- NIS dilepas dari class sticky-col --}}
                                <td class="text-sm text-center col-id">{{ $row->nipd ?? '-' }}</td>
                                
                                @foreach($groupedMapel as $catId => $mapels)
                                    @foreach($mapels as $mp)
                                        @php 
                                            $val = $row->scores[$mp->id_mapel] ?? null; 
                                        @endphp
                                        <td class="col-nilai text-sm {{ !is_numeric($val) ? 'bg-light-danger' : '' }}">
                                            {{ is_numeric($val) ? (int)$val : '-' }}
                                        </td>
                                    @endforeach
                                @endforeach
                                <td class="col-nilai text-sm font-weight-bold bg-rekap">{{ (int)$row->total }}</td>
                                <td class="col-nilai text-sm font-weight-bold text-primary bg-rekap">{{ number_format($row->rata_rata, 1) }}</td>
                                
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->sakit }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->izin }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->alpha }}</td>

                                @if(strtoupper($semesterRaw) == 'GENAP')
                                    <td class="align-middle text-center text-sm font-weight-bold border-start">
                                        @if($row->status_kenaikan == 'naik_kelas')
                                            <span class="text-success">NAIK</span>
                                        @elseif($row->status_kenaikan == 'tinggal_kelas')
                                            <span class="text-danger">TINGGAL</span>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                @endif

                                @if($showRanking == '1')
                                    <td class="col-nilai text-sm text-center bg-ranking border-start">{{ $row->ranking_no }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 mt-4">
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