@extends('layouts.app')

@section('page-title', 'Pengaturan Background Portal SBA')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <style>
        .bg-gradient-danger {
            background-image: linear-gradient(310deg, #ea0606 0%, #ff667c 100%) !important;
            color: #fff !important;
        }
        .bg-gradient-success {
            background-image: linear-gradient(310deg, #17ad37 0%, #98ec2d 100%) !important;
            color: #fff !important;
        }
        .btn-check:checked + .btn-outline-primary,
        .btn-check:active + .btn-outline-primary,
        .btn-outline-primary.active {
            background-color: #6f42c1 !important;
            border-color: #6f42c1 !important;
            color: #ffffff !important;
        }
        .btn-outline-primary {
            border-color: #6f42c1 !important;
            color: #6f42c1 !important;
        }
        .btn-outline-primary:hover {
            background-color: #f3ebff !important;
            color: #4c1d95 !important;
        }
        .source-tab-btn {
            border: 2px solid #e2e8f0;
            background: #ffffff;
            color: #344767;
            border-radius: 10px;
            padding: 12px 14px;
            text-align: left;
            transition: all 0.2s ease;
            cursor: pointer;
            height: 100%;
        }
        .source-tab-btn:hover {
            border-color: #6f42c1;
            background: #faf7ff;
        }
        .source-tab-btn.active {
            border-color: #6f42c1;
            background: #f3ebff;
            color: #4c1d95;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.14);
        }
        .thumb-grid-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 3px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #e2e8f0;
            aspect-ratio: 16 / 9;
        }
        .thumb-grid-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .thumb-grid-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }
        .thumb-grid-item.selected {
            border-color: #6f42c1;
            box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.28);
        }
        .thumb-grid-item .thumb-badge-check {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #6f42c1;
            color: #fff;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }
        .thumb-grid-item.selected .thumb-badge-check {
            display: flex;
        }
        .thumb-grid-item .thumb-caption {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 4px 8px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.78), transparent);
            color: #fff;
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .soft-swatch {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 2px solid #cbd5e1;
            cursor: pointer;
            transition: transform 0.15s ease;
        }
        .soft-swatch:hover {
            transform: scale(1.1);
            border-color: #6f42c1;
        }
        .portal-live-stage {
            position: relative;
            width: 100%;
            min-height: 360px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            background-size: cover;
            background-position: center center;
            transition: background 0.3s ease;
        }
        .portal-live-overlay {
            position: absolute;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1;
            transition: background-color 0.2s ease;
        }
        .portal-live-mock-card {
            position: relative;
            z-index: 2;
            width: 235px;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.28);
            overflow: hidden;
        }
        .portal-live-mock-header {
            background: linear-gradient(135deg, #6f42c1 0%, #512da8 100%);
            color: #fff;
            text-align: center;
            padding: 12px 10px;
        }
    </style>

    <div class="container-fluid py-4 px-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">

                    {{-- HEADER UTAMA BANNER --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-image text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-cogs me-2"></i> Pengaturan Background Portal SBA
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Kustomisasi latar belakang halaman masuk peserta asesmen beserta warna failover otomatis.
                                    </p>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('sba.portal') }}" target="_blank" class="btn btn-outline-white text-white mb-0 shadow-none">
                                        <i class="fas fa-external-link-alt me-1"></i> Lihat Portal
                                    </a>
                                    <button type="submit" form="form-bg-portal" class="btn btn-white text-primary fw-bold mb-0 shadow-sm">
                                        <i class="fas fa-save me-1"></i> Simpan Pengaturan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4 mt-3">

                        {{-- ALERT SYSTEM --}}
                        @if (session('success'))
                            <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                <span class="text-sm"><strong>Sukses!</strong> {{ session('success') }}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                <div class="text-sm">
                                    <strong>Gagal Menyimpan Pengaturan!</strong>
                                    <ul class="mb-0 ps-3 mt-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        <form action="{{ route('sba.settings.background_portal.update') }}" method="POST" enctype="multipart/form-data" id="form-bg-portal">
                            @csrf
                            <input type="hidden" name="source_type" id="input_source_type" value="{{ old('source_type', $bgConfig['source_type']) }}">
                            <input type="hidden" name="selected_url" id="input_selected_url" value="{{ old('selected_url', $bgConfig['image_url']) }}">

                            <div class="row g-4">
                                {{-- KOLOM KIRI: PEMILIHAN 5 SUMBER BACKGROUND & KONFIGURASI --}}
                                <div class="col-lg-7">
                                    <div class="border rounded-3 p-3 p-md-4 bg-white h-100">
                                        <h6 class="text-dark font-weight-bold mb-3">
                                            <i class="fas fa-sliders-h text-primary me-2"></i> 1. Pilih Sumber Background
                                        </h6>

                                        {{-- 5 TOMBOL SUMBER BACKGROUND --}}
                                        <div class="row g-2 mb-4">
                                            @php
                                                $activeSource = old('source_type', $bgConfig['source_type']);
                                                $sources = [
                                                    'unsplash_preset' => ['icon' => 'fa-th-large', 'title' => '1. Unsplash Kurasi', 'desc' => '6 kategori pilihan siap pakai'],
                                                    'static_color'    => ['icon' => 'fa-paint-brush', 'title' => '2. Static / Gradasi', 'desc' => '1 warna solid atau gradasi'],
                                                    'unsplash_live'   => ['icon' => 'fa-random', 'title' => '3. Unsplash Live', 'desc' => 'Acak online + tombol refresh'],
                                                    'image_url'       => ['icon' => 'fa-link', 'title' => '4. URL Image', 'desc' => 'Tautan gambar langsung'],
                                                    'upload'          => ['icon' => 'fa-cloud-upload-alt', 'title' => '5. Upload Komputer', 'desc' => 'Unggah file lokal (maks 2MB)'],
                                                ];
                                            @endphp
                                            @foreach($sources as $key => $meta)
                                                <div class="col-6 col-md-4">
                                                    <div class="source-tab-btn {{ $activeSource === $key ? 'active' : '' }}" data-source="{{ $key }}">
                                                        <div class="fw-bold text-xs mb-1">
                                                            <i class="fas {{ $meta['icon'] }} me-1"></i> {{ $meta['title'] }}
                                                        </div>
                                                        <div class="text-muted" style="font-size: 11px; line-height: 1.3;">{{ $meta['desc'] }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <hr class="horizontal dark my-3">

                                        {{-- PANEL 1: SOURCE UNSPLASH KURASI (6 KATEGORI) --}}
                                        <div class="source-panel {{ $activeSource === 'unsplash_preset' ? '' : 'd-none' }}" id="panel-unsplash_preset">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-dark text-xs">Pilih Kategori Preferensi Unsplash:</label>
                                                <select name="preset_category" id="select_preset_category" class="form-select form-select-sm border px-2">
                                                    @foreach($catalog as $catKey => $catData)
                                                        <option value="{{ $catKey }}" {{ old('preset_category', $bgConfig['preset_category']) === $catKey ? 'selected' : '' }}>
                                                            {{ $catData['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @foreach($catalog as $catKey => $catData)
                                                <div class="row g-2 preset-category-grid {{ old('preset_category', $bgConfig['preset_category']) === $catKey ? '' : 'd-none' }}" data-cat="{{ $catKey }}">
                                                    @foreach($catData['items'] as $item)
                                                        @php
                                                            $isSelected = (old('selected_url', $bgConfig['image_url']) === $item['full']);
                                                        @endphp
                                                        <div class="col-6">
                                                            <div class="thumb-grid-item js-select-thumb {{ $isSelected ? 'selected' : '' }}" data-full-url="{{ $item['full'] }}">
                                                                <img src="{{ $item['thumb'] }}" alt="{{ $item['title'] }}" loading="lazy">
                                                                <span class="thumb-badge-check"><i class="fas fa-check"></i></span>
                                                                <div class="thumb-caption">{{ $item['title'] }}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- PANEL 2: SOURCE STATIC COLOR (1 TONE OR GRADATION) --}}
                                        <div class="source-panel {{ $activeSource === 'static_color' ? '' : 'd-none' }}" id="panel-static_color">
                                            @php
                                                $colorMode = old('color_mode', $bgConfig['color_mode'] ?? 'solid');
                                            @endphp
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-dark text-xs d-block">Tipe Pewarnaan Background:</label>
                                                <div class="btn-group" role="group">
                                                    <input type="radio" class="btn-check" name="color_mode" id="radioColorSolid" value="solid" autocomplete="off" {{ $colorMode === 'solid' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary btn-sm mb-0" for="radioColorSolid">1 Warna Solid (Single Tone)</label>

                                                    <input type="radio" class="btn-check" name="color_mode" id="radioColorGradient" value="gradient" autocomplete="off" {{ $colorMode === 'gradient' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary btn-sm mb-0" for="radioColorGradient">Gradasi 2 Warna (Gradient)</label>
                                                </div>
                                            </div>

                                            <div id="box-color-solid" class="{{ $colorMode === 'solid' ? '' : 'd-none' }}">
                                                <label class="form-label text-xs fw-bold">Pilih Warna Solid:</label>
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <input type="color" name="solid_color" id="input_solid_color" class="form-control form-control-color p-1 border" style="width: 60px; height: 38px;" value="{{ old('solid_color', $bgConfig['solid_color'] ?? '#eef2f7') }}">
                                                    <span class="text-xs text-muted">Klik kotak warna atau pilih preset cepat:</span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach(['#eef2f7', '#e0f2fe', '#f3e8ff', '#ecfdf5', '#fef3c7', '#1e293b', '#312e81'] as $hex)
                                                        <button type="button" class="soft-swatch js-solid-preset" data-color="{{ $hex }}" style="background: {{ $hex }};" title="{{ $hex }}"></button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div id="box-color-gradient" class="{{ $colorMode === 'gradient' ? '' : 'd-none' }}">
                                                <div class="row g-2 mb-2">
                                                    <div class="col-4">
                                                        <label class="form-label text-xs fw-bold">Warna Awal:</label>
                                                        <input type="color" name="gradient_start" id="input_gradient_start" class="form-control form-control-color w-100 p-1 border" style="height: 38px;" value="{{ old('gradient_start', $bgConfig['gradient_start'] ?? '#6f42c1') }}">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="form-label text-xs fw-bold">Warna Akhir:</label>
                                                        <input type="color" name="gradient_end" id="input_gradient_end" class="form-control form-control-color w-100 p-1 border" style="height: 38px;" value="{{ old('gradient_end', $bgConfig['gradient_end'] ?? '#3b1d7a') }}">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="form-label text-xs fw-bold">Arah Gradasi:</label>
                                                        <select name="gradient_angle" id="input_gradient_angle" class="form-select form-select-sm border px-2" style="height: 38px;">
                                                            <option value="135deg" {{ ($bgConfig['gradient_angle'] ?? '') === '135deg' ? 'selected' : '' }}>Diagonal (135°)</option>
                                                            <option value="90deg" {{ ($bgConfig['gradient_angle'] ?? '') === '90deg' ? 'selected' : '' }}>Horizontal (90°)</option>
                                                            <option value="180deg" {{ ($bgConfig['gradient_angle'] ?? '') === '180deg' ? 'selected' : '' }}>Vertikal (180°)</option>
                                                            <option value="45deg" {{ ($bgConfig['gradient_angle'] ?? '') === '45deg' ? 'selected' : '' }}>Diagonal Naik (45°)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-xs d-block mb-1">Preset Gradasi Cepat:</small>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <button type="button" class="soft-swatch js-grad-preset" data-start="#6f42c1" data-end="#3b1d7a" style="background: linear-gradient(135deg, #6f42c1, #3b1d7a);" title="Royal Purple"></button>
                                                        <button type="button" class="soft-swatch js-grad-preset" data-start="#0ea5e9" data-end="#1d4ed8" style="background: linear-gradient(135deg, #0ea5e9, #1d4ed8);" title="Ocean Blue"></button>
                                                        <button type="button" class="soft-swatch js-grad-preset" data-start="#0f172a" data-end="#334155" style="background: linear-gradient(135deg, #0f172a, #334155);" title="Midnight Slate"></button>
                                                        <button type="button" class="soft-swatch js-grad-preset" data-start="#10b981" data-end="#047857" style="background: linear-gradient(135deg, #10b981, #047857);" title="Emerald"></button>
                                                        <button type="button" class="soft-swatch js-grad-preset" data-start="#e0e7ff" data-end="#f3e8ff" style="background: linear-gradient(135deg, #e0e7ff, #f3e8ff);" title="Soft Lavender"></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- PANEL 3: SOURCE UNSPLASH LIVE (ONLINE + REFRESH RANDOM) --}}
                                        <div class="source-panel {{ $activeSource === 'unsplash_live' ? '' : 'd-none' }}" id="panel-unsplash_live">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                                <div class="flex-grow-1">
                                                    <select id="select_live_topic" class="form-select form-select-sm border px-2">
                                                        <option value="workspace">Tema: Workspace</option>
                                                        <option value="study_desk">Tema: Study Desk</option>
                                                        <option value="library">Tema: Library</option>
                                                        <option value="university_campus">Tema: University Campus</option>
                                                        <option value="dark_minimal_desk">Tema: Dark Minimal Desk</option>
                                                        <option value="education">Tema: Education</option>
                                                    </select>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-primary mb-0" style="background-color: #6f42c1; border-color: #6f42c1;" id="btn-refresh-unsplash-live">
                                                    <i class="fas fa-sync-alt me-1"></i> Refresh Gambar Acak
                                                </button>
                                            </div>

                                            <div id="unsplash-live-grid" class="row g-2">
                                                <div class="col-12 text-center py-4 text-muted text-xs">
                                                    Klik tombol <strong>Refresh Gambar Acak</strong> untuk memuat pilihan foto online terbaru.
                                                </div>
                                            </div>
                                            <small id="unsplash-live-status" class="text-muted text-xs d-block mt-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                {{ $hasUnsplashKey ? 'Terhubung ke Unsplash Official API (Live).' : 'Menggunakan pool kurasi online acak (tambahkan UNSPLASH_ACCESS_KEY di .env untuk pencarian API penuh).' }}
                                            </small>
                                        </div>

                                        {{-- PANEL 4: URL IMAGE EKSTERNAL --}}
                                        <div class="source-panel {{ $activeSource === 'image_url' ? '' : 'd-none' }}" id="panel-image_url">
                                            <label class="form-label fw-bold text-dark text-xs">Masukkan URL Gambar Eksternal:</label>
                                            <div class="input-group input-group-sm border rounded mb-2">
                                                <input type="url" name="custom_url" id="input_custom_url" class="form-control border-0 px-2"
                                                       value="{{ old('custom_url', ($bgConfig['source_type'] === 'image_url' ? $bgConfig['image_url'] : '')) }}"
                                                       placeholder="https://example.com/background-portal.jpg">
                                                <button type="button" class="btn btn-outline-primary mb-0" id="btn-apply-custom-url">
                                                    <i class="fas fa-eye me-1"></i> Pratinjau
                                                </button>
                                            </div>
                                            <small class="text-muted text-xs d-block">Pastikan URL mengarah langsung ke file gambar (<code>.jpg</code>, <code>.png</code>, <code>.webp</code>) dan dapat diakses publik tanpa login.</small>
                                        </div>

                                        {{-- PANEL 5: UPLOAD IMAGE DARI KOMPUTER --}}
                                        <div class="source-panel {{ $activeSource === 'upload' ? '' : 'd-none' }}" id="panel-upload">
                                            <div class="p-3 bg-light border rounded-3 mb-3">
                                                <h6 class="fw-bold text-primary text-xs mb-2"><i class="fas fa-info-circle me-1"></i> Spesifikasi Gambar yang Disarankan</h6>
                                                <ul class="text-xs text-dark mb-0 ps-3" style="line-height: 1.65;">
                                                    <li><strong>Resolusi Ideal:</strong> <code>1920 × 1080 px</code> (Full HD) atau minimal <code>1600 × 900 px</code> dengan orientasi <strong>Lanskap (16:9)</strong> agar gambar tajam dan tidak terpotong di layar laptop/monitor.</li>
                                                    <li><strong>Ukuran File Maksimal:</strong> <strong>2 MB (2048 KB)</strong> — disarankan di bawah <strong>500 KB</strong> agar halaman portal tetap ringan saat diakses serentak oleh banyak peserta ujian.</li>
                                                    <li><strong>Format File:</strong> <code>JPG</code>, <code>JPEG</code>, <code>PNG</code>, atau <code>WEBP</code> (WEBP/JPG paling optimal untuk foto).</li>
                                                </ul>
                                            </div>

                                            <div>
                                                <label class="form-label fw-bold text-dark text-xs">Pilih File Gambar dari Komputer:</label>
                                                <input type="file" name="upload_image" id="input_upload_image" class="form-control form-control-sm border" accept=".jpg,.jpeg,.png,.webp">
                                                <small id="upload-file-info" class="d-block mt-2 text-xs text-muted"></small>
                                            </div>
                                        </div>

                                        <hr class="horizontal dark my-4">

                                        {{-- PENGATURAN OVERLAY & FAILOVER SOFT COLOR --}}
                                        <h6 class="text-dark font-weight-bold mb-3">
                                            <i class="fas fa-shield-alt text-success me-2"></i> 2. Overlay & Failover Warna Lembut
                                        </h6>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-xs fw-bold text-dark">
                                                    Tingkat Gelap Overlay Foto (<span id="label-overlay-val">{{ round(($bgConfig['overlay_opacity'] ?? 0.6) * 100) }}%</span>):
                                                </label>
                                                <input type="range" name="overlay_opacity" id="input_overlay_opacity" class="form-range" min="0" max="0.85" step="0.05" value="{{ old('overlay_opacity', $bgConfig['overlay_opacity'] ?? 0.60) }}">
                                                <small class="text-muted text-xs d-block">Meredupkan foto background agar kartu form putih tetap kontras.</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label text-xs fw-bold text-dark">Warna Failover Otomatis (Soft Color):</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="failover_color" id="input_failover_color" class="form-control form-control-color p-1 border" style="width: 52px; height: 34px;" value="{{ old('failover_color', $bgConfig['failover_color'] ?? '#eef2f7') }}">
                                                    @foreach(['#eef2f7', '#f3effb', '#e0f2fe', '#ecfdf5', '#fef9c3'] as $softHex)
                                                        <button type="button" class="soft-swatch js-failover-preset" data-color="{{ $softHex }}" style="background: {{ $softHex }};" title="Failover {{ $softHex }}"></button>
                                                    @endforeach
                                                </div>
                                                <small class="text-muted text-xs d-block mt-1">Tampil otomatis saat gambar eksternal/internet gagal dimuat atau offline.</small>
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                            <a href="{{ route('sba.index') }}" class="btn btn-outline-secondary btn-sm mb-0">
                                                <i class="fas fa-arrow-left me-1"></i> Kembali
                                            </a>
                                            <button type="submit" class="btn btn-primary btn-sm mb-0 shadow-sm" style="background-color: #6f42c1; border-color: #6f42c1;">
                                                <i class="fas fa-save me-1"></i> Simpan Pengaturan Background
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOLOM KANAN: LIVE MINI PREVIEW PORTAL --}}
                                <div class="col-lg-5">
                                    <div class="border rounded-3 p-3 p-md-4 bg-white h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="text-dark font-weight-bold mb-0">
                                                <i class="fas fa-desktop text-primary me-2"></i> Pratinjau Tampilan Portal
                                            </h6>
                                            <button type="button" class="btn btn-xs btn-outline-warning mb-0" id="btn-test-failover" title="Simulasikan tampilan saat gambar gagal dimuat">
                                                <i class="fas fa-bolt me-1"></i> Tes Mode Failover
                                            </button>
                                        </div>

                                        <div class="portal-live-stage" id="portal-live-stage">
                                            <div class="portal-live-overlay" id="portal-live-overlay"></div>

                                            <div class="portal-live-mock-card">
                                                <div class="portal-live-mock-header">
                                                    <div class="fw-bold" style="font-size: 13px;">PORTAL ASESMEN</div>
                                                    <div style="font-size: 10px; opacity: 0.85;">Smart Based Assessment (SBA)</div>
                                                </div>
                                                <div class="p-3">
                                                    <div class="bg-light border rounded px-2 py-1 mb-2 text-muted" style="font-size: 10px;">
                                                        <i class="fas fa-id-badge me-1"></i> Identitas Peserta
                                                    </div>
                                                    <div class="border border-primary rounded text-center py-1 mb-2 fw-bold text-primary" style="font-size: 13px; letter-spacing: 3px;">
                                                        KODE
                                                    </div>
                                                    <div class="btn btn-sm w-100 text-white fw-bold mb-0" style="background: #6f42c1; font-size: 11px;">
                                                        Masuk Ruang Ujian
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 p-3 bg-light border rounded-3 text-xs text-muted">
                                            <div class="d-flex justify-content-between">
                                                <span>Mode Aktif:</span>
                                                <strong class="text-dark" id="preview-info-mode">-</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span>Status Gambar / Resolusi:</span>
                                                <strong class="text-dark" id="preview-info-status">Siap</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-app.footer />
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputSourceType = document.getElementById('input_source_type');
    const inputSelectedUrl = document.getElementById('input_selected_url');
    const sourceTabBtns = document.querySelectorAll('.source-tab-btn');
    const sourcePanels = document.querySelectorAll('.source-panel');

    const liveStage = document.getElementById('portal-live-stage');
    const liveOverlay = document.getElementById('portal-live-overlay');
    const previewModeLabel = document.getElementById('preview-info-mode');
    const previewStatusLabel = document.getElementById('preview-info-status');

    const inputOverlay = document.getElementById('input_overlay_opacity');
    const labelOverlayVal = document.getElementById('label-overlay-val');
    const inputFailover = document.getElementById('input_failover_color');

    let currentResolvedUrl = @json($bgConfig['resolved_image_url'] ?? '');
    let uploadedDataUrl = null;
    let isFailoverTest = false;

    const modeNames = {
        unsplash_preset: 'Unsplash Kurasi',
        static_color: 'Static / Gradasi Warna',
        unsplash_live: 'Unsplash Live (Acak)',
        image_url: 'URL Image Eksternal',
        upload: 'Upload Gambar Lokal'
    };

    function updateLivePreview() {
        const mode = inputSourceType.value;
        const opacity = parseFloat(inputOverlay.value || 0.6);
        const failoverHex = inputFailover.value || '#eef2f7';

        labelOverlayVal.innerText = Math.round(opacity * 100) + '%';
        previewModeLabel.innerText = isFailoverTest ? 'SIMULASI FAILOVER AKTIF' : (modeNames[mode] || mode);

        if (isFailoverTest) {
            liveStage.style.backgroundImage = 'none';
            liveStage.style.backgroundColor = failoverHex;
            liveOverlay.style.backgroundColor = 'rgba(0,0,0,0)';
            previewStatusLabel.innerText = 'Failover Soft Color (' + failoverHex + ')';
            return;
        }

        if (mode === 'static_color') {
            const cMode = document.querySelector('input[name="color_mode"]:checked')?.value || 'solid';
            liveStage.style.backgroundImage = 'none';
            liveOverlay.style.backgroundColor = 'rgba(0,0,0,0)';

            if (cMode === 'gradient') {
                const start = document.getElementById('input_gradient_start').value;
                const end = document.getElementById('input_gradient_end').value;
                const angle = document.getElementById('input_gradient_angle').value;
                liveStage.style.background = `linear-gradient(${angle}, ${start} 0%, ${end} 100%)`;
                previewStatusLabel.innerText = `Gradasi ${start} → ${end}`;
            } else {
                const solid = document.getElementById('input_solid_color').value;
                liveStage.style.background = solid;
                previewStatusLabel.innerText = `Solid ${solid}`;
            }
            return;
        }

        // Mode berbasis Gambar (unsplash_preset, unsplash_live, image_url, upload)
        liveStage.style.background = '';
        liveStage.style.backgroundColor = failoverHex;
        liveOverlay.style.backgroundColor = `rgba(0, 0, 0, ${opacity})`;

        let targetUrl = '';
        if (mode === 'upload') {
            targetUrl = uploadedDataUrl || currentResolvedUrl;
        } else if (mode === 'image_url') {
            targetUrl = document.getElementById('input_custom_url').value.trim();
        } else {
            targetUrl = inputSelectedUrl.value.trim();
        }

        if (targetUrl) {
            liveStage.style.backgroundImage = `url('${targetUrl}')`;
            liveStage.style.backgroundSize = 'cover';
            liveStage.style.backgroundPosition = 'center center';
        } else {
            liveStage.style.backgroundImage = 'none';
            liveOverlay.style.backgroundColor = 'rgba(0,0,0,0)';
            previewStatusLabel.innerText = 'Belum ada gambar dipilih';
        }
    }

    // Switch Tab 5 Sumber Background
    sourceTabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            isFailoverTest = false;
            const targetSource = this.dataset.source;
            inputSourceType.value = targetSource;

            sourceTabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            sourcePanels.forEach(p => p.classList.add('d-none'));
            document.getElementById('panel-' + targetSource)?.classList.remove('d-none');

            if (targetSource === 'unsplash_live' && document.querySelectorAll('#unsplash-live-grid .js-select-thumb').length === 0) {
                loadUnsplashLive();
            }

            updateLivePreview();
        });
    });

    // Kategori Unsplash Kurasi
    const selectPresetCat = document.getElementById('select_preset_category');
    if (selectPresetCat) {
        selectPresetCat.addEventListener('change', function() {
            const cat = this.value;
            document.querySelectorAll('.preset-category-grid').forEach(grid => {
                grid.classList.toggle('d-none', grid.dataset.cat !== cat);
            });
        });
    }

    // Delegasi Klik Thumbnail Gambar (Kurasi & Unsplash Live)
    document.addEventListener('click', function(e) {
        const thumb = e.target.closest('.js-select-thumb');
        if (!thumb) return;

        isFailoverTest = false;
        document.querySelectorAll('.js-select-thumb').forEach(el => el.classList.remove('selected'));
        thumb.classList.add('selected');

        const fullUrl = thumb.dataset.fullUrl;
        inputSelectedUrl.value = fullUrl;
        previewStatusLabel.innerText = 'Gambar terpilih siap disimpan';
        updateLivePreview();
    });

    // Static Color & Gradasi Events
    document.querySelectorAll('input[name="color_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const isGrad = this.value === 'gradient';
            document.getElementById('box-color-solid').classList.toggle('d-none', isGrad);
            document.getElementById('box-color-gradient').classList.toggle('d-none', !isGrad);
            updateLivePreview();
        });
    });

    ['input_solid_color', 'input_gradient_start', 'input_gradient_end', 'input_gradient_angle'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updateLivePreview);
        document.getElementById(id)?.addEventListener('change', updateLivePreview);
    });

    document.querySelectorAll('.js-solid-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('input_solid_color').value = this.dataset.color;
            updateLivePreview();
        });
    });

    document.querySelectorAll('.js-grad-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('input_gradient_start').value = this.dataset.start;
            document.getElementById('input_gradient_end').value = this.dataset.end;
            updateLivePreview();
        });
    });

    // Unsplash Live Fetcher (Anti-Cache + Indikator Status API)
    const liveGrid = document.getElementById('unsplash-live-grid');
    const btnRefreshLive = document.getElementById('btn-refresh-unsplash-live');
    const selectLiveTopic = document.getElementById('select_live_topic');

    async function loadUnsplashLive() {
        const topic = selectLiveTopic.value;
        const liveStatusEl = document.getElementById('unsplash-live-status');
        btnRefreshLive.disabled = true;
        btnRefreshLive.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memuat...';
        liveGrid.innerHTML = '<div class="col-12 text-center py-4 text-muted text-xs"><i class="fas fa-spinner fa-spin me-1"></i> Mengambil gambar acak dari Unsplash...</div>';

        try {
            const res = await fetch(`{{ route('sba.settings.background_portal.unsplash_live') }}?query=${encodeURIComponent(topic)}&_t=${Date.now()}`, {
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            });
            const data = await res.json();

            if (data.status === 'success' && Array.isArray(data.photos)) {
                liveGrid.innerHTML = '';
                data.photos.forEach(photo => {
                    const isSel = inputSelectedUrl.value === photo.full;
                    const col = document.createElement('div');
                    col.className = 'col-6 col-md-4';
                    col.innerHTML = `
                        <div class="thumb-grid-item js-select-thumb ${isSel ? 'selected' : ''}" data-full-url="${photo.full}">
                            <img src="${photo.thumb}" alt="${photo.title}" loading="lazy">
                            <span class="thumb-badge-check"><i class="fas fa-check"></i></span>
                            <div class="thumb-caption">${photo.title}</div>
                        </div>
                    `;
                    liveGrid.appendChild(col);
                });

                if (liveStatusEl) {
                    if (data.mode === 'official_api') {
                        liveStatusEl.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i> Live dari Unsplash Official API (6 foto acak baru dimuat).</span>';
                    } else if (data.api_error) {
                        liveStatusEl.innerHTML = `<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Fallback lokal aktif (${data.api_error}).</span>`;
                    }
                }
            }
        } catch (err) {
            liveGrid.innerHTML = '<div class="col-12 text-center py-3 text-danger text-xs">Gagal memuat gambar online. Periksa koneksi internet Anda.</div>';
        } finally {
            btnRefreshLive.disabled = false;
            btnRefreshLive.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh Gambar Acak';
        }
    }

    btnRefreshLive?.addEventListener('click', loadUnsplashLive);
    selectLiveTopic?.addEventListener('change', loadUnsplashLive);

    // Custom URL Image Preview
    document.getElementById('btn-apply-custom-url')?.addEventListener('click', function() {
        isFailoverTest = false;
        const url = document.getElementById('input_custom_url').value.trim();
        if (!url) return;

        previewStatusLabel.innerText = 'Memeriksa URL gambar...';
        const testImg = new Image();
        testImg.onload = function() {
            previewStatusLabel.innerText = `Valid (${this.naturalWidth} × ${this.naturalHeight} px)`;
            updateLivePreview();
        };
        testImg.onerror = function() {
            previewStatusLabel.innerText = 'URL gagal dimuat (akan memakai Failover)';
            updateLivePreview();
        };
        testImg.src = url;
    });

    // Upload Image Lokal + Cek Resolusi & Ukuran File
    const inputUpload = document.getElementById('input_upload_image');
    const uploadInfo = document.getElementById('upload-file-info');

    inputUpload?.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        isFailoverTest = false;
        const sizeKb = Math.round(file.size / 1024);
        const reader = new FileReader();
        reader.onload = function(e) {
            uploadedDataUrl = e.target.result;
            const img = new Image();
            img.onload = function() {
                const dimText = `${this.naturalWidth} × ${this.naturalHeight} px (${sizeKb} KB)`;
                uploadInfo.innerHTML = `<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> File siap diunggah:</span> ${dimText}`;
                previewStatusLabel.innerText = dimText;
                updateLivePreview();
            };
            img.src = uploadedDataUrl;
        };
        reader.readAsDataURL(file);
    });

    // Overlay & Failover Controls
    inputOverlay?.addEventListener('input', updateLivePreview);
    inputFailover?.addEventListener('input', updateLivePreview);

    document.querySelectorAll('.js-failover-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            inputFailover.value = this.dataset.color;
            updateLivePreview();
        });
    });

    document.getElementById('btn-test-failover')?.addEventListener('click', function() {
        isFailoverTest = !isFailoverTest;
        this.classList.toggle('btn-warning', isFailoverTest);
        this.classList.toggle('btn-outline-warning', !isFailoverTest);
        this.innerHTML = isFailoverTest
            ? '<i class="fas fa-undo me-1"></i> Kembali ke Normal'
            : '<i class="fas fa-bolt me-1"></i> Tes Mode Failover';
        updateLivePreview();
    });

    updateLivePreview();
});
</script>
@endsection