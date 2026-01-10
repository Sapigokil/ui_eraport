<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
    @page {
        margin: 140px 30px 40px 30px;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
    }

    /* ===== HEADER ===== */
    .header {
        position: fixed;
        top: -100px;
        left: 0;
        right: 0;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    .header-table td {
        border: none;
        text-align: left !important;
    }

    .school-name {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .school-address {
        font-size: 9px;
        line-height: 1.3;
    }

    .ledger-title {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        text-align: right;
    }

    .ledger-info {
        font-size: 10px;
        text-align: right;
        margin-top: 4px;
    }

    /* ===== TABLE ===== */
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
    }

    th {
        background: #f1f1f1;
    }

    .text-left {
        text-align: left;
    }
</style>
</head>
<body>

{{-- HEADER (muncul di setiap halaman) --}}
<div class="header">
    <table class="header-table">
        <tr>
            <!-- KIRI -->
            <td width="60%">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="70" style="vertical-align: middle;">
                            <img src="{{ public_path('assets/img/theme/logo-sekolah.png') }}" width="75">
                        </td>
                        <td style="padding-left:6px; vertical-align: middle; text-align: left;">
                        <div class="school-name">{{ $namaSekolah }}</div>
                        <div class="school-address">
                            {{ $alamatSekolah }}
                        </div>
                    </td>
                    </tr>
                </table>
            </td>

            <!-- KANAN -->
            <td width="40%" style="vertical-align: middle;">
                <div class="ledger-title">Daftar Nilai Ledger Siswa</div>
                <div class="ledger-info">
                    Kelas: {{ $kelas->nama_kelas ?? '-' }} |
                    Semester: {{ $semesterRaw }} |
                    Tahun Ajaran: {{ $tahun_ajaran }}
                </div>
            </td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>

            @foreach($daftarMapel as $mp)
                <th>{{ $mp->nama_singkat ?? $mp->nama_mapel }}</th>
            @endforeach

            <th>Total</th>
            <th>Rata-rata</th>
            <th>S</th>
            <th>I</th>
            <th>A</th>
        </tr>
    </thead>

    <tbody>
        @foreach($dataLedger as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="text-left">{{ $row->nama_siswa }}</td>

            @foreach($daftarMapel as $mp)
                @php
                    $nilai = $row->scores[$mp->id_mapel] ?? 0;
                @endphp
                <td>
                    {{ $nilai > 0 ? (int) $nilai : '-' }}
                </td>
            @endforeach

            <td>{{ (int) $row->total }}</td>
            <td>{{ $row->rata_rata }}</td>
            <td>{{ $row->absensi->sakit }}</td>
            <td>{{ $row->absensi->izin }}</td>
            <td>{{ $row->absensi->alpha }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
