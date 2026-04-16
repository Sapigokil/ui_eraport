<!DOCTYPE html>
<html>
<head>
    <title>Laporan Hasil Belajar</title>
    @php
    // Definisikan label untuk setiap ID kategori
        $labelKategori = [
            1 => 'MATA PELAJARAN UMUM',
            2 => 'MATA PELAJARAN KEJURUAN',
            3 => 'MATA PELAJARAN PILIHAN', // Tambahkan jika ada kategori lain
            4 => 'MUATAN LOKAL' // Tambahkan jika ada kategori lain
        ];
    @endphp
    <style>
        @page {
            margin: 10px 50px 50px 50px; /* Atas dibuat tipis (10px) agar mepet */
        }      
        .header-fixed {
            position: fixed;
            top: 20px; /* Menempel ke margin atas yang sudah kita set di @page */
            left: 0px;
            right: 0px;
            height: 160px; /* Sesuaikan dengan tinggi total header Anda */
            border-bottom: 2px solid #000;
            background-color: white; /* Mencegah tembus pandang */
            z-index: 1000;
        }
        body { font-family: 'Arial', sans-serif; font-size: 11pt; line-height: 1.3; }
        .header-table td { vertical-align: top; padding-bottom: 5px; }
        .main-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .main-table th, .main-table td { border: 1px solid black; padding: 8px; text-align: left; }
        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold; }
        .kategori-row { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        /* CSS yang lebih stabil untuk DomPDF */
        .header-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-container td {
            vertical-align: top;
            font-size: 11pt; /* Font sudah diperbesar */
            padding: 2px 0;
        }

        /* Definisi Lebar Kolom */
        .col-title-left { width: 110px; }
        .col-dots { width: 15px; text-align: center; }
        .col-value-left { width: 280px; font-weight: bold; } /* Menampung Nama/Sekolah */
        .col-spacer { width: 5px; } /* Ruang kosong di tengah */
        
        .col-title-right { width: 110px; }
        .col-value-right { width: 120px; }

        .font-alamat {
            font-size: 10pt;
            font-weight: normal;
        }
        .uppercase { text-transform: uppercase; }

        /* Garis melintang tepat di bawah tabel header */
        .header-container {
            width: 100%;
            border-collapse: collapse;
            /* Memberikan garis bawah setebal 2px */
            border-bottom: 2px solid #000; 
            /* Memberi jarak antara teks terakhir header dengan garis */
            padding-bottom: 5px; 
            margin-bottom: 5px;
        }

        /* Jika Anda ingin menggunakan tag HR yang lebih fleksibel */
        hr.garis-pembatas {
            border: none;
            border-top: 2px solid black;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .judul-rapor {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            /* Margin top dibuat sangat kecil atau bahkan 0 untuk menaikkan teks */
            margin-top: 5px; 
            margin-bottom: 15px;
        }

        .container-bawah {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: none !important;
        }
        .container-bawah td {
            border: none !important; /* Menghilangkan border tabel pembungkus */
            vertical-align: top;
            padding: 0;
        }
        .tabel-info-rapor {
            width: 100%;
            border-collapse: collapse;
        }
        .tabel-info-rapor th, .tabel-info-rapor td {
            border: 1px solid black !important; /* Memberikan border pada tabel kontennya */
            padding: 5px 8px;
            font-size: 10pt;
        }

        .tabel-info-rapor th {
            background-color: #f2f2f2;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            padding: 5px;
            border: 1px solid black;
        }
        .bg-light {
            background-color: #f2f2f2;
        }
        .font-bold {
            font-weight: bold;
        }

        @page { margin: 0mm 50px 50px 50px; } /* Margin kertas */

        .header-fixed {
            position: fixed;
            top: 15px;
            left: 0;
            right: 0;
            height: 110px;
            border-bottom: 2px solid #000000;
            padding-bottom: 0px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
            line-height: 1.2;
        }

        .header-table td { vertical-align: top; padding: 2px 0; }
        
        /* Lebar label kolom kiri */
        .label-kiri { width: 110px; }
        .titik-dua { width: 15px; text-align: center; }
        .value-kiri { width: 280px; font-weight: bold; }
        
        /* Spacer fleksibel di tengah */
        .spacer { width: auto; }

        /* Lebar label kolom kanan */
        .label-kanan { width: 120px; white-space: nowrap; }
        .value-kanan { width: 150px; }

        body { padding-top: 150px; } /* Memberi ruang agar isi tidak tertutup header */

        .main-table td, .main-table th {
            border: 1px solid black;
            padding-top: 10px;    /* Jarak atas */
            padding-bottom: 10px; /* Jarak bawah */
            padding-left: 10px;  /* Jarak kiri agar teks tidak nempel garis */
            padding-right: 10px; /* Jarak kanan */
            margin-bottom: 150px;
            font-size: 11pt;
        }

        .table-ttd {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .table-ttd td {
            border: none !important; /* Pastikan tidak ada garis tabel */
            text-align: center;
            vertical-align: top;
            font-size: 11pt;
        }
        .space-ttd {
            height: 70px; /* Ruang untuk tanda tangan basah */
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
    </style>
</head>
<body>
    <div class="header-fixed">
    <table class="header-table">
        <tr>
            <td class="col-title-left">Nama</td>
            <td class="col-dots">:</td>
            <td class="col-value-left">{{ strtoupper($siswa->nama_siswa) }}</td>
            
            <td class="col-spacer"></td>
            
            <td style="width: 220px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 120px;">Kelas</td>
                        <td style="width: 15px;">:</td>
                        <td>{{ $siswa->kelas->nama_kelas }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="col-title-left">NIS/NISN</td>
            <td class="col-dots">:</td>
            <td class="col-value-left" style="font-weight: normal;">{{ $siswa->nipd }} / {{ $siswa->nisn }}</td>
            
            <td class="col-spacer"></td>
            
            <td>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 120px;">Fase</td>
                        <td style="width: 15px;">:</td>
                        <td>{{ $siswa->kelas->fase ?? 'F' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="col-title-left">Nama Sekolah</td>
            <td class="col-dots">:</td>
            <td class="col-value-left" style="font-weight: normal;">{{ $infosekolah->nama_sekolah ?? 'SMKN 1 SALATIGA' }}</td>
            
            <td class="col-spacer"></td>
            
            <td>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 120px;">Semester</td>
                        <td style="width: 15px;">:</td>
                        <td>{{ $semesterInt }} ({{ $semester }})</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="col-title-left">Alamat</td>
            <td class="col-dots">:</td>
            <td class="col-value-left font-alamat">{{ $infosekolah->alamat ?? '-' }}</td>
            
            <td class="col-spacer"></td>
            
            <td>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 120px;">Tahun Pelajaran</td>
                        <td style="width: 15px;">:</td>
                        <td>{{ $tahun_ajaran }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    </div>
    <body style="margin-top: -160px;">
    <div class="judul-rapor">LAPORAN HASIL BELAJAR</div>
    {{-- <h4 class="text-center" style="margin-top: 5px;">LAPORAN HASIL BELAJAR</h4> --}}
    </body>
    <table class="main-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th class="text-center" style="width: 30%;">Mata Pelajaran</th>
                <th class="text-center" style="width: 15%;">Nilai Akhir</th>
                <th class="text-center">Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mapelGroup as $kategori => $mapels)
                <tr class="kategori-row">
                    <td colspan="4">{{ $labelKategori[$kategori] ?? 'KATEGORI ' . $kategori }}</td>
                </tr>
                @foreach($mapels as $index => $m)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $m->nama_mapel }}</td>
                    <td class="text-center">{{ $m->nilai_akhir }}</td>
                    <td style="font-size: 9pt;">{{ $m->capaian }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
<table class="main-table">
    <thead>
        <tr class="bg-light">
            {{-- Baris 1: Judul --}}
            <th class="text-center">Kokurikuler</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            {{-- Baris 2: Konten Teks Panjang --}}
            <td style="padding: 12px; text-align: justify; font-size: 10pt; line-height: 1.5; min-height: 60px;">
                {{ $catatan->kokurikuler ?? '-' }}
            </td>
        </tr>
    </tbody>
</table>
    
<table class="main-table">
    <thead>
        <tr class="bg-light">
            <th class="text-center" style="width: 5%;">No</th>
            <th class="text-center" style="width: 35%;">Kegiatan Ekstrakurikuler</th>
            <th class="text-center" style="width: 15%;">Predikat</th>
            <th class="text-center">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        {{-- Kita paksa loop sebanyak 3 kali --}}
        @for($i = 0; $i < 3; $i++)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                
                {{-- Cek apakah ada data ekskul untuk index ini --}}
                @if(isset($dataEkskul[$i]))
                    <td>{{ $dataEkskul[$i]->nama }}</td>
                    <td class="text-center">{{ $dataEkskul[$i]->predikat }}</td>
                    <td style="font-size: 9pt;">{{ $dataEkskul[$i]->keterangan }}</td>
                @else
                    {{-- Jika tidak ada data, tampilkan baris kosong/strip --}}
                    <td>-</td>
                    <td class="text-center">-</td>
                    <td>-</td>
                @endif
            </tr>
        @endfor
    </tbody>
</table>

<table class="container-bawah">
    <tr>
        <td style="width: 45%;">
            <table class="tabel-info-rapor">
                <thead>
                    <tr>
                        <th colspan="2">Ketidakhadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width: 70%;">Sakit</td>
                        <td class="text-center">{{ $catatan->sakit ?? 0 }} hari</td>
                    </tr>
                    <tr>
                        <td>Izin</td>
                        <td class="text-center">{{ $catatan->ijin ?? 0 }} hari</td>
                    </tr>
                    <tr>
                        <td>Tanpa Keterangan</td>
                        <td class="text-center">{{ $catatan->alpha ?? 0 }} hari</td>
                    </tr>
                </tbody>
            </table>
        </td>

        <td style="width: 4%;"></td>

        <td style="width: 51%;">
            <table class="tabel-info-rapor">
                <thead>
                    <tr>
                        <th>Catatan Wali Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="height: 68px; vertical-align: top; text-align: justify; font-style: italic; font-size: 9pt;">
                            {{ $catatan->catatan_wali_kelas ?? '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

    <table class="table-ttd">
        <tr>
            <td style="width: 35%;">
                Mengetahui,<br>
                Orang Tua/Wali,
                <div class="space-ttd"></div>
                ..........................................
            </td>

            <td style="width: 30%;">
                </td>

            <td style="width: 35%;">
                Salatiga, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                Wali Kelas,
                <div class="space-ttd"></div>
                <span class="font-bold" style="text-decoration: underline;">
                    {{ $nama_wali ?? 'NAMA WALI KELAS' }}
                </span><br>
                NIP. {{ $nip_wali ?? '-' }}
            </td>
        </tr>
    </table>

    <div class="ttd-kepsek">
        <p style="margin-bottom: 5px;">Mengetahui,</p>
        <p style="margin-top: 0;">Kepala Sekolah</p>
        
        {{-- Nama Kepala Sekolah diambil dari variabel info --}}
        <span class="nama-kepsek">
            {{ $info_sekolah->nama_kepsek ?? 'NAMA KEPALA SEKOLAH' }}
        </span>
        
        {{-- NIP Kepala Sekolah --}}
        <span>NIP. {{ $info_sekolah->nip_kepsek ?? '-' }}</span>
    </div>
</body>
</html>