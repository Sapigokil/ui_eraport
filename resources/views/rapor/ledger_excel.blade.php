<table border="1">
    <colgroup>
        <col style="width: 28px;"> 
        <col style="width: 28px;">   {{-- No --}}
        <col style="width: 180px;">  {{-- Nama --}}

        @foreach($daftarMapel as $mp)
            <col style="width: 60px;"> {{-- Nilai --}}
        @endforeach

        <col style="width: 70px;"> {{-- Total --}}
        <col style="width: 80px;"> {{-- Rata-rata --}}
        <col style="width: 40px;"> {{-- S --}}
        <col style="width: 40px;"> {{-- I --}}
        <col style="width: 40px;"> {{-- A --}}
    </colgroup>

    <thead>
        <tr>
            <th>Rank</th>
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
            <td>
                @if(request('urut','ranking') === 'ranking')
                    {{ $loop->iteration }}
                @else
                    -
                @endif
            </td>
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
