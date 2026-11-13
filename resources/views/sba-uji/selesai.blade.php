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
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-finish {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            max-width: 500px;
            width: 100%;
        }
        .text-purple {
            color: #6f42c1;
        }
    </style>
</head>
<body>
    <div class="container p-3 d-flex justify-content-center">
        <div class="card card-finish text-center p-4 p-md-5">
            <div class="mb-3">
                <i class="fas fa-check-circle text-success display-1"></i>
            </div>
            
            <h4 class="fw-bold mb-1">Ujian Telah Selesai!</h4>
            <p class="text-muted small mb-4">Terima kasih telah menyelesaikan <strong>{{ $uji->judul }}</strong>.</p>

            {{-- JIKA PENGATURAN "TAMPILKAN NILAI" DIAKTIFKAN OLEH ADMIN --}}
            @if($uji->tampilkan_nilai)
                <div class="bg-light p-4 rounded-3 border mb-4">
                    <span class="text-muted text-uppercase text-xs fw-bold d-block mb-1">Skor Akhir Anda</span>
                    <h1 class="display-3 fw-bold text-purple mb-0">{{ number_format($hasil->total_skor, 0) }}</h1>
                    <small class="text-muted">Skor otomatis terhitung dari soal pilihan ganda.</small>
                </div>
            @else
                <div class="alert alert-info text-sm py-3 mb-4">
                    <i class="fas fa-info-circle me-1"></i> Nilai Anda telah tersimpan ke dalam sistem dan akan direkap oleh guru pengampu.
                </div>
            @endif

            <a href="{{ route('sba.portal') }}" class="btn btn-primary py-2 px-4 fw-bold" style="background-color: #6f42c1; border-color: #6f42c1;">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Portal Ujian
            </a>
        </div>
    </div>
</body>
</html>