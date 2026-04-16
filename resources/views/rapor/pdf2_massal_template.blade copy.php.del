<!DOCTYPE html>
<html>
<head>
    <title>Cetak Massal Rapor</title>
    <style>
        /* CSS Identik dengan PDF1 */
        @page { margin: 50px 50px 50px 50px; }
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 11pt; 
            line-height: 1.3; 
            margin: 0;
            padding: 0;
        }


        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: never; }

        /* Tabel Identitas (Pengganti Header Fixed agar stabil di Massal) */
        .table-identitas { width: 100%; border-collapse: collapse; margin-bottom: 10px; border-bottom: 2px solid #000; }
        .table-identitas td { vertical-align: top; padding: 2px 0; font-size: 10pt; }
        .val-bold { font-weight: bold; }

        /* Tabel Utama Nilai */
        .main-table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        .main-table th, .main-table td { border: 1px solid black; padding: 8px; font-size: 9pt; }
        thead { display: table-header-group; } /* Munculkan header kolom di hal 2 */

        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold; }
        .bg-light { background-color: #f2f2f2; }
        .kategori-row { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }

        .judul-rapor { text-align: center; font-size: 13pt; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; }

        /* Container Bawah (Absensi & Catatan) */
        .container-bawah { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .tabel-info-rapor { width: 100%; border-collapse: collapse; }
        .tabel-info-rapor th, .tabel-info-rapor td { border: 1px solid black; padding: 5px 8px; font-size: 10pt; }
        .tabel-info-rapor th { background-color: #f2f2f2; font-weight: bold; text-align: center; }

        /* Tanda Tangan */
        .table-ttd { width: 100%; margin-top: 30px; border-collapse: collapse; }
        .table-ttd td { text-align: center; vertical-align: top; font-size: 9pt; }
        .space-ttd { height: 70px; }
        .nama-kepsek { font-weight: bold; text-decoration: underline; display: block; margin-top: 70px; }

        /* Garis Footer Fixed HTML */
        .footer-line { position: fixed; bottom: -30px; left: 0; right: 0; border-top: 1px solid #000; }
    </style>
</head>
<body>
    {{-- Garis Footer dirender sekali saja di luar loop --}}
    <div class="footer-line"></div>

    @foreach($allData as $data)
    <div class="page-break">
        <table class="table-identitas">
            <tr>
                <td width="110">Nama</td><td width="15" class="text-center">:</td>
                <td width="280" class="val-bold">{{ strtoupper($data['siswa']->nama_siswa) }}</td>
                <td width="20"></td>
                <td width="110">Kelas</td><td width="15" class="text-center">:</td>
                <td>{{ $data['siswa']->kelas->nama_kelas }}</td>
            </tr>
            <tr>
                <td>NIS/NISN</td><td class="text-center">:</td>
                <td>{{ $data['siswa']->nipd }} / {{ $data['siswa']->nisn }}</td>
                <td></td>
                <td>Fase</td><td class="text-center">:</td>
                <td>{{ $data['fase'] }}</td>
            </tr>
            <tr>
                <td>Nama Sekolah</td><td class="text-center">:</td>
                <td>{{ $data['sekolah'] }}</td>
                <td></td>
                <td>Semester</td><td class="text-center">:</td>
                <td>{{ $data['semesterInt'] }} ({{ $data['semester'] }})</td>
            </tr>
            <tr>
                <td>Alamat</td><td class="text-center">:</td>
                <td style="font-size: 9pt;">{{ $data['infoSekolah'] }}</td>
                <td></td>
                <td>Tahun Pelajaran</td><td class="text-center">:</td>
                <td>{{ $data['tahun_ajaran'] }}</td>
            </tr>
        </table>

        <div class="judul-rapor">LAPORAN HASIL BELAJAR</div>

        <table class="main-table">
            <thead>
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th class="text-center" width="25%">Mata Pelajaran</th>
                    <th class="text-center" width="11%">Nilai Akhir</th>
                    <th class="text-center">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['mapelGroup'] as $kategori => $mapels)
                    <tr class="kategori-row">
                        <td colspan="4">{{ $kategori }}</td>
                    </tr>
                    @foreach($mapels as $m)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $m->nama_mapel }}</td>
                        <td class="text-center">{{ round($m->nilai_akhir) }}</td>
                        <td style="font-size: 9pt;">{{ $m->capaian }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <table class="main-table">
            <thead><tr class="bg-light"><th class="text-center">KOKURIKULER</th></tr></thead>
            <tbody>
                <tr><td style="text-align: justify; font-size: 9pt;">{{ $data['catatan']->kokurikuler ?? '-' }}</td></tr>
            </tbody>
        </table>

        <table class="main-table">
            <thead>
                <tr class="bg-light">
                    <th class="text-center" width="5%">No</th>
                    <th class="text-center" width="35%">Kegiatan Ekstrakurikuler</th>
                    <th class="text-center" width="15%">Predikat</th>
                    <th class="text-center">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < 3; $i++)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    @if(isset($data['dataEkskul'][$i]))
                        <td>{{ $data['dataEkskul'][$i]->nama }}</td>
                        <td class="text-center">{{ $data['dataEkskul'][$i]->predikat }}</td>
                        <td style="font-size: 9pt;">{{ $data['dataEkskul'][$i]->keterangan }}</td>
                    @else
                        <td>-</td><td class="text-center">-</td><td>-</td>
                    @endif
                </tr>
                @endfor
            </tbody>
        </table>

        <table class="container-bawah" style="border: none;">
            <tr>
                <td width="45%" style="vertical-align: top;">
                    <table class="tabel-info-rapor">
                        <thead><tr><th colspan="2">Ketidakhadiran</th></tr></thead>
                        <tbody>
                            <tr><td width="70%">Sakit</td><td class="text-center">{{ $data['catatan']->sakit ?? 0 }} hari</td></tr>
                            <tr><td>Izin</td><td class="text-center">{{ $data['catatan']->ijin ?? 0 }} hari</td></tr>
                            <tr><td>Tanpa Keterangan</td><td class="text-center">{{ $data['catatan']->alpha ?? 0 }} hari</td></tr>
                        </tbody>
                    </table>
                </td>
                <td width="5%"></td>
                <td width="50%" style="vertical-align: top;">
                    <table class="tabel-info-rapor">
                        <thead><tr><th>Catatan Wali Kelas</th></tr></thead>
                        <tbody>
                            <tr><td style="height: 60px; font-style: italic; font-size: 9pt; vertical-align: top;">{{ $data['catatan']->catatan_wali_kelas ?? '-' }}</td></tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        <table class="table-ttd">
            <tr>
                <td width="35%">Mengetahui,<br>Orang Tua/Wali,<div class="space-ttd"></div>..........................</td>
                <td></td>
                <td width="35%">Salatiga, {{ date('d F Y') }}<br>Wali Kelas,<div class="space-ttd"></div><span class="font-bold" style="text-decoration: underline;">{{ $data['nama_wali'] }}</span><br>NIP. {{ $data['nip_wali'] }}</td>
            </tr>
        </table>
        <div class="text-center" style="margin-top: 20px;">
            Mengetahui,<br>Kepala Sekolah
            <span class="nama-kepsek">{{ $data['info_sekolah']->nama_kepsek ?? '-' }}</span>
            <span>NIP. {{ $data['info_sekolah']->nip_kepsek ?? '-' }}</span>
        </div>
    </div>
    @endforeach

    {{-- 7. SCRIPT PHP FOOTER (DI LUAR LOOP: Tidak akan bertumpuk) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("helvetica", "italic");
            $size = 9;
            $color = array(0, 0, 0);
            $y = $pdf->get_height() - 33; 
            $width = $pdf->get_width();

            // Teks Halaman Massal
            $pdf->page_text(50, $y, "E-Rapor SMK | Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, $size, $color);
        }
    </script>
</body>
</html>