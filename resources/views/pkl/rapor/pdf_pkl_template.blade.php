{{-- File: resources/views/pkl/rapor/pdf_pkl_template.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Hasil Belajar PKL</title>
    <style>
        @page {
            margin: 40px 50px 50px 50px; 
        }      
        
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 11pt; 
            line-height: 1.3; 
            color: #000;
        }

        /* Header Judul */
        .kop-sekolah {
            text-align: center;
            font-weight: bold;
            font-size: 16pt; /* REVISI: Dinaikkan 1 level (dari 14pt) */
            margin-bottom: 0px;
        }
        .tahun-ajaran {
            text-align: center;
            font-weight: bold; /* REVISI: Dicetak Bold */
            font-size: 12pt;
            margin-bottom: 20px; 
        }

        /* 1. Tabel Info Siswa */
        .info-table {
            width: 95%; 
            border-collapse: collapse;
            margin-bottom: 20px; 
            font-size: 11pt; 
            margin-left: 35px; 
        }
        .info-table td {
            vertical-align: top;
            padding: 3px 0; 
        }
        .info-label { width: 160px; }
        .info-titik { width: 15px; text-align: center; }
        .info-value { font-weight: normal; }

        /* 2. Tabel Utama (Nilai) */
        .main-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
            font-size: 10pt; 
        }
        .main-table th, .main-table td { 
            border: 1px solid black; 
            padding: 3px 6px; 
            text-align: left; 
            vertical-align: top;
        }
        .main-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            padding: 4px 6px;
        }
        .text-center { text-align: center !important; }
        .text-justify { text-align: justify; }

        /* 3. Tabel Catatan & Kehadiran */
        .bottom-section {
            width: 100%;
            margin-top: 10px;
            page-break-inside: avoid;
            font-size: 10pt; 
        }

        .catatan-box {
            border: 1px solid black;
            padding: 6px 8px; 
            min-height: 40px; 
            margin-bottom: 15px;
            text-align: justify;
        }

        .kehadiran-table {
            width: 40%;
            border-collapse: collapse;
            font-size: 10pt; 
        }
        .kehadiran-table th, .kehadiran-table td {
            border: 1px solid black;
            padding: 3px 6px; 
        }
        .kehadiran-table th {
            background-color: #f2f2f2;
            text-align: left;
        }

        /* 4. Tanda Tangan */
        .ttd-table {
            width: 100%;
            margin-top: 25px; 
            border-collapse: collapse;
            page-break-inside: avoid;
            font-size: 10pt; /* REVISI: Font diturunkan 1 level (dari 11pt) */
        }
        .ttd-table td {
            border: none;
            text-align: center;
            vertical-align: top;
            width: 50%;
        }
        .ttd-space {
            height: 70px; 
        }
        .garis-bawah {
            text-decoration: underline;
            font-weight: bold;
        }

        /* Footer Garis Bawah */
        .footer-fixed {
            position: fixed;
            bottom: -30px; 
            left: 0;
            right: 0;
            height: 40px;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="kop-sekolah">{{ strtoupper($infoSekolah->nama_sekolah ?? 'SMKN 1 SALATIGA') }}</div>
    <div class="tahun-ajaran">Tahun Ajaran {{ $raporSiswa->tahun_ajaran }}</div>

    {{-- INFO SISWA & PKL --}}
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Peserta Didik</td>
            <td class="info-titik">:</td>
            <td class="info-value">{{ strtoupper($raporSiswa->nama_siswa_snapshot) }}</td>
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
                Mulai: {{ $raporSiswa->tanggal_mulai_snapshot ? \Carbon\Carbon::parse($raporSiswa->tanggal_mulai_snapshot)->translatedFormat('d F Y') : '-' }} &nbsp;&nbsp;&nbsp; 
                Selesai: {{ $raporSiswa->tanggal_selesai_snapshot ? \Carbon\Carbon::parse($raporSiswa->tanggal_selesai_snapshot)->translatedFormat('d F Y') : '-' }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Nama Instruktur</td>
            <td class="info-titik">:</td>
            <td class="info-value">{{ $raporSiswa->nama_instruktur_snapshot ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Nama Pembimbing</td>
            <td class="info-titik">:</td>
            <td class="info-value">{{ $raporSiswa->nama_guru_snapshot ?? '-' }}</td>
        </tr>
    </table>

    {{-- TABEL NILAI PKL --}}
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
                <td class="text-center">
                    {{ number_format((float)$nilai->nilai_rata_rata, 2, '.', '') }}
                </td>
                <td class="text-justify">{{ $nilai->deskripsi_gabungan }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Belum ada data nilai.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="bottom-section">
        {{-- CATATAN PEMBIMBING --}}
        <div><strong>Catatan:</strong></div>
        <div class="catatan-box">
            {{ $raporSiswa->catatan_pembimbing ?? '-' }}
        </div>

        {{-- TABEL KEHADIRAN (REVISI: Format : {nilai} Hari tanpa memecah kolom) --}}
        <table class="kehadiran-table">
            <thead>
                <tr>
                    <th colspan="2">Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 50%;">Sakit</td>
                    <td style="width: 50%;">:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $raporSiswa->sakit ?? 0 }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Hari</td>
                </tr>
                <tr>
                    <td>Ijin</td>
                    <td>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $raporSiswa->izin ?? 0 }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Hari</td>
                </tr>
                <tr>
                    <td>Tanpa Keterangan</td>
                    <td>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $raporSiswa->alpa ?? 0 }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Hari</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- TANDA TANGAN --}}
    <table class="ttd-table">
        <tr>
            <td>
                <br>
                Guru Pembimbing<br>
                <div class="ttd-space"></div>
                <span class="garis-bawah">{{ $raporSiswa->nama_guru_snapshot ?? '_______________________' }}</span>
            </td>
            <td>
                {{ $infoSekolah->kota_kab ?? 'Salatiga' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Pembimbing Dunia Kerja<br>
                <div class="ttd-space"></div>
                <span class="garis-bawah">{{ $raporSiswa->nama_instruktur_snapshot ?? '_______________________' }}</span>
            </td>
        </tr>
    </table>

    {{-- SCRIPT PENOMORAN HALAMAN DI BAWAH --}}
    <div class="footer-fixed"></div>
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("helvetica", "italic");
            $size = 9;
            $color = array(0, 0, 0);
            $width = $pdf->get_width();
            $height = $pdf->get_height();
            $marginSide = 50; 
            
            $y = $height - 33; 

            // Teks Kiri: Nama / NISN
            $leftText = html_entity_decode(
                "{{ strtoupper($raporSiswa->nama_siswa_snapshot) }} / {{ $raporSiswa->nisn_snapshot }}",
                ENT_QUOTES,
                'UTF-8'
            );
            $pdf->page_text($marginSide, $y, $leftText, $font, $size, $color);

            // Teks Kanan: Hal
            $rightText = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $x = $width - $marginSide - 115; 
            $pdf->page_text($x, $y, $rightText, $font, $size, $color);
        }
    </script>
</body>
</html>