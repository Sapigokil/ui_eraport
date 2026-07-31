@extends('layouts.app')

@section('title', 'Dashboard E-Rapor Corporate')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

    <x-app.navbar />

    <div class="container-fluid py-4 px-5">

        {{-- HEADER --}}
        <h3 class="mb-3">Hello, {{ auth()->user()->name ?? 'Pengguna' }}</h3>

        {{-- INFO SEASON (TETAP / TIDAK BISA DITUTUP) --}}
        @if($season)
        <div style="
            background-color: #E8F9FF; 
            border-left: 4px solid #77BEF0;
            padding: 14px 18px;
            margin-bottom: 16px;
            border-radius: 6px;
            font-size: 13px;
        ">
            <h6 style="margin-bottom:6px; font-weight:600;">
                📅 Info Season Input Nilai
            </h6>

            <div style="line-height:1.5; color:#333;">
                <strong>Semester:</strong>
                {{ $season->semester == 1 ? 'Ganjil' : 'Genap' }} <br>

                <strong>Tahun Ajaran:</strong>
                {{ $season->tahun_ajaran }} <br>

                <strong>Status Input Nilai:</strong>
                @if($season->is_open)
                    <span style="
                        background:#93DA97;
                        color:#fff;
                        padding:2px 8px;
                        border-radius:12px;
                        font-size:11px;
                    ">
                        DIBUKA
                    </span>
                @else
                    <span style="
                        background:#E57373;
                        color:#fff;
                        padding:2px 8px;
                        border-radius:12px;
                        font-size:11px;
                    ">
                        TERKUNCI
                    </span>
                @endif
            </div>
        </div>
        @else
        <div style="
            background-color: #FFF3CD;
            border-left: 5px solid #FFC107;
            padding: 14px 18px;
            margin-bottom: 16px;
            border-radius: 6px;
            font-size: 13px;
        ">
            ⚠️ Season belum diset. Silakan hubungi admin.
        </div>
        @endif

        {{-- ========================================== --}}
        {{-- NOTIFIKASI (BACKGROUND ORANGE, TEKS HITAM) --}}
        {{-- ========================================== --}}
        {{-- @forelse($notifications as $notif)
        <div class="notification-card shadow-sm" style="
            background-color: #FFB74D;
            border-left: 6px solid #E65100;
            color: #000000;
            padding: 14px 18px;
            margin-bottom: 16px;
            border-radius: 8px;
            position: relative;
            width: 100%;
        ">
            <span style="
                position: absolute;
                top: 10px;
                right: 14px;
                cursor: pointer;
                font-weight: bold;
                font-size: 18px;
            " onclick="this.parentElement.style.display='none'" title="Tutup">&times;</span>

            <h6 style="
                margin-bottom: 6px;
                font-size: 16px;
                font-weight: 800;
                color: #000000;
            ">
                <i class="fas fa-bell me-1"></i> Notifikasi Terbaru
            </h6>

            <div class="notif-deskripsi" style="
                margin-bottom: 8px;
                font-size: 14px;
                font-weight: 500;
                line-height: 1.4;
            ">
                {{ $notif->deskripsi }}
            </div>

            <div class="notif-tanggal" style="
                font-size: 12px;
                font-weight: 600;
                opacity: 0.8;
            ">
                ({{ \Carbon\Carbon::parse($notif->tanggal)->translatedFormat('d F Y') }})
            </div>
        </div>
        @empty
        <p style="font-size: 14px; color: #555; font-weight: 500;">Belum ada notifikasi.</p>
        @endforelse --}}


        {{-- ========================================== --}}
        {{-- DAFTAR INFORMASI & ACARA                   --}}
        {{-- ========================================== --}}
        <h6 style="margin-top: 24px; margin-bottom: 14px; font-size: 16px; font-weight: 700; color: #344767;">
            <i class="fas fa-bullhorn text-warning me-1"></i> Informasi & Acara
        </h6>
        
        @forelse($events as $event)
            @php
                $isAcara = ($event->kategori == 'Acara');
                
                // Tema Warna Dinamis
                $cardBg      = $isAcara ? '#7E57C2' : '#FFB74D';
                $cardBorder  = $isAcara ? '#5E35B1' : '#E65100';
                $textColor   = $isAcara ? '#ffffff' : '#000000';
                $descColor   = $isAcara ? '#f8f9fa' : '#1a1a1a';
                $closeColor  = $isAcara ? '#ffffff' : '#000000';
                $iconTitle   = $isAcara ? 'fa-calendar-check' : 'fa-bell';
                
                // Box Tanggal
                $dateBg      = '#ffffff';
                $dateText    = $isAcara ? '#7E57C2' : '#E65100';
                $dateBorder  = $isAcara ? '#ede7f6' : '#FFE0A5';
                
                // Link Lampiran
                $badgeBg     = $isAcara ? 'bg-white' : 'bg-dark';
                $badgeText   = $isAcara ? '#7E57C2' : '#ffffff';
                $footerColor = $isAcara ? '#e1bee7' : '#555555';
            @endphp

            <div class="event-card d-flex align-items-start p-3 mb-3 shadow-sm" style="
                background: {{ $cardBg }}; 
                color: {{ $textColor }}; 
                border-radius: 12px; 
                border-left: 6px solid {{ $cardBorder }}; 
                position: relative;
            ">
                {{-- Tombol Close --}}
                <span style="position: absolute; top: 8px; right: 14px; cursor: pointer; color: {{ $closeColor }}; font-size: 20px; font-weight: bold; opacity: 0.8;" onclick="this.parentElement.style.display='none'" title="Tutup">
                    &times;
                </span>

                {{-- Ikon Kalender Mini --}}
                <div class="date-box text-center rounded p-2 me-3" style="background: {{ $dateBg }}; border: 1px solid {{ $dateBorder }}; min-width: 65px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <span class="d-block fw-bolder" style="font-size: 20px; line-height: 1; color: {{ $dateText }};">
                        {{ \Carbon\Carbon::parse($event->tanggal)->format('d') }}
                    </span>
                    <span class="d-block text-uppercase mt-1" style="font-size: 11px; font-weight: 800; letter-spacing: 0.5px; color: {{ $dateText }};">
                        {{ \Carbon\Carbon::parse($event->tanggal)->translatedFormat('M') }}
                    </span>
                </div>
                
                {{-- Info Event (Judul & Deskripsi) --}}
                <div class="event-info flex-grow-1 pe-3">
                    <h6 class="mb-1 fw-bolder" style="font-size: 16px; color: {{ $textColor }};">
                        <i class="fas {{ $iconTitle }} me-1" style="opacity: 0.8;"></i> {{ $event->judul }}
                    </h6>
                    <p class="mb-2" style="font-size: 14px; line-height: 1.5; font-weight: 500; color: {{ $descColor }};">
                        {{ $event->deskripsi }}
                    </p>
                    
                    {{-- Footer Info: Durasi & Lampiran --}}
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <span style="font-size: 12px; font-weight: 600; color: {{ $footerColor }};">
                            <i class="fas fa-clock me-1"></i> s/d {{ \Carbon\Carbon::parse($event->tanggal_selesai)->translatedFormat('d F Y') }}
                        </span>
                        
                        @if($event->lampiran)
                            <a href="{{ asset('storage/' . $event->lampiran) }}" target="_blank" class="badge {{ $badgeBg }} px-3 py-2 shadow-sm" style="font-size: 11px; font-weight: 700; color: {{ $badgeText }}; text-decoration: none; border-radius: 6px;">
                                <i class="fas fa-paperclip me-1"></i> Lihat Lampiran
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-3 mb-4" style="background: #f8f9fa; border-radius: 8px; border: 1px dashed #dee2e6;">
                <p style="font-size: 14px; color: #6c757d; font-weight: 500; margin-bottom: 0;">Belum ada informasi terbaru.</p>
            </div>
        @endforelse


        {{-- CARD STATISTIK --}}
        <div class="row g-3 mb-4">

            {{-- JUMLAH SISWA --}}
            <div class="col-md-3">
                <div style="
                    background-color: #FFDEE6;
                    border-left: 5px solid #EA5B6F;
                    padding: 14px 18px;
                    border-radius: 6px;
                    font-size: 13px;
                ">
                    <div class="text-muted text-xs">Jumlah Siswa</div>
                    <div class="fs-4 fw-bold text-dark">
                        {{ $totalSiswa }}
                    </div>
                </div>
            </div>
            
            {{-- JUMLAH GURU --}}
            <div class="col-md-3">
                <div style="
                    background-color: #FFFFE0;
                    border-left: 4px solid #FFCB61;
                    padding: 14px 18px;
                    border-radius: 6px;
                    font-size: 13px;
                ">
                    <div class="text-muted text-xs">Jumlah Guru</div>
                    <div class="fs-4 fw-bold text-dark">
                        {{ $totalGuru }}
                    </div>
                </div>
            </div>
            
            {{-- JUMLAH KELAS --}}
            <div class="col-md-3">
                <div style="
                    background-color: #E8F5E9;
                    border-left: 4px solid #93DA97;
                    padding: 14px 18px;
                    border-radius: 6px;
                    font-size: 13px;
                ">
                    <div class="text-muted text-xs">Jumlah Kelas</div>
                    <div class="fs-4 fw-bold text-dark">
                        {{ $totalKelas }}
                    </div>
                </div>
            </div>
            
            {{-- JUMLAH MAPEL --}}
            <div class="col-md-3">
                <div style="
                    background-color: #E8F9FF; 
                    border-left: 4px solid #77BEF0;
                    padding: 14px 18px;
                    border-radius: 6px;
                    font-size: 13px;
                ">
                    <div class="text-muted text-xs">Jumlah Mapel</div>
                    <div class="fs-4 fw-bold text-dark">
                        {{ $totalMapel }}
                    </div>
                </div>
            </div>

        </div>

        {{-- KOLOM KIRI & KANAN --}}
        <div class="row mb-4">

            {{-- KOLOM KIRI --}}
            <div class="col-md-6 d-flex flex-column gap-3">

                {{-- STATISTIK NILAI --}}
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-3">Statistik Nilai</h6>
                        <form method="GET" id="filterForm" class="d-flex gap-3">

                            {{-- KELAS --}}
                            <select name="kelas"
                                id="kelasSelect"
                                class="form-select"
                                style="min-width:130px; height:40px;">

                                <option value="" disabled {{ request('kelas') ? '' : 'selected' }}>
                                    Pilih Kelas
                                </option>

                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id_kelas }}"
                                        {{ request('kelas') == $k->id_kelas ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- SEMESTER --}}
                            <select name="semester"
                                id="semesterSelect"
                                class="form-select"
                                style="min-width:130px; height:40px;"
                                required>
                                @foreach($semesterList as $sem)
                                    <option value="{{ $sem }}"
                                        {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>
                                        {{ $sem }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- TAHUN AJARAN --}}
                            <select name="tahun_ajaran"
                                id="tahunSelect"
                                class="form-select"
                                style="min-width:130px; height:40px;"
                                required>
                                @foreach($tahunAjaranList as $ta)
                                <option value="{{ $ta }}"
                                    {{ $tahunAjaranAktif == $ta ? 'selected' : '' }}>
                                    {{ $ta }}
                                </option>
                                @endforeach
                            </select>

                        </form>
                    </div>

                    <div class="card-body">
                        <canvas id="chart-donut"></canvas>
                    </div>
                </div>

                {{-- DETAIL SISWA NILAI MERAH --}}
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background:#DDDDDD;">
                        <h5 class="text-sm font-weight-bold mb-0">
                            Detail Siswa Berdasarkan Rentang Nilai
                        </h5>

                        <form method="GET">
                            <input type="hidden" name="kelas" value="{{ request('kelas') }}">
                            <input type="hidden" name="semester" value="{{ request('semester') }}">
                            <input type="hidden" name="tahun_ajaran" value="{{ request('tahun_ajaran') }}">

                            <select name="rentang_nilai"
                                    class="form-select form-select-sm"
                                    style="min-width: 110px"
                                    onchange="this.form.submit()">
                                <option value="lt78" {{ request('rentang_nilai')=='lt78'?'selected':'' }}>Nilai < 78</option>
                                <option value="78_85" {{ request('rentang_nilai')=='78_85'?'selected':'' }}>Nilai 78 - 85</option>
                                <option value="86_92" {{ request('rentang_nilai')=='86_92'?'selected':'' }}>Nilai 86 - 92</option>
                                <option value="gte93" {{ request('rentang_nilai')=='gte93'?'selected':'' }}>Nilai ≥ 93</option>
                            </select>
                        </form>
                    </div>

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
                                                @php
                                                    $nilai = $row->nilai_akhir;
                                                    $warna = match(true) {
                                                        $nilai < 78 => 'danger',
                                                        $nilai <= 85 => 'warning',
                                                        $nilai <= 92 => 'info',
                                                        default => 'success'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $warna }}">
                                                    {{ number_format($nilai, 1) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-muted text-sm text-center py-3">
                                Tidak ada data pada rentang nilai ini
                            </div>
                        @endif
                    </div>
                </div>

                {{-- PROGRESS INPUT NILAI --}}
                <div class="card shadow-sm mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            Progress Input Nilai per Jurusan
                            <span class="text-muted text-sm">
                                (Tingkat {{ request('tingkat', 'Semua') }})
                            </span>
                        </h6>
                        <form method="GET" id="barFilterForm" class="d-flex gap-3 mt-3">
                            <input type="hidden" name="kelas" value="{{ request('kelas') }}">

                            {{-- TINGKAT --}}
                            <select name="tingkat" id="tingkatSelect" class="form-select" style="min-width:130px; height:40px;">
                                <option value="" disabled {{ request('tingkat') ? '' : 'selected' }}>Pilih Tingkat</option>
                                <option value="10" {{ request('tingkat') == '10' ? 'selected' : '' }}>10</option>
                                <option value="11" {{ request('tingkat') == '11' ? 'selected' : '' }}>11</option>
                                <option value="12" {{ request('tingkat') == '12' ? 'selected' : '' }}>12</option>
                            </select>

                            {{-- SEMESTER --}}
                            <select name="semester" id="semesterBarSelect" class="form-select" style="min-width:130px; height:40px;" required>
                                @foreach($semesterList as $sem)
                                    <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                @endforeach
                            </select>

                            {{-- TAHUN AJARAN --}}
                            <select name="tahun_ajaran" id="tahunBarSelect" class="form-select" style="min-width:130px; height:40px;" required>
                                @foreach($tahunAjaranList as $ta)
                                    <option value="{{ $ta }}" {{ $tahunAjaranAktif == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="card-body" style="height:250px;">
                        <canvas id="progressChart"></canvas>
                    </div>

                    <div class="mt-3">
                        <div class="card-header" style="background:#DDDDDD;">
                            <h5 class="text-sm font-weight-bold mb-2">
                                Detail Mapel Belum Input Nilai
                            </h5>
                        </div>
                        <div style="max-height: 300px; overflow-y: auto; padding: 12px 16px;">
                            @foreach($progressDetail as $tingkat => $detail)
                                <div class="mb-3">
                                    <strong>Kelas {{ $tingkat }} ({{ $detail['progress'] }}%)</strong>

                                    @if(collect($detail['belum'])->isNotEmpty())
                                        <div class="text-warning text-sm mt-1">Mapel belum lengkap:</div>
                                        <ul class="mb-0">
                                            @foreach($detail['belum'] as $mapel)
                                                <li>{{ $mapel }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="text-success mt-1">Semua mapel sudah menginput nilai ✓</div>
                                    @endif
                                </div>
                            @endforeach

                            @if(collect($progressDetail)->every(fn($d) => $d['progress'] == 100))
                                <p class="text-success text-sm mb-0">Semua mapel sudah menginput nilai ✔</p>
                            @endif
                        </div>    
                    </div>
                </div>

            </div> {{-- Tutup Kolom Kiri --}}


            {{-- KOLOM KANAN --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6>Status Kesiapan Rapor</h6>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr class="text-center">
                                    <th>Kelas</th>
                                    <th>Tingkat</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($statusRapor as $row)
                                <tr class="text-center">
                                    <td>{{ $row['kelas'] }}</td>
                                    <td>{{ $row['tingkat'] }}</td>
                                    <td>
                                        @php
                                            $warna = match($row['status']) {
                                                'Siap' => '#93DA97',
                                                'Belum Siap' => '#FFCB61',
                                                default => '#EA5B6F'
                                            };
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
            </div> {{-- Tutup Kolom Kanan --}}

        </div> {{-- Tutup Row --}}

    </div> {{-- Tutup Container --}}

    <x-app.footer />
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
            if (val === sortedProgress[0]) return '#93DA97'; // hijau
            if (val === sortedProgress[1]) return '#77BEF0'; // biru
            return '#EA5B6F';                                // merah
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
                        '#EA5B6F',
                        '#FFCB61',
                        '#93DA97',
                        '#77BEF0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    /* =======================
        FILTER FORM LOGIC
    ======================= */
    const form     = document.getElementById('filterForm');
    const kelas    = document.getElementById('kelasSelect');
    const semester = document.getElementById('semesterSelect');
    const tahun    = document.getElementById('tahunSelect');

    if (form && kelas && semester && tahun) {
        let sudahSubmit = {{ request()->has('kelas') ? 'true' : 'false' }};

        semester.addEventListener('change', () => {
            sudahSubmit = true;
            form.submit();
        });

        tahun.addEventListener('change', () => {
            sudahSubmit = true;
            form.submit();
        });

        kelas.addEventListener('change', () => {
            if (sudahSubmit) {
                form.submit();
            }
        });
    }

    // BAR CHART FILTER (TINGKAT)
    const barForm     = document.getElementById('barFilterForm');
    const tingkatBar  = document.getElementById('tingkatSelect');
    const semesterBar = document.getElementById('semesterBarSelect');
    const tahunBar    = document.getElementById('tahunBarSelect');

    if (!barForm || !tingkatBar || !semesterBar || !tahunBar) return;

    let sudahSubmitBar = {{ request()->has('semester') || request()->has('tahun_ajaran') ? 'true' : 'false' }};

    semesterBar.addEventListener('change', () => {
        sudahSubmitBar = true;
        barForm.submit();
    });

    tahunBar.addEventListener('change', () => {
        sudahSubmitBar = true;
        barForm.submit();
    });

    tingkatBar.addEventListener('change', () => {
        if (sudahSubmitBar) {
            barForm.submit();
        }
    });
});
</script>
@endpush