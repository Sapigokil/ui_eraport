@extends('layouts.app')

@section('title', 'Dashboard Siswa E-Rapor')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

    <x-app.navbar />

    <div class="container-fluid py-4 px-5">

        {{-- HEADER --}}
        <h3 class="mb-3">Hello, {{ $siswa->nama_siswa ?? auth()->user()->name ?? 'Siswa' }} 👋</h3>

        <div class="row">
            {{-- KOLOM KIRI (Info Season & Identitas) --}}
            <div class="col-md-6 d-flex flex-column gap-3 mb-4">
                
                {{-- INFO SEASON (TETAP / TIDAK BISA DITUTUP) --}}
                @if($activeSeason)
                <div style="
                    background-color: #E8F9FF; 
                    border-left: 4px solid #77BEF0;
                    padding: 14px 18px;
                    border-radius: 6px;
                    font-size: 13px;
                ">
                    <h6 style="margin-bottom:6px; font-weight:600;">
                        <i class="fas fa-calendar-alt text-info me-1"></i> Status Tahun Ajaran Akademik
                    </h6>

                    <div style="line-height:1.5; color:#333;">
                        <strong>Semester:</strong>
                        {{ $activeSeason->semester == 1 ? 'Ganjil' : 'Genap' }} <br>

                        <strong>Tahun Ajaran:</strong>
                        {{ $activeSeason->tahun_ajaran }} <br>

                        <strong>Status Portal:</strong>
                        @if($activeSeason->is_open)
                            <span style="
                                background:#93DA97;
                                color:#fff;
                                padding:2px 8px;
                                border-radius:12px;
                                font-size:11px;
                            ">
                                AKTIF
                            </span>
                        @else
                            <span style="
                                background:#E57373;
                                color:#fff;
                                padding:2px 8px;
                                border-radius:12px;
                                font-size:11px;
                            ">
                                TERKUNCI DARI ADMIN
                            </span>
                        @endif
                    </div>
                </div>
                @else
                <div style="
                    background-color: #FFF3CD;
                    border-left: 5px solid #FFC107;
                    padding: 14px 18px;
                    border-radius: 6px;
                    font-size: 13px;
                ">
                    <i class="fas fa-exclamation-triangle text-warning me-1"></i> Tahun Ajaran saat ini belum diset oleh Admin Sekolah.
                </div>
                @endif

                {{-- CARD IDENTITAS SISWA --}}
                <div style="
                    background-color: #E8F5E9;
                    border-left: 4px solid #93DA97;
                    padding: 14px 18px;
                    border-radius: 6px;
                    font-size: 13px;
                ">
                    <h6 style="margin-bottom:6px; font-weight:600;">
                        <i class="fas fa-id-card text-success me-1"></i> Identitas Siswa
                    </h6>
                    <div style="line-height:1.5; color:#333;">
                        <table style="width: 100%; border:none;">
                            <tr>
                                <td style="width: 100px;"><strong>NISN</strong></td>
                                <td style="width: 10px;">:</td>
                                <td>{{ $siswa->nisn ?? 'Belum terdata' }}</td>
                            </tr>
                            <tr>
                                <td><strong>NIPD</strong></td>
                                <td>:</td>
                                <td>{{ $siswa->nipd ?? 'Belum terdata' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kelas</strong></td>
                                <td>:</td>
                                <td><span class="font-weight-bold text-dark">{{ $siswa->kelas->nama_kelas ?? 'Belum ada kelas' }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- INFO PRAKERIN (Opsional jika diperlukan) --}}
                <div style="
                    background-color: #FFDEE6; 
                    border-left: 5px solid #EA5B6F; 
                    padding: 14px 18px;
                    border-radius: 6px;
                    font-size: 13px;
                ">
                    <h6 style="margin-bottom:6px; font-weight:600;">
                        <i class="fas fa-briefcase text-danger me-1"></i> Ruang Praktik (TBA)
                    </h6>
                    <div style="line-height:1.5; color:#555;">
                        Fitur pantauan kehadiran dan capaian nilai rapor sementara akan segera hadir di area ini.
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN (Papan Pengumuman) --}}
            <div class="col-md-6">
                <h5 class="text-sm font-weight-bold mb-3">Papan Pengumuman Sekolah</h5>

                @forelse($pengumuman as $event)
                <div class="notification-card" style="
                    background-color: #FFFFE0;
                    border-left: 4px solid #FFCB61;
                    padding: 12px 16px;
                    margin-bottom: 12px;
                    border-radius: 6px;
                    font-size: 13px;
                ">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                        <h6 style="
                            margin-bottom: 0;
                            font-size: 14px;
                            font-weight: 600;
                        ">
                            <i class="fas fa-bullhorn text-warning me-1"></i> {{ $event->judul ?? 'Informasi Sekolah' }}
                        </h6>
                        @if(!empty($event->kategori))
                            <span class="badge {{ $event->kategori === 'Pengumuman' ? 'bg-warning text-dark' : 'bg-info text-white' }}" style="font-size: 10px;">
                                {{ $event->kategori }}
                            </span>
                        @endif
                    </div>

                    <div style="
                        margin-bottom: 6px;
                        line-height: 1.4;
                        color: #333;
                        text-align: justify;
                    ">
                        {!! nl2br(e($event->deskripsi)) !!}
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2" style="
                        font-size: 11px;
                        color: #555;
                    ">
                        <span>
                            <i class="far fa-calendar-alt me-1"></i>
                            {{ \Carbon\Carbon::parse($event->tanggal)->locale('id')->translatedFormat('l, d F Y') }}
                            @if(!empty($event->tanggal_selesai) && $event->tanggal_selesai != $event->tanggal)
                                s.d. {{ \Carbon\Carbon::parse($event->tanggal_selesai)->locale('id')->translatedFormat('l, d F Y') }}
                            @endif
                        </span>

                        @if(!empty($event->lampiran))
                            <a href="{{ asset('storage/' . $event->lampiran) }}" target="_blank" class="btn btn-xs btn-outline-dark mb-0 py-1 px-2" style="font-size: 11px;">
                                <i class="fas fa-paperclip me-1"></i> Lihat Lampiran
                            </a>
                        @endif
                    </div>
                </div>
                @empty
                <p style="font-size: 13px; color: #555; font-style: italic;">Saat ini belum ada pengumuman atau kegiatan dari sekolah.</p>
                @endforelse
            </div>
        </div>

    </div>

    <x-app.footer />
</main>
@endsection