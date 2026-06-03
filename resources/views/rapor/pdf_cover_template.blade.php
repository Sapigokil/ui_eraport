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
        
        <div class="cover-title">LAPORAN HASIL BELAJAR PESERTA DIDIK</div>
        <div class="cover-title">SEKOLAH MENENGAH KEJURUAN</div>
        <div class="cover-title">(SMK) NEGERI 1 SALATIGA</div>
        
        <div style="margin-top: 80px; font-size: 14pt; font-weight: bold; margin-bottom: 5px;">Nama Peserta Didik</div>
        <div class="box-nama">{{ strtoupper($siswa->nama_siswa) }}</div>
        
        <div style="font-size: 14pt; font-weight: bold; margin-bottom: 5px;">NISN / NIS</div>
        <div class="box-nama">{{ $siswa->nisn ?? '-' }} / {{ $siswa->nipd ?? '-' }}</div>
        
        <div style="margin-top: 100px;">
            <div class="cover-subtitle" style="font-size: 12pt;">{{ ucwords(strtolower($infoSekolah->jalan ?? '-')) }}, {{ ucwords(strtolower($infoSekolah->kota_kab ?? '-')) }}, {{ ucwords(strtolower($infoSekolah->provinsi ?? '-')) }}, Kode Pos {{ $infoSekolah->kode_pos ?? '-' }}</div>
            <div class="cover-subtitle" style="font-size: 12pt;">Telepon {{ $infoSekolah->telp_fax ?? '-' }}, Faksimile {{ $infoSekolah->telp_fax ?? '-' }}</div>
            <div class="cover-subtitle" style="font-size: 12pt;">Laman {{ strtolower($infoSekolah->website ?? '-') }}, Pos-el {{ strtolower($infoSekolah->email ?? '-') }}</div>
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
            {{-- <td class="col-value2 font-bold">{{ strtoupper($infoSekolah->nama_sekolah ?? '-') }}</td> --}}
            <td class="col-value2 font-bold">SMK NEGERI 1 SALATIGA</td>
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
            <td class="col-value2">{{ $infoSekolah->jalan ?? '-' }} {{ $infoSekolah->kota_kab ?? '-' }}</td>
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
            <td class="col-value2">https://{{ $infoSekolah->website ?? '-' }}</td>
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
        <tr><td class="col-no">3.</td><td class="col-label">Tempat, Tanggal Lahir</td><td class="col-titik">:</td><td class="col-value">{{ ucwords(strtolower($det->tempat_lahir ?? '-')) }}, {{ $det->tanggal_lahir ? \Carbon\Carbon::parse($det->tanggal_lahir)->locale('id')->translatedFormat('d F Y') : '-' }}</td></tr>
        <tr><td class="col-no">4.</td><td class="col-label">Jenis Kelamin</td><td class="col-titik">:</td><td class="col-value">{{ ($siswa->jenis_kelamin == 'L') ? 'Laki-Laki' : (($siswa->jenis_kelamin == 'P') ? 'Perempuan' : '-') }}</td></tr>
        <tr><td class="col-no">5.</td><td class="col-label">Agama</td><td class="col-titik">:</td><td class="col-value">{{ ucwords(strtolower($det->agama ?? '-')) }}</td></tr>
        <tr><td class="col-no">6.</td><td class="col-label">Status dalam Keluarga</td><td class="col-titik">:</td><td class="col-value">Anak Kandung</td></tr> 
        <tr><td class="col-no">7.</td><td class="col-label">Anak ke</td><td class="col-titik">:</td><td class="col-value">{{ $det->anak_ke_berapa ?? '-' }}</td></tr>
        <tr><td class="col-no">8.</td><td class="col-label">Alamat Peserta Didik</td><td class="col-titik">:</td><td class="col-value">{{ ucwords(strtolower($det->alamat ?? '-')) }} {{ ucwords(strtolower($det->kelurahan ?? '' )) }} {{ ucwords(strtolower($det->kecamatan ?? '' )) }}</td></tr>
        <tr><td class="col-no">9.</td><td class="col-label">Nomor Telepon/HP</td><td class="col-titik">:</td><td class="col-value">{{ $det->no_hp ?? '-' }}</td></tr>
        <tr><td class="col-no">10.</td><td class="col-label">Sekolah Asal</td><td class="col-titik">:</td><td class="col-value">{{ $det->sekolah_asal ?? '-' }}</td></tr>
        <tr><td class="col-no">11.</td><td class="col-label">Diterima di sekolah ini</td><td class="col-titik"></td><td class="col-value"></td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">Di kelas</td><td class="col-titik">:</td><td class="col-value">{{ $det->kelas_awal ?? '-' }}</td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">Pada tanggal</td><td class="col-titik">:</td><td class="col-value">{{ $det->tgl_masuk ? \Carbon\Carbon::parse($det->tgl_masuk)->locale('id')->translatedFormat('d F Y') : '-' }}</td></tr>
        <tr><td class="col-no">12.</td><td class="col-label">Nama Orang Tua</td><td class="col-titik"></td><td class="col-value"></td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">a. Ayah</td><td class="col-titik">:</td><td class="col-value">{{ ucwords(strtolower($det->nama_ayah ?? '-')) }}</td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">b. Ibu</td><td class="col-titik">:</td><td class="col-value">{{ ucwords(strtolower($det->nama_ibu ?? '-')) }}</td></tr>
        <tr><td class="col-no">13.</td><td class="col-label">Alamat Orang Tua</td><td class="col-titik">:</td><td class="col-value">{{ $det->alamat ?? '-' }}</td></tr>
        <tr><td class="col-no">14.</td><td class="col-label">Nomor Telepon/HP Ortu</td><td class="col-titik">:</td><td class="col-value">{{ $det->telp_wali ?? '-' }}</td></tr>
        <tr><td class="col-no">15.</td><td class="col-label">Pekerjaan Orang Tua</td><td class="col-titik"></td><td class="col-value"></td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">a. Ayah</td><td class="col-titik">:</td><td class="col-value">{{ ucwords(strtolower($det->pekerjaan_ayah ?? '-')) }}</td></tr>
        <tr><td class="col-no"></td><td class="col-label" style="padding-left:15px;">b. Ibu</td><td class="col-titik">:</td><td class="col-value">{{ ucwords(strtolower($det->pekerjaan_ibu ?? '-')) }}</td></tr>
        <tr><td class="col-no">16.</td><td class="col-label">Nama Wali Siswa</td><td class="col-titik">:</td><td class="col-value">{{ ucwords(strtolower($det->nama_wali ?? '-')) }}</td></tr>
        <tr><td class="col-no">17.</td><td class="col-label">Alamat Wali Peserta Didik</td><td class="col-titik">:</td><td class="col-value">-</td></tr>
        <tr><td class="col-no">18.</td><td class="col-label">Nomor Telepon Wali</td><td class="col-titik">:</td><td class="col-value">-</td></tr>
        <tr><td class="col-no">19.</td><td class="col-label">Pekerjaan Wali Peserta Didik</td><td class="col-titik">:</td><td class="col-value">{{ ucwords(strtolower($det->pekerjaan_wali ?? '-')) }}</td></tr>
    </table>

    <div class="clearfix">
        <div class="box-photo">
            Pas Foto
        </div>
        <div class="box-ttd">
            {{-- 👇 PERBAIKAN: Format Title Case untuk Kota/Kab 👇 --}}
            {{ \Illuminate\Support\Str::title($infoSekolah->kota_kab ?? 'Salatiga') }}, {{ \Carbon\Carbon::parse($tanggal_cetak)->locale('id')->translatedFormat('d F Y') }}<br>
            Kepala Sekolah<br><br><br><br><br>
            <span class="font-bold" style="text-decoration: underline;">{{ $infoSekolah->nama_kepsek ?? '__________________________' }}</span><br>
            NIP. {{ $infoSekolah->nip_kepsek ?? '-' }}
        </div>
    </div>

    {{-- Beri pemisah halaman untuk siswa berikutnya JIKA bukan siswa terakhir --}}
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif

@endforeach

</body>
</html>