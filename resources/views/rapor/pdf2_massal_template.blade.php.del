<!DOCTYPE html>
<html>
<head>
    <title>Cetak Massal Rapor</title>
    <style>
        @page {
        size: A4;
        margin: 50px 50px 60px 50px;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
        line-height: 1.45;
        margin: 0;
        padding: 0;
    }

    /* ========== HEADER RAPOR ========== */
    .rapor-header {
        width: 100%;
        border-collapse: collapse;
        font-size: 13pt;
        margin-bottom: 10px;
    }

    .rapor-header td {
        padding: 2px 0;
        vertical-align: top;
        font-size: 11pt;
        border: none;
    }

    .judul-header {
        text-align: center;
        font-size: 22pt;
        font-weight: bold;
        padding: 12px 0 12px;
        margin-top: 50px;
    }

    .h-label { display:inline-block; width:110px; white-space:nowrap; }
    .h-sep   { display:inline-block; width:12px; text-align:center; }
    .h-val   { display:inline-block; }
    .nama-siswa { font-weight:bold; }

    .no-border-header {
        padding: 0 !important;
        border: none !important;
    }

    .no-border-header table,
    .no-border-header td {
        border: none !important;
    }

    /* ========== TABEL NILAI ========== */
    .main-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    /* FORCE HEADER SIZE (OVERRIDE main-table) */
    .main-table .rapor-header td {
        font-size: 11pt;
    }

    .main-table td.judul-header {
        font-size: 22pt;
        font-weight: bold;
        text-align: center;
        padding: 18px 0 10px;
    }

    .main-table th,
    .main-table td {
        border: 1px solid #000;
        padding: 6px;
        font-size: 9pt;
    }

    .main-table thead {
        display: table-header-group;
    }

    .kategori-row {
        background: #f2f2f2;
        font-weight: bold;
        text-transform: uppercase;
    }

    .page-avoid {
        page-break-inside: avoid;
    }

    /* ========== TABEL BAWAH ========== */
    .container-bawah {
        width: 100%;
        border-collapse: collapse;
    }

    .tabel-info-rapor {
        width: 100%;
        border-collapse: collapse;
    }

    .tabel-info-rapor th,
    .tabel-info-rapor td {
        border: 1px solid #000;
        padding: 5px;
        font-size: 10pt;
    }

    .tabel-info-rapor th {
        background: #f2f2f2;
        text-align: center;
    }

    .bg-light {
        background-color: #f2f2f2;
    }

    /* ========== TTD ========== */
    .table-ttd {
        width: 100%;
        margin-top: 30px;
        border-collapse: collapse;
    }

    .table-ttd td {
        text-align: center;
        font-size: 9pt;
    }

    .space-ttd {
        height: 70px;
    }

    .ttd-kepsek {
        width: 100%;
        margin-top: 30px; /* Jarak dari tabel TTD di atasnya */
        text-align: center;
    }
    .ttd-kepsek .nama-kepsek {
        font-weight: bold;
        text-decoration: underline;
        display: block;
        margin-top: 70px; /* Ruang kosong untuk tanda tangan & stempel */
    }

    .blok-akhir {
        page-break-before: always;
        page-break-inside: avoid;
    }


    /* ========== FOOTER ========== */
    .footer-line {
        position: fixed;
        bottom: -30px;
        left: 0;
        right: 0;
        border-top: 1px solid #000;
    }

    </style>
</head>
<body>

<div class="footer-line"></div>

@foreach($allData as $data)

@php
    $mapelPage1 = [];
    $mapelNext = [];

    $limitPage1 = 12; // bisa kamu sesuaikan
    $counter = 0;

    foreach($data['mapelGroup'] as $kategori => $mapels) {
        foreach($mapels as $m) {
            if ($counter < $limitPage1) {
                $mapelPage1[$kategori][] = $m;
            } else {
                $mapelNext[$kategori][] = $m;
            }
            $counter++;
        }
    }
@endphp

<!-- ================= NILAI ================= -->
<table class="main-table">
    <thead>
        @include('rapor.rapor_header', [
            'data' => $data,
            'showTitle' => true
        ])
        <tr>
            <th width="5%">No</th>
            <th width="25%">Mata Pelajaran</th>
            <th width="10%">Nilai Akhir</th>
            <th>Capaian Kompetensi</th>
        </tr>
    </thead>
    <tbody>
    @php
        $labelKategori = [
            1 => 'MATA PELAJARAN UMUM',
            2 => 'MATA PELAJARAN KEJURUAN',
            3 => 'MATA PELAJARAN PILIHAN',
            4 => 'MUATAN LOKAL',
        ];
    @endphp

    @foreach($data['mapelGroup'] as $kategori => $mapels)

    {{-- BARIS KATEGORI --}}
    <tr class="kategori-row">
        <td colspan="4">
            {{ $labelKategori[$kategori] ?? 'MATA PELAJARAN LAINNYA' }}
        </td>
    </tr>

    {{-- BARIS MAPEL --}}
    @foreach($mapels as $i => $m)
    <tr>
        <td align="center">{{ $i + 1 }}</td>
        <td>{{ $m->nama_mapel }}</td>
        <td align="center">{{ round($m->nilai_akhir) }}</td>
        <td>{{ $m->capaian }}</td>
    </tr>
    @endforeach
@endforeach
    </tbody>
</table>


<div class="page-break"></div>

<!-- ================= EKSKUL, ABSENSI & CATATAN ================= -->
<div class="blok-akhir">

    {{-- KOKURIKULER --}}
    <table class="main-table page-avoid">
        <thead>
            <tr>
                <td colspan="4" class="no-border-header">
                    @include('rapor.rapor_header', ['data' => $data])
                </td>
            </tr>
            <tr class="bg-light">
                <th colspan="4">KOKURIKULER</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4">
                    {{ $data['catatan']->kokurikuler ?? '-' }}
                </td>
            </tr>
        </tbody>
    </table>

{{-- EKSKUL --}}
    <table class="main-table page-avoid">
        <thead>
            <tr class="bg-light">
                <th width="5%">No</th>
                <th width="35%">Kegiatan Ekstrakurikuler</th>
                <th width="15%">Predikat</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @for($i=0;$i<3;$i++)
            <tr>
                <td align="center">{{ $i+1 }}</td>
                @if(isset($data['dataEkskul'][$i]))
                    <td>{{ $data['dataEkskul'][$i]->nama }}</td>
                    <td align="center">{{ $data['dataEkskul'][$i]->predikat }}</td>
                    <td>{{ $data['dataEkskul'][$i]->keterangan }}</td>
                @else
                    <td>-</td><td>-</td><td>-</td>
                @endif
            </tr>
            @endfor
        </tbody>
    </table>

<table class="container-bawah">

    <tr>
        <td width="45%">
            <table class="tabel-info-rapor">
                <tr><th colspan="2">Ketidakhadiran</th></tr>
                <tr><td>Sakit</td><td align="center">{{ $data['catatan']->sakit ?? 0 }} hari</td></tr>
                <tr><td>Izin</td><td align="center">{{ $data['catatan']->ijin ?? 0 }} hari</td></tr>
                <tr><td>Alpha</td><td align="center">{{ $data['catatan']->alpha ?? 0 }} hari</td></tr>
            </table>
        </td>
        <td width="5%"></td>
        <td width="50%">
            <table class="tabel-info-rapor">
                <tr><th>Catatan Wali Kelas</th></tr>
                <tr><td style="height:60px">{{ $data['catatan']->catatan_wali_kelas ?? '-' }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="table-ttd">
    <tr>
        <td width="35%">Orang Tua/Wali<br><div class="space-ttd"></div>....................</td>
        <td></td>
        <td width="35%">
            Salatiga, {{ date('d F Y') }}<br>
            Wali Kelas<br>
            <div class="space-ttd"></div>
            <strong>{{ $data['nama_wali'] }}</strong><br>
            NIP. {{ $data['nip_wali'] }}
        </td>
    </tr>
</table>

<div class="ttd-kepsek">
    <p style="margin-bottom: 5px;">Mengetahui,</p>
    <p style="margin-top: 0;">Kepala Sekolah</p>

    {{-- Nama Kepala Sekolah diambil dari variabel info --}}
    <span class="nama-kepsek">
        {{ $data['nama_kepsek'] }}
    </span>

    {{-- NIP Kepala Sekolah --}}
    <span>NIP. {{ $data['nip_kepsek'] }}</span>
</div>

</div>

<div style="page-break-after: always;"></div>

@endforeach

<script type="text/php">
if (isset($pdf)) {
    $font = $fontMetrics->get_font("helvetica", "italic");
    $pdf->page_text(50, $pdf->get_height() - 33, "E-Rapor SMK | Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, 9);
}
</script>

</body>
</html>
