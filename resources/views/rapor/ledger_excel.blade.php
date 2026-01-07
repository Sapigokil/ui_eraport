<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
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
            <td>{{ $row->nama_siswa }}</td>
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
