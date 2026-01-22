<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle ?? 'Ledger Nilai' }}</title>
    <style>
    /* KONFIGURASI HALAMAN CETAK (BROWSER) */
    @page {
        size: A4 landscape; /* Paksa Landscape */
        margin: 10mm 15mm 10mm 15mm; /* Margin atas kanan bawah kiri */
    }

    body {
        font-family: Arial, sans-serif; /* Font standar browser yg bagus */
        font-size: 11px; /* Sedikit diperbesar karena browser scalingnya beda dgn dompdf */
        -webkit-print-color-adjust: exact; /* Agar background warna ikut ter-print */
        print-color-adjust: exact;
    }

    /* ===== HEADER ===== */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .header-table td {
        border: none;
        vertical-align: middle;
    }

    .school-name {
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .school-address {
        font-size: 10px;
        line-height: 1.3;
    }

    .ledger-title {
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        text-align: right;
    }

    .ledger-info {
        font-size: 11px;
        text-align: right;
        margin-top: 4px;
    }

    /* ===== TABLE ===== */
    table.main-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* WAJIB: Agar lebar kolom konsisten */
    }

    table.main-table th, 
    table.main-table td {
        border: 1px solid #000;
        padding: 4px 2px;
        text-align: center;
        vertical-align: middle;
        word-wrap: break-word; 
        overflow-wrap: break-word;
    }

    table.main-table th {
        background-color: #f1f1f1 !important; /* Warna header */
        font-weight: bold;
        font-size: 10px;
    }
    
    table.main-table td {
        font-size: 10px;
    }

    .text-left { text-align: left !important; padding-left: 4px !important; }
    .font-bold { font-weight: bold; }

    /* TTD SECTION */
    .ttd-table {
        width: 100%;
        margin-top: 30px;
        page-break-inside: avoid; /* Jangan terpotong halaman */
    }
    .space-ttd { height: 70px; }

    /* TOMBOL PRINT (Hanya muncul di layar, hilang saat diprint) */
    .no-print {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #333;
        color: #fff;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-family: sans-serif;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        cursor: pointer;
        z-index: 9999;
    }
    .no-print:hover { background: #000; }

    @media print {
        .no-print { display: none; }
    }
    </style>
</head>
<body>

{{-- Tombol Print Manual (untuk jaga-jaga) --}}
<a href="javascript:window.print()" class="no-print">🖨️ Cetak / Simpan PDF</a>

{{-- HEADER --}}
<table class="header-table">
    <tr>
        <td width="60%">
            <table style="width: 100%;">
                <tr>
                    <td width="80">
                        {{-- Pastikan path gambar benar, gunakan asset() untuk browser view --}}
                        <img src="{{ asset('assets/img/theme/logo-sekolah.png') }}" width="75">
                    </td>
                    <td class="text-left">
                        <div class="school-name">{{ $namaSekolah }}</div>
                        <div class="school-address">{{ $alamatSekolah }}</div>
                    </td>
                </tr>
            </table>
        </td>

        <td width="40%">
            <div class="ledger-title">Daftar Nilai Ledger Siswa</div>
            <div class="ledger-info">
                Kelas: {{ $kelas->nama_kelas ?? '-' }} |
                Semester: {{ $semesterRaw }} |
                Tahun Ajaran: {{ $tahun_ajaran }}
            </div>
        </td>
    </tr>
</table>

@php
    $jumlahMapel = count($daftarMapel);

    // =========================================================
    // KONFIGURASI LEBAR KOLOM (PERSENTASE)
    // =========================================================
    
    $wRank = 3;   
    $wNo   = 3;   
    $wNama = 25;  // Nama Siswa Lebar
    
    $wTotal = 5;
    $wRata  = 5;
    $wAbsen = 2;  // S, I, A

    $totalFixed = $wRank + $wNo + $wNama + $wTotal + $wRata + ($wAbsen * 3);
    
    $sisaLebar = 100 - $totalFixed;
    $jumlahKolomDinamis = $jumlahMapel + 2; // + NIS + NISN
    
    if ($jumlahKolomDinamis > 0) {
        $wDinamis = $sisaLebar / $jumlahKolomDinamis;
    } else {
        $wDinamis = 5; 
    }
@endphp

<table class="main-table">
    <thead>
        <tr>
            {{-- Header dengan Width Langsung --}}
            <th style="width: {{ $wRank }}%;">Rank</th>
            <th style="width: {{ $wNo }}%;">No</th>
            <th style="width: {{ $wNama }}%;">Nama Siswa</th>
            
            <th style="width: {{ $wDinamis }}%;">NIS</th>
            <th style="width: {{ $wDinamis }}%;">NISN</th>

            @foreach($daftarMapel as $mp)
                <th style="width: {{ $wDinamis }}%;">
                    <div style="font-size: 9px;">
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
            <td class="text-left">{{ $row->nama_siswa }}</td>

            <td>{{ $row->nipd ?? '-' }}</td>
            <td>{{ $row->nisn ?? '-' }}</td>

            @foreach($daftarMapel as $mp)
                @php $nilai = $row->scores[$mp->id_mapel] ?? 0; @endphp
                <td>{{ $nilai > 0 ? (int) $nilai : '-' }}</td>
            @endforeach

            <td class="font-bold">{{ (int) $row->total }}</td>
            <td class="font-bold">{{ number_format($row->rata_rata, 1) }}</td>
            
            <td>{{ $row->absensi->sakit }}</td>
            <td>{{ $row->absensi->izin }}</td>
            <td>{{ $row->absensi->alpha }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="ttd-table">
    <tr>
        <td style="width: 75%; border: none;"></td>
        <td style="width: 25%; border: none; text-align: left;">
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

{{-- SCRIPT AUTO PRINT --}}
<script>
    window.onload = function() {
        window.print();
    }
</script>

</body>
</html>