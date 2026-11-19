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
    
    <style>
        :root {
            --sba-primary: #5e72e4;
            --sba-secondary: #8392ab;
            --sba-dark: #172b4d;
            --sba-light: #f7fafc;
            --sba-purple: #6f42c1;
        }

        body {
            background-color: #f4f5f9;
            font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            user-select: none;
            -webkit-user-select: none;
            min-height: 100vh;
        }

        /* Header Navbar Modern */
        .navbar-cbt {
            background: linear-gradient(135deg, #11cdef 0%, #1171ef 100%);
            box-shadow: 0 4px 15px rgba(17, 113, 239, 0.15);
        }

        /* Card Styling */
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background: #ffffff;
        }

        .header-gradient {
            background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%);
            color: white;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        /* Styling Media / Gambar Soal */
        .soal-media-wrapper {
            background-color: #f8f9fe;
            border: 1px solid #e9ecef;
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

        /* Custom Radio & Checkbox Opsi Jawaban */
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
            border-color: #5e72e4;
            background-color: #f8f9fe;
            transform: translateY(-1px);
        }

        .option-card.selected-option {
            border-color: #5e72e4 !important;
            background-color: #f4f5ff !important;
            box-shadow: 0 3px 10px rgba(94, 114, 228, 0.12);
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
            background-color: #5e72e4;
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
            border-color: #5e72e4;
            color: #5e72e4;
        }

        .nav-grid-btn.active-grid {
            border-color: #5e72e4 !important;
            box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.25);
        }

        .nav-grid-btn.btn-answered {
            background-color: #2dce89 !important;
            border-color: #2dce89 !important;
            color: #ffffff !important;
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
                                <span class="text-muted small"><i class="fas fa-user text-primary me-2"></i>Nama Peserta:</span>
                                <span class="fw-bold text-dark">{{ $namaPeserta }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                <span class="text-muted small"><i class="fas fa-book-open text-primary me-2"></i>Judul Asesmen:</span>
                                <span class="fw-bold text-dark text-end">{{ $uji->judul }}</span>
                            </div>
                            <div class="d-flex justify-content-between {{ $uji->waktu_selesai ? 'mb-2 border-bottom pb-2' : '' }}">
                                <span class="text-muted small"><i class="fas fa-hourglass-half text-primary me-2"></i>Durasi Ujian:</span>
                                <span class="fw-bold text-primary">
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
                            <div class="alert alert-info border-0 rounded-3 p-3 mb-4 bg-opacity-10" style="background-color: #e3f2fd;">
                                <div class="d-flex align-items-center text-primary small">
                                    <i class="fas fa-history fs-5 me-3"></i>
                                    <div>
                                        <strong>Melanjutkan Sesi Sebelumnya:</strong> Sebanyak <strong>{{ $jawabanTersimpan->count() }} jawaban</strong> Anda telah dipulihkan otomatis. Sisa waktu mengikuti waktu mulai awal dan jadwal ujian.
                                    </div>
                                </div>
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
                                <i class="fas fa-exclamation-triangle me-2"></i>Peraturan Strict Mode & Keamanan
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
        
        <!-- NAVBAR HEAD -->
        <nav class="navbar navbar-expand navbar-cbt px-4 py-3 shadow-sm sticky-top">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <div class="bg-white text-primary rounded-circle p-2 me-3 d-none d-md-block" style="width:40px; height:40px; text-align:center;">
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
                        <i class="fas fa-expand text-primary"></i>
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
                                $savedArr = ($soal->tipe === 'pg_kompleks' && $savedVal) ? (json_decode($savedVal, true) ?? []) : [];
                            @endphp

                            <div class="card card-custom soal-item-pane mb-4 {{ $index === 0 ? '' : 'd-none' }}" data-index="{{ $index }}" data-soal-id="{{ $soal->id }}">
                                
                                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 rounded-pill" style="font-size: 0.85rem;">
                                        Soal Nomor {{ $index + 1 }}
                                    </span>
                                    <span class="text-muted small">Bobot: <strong class="text-dark">{{ $soal->bobot_nilai }} Poin</strong></span>
                                </div>
                                
                                <div class="card-body p-4">
                                    
                                    {{-- 1. MEDIA GAMBAR SOAL (DITAMPILKAN SEBELUM TEKS SOAL) --}}
                                    @if(!empty($soal->media))
                                        <div class="soal-media-wrapper">
                                            <img src="{{ asset('storage/' . $soal->media) }}" alt="Gambar Soal {{ $index + 1 }}" class="soal-media-img">
                                        </div>
                                    @endif

                                    {{-- 2. TEKS PERTANYAAN SOAL --}}
                                    <div class="mb-4 text-dark fs-6" style="line-height: 1.7; font-weight: 500;">
                                        {!! nl2br(e($soal->pertanyaan)) !!}
                                    </div>

                                    {{-- 3. OPSI JAWABAN (DENGAN PEMULIHAN JAWABAN TERSIMPAN) --}}
                                    <div class="options-container">
                                        @if($soal->tipe === 'pg')
                                            @foreach($soal->opsi_jawaban as $optIndex => $opsiTeks)
                                                @if(!empty(trim($opsiTeks)))
                                                    @php
                                                        $isChecked = ($savedVal !== null && (string) $savedVal === (string) $optIndex);
                                                    @endphp
                                                    <div class="option-card d-flex align-items-center {{ $isChecked ? 'selected-option' : '' }}">
                                                        <input class="form-check-input d-none radio-jawaban" type="radio" name="jawaban_{{ $soal->id }}" value="{{ $optIndex }}" id="opt_{{ $soal->id }}_{{ $optIndex }}" data-soal-id="{{ $soal->id }}" {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="w-100 d-flex align-items-center m-0 cursor-pointer text-dark" for="opt_{{ $soal->id }}_{{ $optIndex }}">
                                                            <div class="option-badge">{{ chr(65 + $optIndex) }}</div>
                                                            <div class="flex-grow-1">{{ $opsiTeks }}</div>
                                                        </label>
                                                    </div>
                                                @endif
                                            @endforeach

                                        @elseif($soal->tipe === 'pg_kompleks')
                                            <div class="alert alert-info py-2 px-3 text-xs mb-3 border-0 bg-info bg-opacity-10 text-info fw-bold">
                                                <i class="fas fa-info-circle me-1"></i> Jawaban kompleks: Pilih satu atau lebih pilihan yang menurut Anda benar.
                                            </div>
                                            @foreach($soal->opsi_jawaban as $optIndex => $opsiTeks)
                                                @if(!empty(trim($opsiTeks)))
                                                    @php
                                                        $isChecked = in_array((string) $optIndex, array_map('strval', $savedArr));
                                                    @endphp
                                                    <div class="option-card d-flex align-items-center {{ $isChecked ? 'selected-option' : '' }}">
                                                        <input class="form-check-input me-3 checkbox-jawaban" type="checkbox" name="jawaban_{{ $soal->id }}[]" value="{{ $optIndex }}" id="opt_{{ $soal->id }}_{{ $optIndex }}" data-soal-id="{{ $soal->id }}" style="transform: scale(1.2);" {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="w-100 d-flex align-items-center m-0 cursor-pointer text-dark" for="opt_{{ $soal->id }}_{{ $optIndex }}">
                                                            <div class="option-badge">{{ chr(65 + $optIndex) }}</div>
                                                            <div class="flex-grow-1">{{ $opsiTeks }}</div>
                                                        </label>
                                                    </div>
                                                @endif
                                            @endforeach

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
                                        <button type="button" class="btn btn-primary px-4 rounded-3 btn-next" style="background-color: #5e72e4; border-color: #5e72e4;">
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
                                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-th me-2 text-primary"></i> Navigasi Soal</h6>
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
                                <div class="d-flex justify-content-between text-xs text-muted px-2">
                                    <div><span class="badge bg-success rounded-circle p-1 me-1">&nbsp;</span> Terjawab</div>
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
            
            const isOpenBook = {{ $uji->open_book ? 'true' : 'false' }};
            let isUjianDimulai = false;
            let isLockedGagal = false; // Flag pengunci jika diskualifikasi
            let isSubmittingFinal = false;

            // Sinkronisasi sisa waktu langsung dari waktu server saat halaman dimuat
            const initialRemainingSeconds = {{ $sisaDetikAwal !== null ? (int) $sisaDetikAwal : 'null' }};
            const clientPageLoadTimestamp = Date.now();

            // Deteksi perangkat iOS (iPhone / iPad) & dukungan Fullscreen API browser
            const docEl = document.documentElement;
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            const supportsFullscreen = !isIOS && Boolean(
                (docEl.requestFullscreen || docEl.webkitRequestFullscreen || docEl.msRequestFullscreen) &&
                (document.fullscreenEnabled || document.webkitFullscreenEnabled || document.msFullscreenEnabled)
            );

            // Penyesuaian UI untuk perangkat non-fullscreen (seperti iPhone/iPad)
            if (!supportsFullscreen) {
                if (btnFullscreen) {
                    btnFullscreen.classList.add('d-none');
                }
                if (btnKembaliUjian) {
                    btnKembaliUjian.innerHTML = '<i class="fas fa-play me-2"></i> Lanjutkan Ujian';
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

            function submitFinalisasiOtomatis() {
                if (isSubmittingFinal || isLockedGagal) return;
                isSubmittingFinal = true;
                document.getElementById('form-finalisasi').submit();
            }

            // --- 1. TOGGLE TOMBOL MULAI PERSIAPAN ---
            checkSetuju.addEventListener('change', function() {
                if(this.checked) {
                    btnMulaiUji.removeAttribute('disabled');
                    btnMulaiUji.classList.remove('btn-secondary');
                    btnMulaiUji.style.backgroundColor = '#5e72e4';
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

            btnFullscreen?.addEventListener('click', mintaFullscreen);

            // --- 3. KLIK MULAI UJIAN ---
            btnMulaiUji.addEventListener('click', function() {
                if (!isOpenBook && supportsFullscreen) {
                    mintaFullscreen();
                }

                secPersiapan.classList.add('d-none');
                secUjian.classList.remove('d-none');
                isUjianDimulai = true;

                mulaiTimer();
                updateGridStatus();
                highlightRadioSelection();
            });

            // --- 4. SYSTEM DETEKSI PELANGGARAN STRICT MODE ---
            let sedangMelapor = false;
            let waktuPelanggaranTerakhir = 0;
            const jedaPelanggaranMs = 2500; // Cooldown anti-cascading

            const modalPelanggaranEl = document.getElementById('modalPelanggaran');
            const bsModalPelanggaran = new bootstrap.Modal(modalPelanggaranEl);
            
            const modalGagalEl = document.getElementById('modalGagal');
            const bsModalGagal = new bootstrap.Modal(modalGagalEl);
            
            const btnKeluarPortal = document.getElementById('btnKeluarPortal');

            function kunciFormUjian() {
                isLockedGagal = true;
                document.querySelectorAll('input, button, select, textarea').forEach(el => {
                    if (el.id !== 'btnKeluarPortal') {
                        el.disabled = true;
                    }
                });
            }

            function laporkanPelanggaran(tipeSpesifik = '') {
                // Abaikan seluruh pelanggaran jika mode Open Book aktif
                if (isOpenBook) return;

                const sekarang = new Date().getTime();

                // Jangan laporkan jika ujian belum mulai, sedang finalisasi, sedang gagal/terkunci, sedang melapor, atau masih dalam jeda cooldown
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
            });

            btnKeluarPortal.addEventListener('click', function() {
                window.location.href = "{{ route('sba.portal') }}";
            });

            // Event Listeners Pelanggaran (Hanya berjalan jika BUKAN mode Open Book)
            if (!isOpenBook) {
                // Berlaku di semua perangkat (PC, Android, maupun iPhone/iPad) saat pindah tab / aplikasi / Home Screen
                document.addEventListener("visibilitychange", function() {
                    if (document.hidden) {
                        laporkanPelanggaran('pindah_tab');
                    }
                });

                // Khusus perangkat desktop / browser yang mendukung fullscreen penuh
                if (supportsFullscreen) {
                    window.addEventListener('blur', function() {
                        if (!document.hidden) {
                            laporkanPelanggaran('loss_focus');
                        }
                    });

                    document.addEventListener("fullscreenchange", function() {
                        if (!isSedangFullscreen() && isUjianDimulai && !document.hidden) {
                            laporkanPelanggaran('keluar_fullscreen');
                        }
                    });

                    document.addEventListener("webkitfullscreenchange", function() {
                        if (!isSedangFullscreen() && isUjianDimulai && !document.hidden) {
                            laporkanPelanggaran('keluar_fullscreen');
                        }
                    });
                }
            }

            // Mencegat submit manual finalisasi jika user terkena diskualifikasi
            document.getElementById('form-finalisasi')?.addEventListener('submit', function(e) {
                if (isLockedGagal) {
                    e.preventDefault();
                    alert('Ujian Anda telah dihentikan oleh sistem.');
                    return;
                }
                isSubmittingFinal = true;
            });

            // --- 5. LOGIKA NAVIGASI & PENGECEKAN FULLSCREEN SETIAP GANTI SOAL ---
            let currentIndex = 0;
            const totalSoal = {{ count($soals) }};
            const panes = document.querySelectorAll('.soal-item-pane');
            const gridBtns = document.querySelectorAll('.nav-grid-btn');

            function switchSoal(targetIndex) {
                if (isLockedGagal) return;

                // PENGECEKAN FULLSCREEN SETIAP PERPINDAHAN SOAL
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

            // Highlight visual pilihan jawaban PG
            function highlightRadioSelection() {
                document.querySelectorAll('.option-card').forEach(card => {
                    const input = card.querySelector('input');
                    if(input && input.checked) {
                        card.classList.add('selected-option');
                    } else {
                        card.classList.remove('selected-option');
                    }
                });
            }

            // AJAX Simpan Jawaban
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
                    let currentVal = null;
                    const r = pane.querySelector('.radio-jawaban:checked');
                    if(r) currentVal = r.value;
                    const cb = pane.querySelectorAll('.checkbox-jawaban:checked');
                    if(cb.length > 0) { currentVal = []; cb.forEach(b => currentVal.push(b.value)); }
                    const es = pane.querySelector('.text-area-esai');
                    if(es && es.value) currentVal = es.value;

                    simpanKeServer(sId, currentVal, this.checked);
                });
            });

            function updateGridStatus() {
                let terjawabCount = 0;
                panes.forEach((pane, idx) => {
                    const btnGrid = document.getElementById(`grid_btn_${idx}`);
                    const isRagu = pane.querySelector('.check-ragu').checked;

                    let hasAnswer = false;
                    if(pane.querySelector('.radio-jawaban:checked')) hasAnswer = true;
                    if(pane.querySelectorAll('.checkbox-jawaban:checked').length > 0) hasAnswer = true;
                    const es = pane.querySelector('.text-area-esai');
                    if(es && es.value.trim() !== '') hasAnswer = true;

                    btnGrid.className = "nav-grid-btn " + (idx === currentIndex ? "active-grid " : "");
                    if(isRagu) {
                        btnGrid.classList.add('btn-ragu');
                    } else if(hasAnswer) {
                        btnGrid.classList.add('btn-answered');
                        terjawabCount++;
                    }
                });

                document.getElementById('stat-terjawab').innerText = terjawabCount;
                document.getElementById('stat-belum').innerText = totalSoal - terjawabCount;
            }

            // --- 6. TIMER (MENGIKUTI BATAS TERCEPAT: DURASI UJIAN VS JADWAL SELESAI) ---
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