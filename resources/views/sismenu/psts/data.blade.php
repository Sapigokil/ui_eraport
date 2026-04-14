{{-- resources/views/sismenu/psts/data.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Laporan PSTS {{ $siswa->nama_siswa }}</title>
    <style>
        /* 1. ATUR MARGIN KERTAS */
        @page {
            margin: 180px 50px 50px 50px;
        }      

        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11pt; 
            line-height: 1.3; 
            padding-bottom: 30px;
        }
        
        /* 2. HEADER TETAP (FIXED) DI SETIAP HALAMAN */
        .header-fixed {
            position: fixed;
            top: -150px; 
            left: 0px;
            right: 0px;
            /* Height disesuaikan tanpa border-bottom */
            height: 140px; 
        }

        /* JUDUL LAPORAN */
        .judul-laporan {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 1.5px; /* Sedikit dilebarkan agar mirip rapor */
            text-transform: uppercase;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        /* TABEL UTAMA */
        .main-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
        }
        .main-table th, .main-table td { 
            border: 1px solid black; 
            padding: 8px; 
            font-size: 10pt;
        }
        .main-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .kategori-row { 
            background-color: #e9ecef; 
            font-weight: bold; 
            text-transform: uppercase; 
        }
        .text-center { text-align: center !important; }
        .text-justify { text-align: justify; text-justify: inter-word; }
        .font-bold { font-weight: bold; }
        
        /* GARIS FOOTER (Nomor Halaman) */
        .footer-line {
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

    {{-- HEADER DITARUH PALING ATAS DENGAN CLASS FIXED --}}
    <div class="header-fixed">
        @include('sismenu.psts.header')
    </div>

    {{-- JUDUL LAPORAN --}}
    @php
        $judul_jenis = is_numeric($jenis) ? 'SUMATIF ' . $jenis : strtoupper($jenis);
    @endphp
    <div class="judul-laporan">
        LAPORAN HASIL {{ $judul_jenis }}
    </div>

    {{-- TABEL NILAI --}}
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Mata Pelajaran</th>
                <th style="width: 10%;">Nilai Akhir</th>
                <th style="width: 55%;">Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            @php $nomor_global = 1; @endphp
            
            @forelse($data_psts as $kelompok => $mapels)
                <tr class="kategori-row">
                    <td colspan="4" style="padding-left: 10px;">{{ $kelompok }}</td>
                </tr>
                
                @foreach($mapels as $nama_mapel => $data_nilai)
                    <tr>
                        <td class="text-center">{{ $nomor_global++ }}</td>
                        <td>{{ $nama_mapel }}</td>
                        <td class="text-center">
                            @if(isset($data_nilai['nilai']))
                                <span class="{{ $data_nilai['nilai'] < 70 ? 'font-bold' : '' }}">
                                    {{ $data_nilai['nilai'] }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-justify" style="font-size: 9pt;">
                            {{ $data_nilai['capaian'] ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px; font-style: italic;">
                        Data nilai belum tersedia untuk jenis penilaian ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-line"></div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("helvetica", "italic");
            $size = 9;
            $color = array(0, 0, 0);
            $width = $pdf->get_width();
            $height = $pdf->get_height();
            $marginSide = 50; 
            $y = $height - 33; 

            $leftText = html_entity_decode(
                "{{ $siswa->kelas->nama_kelas }} / {{ strtoupper($siswa->nama_siswa) }} / {{ $siswa->nipd }}",
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