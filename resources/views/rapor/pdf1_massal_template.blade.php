<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <style>
        /* =========================
           PAGE SETTING (DOMPDF)
        ========================== */
        @page {
            size: A4;
            margin: 150px 40px 60px 40px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
            color: #000;
        }

        /* =========================
           HEADER
        ========================== */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 130px;
            background: #ffffff;
            border-bottom: 2px solid #000;
        }

        .header-wrapper {
            width: 100%;
            padding: 10px 15px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* WAJIB */
        }

        .header-table td {
            padding: 2px 4px;
            vertical-align: top;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .font-bold {
            font-weight: bold;
        }

        /* =========================
           CONTENT
        ========================== */
        main {
            padding-top: 10px;
        }

        .dummy-box {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    {{-- =========================
         HEADER (FIXED)
    ========================== --}}
    <header>
        <div class="header-wrapper">
            <table class="header-table">
                <tr>
                    <td style="width: 25mm;">Nama</td>
                    <td style="width: 5mm;">:</td>
                    <td style="width: 60mm;" class="font-bold">
                        {{ strtoupper($siswa->nama_siswa ?? 'NAMA SISWA SANGAT PANJANG SEKALI') }}
                    </td>

                    <td style="width: 10mm;"></td>

                    <td style="width: 20mm;">Kelas</td>
                    <td style="width: 5mm;">:</td>
                    <td style="width: 25mm;">
                        {{ $siswa->kelas->nama_kelas ?? 'XII RPL 1' }}
                    </td>
                </tr>

                <tr>
                    <td>NIS / NIPD</td>
                    <td>:</td>
                    <td>{{ $siswa->nipd ?? '1234567890' }}</td>

                    <td></td>

                    <td>Semester</td>
                    <td>:</td>
                    <td>{{ $semester ?? 'Ganjil' }}</td>
                </tr>

                <tr>
                    <td>Tahun Ajaran</td>
                    <td>:</td>
                    <td>{{ $tahun_ajaran ?? '2025 / 2026' }}</td>

                    <td></td>

                    <td>Wali Kelas</td>
                    <td>:</td>
                    <td>
                        {{ $wali_kelas ?? 'NAMA WALI KELAS PANJANG SEKALI' }}
                    </td>
                </tr>
            </table>
        </div>
    </header>

    {{-- =========================
         CONTENT
    ========================== --}}
    <main>
        <div class="dummy-box">
            Contoh konten halaman 1.
        </div>

        <div class="dummy-box">
            Tambahkan banyak konten di sini sampai halaman 2,
            untuk memastikan header tetap aman dan tidak overflow.
        </div>

        @for ($i = 1; $i <= 40; $i++)
            <div class="dummy-box">
                Baris konten ke-{{ $i }}
            </div>
        @endfor
    </main>

</body>
</html>
