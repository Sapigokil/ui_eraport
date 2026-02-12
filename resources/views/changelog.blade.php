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
                            
                            @forelse($history['changelog'] as $index => $log)
                                <div class="timeline-block mb-3">
                                    <span class="timeline-step">
                                        @if($index == 0)
                                            {{-- Versi Terbaru Icon Beda --}}
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
                                                    <span class="badge badge-sm bg-gradient-warning ms-2">Terbaru</span>
                                                @endif
                                            </h6>
                                            <span class="text-secondary text-xs font-weight-bold">
                                                <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($log['date'])->translatedFormat('d F Y') }}
                                            </span>
                                        </div>
                                        
                                        <div class="bg-gray-100 border-radius-lg p-3 border">
                                            <ul class="list-unstyled mb-0">
                                                @foreach($log['notes'] as $note)
                                                    <li class="d-flex align-items-start text-sm text-secondary mb-1">
                                                        <i class="fas fa-angle-right text-xs mt-1 me-2 text-dark"></i>
                                                        <span style="line-height: 1.5;">{{ $note }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
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
    .timeline-content {
        position: relative;
    }
</style>
@endsection