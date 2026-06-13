{{-- File: resources/views/nilai/exports/ledger_wali_export.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ledger Kelas</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-weight-bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: middle; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @php
        $catLabels = [1 => 'Umum', 2 => 'Kejuruan', 3 => 'Pilihan', 4 => 'Mulok'];
        $groupedMapel = $daftarMapel->groupBy('kategori');
    @endphp

    <div class="text-center">
        <h2>LEDGER NILAI SISWA</h2>
        <p class="font-weight-bold">
            Kelas: {{ $kelasTerpilih->nama_kelas ?? '-' }} | 
            Semester: {{ $semesterRaw }} | 
            Tahun Ajaran: {{ $tahun_ajaran }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">No</th>
                <th rowspan="2" style="width: 150px;">Nama Siswa</th>
                <th rowspan="2" style="width: 60px;">NIS</th>
                @foreach($groupedMapel as $catId => $mapels)
                    <th colspan="{{ count($mapels)}}">{{ $catLabels[$catId] ?? 'Lainnya' }}</th>
                @endforeach
                <th colspan="2">REKAP</th>
                <th colspan="3">ABSENSI</th>
                @if(strtoupper($semesterRaw) == 'GENAP')
                    <th rowspan="2">KENAIKAN</th>
                @endif
                @if($showRanking == '1')
                    <th rowspan="2">RANK</th>
                @endif
            </tr>
            <tr>
                @foreach($groupedMapel as $catId => $mapels)
                    @foreach($mapels as $mp)
                        <th>{{ substr($mp->nama_singkat ?? $mp->nama_mapel, 0, 5) }}</th>
                    @endforeach
                @endforeach
                <th>JML</th>
                <th>AVG</th>
                <th>S</th>
                <th>I</th>
                <th>A</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($dataLedger) && $dataLedger->isNotEmpty())
                @foreach($dataLedger as $idx => $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-left font-weight-bold">{{ $row->nama_siswa }}</td>
                    <td class="text-center">{{ $row->nipd ?? '-' }}</td>
                    @foreach($groupedMapel as $catId => $mapels)
                        @foreach($mapels as $mp)
                            @php $val = $row->scores[$mp->id_mapel] ?? null; @endphp
                            <td class="text-center" style="{{ !is_numeric($val) ? 'color: red;' : '' }}">
                                {{ is_numeric($val) ? (int)$val : '-' }}
                            </td>
                        @endforeach
                    @endforeach
                    <td class="text-center font-weight-bold">{{ (int)$row->total }}</td>
                    <td class="text-center font-weight-bold">{{ number_format($row->rata_rata, 1) }}</td>
                    
                    <td class="text-center">{{ $row->absensi->sakit }}</td>
                    <td class="text-center">{{ $row->absensi->izin }}</td>
                    <td class="text-center">{{ $row->absensi->alpha }}</td>

                    @if(strtoupper($semesterRaw) == 'GENAP')
                        <td class="text-center font-weight-bold">
                            @if($row->status_kenaikan == 'naik_kelas') NAIK
                            @elseif($row->status_kenaikan == 'tinggal_kelas') TINGGAL
                            @else - @endif
                        </td>
                    @endif

                    @if($showRanking == '1')
                        <td class="text-center font-weight-bold">{{ $row->ranking_no }}</td>
                    @endif
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="100%" class="text-center">Data Ledger Kosong.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>