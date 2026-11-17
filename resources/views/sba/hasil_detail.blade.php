@extends('layouts.app')

@section('page-title', 'Detail Penilaian Ujian - ' . $hasil->uji->judul)

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <style>
        .card-custom {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .option-box {
            border: 1.5px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 8px;
            background-color: #fff;
        }
        .option-correct {
            border-color: #2dce89 !important;
            background-color: #f0fdf4 !important;
        }
        .option-wrong {
            border-color: #f5365c !important;
            background-color: #fff5f5 !important;
        }
        .option-chosen-neutral {
            border-color: #5e72e4 !important;
            background-color: #f4f5f7 !important;
        }
        .badge-kunci {
            background-color: #2dce89;
            color: #fff;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .badge-jawaban-siswa {
            background-color: #5e72e4;
            color: #fff;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 4px;
        }
        @media (min-width: 992px) {
            .sticky-summary {
                position: sticky;
                top: 90px;
                z-index: 100;
            }
        }
    </style>

    <div class="container-fluid py-4 px-4">
        
        {{-- ALERT SUKSES / ERROR --}}
        @if (session('success'))
            <div class="alert bg-gradient-success alert-dismissible text-white fade show mb-4" role="alert">
                <span class="text-sm"><strong>Sukses!</strong> {{ session('success') }}</span>
                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert bg-gradient-danger alert-dismissible text-white fade show mb-4" role="alert">
                <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
            </div>
        @endif

        {{-- HEADER BANNER & IDENTITAS ASESI --}}
        <div class="card card-custom mb-4 border">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="text-white mb-0"><i class="fas fa-user-check me-2"></i> Detail & Lembar Penilaian Asesi</h6>
                        <p class="text-white text-xs opacity-8 mb-0 ms-4">
                            Ujian: <strong>{{ $hasil->uji->judul }}</strong> ({{ strtoupper($hasil->uji->kode_uji) }})
                        </p>
                    </div>
                    
                    <div class="d-flex gap-2">
                        {{-- TOMBOL EXPORT PDF LEMBAR ASESI --}}
                        <a href="{{ route('sba.hasil.detail.pdf', $hasil->id) }}" class="btn btn-white text-danger btn-sm mb-0 shadow-sm" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> Cetak / Export PDF
                        </a>

                        <a href="{{ route('sba.hasil.index', $hasil->uji_id) }}" class="btn btn-outline-white text-white btn-sm mb-0">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body px-4 py-3">
                <div class="row align-items-center g-3">
                    <div class="col-md-3 border-end">
                        <span class="text-xs text-secondary d-block">Nama Peserta / Asesi:</span>
                        <h6 class="mb-0 text-dark font-weight-bold">
                            {{ $hasil->asesi ? $hasil->asesi->name : ($hasil->nama_peserta_publik ?? 'Guest') }}
                        </h6>
                        <span class="text-xxs text-muted">ID: {{ $hasil->asesi_id ? 'USER #'.$hasil->asesi_id : 'Peserta Publik' }}</span>
                    </div>

                    <div class="col-md-3 border-end">
                        <span class="text-xs text-secondary d-block">Waktu & Durasi Pengerjaan:</span>
                        <span class="text-xs font-weight-bold text-dark d-block">
                            {{ $hasil->waktu_mulai ? $hasil->waktu_mulai->format('d/m/Y H:i') : '-' }}
                            @if($hasil->waktu_selesai)
                                - {{ $hasil->waktu_selesai->format('H:i') }}
                            @endif
                        </span>
                        <span class="text-xxs text-info">
                            <i class="far fa-clock me-1"></i> Durasi: 
                            <strong>{{ $hasil->durasi_formatted }}</strong>
                        </span>
                    </div>

                    <div class="col-md-2 border-end text-md-center">
                        <span class="text-xs text-secondary d-block">Percobaan / Pelanggaran:</span>
                        <span class="badge bg-light text-dark border text-xxs font-weight-bold">
                            Ke-{{ $hasil->percobaan_ke }}
                        </span>
                        <span class="badge {{ $hasil->jumlah_pelanggaran > 0 ? 'bg-danger' : 'bg-success' }} text-xxs ms-1">
                            {{ $hasil->jumlah_pelanggaran }} Pelanggaran
                        </span>
                    </div>

                    <div class="col-md-4 text-center">
                        <span class="text-xs text-secondary d-block">Total Skor Akhir:</span>
                        <h3 class="mb-0 text-primary font-weight-bolder" id="display-total-skor">
                            <span id="nilai-total-realtime">{{ number_format($hasil->total_skor, 0) }}</span> <span class="fs-6 text-muted">/ 100 Poin</span>
                        </h3>
                        @if($hasil->status_pengerjaan === 'final')
                            <span class="badge bg-gradient-primary text-xxs">Status: FINAL</span>
                        @elseif($hasil->status_pengerjaan === 'selesai' && $hasil->uji->butuhPenilaianManual())
                            <span class="badge bg-warning text-dark text-xxs">Status: SELESAI (Menunggu Penilaian Manual)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM HIDDEN UNTUK REFRESH NILAI OTOMATIS SESUAI BOBOT TERBARU --}}
        <form action="{{ route('sba.hasil.refresh_nilai', $hasil->id) }}" method="POST" id="formRefreshNilai" class="d-none">
            @csrf
        </form>

        {{-- FORM PENILAIAN MANUAL (ESAI & PG/PGK TANPA KUNCI) --}}
        <form action="{{ route('sba.hasil.simpan_nilai', $hasil->id) }}" method="POST" id="formPenilaian">
            @csrf
            
            @php
                $totalSkorOtomatis = 0;
                $hasManualQuestions = false;
            @endphp

            <div class="row">
                {{-- SIDEBAR AKSI & AUTO SCORING (Tampil di ATAS pada Mobile/Tablet, di KANAN pada Desktop) --}}
                <div class="col-lg-3 order-1 order-lg-2 mb-4 mb-lg-0">
                    <div class="card card-custom border sticky-summary">
                        <div class="card-header bg-light py-3 border-bottom">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark"><i class="fas fa-sliders-h me-2 text-primary"></i> Panel Akses Nilai</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="p-2 bg-light rounded border mb-3 text-xs">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-secondary">Skor Otomatis:</span>
                                    <strong class="text-dark" id="sidebar-skor-otomatis">{{ number_format($hasil->skor_otomatis ?? 0, 0) }} Poin</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary">Skor Manual:</span>
                                    <strong class="text-warning" id="sidebar-skor-manual">0 Poin</strong>
                                </div>
                            </div>

                            {{-- TOMBOL REFRESH NILAI SESUAI BOBOT SOAL TERBARU --}}
                            <button type="button" id="btnRefreshNilai" class="btn btn-outline-primary btn-sm w-100 mb-1">
                                <i class="fas fa-sync-alt me-1"></i> Refresh Nilai (Bobot Baru)
                            </button>
                            <small class="text-xxs text-muted d-block mb-3" style="line-height: 1.3;">
                                *Menghitung ulang skor PG/PG Kompleks mengikuti perubahan bobot soal terbaru.
                            </small>

                            <button type="button" id="btnAutoScoring" class="btn btn-outline-info btn-sm w-100 mb-1">
                                <i class="fas fa-magic me-1"></i> Auto-Scoring Penuh
                            </button>
                            <small class="text-xxs text-muted d-block mb-3" style="line-height: 1.3;">
                                *Memberikan nilai maksimal pada seluruh soal manual (esai / PG tanpa kunci).
                            </small>

                            <hr class="my-2">

                            <button type="submit" class="btn bg-gradient-primary text-white btn-sm w-100 py-2 font-weight-bold shadow-sm mb-0">
                                <i class="fas fa-save me-1"></i> Simpan & Finalisasi
                            </button>
                        </div>
                    </div>
                </div>

                {{-- KOLOM DAFTAR SOAL (Tampil di BAWAH pada Mobile/Tablet, di KIRI pada Desktop) --}}
                <div class="col-lg-9 order-2 order-lg-1">
                    @foreach($hasil->uji->soals as $index => $soal)
                        @php
                            $jawabanSiswa = $mapJawaban->get($soal->id);
                            $jawabanRaw = $jawabanSiswa ? $jawabanSiswa->jawaban : null;
                            $skorDidapat = $jawabanSiswa ? (float) $jawabanSiswa->skor_didapat : 0;
                            $kunciArray = is_array($soal->kunci_jawaban) ? array_filter($soal->kunci_jawaban, fn($v) => $v !== null && $v !== '') : [];
                            $tanpaKunci = in_array($soal->tipe, ['pg', 'pg_kompleks']) && empty($kunciArray);
                            $isManualGraded = ($soal->tipe === 'esai') || $tanpaKunci;

                            if ($isManualGraded) {
                                $hasManualQuestions = true;
                            } else {
                                $totalSkorOtomatis += $skorDidapat;
                            }
                        @endphp

                        <div class="card card-custom mb-3 {{ $isManualGraded ? 'border border-warning' : 'border' }}">
                            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                                <div>
                                    <span class="badge bg-gradient-dark text-xxs me-2">Soal #{{ $index + 1 }}</span>
                                    <span class="badge bg-outline-primary text-xxs text-uppercase">{{ str_replace('_', ' ', $soal->tipe) }}</span>
                                    @if($tanpaKunci)
                                        <span class="badge bg-warning text-dark text-xxs ms-1">
                                            <i class="fas fa-key me-1"></i> Tanpa Kunci Jawaban
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-xs text-secondary me-2">Bobot Poin: <strong>{{ $soal->bobot_nilai }}</strong></span>
                                    
                                    @if($soal->tipe === 'esai')
                                        <span class="badge bg-warning text-dark text-xxs">
                                            <i class="fas fa-pen-alt me-1"></i> Penilaian Uraian
                                        </span>
                                    @elseif($tanpaKunci)
                                        <span class="badge bg-warning text-dark text-xxs">
                                            <i class="fas fa-pen-alt me-1"></i> Perlu Penilaian Manual
                                        </span>
                                    @else
                                        @if($skorDidapat > 0)
                                            <span class="badge bg-success text-xxs"><i class="fas fa-check me-1"></i> Benar (+{{ $skorDidapat }})</span>
                                        @else
                                            <span class="badge bg-danger text-xxs"><i class="fas fa-times me-1"></i> Salah (0)</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="card-body p-3">
                                {{-- PERTANYAAN --}}
                                <p class="text-sm text-dark font-weight-bold mb-3">{!! nl2br(e($soal->pertanyaan)) !!}</p>

                                {{-- GAMBAR SOAL JIKA ADA --}}
                                @if($soal->media_url)
                                    <div class="mb-3">
                                        <img src="{{ $soal->media_url }}" alt="Lampiran Soal" class="img-thumbnail rounded shadow-xs" style="max-height: 220px;">
                                    </div>
                                @endif

                                {{-- TIPE PG & PG KOMPLEKS --}}
                                @if(in_array($soal->tipe, ['pg', 'pg_kompleks']))
                                    <div class="options-wrapper">
                                        @foreach($soal->opsi_jawaban as $optIdx => $opsiTeks)
                                            @if(!empty(trim($opsiTeks)))
                                                @php
                                                    $isKunci = in_array((string)$optIdx, $kunciArray);
                                                    
                                                    // Cek apakah opsi ini dipilih siswa
                                                    $isChosen = false;
                                                    if ($soal->tipe === 'pg_kompleks') {
                                                        $siswaArr = json_decode($jawabanRaw, true) ?? [];
                                                        $isChosen = in_array((string)$optIdx, $siswaArr);
                                                    } else {
                                                        $isChosen = ((string)$optIdx === (string)$jawabanRaw && $jawabanRaw !== null);
                                                    }

                                                    $boxClass = 'option-box';
                                                    if ($tanpaKunci) {
                                                        if ($isChosen) $boxClass .= ' option-chosen-neutral';
                                                    } else {
                                                        if ($isKunci) $boxClass .= ' option-correct';
                                                        elseif ($isChosen && !$isKunci) $boxClass .= ' option-wrong';
                                                    }
                                                @endphp

                                                <div class="{{ $boxClass }} d-flex justify-content-between align-items-center">
                                                    <div class="text-xs text-dark">
                                                        <strong class="me-2">{{ chr(65 + $optIdx) }}.</strong> {{ $opsiTeks }}
                                                    </div>
                                                    <div>
                                                        @if($isKunci)
                                                            <span class="badge-kunci me-1"><i class="fas fa-key me-1"></i> Kunci</span>
                                                        @endif
                                                        @if($isChosen)
                                                            <span class="badge-jawaban-siswa"><i class="fas fa-user-check me-1"></i> Pilihan Asesi</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    {{-- TOOLBAR INPUT SKOR MANUAL UNTUK PG / PGK YANG TIDAK DILENGKAPI KUNCI --}}
                                    @if($tanpaKunci)
                                        <div class="d-flex flex-wrap align-items-center gap-2 bg-light p-2 px-3 border border-warning rounded mt-3">
                                            <span class="text-xs font-weight-bold text-dark me-2">
                                                <i class="fas fa-gavel text-warning me-1"></i> Beri Skor Manual (Tanpa Kunci):
                                            </span>
                                            
                                            <button type="button" class="btn btn-xs btn-outline-success mb-0 btn-quick-grade" 
                                                    data-soal-id="{{ $soal->id }}" data-skor="{{ $soal->bobot_nilai }}">
                                                <i class="fas fa-check me-1"></i> Benar Full ({{ $soal->bobot_nilai }})
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-danger mb-0 btn-quick-grade" 
                                                    data-soal-id="{{ $soal->id }}" data-skor="0">
                                                <i class="fas fa-times me-1"></i> Salah (0)
                                            </button>

                                            <div class="input-group input-group-sm ms-auto" style="width: 145px;">
                                                <input type="number" step="0.5" min="0" max="{{ $soal->bobot_nilai }}" 
                                                       name="skor_manual[{{ $soal->id }}]" 
                                                       id="input_skor_{{ $soal->id }}"
                                                       class="form-control text-center font-weight-bold input-skor-manual bg-white" 
                                                       value="{{ $skorDidapat }}" 
                                                       data-bobot-max="{{ $soal->bobot_nilai }}">
                                                <span class="input-group-text text-xxs">/ {{ $soal->bobot_nilai }}</span>
                                            </div>
                                        </div>
                                    @endif

                                {{-- TIPE ESAI / URAIAN (PANEL KORREKSI GURU) --}}
                                @elseif($soal->tipe === 'esai')
                                    <div class="bg-light p-3 rounded border mb-3">
                                        <span class="text-xxs text-secondary font-weight-bold d-block mb-1">
                                            <i class="fas fa-pen-alt me-1 text-primary"></i> Jawaban Uraian Asesi:
                                        </span>
                                        <p class="text-sm text-dark mb-0 italic" style="white-space: pre-line;">
                                            {{ $jawabanRaw ? $jawabanRaw : '- Tidak Menjawab -' }}
                                        </p>
                                    </div>

                                    {{-- TOOLBAR INPUT SKOR ESAI --}}
                                    <div class="d-flex flex-wrap align-items-center gap-2 bg-white p-2 px-3 border border-warning rounded">
                                        <span class="text-xs font-weight-bold text-dark me-2">Beri Skor Esai:</span>
                                        
                                        {{-- Tombol Quick Grade --}}
                                        <button type="button" class="btn btn-xs btn-outline-success mb-0 btn-quick-grade" 
                                                data-soal-id="{{ $soal->id }}" data-skor="{{ $soal->bobot_nilai }}">
                                            <i class="fas fa-check me-1"></i> Benar Full ({{ $soal->bobot_nilai }})
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-danger mb-0 btn-quick-grade" 
                                                data-soal-id="{{ $soal->id }}" data-skor="0">
                                            <i class="fas fa-times me-1"></i> Salah (0)
                                        </button>

                                        <div class="input-group input-group-sm ms-auto" style="width: 145px;">
                                            <input type="number" step="0.5" min="0" max="{{ $soal->bobot_nilai }}" 
                                                   name="skor_manual[{{ $soal->id }}]" 
                                                   id="input_skor_{{ $soal->id }}"
                                                   class="form-control text-center font-weight-bold input-skor-manual" 
                                                   value="{{ $skorDidapat }}" 
                                                   data-bobot-max="{{ $soal->bobot_nilai }}">
                                            <span class="input-group-text text-xxs">/ {{ $soal->bobot_nilai }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>

    </div>
    <x-app.footer />
</main>

{{-- SCRIPT JAVASCRIPT KALKULASI REALTIME --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const skorOtomatisBase = {{ (float) $totalSkorOtomatis }};
    const manualInputs = document.querySelectorAll('.input-skor-manual');
    const totalRealtimeEl = document.getElementById('nilai-total-realtime');
    const sidebarOtomatisEl = document.getElementById('sidebar-skor-otomatis');
    const sidebarManualEl = document.getElementById('sidebar-skor-manual');

    if (sidebarOtomatisEl) {
        sidebarOtomatisEl.innerText = `${Number.isInteger(skorOtomatisBase) ? skorOtomatisBase : skorOtomatisBase.toFixed(1)} Poin`;
    }

    function recalculateTotalRealtime() {
        let totalManual = 0;
        manualInputs.forEach(input => {
            totalManual += parseFloat(input.value) || 0;
        });
        const grandTotal = skorOtomatisBase + totalManual;
        if (sidebarManualEl) {
            sidebarManualEl.innerText = `${Number.isInteger(totalManual) ? totalManual : totalManual.toFixed(1)} Poin`;
        }
        if (totalRealtimeEl) {
            totalRealtimeEl.innerText = Number.isInteger(grandTotal) ? grandTotal : grandTotal.toFixed(1);
        }
    }

    manualInputs.forEach(input => {
        input.addEventListener('input', recalculateTotalRealtime);
    });

    // Quick Grade Buttons (Benar Full / Salah 0)
    document.querySelectorAll('.btn-quick-grade').forEach(btn => {
        btn.addEventListener('click', function() {
            const soalId = this.getAttribute('data-soal-id');
            const skor = this.getAttribute('data-skor');
            const inputEl = document.getElementById(`input_skor_${soalId}`);
            if (inputEl) {
                inputEl.value = skor;
                recalculateTotalRealtime();
            }
        });
    });

    // Refresh Nilai Otomatis Sesuai Bobot Soal Terbaru
    const btnRefreshNilai = document.getElementById('btnRefreshNilai');
    const formRefreshNilai = document.getElementById('formRefreshNilai');
    if (btnRefreshNilai && formRefreshNilai) {
        btnRefreshNilai.addEventListener('click', function() {
            if (!confirm('Hitung ulang dan perbarui nilai peserta ini sesuai bobot soal terbaru?')) return;
            formRefreshNilai.submit();
        });
    }

    // Auto Scoring Penuh untuk semua item penilaian manual
    document.getElementById('btnAutoScoring').addEventListener('click', function() {
        if (!confirm('Apakah Anda yakin ingin memberikan nilai maksimal pada seluruh soal penilaian manual?')) return;

        manualInputs.forEach(input => {
            const max = parseFloat(input.getAttribute('data-bobot-max')) || 0;
            input.value = max;
        });
        recalculateTotalRealtime();
    });

    recalculateTotalRealtime();
});
</script>
@endsection