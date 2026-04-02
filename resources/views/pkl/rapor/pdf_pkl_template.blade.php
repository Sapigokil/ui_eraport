{{-- File: resources/views/pkl/rapor/pdf_pkl_template.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Hasil Belajar PKL</title>
    <style>
        /* 1. MARGIN KERTAS */
        @page {
            margin: 30px 50px 30px 50px; 
        }      
        
        /* 2. FONT BODY */
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 10pt; 
            line-height: 1.15; 
            color: #000;
        }

        .kop-sekolah {
            text-align: center;
            font-weight: bold;
            font-size: 14pt; 
            margin-bottom: 2px;
        }
        .tahun-ajaran {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 10px; 
        }

        /* 3. Tabel Info Siswa */
        .info-table {
            width: 95%; 
            border-collapse: collapse;
            margin-bottom: 10px; 
            font-size: 10pt; 
            margin-left: 35px; 
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 0; 
        }
        .info-label { width: 140px; }
        .info-titik { width: 15px; text-align: center; }
        .info-value { font-weight: normal; }

        /* 4. Tabel Utama (Nilai) */
        .main-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px; 
            font-size: 10pt; 
        }
        .main-table th, .main-table td { 
            border: 1px solid black; 
            padding: 4px 6px; 
            text-align: left; 
            vertical-align: top;
        }
        .main-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center !important; }
        .text-justify { text-align: justify !important; text-justify: inter-word; }

        /* 5. Tabel Catatan & Kehadiran */
        .bottom-section {
            width: 100%;
            margin-top: 5px;
            page-break-inside: avoid;
            font-size: 10pt; 
        }
        .catatan-box {
            border: 1px solid black;
            padding: 6px; 
            min-height: 30px; 
            margin-bottom: 10px;
            text-align: justify;
        }
        .kehadiran-table {
            width: 40%;
            border-collapse: collapse;
            font-size: 10pt; 
        }
        .kehadiran-table th, .kehadiran-table td {
            border: 1px solid black;
            padding: 2px 5px; 
        }
        .kehadiran-table th {
            background-color: #f2f2f2;
            text-align: left;
        }

        /* 6. AREA TANDA TANGAN SMART (REVISI PIXEL) */
        .keep-together {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        
        .ttd-wrapper {
            display: inline-block;
            border-bottom: 1px solid #000; 
            font-weight: bold;
            padding-bottom: 2px;
            text-align: center;
            white-space: nowrap;
        }

        .ttd-table {
            width: 100%;
            margin-top: 15px; 
            border-collapse: collapse;
            font-size: 10pt; 
        }
        .ttd-table td {
            border: none;
            text-align: center;
            vertical-align: top;
            width: 50%;
        }
        .ttd-space {
            height: 65px; 
        }

        .ttd-kepsek {
            width: 100%;
            margin-top: 15px; 
            text-align: center;
            font-size: 10pt;
        }
        .ttd-kepsek p {
            margin: 0;
            padding: 0;
        }

        .footer-fixed {
            position: fixed;
            bottom: -30px; 
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>

    @php
        // LOGIKA SMART LINE WIDTH DENGAN PIXEL
        $namaGuru = $raporSiswa->nama_guru_snapshot ?? 'Nama Pembimbing';
        $namaInstruktur = $raporSiswa->nama_instruktur_snapshot ?? 'Nama Instruktur';
        $namaKepsek = $infoSekolah->nama_kepsek ?? 'Nama Kepala Sekolah';

        // Estimasikan lebar 1 karakter Arial 10pt adalah ~7.5px
        $charWidth = 7.5;

        // 1. Baris Pertama (Guru & Instruktur)
        $lenGuru = strlen($namaGuru);
        $lenInst = strlen($namaInstruktur);
        $maxCharsBaris1 = ($lenGuru > $lenInst ? $lenGuru : $lenInst) + 0;
        $widthBaris1 = $maxCharsBaris1 * $charWidth;

        // 2. Baris Kedua (Kepsek)
        $widthKepsek = (strlen($namaKepsek) + 6) * $charWidth;
    @endphp

    <div class="kop-sekolah">RAPOR PKL SMK NEGERI 1 SALATIGA</div>
    <div class="tahun-ajaran">Tahun Ajaran {{ $raporSiswa->tahun_ajaran }}</div>

    <br>
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Peserta Didik</td>
            <td class="info-titik">:</td>
            <td class="info-value"><b>{{ strtoupper($raporSiswa->nama_siswa_snapshot) }}</b></td>
        </tr>
        <tr>
            <td class="info-label">NISN</td>
            <td class="info-titik">:</td>
            <td class="info-value">{{ $raporSiswa->nisn_snapshot ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Kelas</td>
            <td class="info-titik">:</td>
            <td class="info-value">{{ $raporSiswa->kelas_snapshot }}</td>
        </tr>
        <tr>
            <td class="info-label">Program Keahlian</td>
            <td class="info-titik">:</td>
            <td class="info-value">{{ $raporSiswa->program_keahlian_snapshot ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Konsentrasi Keahlian</td>
            <td class="info-titik">:</td>
            <td class="info-value">{{ $raporSiswa->konsentrasi_keahlian_snapshot ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Tempat PKL</td>
            <td class="info-titik">:</td>
            <td class="info-value">{{ $raporSiswa->tempat_pkl_snapshot ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Tanggal PKL</td>
            <td class="info-titik">:</td>
            <td class="info-value">
                Mulai: {{ $raporSiswa->tanggal_mulai_snapshot ? \Carbon\Carbon::parse($raporSiswa->tanggal_mulai_snapshot)->locale('id')->translatedFormat('d F Y') : '-' }} &nbsp;&nbsp;&nbsp; 
                Selesai: {{ $raporSiswa->tanggal_selesai_snapshot ? \Carbon\Carbon::parse($raporSiswa->tanggal_selesai_snapshot)->locale('id')->translatedFormat('d F Y') : '-' }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Nama Instruktur</td>
            <td class="info-titik">:</td>
            <td class="info-value">{{ $namaInstruktur }}</td>
        </tr>
        <tr>
            <td class="info-label">Nama Pembimbing</td>
            <td class="info-titik">:</td>
            <td class="info-value">{{ $namaGuru }}</td>
        </tr>
    </table>
    <br>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Tujuan Pembelajaran</th>
                <th style="width: 10%;">Skor</th>
                <th style="width: 50%;">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($raporNilai as $index => $nilai)
            <tr>
                <td class="text-center">{{ $index + 1 }}.</td>
                <td>{{ $nilai->nama_tp_snapshot }}</td>
                <td class="text-center">{{ number_format((float)$nilai->nilai_rata_rata, 0, '', '') }}</td>
                <td class="text-justify">{{ $nilai->deskripsi_gabungan }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center">Belum ada data nilai.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="bottom-section">
        <div><strong>Catatan:</strong></div>
        <div class="catatan-box">{{ $raporSiswa->catatan_pembimbing ?? '-' }}</div>

        <table class="kehadiran-table">
            <thead><tr><th colspan="2">Kehadiran</th></tr></thead>
            <tbody>
                <tr><td>Sakit</td><td>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $raporSiswa->sakit ?? 0 }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Hari</td></tr>
                <tr><td>Ijin</td><td>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $raporSiswa->izin ?? 0 }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Hari</td></tr>
                <tr><td>Tanpa Keterangan</td><td>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $raporSiswa->alpa ?? 0 }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Hari</td></tr>
            </tbody>
        </table>
    </div>

    <div class="keep-together">
        <table class="ttd-table">
            <tr>
                <td>
                    <br>Guru Pembimbing<br>
                    <div class="ttd-space"></div>
                    <div class="ttd-wrapper" style="width: {{ $widthBaris1 }}px;">{{ $namaGuru }}</div>
                </td>
                <td>
                    {{ ucwords(strtolower($infoSekolah->kota_kab ?? 'Salatiga')) }}, {{ $tanggalCetakRapor->locale('id')->translatedFormat('d F Y') }}<br>
                    Pembimbing Dunia Kerja<br>
                    <div class="ttd-space"></div>
                    <div class="ttd-wrapper" style="width: {{ $widthBaris1 }}px;">{{ $namaInstruktur }}</div>
                </td>
            </tr>
        </table>
        <br>

        <div class="ttd-kepsek">
            <p>Mengetahui,</p>
            <p>Kepala Sekolah</p>
            <div class="ttd-space" style="height: 65px;"></div>
            <div class="ttd-wrapper" style="width: {{ $widthKepsek }}px;">{{ $namaKepsek }}</div>
            <br>
            <span>NIP. {{ $infoSekolah->nip_kepsek ?? '-' }}</span>
        </div>
    </div>

    <div class="footer-fixed"></div>
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("helvetica", "italic");
            $size = 9;
            $color = array(0, 0, 0);
            $width = $pdf->get_width();
            $height = $pdf->get_height();
            $marginSide = 50; 

            // Footer text position (30px from bottom)
            $y = $height - 20; 

            $leftText = html_entity_decode(
                "{{ strtoupper($raporSiswa->nama_siswa_snapshot) }} / {{ $raporSiswa->nisn_snapshot }}",
                ENT_QUOTES,
                'UTF-8'
            );
            $pdf->page_text($marginSide, $y, $leftText, $font, $size, $color);

            $rightText = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $x = $width - $marginSide - 115; 
            $pdf->page_text($x, $y, $rightText, $font, $size, $color);
        }
    </script>
</body>
</html>