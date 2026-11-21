<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ujian Selesai - {{ $uji->judul }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wrapper-finish {
            max-width: 500px;
            width: 100%;
        }
        .card-finish {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            background: #ffffff;
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, #6f42c1 0%, #4e2a8e 100%);
            color: #ffffff;
            padding: 24px 20px 20px;
        }
        .text-purple {
            color: #6f42c1;
        }
        .info-table td {
            padding: 6px 0;
            font-size: 0.875rem;
            vertical-align: top;
        }
        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: #fff;
        }
        .btn-purple:hover {
            background-color: #59339d;
            border-color: #59339d;
            color: #fff;
        }
    </style>
</head>
<body>
    @php
        $namaPeserta = $hasil->asesi
            ? ($hasil->asesi->nama_siswa ?? $hasil->asesi->nama_user ?? $hasil->asesi->name ?? 'Peserta')
            : ($hasil->nama_peserta_publik ?? 'Peserta');
        $waktuSelesaiText = $hasil->waktu_selesai
            ? $hasil->waktu_selesai->format('d/m/Y H:i') . ' WIB'
            : now()->format('d/m/Y H:i') . ' WIB';
        $isMenungguManual = !empty($butuhPenilaianManual) && $butuhPenilaianManual && $hasil->status_pengerjaan !== 'final';
    @endphp

    <div class="container p-3 d-flex justify-content-center">
        <div class="wrapper-finish">

            {{-- AREA YANG AKAN DITANGKAP MENJADI GAMBAR BUKTI --}}
            <div id="receipt-card" class="card card-finish text-center mb-3">
                <div class="receipt-header">
                    <div class="mb-2">
                        <i class="fas fa-check-circle fa-3x text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Ujian Telah Selesai!</h4>
                    <span class="badge bg-white text-purple fw-semibold px-3 py-1 rounded-pill">
                        Kode: {{ strtoupper($uji->kode_uji) }} &bull; ID #{{ $hasil->id }}
                    </span>
                </div>

                <div class="p-4">
                    <h6 class="fw-bold text-dark mb-3">{{ $uji->judul }}</h6>

                    {{-- IDENTITAS & WAKTU PENGERJAAN --}}
                    <div class="bg-light rounded-3 p-3 border text-start mb-3">
                        <table class="w-100 info-table">
                            <tr>
                                <td class="text-muted" width="38%">Nama Peserta</td>
                                <td class="fw-bold text-dark text-end">{{ $namaPeserta }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Waktu Selesai</td>
                                <td class="fw-semibold text-dark text-end">{{ $waktuSelesaiText }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Durasi</td>
                                <td class="fw-semibold text-dark text-end">{{ $hasil->durasi_formatted ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Percobaan</td>
                                <td class="fw-semibold text-dark text-end">Ke-{{ $hasil->percobaan_ke ?? 1 }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- JIKA PENGATURAN "TAMPILKAN NILAI" DIAKTIFKAN OLEH ADMIN --}}
                    @if($uji->tampilkan_nilai)
                        @if($isMenungguManual)
                            <div class="bg-light p-3 rounded-3 border">
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-2">
                                    <i class="fas fa-hourglass-half me-1"></i> Menunggu Penilaian Penguji
                                </span>
                                <p class="text-muted small mb-0 mt-1">
                                    Asesmen ini memiliki soal uraian/penilaian manual. Skor akhir akan dihitung setelah penguji selesai memeriksa jawaban Anda.
                                </p>
                            </div>
                        @else
                            <div class="bg-light p-3 rounded-3 border">
                                <span class="text-muted text-uppercase small fw-bold d-block mb-1">Skor Akhir Anda</span>
                                <h1 class="display-3 fw-bold text-purple mb-0">{{ number_format($hasil->total_skor, 0) }}</h1>
                                <small class="text-muted">
                                    {{ $hasil->status_pengerjaan === 'final' ? 'Skor akhir telah difinalisasi penguji.' : 'Skor otomatis terhitung dari jawaban Anda.' }}
                                </small>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info small py-3 mb-0">
                            <i class="fas fa-info-circle me-1"></i> Jawaban dan nilai Anda telah tersimpan ke dalam sistem dan akan direkap oleh penguji.
                        </div>
                    @endif
                </div>
            </div>

            {{-- TOMBOL AKSI: SIMPAN GAMBAR, SHARE NATIVE OS, & KEMBALI --}}
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <button type="button" id="btnSimpanBukti" class="btn btn-outline-success w-100 py-2 fw-bold">
                            <i class="fas fa-download me-1"></i> Simpan
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" id="btnShareBukti" class="btn btn-success w-100 py-2 fw-bold">
                            <i class="fas fa-share-alt me-1"></i> Share
                        </button>
                    </div>
                </div>

                <div id="shareFeedback" class="text-center small text-success fw-semibold mb-2 d-none"></div>

                <a href="{{ route('sba.portal') }}" class="btn btn-purple w-100 py-2 fw-bold">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Portal Ujian
                </a>
            </div>

        </div>
    </div>

    {{-- Library html2canvas untuk merender kartu hasil menjadi gambar PNG --}}
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const receiptEl = document.getElementById('receipt-card');
        const btnSimpan = document.getElementById('btnSimpanBukti');
        const btnShare = document.getElementById('btnShareBukti');
        const feedbackEl = document.getElementById('shareFeedback');

        const namaFile = @json('Bukti_Ujian_' . strtoupper($uji->kode_uji) . '_' . \Illuminate\Support\Str::slug($namaPeserta) . '.png');
        const judulUjian = @json($uji->judul);
        const teksRingkasan = @json(
            "Bukti Selesai Ujian: {$uji->judul} (" . strtoupper($uji->kode_uji) . ")\n" .
            "Nama: {$namaPeserta}\n" .
            "Waktu Selesai: {$waktuSelesaiText}\n" .
            ($uji->tampilkan_nilai && !$isMenungguManual ? "Skor Akhir: " . number_format($hasil->total_skor, 0) : "Status: Selesai")
        );

        let cachedBlob = null;
        let cachedFile = null;

        function showFeedback(msg, isError = false) {
            feedbackEl.textContent = msg;
            feedbackEl.classList.remove('d-none', 'text-success', 'text-danger');
            feedbackEl.classList.add(isError ? 'text-danger' : 'text-success');
            setTimeout(() => feedbackEl.classList.add('d-none'), 4000);
        }

        async function renderReceiptImage() {
            if (cachedBlob && cachedFile) {
                return { blob: cachedBlob, file: cachedFile };
            }
            const canvas = await html2canvas(receiptEl, {
                scale: 2,
                backgroundColor: '#ffffff',
                useCORS: true
            });
            const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
            const file = new File([blob], namaFile, { type: 'image/png' });
            cachedBlob = blob;
            cachedFile = file;
            return { blob, file };
        }

        function triggerDownload(blob) {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = namaFile;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(url), 1000);
        }

        // Pre-render gambar setelah halaman selesai dimuat agar tombol Share di Android/iOS merespons instan
        window.addEventListener('load', () => {
            renderReceiptImage().catch(() => {});
        });

        // 1. Tombol Simpan Gambar (.png)
        btnSimpan.addEventListener('click', async function() {
            const originalHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

            try {
                const { blob } = await renderReceiptImage();
                triggerDownload(blob);
                showFeedback('Bukti hasil ujian berhasil disimpan ke perangkat!');
            } catch (e) {
                showFeedback('Gagal membuat gambar bukti. Silakan coba lagi.', true);
            } finally {
                this.disabled = false;
                this.innerHTML = originalHtml;
            }
        });

        // 2. Tombol Share (Memicu Popup Menu Share Native Android / iPhone)
        btnShare.addEventListener('click', async function() {
            try {
                const { blob, file } = await renderReceiptImage();

                // Cek dukungan Share File (Android Chrome / iOS Safari)
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        title: 'Bukti Ujian - ' + judulUjian,
                        text: teksRingkasan,
                        files: [file]
                    });
                    return;
                }

                // Fallback 1: Native Share Teks (jika perangkat mendukung share teks tanpa file)
                if (navigator.share) {
                    await navigator.share({
                        title: 'Bukti Ujian - ' + judulUjian,
                        text: teksRingkasan
                    });
                    return;
                }

                // Fallback 2: Browser Desktop -> Unduh gambar + Salin teks bukti
                triggerDownload(blob);
                if (navigator.clipboard) {
                    await navigator.clipboard.writeText(teksRingkasan);
                }
                showFeedback('Gambar bukti diunduh & teks ringkasan disalin ke clipboard!');
            } catch (err) {
                // Abaikan jika pengguna membatalkan menu share (AbortError)
                if (err && err.name !== 'AbortError') {
                    try {
                        const { blob } = await renderReceiptImage();
                        triggerDownload(blob);
                        showFeedback('Menu share tidak tersedia, gambar bukti langsung diunduh.');
                    } catch (_) {
                        showFeedback('Gagal membagikan hasil ujian.', true);
                    }
                }
            }
        });
    });
    </script>
</body>
</html>