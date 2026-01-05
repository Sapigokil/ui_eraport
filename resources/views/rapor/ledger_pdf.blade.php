<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        th {
            background: #f1f1f1;
        }
        .text-left {
            text-align: left;
        }
    </style>
</head>
<body>

<h3 style="text-align:center">
    LEDGER NILAI SISWA
</h3>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>

            @foreach($daftarMapel as $mp)
                <th>{{ $mp->nama_singkat ?? $mp->nama_mapel }}</th>
            @endforeach

            <th>Total</th>
            <th>Rata-rata</th>
            <th>S</th>
            <th>I</th>
            <th>A</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dataLedger as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="text-left">{{ $row->nama_siswa }}</td>

            @foreach($daftarMapel as $mp)
                <td>{{ $row->scores[$mp->id_mapel] ?? '-' }}</td>
            @endforeach

            <td>{{ $row->total }}</td>
            <td>{{ $row->rata_rata }}</td>
            <td>{{ $row->absensi->sakit }}</td>
            <td>{{ $row->absensi->izin }}</td>
            <td>{{ $row->absensi->alpha }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
