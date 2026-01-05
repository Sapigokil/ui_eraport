@extends('layouts.app')

@section('title', 'Dashboard E-Rapor Corporate')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

    <x-app.navbar />

    <div class="container-fluid py-4 px-5">

        {{-- HEADER --}}
        <h3 class="mb-3">Hello, {{ auth()->user()->name ?? 'Pengguna' }}</h3>
{{-- (NOTIFIKASI) --}}
    @forelse($notifications as $notif)
<div class="notification-card" style="
    background-color: #d4f5d4;
    border-left: 4px solid #3ac13a;
    padding: 10px 14px;
    margin-bottom: 12px;
    border-radius: 6px;
    position: relative;
    font-size: 13px;
    width: 100%;               /* 🔥 full width */
    max-width: 100%;           /* 🔥 sejajar card statistik */
">
    {{-- Tombol close --}}
    <span style="
        position: absolute;
        top: 6px;
        right: 10px;
        cursor: pointer;
        font-weight: bold;
        font-size: 14px;
    " onclick="this.parentElement.style.display='none'">&times;</span>

    <h6 style="
        margin-bottom: 4px;     /* 🔥 dipadatin */
        font-size: 14px;
        font-weight: 600;
    ">
        Notifikasi Terbaru
    </h6>

    <div class="notif-deskripsi" style="
        margin-bottom: 4px;     /* 🔥 dipadatin */
        line-height: 1.3;
        color: #555; 
    ">
        {{ $notif->deskripsi }}
    </div>

    <div class="notif-tanggal" style="
    font-size: 11px;
    color: #555;
    line-height: 1.2;
">
    ({{ \Carbon\Carbon::parse($notif->tanggal)->translatedFormat('d F Y') }})
</div>
</div>

@empty
<p style="font-size: 13px; color: #555;">Belum ada notifikasi.</p>
@endforelse
    {{-- EVENT (STYLE SAMA KAYAK NOTIFIKASI) --}}
@forelse($events as $event)
<div class="notification-card" style="
    background-color: #FEEAC9;
    border-left: 4px solid #FF6D1F;
    padding: 10px 14px;
    margin-bottom: 12px;
    border-radius: 6px;
    position: relative;
    font-size: 13px;
">
{{-- Tombol close --}}
    <span style="
        position: absolute;
        top: 6px;
        right: 10px;
        cursor: pointer;
        font-weight: bold;
        font-size: 14px;
    " onclick="this.parentElement.style.display='none'">&times;</span>
    
    <h6 style="
        margin-bottom: 4px;
        font-size: 14px;
        font-weight: 600;
    ">
        Upcoming Event
    </h6>

    <div style="
        margin-bottom: 4px;
        line-height: 1.3;
        color: #333;
    ">
        {{ $event->deskripsi }}
    </div>

    <div style="
        font-size: 11px;
        color: #555;
    ">
        ({{ \Carbon\Carbon::parse($event->tanggal)->translatedFormat('d F Y') }})
    </div>
</div>
@empty
<p style="font-size: 13px; color: #555;">Belum ada event.</p>
@endforelse

        {{-- CARD STATISTIK --}}
        <div class="row mb-4">
            @foreach ([
                'Jumlah Siswa' => $totalSiswa,
                'Jumlah Guru' => $totalGuru,
                'Jumlah Kelas' => $totalKelas,
                'Jumlah Mapel' => $totalMapel
            ] as $label => $value)
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <p class="text-sm text-muted mb-1">{{ $label }}</p>
                            <h4 class="fw-bold">{{ $value }}</h4>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

<div class="row mb-4">

    {{-- KOLOM KIRI --}}
    <div class="col-md-6 d-flex flex-column gap-3">

        {{-- STATISTIK NILAI --}}
        <div class="card shadow-sm">
    <div class="card-header">
        <h6 class="mb-3">Statistik Nilai</h6>

        <form method="GET" class="d-flex gap-3">

            {{-- KELAS --}}
            <select name="kelas"
                class="form-select"
                style="min-width: 130px; height: 40px;"
                onchange="this.form.submit()">
                <option value="">Pilih Kelas</option>
                @foreach ($kelasList as $k)
                    <option value="{{ $k->id_kelas }}"
                        {{ request('kelas') == $k->id_kelas ? 'selected' : '' }}>
                        {{ $k->nama_kelas }}
                    </option>
                @endforeach
            </select>

            {{-- SEMESTER --}}
            <select name="semester"
                class="form-select"
                style="min-width: 130px; height: 40px;"
                onchange="this.form.submit()">
                <option value="Ganjil"
                    {{ request('semester', $defaultSemester) == 'Ganjil' ? 'selected' : '' }}>
                    Ganjil
                </option>
                <option value="Genap"
                    {{ request('semester', $defaultSemester) == 'Genap' ? 'selected' : '' }}>
                    Genap
                </option>
            </select>

            {{-- TAHUN AJARAN --}}
            <select name="tahun_ajaran"
                class="form-select"
                style="min-width: 130px; height: 40px;"
                onchange="this.form.submit()">
                @foreach ($tahunAjaran as $ta)
                    <option value="{{ $ta }}"
                        {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                        {{ $ta }}
                    </option>
                @endforeach
            </select>

        </form>
    </div>

    <div class="card-body">
        <canvas id="chart-donut"></canvas>
    </div>
            {{-- DETAIL SISWA NILAI MERAH --}}
<div class="card shadow-sm mt-3">
    <div class="card-header bg-light">
        <h5 class="text-sm font-weight-bold mb-2">
        Detail Siswa Nilai di Bawah 78
    </h5></div>

    <div class="card-body p-2" style="max-height: 260px; overflow-y: auto;">
        @if($detailNilaiMerah->count() > 0)
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr class="text-muted text-xs">
                        <th>Nama Siswa</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailNilaiMerah as $row)
                        <tr>
                            <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
                            <td>{{ $row->mapel->nama_mapel ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-danger">
                                    {{ number_format($row->nilai_akhir, 1) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-success text-sm text-center py-3">
                🎉 Tidak ada nilai di bawah 78
            </div>
        @endif
    </div>
</div>

        </div>

        {{-- PROGRESS INPUT NILAI --}}
        <div class="card shadow-sm">
            <div class="card-header">
        <h6 class="mb-0">Progress Input Nilai per Tingkat</h6>
        <form method="GET" class="d-flex gap-3">
                    <input type="hidden" name="kelas" value="{{ request('kelas') }}">
                    {{-- JURUSAN --}}
                    <select name="jurusan" class="form-select form-select-sm" style="min-width: 130px;" onchange="this.form.submit()">
                        <option value="">Semua Jurusan</option>
                        @foreach ($jurusanList as $j)
                            <option value="{{ $j }}" {{ request('jurusan') == $j ? 'selected' : '' }}>
                                {{ $j }}
                            </option>
                        @endforeach
                    </select>
            {{-- SEMESTER --}}
            <select name="semester"
                class="form-select"
                style="min-width: 130px; height: 40px;"
                onchange="this.form.submit()">
                <option value="Ganjil"
                    {{ request('semester', $defaultSemester) == 'Ganjil' ? 'selected' : '' }}>
                    Ganjil
                </option>
                <option value="Genap"
                    {{ request('semester', $defaultSemester) == 'Genap' ? 'selected' : '' }}>
                    Genap
                </option>
            </select>
             {{-- TAHUN AJARAN --}}
            <select name="tahun_ajaran"
                class="form-select"
                style="min-width: 130px; height: 40px;"
                onchange="this.form.submit()">
                @foreach ($tahunAjaran as $ta)
                    <option value="{{ $ta }}"
                        {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                        {{ $ta }}
                    </option>
                @endforeach
            </select>
                </form>
            </div>
            <div class="card-body" style="height:250px;">
                <canvas id="progressChart"></canvas>
            </div>
            <div class="mt-3">
    <div class="card-header bg-light">
            <h5 class="text-sm font-weight-bold mb-2">
        Detail Mapel Belum Input Nilai
    </h5></div>
    <div style="
        max-height: 300px;
        overflow-y: auto;
        padding: 12px 16px;
    ">
    @foreach($progressDetail as $tingkat => $detail)
<div class="mb-3">
    <strong>Kelas {{ $tingkat }} ({{ $detail['progress'] }}%)</strong>

    @if($detail['belum']->count() > 0)
    <div class="text-warning text-sm mt-1">
        Mapel belum lengkap:
    </div>
    <ul class="mb-0">
        @foreach($detail['belum'] as $mapel)
            <li>{{ $mapel }}</li>
        @endforeach
    </ul>
@else
    <div class="text-success mt-1">
        Semua mapel sudah menginput nilai ✓
    </div>
@endif

</div>
@endforeach

    @if(collect($progressDetail)->every(fn($d) => $d['progress'] == 100))
    <p class="text-success text-sm mb-0">
        Semua mapel sudah menginput nilai ✔
    </p>
@endif
</div>    
</div>

        </div>


<div class="row">
<div class="col-12">



  </div>
</div>

    </div>

    {{-- KOLOM KANAN --}}
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <h6>Status Kesiapan Rapor</h6>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Tingkat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statusRapor as $row)
                        <tr>
                            <td>{{ $row['kelas'] }}</td>
                            <td>{{ $row['tingkat'] }}</td>
                            <td>
                            @php
                // Atur warna custom sesuai status
                $warna = '';
                if($row['status'] == 'Siap'){
                    $warna = '#76fa72'; // hijau
                } elseif($row['status'] == 'Belum Siap'){
                    $warna = '#fa7276'; // merah
                } else {
                    $warna = '#cccccc'; // default abu-abu
                }
            @endphp
                                <span class="badge" style="background-color: {{ $warna }}; color: white;">
                {{ $row['status'] }}
            </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <x-app.footer />
    </div>
</main>



@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    if (typeof Chart === 'undefined') {
        console.error('Chart.js belum termuat');
        return;
    }

    const progressLabels = @json($progressLabels);

    const progressData   = @json($progressData);
    const statistikNilai = @json($statistikNilai);
    const progressDetail = @json($progressDetail);

    // BAR CHART
const progressCanvas = document.getElementById('progressChart');

if (progressCanvas) {

    const sortedProgress = [...progressData].sort((a, b) => b - a);

    const barColors = progressData.map(val => {
        if (val === sortedProgress[0]) return '#76fa72'; // hijau
        if (val === sortedProgress[1]) return '#72bafa'; // biru
        return '#fa7276';                                // merah
    });

    new Chart(progressCanvas, {
        type: 'bar',
        data: {
            labels: progressLabels,
            datasets: [{
                label: 'Progress (%)',
                data: progressData,
                backgroundColor: barColors,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

    // DONUT CHART
    const donutCanvas = document.getElementById('chart-donut');
    if (donutCanvas) {
        new Chart(donutCanvas, {
            type: 'doughnut',
            data: {
                labels: ["Nilai < 78", "78–85", "86–92", "≥ 93"],
                datasets: [{
                    data: statistikNilai,
                    backgroundColor: [
                        '#fa7276',
                        '#fab272',
                        '#72bafa',
                        '#76fa72'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

});
</script>
@endpush
