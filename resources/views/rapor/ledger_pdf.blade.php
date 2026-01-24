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
        /* WAJIB: table-layout fixed agar % lebar kolom dipatuhi */
        table-layout: fixed; 
    }

    th, td {
        border: 1px solid #000;
        padding: 2px 2px;
        text-align: center;
        vertical-align: middle;
        /* Agar teks panjang turun ke bawah (wrap), tidak melebarkan kolom */
        word-wrap: break-word; 
        overflow-wrap: break-word;
    }

    th {
        background: #f1f1f1;
        font-weight: bold;
        font-size: 9px;
    }

    .text-left {
        text-align: left;
    }
    .space-ttd {
        height: 70px;
    }

    .font-bold {
        font-weight: bold;
    }

    .ttd-table {
        width: 100%;
        margin-top: 40px;
    }

    .ttd-table td {
        border: none;
        text-align: left;
        vertical-align: top;
    }

    </style>
</head>
<body>

{{-- HEADER (muncul di setiap halaman) --}}
<div class="header">
    <table class="header-table" style="table-layout: auto;">
        <tr>
            <td width="60%">
                <table cellpadding="0" cellspacing="0" style="table-layout: auto;">
                    <tr>
                        <td width="70" style="vertical-align: middle; border: none;">
                            <img src="{{ public_path('assets/img/theme/logo-sekolah-sml.png') }}" width="75">
                        </td>
                        <td style="padding-left:6px; vertical-align: middle; text-align: left; border: none;">
                        <div class="school-name">{{ $namaSekolah }}</div>
                        <div class="school-address">
                            {{ $alamatSekolah }}
                        </div>
                    </td>
                    </tr>
                </table>
            </td>

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

@php
    $jumlahMapel = count($daftarMapel);

    // =========================================================
    // 1. CONFIG WIDTH (FIXED COLUMNS)
    // =========================================================
    // Total Fixed harus di bawah 100% agar sisa bisa dibagi ke mapel
    
    $wRank = 3;   // Kecil
    $wNo   = 3;   // Kecil
    $wNama = 11;  // Lebar (Nama Siswa)
    
    $wTotal = 3.5;
    $wRata  = 3.5;
    
    $wAbsen = 2;  // S, I, A (Masing-masing 2%)

    // Hitung total yg sudah dipakai
    // Rank(3) + No(3) + Nama(25) + Total(5) + Rata(5) + S(2) + I(2) + A(2)
    $totalFixed = $wRank + $wNo + $wNama + $wTotal + $wRata + ($wAbsen * 3);
    
    // =========================================================
    // 2. HITUNG SISA UNTUK KOLOM DINAMIS
    // =========================================================
    // Kolom Dinamis = Mapel + NIS + NISN
    // Kita samakan lebar NIS/NISN dengan Nilai Mapel agar rapi
    
    $sisaLebar = 100 - $totalFixed;
    $jumlahKolomDinamis = $jumlahMapel + 2; // +2 untuk NIS dan NISN
    
    if ($jumlahKolomDinamis > 0) {
        $wDinamis = $sisaLebar / $jumlahKolomDinamis;
    } else {
        $wDinamis = 5; // Fallback jika tidak ada mapel
    }
@endphp

<table>
    <thead>
        <tr>
            {{-- PERBAIKAN: Style Width dipasang LANGSUNG di TH --}}
            
            <th style="width: {{ $wRank }}%;">Rank</th>
            <th style="width: {{ $wNo }}%;">No</th>
            <th style="width: {{ $wNama }}%;">Nama Siswa</th>
            
            {{-- NIS & NISN (Pakai Lebar Dinamis) --}}
            <th style="width: {{ $wDinamis }}%;">NIS</th>
            <th style="width: {{ $wDinamis }}%;">NISN</th>

            @foreach($daftarMapel as $mp)
                <th style="width: {{ $wDinamis }}%;">
                    <div style="font-size: 8px;">
                        {{ $mp->nama_singkat ?? $mp->nama_mapel }}
                    </div>
                </th>
            @endforeach

            <th style="width: {{ $wTotal }}%;">Total</th>
            <th style="width: {{ $wRata }}%;">Rata</th> 
            
            <th style="width: {{ $wAbsen }}%;">S</th>
            <th style="width: {{ $wAbsen }}%;">I</th>
            <th style="width: {{ $wAbsen }}%;">A</th>
        </tr>
    </thead>

    <tbody>
        @foreach($dataLedger as $i => $row)
        <tr>
            <td>
                @if(request('urut','ranking') === 'ranking')
                    {{ $loop->iteration }}
                @else
                    -
                @endif
            </td>
            <td>{{ $i + 1 }}</td>
            <td class="text-left" style="font-size: 9px;">
                {{ $row->nama_siswa }}
            </td>

            <td style="font-size: 9px;">{{ $row->nipd ?? '-' }}</td>
            <td style="font-size: 9px;">{{ $row->nisn ?? '-' }}</td>

            @foreach($daftarMapel as $mp)
                @php
                    $nilai = $row->scores[$mp->id_mapel] ?? 0;
                @endphp
                <td style="font-size: 9px;">
                    {{ $nilai > 0 ? (int) $nilai : '-' }}
                </td>
            @endforeach

            <td style="font-weight:bold;">{{ (int) $row->total }}</td>
            <td style="font-weight:bold;">{{ number_format($row->rata_rata, 1) }}</td>
            
            <td>{{ $row->absensi->sakit }}</td>
            <td>{{ $row->absensi->izin }}</td>
            <td>{{ $row->absensi->alpha }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="ttd-table" style="table-layout: auto;">
    <tr>
        <td style="width: 75%; border: none;"></td>
        <td style="width: 25%; border: none;">
            Salatiga, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
            Wali Kelas,
            <div class="space-ttd"></div>
            <span style="font-weight:bold; text-decoration: underline;">
                {{ $nama_wali }}
            </span><br>
            NIP. {{ $nip_wali }}
        </td>
    </tr>
</table>

</body>
</html>