@extends('layouts.app') 

@section('page-title', 'Dashboard Proses Akhir Tahun')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- HEADER BANNER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-dark overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                        <i class="fas fa-sitemap text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="row align-items-center text-white">
                            <div class="col-md-8">
                                <h3 class="text-white font-weight-bold mb-1">Dashboard Mutasi Akhir Tahun</h3>
                                <p class="text-white opacity-8 mb-0">
                                    <i class="fas fa-info-circle me-1"></i> Tahun Ajaran: <b>{{ $taLama }}</b>. Silakan proses dari tingkat tertinggi (Kelulusan) hingga tingkat terendah (Kenaikan Kelas).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CUSTOM CSS UNTUK EFEK KARTU MODERN --}}
        <style>
            .modern-card {
                border-radius: 1rem !important; 
                border: none;
                position: relative;
                overflow: hidden;
                box-shadow: 0 8px 15px -5px rgba(0,0,0,0.1) !important;
                transition: transform 0.2s ease-in-out, box-shadow 0.2s;
            }
            .modern-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 20px -5px rgba(0,0,0,0.15) !important;
            }
            
            .card-shape {
                position: absolute;
                background: rgba(255, 255, 255, 0.12);
                border-radius: 50%;
                z-index: 0;
            }
            .shape-1 { width: 120px; height: 120px; top: -30px; right: -30px; }
            .shape-2 { width: 150px; height: 150px; bottom: -60px; left: -50px; }
            
            .card-content {
                position: relative;
                z-index: 1;
            }
        </style>

        {{-- LOOPING PER GRUP TINGKAT --}}
        @foreach($groupedData as $tingkat => $kelasGroup)
            
            {{-- Header Label Untuk Setiap Tingkat (Pemisah Baris) --}}
            <div class="d-flex align-items-center mb-3 mt-4 pt-2">
                <h5 class="mb-0 text-dark font-weight-bold">
                    <i class="fas fa-layer-group me-2 text-primary"></i> Tingkat {{ $tingkat }}
                </h5>
                @if($tingkat == $maxTingkat) 
                    <span class="badge bg-dark ms-3 shadow-sm">Fase Kelulusan</span>
                @else
                    <span class="badge bg-secondary ms-3 shadow-sm">Fase Kenaikan</span>
                @endif
                {{-- Garis Lurus (Divider) memanjang ke kanan --}}
                <div class="ms-3 flex-grow-1 border-top border-2" style="border-color: #e9ecef !important;"></div>
            </div>

            {{-- GRID CARD KELAS (6 Kolom per baris di layar besar) --}}
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-3 mb-4">
                @foreach($kelasGroup as $kelas)
                <div class="col">
                    <div class="card h-100 modern-card" style="background: {{ $kelas->bg_gradient }};">
                        
                        {{-- Elemen Dekorasi --}}
                        <div class="card-shape shape-1"></div>
                        <div class="card-shape shape-2"></div>

                        <div class="card-body d-flex flex-column card-content p-3 text-white">
                            
                            {{-- Header Card --}}
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white text-dark text-center rounded-circle shadow-sm me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                        <i class="fas {{ $kelas->tingkat == $maxTingkat ? 'fa-user-graduate' : 'fa-chalkboard-teacher' }} text-xs opacity-8"></i>
                                    </div>
                                    <h6 class="text-white font-weight-bold mb-0 text-sm">{{ $kelas->nama_kelas }}</h6>
                                </div>
                            </div>

                            {{-- Nama Wali --}}
                            <div class="mb-3">
                                <span class="text-xs text-white opacity-8 text-truncate d-block" title="Wali: {{ $kelas->wali_kelas ?? 'Belum Diatur' }}">
                                    {{ $kelas->wali_kelas ?? 'Tanpa Wali' }}
                                </span>
                            </div>

                            {{-- Info Utama --}}
                            <div class="text-center mb-3">
                                <h2 class="text-white font-weight-bolder mb-0" style="font-size: 1.8rem; line-height: 1;">
                                    {{ $kelas->siswa_aktif }}
                                </h2>
                                <p class="text-white opacity-8 mb-0" style="font-size: 0.7rem;">Siswa Aktif</p>
                            </div>

                            {{-- Info Rapor --}}
                            @if($kelas->siswa_aktif > 0)
                                <div class="d-flex justify-content-between align-items-center pt-2 mb-3 border-top" style="border-color: rgba(255,255,255,0.2) !important; font-size: 0.75rem;">
                                    <span class="opacity-8"><i class="fas fa-print me-1"></i> Rapor</span>
                                    <span class="font-weight-bold">{{ $kelas->rapor_cetak }}/{{ $kelas->siswa_aktif }}</span>
                                </div>
                            @else
                                <div class="text-center font-weight-bold pt-2 mb-3 border-top opacity-8" style="border-color: rgba(255,255,255,0.2) !important; font-size: 0.75rem;">
                                    <i class="fas fa-check-circle me-1"></i> Kosong
                                </div>
                            @endif

                            {{-- AREA TOMBOL & GATEKEEPER --}}
                            <div class="mt-auto">
                                @if($kelas->is_selesai)
                                    <button class="btn btn-white w-100 mb-0 disabled shadow-sm text-success text-xs py-2 px-1 font-weight-bold" style="opacity: 0.9;">
                                        <i class="fas fa-check-double me-1"></i> SELESAI
                                    </button>
                                @elseif($kelas->is_terkunci)
                                    <div class="text-center bg-white text-danger font-weight-bold py-2 px-1 rounded text-xs shadow-sm" style="opacity: 0.9; cursor: help;" data-bs-toggle="tooltip" title="{{ $kelas->pesan_kunci }}">
                                        <i class="fas fa-lock me-1"></i> TERKUNCI
                                    </div>
                                @elseif(!$kelas->rapor_aman)
                                    <div class="text-center bg-white text-danger font-weight-bold py-2 px-1 rounded text-xs shadow-sm" style="opacity: 0.9;" data-bs-toggle="tooltip" title="Ada {{ $kelas->siswa_aktif - $kelas->rapor_cetak }} siswa belum cetak rapor!">
                                        <i class="fas fa-ban me-1"></i> -{{ $kelas->siswa_aktif - $kelas->rapor_cetak }} RAPOR
                                    </div>
                                @else
                                    {{-- JIKA AMAN, TAMPILKAN TOMBOL PROSES --}}
                                    @if($kelas->tingkat == $maxTingkat)
                                        <form action="{{ route('mutasi.kelulusan.index') }}" method="GET">
                                            <input type="hidden" name="id_kelas_asal" value="{{ $kelas->id_kelas }}">
                                            <button type="submit" class="btn btn-white text-dark w-100 mb-0 shadow-sm py-2 px-1 text-xs font-weight-bold">
                                                <i class="fas fa-user-graduate me-1"></i> KELULUSAN
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('mutasi.kenaikan.index') }}" method="GET">
                                            <input type="hidden" name="id_kelas_asal" value="{{ $kelas->id_kelas }}">
                                            <button type="submit" class="btn btn-white text-primary w-100 mb-0 shadow-sm py-2 px-1 text-xs font-weight-bold">
                                                <i class="fas fa-share-square me-1"></i> KENAIKAN
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        @endforeach

    </div>
    <x-app.footer />
</main>
@endsection