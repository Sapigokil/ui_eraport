<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <!-- Masukkan meta CSRF token di header layout blade -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // Ping server setiap 5 menit (300.000 ms) untuk memperbarui session & token CSRF
        setInterval(function() {
            fetch('/ping-csrf', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Jika mereturn token baru, update atribut meta & input _token jika ada
                if (data.token) {
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                    document.querySelectorAll('input[name="_token"]').forEach(input => {
                        input.value = data.token;
                    });
                }
            })
            .catch(err => console.log('Keep-alive ping failed'));
        }, 5 * 60 * 1000);
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SBA Engine - {{ $uji->judul }}</title>
    
    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- KaTeX untuk Render Rumus Matematika -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js"></script>
    
    <style>
        :root {
            --sba-primary: #6f42c1;
            --sba-secondary: #8392ab;
            --sba-dark: #172b4d;
            --sba-light: #f7fafc;
            --sba-purple: #6f42c1;
            --sba-purple-light: #f6f2ff;
        }

        body {
            background-color: #f4f5f9;
            font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            user-select: none;
            -webkit-user-select: none;
            min-height: 100vh;
        }

        /* Penyesuaian Rumus KaTeX agar Responsif di Layar HP */
        .katex {
            font-size: 1.08em;
        }
        .katex-display {
            overflow-x: auto;
            overflow-y: hidden;
            padding: 6px 2px;
            margin: 0.6em 0;
            max-width: 100%;
        }

        /* Header Navbar & Banner Ungu Gradasi */
        .navbar-cbt {
            background: linear-gradient(135deg, #6f42c1 0%, #825ee4 100%);
            box-shadow: 0 4px 15px rgba(111, 66, 193, 0.22);
        }

        /* Card Styling */
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background: #ffffff;
        }

        .header-gradient {
            background: linear-gradient(135deg, #6f42c1 0%, #825ee4 100%);
            color: white;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .text-sba-purple {
            color: #6f42c1 !important;
        }

        .badge-sba-purple {
            background-color: rgba(111, 66, 193, 0.12) !important;
            color: #6f42c1 !important;
        }

        .btn-sba-purple {
            background-color: #6f42c1 !important;
            border-color: #6f42c1 !important;
            color: #ffffff !important;
        }

        .btn-sba-purple:hover {
            background-color: #59339d !important;
            border-color: #59339d !important;
            color: #ffffff !important;
        }

        /* Styling Media / Gambar Soal */
        .soal-media-wrapper {
            background-color: #f8f6fc;
            border: 1px solid #e9e3f5;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
            text-align: center;
        }

        .soal-media-img {
            max-height: 380px;
            max-width: 100%;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        /* Custom Radio & Checkbox Opsi Jawaban (Ungu Konsisten) */
        .option-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            background-color: #fff;
        }

        .option-card:hover {
            border-color: #6f42c1;
            background-color: #faf7ff;
            transform: translateY(-1px);
        }

        .option-card.selected-option {
            border-color: #6f42c1 !important;
            background-color: #f6f2ff !important;
            box-shadow: 0 3px 10px rgba(111, 66, 193, 0.14);
        }

        .option-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #495057;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            margin-right: 14px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .selected-option .option-badge {
            background-color: #6f42c1;
            color: #ffffff;
        }

        .option-media-img {
            max-height: 190px;
            max-width: 100%;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            background-color: #fff;
            padding: 3px;
        }

        /* Styling Pernyataan Benar / Salah (Satu Warna Ungu Netral + Ikon Radio) */
        .bs-statement-row {
            border: 2px solid #cbd5e1;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 12px;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
        }

        .bs-statement-row.bs-answered {
            border-color: #6f42c1;
            background-color: #f6f2ff;
            box-shadow: 0 3px 10px rgba(111, 66, 193, 0.12);
        }

        .bs-choice-btn {
            min-width: 104px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 8px 14px;
            cursor: pointer;
            border: 2px solid #cbd5e1;
            background-color: #ffffff;
            color: #495057;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            user-select: none;
        }

        .bs-choice-btn:hover {
            border-color: #6f42c1;
            background-color: #faf7ff;
            color: #6f42c1;
        }

        .bs-choice-btn .bs-radio-icon {
            font-size: 0.95rem;
            color: #8392ab;
            transition: color 0.15s ease;
        }

        .bs-choice-btn:hover .bs-radio-icon {
            color: #6f42c1;
        }

        .radio-bs:checked + .bs-choice-btn {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(111, 66, 193, 0.28);
        }

        .radio-bs:checked + .bs-choice-btn .bs-radio-icon {
            color: #ffffff;
        }

        /* Grid Navigasi Soal */
        .nav-grid-btn {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            border: 2px solid #e9ecef;
            background-color: #fff;
            color: #495057;
        }

        .nav-grid-btn:hover {
            border-color: #6f42c1;
            color: #6f42c1;
        }

        .nav-grid-btn.active-grid {
            border-color: #6f42c1 !important;
            box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.25);
        }

        .nav-grid-btn.btn-answered {
            background-color: #2dce89 !important;
            border-color: #2dce89 !important;
            color: #ffffff !important;
        }

        .nav-grid-btn.btn-partial {
            background-color: #f3ebff !important;
            border-color: #825ee4 !important;
            color: #6f42c1 !important;
        }

        .nav-grid-btn.btn-ragu {
            background-color: #fb6340 !important;
            border-color: #fb6340 !important;
            color: #ffffff !important;
        }

        /* Sticky Navigation Column */
        @media (min-width: 992px) {
            .sticky-sidebar {
                position: sticky;
                top: 90px;
            }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- ====================================================================== --}}
    {{-- TAMPILAN 1: PERSIAPAN ASESMEN --}}
    {{-- ====================================================================== --}}
    <div id="section-persiapan" class="container py-5 my-auto">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                
                <div class="card card-custom overflow-hidden">
                    <div class="card-header header-gradient py-4 text-center">
                        <i class="fas fa-user-shield display-5 mb-2 opacity-8"></i>
                        <h4 class="mb-1 fw-bold text-white">PERSIAPAN ASESMEN DIGITAL</h4>
                        <p class="mb-0 text-white-50 text-sm">Smart Based Assessment System</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <div class="bg-light border rounded-3 p-3 mb-4">
                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                <span class="text-muted small"><i class="fas fa-user text-sba-purple me-2"></i>Nama Peserta:</span>
                                <span class="fw-bold text-dark">{{ $namaPeserta }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                <span class="text-muted small"><i class="fas fa-book-open text-sba-purple me-2"></i>Judul Asesmen:</span>
                                <span class="fw-bold text-dark text-end">{{ $uji->judul }}</span>
                            </div>
                            <div class="d-flex justify-content-between {{ $uji->waktu_selesai ? 'mb-2 border-bottom pb-2' : '' }}">
                                <span class="text-muted small"><i class="fas fa-hourglass-half text-sba-purple me-2"></i>Durasi Ujian:</span>
                                <span class="fw-bold text-sba-purple">
                                    {{ $uji->durasi_menit > 0 ? $uji->durasi_menit . ' Menit' : 'Tanpa Batas Waktu' }}
                                </span>
                            </div>
                            @if($uji->waktu_selesai)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small"><i class="fas fa-calendar-times text-danger me-2"></i>Jadwal Berakhir:</span>
                                    <span class="fw-bold text-danger">{{ $uji->waktu_selesai->format('d/m/Y H:i') }} WIB</span>
                                </div>
                            @endif
                        </div>

                        @if($uji->waktu_selesai && $uji->durasi_menit > 0 && $hasil->waktu_mulai->copy()->addMinutes($uji->durasi_menit)->gt($uji->waktu_selesai))
                            <div class="alert alert-warning border-0 rounded-3 p-3 mb-4" style="background-color: #fff8e1;">
                                <div class="d-flex align-items-center text-dark small">
                                    <i class="fas fa-clock text-warning fs-5 me-3"></i>
                                    <div>
                                        <strong>Perhatian Jadwal Berakhir:</strong> Sisa waktu pengerjaan mengikuti batas jadwal selesai ujian pada pukul <strong>{{ $uji->waktu_selesai->format('H:i') }} WIB</strong> dan akan terkumpul otomatis saat jadwal berakhir.
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(isset($jawabanTersimpan) && $jawabanTersimpan->count() > 0)
                            <div class="alert border-0 rounded-3 p-3 mb-4" style="background-color: #f3ebff;">
                                <div class="d-flex align-items-center text-sba-purple small">
                                    <i class="fas fa-history fs-5 me-3"></i>
                                    <div>
                                        <strong>Melanjutkan Sesi Sebelumnya:</strong> Sebanyak <strong>{{ $jawabanTersimpan->count() }} jawaban</strong> Anda telah dipulihkan otomatis. Sisa waktu mengikuti waktu mulai awal dan jadwal ujian.
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- CARD DESKRIPSI ASESMEN (Tampil jika terisi > 10 karakter) --}}
                        @if(!empty($uji->deskripsi) && mb_strlen(trim($uji->deskripsi)) > 10)
                            <div class="card border rounded-3 p-3 mb-4 bg-white shadow-none">
                                <h6 class="fw-bold text-dark mb-2 small">
                                    <i class="fas fa-align-left text-sba-purple me-2"></i>Informasi Asesmen
                                </h6>
                                <div class="text-dark small mb-0 math-render-area" style="line-height: 1.6;">{{ $uji->deskripsi }}</div>
                            </div>
                        @endif

                        @if($uji->open_book)
                            <h6 class="fw-bold text-success mb-3">
                                <i class="fas fa-book-open me-2"></i>Mode Asesmen: Open Book
                            </h6>
                            
                            <div class="alert alert-success border-0 rounded-3 p-3 mb-4 bg-opacity-10" style="background-color: #e6f7ed;">
                                <ul class="text-dark small ps-3 mb-0" style="line-height: 1.6;">
                                    <li>Asesmen ini menggunakan <strong>Mode Open Book</strong>.</li>
                                    <li>Anda diperbolehkan membuka tab lain, catatan, atau referensi materi selama waktu ujian masih berjalan.</li>
                                    <li>Pastikan menekan tombol <strong>Kumpulkan Ujian</strong> sebelum durasi atau jadwal ujian berakhir.</li>
                                </ul>
                            </div>

                            <div class="form-check mb-4 bg-light p-3 rounded border">
                                <input class="form-check-input ms-1 border-secondary" type="checkbox" id="checkSetuju" style="transform: scale(1.2); cursor: pointer;">
                                <label class="form-check-label ms-3 text-sm text-dark fw-bold" for="checkSetuju" style="cursor: pointer;">
                                    Saya memahami petunjuk di atas dan siap memulai/melanjutkan ujian.
                                </label>
                            </div>
                        @else
                            <h6 class="fw-bold text-danger mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>Peraturan & Keamanan
                            </h6>
                            
                            <div class="alert alert-danger border-0 rounded-3 p-3 mb-4 bg-opacity-10" style="background-color: #fce8e6;">
                                <ul class="text-dark small ps-3 mb-0" style="line-height: 1.6;">
                                    <li>Asesmen dikerjakan penuh dalam mode <strong>Layar Penuh (Fullscreen)</strong> pada perangkat yang mendukung.</li>
                                    <li>Setiap tindakan <strong>pindah tab, membuka aplikasi lain, atau keluar dari area ujian</strong> dianggap sebagai pelanggaran.</li>
                                    <li>Sisa toleransi pelanggaran Anda: <strong class="text-danger fs-6">{{ $uji->toleransi_pelanggaran }} kali</strong>. Melebihi batas akan membuat sistem mengunci ujian otomatis.</li>
                                </ul>
                            </div>

                            <div class="form-check mb-4 bg-light p-3 rounded border">
                                <input class="form-check-input ms-1 border-secondary" type="checkbox" id="checkSetuju" style="transform: scale(1.2); cursor: pointer;">
                                <label class="form-check-label ms-3 text-sm text-dark fw-bold" for="checkSetuju" style="cursor: pointer;">
                                    Saya memahami aturan dan siap memulai ujian.
                                </label>
                            </div>
                        @endif

                        <button type="button" id="btnMulaiUji" class="btn btn-secondary w-100 py-3 fw-bold rounded-3 shadow-sm text-white" disabled style="transition: all 0.3s ease;">
                            <i class="fas fa-play-circle me-2"></i> {{ (isset($jawabanTersimpan) && $jawabanTersimpan->count() > 0) ? 'LANJUTKAN ASESMEN SEKARANG' : 'MULAI ASESMEN SEKARANG' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- ====================================================================== --}}
    {{-- TAMPILAN 2: RUANG UJIAN (CBT ENGINE) --}}
    {{-- ====================================================================== --}}
    <div id="section-ujian" class="d-none d-flex flex-column min-vh-100 w-100">
        
        <!-- NAVBAR HEAD (UNGU GRADASI) -->
        <nav class="navbar navbar-expand navbar-cbt px-4 py-3 shadow-sm sticky-top">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <div class="bg-white text-sba-purple rounded-circle p-2 me-3 d-none d-md-block" style="width:40px; height:40px; text-align:center;">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-white fs-6">{{ $uji->judul }}</h6>
                        <small class="text-white-50" style="font-size: 0.8rem;">
                            Peserta: <strong>{{ $namaPeserta }}</strong>
                            @if($uji->open_book)
                                <span class="badge bg-success ms-2">Open Book</span>
                            @endif
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white text-dark px-3 py-2 rounded-3 shadow-sm fw-bold border">
                        <i class="fas fa-clock text-danger me-2"></i><span id="timer-countdown">--:--:--</span>
                    </div>
                    <button type="button" class="btn btn-light btn-sm rounded-circle p-2" id="btnFullscreen" title="Mode Layar Penuh" style="width:38px; height:38px;">
                        <i class="fas fa-expand text-sba-purple"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- KONTEN UTAMA -->
        <div class="container-fluid py-4 px-4 flex-grow-1">
            <div class="row">
                
                <!-- PANELS SOAL -->
                <div class="col-lg-8 mb-4">
                    <form id="form-pengerjaan">
                        @csrf
                        @foreach($soals as $index => $soal)
                            @php
                                $savedItem = isset($jawabanTersimpan) ? $jawabanTersimpan->get($soal->id) : null;
                                $savedVal = $savedItem ? $savedItem->jawaban : null;
                                $isSavedRagu = $savedItem ? (bool) $savedItem->ragu_ragu : false;
                                $savedArr = (in_array($soal->tipe, ['pg_kompleks', 'benar_salah']) && $savedVal)
                                    ? (is_array($savedVal) ? $savedVal : (json_decode((string) $savedVal, true) ?? []))
                                    : [];

                                $opsiTeksList = is_array($soal->opsi_jawaban) ? $soal->opsi_jawaban : [];
                                $opsiMediaList = is_array($soal->opsi_media) ? $soal->opsi_media : [];
                                $jumlahSlotOpsi = max(count($opsiTeksList), count($opsiMediaList));
                            @endphp

                            <div class="card card-custom soal-item-pane mb-4 {{ $index === 0 ? '' : 'd-none' }}" data-index="{{ $index }}" data-soal-id="{{ $soal->id }}" data-soal-tipe="{{ $soal->tipe }}">
                                
                                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-sba-purple fw-bold px-3 py-2 rounded-pill" style="font-size: 0.85rem;">
                                            Soal Nomor {{ $index + 1 }}
                                        </span>
                                        @if($soal->tipe === 'benar_salah')
                                            <span class="badge badge-sba-purple fw-bold px-3 py-2 rounded-pill" style="font-size: 0.8rem;">
                                                Pernyataan Benar / Salah
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-muted small">Bobot: <strong class="text-dark">{{ $soal->bobot_nilai }} Poin</strong></span>
                                </div>
                                
                                <div class="card-body p-4">
                                    
                                    {{-- 1. MEDIA GAMBAR SOAL (DITAMPILKAN SEBELUM TEKS SOAL) --}}
                                    @if(!empty($soal->media))
                                        <div class="soal-media-wrapper">
                                            <img src="{{ asset('storage/' . $soal->media) }}" alt="Gambar Soal {{ $index + 1 }}" class="soal-media-img">
                                        </div>
                                    @endif

                                    {{-- 2. TEKS PERTANYAAN / STIMULUS BACAAN --}}
                                    <div class="mb-4 text-dark fs-6 math-render-area" style="line-height: 1.7; font-weight: 500;">{{ $soal->pertanyaan }}</div>

                                    {{-- 3. OPSI JAWABAN (TEKS &/ATAU GAMBAR + PEMULIHAN JAWABAN TERSIMPAN) --}}
                                    <div class="options-container">
                                        @if($soal->tipe === 'pg')
                                            @for($optIndex = 0; $optIndex < $jumlahSlotOpsi; $optIndex++)
                                                @php
                                                    $opsiTeks = trim((string) ($opsiTeksList[$optIndex] ?? ''));
                                                    $opsiMediaPath = $opsiMediaList[$optIndex] ?? null;
                                                @endphp
                                                @if($opsiTeks !== '' || !empty($opsiMediaPath))
                                                    @php
                                                        $isChecked = ($savedVal !== null && (string) $savedVal === (string) $optIndex);
                                                    @endphp
                                                    <div class="option-card d-flex align-items-center {{ $isChecked ? 'selected-option' : '' }}">
                                                        <input class="form-check-input d-none radio-jawaban" type="radio" name="jawaban_{{ $soal->id }}" value="{{ $optIndex }}" id="opt_{{ $soal->id }}_{{ $optIndex }}" data-soal-id="{{ $soal->id }}" {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="w-100 d-flex align-items-center m-0 cursor-pointer text-dark" for="opt_{{ $soal->id }}_{{ $optIndex }}">
                                                            <div class="option-badge">{{ chr(65 + $optIndex) }}</div>
                                                            <div class="flex-grow-1" style="min-width: 0;">
                                                                @if($opsiTeks !== '')
                                                                    <div class="math-render-area" style="line-height: 1.5;">{{ $opsiTeks }}</div>
                                                                @endif
                                                                @if(!empty($opsiMediaPath))
                                                                    <div class="{{ $opsiTeks !== '' ? 'mt-2' : '' }}">
                                                                        <img src="{{ asset('storage/' . $opsiMediaPath) }}" alt="Gambar Opsi {{ chr(65 + $optIndex) }}" class="option-media-img">
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </label>
                                                    </div>
                                                @endif
                                            @endfor

                                        @elseif($soal->tipe === 'pg_kompleks')
                                            <div class="alert py-2 px-3 text-xs mb-3 border-0 text-sba-purple fw-bold" style="background-color: #f3ebff;">
                                                <i class="fas fa-info-circle me-1"></i> Jawaban kompleks: Pilih satu atau lebih pilihan yang menurut Anda benar.
                                            </div>
                                            @for($optIndex = 0; $optIndex < $jumlahSlotOpsi; $optIndex++)
                                                @php
                                                    $opsiTeks = trim((string) ($opsiTeksList[$optIndex] ?? ''));
                                                    $opsiMediaPath = $opsiMediaList[$optIndex] ?? null;
                                                @endphp
                                                @if($opsiTeks !== '' || !empty($opsiMediaPath))
                                                    @php
                                                        $isChecked = in_array((string) $optIndex, array_map('strval', $savedArr));
                                                    @endphp
                                                    <div class="option-card d-flex align-items-center {{ $isChecked ? 'selected-option' : '' }}">
                                                        <input class="form-check-input me-3 checkbox-jawaban" type="checkbox" name="jawaban_{{ $soal->id }}[]" value="{{ $optIndex }}" id="opt_{{ $soal->id }}_{{ $optIndex }}" data-soal-id="{{ $soal->id }}" style="transform: scale(1.2);" {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="w-100 d-flex align-items-center m-0 cursor-pointer text-dark" for="opt_{{ $soal->id }}_{{ $optIndex }}">
                                                            <div class="option-badge">{{ chr(65 + $optIndex) }}</div>
                                                            <div class="flex-grow-1" style="min-width: 0;">
                                                                @if($opsiTeks !== '')
                                                                    <div class="math-render-area" style="line-height: 1.5;">{{ $opsiTeks }}</div>
                                                                @endif
                                                                @if(!empty($opsiMediaPath))
                                                                    <div class="{{ $opsiTeks !== '' ? 'mt-2' : '' }}">
                                                                        <img src="{{ asset('storage/' . $opsiMediaPath) }}" alt="Gambar Opsi {{ chr(65 + $optIndex) }}" class="option-media-img">
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </label>
                                                    </div>
                                                @endif
                                            @endfor

                                        @elseif($soal->tipe === 'benar_salah')
                                            @php
                                                $pernyataanList = is_array($soal->opsi_jawaban) ? $soal->opsi_jawaban : [];
                                                $totalPernyataan = count($pernyataanList);
                                            @endphp
                                            <div class="alert py-2 px-3 small mb-3 border-0 text-sba-purple fw-bold d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #f3ebff;">
                                                <span>
                                                    <i class="fas fa-check-double me-1"></i> Tentukan <strong>Benar</strong> atau <strong>Salah</strong> untuk setiap pernyataan di bawah ini.
                                                </span>
                                                <span class="badge text-white bs-progress-badge" style="background-color: #6f42c1;" data-soal-id="{{ $soal->id }}">
                                                    Terjawab: 0 / {{ $totalPernyataan }} pernyataan
                                                </span>
                                            </div>

                                            <div class="bs-statements-container" data-soal-id="{{ $soal->id }}" data-total-statements="{{ $totalPernyataan }}">
                                                @foreach($pernyataanList as $stmtIndex => $stmtItem)
                                                    @php
                                                        $teksPernyataan = is_array($stmtItem) ? ($stmtItem['teks'] ?? '') : (string) $stmtItem;
                                                        $savedChoice = strtolower(trim((string) ($savedArr[$stmtIndex] ?? '')));
                                                        $isAnsweredRow = in_array($savedChoice, ['benar', 'salah'], true);
                                                    @endphp
                                                    <div class="bs-statement-row {{ $isAnsweredRow ? 'bs-answered' : '' }}" data-stmt-index="{{ $stmtIndex }}">
                                                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                                            <div class="d-flex align-items-start flex-grow-1" style="min-width: 0;">
                                                                <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold me-3 mt-1 px-2 py-1" style="min-width: 28px;">
                                                                    {{ $stmtIndex + 1 }}.
                                                                </span>
                                                                <div class="text-dark math-render-area" style="line-height: 1.6; min-width: 0;">{{ $teksPernyataan }}</div>
                                                            </div>

                                                            <div class="d-flex align-items-center gap-2 flex-shrink-0 justify-content-end">
                                                                <div>
                                                                    <input type="radio"
                                                                           class="d-none radio-bs radio-bs-benar"
                                                                           name="bs_{{ $soal->id }}_{{ $stmtIndex }}"
                                                                           id="bs_{{ $soal->id }}_{{ $stmtIndex }}_benar"
                                                                           value="benar"
                                                                           data-soal-id="{{ $soal->id }}"
                                                                           data-stmt-index="{{ $stmtIndex }}"
                                                                           {{ $savedChoice === 'benar' ? 'checked' : '' }}>
                                                                    <label class="bs-choice-btn bs-choice-btn-benar mb-0" for="bs_{{ $soal->id }}_{{ $stmtIndex }}_benar">
                                                                        <i class="{{ $savedChoice === 'benar' ? 'fas fa-dot-circle' : 'far fa-circle' }} bs-radio-icon"></i>
                                                                        <span>Benar</span>
                                                                    </label>
                                                                </div>

                                                                <div>
                                                                    <input type="radio"
                                                                           class="d-none radio-bs radio-bs-salah"
                                                                           name="bs_{{ $soal->id }}_{{ $stmtIndex }}"
                                                                           id="bs_{{ $soal->id }}_{{ $stmtIndex }}_salah"
                                                                           value="salah"
                                                                           data-soal-id="{{ $soal->id }}"
                                                                           data-stmt-index="{{ $stmtIndex }}"
                                                                           {{ $savedChoice === 'salah' ? 'checked' : '' }}>
                                                                    <label class="bs-choice-btn bs-choice-btn-salah mb-0" for="bs_{{ $soal->id }}_{{ $stmtIndex }}_salah">
                                                                        <i class="{{ $savedChoice === 'salah' ? 'fas fa-dot-circle' : 'far fa-circle' }} bs-radio-icon"></i>
                                                                        <span>Salah</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                        @elseif($soal->tipe === 'esai')
                                            <div class="form-group">
                                                <label class="form-label text-muted small fw-bold">Tuliskan Jawaban Uraian Anda:</label>
                                                <textarea class="form-control text-area-esai border p-3 rounded-3" rows="6" placeholder="Ketik jawaban esai secara rinci di sini..." data-soal-id="{{ $soal->id }}">{{ $savedVal ?? '' }}</textarea>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer bg-white py-3 px-4 d-flex justify-content-between align-items-center border-top">
                                    <button type="button" class="btn btn-outline-secondary px-4 rounded-3 btn-prev" {{ $index === 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-chevron-left me-2"></i> Sebelumnya
                                    </button>
                                    
                                    <div class="form-check d-flex align-items-center m-0">
                                        <input class="form-check-input me-2 check-ragu" type="checkbox" id="ragu_{{ $soal->id }}" data-soal-id="{{ $soal->id }}" style="transform: scale(1.2); cursor: pointer;" {{ $isSavedRagu ? 'checked' : '' }}>
                                        <label class="form-check-label text-xs fw-bold text-warning cursor-pointer" for="ragu_{{ $soal->id }}">
                                            Ragu-Ragu
                                        </label>
                                    </div>

                                    @if($index < count($soals) - 1)
                                        <button type="button" class="btn btn-sba-purple px-4 rounded-3 btn-next">
                                            Selanjutnya <i class="fas fa-chevron-right ms-2"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-success px-4 rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalFinalisasi">
                                            <i class="fas fa-check-circle me-2"></i> Selesai
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </form>
                </div>

                <!-- NAVIGASI NOMOR SOAL -->
                <div class="col-lg-4">
                    <div class="sticky-sidebar">
                        <div class="card card-custom mb-3">
                            <div class="card-header bg-white py-3 px-4 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-th me-2 text-sba-purple"></i> Navigasi Soal</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex flex-wrap gap-2 justify-content-center" id="nav-grid-container">
                                    @foreach($soals as $index => $soal)
                                        <button type="button" class="nav-grid-btn {{ $index === 0 ? 'active-grid' : '' }}" data-target-index="{{ $index }}" id="grid_btn_{{ $index }}">
                                            {{ $index + 1 }}
                                        </button>
                                    @endforeach
                                </div>
                                <hr class="my-3 opacity-2">
                                <div class="d-flex flex-wrap justify-content-between gap-2 text-xs text-muted px-2">
                                    <div><span class="badge bg-success rounded-circle p-1 me-1">&nbsp;</span> Terjawab</div>
                                    <div><span class="badge rounded-circle p-1 me-1" style="background-color: #825ee4;">&nbsp;</span> Sebagian</div>
                                    <div><span class="badge bg-warning rounded-circle p-1 me-1">&nbsp;</span> Ragu-Ragu</div>
                                    <div><span class="badge bg-light text-dark border rounded-circle p-1 me-1">&nbsp;</span> Belum</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="button" class="btn btn-danger py-3 fw-bold rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFinalisasi">
                                <i class="fas fa-paper-plane me-2"></i> KUMPULKAN UJIAN
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL FINALISASI -->
    <div class="modal fade" id="modalFinalisasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 card-custom">
                <div class="modal-header header-gradient">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-clipboard-check me-2"></i> Finalisasi Ujian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">Pastikan semua soal telah terjawab dengan benar sebelum menyelesaikan ujian.</p>
                    <div class="row g-2 mb-3 text-center">
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="d-block text-muted small">Total Soal</span>
                                <strong class="fs-4 text-dark">{{ count($soals) }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="d-block text-muted small">Terjawab</span>
                                <strong class="fs-4 text-success" id="stat-terjawab">0</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="d-block text-muted small">Belum</span>
                                <strong class="fs-4 text-danger" id="stat-belum">0</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-0">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal">Kembali</button>
                    <form action="{{ route('sba.uji.selesai', $uji->id) }}" method="POST" id="form-finalisasi">
                        @csrf
                        <input type="hidden" name="hasil_id" value="{{ $hasil->id }}">
                        <button type="submit" class="btn btn-success px-4 fw-bold rounded-3">Ya, Kumpulkan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PERINGATAN PELANGGARAN -->
    <div class="modal fade" id="modalPelanggaran" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 card-custom">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> PERINGATAN PELANGGARAN</h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="fs-6 text-dark mb-2" id="text-pesan-pelanggaran">Anda terdeteksi keluar dari area ujian!</p>
                    <div class="alert alert-warning py-2 mb-0 border-0 rounded-3">
                        Sisa toleransi kesempatan Anda: <strong id="text-sisa-toleransi" class="fs-5 text-danger">0</strong> kali lagi.
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-center border-0">
                    <button type="button" class="btn btn-danger px-4 fw-bold rounded-3" id="btnKembaliUjian">
                        <i class="fas fa-expand me-2"></i> Kembalikan Layar Penuh & Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DISKUALIFIKASI / GAGAL UJIAN -->
    <div class="modal fade" id="modalGagal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 card-custom">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold text-danger"><i class="fas fa-user-slash me-2"></i> UJIAN DIBATALKAN</h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-times-circle text-danger display-3 mb-3"></i>
                    <h5 class="fw-bold text-dark">Batas Toleransi Terlampaui</h5>
                    <p class="text-muted small mb-0" id="text-pesan-gagal">Anda telah melewati batas toleransi pelanggaran. Ujian dihentikan secara otomatis.</p>
                </div>
                <div class="modal-footer bg-light justify-content-center border-0">
                    <button type="button" class="btn btn-secondary px-4 fw-bold rounded-3" id="btnKeluarPortal">
                        Kembali ke Portal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkSetuju = document.getElementById('checkSetuju');
            const btnMulaiUji = document.getElementById('btnMulaiUji');
            const secPersiapan = document.getElementById('section-persiapan');
            const secUjian = document.getElementById('section-ujian');
            const btnFullscreen = document.getElementById('btnFullscreen');
            const btnKembaliUjian = document.getElementById('btnKembaliUjian');

            // --- RENDER FORMAT TEKS (B/I/U/SUB/SUP) & RUMUS MATEMATIKA (KATEX) TANPA BENTROK ---
            function buildSafeRichHtmlWithMathTokens(rawText) {
                const mathTokens = [];
                // 1. Amankan blok rumus $$...$$ dan $...$ lebih dulu
                const protectedText = rawText.replace(/(\$\$[\s\S]+?\$\$|\$[^$\n]+?\$)/g, function(match) {
                    const token = `@@MATH_TOKEN_${mathTokens.length}@@`;
                    mathTokens.push(match);
                    return token;
                });

                // 2. Escape karakter HTML berbahaya di luar rumus
                let safeHtml = protectedText
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');

                // 3. Kembalikan tag format teks yang aman (whitelist)
                safeHtml = safeHtml.replace(/&lt;(\/?(b|i|u|sub|sup|strong|em))&gt;/gi, '<$1>');

                // 4. Konversi baris baru menjadi <br>
                safeHtml = safeHtml.replace(/\n/g, '<br>');

                // 5. Kembalikan blok rumus matematika ke posisi semula
                safeHtml = safeHtml.replace(/@@MATH_TOKEN_(\d+)@@/g, function(_, idx) {
                    return mathTokens[Number(idx)] ?? '';
                });

                return safeHtml;
            }

            function renderAllRichTextAndKatex() {
                document.querySelectorAll('.math-render-area').forEach(el => {
                    if (el.dataset.richFormatted !== '1') {
                        const rawText = el.textContent || '';
                        el.innerHTML = buildSafeRichHtmlWithMathTokens(rawText);
                        el.dataset.richFormatted = '1';
                    }

                    if (el.dataset.katexRendered !== '1' && typeof renderMathInElement === 'function') {
                        if ((el.textContent || '').includes('$')) {
                            renderMathInElement(el, {
                                delimiters: [
                                    { left: '$$', right: '$$', display: true },
                                    { left: '$', right: '$', display: false }
                                ],
                                throwOnError: false
                            });
                        }
                        el.dataset.katexRendered = '1';
                    }
                });
            }

            window.addEventListener('load', renderAllRichTextAndKatex);
            renderAllRichTextAndKatex();
            
            const isOpenBook = {{ $uji->open_book ? 'true' : 'false' }};
            let isUjianDimulai = false;
            let isLockedGagal = false;
            let isSubmittingFinal = false;

            const initialRemainingSeconds = {{ $sisaDetikAwal !== null ? (int) $sisaDetikAwal : 'null' }};
            const clientPageLoadTimestamp = Date.now();

            const docEl = document.documentElement;
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            const supportsFullscreen = !isIOS && Boolean(
                (docEl.requestFullscreen || docEl.webkitRequestFullscreen || docEl.msRequestFullscreen) &&
                (document.fullscreenEnabled || document.webkitFullscreenEnabled || document.msFullscreenEnabled)
            );

            if (!supportsFullscreen) {
                if (btnFullscreen) {
                    btnFullscreen.classList.add('d-none');
                }
                if (btnKembaliUjian) {
                    btnKembaliUjian.innerHTML = '<i class="fas fa-play me-2"></i> Lanjutkan Ujian';
                }
            }

            // --- SCREEN WAKE LOCK (MENCEGAH LAYAR ANDROID SLEEP SAAT UJIAN) ---
            let wakeLockSentinel = null;
            let fallbackVideoEl = null;

            function startFallbackNoSleepVideo() {
                try {
                    if (!fallbackVideoEl) {
                        const canvas = document.createElement('canvas');
                        canvas.width = 2;
                        canvas.height = 2;
                        const ctx = canvas.getContext('2d');
                        if (ctx) {
                            ctx.fillStyle = '#000000';
                            ctx.fillRect(0, 0, 2, 2);
                        }
                        if (typeof canvas.captureStream === 'function') {
                            fallbackVideoEl = document.createElement('video');
                            fallbackVideoEl.setAttribute('playsinline', '');
                            fallbackVideoEl.setAttribute('muted', '');
                            fallbackVideoEl.muted = true;
                            fallbackVideoEl.loop = true;
                            fallbackVideoEl.style.position = 'fixed';
                            fallbackVideoEl.style.width = '1px';
                            fallbackVideoEl.style.height = '1px';
                            fallbackVideoEl.style.opacity = '0.01';
                            fallbackVideoEl.style.pointerEvents = 'none';
                            fallbackVideoEl.style.zIndex = '-1';
                            fallbackVideoEl.srcObject = canvas.captureStream(1);
                            document.body.appendChild(fallbackVideoEl);
                        }
                    }
                    if (fallbackVideoEl && fallbackVideoEl.paused) {
                        fallbackVideoEl.play().catch(() => {});
                    }
                } catch (e) {
                    // Abaikan jika perangkat membatasi canvas stream
                }
            }

            async function requestScreenWakeLock() {
                if (!isUjianDimulai || isLockedGagal || isSubmittingFinal) return;

                if ('wakeLock' in navigator) {
                    try {
                        if (!wakeLockSentinel || wakeLockSentinel.released) {
                            wakeLockSentinel = await navigator.wakeLock.request('screen');
                            wakeLockSentinel.addEventListener('release', () => {
                                wakeLockSentinel = null;
                            });
                        }
                        return;
                    } catch (err) {
                        // Jika ditolak oleh sistem/webview, jalankan fallback video hening
                    }
                }

                startFallbackNoSleepVideo();
            }

            function releaseScreenWakeLock() {
                if (wakeLockSentinel) {
                    wakeLockSentinel.release().catch(() => {});
                    wakeLockSentinel = null;
                }
                if (fallbackVideoEl) {
                    try {
                        fallbackVideoEl.pause();
                        fallbackVideoEl.srcObject = null;
                        fallbackVideoEl.remove();
                    } catch (e) {}
                    fallbackVideoEl = null;
                }
            }

            function getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            }

            function isSedangFullscreen() {
                return Boolean(
                    document.fullscreenElement ||
                    document.webkitFullscreenElement ||
                    document.msFullscreenElement
                );
            }

            function lampirkanJawabanEsaiKeFormFinalisasi() {
                const formFinal = document.getElementById('form-finalisasi');
                if (!formFinal) return;

                formFinal.querySelectorAll('.hidden-esai-final').forEach(el => el.remove());

                document.querySelectorAll('.text-area-esai').forEach(ta => {
                    const sId = ta.dataset.soalId;
                    if (!sId) return;
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `jawaban_esai[${sId}]`;
                    hiddenInput.value = ta.value;
                    hiddenInput.className = 'hidden-esai-final';
                    formFinal.appendChild(hiddenInput);
                });
            }

            function submitFinalisasiOtomatis() {
                if (isSubmittingFinal || isLockedGagal) return;
                isSubmittingFinal = true;
                releaseScreenWakeLock();
                lampirkanJawabanEsaiKeFormFinalisasi();
                document.getElementById('form-finalisasi').submit();
            }

            // --- 1. TOGGLE TOMBOL MULAI PERSIAPAN ---
            checkSetuju.addEventListener('change', function() {
                if(this.checked) {
                    btnMulaiUji.removeAttribute('disabled');
                    btnMulaiUji.classList.remove('btn-secondary');
                    btnMulaiUji.style.backgroundColor = '#6f42c1';
                } else {
                    btnMulaiUji.setAttribute('disabled', 'true');
                    btnMulaiUji.classList.add('btn-secondary');
                    btnMulaiUji.style.backgroundColor = '';
                }
            });

            // --- 2. FUNGSI FULLSCREEN ---
            function mintaFullscreen() {
                if (isLockedGagal || !supportsFullscreen) return;
                if (!isSedangFullscreen()) {
                    if (docEl.requestFullscreen) {
                        docEl.requestFullscreen().catch(err => console.log("Fullscreen restriction:", err));
                    } else if (docEl.webkitRequestFullscreen) {
                        docEl.webkitRequestFullscreen();
                    } else if (docEl.msRequestFullscreen) {
                        docEl.msRequestFullscreen();
                    }
                }
            }

            btnFullscreen?.addEventListener('click', function() {
                mintaFullscreen();
                requestScreenWakeLock();
            });

            // --- 3. KLIK MULAI UJIAN ---
            btnMulaiUji.addEventListener('click', function() {
                if (!isOpenBook && supportsFullscreen) {
                    mintaFullscreen();
                }

                secPersiapan.classList.add('d-none');
                secUjian.classList.remove('d-none');
                isUjianDimulai = true;

                renderAllRichTextAndKatex();
                requestScreenWakeLock();
                mulaiTimer();
                updateGridStatus();
                highlightRadioSelection();
            });

            // --- 4. SYSTEM DETEKSI PELANGGARAN STRICT MODE ---
            let sedangMelapor = false;
            let waktuPelanggaranTerakhir = 0;
            const jedaPelanggaranMs = 2500;

            const modalPelanggaranEl = document.getElementById('modalPelanggaran');
            const bsModalPelanggaran = new bootstrap.Modal(modalPelanggaranEl);
            
            const modalGagalEl = document.getElementById('modalGagal');
            const bsModalGagal = new bootstrap.Modal(modalGagalEl);
            
            const btnKeluarPortal = document.getElementById('btnKeluarPortal');

            function kunciFormUjian() {
                isLockedGagal = true;
                releaseScreenWakeLock();
                document.querySelectorAll('input, button, select, textarea').forEach(el => {
                    if (el.id !== 'btnKeluarPortal') {
                        el.disabled = true;
                    }
                });
            }

            function laporkanPelanggaran(tipeSpesifik = '') {
                if (isOpenBook) return;

                const sekarang = new Date().getTime();

                if (!isUjianDimulai || isSubmittingFinal || isLockedGagal || sedangMelapor || (sekarang - waktuPelanggaranTerakhir < jedaPelanggaranMs)) {
                    return;
                }

                sedangMelapor = true;
                waktuPelanggaranTerakhir = sekarang;

                fetch("{{ route('sba.uji.pelanggaran', $uji->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ tipe: tipeSpesifik })
                })
                .then(res => res.json())
                .then(data => {
                    sedangMelapor = false;
                    
                    if (data.status === 'gagal') {
                        kunciFormUjian();
                        document.getElementById('text-pesan-gagal').innerText = data.message;
                        bsModalPelanggaran.hide();
                        bsModalGagal.show();
                    } else if (data.status === 'warning') {
                        document.getElementById('text-pesan-pelanggaran').innerText = data.message;
                        document.getElementById('text-sisa-toleransi').innerText = data.sisa_toleransi;
                        bsModalPelanggaran.show();
                    }
                })
                .catch(() => { 
                    sedangMelapor = false; 
                });
            }

            btnKembaliUjian.addEventListener('click', function() {
                if (isLockedGagal) return;
                bsModalPelanggaran.hide();
                if (!isOpenBook && supportsFullscreen) {
                    mintaFullscreen();
                }
                requestScreenWakeLock();
            });

            btnKeluarPortal.addEventListener('click', function() {
                releaseScreenWakeLock();
                window.location.href = "{{ route('sba.portal') }}";
            });

            // Aktifkan kembali Wake Lock saat siswa kembali ke halaman ujian (berlaku untuk Open Book maupun Strict Mode)
            document.addEventListener("visibilitychange", function() {
                if (document.hidden) {
                    if (!isOpenBook) {
                        laporkanPelanggaran('pindah_tab');
                    }
                } else {
                    requestScreenWakeLock();
                }
            });

            // Pastikan Wake Lock tetap aktif saat peserta menyentuh layar / mengganti soal
            document.addEventListener('pointerdown', function() {
                if (isUjianDimulai && (!wakeLockSentinel || wakeLockSentinel.released)) {
                    requestScreenWakeLock();
                }
            }, { passive: true });

            if (!isOpenBook) {
                if (supportsFullscreen) {
                    window.addEventListener('blur', function() {
                        if (!document.hidden) {
                            laporkanPelanggaran('loss_focus');
                        }
                    });

                    document.addEventListener("fullscreenchange", function() {
                        if (!isSedangFullscreen() && isUjianDimulai && !document.hidden) {
                            laporkanPelanggaran('keluar_fullscreen');
                        } else if (isSedangFullscreen()) {
                            requestScreenWakeLock();
                        }
                    });

                    document.addEventListener("webkitfullscreenchange", function() {
                        if (!isSedangFullscreen() && isUjianDimulai && !document.hidden) {
                            laporkanPelanggaran('keluar_fullscreen');
                        } else if (isSedangFullscreen()) {
                            requestScreenWakeLock();
                        }
                    });
                }
            }

            document.getElementById('form-finalisasi')?.addEventListener('submit', function(e) {
                if (isLockedGagal) {
                    e.preventDefault();
                    alert('Ujian Anda telah dihentikan oleh sistem.');
                    return;
                }
                isSubmittingFinal = true;
                releaseScreenWakeLock();
                lampirkanJawabanEsaiKeFormFinalisasi();
            });

            // --- 5. LOGIKA NAVIGASI & PENGECEKAN FULLSCREEN SETIAP GANTI SOAL ---
            let currentIndex = 0;
            const totalSoal = {{ count($soals) }};
            const panes = document.querySelectorAll('.soal-item-pane');
            const gridBtns = document.querySelectorAll('.nav-grid-btn');

            function switchSoal(targetIndex) {
                if (isLockedGagal) return;

                if (!isOpenBook && supportsFullscreen && isUjianDimulai && !isSedangFullscreen()) {
                    laporkanPelanggaran('ganti_soal_non_fullscreen');
                }

                panes[currentIndex].classList.add('d-none');
                gridBtns[currentIndex].classList.remove('active-grid');

                currentIndex = targetIndex;

                panes[currentIndex].classList.remove('d-none');
                gridBtns[currentIndex].classList.add('active-grid');
            }

            gridBtns.forEach((btn, idx) => btn.addEventListener('click', () => switchSoal(idx)));
            document.querySelectorAll('.btn-next').forEach(btn => btn.addEventListener('click', () => { if(currentIndex < totalSoal - 1) switchSoal(currentIndex + 1); }));
            document.querySelectorAll('.btn-prev').forEach(btn => btn.addEventListener('click', () => { if(currentIndex > 0) switchSoal(currentIndex - 1); }));

            function highlightRadioSelection() {
                document.querySelectorAll('.option-card').forEach(card => {
                    const input = card.querySelector('input');
                    if(input && input.checked) {
                        card.classList.add('selected-option');
                    } else {
                        card.classList.remove('selected-option');
                    }
                });

                document.querySelectorAll('.bs-statement-row').forEach(row => {
                    const checkedBs = row.querySelector('.radio-bs:checked');
                    row.classList.toggle('bs-answered', Boolean(checkedBs));

                    row.querySelectorAll('.radio-bs').forEach(radio => {
                        const icon = radio.nextElementSibling?.querySelector('.bs-radio-icon');
                        if (!icon) return;
                        if (radio.checked) {
                            icon.classList.remove('far', 'fa-circle');
                            icon.classList.add('fas', 'fa-dot-circle');
                        } else {
                            icon.classList.remove('fas', 'fa-dot-circle');
                            icon.classList.add('far', 'fa-circle');
                        }
                    });
                });
            }

            function collectBenarSalahValues(soalId) {
                const container = document.querySelector(`.bs-statements-container[data-soal-id="${soalId}"]`);
                if (!container) return null;

                const totalStatements = parseInt(container.dataset.totalStatements || '0', 10);
                const result = [];
                let anyAnswered = false;

                for (let i = 0; i < totalStatements; i++) {
                    const checked = container.querySelector(`input[name="bs_${soalId}_${i}"]:checked`);
                    if (checked) {
                        result.push(checked.value);
                        anyAnswered = true;
                    } else {
                        result.push('');
                    }
                }

                return anyAnswered ? result : null;
            }

            function simpanKeServer(soalId, jawabanVal, raguVal) {
                if (isLockedGagal || isSubmittingFinal) return;

                fetch("{{ route('sba.uji.simpan', $uji->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        uji_soal_id: soalId,
                        jawaban: jawabanVal,
                        ragu_ragu: raguVal ? 1 : 0
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.status === 'expired') {
                        submitFinalisasiOtomatis();
                        return;
                    }
                    updateGridStatus();
                })
                .catch(() => updateGridStatus());
            }

            document.querySelectorAll('.radio-jawaban').forEach(r => {
                r.addEventListener('change', function() {
                    highlightRadioSelection();
                    const sId = this.dataset.soalId;
                    const ragu = document.getElementById(`ragu_${sId}`).checked;
                    simpanKeServer(sId, this.value, ragu);
                });
            });

            document.querySelectorAll('.checkbox-jawaban').forEach(c => {
                c.addEventListener('change', function() {
                    highlightRadioSelection();
                    const sId = this.dataset.soalId;
                    let vals = [];
                    document.querySelectorAll(`input[name="jawaban_${sId}[]"]:checked`).forEach(b => vals.push(b.value));
                    const ragu = document.getElementById(`ragu_${sId}`).checked;
                    simpanKeServer(sId, vals, ragu);
                });
            });

            document.querySelectorAll('.radio-bs').forEach(rb => {
                rb.addEventListener('change', function() {
                    highlightRadioSelection();
                    updateGridStatus();
                    const sId = this.dataset.soalId;
                    const vals = collectBenarSalahValues(sId);
                    const ragu = document.getElementById(`ragu_${sId}`).checked;
                    simpanKeServer(sId, vals, ragu);
                });
            });

            document.querySelectorAll('.text-area-esai').forEach(t => {
                t.addEventListener('blur', function() {
                    const sId = this.dataset.soalId;
                    const ragu = document.getElementById(`ragu_${sId}`).checked;
                    simpanKeServer(sId, this.value, ragu);
                });
            });

            document.querySelectorAll('.check-ragu').forEach(chk => {
                chk.addEventListener('change', function() {
                    const sId = this.dataset.soalId;
                    const pane = this.closest('.soal-item-pane');
                    const tipe = pane.dataset.soalTipe;
                    let currentVal = null;

                    if (tipe === 'benar_salah') {
                        currentVal = collectBenarSalahValues(sId);
                    } else {
                        const r = pane.querySelector('.radio-jawaban:checked');
                        if(r) currentVal = r.value;
                        const cb = pane.querySelectorAll('.checkbox-jawaban:checked');
                        if(cb.length > 0) { currentVal = []; cb.forEach(b => currentVal.push(b.value)); }
                        const es = pane.querySelector('.text-area-esai');
                        if(es && es.value) currentVal = es.value;
                    }

                    simpanKeServer(sId, currentVal, this.checked);
                });
            });

            function updateGridStatus() {
                let terjawabCount = 0;
                panes.forEach((pane, idx) => {
                    const btnGrid = document.getElementById(`grid_btn_${idx}`);
                    const isRagu = pane.querySelector('.check-ragu').checked;
                    const tipe = pane.dataset.soalTipe;
                    const sId = pane.dataset.soalId;

                    let hasFullAnswer = false;
                    let hasPartialAnswer = false;

                    if (tipe === 'benar_salah') {
                        const totalStatements = pane.querySelectorAll('.bs-statement-row').length;
                        const answeredStatements = pane.querySelectorAll('.radio-bs:checked').length;

                        const badgeProgress = pane.querySelector(`.bs-progress-badge[data-soal-id="${sId}"]`);
                        if (badgeProgress) {
                            badgeProgress.innerText = `Terjawab: ${answeredStatements} / ${totalStatements} pernyataan`;
                        }

                        if (totalStatements > 0 && answeredStatements === totalStatements) {
                            hasFullAnswer = true;
                        } else if (answeredStatements > 0) {
                            hasPartialAnswer = true;
                        }
                    } else {
                        if(pane.querySelector('.radio-jawaban:checked')) hasFullAnswer = true;
                        if(pane.querySelectorAll('.checkbox-jawaban:checked').length > 0) hasFullAnswer = true;
                        const es = pane.querySelector('.text-area-esai');
                        if(es && es.value.trim() !== '') hasFullAnswer = true;
                    }

                    btnGrid.className = "nav-grid-btn " + (idx === currentIndex ? "active-grid " : "");
                    if(isRagu) {
                        btnGrid.classList.add('btn-ragu');
                    } else if(hasFullAnswer) {
                        btnGrid.classList.add('btn-answered');
                        terjawabCount++;
                    } else if(hasPartialAnswer) {
                        btnGrid.classList.add('btn-partial');
                    }
                });

                document.getElementById('stat-terjawab').innerText = terjawabCount;
                document.getElementById('stat-belum').innerText = totalSoal - terjawabCount;
            }

            updateGridStatus();
            highlightRadioSelection();

            // --- 6. TIMER ---
            function renderCountdown(timeLeft) {
                let safeSeconds = Math.max(0, timeLeft);
                let hours = Math.floor(safeSeconds / 3600);
                let minutes = Math.floor((safeSeconds % 3600) / 60);
                let seconds = safeSeconds % 60;

                document.getElementById('timer-countdown').innerText = 
                    String(hours).padStart(2, '0') + ":" + 
                    String(minutes).padStart(2, '0') + ":" + 
                    String(seconds).padStart(2, '0');
            }

            function mulaiTimer() {
                if (initialRemainingSeconds !== null) {
                    renderCountdown(initialRemainingSeconds);

                    let x = setInterval(function() {
                        let elapsedSeconds = Math.floor((Date.now() - clientPageLoadTimestamp) / 1000);
                        let timeLeft = initialRemainingSeconds - elapsedSeconds;

                        if (timeLeft <= 0) {
                            renderCountdown(0);
                            clearInterval(x);
                            submitFinalisasiOtomatis();
                        } else {
                            renderCountdown(timeLeft);
                        }
                    }, 1000);
                } else {
                    document.getElementById('timer-countdown').innerText = "Tanpa Batas";
                }
            }
        });
    </script>
</body>
</html>