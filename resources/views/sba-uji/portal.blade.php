@php
    $bg = $bgConfig ?? \App\Models\SbaSetting::getPortalBackground();
    $sourceType     = $bg['source_type'] ?? 'unsplash_preset';
    $failoverColor  = $bg['failover_color'] ?? '#eef2f7';
    $overlayOpacity = (float) ($bg['overlay_opacity'] ?? 0.60);
    $imageUrl       = $bg['resolved_image_url'] ?? '';

    $inlineBodyStyle = "background-color: {$failoverColor};";
    $showOverlay     = false;

    if ($sourceType === 'static_color') {
        if (($bg['color_mode'] ?? 'solid') === 'gradient') {
            $angle = $bg['gradient_angle'] ?? '135deg';
            $start = $bg['gradient_start'] ?? '#6f42c1';
            $end   = $bg['gradient_end'] ?? '#3b1d7a';
            $inlineBodyStyle = "background: linear-gradient({$angle}, {$start} 0%, {$end} 100%);";
        } else {
            $solid = $bg['solid_color'] ?? $failoverColor;
            $inlineBodyStyle = "background-color: {$solid};";
        }
    } elseif (!empty($imageUrl)) {
        $inlineBodyStyle = "background-color: {$failoverColor}; background-image: url('{$imageUrl}'); background-repeat: no-repeat; background-position: center center; background-attachment: fixed; background-size: cover;";
        $showOverlay = true;
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Asesmen (SBA)</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s ease;
        }
        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: 1;
            transition: opacity 0.3s ease;
        }
        .portal-card {
            z-index: 2;
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .portal-header {
            background: linear-gradient(135deg, #6f42c1 0%, #512da8 100%);
            padding: 25px 20px;
            text-align: center;
            color: white;
        }
        .input-group-text {
            background-color: #f8f9fc;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
            border-radius: 0.375rem;
        }
        .btn-primary-custom {
            background-color: #6f42c1;
            border-color: #6f42c1;
            font-weight: bold;
            padding: 12px;
        }
        .btn-primary-custom:hover {
            background-color: #59359a;
            border-color: #59359a;
        }
        .kode-input {
            letter-spacing: 5px; 
            font-size: 1.5rem; 
            font-weight: bold;
            text-align: center;
            border: 2px solid #6f42c1;
            padding: 15px;
        }
        .kode-input[readonly] {
            background-color: #f3efff;
            color: #512da8;
            cursor: not-allowed;
        }
    </style>
</head>
<body style="{{ $inlineBodyStyle }}">

    <div class="bg-overlay" id="portalBgOverlay" style="background-color: rgba(0, 0, 0, {{ $overlayOpacity }}); {{ $showOverlay ? '' : 'display: none;' }}"></div>

    <div class="container position-relative z-2 d-flex justify-content-center px-3">
        <div class="portal-card border-0">
            
            <div class="portal-header">
                <h4 class="fw-bold mb-1">PORTAL ASESMEN</h4>
                <p class="mb-0 text-white-50">Smart Based Assessment (SBA)</p>
            </div>

            <div class="card-body p-4">
                @if(session('error'))
                    <div class="alert alert-danger text-center py-2 shadow-sm text-sm" role="alert">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning text-center py-2 shadow-sm text-sm" role="alert">
                        <i class="fas fa-info-circle me-1"></i> {{ session('warning') }}
                    </div>
                @endif

                <!-- Form action mengarah ke route sba.portal.process -->
                <form action="{{ route('sba.portal.process') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label id="labelIdentitas" class="form-label fw-bold text-dark small mb-1">Nomor Induk Siswa Nasional (NISN)</label>
                        <div class="input-group shadow-sm mb-2">
                            <span class="input-group-text text-secondary border-secondary-subtle">
                                <i id="iconIdentitas" class="fas fa-id-badge"></i>
                            </span>
                            <input type="text" id="inputIdentitas" name="nisn" value="{{ old('nisn', old('nama_lengkap')) }}" class="form-control border-secondary-subtle" placeholder="Masukkan NISN Anda" required>
                        </div>
                    </div>

                    <!-- Checkbox Jalur Umum -->
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="checkPublic" name="is_public" value="1" style="cursor: pointer;" {{ old('is_public') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted small ms-1" for="checkPublic" style="cursor: pointer;">
                            Bukan siswa? Gunakan akun public
                        </label>
                    </div>

                    <!-- INPUT KODE UJIAN -->
                    <div class="mb-4 text-center">
                        <label class="form-label fw-bold text-dark small mb-2">Kode Asesmen</label>
                        <input type="text" 
                               id="inputKodeUji"
                               name="kode_uji" 
                               class="form-control kode-input text-uppercase shadow-sm" 
                               placeholder="KODE" 
                               value="{{ old('kode_uji', request('kode')) }}"
                               {{ request()->filled('kode') ? 'readonly' : '' }}
                               required>
                    </div>

                    <button type="submit" class="btn btn-primary-custom text-white w-100 shadow-sm rounded-3 mb-2">
                        <i class="fas fa-sign-in-alt me-2"></i> Masuk Ruang Ujian
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkPublic = document.getElementById('checkPublic');
            const labelIdentitas = document.getElementById('labelIdentitas');
            const inputIdentitas = document.getElementById('inputIdentitas');
            const iconIdentitas = document.getElementById('iconIdentitas');
            const inputKodeUji = document.getElementById('inputKodeUji');

            function updateIdentitasMode(isPublic) {
                if (isPublic) {
                    labelIdentitas.innerText = 'Nama Lengkap';
                    inputIdentitas.name = 'nama_lengkap';
                    inputIdentitas.placeholder = 'Masukkan Nama Lengkap Anda';
                    iconIdentitas.className = 'fas fa-user';
                } else {
                    labelIdentitas.innerText = 'Nomor Induk Siswa Nasional (NISN)';
                    inputIdentitas.name = 'nisn';
                    inputIdentitas.placeholder = 'Masukkan NISN Anda';
                    iconIdentitas.className = 'fas fa-id-badge';
                }
            }

            if (checkPublic) {
                // Jalankan saat load jika ada old('is_public') dari validasi gagal
                updateIdentitasMode(checkPublic.checked);

                checkPublic.addEventListener('change', function() {
                    updateIdentitasMode(this.checked);
                    inputIdentitas.focus();
                });
            }

            // Auto-focus langsung ke input NISN jika kode asesmen sudah terisi otomatis dari URL
            if (inputKodeUji && inputKodeUji.value.trim() !== '' && inputIdentitas) {
                inputIdentitas.focus();
            }
        });
    </script>

    {{-- Failover Otomatis ke Warna Lembut jika Gambar Gagal Dimuat atau Timeout --}}
    @if($showOverlay && !empty($imageUrl))
    <script>
        (function() {
            const probe = new Image();
            const fallbackToSoftColor = function() {
                document.body.style.backgroundImage = 'none';
                document.body.style.backgroundColor = @json($failoverColor);
                const ov = document.getElementById('portalBgOverlay');
                if (ov) ov.style.display = 'none';
            };
            const timer = setTimeout(fallbackToSoftColor, 4000);
            probe.onload = function() { clearTimeout(timer); };
            probe.onerror = function() { clearTimeout(timer); fallbackToSoftColor(); };
            probe.src = @json($imageUrl);
        })();
    </script>
    @endif
</body>
</html>