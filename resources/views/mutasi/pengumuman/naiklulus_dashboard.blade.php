@extends('layouts.app') 

@section('page-title', 'Penjadwalan Pengumuman')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg pt-4">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                {{-- 👇 REVISI JUDUL DAN DESKRIPSI 👇 --}}
                <h3 class="text-dark font-weight-bolder mb-0">Penjadwalan Pengumuman</h3>
                <p class="text-secondary mb-0">Set Penjadwalan Pengumuman Kelulusan dan Kenaikan Kelas.</p>
            </div>
            <div>
                <form action="{{ route('mutasi.dashboard.index') }}" method="GET">
                    <select name="tahun_ajaran" class="form-select border-radius-md font-weight-bold shadow-sm pe-5 cursor-pointer" style="min-width: 220px;" onchange="this.form.submit()">
                        @foreach($listTA as $t)
                            <option value="{{ $t }}" {{ $ta == $t ? 'selected' : '' }}>Tahun Ajaran {{ $t }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="row g-4">
            {{-- SEKSI KELULUSAN (TINGKAT 12) --}}
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100" style="background-color: rgba(253, 160, 21, 0.08);">
                    <div class="card-header bg-gradient-warning p-4 border-radius-top-lg">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-white mb-0"><i class="fas fa-graduation-cap me-2"></i> Kelulusan Siswa</h5>
                            <span class="badge bg-white text-warning font-weight-bold">Tingkat {{ $maxTingkat }}</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        {{-- Jadwal Info --}}
                        <div class="bg-white border-radius-md p-3 mb-4 d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <p class="text-xxs text-secondary mb-1 font-weight-bold text-uppercase">Jadwal Rilis Pengumuman</p>
                                @if($jadwalLulus)
                                    <div class="d-flex align-items-center">
                                        <h6 class="mb-0 text-dark font-weight-bolder">
                                            {{ $jadwalLulus->waktu_buka->locale('id')->isoFormat('D MMMM Y, HH:mm') }} - {{ $jadwalLulus->waktu_tutup->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
                                        </h6>
                                        
                                        @if($jadwalLulus->waktu_tutup->isPast())
                                            <span class="badge bg-danger ms-2 px-2 py-1 shadow-sm" style="font-size: 0.6rem;"><i class="fas fa-lock me-1"></i> Sesi Berakhir</span>
                                        @elseif($jadwalLulus->waktu_buka->isFuture())
                                            <span class="badge bg-secondary ms-2 px-2 py-1 shadow-sm" style="font-size: 0.6rem;"><i class="fas fa-hourglass-start me-1"></i> Menunggu Waktu</span>
                                        @else
                                            <span class="badge bg-success ms-2 px-2 py-1 shadow-sm" style="font-size: 0.6rem;"><i class="fas fa-broadcast-tower me-1"></i> Sedang Berjalan</span>
                                        @endif
                                    </div>
                                @else
                                    <h6 class="mb-0 text-danger font-weight-bolder mt-1"><i class="fas fa-calendar-times me-1"></i> Belum Diatur</h6>
                                @endif
                            </div>
                            <button class="btn btn-sm btn-white border shadow-sm mb-0" data-bs-toggle="modal" data-bs-target="#modalLulus">Set Waktu</button>
                        </div>

                        {{-- Progress Bar Stacked --}}
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-xs font-weight-bold text-dark">Progres Eksekusi Seluruh Kelas</span>
                            <span class="text-xs font-weight-bold">{{ $totalSiswaLulusan > 0 ? round((($statLulus['lulus'] + $statLulus['gagal']) / $totalSiswaLulusan) * 100) : 0 }}%</span>
                        </div>
                        <div class="progress mb-4" style="height: 12px; background-color: rgba(255,255,255,0.5);">
                            <div class="progress-bar bg-success" style="width: {{ $totalSiswaLulusan > 0 ? ($statLulus['lulus']/$totalSiswaLulusan)*100 : 0 }}%"></div>
                            <div class="progress-bar bg-danger" style="width: {{ $totalSiswaLulusan > 0 ? ($statLulus['gagal']/$totalSiswaLulusan)*100 : 0 }}%"></div>
                        </div>

                        <div class="row text-center mb-4">
                            <div class="col-4 border-end border-white">
                                <h4 class="mb-0 font-weight-bolder">{{ $statLulus['lulus'] }}</h4>
                                <p class="text-xxs text-uppercase font-weight-bold text-secondary mb-0">Lulus</p>
                            </div>
                            <div class="col-4 border-end border-white">
                                <h4 class="mb-0 font-weight-bolder">{{ $statLulus['gagal'] }}</h4>
                                <p class="text-xxs text-uppercase font-weight-bold text-secondary mb-0">Gagal</p>
                            </div>
                            <div class="col-4">
                                <h4 class="mb-0 font-weight-bolder text-secondary opacity-7">{{ $statLulus['belum'] }}</h4>
                                <p class="text-xxs text-uppercase font-weight-bold text-secondary mb-0">Belum</p>
                            </div>
                        </div>

                        {{-- 👇 REVISI MONITORING BACA PENGUMUMAN (Lebih Merapat) 👇 --}}
                        <div class="bg-white border-radius-md p-3 mb-4 border border-warning shadow-sm">
                            <p class="text-xs font-weight-bold text-dark mb-2"><i class="fas fa-eye text-warning me-1"></i> Status Pantauan Siswa</p>
                            <div class="d-flex justify-content-start align-items-center gap-5">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape icon-sm bg-success text-white text-center rounded-circle me-3">
                                        <i class="fas fa-check-double" style="font-size: 0.6rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-sm font-weight-bolder">{{ $bacaLulus['sudah'] }} Siswa</h6>
                                        <p class="text-xs text-secondary mb-0">Sudah Membaca</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape icon-sm bg-secondary text-white text-center rounded-circle me-3">
                                        <i class="fas fa-envelope" style="font-size: 0.6rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-sm font-weight-bolder">{{ $bacaLulus['belum'] }} Siswa</h6>
                                        <p class="text-xs text-secondary mb-0">Belum Membaca</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('mutasi.kelulusan_dashboard.index', ['tahun_ajaran' => $ta]) }}" class="btn btn-warning text-white w-100 shadow-sm mb-0">
                            Kelola Detail per Kelas <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- SEKSI KENAIKAN (TINGKAT 10 & 11) --}}
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100" style="background-color: rgba(94, 114, 228, 0.08);">
                    <div class="card-header bg-gradient-primary p-4 border-radius-top-lg">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-white mb-0"><i class="fas fa-level-up-alt me-2"></i> Kenaikan Kelas</h5>
                            <span class="badge bg-white text-primary font-weight-bold">Tingkat Bawah</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        {{-- Jadwal Info --}}
                        <div class="bg-white border-radius-md p-3 mb-4 d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <p class="text-xxs text-secondary mb-1 font-weight-bold text-uppercase">Jadwal Rilis Pengumuman</p>
                                @if($jadwalNaik)
                                    <div class="d-flex align-items-center">
                                        <h6 class="mb-0 text-dark font-weight-bolder">
                                            {{ $jadwalNaik->waktu_buka->locale('id')->isoFormat('D MMMM Y, HH:mm') }} - {{ $jadwalNaik->waktu_tutup->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
                                        </h6>
                                        
                                        @if($jadwalNaik->waktu_tutup->isPast())
                                            <span class="badge bg-danger ms-2 px-2 py-1 shadow-sm" style="font-size: 0.6rem;"><i class="fas fa-lock me-1"></i> Sesi Berakhir</span>
                                        @elseif($jadwalNaik->waktu_buka->isFuture())
                                            <span class="badge bg-secondary ms-2 px-2 py-1 shadow-sm" style="font-size: 0.6rem;"><i class="fas fa-hourglass-start me-1"></i> Menunggu Waktu</span>
                                        @else
                                            <span class="badge bg-success ms-2 px-2 py-1 shadow-sm" style="font-size: 0.6rem;"><i class="fas fa-broadcast-tower me-1"></i> Sedang Berjalan</span>
                                        @endif
                                    </div>
                                @else
                                    <h6 class="mb-0 text-danger font-weight-bolder mt-1"><i class="fas fa-calendar-times me-1"></i> Belum Diatur</h6>
                                @endif
                            </div>
                            <button class="btn btn-sm btn-white border shadow-sm mb-0" data-bs-toggle="modal" data-bs-target="#modalNaik">Set Waktu</button>
                        </div>

                        {{-- Progress Bar Stacked --}}
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-xs font-weight-bold text-dark">Progres Eksekusi Seluruh Kelas</span>
                            <span class="text-xs font-weight-bold">{{ $totalSiswaKenaikan > 0 ? round((($statNaik['naik'] + $statNaik['tinggal']) / $totalSiswaKenaikan) * 100) : 0 }}%</span>
                        </div>
                        <div class="progress mb-4" style="height: 12px; background-color: rgba(255,255,255,0.5);">
                            <div class="progress-bar bg-primary" style="width: {{ $totalSiswaKenaikan > 0 ? ($statNaik['naik']/$totalSiswaKenaikan)*100 : 0 }}%"></div>
                            <div class="progress-bar bg-danger" style="width: {{ $totalSiswaKenaikan > 0 ? ($statNaik['tinggal']/$totalSiswaKenaikan)*100 : 0 }}%"></div>
                        </div>

                        <div class="row text-center mb-4">
                            <div class="col-4 border-end border-white">
                                <h4 class="mb-0 font-weight-bolder">{{ $statNaik['naik'] }}</h4>
                                <p class="text-xxs text-uppercase font-weight-bold text-secondary mb-0">Naik</p>
                            </div>
                            <div class="col-4 border-end border-white">
                                <h4 class="mb-0 font-weight-bolder">{{ $statNaik['tinggal'] }}</h4>
                                <p class="text-xxs text-uppercase font-weight-bold text-secondary mb-0">Tinggal</p>
                            </div>
                            <div class="col-4">
                                <h4 class="mb-0 font-weight-bolder text-secondary opacity-7">{{ $statNaik['belum'] }}</h4>
                                <p class="text-xxs text-uppercase font-weight-bold text-secondary mb-0">Belum</p>
                            </div>
                        </div>

                        {{-- 👇 REVISI MONITORING BACA PENGUMUMAN (Lebih Merapat) 👇 --}}
                        <div class="bg-white border-radius-md p-3 mb-4 border border-primary shadow-sm">
                            <p class="text-xs font-weight-bold text-dark mb-2"><i class="fas fa-eye text-primary me-1"></i> Status Pantauan Siswa</p>
                            <div class="d-flex justify-content-start align-items-center gap-5">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape icon-sm bg-success text-white text-center rounded-circle me-3">
                                        <i class="fas fa-check-double" style="font-size: 0.6rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-sm font-weight-bolder">{{ $bacaNaik['sudah'] }} Siswa</h6>
                                        <p class="text-xs text-secondary mb-0">Sudah Membaca</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape icon-sm bg-secondary text-white text-center rounded-circle me-3">
                                        <i class="fas fa-envelope" style="font-size: 0.6rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-sm font-weight-bolder">{{ $bacaNaik['belum'] }} Siswa</h6>
                                        <p class="text-xs text-secondary mb-0">Belum Membaca</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('mutasi.kenaikan_dashboard.index', ['tahun_ajaran' => $ta]) }}" class="btn btn-primary w-100 shadow-sm mb-0">
                            Kelola Detail per Kelas <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL SET JADWAL --}}
    @include('mutasi.pengumuman.partials.modal_jadwal', ['jenis' => 'kelulusan', 'id' => 'modalLulus', 'current' => $jadwalLulus])
    @include('mutasi.pengumuman.partials.modal_jadwal', ['jenis' => 'kenaikan', 'id' => 'modalNaik', 'current' => $jadwalNaik])

    <x-app.footer />
</main>
@endsection