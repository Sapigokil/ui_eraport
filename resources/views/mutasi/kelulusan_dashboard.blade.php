@extends('layouts.app') 

@section('page-title', 'Dashboard Proses Kelulusan')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- 👇 PERBAIKAN: HEADER BANNER KHUSUS KELULUSAN (ORANGE/WARNING) 👇 --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-warning overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                        <i class="fas fa-graduation-cap text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="row align-items-center text-white">
                            <div class="col-md-7">
                                <h3 class="text-white font-weight-bold mb-1">Dashboard Proses Kelulusan</h3>
                                <p class="text-white opacity-8 mb-0">
                                    <i class="fas fa-info-circle me-1"></i> Pastikan seluruh nilai rapor genap kelas tingkat akhir telah difinalisasi sebelum mengeksekusi kelulusan.
                                </p>
                            </div>
                            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                <form action="{{ route('mutasi.kelulusan_dashboard.index') }}" method="GET" class="d-inline-block">
                                    <div class="input-group input-group-sm bg-white border-radius-md overflow-hidden shadow-sm" style="max-width: 250px; float: right;">
                                        <span class="input-group-text border-0 bg-transparent text-dark"><i class="fas fa-calendar-alt"></i></span>
                                        <select name="tahun_ajaran" class="form-control border-0 ps-0 text-dark font-weight-bold cursor-pointer" onchange="this.form.submit()">
                                            @foreach($listTA as $ta)
                                                <option value="{{ $ta }}" {{ $taLama == $ta ? 'selected' : '' }}>Tahun Ajaran {{ $ta }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CUSTOM CSS UNTUK EFEK KARTU MODERN & KACA TIPIS --}}
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
            .card-shape { position: absolute; background: rgba(255, 255, 255, 0.12); border-radius: 50%; z-index: 0; }
            .shape-1 { width: 120px; height: 120px; top: -30px; right: -30px; }
            .shape-2 { width: 150px; height: 150px; bottom: -60px; left: -50px; }
            .card-content { position: relative; z-index: 1; }
            
            /* Efek Kaca Sangat Tipis (Ultra-thin Glassmorphism) */
            .glass-box {
                background-color: rgba(255, 255, 255, 0.35); 
                backdrop-filter: blur(10px); 
                -webkit-backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.4);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            }
        </style>

        {{-- LOOPING PER GRUP TINGKAT --}}
        @foreach($groupedData as $jurusan => $kelasGroup)
            
            {{-- 👇 PERBAIKAN: Ikon Layer juga diubah menjadi warning (oranye) 👇 --}}
            <div class="d-flex align-items-center mb-3 mt-4 pt-2">
                <h5 class="mb-0 text-dark font-weight-bold">
                    <i class="fas fa-layer-group me-2 text-warning"></i> Program Keahlian: {{ $jurusan }}
                </h5>
                <span class="badge bg-dark ms-3 shadow-sm">Fase Kelulusan</span>
                <div class="ms-3 flex-grow-1 border-top border-2" style="border-color: #e9ecef !important;"></div>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-3 mb-4">
                @foreach($kelasGroup as $kelas)
                <div class="col">
                    <div class="card h-100 modern-card" style="background: {{ $kelas->bg_gradient }};">
                        
                        <div class="card-shape shape-1"></div>
                        <div class="card-shape shape-2"></div>

                        <div class="card-body d-flex flex-column card-content p-3 text-white">
                            
                            {{-- Header Card --}}
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    {{-- 👇 PERBAIKAN: Ikon Topi Toga juga diubah menjadi warning (oranye) 👇 --}}
                                    <div class="bg-white text-dark text-center rounded-circle shadow-sm me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                        <i class="fas fa-user-graduate text-xs opacity-8 text-warning"></i>
                                    </div>
                                    <h6 class="text-white font-weight-bold mb-0 text-sm">{{ $kelas->nama_kelas }}</h6>
                                </div>
                            </div>

                            <div class="mb-2 border-bottom pb-2" style="border-color: rgba(255,255,255,0.2) !important;">
                                <span class="text-xs text-white opacity-8 text-truncate d-block" title="Wali: {{ $kelas->wali_kelas ?? 'Belum Diatur' }}">
                                    Wali: {{ $kelas->wali_kelas ?? 'Tanpa Wali' }}
                                </span>
                            </div>

                            {{-- 👇 RINCIAN STATISTIK BARU (KACA SANGAT TIPIS) 👇 --}}
                            <div class="glass-box rounded p-3 mt-2 mb-3">
                                
                                {{-- Baris 1: Total Siswa --}}
                                <div class="text-center border-bottom pb-2 mb-2" style="border-color: rgba(0,0,0,0.1) !important;">
                                    <h2 class="font-weight-bolder mb-0 text-dark" style="font-size: 1.8rem; line-height: 1;">
                                        {{ $kelas->siswa_aktif }}
                                    </h2>
                                    <span class="text-xs font-weight-bold text-secondary">Total Siswa</span>
                                </div>

                                {{-- Baris 2: Lulus & Tidak Lulus --}}
                                <div class="d-flex justify-content-between text-center border-bottom pb-2 mb-2" style="border-color: rgba(0,0,0,0.1) !important;">
                                    <div class="flex-fill border-end pe-2" style="border-color: rgba(0,0,0,0.1) !important;">
                                        {{-- 👇 PERBAIKAN: Warna Lulus diubah menjadi Sukses (Hijau) agar lebih intuitif 👇 --}}
                                        <h2 class="font-weight-bolder mb-0 text-success" style="font-size: 1.8rem; line-height: 1;">
                                            {{ $kelas->stat_lulus }}
                                        </h2>
                                        <span class="text-xs font-weight-bold text-secondary">Lulus</span>
                                    </div>
                                    <div class="flex-fill ps-2">
                                        <h2 class="font-weight-bolder mb-0 text-danger" style="font-size: 1.8rem; line-height: 1;">
                                            {{ $kelas->stat_tinggal }}
                                        </h2>
                                        <span class="text-xs font-weight-bold text-secondary">Tidak Lulus</span>
                                    </div>
                                </div>

                                {{-- Baris 3: Belum Proses --}}
                                <div class="d-flex justify-content-between align-items-center text-sm pt-1">
                                    <span class="text-secondary font-weight-bold"><i class="fas fa-hourglass-half me-1"></i> Belum Proses</span>
                                    <span class="font-weight-bolder text-dark">{{ $kelas->stat_belum }}</span>
                                </div>

                            </div>

                            {{-- Info Gatekeeper Rapor (Penting) --}}
                            @if($kelas->siswa_aktif > 0)
                                <div class="d-flex justify-content-between align-items-center pt-1 pb-3 text-white mt-auto" style="font-size: 0.75rem;">
                                    <span class="opacity-8"><i class="fas fa-file-invoice me-1"></i> Kesiapan Rapor</span>
                                    <span class="font-weight-bold">{{ $kelas->rapor_cetak }}/{{ $kelas->siswa_aktif }}</span>
                                </div>
                            @endif

                            {{-- AREA TOMBOL --}}
                            <div>
                                @if(!$kelas->rapor_aman)
                                    <div class="text-center bg-white text-danger font-weight-bold py-2 px-1 rounded text-xs shadow-sm" style="opacity: 0.9;" data-bs-toggle="tooltip" title="Ada {{ $kelas->siswa_aktif - $kelas->rapor_cetak }} siswa belum finalisasi rapor genap!">
                                        <i class="fas fa-ban me-1"></i> LENGKAPI RAPOR DULU
                                    </div>
                                @else
                                    <form action="{{ route('mutasi.kelulusan.index') }}" method="GET">
                                        <input type="hidden" name="id_kelas_asal" value="{{ $kelas->id_kelas }}">
                                        <input type="hidden" name="tahun_ajaran_lama" value="{{ $taLama }}">
                                        
                                        @if($kelas->sudah_proses > 0)
                                            <button type="submit" class="btn btn-white text-dark w-100 mb-0 shadow-sm py-2 px-1 text-xs font-weight-bold">
                                                <i class="fas fa-edit me-1"></i> REVISI DATA
                                            </button>
                                        @else
                                            {{-- 👇 PERBAIKAN: Tombol proses diubah teksnya menjadi warning (oranye) 👇 --}}
                                            <button type="submit" class="btn btn-white text-warning w-100 mb-0 shadow-sm py-2 px-1 text-xs font-weight-bold">
                                                <i class="fas fa-play-circle me-1"></i> MULAI PROSES
                                            </button>
                                        @endif
                                    </form>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection