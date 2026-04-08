@extends('layouts.app') 

@section('page-title', 'Laporan PSTS')

@section('content')

{{-- CSS Custom untuk Colored Compact Card --}}
<style>
    /* Tema Warna Ganjil: Indigo / Soft Blue */
    .bg-ganjil {
        background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
    }
    
    /* Tema Warna Genap: Teal / Emerald Green */
    .bg-genap {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .card-psts-colored {
        border: none;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border-radius: 14px;
        overflow: hidden;
    }
    
    /* Efek Hover Melayang dengan Shadow Halus */
    .card-psts-colored:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px rgba(0,0,0,0.15) !important;
    }

    /* Ikon Transparan Super Besar di Kanan */
    .watermark-mini {
        position: absolute;
        right: -15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 6rem;
        opacity: 0.15; /* Transparansi agar tidak menutupi teks */
        pointer-events: none;
    }

    /* Box Ikon di Kiri */
    .icon-shape-custom {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.25); /* Putih Transparan */
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1.25rem;
        backdrop-filter: blur(5px);
    }

    /* Tombol Putih Dinamis */
    .btn-white-custom {
        background-color: #ffffff;
        transition: all 0.2s ease;
        font-weight: 700;
    }
    .btn-white-custom:hover {
        background-color: #f8f9fa;
        transform: scale(1.05);
    }
</style>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- BANNER HEADER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-primary overflow-hidden position-relative" style="border-radius: 14px;">
                    <div class="position-absolute top-0 end-0 opacity-2 pe-3 pt-2">
                        <i class="fas fa-chart-pie text-white" style="font-size: 6rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1 d-flex flex-column justify-content-center">
                        <h4 class="text-white font-weight-bold mb-1">Laporan PSTS</h4>
                        <p class="text-white opacity-8 mb-0 text-sm">Riwayat capaian nilai sumatif tengah semester Anda.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- LOOPING GROUP TAHUN AJARAN --}}
        @forelse($grouped_psts as $tahun => $riwayats)
            
            {{-- Header Timeline Tahun Ajaran --}}
            <div class="row mt-2 mb-3">
                <div class="col-12">
                    <div class="d-flex align-items-center border-bottom pb-2">
                        <div class="icon icon-shape icon-sm bg-gradient-dark shadow-sm text-center border-radius-md me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-calendar-check text-white text-xs"></i>
                        </div>
                        <h6 class="text-dark font-weight-bolder mb-0">Tahun Ajaran {{ $tahun }}</h6>
                    </div>
                </div>
            </div>

            {{-- Container Card (Max 2 Card Per Baris) --}}
            <div class="row mb-3">
                @foreach($riwayats as $riwayat)
                    @php
                        // Menentukan Tema berdasarkan Ganjil / Genap
                        $isGanjil = $riwayat->semester == 1;
                        $bgClass = $isGanjil ? 'bg-ganjil' : 'bg-genap';
                        $btnTextColor = $isGanjil ? '#4e54c8' : '#11998e'; // Warna teks tombol menyesuaikan tema card
                        $watermarkIcon = $isGanjil ? 'fa-sun' : 'fa-leaf';
                    @endphp

                    <div class="col-md-6 mb-3">
                        {{-- CARD COMPACT COLORED --}}
                        <div class="card card-psts-colored h-100 position-relative shadow-sm {{ $bgClass }}">
                            
                            {{-- Watermark Ikon Latar Belakang --}}
                            <i class="fas {{ $watermarkIcon }} text-white watermark-mini"></i>

                            <div class="card-body p-3 d-flex align-items-center">
                                
                                {{-- Ikon Kiri (Kaca Transparan) --}}
                                <div class="icon-shape-custom me-3 z-index-1">
                                    <i class="fas fa-file-invoice"></i>
                                </div>

                                {{-- Teks Info di Tengah (Teks Putih) --}}
                                <div class="flex-grow-1 z-index-1">
                                    <h6 class="mb-0 text-white font-weight-bold" style="letter-spacing: 0.5px;">Semester {{ $isGanjil ? 'Ganjil' : 'Genap' }}</h6>
                                    <p class="text-xs text-white opacity-8 mb-0">Kelas: <span class="font-weight-bold">{{ $riwayat->nama_kelas }}</span></p>
                                </div>

                                {{-- Tombol Aksi di Kanan (Putih Bersih) --}}
                                <div class="z-index-1">
                                    {{-- ✅ PERBAIKAN: Rute diperbaiki menjadi sis.psts.detail dan mengubah garis miring menjadi strip --}}
                                    <a href="{{ route('sis.psts.detail', [
                                        'tahun_ajaran' => str_replace('/', '-', $riwayat->tahun_ajaran), 
                                        'semester' => $riwayat->semester,
                                        'id_kelas' => $riwayat->id_kelas
                                    ]) }}" class="btn btn-sm btn-white-custom mb-0 px-3 py-2 shadow-sm" style="color: {{ $btnTextColor }}; border-radius: 8px;">
                                        Rincian <i class="fas fa-chevron-right ms-1 text-xxs"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        @empty
            {{-- KONDISI JIKA DATA KOSONG --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="text-center py-5 bg-light border border-light rounded-3 shadow-none">
                        <div class="icon icon-shape bg-white shadow-sm text-center border-radius-circle mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <i class="fas fa-folder-open fa-2x text-secondary opacity-6"></i>
                        </div>
                        <h6 class="text-dark font-weight-bold">Belum Ada Riwayat Penilaian</h6>
                        <p class="text-sm text-secondary px-md-5 mx-md-5 mb-0">Data Laporan PSTS Anda belum tersedia. Hal ini biasanya terjadi jika Anda baru saja terdaftar atau Bapak/Ibu Guru belum mempublikasikan nilai sumatif ke dalam sistem.</p>
                    </div>
                </div>
            </div>
        @endforelse

    </div>
    <x-app.footer />
</main>
@endsection