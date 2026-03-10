{{-- File: resources/views/rapor/pdf_cover_template.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Cetak Cover Rapor</title>
    <style>
        @page { 
            margin: 30px 50px 30px 100px; 
        }
        body { font-family: 'Arial', sans-serif; color: #000; line-height: 1.3;}
        
        .page-break { page-break-after: always; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        /* KOP HALAMAN 1 */
        .cover-title { font-size: 18pt; font-weight: bold; margin-bottom: 5px; margin-top: 0; }
        .cover-subtitle { font-size: 16pt; font-weight: bold; margin-top: 0; }
        .cover-logo { width: 180px; height: auto; margin: 40px 0; }
        
        .box-nama {
            border: 1px solid #000;
            padding: 10px;
            width: 70%;
            margin: 0 auto 30px auto;
            font-size: 14pt;
            font-weight: bold;
        }

        /* TABEL IDENTITAS HALAMAN 2 (12pt) */
        .table-identitas-sekolah {
            width: 100%;
            border-collapse: collapse;
            font-size: 12pt;
            line-height: 1.5;
        }
        .table-identitas-sekolah td { vertical-align: top; padding: 4px 0; }
        
        /* TABEL IDENTITAS HALAMAN 3 (11pt - Lebih Rapat) */
        .table-identitas-siswa {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt; 
            line-height: 1.5; 
        }
        .table-identitas-siswa td { 
            vertical-align: top; 
            padding: 1px 0; 
        }

        /* Pengaturan Lebar Kolom Umum */
        .col-no { width: 40px; text-align: center; }
        .col-label { width: 220px; }
        .col-titik { width: 20px; text-align: center; }
        .col-value { padding-left: 5px; }

        /* Khusus halaman 2 */
        .col-label2 { width: 160px; }
        .col-titik2 { width: 20px; text-align: center; }
        .col-value2 { padding-left: 5px; }

        /* TABEL MUTASI HALAMAN 4 & 5 */
        .table-mutasi { width: 100%; border-collapse: collapse; font-size: 11pt; margin-top: 20px;}
        .table-mutasi th, .table-mutasi td { border: 1px solid black; padding: 8px; vertical-align: top; }
        .table-mutasi th { text-align: center; font-weight: bold; background-color: #f5f5f5;}
        
        /* KOTAK FOTO */
        .box-photo {
            width: 3cm;
            height: 4cm;
            border: 1px solid #000;
            text-align: center;
            line-height: 4cm;
            float: left;
            margin-left: 150px;
            margin-top: 20px;
            font-size: 10pt;
        }
        
        .box-ttd {
            float: right;
            width: 300px;
            margin-top: 20px;
            font-size: 11pt; 
        }
        .clearfix::after { content: ""; clear: both; display: table; }

        /* CSS KHUSUS HALAMAN 5 (Tidak merusak tabel lain) */
        .cell-inner {
            border-left: 1px solid black;
            border-right: 1px solid black;
            border-top: none; /* Hilangkan garis atas */
            border-bottom: none; /* Hilangkan garis bawah */
            padding: 6px 8px; /* Padding rapi */
            vertical-align: middle;
        }
        .cell-top { border-top: 1px solid black; }
        .cell-bottom { border-bottom: 1px solid black; }
        .garis-isian {
            border-bottom: 1px solid black;
            width: 95%; 
            display: inline-block;
            margin-top: 8px;
        }
    </style>
</head>
<body>

@foreach($siswaList as $siswa)

    {{-- ========================================================================= --}}
    {{-- HALAMAN 1: COVER DEPAN --}}
    {{-- ========================================================================= --}}
    <div class="text-center" style="padding-top: 60px;">
        <img src="{{ public_path('images/logo_provinsi.png') }}" class="cover-logo" alt="Logo" onerror="this.style.display='none'">
        
        <div class="cover-title">SEKOLAH MENENGAH KEJURUAN</div>
        <div class="cover-title">(SMK) NEGERI 1 SALATIGA</div>
        
        <div style="margin-top: 80px; font-size: 14pt; font-weight: bold; margin-bottom: 5px;">Nama Peserta Didik</div>
        <div class="box-nama">{{ strtoupper($siswa->nama_siswa) }}</div>
        
        <div style="font-size: 14pt; font-weight: bold; margin-bottom: 5px;">NISN / NIS</div>
        <div class="box-nama">{{ $siswa->nisn ?? '-' }} / {{ $siswa->nipd ?? '-' }}</div>
        
        <div style="margin-top: 100px;">
            <div class="cover-subtitle" style="font-size: 16pt;">KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</div>
            <div class="cover-subtitle" style="font-size: 16pt;">REPUBLIK INDONESIA</div>
        </div>
    </div>

    <div class="page-break"></div>

    {{-- ========================================================================= --}}
    {{-- HALAMAN 2: IDENTITAS SEKOLAH --}}
    {{-- ========================================================================= --}}
    <div class="text-center font-bold" style="font-size: 14pt; margin-bottom: 30px; margin-top: 40px;">
        SEKOLAH MENENGAH KEJURUAN<br>( SMK )
    </div><br>

    <table class="table-identitas-sekolah" style="width: 90%; margin: 0 auto;">
        <tr>
            <td class="col-label2">Nama Sekolah</td>
            <td class="col-titik2">:</td>
            <td class="col-value2 font-bold">{{ strtoupper($infoSekolah->nama_sekolah ?? '-') }}</td>
        </tr>
        <tr>
            <td class="col-label2">NPSN</td>
            <td class="col-titik2">:</td>
            <td class="col-value2">{{ $infoSekolah->npsn ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label2">NIS/NSS/NDS</td>
            <td class="col-titik2">:</td>
            <td class="col-value2">{{ $infoSekolah->nisn ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label2">Alamat Sekolah</td>
            <td class="col-titik2">:</td>
            <td class="col-value2">{{ $infoSekolah->jalan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label2">Kelurahan / Desa</td>
            <td class="col-titik2">:</td>
            <td class="col-value2">{{ $infoSekolah->kelurahan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label2">Kecamatan</td>
            <td class="col-titik2">:</td>
            <td class="col-value2">{{ $infoSekolah->kecamatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label2">Kota/Kabupaten</td>
            <td class="col-titik2">:</td>
            <td class="col-value2">{{ $infoSekolah->kota_kab ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label2">Provinsi</td>
            <td class="col-titik2">:</td>
            <td class="col-value2">{{ $infoSekolah->provinsi ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label2">Website</td>
            <td class="col-titik2">:</td>
            <td class="col-value2">{{ $infoSekolah->website ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label2">E-mail</td>
            <td class="col-titik2">:</td>
            <td class="col-value2">{{ $infoSekolah->email ?? '-' }}</td>
        </tr>
    </table>

    <div class="page-break"></div>

    {{-- ========================================================================= --}}
    {{-- HALAMAN 3: IDENTITAS PESERTA DIDIK --}}
    {{-- ========================================================================= --}}
    <div class="text-center font-bold" style="font-size: 14pt; margin-bottom: 20px;">
        IDENTITAS PESERTA DIDIK
    </div><br>

    @php
        $det = $siswa->detail; 
    @endphp

    <table class="table-identitas-siswa">
        <tr><td class="col-no">1.</td><td class="col-label">Nama Lengkap Peserta Didik</td><td class="col-titik">:</td><td class="col-value">{{ strtoupper($siswa->nama_siswa) }}</td></tr>
        <tr><td class="col-no">2.</td><td class="col-label">Nomor Induk/NISN</td><td class="col-titik">:</td><td class="col-value">{{ $siswa->nipd ?? '-' }} / {{ $siswa->nisn ?? '-' }}</td></tr>
        <tr><td class="col-no">3.</td><td class="col-label">Tempat, Tanggal Lahir</td><td class="col-titik">:</td><td class="col-value">{{ $det->tempat_lahir ?? '-' }}, {{ $det->tanggal_lahir ? \Carbon\Carbon::parse($det->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td></tr>
        <tr><td class="col-no">4.</td><td class="col-label">Jenis Kelamin</td><td class="col-titik">:</td><td class="col-value">{{ ($siswa->jenis_kelamin == 'L') ? 'Laki-Laki' : (($siswa->jenis_kelamin == 'P') ? 'Perempuan' : '-') }}</td></tr>
        <tr><td class="col-no">5.</td><td class="col-label">Agama</td><td class="col-titik">:</td><td class="col-value">{{ $det->agama ?? '-' }}</td></tr>
        <tr><td class="col-no">6.</td><td class="col-label">Status dalam Keluarga</td><td class="col-titik">:</td><td class="col-value">Anak Kandung</td></tr> 
        <tr><td class="col-no">7.</td><td class="col-label">Anak ke</td><td class="col-titik">:</td><td class="col-value">{{ $det->anak_ke_berapa ?? '-' }}</td></tr>
        <tr><td class="col-no">8.</td><td class="col-label">Alamat Peserta Didik</td><td class="col-titik">:</td><td class="col-value">{{ $det->alamat ?? '-' }} {{ $det->kelurahan ?? '' }} {{ $det->kecamatan ?? '' }}</td></tr>
        <tr><td class="col-no">9.</td><td class="col-label">Nomor Telepon/HP</td><td class="col-titik">:</td><td class="col-value">{{ $det->no_hp ?? '-' }}</td></tr>
        <tr><td class="col-no">10.</td><td class="col-label">Sekolah Asal</td><td class="col-titik">:</td><td class="col-value">{{ $det->sekolah_asal ?? '-' }}</td></tr>
        <tr><td class="col-no">11.</td><td class="col-label">Diterima di sekolah ini</td><td class="col-titik"></td><td class="col-value"></td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">Di kelas</td><td class="col-titik">:</td><td class="col-value">{{ $siswa->kelas->nama_kelas ?? '-' }}</td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">Pada tanggal</td><td class="col-titik">:</td><td class="col-value">................................................</td></tr>
        <tr><td class="col-no">12.</td><td class="col-label">Nama Orang Tua</td><td class="col-titik"></td><td class="col-value"></td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">a. Ayah</td><td class="col-titik">:</td><td class="col-value">{{ $det->nama_ayah ?? '-' }}</td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">b. Ibu</td><td class="col-titik">:</td><td class="col-value">{{ $det->nama_ibu ?? '-' }}</td></tr>
        <tr><td class="col-no">13.</td><td class="col-label">Alamat Orang Tua</td><td class="col-titik">:</td><td class="col-value">{{ $det->alamat ?? '-' }}</td></tr>
        <tr><td class="col-no">14.</td><td class="col-label">Nomor Telepon/HP Ortu</td><td class="col-titik">:</td><td class="col-value">{{ $det->no_hp ?? '-' }}</td></tr>
        <tr><td class="col-no">15.</td><td class="col-label">Pekerjaan Orang Tua</td><td class="col-titik"></td><td class="col-value"></td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">a. Ayah</td><td class="col-titik">:</td><td class="col-value">{{ $det->pekerjaan_ayah ?? '-' }}</td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">b. Ibu</td><td class="col-titik">:</td><td class="col-value">{{ $det->pekerjaan_ibu ?? '-' }}</td></tr>
        <tr><td class="col-no">16.</td><td class="col-label">Nama Wali Siswa</td><td class="col-titik">:</td><td class="col-value">{{ $det->nama_wali ?? '-' }}</td></tr>
        <tr><td class="col-no">17.</td><td class="col-label">Alamat Wali Peserta Didik</td><td class="col-titik">:</td><td class="col-value">-</td></tr>
        <tr><td class="col-no">18.</td><td class="col-label">Nomor Telepon Wali</td><td class="col-titik">:</td><td class="col-value">-</td></tr>
        <tr><td class="col-no">19.</td><td class="col-label">Pekerjaan Wali Peserta Didik</td><td class="col-titik">:</td><td class="col-value">{{ $det->pekerjaan_wali ?? '-' }}</td></tr>
    </table>

    <div class="clearfix">
        <div class="box-photo">
            Pas Foto
        </div>
        <div class="box-ttd">
            {{ $infoSekolah->kota_kab ?? 'Salatiga' }}, ..................................<br>
            Kepala Sekolah<br><br><br><br><br>
            <span class="font-bold" style="text-decoration: underline;">{{ $infoSekolah->nama_kepsek ?? '__________________________' }}</span><br>
            NIP. {{ $infoSekolah->nip_kepsek ?? '-' }}
        </div>
    </div>

    <div class="page-break"></div>

    {{-- TAMBAHKAN CSS MANDIRI KHUSUS HALAMAN 4 & 5 DI SINI --}}
    <style>
        /* CSS KHUSUS HALAMAN 4 & 5 - Dijamin 100% tidak merusak Halaman 1-3 */
        .tabel-mutasi-khusus { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 11pt; 
            margin-bottom: 10px;
        }
        /* Pengaturan khusus untuk ISI TABEL (td) */
        .tabel-mutasi-khusus td { 
            border: 1px solid black; 
            padding: 10px 10px; /* Silakan sesuaikan padding isi tabel di sini */
            vertical-align: middle; 
            line-height: 1.2; 
        }

        /* Pengaturan khusus untuk HEADER TABEL (th) */
        .tabel-mutasi-khusus th { 
            border: 1px solid black; 
            padding: 10px 2px; /* <-- Ubah nilai 2px ini untuk mengurangi padding kiri-kanan header */
            vertical-align: middle; 
            line-height: 1.2; 
            text-align: center; 
            font-weight: bold; 
            background-color: #f5f5f5;
        }
        
        .tabel-masuk-statis {
            width: 100%; 
            border-collapse: collapse; 
            font-size: 11pt;
        }
        .tabel-masuk-statis th {
            border: 1px solid black; 
            background-color: #dbe5f1; 
            padding: 6px 8px; 
            text-align: center;
        }
        .sel-dalam {
            border-left: 1px solid black;
            border-right: 1px solid black;
            border-top: none;
            border-bottom: none;
            padding: 6px 8px; /* SANGAT RAPAT: padding dikecilkan */
            vertical-align: middle;
            line-height: 1.1; /* SANGAT RAPAT: spasi teks dikecilkan */
        }
        .sel-atas { border-top: 1px solid black; padding-top: 15px; }
        .sel-bawah { border-bottom: 1px solid black; padding-bottom: 4px; }
        .garis-isi-statis {
            border-bottom: 1px solid black;
            width: 95%; 
            display: inline-block;
            margin-top: 4px;
        }
        .sel-ttd {
            border: 1px solid black; 
            padding: 6px 10px; 
            vertical-align: top;
            line-height: 1.2;
        }
    </style>

    {{-- ========================================================================= --}}
    {{-- HALAMAN 4: KETERANGAN PINDAH SEKOLAH (KELUAR) --}}
    {{-- ========================================================================= --}}
    <div class="text-center font-bold" style="font-size: 14pt; margin-bottom: 20px;">
        KETERANGAN PINDAH SEKOLAH
    </div>
    <table style="width: 100%; margin-bottom: 10px; font-size: 12pt;">
        <tr>
            <td style="width: 160px;">Nama Peserta Didik</td>
            <td style="width: 15px;">:</td>
            <td class="font-bold">{{ strtoupper($siswa->nama_siswa) }}</td>
        </tr>
    </table>

    <table class="tabel-mutasi-khusus">
        <tr>
            <th colspan="4">KELUAR</th>
        </tr>
        <tr>
            <th style="width: 10%;">Tanggal</th>
            <th style="width: 20%;">Kelas yang<br>ditinggalkan</th>
            <th style="width: 40%;">Sebab-sebab Keluar atau<br>Atas Permintaan (Tertulis)</th>
            <th style="width: 30%;">Tanda Tangan Kepala Sekolah,<br>Stempel Sekolah, dan<br>Tanda Tangan Orang Tua/Wali</th>
        </tr>
        @for($i=1; $i<=3; $i++)
        <tr>
            <td></td> <td></td>
            <td></td>
            <td>
                {{ $infoSekolah->kota_kab ?? 'Salatiga' }}, ..............................<br>
                Kepala Sekolah,<br>
                <div style="height: 25px;"></div> ..................................................<br>
                NIP.<br><br>
                <div style="height: 5px;"></div>
                Orang Tua/Wali,<br>
                <div style="height: 25px;"></div> ..................................................
            </td>
        </tr>
        @endfor
    </table>

    <div class="page-break"></div>

    {{-- ========================================================================= --}}
    {{-- HALAMAN 5: KETERANGAN PINDAH SEKOLAH (MASUK) --}}
    {{-- ========================================================================= --}}
    <div class="text-center font-bold" style="font-size: 14pt; margin-bottom: 20px;">
        KETERANGAN PINDAH SEKOLAH
    </div>
    <table style="width: 100%; margin-bottom: 10px; font-size: 12pt;">
        <tr>
            <td style="width: 160px;">Nama Peserta Didik</td>
            <td style="width: 15px;">:</td>
            <td class="font-bold">{{ strtoupper($siswa->nama_siswa) }}</td>
        </tr>
    </table>

    {{-- REVISI HALAMAN 5: Menggunakan struktur Rowspan & Class CSS Mandiri --}}
    <table class="tabel-masuk-statis">
        <tr>
            <th style="width: 5%;">NO</th>
            <th colspan="3">MASUK</th>
        </tr>
        
        @for($i=1; $i<=3; $i++)
            {{-- Baris 1: Nama Siswa + Tanda Tangan (Rowspan 7) --}}
            <tr>
                <td class="sel-dalam sel-atas text-center" style="width: 5%;">1.</td>
                <td class="sel-dalam sel-atas" style="width: 30%;">Nama Siswa</td>
                <td class="sel-dalam sel-atas" style="width: 35%;"><span class="garis-isi-statis"></span></td>
                <td rowspan="7" class="sel-ttd" style="width: 30%; vertical-align: middle;">
                    {{ $infoSekolah->kota_kab ?? 'Salatiga' }} ,...................<br>
                    Kepala Sekolah,<br><br><br>
                    <div style="height: 35px;"></div> <div style="border-bottom: 1px dotted #000; width: 90%; margin-bottom: 3px;"></div>
                    NIP.
                </td>
            </tr>
            
            {{-- Baris 2: Nomor Induk --}}
            <tr>
                <td class="sel-dalam text-center">2.</td>
                <td class="sel-dalam">Nomor Induk</td>
                <td class="sel-dalam"><span class="garis-isi-statis"></span></td>
            </tr>
            
            {{-- Baris 3: Nama Sekolah --}}
            <tr>
                <td class="sel-dalam text-center">3.</td>
                <td class="sel-dalam">Nama Sekolah</td>
                <td class="sel-dalam"><span class="garis-isi-statis"></span></td>
            </tr>
            
            {{-- Baris 4: Masuk di Sekolah ini --}}
            <tr>
                <td class="sel-dalam text-center">4.</td>
                <td class="sel-dalam">Masuk di Sekolah ini:</td>
                <td class="sel-dalam"></td> 
            </tr>
            
            {{-- Baris 5: Tanggal (Menjorok) --}}
            <tr>
                <td class="sel-dalam"></td>
                <td class="sel-dalam" style="padding-left: 20px;">a. Tanggal</td>
                <td class="sel-dalam"><span class="garis-isi-statis"></span></td>
            </tr>
            
            {{-- Baris 6: Di Kelas (Menjorok) --}}
            <tr>
                <td class="sel-dalam"></td>
                <td class="sel-dalam" style="padding-left: 20px;">b. Di Kelas</td>
                <td class="sel-dalam"><span class="garis-isi-statis"></span></td>
            </tr>
            
            {{-- Baris 7: Tahun Pelajaran --}}
            <tr>
                <td class="sel-dalam sel-bawah text-center">5.</td>
                <td class="sel-dalam sel-bawah">Tahun Pelajaran</td>
                <td class="sel-dalam sel-bawah"><span class="garis-isi-statis"></span></td>
            </tr>
        @endfor
    </table>

    {{-- Beri pemisah halaman untuk siswa berikutnya JIKA bukan siswa terakhir --}}
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif

@endforeach

</body>
</html>