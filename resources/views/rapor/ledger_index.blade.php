@extends('layouts.app')

@section('page-title', 'Ledger Nilai Siswa')

@section('content')
<style>
    .table-responsive {
        /* Hapus max-height agar scroll halaman, bukan scroll elemen */
        max-height: 90vh;          
        overflow: auto;
        /* overflow-x: auto; 
        overflow-y: visible;  */
        /* border: 1px solid #c1d6e0; Border container lebih tegas */
        border-radius: 2px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    /* REVISI 1 & 2: Styling Header yang Konsisten & Tegas */
    .table-ledger {
        border-collapse: collapse !important; /* Penting untuk Sticky Header & Border */
        border-spacing: 0;
        width: 100%;
    }
    .table-ledger tbody tr:hover {
        background-color: #f9fafb;
    }

    .table-ledger thead th[class^="kategori-"] {
        color: #fff;
        font-weight: 700;
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

    /* HEADER KATEGORI (ROW 1) */
    .table-ledger thead tr:first-child th {
        top: 0;
        height: : 32px;
        color: #fff;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .table-ledger thead tr:nth-child(2) th.kategori-sub {
        background-color: #f8f9fa !important;
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid transparent;
    }

    /* WARNA KATEGORI */
    .kategori-1 { background-color: #b0bec5  !important; } /* Umum */
    .kategori-2 { background-color: #b0bec5  !important; } /* Kejuruan */
    .kategori-3 { background-color: #b0bec5  !important; } /* Pilihan */
    .kategori-4 { background-color: #b0bec5  !important; } /* Mulok */
    .kategori-5 { background-color: #b0bec5  !important; } /* Rekap */
    .kategori-6 { background-color: #b0bec5  !important; } /* Absen */

    /* Baris kedua (Nama Mapel) - Background disamakan */
    /* .table-ledger thead tr:nth-child(2) th {
        background-color: #f7f7f7 !important;
        color: #fff;
        font-weight: 600;
        top: 36px;
    } */

    /* ===== Style Sub Header ===== */
    .table-ledger thead tr:nth-child(2) th.kategori-1 {
        background-color: #cfd8dc !important; /* pastel merah */
        color: #37474f;
    }

    .table-ledger thead tr:nth-child(2) th.kategori-2 {
        background-color: #cfd8dc !important; /* pastel kuning */
        color: #37474f;
    }

    .table-ledger thead tr:nth-child(2) th.kategori-3 {
        background-color: #cfd8dc !important; /* pastel hijau */
        color: #37474f;
    }

    .table-ledger thead tr:nth-child(2) th.kategori-4 {
        background-color: #cfd8dc !important; /* pastel biru */
        color: #37474f;
    }

    .table-ledger thead tr:nth-child(2) th.kategori-5 {
        background-color: #cfd8dc !important; /* pastel coklat */
        color: #37474f;
    }

    .table-ledger thead tr:nth-child(2) th.kategori-6 {
        background-color: #cfd8dc !important; /* pastel abu */
        color: #37474f;
    }


    /* Styling Kolom Nama & No (Sticky Left) */
    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 10;
        background-color: #ffffff !important; 
        border-right: 1px solid #d0d7de  !important;
        border-bottom: 1px solid #e9ecef !important;
    }
    
    .sticky-col-header {
        /* position: sticky;
        left: 0;
        z-index: 30 !important;
        background-color: #f0f5fa !important;
        border-right: 2px solid #c1d6e0 !important;
        border: 1px solid #c1d6e0 !important; */
        border: none !important;
        background-color: #f7f7f7 !important;
    }

    /* ===== HEADER NO & NAMA ===== */
    .table-ledger thead th.sticky-col-header {
        background-color: #37474f !important;
        color: #fff !important;
    }

    /* REVISI 3: Indikator Sorting */
    .sortable {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .sortable:hover {
        background-color: #e2e6ea !important;
    }
    .sort-icon {
        font-size: 0.7em;
        margin-left: 5px;
        color: #adb5bd;
    }
    .sort-active .sort-icon {
        color: #5e72e4; /* Warna Primary */
    }

    /* Styling Lainnya Tetap */
    .col-nama {
        width: 220px !important;
        min-width: 220px !important;
        max-width: 220px !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .col-nilai {
        width: 55px !important;
        min-width: 55px !important;
        max-width: 55px !important;
        text-align: center;
        font-size: 0.85rem;
    }
    .bg-light-danger { background-color: #fde8e8 !important; color: #c81e1e !important; font-weight: bold; }
    .bg-rekap { background-color: #fff8e1 !important; color: #344767; }
    .bg-absen { background-color: #e3f2fd !important; color: #344767; }
    
    /* Border Body */
    .table-ledger tbody td {
        border: none !important;
        border-bottom: 1px solid #e0e0e0 !important;
        padding: 8px 6px;
    }
</style>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />
    
    <div class="container-fluid py-4 px-5">
        <div class="card shadow-xs border mb-4">
            <div class="card-header bg-gradient-primary py-3">
                <h6 class="text-white mb-0"><i class="fas fa-table me-2"></i> Ledger Nilai Siswa</h6>
            </div>
            <div class="card-body">
                {{-- Form Filter (Tetap Sama) --}}
                <form action="{{ route('ledger.ledger_index') }}" method="GET" class="row align-items-end mb-4">
                   <div class="col-md-3">
                        <label class="form-label font-weight-bold">Kelas</label>
                        <select name="id_kelas" class="form-select" required onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ $id_kelas == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                         <label class="form-label font-weight-bold">Semester</label>
                         <select name="semester" class="form-select" onchange="this.form.submit()">
                             <option value="Ganjil" {{ $semesterRaw == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                             <option value="Genap" {{ $semesterRaw == 'Genap' ? 'selected' : '' }}>Genap</option>
                         </select>
                    </div>
                    <div class="col-md-3">
                         <label class="form-label font-weight-bold">Tahun Ajaran</label>
                         <select name="tahun_ajaran" class="form-select" onchange="this.form.submit()">
                             @php $years = ['2024/2025', '2025/2026', '2026/2027']; @endphp
                             @foreach($years as $year)
                                 <option value="{{ $year }}" {{ $tahun_ajaran == $year ? 'selected' : '' }}>{{ $year }}</option>
                             @endforeach
                         </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="submit" class="btn btn-dark mb-0">Tampilkan Ledger</button>
                    </div>
                </form>

                @if($id_kelas)
                @php
                    $catLabels = [1 => 'Umum', 2 => 'Kejuruan', 3 => 'Pilihan', 4 => 'Mulok'];
                    $groupedMapel = $daftarMapel->groupBy('kategori');
                @endphp

                <div class="table-responsive p-0">
                    <table id="ledgerTable" class="table table-ledger align-items-center mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-col-header" style="width: 45px;">No</th>
                                {{-- SORTING NAMA --}}
                                <th rowspan="2" class="sticky-col-header col-nama sortable" onclick="sortByName()">
                                    Nama Siswa <i class="fas fa-sort sort-icon"></i>
                                </th>
                                
                                @foreach($groupedMapel as $catId => $mapels)
                                    <th colspan="{{ count($mapels)}}" class="kategori-header kategori-{{ $catId }}">
                                        {{ $catLabels[$catId] ?? 'Lainnya' }}
                                    </th>
                                @endforeach

                                <th colspan="2" class="kategori-header kategori-5">REKAP</th>
                                <th colspan="3" class="kategori-header kategori-6">ABSEN</th>
                            </tr>

                            <tr>
                                @foreach($groupedMapel as $catId => $mapels)
                                    @foreach($mapels as $mp)
                                        <th class="col-nilai kategori-sub kategori-{{ $catId }}" 
                                            data-bs-toggle="tooltip" title="{{ $mp->nama_mapel }}">
                                            {{ substr($mp->nama_singkat ?? $mp->nama_mapel, 0, 5) }}
                                        </th>
                                    @endforeach
                                @endforeach

                                <th class="kategori-sub kategori-5">JML</th>
                                {{-- SORTING AVG --}}
                                <th class="kategori-sub kategori-5" onclick="sortByAvg()">
                                    AVG <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <th class="kategori-sub kategori-6">S</th>
                                <th class="kategori-sub kategori-6">I</th>
                                <th class="kategori-sub kategori-6">A</th>
                        </thead>
                        <tbody id="ledgerBody">
                            @forelse($dataLedger as $idx => $row)
                            <tr>
                                <td class="text-center text-sm sticky-col row-number">{{ $idx + 1 }}</td>
                                <td class="text-sm sticky-col col-nama font-weight-bold text-dark" 
                                    data-bs-toggle="tooltip" title="{{ $row->nama_siswa }}">
                                    {{ $row->nama_siswa }}
                                </td>

                                @foreach($groupedMapel as $catId => $mapels)
                                    @foreach($mapels as $mp)
                                        @php $val = $row->scores[$mp->id_mapel] ?? 0; @endphp
                                        <td class="col-nilai text-sm {{ $val <= 0 ? 'bg-light-danger' : '' }}">
                                            {{ $val > 0 ? (int)$val : '-' }}
                                        </td>
                                    @endforeach
                                @endforeach

                                <td class="col-nilai text-sm font-weight-bold bg-rekap">{{ (int)$row->total }}</td>
                                <td class="col-nilai text-sm font-weight-bold text-primary bg-rekap avg-cell">{{ number_format($row->rata_rata, 1) }}</td>
                                
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->sakit }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->izin }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->alpha }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="100%" class="text-center py-4">Data tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- EXPORT BUTTON --}}
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('ledger.export.excel', request()->query()) }}"
                    class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>

                    <a href="{{ route('ledger.export.pdf', request()->query()) }}"
                    target="_blank"
                    class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

{{-- SCRIPT SORTING --}}
<script>
    let sortDirName = 'asc';
    let sortDirAvg = 'desc';

    function sortByName() {
        const table = document.getElementById("ledgerTable");
        const tbody = document.getElementById("ledgerBody");
        const rows = Array.from(tbody.querySelectorAll("tr"));

        // Toggle Sort Direction
        sortDirName = (sortDirName === 'asc') ? 'desc' : 'asc';

        rows.sort((a, b) => {
            const nameA = a.cells[1].innerText.toLowerCase(); // Kolom Nama index 1
            const nameB = b.cells[1].innerText.toLowerCase();

            if (nameA < nameB) return sortDirName === 'asc' ? -1 : 1;
            if (nameA > nameB) return sortDirName === 'asc' ? 1 : -1;
            return 0;
        });

        rebuildTable(tbody, rows);
    }

    function sortByAvg() {
        const tbody = document.getElementById("ledgerBody");
        const rows = Array.from(tbody.querySelectorAll("tr"));

        // Toggle Sort Direction
        sortDirAvg = (sortDirAvg === 'desc') ? 'asc' : 'desc';

        rows.sort((a, b) => {
            // Mengambil kolom AVG. Karena kolom dinamis, kita ambil dari class 'avg-cell' atau posisi dari belakang
            // Kolom AVG adalah ke-4 dari belakang (A, I, S, AVG)
            const avgA = parseFloat(a.cells[a.cells.length - 4].innerText) || 0;
            const avgB = parseFloat(b.cells[b.cells.length - 4].innerText) || 0;

            return sortDirAvg === 'asc' ? avgA - avgB : avgB - avgA;
        });

        rebuildTable(tbody, rows);
    }

    function rebuildTable(tbody, rows) {
        // Hapus body lama
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }

        // Masukkan row yang sudah diurutkan & Update Nomor Urut
        rows.forEach((row, index) => {
            row.cells[0].innerText = index + 1; // Update Kolom No (Index 0)
            tbody.appendChild(row);
        });
    }
</script>
@endsection