@extends('layouts.app')

@section('page-title', 'ChangeLog Aplikasi')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- 1. HEADER UTAMA --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            {{-- Dekorasi Icon Besar --}}
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-code-branch text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-history me-2"></i> Riwayat Pembaruan (ChangeLog)
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Versi Saat Ini: <span class="font-weight-bold text-warning">v{{ $history['current_version'] ?? '1.0.0' }}</span> 
                                        | Terakhir Update: {{ $history['last_updated'] ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-5">
                        
                        {{-- TIMELINE STYLE --}}
                        <div class="timeline timeline-one-side mt-4" data-timeline-axis-style="dashed">
                            
                            @php
                                // Hitung total riwayat untuk penanda accordion
                                $totalLogs = count($history['changelog'] ?? []);
                            @endphp

                            @forelse($history['changelog'] as $index => $log)
                                
                                {{-- 👇 Mulai Accordion untuk Log ke-11 dan seterusnya 👇 --}}
                                @if($index == 10)
                                    <div class="accordion mt-4 w-100" id="accordionOlderLogs">
                                        <div class="accordion-item border rounded shadow-sm">
                                            <h2 class="accordion-header" id="headingOlderLogs">
                                                <button class="accordion-button collapsed font-weight-bold text-secondary text-sm px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOlderLogs" aria-expanded="false" aria-controls="collapseOlderLogs">
                                                    <i class="fas fa-archive text-warning me-2"></i> Tampilkan Versi Lama ({{ $totalLogs - 10 }} Pembaruan Sebelumnya)
                                                </button>
                                            </h2>
                                            <div id="collapseOlderLogs" class="accordion-collapse collapse" aria-labelledby="headingOlderLogs" data-bs-parent="#accordionOlderLogs">
                                                <div class="accordion-body bg-light pt-4 pb-2 px-4 border-top">
                                @endif

                                <div class="timeline-block mb-4">
                                    <span class="timeline-step shadow-sm">
                                        @if($index == 0)
                                            <i class="fas fa-star text-warning text-gradient"></i>
                                        @else
                                            <i class="fas fa-check-circle text-success text-gradient"></i>
                                        @endif
                                    </span>
                                    
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="text-dark text-sm font-weight-bold mb-0">
                                                Versi {{ $log['version'] }}
                                                @if($index == 0)
                                                    <span class="badge badge-sm bg-gradient-warning ms-2 shadow-sm">Terbaru</span>
                                                @endif
                                            </h6>
                                            <span class="text-secondary text-xs font-weight-bold">
                                                <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($log['date'])->translatedFormat('d F Y') }}
                                            </span>
                                        </div>
                                        
                                        <div class="bg-gray-100 border-radius-lg p-3 border shadow-sm">
                                            <ul class="list-unstyled mb-0">
                                                @foreach($log['notes'] as $note)
                                                    <li class="d-flex align-items-start text-sm text-secondary mb-1">
                                                        <i class="fas fa-angle-right text-xs mt-1 me-2 text-dark"></i>
                                                        <span style="line-height: 1.5; text-align: justify;">{{ $note }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- 👇 Tutup div Accordion jika iterasi telah mencapai log terakhir 👇 --}}
                                @if($loop->last && $totalLogs > 10)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            @empty
                                <div class="text-center py-5">
                                    <p class="text-secondary">Belum ada riwayat pembaruan.</p>
                                </div>
                            @endforelse

                        </div>
                        {{-- END TIMELINE --}}

                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

<style>
    /* Styling Tambahan untuk Timeline Vertikal */
    .timeline {
        position: relative;
        padding-left: 3rem;
        width: 100%; /* Pastikan container utama penuh */
    }
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        left: 1rem; /* Posisi Garis Vertikal */
        height: 100%;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-block {
        position: relative;
        margin-bottom: 2rem;
        display: block; /* Hindari perilaku flex yang menyusut */
        width: 95%; /* Paksa lebar 100% */
    }
    .timeline-step {
        position: absolute;
        left: -2.8rem; /* Posisi Icon Bulat */
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #e9ecef;
        text-align: center;
        line-height: 26px;
        z-index: 1;
    }
    
    /* 👇 INI KUNCI UTAMANYA: Mengalahkan CSS Bawaan Template 👇 */
    .timeline-content {
        position: relative;
        width: 100% !important; 
        max-width: 100% !important; /* Membunuh max-width bawaan template */
        padding-left: 1rem;
    }
    
    /* Kustomisasi Accordion khusus timeline */
    #accordionOlderLogs .accordion-button:not(.collapsed) {
        color: #344767;
        background-color: #f8f9fa;
        box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.125);
    }
</style>
@endsection