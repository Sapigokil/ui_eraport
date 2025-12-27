@extends('layouts.app')

@section('title', 'Dashboard E-Rapor Corporate')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

    <x-app.navbar />

    <div class="container-fluid py-4 px-5">

        {{-- HEADER --}}
        <h3 class="mb-3">Hello, {{ auth()->user()->name ?? 'Pengguna' }}</h3>

        {{-- Debug Data --}}
<!-- <pre>
{{ json_encode($progressData) }}
{{ json_encode($statistikNilai) }}
</pre> -->

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
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">Statistik Nilai Siswa</h6>
                <form method="GET">
                    <input type="hidden" name="jurusan" value="{{ request('jurusan') }}">
                    <select name="kelas" class="form-select form-select-sm" style="min-width: 160px;" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id_kelas }}" {{ request('kelas') == $k->id_kelas ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div style="height:300px;">
                <canvas id="chart-donut"></canvas>
            </div>
        </div>

        {{-- PROGRESS INPUT NILAI --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">Progress Input Nilai per Tingkat</h6>
                <form method="GET">
                    <input type="hidden" name="kelas" value="{{ request('kelas') }}">
                    <select name="jurusan" class="form-select form-select-sm" style="min-width: 160px;" onchange="this.form.submit()">
                        <option value="">Semua Jurusan</option>
                        @foreach ($jurusanList as $j)
                            <option value="{{ $j }}" {{ request('jurusan') == $j ? 'selected' : '' }}>
                                {{ $j }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body" style="height:250px;">
                <canvas id="progressChart"></canvas>
            </div>
        </div>


<div class="row">
<div class="col-12">

    <!-- FRAME BESAR -->
    <div class="card shadow-sm rounded-4 mb-4" style="max-width:700px;">
      <div class="card-body">

    <!-- FORM INPUT -->
        <form action="{{ route('dashboard.event.store') }}" method="POST">
          @csrf

        <!-- FORM INPUT -->
        <h6 class="mb-3">Form Input</h6>

        <textarea
        name="deskripsi"
        class="form-control mb-2"
        placeholder="Deskripsi ..."
        required></textarea>

        <input type="date" name="tanggal" class="form-control mb-2" required>

        <!-- <select class="form-control mb-3">
          <option selected disabled>Kategori</option>
          <option>Akademik</option>
          <option>Rapor</option>
          <option>Ujian</option>
        </select> -->

        <button type="submit" class="btn btn-info w-100 mb-4">
          Tambah Event
        </button>

        <!-- GARIS PEMBATAS -->
        <hr>

        <!-- UPCOMING EVENT -->
        <h6 class="mb-3">Upcoming Event</h6>

@forelse ($events as $event)

@php
  $eventDate = \Carbon\Carbon::parse($event->tanggal);

  // DEFAULT
  $bg = 'bg-secondary';
  $icon = 'fa-book';

  if ($eventDate->isToday()) {
      $bg = 'bg-success';
      $icon = 'fa-clock';
  } elseif ($eventDate->isTomorrow()) {
      $bg = 'bg-primary';
      $icon = 'fa-pencil';
  }
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">

<!-- KIRI : ICON + TEXT -->
  <div class="d-flex align-items-center">

  <!-- KOTAK ICON -->
  <div class="rounded d-flex align-items-center justify-content-center {{ $bg }} me-3"
       style="width:46px;height:46px;">
    <i class="fa-solid {{ $icon }} text-white"></i>
  </div>

  <!-- TEXT EVENT -->
  <div>
    <strong>{{ $event->deskripsi }}</strong><br>
    <small class="text-muted">
      {{ $eventDate->translatedFormat('d F Y') }}
    </small>
  </div>

</div>

<!-- KANAN : ACTION -->
  <div class="d-flex gap-4">

    <!-- EDIT -->
    <button
      class="btn btn-sm btn-link text-warning p-0"
      data-bs-toggle="modal"
      data-bs-target="#editEvent{{ $event->id_event }}">
      <i class="fa-solid fa-pen fs-6"></i>
    </button>

    <!-- DELETE -->
    <form action="{{ route('dashboard.event.destroy', $event->id_event) }}"
          method="POST"
          onsubmit="return confirm('Yakin hapus event ini?')">
      @csrf
      @method('DELETE')
      <button class="btn btn-sm btn-link text-danger p-0">
        <i class="fa-solid fa-trash fs-6"></i>
      </button>
    </form>

  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="editEvent{{ $event->id_event }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <form action="{{ route('dashboard.event.update', $event->id_event) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="modal-header">
          <h6 class="modal-title">Edit Event</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <textarea name="deskripsi" class="form-control mb-2" required>
{{ $event->deskripsi }}</textarea>

          <input type="date" name="tanggal"
                 class="form-control"
                 value="{{ $event->tanggal }}"
                 required>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-warning">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@empty
  <p class="text-muted">Belum ada event mendatang</p>
@endforelse

      </div>
    </div>
    <!-- END FRAME -->

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
