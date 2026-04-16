<!DOCTYPE html>
<html>
<head>
    <title>Keterangan Pindah Sekolah - {{ $mutasi->siswa->nama_siswa ?? 'Siswa' }}</title>
    <style>
        @page { 
            margin: 30px 50px 30px 100px; 
        }
        body { font-family: 'Arial', sans-serif; color: #000; line-height: 1.3;}
        
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        /* CSS KHUSUS HALAMAN MUTASI */
        .tabel-mutasi-khusus { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 11pt; 
            margin-bottom: 10px;
            margin-top: 20px;
        }
        .tabel-mutasi-khusus td { 
            border: 1px solid black; 
            padding: 10px 10px; 
            vertical-align: top; 
            line-height: 1.3; 
        }
        .tabel-mutasi-khusus th { 
            border: 1px solid black; 
            padding: 10px 5px; 
            vertical-align: middle; 
            line-height: 1.2; 
            text-align: center; 
            font-weight: bold; 
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>

    <div class="text-center font-bold" style="font-size: 14pt; margin-bottom: 30px; margin-top: 20px;">
        KETERANGAN PINDAH SEKOLAH
    </div>
    
    <table style="width: 100%; margin-bottom: 10px; font-size: 12pt;">
        <tr>
            <td style="width: 160px;">Nama Peserta Didik</td>
            <td style="width: 15px;">:</td>
            <td class="font-bold">{{ strtoupper($mutasi->siswa->nama_siswa ?? '-') }}</td>
        </tr>
    </table>

    <table class="tabel-mutasi-khusus">
        <tr>
            <th colspan="4">KELUAR</th>
        </tr>
        <tr>
            <th style="width: 15%;">Tanggal</th>
            <th style="width: 15%;">Kelas yang<br>ditinggalkan</th>
            <th style="width: 35%;">Sebab-sebab Keluar atau<br>Atas Permintaan (Tertulis)</th>
            <th style="width: 35%;">Tanda Tangan Kepala Sekolah,<br>Stempel Sekolah, dan<br>Tanda Tangan Orang Tua/Wali</th>
        </tr>
        
        {{-- BARIS 1: DIISI DENGAN DATA MUTASI NYATA --}}
        <tr>
            <td class="text-center">
                {{ \Carbon\Carbon::parse($mutasi->tgl_mutasi)->locale('id')->isoFormat('D MMMM YYYY') }}
            </td> 
            <td class="text-center">
                {{ $mutasi->kelasTerakhir->nama_kelas ?? '-' }}
            </td>
            <td>
                {{ $mutasi->alasan }}
            </td>
            <td>
                {{-- 👇 PERBAIKAN: Format Title Case untuk Kota/Kab 👇 --}}
                {{ \Illuminate\Support\Str::title($infoSekolah->kota_kab ?? 'Salatiga') }}, {{ \Carbon\Carbon::parse($tanggal_ttd)->locale('id')->isoFormat('D MMMM YYYY') }}<br>
                Kepala Sekolah,<br>
                <div style="height: 50px;"></div> 
                <span class="font-bold" style="text-decoration: underline;">{{ $infoSekolah->nama_kepsek ?? '__________________________' }}</span><br>
                NIP. {{ $infoSekolah->nip_kepsek ?? '-' }}<br><br>
                <div style="height: 10px;"></div>
                Orang Tua/Wali,<br>
                <div style="height: 40px;"></div> 
                ..................................................
            </td>
        </tr>

        {{-- BARIS 2 & 3: DIBIARKAN KOSONG SESUAI FORMAT --}}
        @for($i=1; $i<=2; $i++)
        <tr>
            <td><div style="height: 120px;"></div></td> 
            <td></td>
            <td></td>
            <td>
                {{-- 👇 PERBAIKAN: Format Title Case untuk Kota/Kab (Baris Kosong) 👇 --}}
                {{ \Illuminate\Support\Str::title($infoSekolah->kota_kab ?? 'Salatiga') }}, ..............................<br>
                Kepala Sekolah,<br>
                <div style="height: 40px;"></div> ..................................................<br>
                NIP.<br><br>
                <div style="height: 10px;"></div>
                Orang Tua/Wali,<br>
                <div style="height: 40px;"></div> ..................................................
            </td>
        </tr>
        @endfor
    </table>

</body>
</html>