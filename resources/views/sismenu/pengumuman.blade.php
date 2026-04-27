@extends('layouts.app') 

@section('page-title', 'Pengumuman Hasil Belajar')

@section('content')

{{-- KUMPULAN CSS ANIMASI DRAMATIS --}}
<style>
    /* Efek memudar dan menghilang */
    .fade-out-up {
        animation: fadeOutUp 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
    }
    @keyframes fadeOutUp {
        0% { opacity: 1; transform: translateY(0) scale(1); }
        100% { opacity: 0; transform: translateY(-30px) scale(0.95); display: none; }
    }

    /* Efek muncul dari bawah */
    .fade-in-up {
        animation: fadeInUp 0.8s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
        opacity: 0; 
    }
    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(40px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    /* Efek denyut tegang pada amplop */
    .pulse-tension {
        animation: pulseTension 0.8s ease-in-out infinite alternate;
    }
    @keyframes pulseTension {
        0% { transform: scale(1); box-shadow: 0 10px 30px rgba(94, 114, 228, 0.2); }
        100% { transform: scale(1.02); box-shadow: 0 20px 40px rgba(94, 114, 228, 0.6); }
    }
</style>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg pt-4">
    
    <x-app.navbar />

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 mx-auto text-center mt-4">
                
                {{-- KOP SEKOLAH --}}
                <div class="mb-5">
                    <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg mb-3 mx-auto">
                        <i class="fas fa-envelope-open-text opacity-10"></i>
                    </div>
                    <h3 class="text-dark font-weight-bolder">Pengumuman Hasil Belajar</h3>
                    <p class="text-secondary">Tahun Pelajaran {{ $pengumuman->tahun_ajaran ?? $defaultTA }}</p>
                </div>

                {{-- Tambahkan id="card-utama" untuk target animasi denyut --}}
                <div id="card-utama" class="card shadow-lg border-0 overflow-hidden" style="border-radius: 15px; transition: all 0.3s ease;">
                    
                    {{-- SKENARIO 1: Pengumuman Dinonaktifkan --}}
                    @if(!$isAktif)
                        <div class="card-body p-5 bg-light">
                            <i class="fas fa-lock fa-4x text-secondary mb-4 opacity-5"></i>
                            <h5 class="font-weight-bolder text-dark">Pengumuman Ditutup</h5>
                            <p class="text-sm text-secondary mb-0">Halaman pengumuman saat ini sedang dinonaktifkan oleh pihak sekolah.</p>
                        </div>

                    {{-- SKENARIO 2: Belum Waktunya Buka --}}
                    @elseif(!$isWaktuBuka)
                        <div class="card-body p-5 bg-gradient-dark text-white position-relative">
                            <div style="position: absolute; top: -50px; left: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                            
                            <i class="fas fa-hourglass-half fa-3x mb-4 opacity-8"></i>
                            <h4 class="font-weight-bolder text-white mb-2">Harap Bersabar</h4>
                            <p class="text-sm opacity-8 mb-4">Pengumuman akan dibuka secara serentak dalam waktu:</p>
                            
                            <div class="d-flex justify-content-center gap-3 mb-4">
                                <div class="bg-white rounded-3 shadow-sm p-3 text-center" style="min-width: 80px;">
                                    <h2 class="mb-0 text-primary font-weight-bolder" id="cd-hari">00</h2>
                                    <span class="text-xs text-uppercase text-secondary font-weight-bold">Hari</span>
                                </div>
                                <div class="bg-white rounded-3 shadow-sm p-3 text-center" style="min-width: 80px;">
                                    <h2 class="mb-0 text-primary font-weight-bolder" id="cd-jam">00</h2>
                                    <span class="text-xs text-uppercase text-secondary font-weight-bold">Jam</span>
                                </div>
                                <div class="bg-white rounded-3 shadow-sm p-3 text-center" style="min-width: 80px;">
                                    <h2 class="mb-0 text-primary font-weight-bolder" id="cd-menit">00</h2>
                                    <span class="text-xs text-uppercase text-secondary font-weight-bold">Menit</span>
                                </div>
                                <div class="bg-gradient-primary rounded-3 shadow p-3 text-center" style="min-width: 80px; transform: scale(1.05);">
                                    <h2 class="mb-0 text-white font-weight-bolder" id="cd-detik">00</h2>
                                    <span class="text-xs text-uppercase text-white opacity-8 font-weight-bold">Detik</span>
                                </div>
                            </div>

                            <p class="text-xs mb-0 opacity-8 mt-3">
                                Jadwal rilis: {{ $waktuPengumuman->locale('id')->isoFormat('dddd, D MMMM YYYY, HH:mm') }} WIB
                            </p>
                            
                            <div id="btn-refresh-container" class="mt-4 d-none">
                                <p class="text-sm text-success font-weight-bold mb-2">Waktu Tunggu Selesai!</p>
                                <button onclick="window.location.reload();" class="btn btn-white text-dark shadow-sm px-4 rounded-pill">
                                    <i class="fas fa-sync-alt me-2"></i> Muat Ulang Halaman
                                </button>
                            </div>
                        </div>

                    {{-- SKENARIO 3: Data Pengumuman Belum Tersedia --}}
                    @elseif(!$pengumuman)
                        <div class="card-body p-5 bg-light">
                            <i class="fas fa-file-excel fa-4x text-warning mb-4 opacity-5"></i>
                            <h5 class="font-weight-bolder text-dark">Data Belum Tersedia</h5>
                            <p class="text-sm text-secondary mb-0">Data surat keputusan Anda belum dirilis oleh pihak sekolah. Silakan hubungi Wali Kelas Anda.</p>
                        </div>

                    {{-- SKENARIO 4: Waktunya Buka Amplop! --}}
                    @else
                        
                        {{-- Cover Amplop (Tampilan Awal) --}}
                        @if($pengumuman->has_seen == 0)
                            <div id="cover-amplop" class="card-body p-5 bg-gradient-primary">
                                <h5 class="text-white font-weight-normal mb-1">Surat Keputusan Untuk:</h5>
                                <h3 class="text-white font-weight-bolder mb-4">{{ $siswa->nama_siswa ?? 'Siswa' }}</h3>
                                <p class="text-white opacity-8 text-sm mb-5">NISN: {{ $siswa->nisn ?? '-' }}</p>
                                
                                <button id="btn-buka" class="btn btn-lg btn-white text-primary w-100 shadow-lg" style="border-radius: 50px; transition: all 0.3s;">
                                    <i class="fas fa-seal me-2"></i> BUKA HASIL SEKARANG
                                </button>
                            </div>
                        @endif

                        {{-- Isi Surat (Tampilan Hasil) --}}
                        <div id="isi-surat" class="card-body p-5 {{ $pengumuman->has_seen == 0 ? 'd-none' : '' }}">
                            <h6 class="text-uppercase text-secondary font-weight-bolder mb-3">Memutuskan Bahwa Anda Dinyatakan:</h6>
                            
                            @if($status == 'sukses')
                                <div class="py-4 border-radius-lg mb-4" style="background-color: #e8fbee; border: 2px dashed #17ad37;">
                                    <h1 class="text-success font-weight-bolder mb-0" style="letter-spacing: 2px;">{{ $pesan }}</h1>
                                </div>
                                <h5 class="text-dark font-weight-bolder">Selamat atas pencapaian Anda! 🎉</h5>
                                <p class="text-sm text-secondary">Teruslah semangat belajar dan kejar cita-cita Anda di masa depan.</p>
                            @else
                                <div class="py-4 border-radius-lg mb-4" style="background-color: #fef0f0; border: 2px dashed #ea0606;">
                                    <h2 class="text-danger font-weight-bolder mb-0" style="letter-spacing: 1px;">{{ $pesan }}</h2>
                                </div>
                                <h5 class="text-dark font-weight-bolder">Tetap Semangat! 💪</h5>
                                <p class="text-sm text-secondary">Ini bukan akhir dari segalanya. Jadikan evaluasi ini sebagai batu loncatan untuk menjadi lebih baik lagi di masa depan.</p>
                            @endif

                            @if($catatanTambahan)
                                <div class="mt-4 p-3 bg-gray-100 border-radius-md text-start">
                                    <p class="text-xs font-weight-bold text-secondary mb-1">Catatan Sekolah:</p>
                                    <p class="text-sm text-dark mb-0 fst-italic">"{{ $catatanTambahan }}"</p>
                                </div>
                            @endif

                            <div class="mt-4 pt-4 border-top">
                                <a href="{{ route('sis.biodata') }}" class="btn btn-outline-secondary btn-sm mb-0">Kembali ke Beranda</a>
                            </div>
                        </div>

                    @endif

                </div>
            </div>
        </div>
    </div>
</main>

{{-- Library Canvas Confetti --}}
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // ==========================================
        // FITUR 1: COUNTDOWN TIMER
        // ==========================================
        @if(!$isWaktuBuka && isset($waktuPengumuman))
            const countDownDate = {{ $waktuPengumuman->timestamp * 1000 }};
            
            const x = setInterval(function() {
                const now = new Date().getTime();
                const distance = countDownDate - now;

                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    document.getElementById("cd-hari").innerText = days.toString().padStart(2, '0');
                    document.getElementById("cd-jam").innerText = hours.toString().padStart(2, '0');
                    document.getElementById("cd-menit").innerText = minutes.toString().padStart(2, '0');
                    document.getElementById("cd-detik").innerText = seconds.toString().padStart(2, '0');
                } else {
                    clearInterval(x);
                    document.getElementById("cd-hari").innerText = "00";
                    document.getElementById("cd-jam").innerText = "00";
                    document.getElementById("cd-menit").innerText = "00";
                    document.getElementById("cd-detik").innerText = "00";
                    
                    document.getElementById("btn-refresh-container").classList.remove("d-none");
                }
            }, 1000);
        @endif

        // ==========================================
        // FITUR 2: BUKA AMPLOP DRAMATIS
        // ==========================================
        const btnBuka = document.getElementById('btn-buka');
        const coverAmplop = document.getElementById('cover-amplop');
        const isiSurat = document.getElementById('isi-surat');
        const cardUtama = document.getElementById('card-utama');
        
        const statusHasil = "{{ $status ?? '' }}";

        if(btnBuka) {
            btnBuka.addEventListener('click', function() {
                
                // 1. Ubah visual tombol agar tidak diklik dua kali
                btnBuka.disabled = true;
                btnBuka.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Membuka Segel...';
                btnBuka.classList.remove('text-primary');
                btnBuka.classList.add('text-secondary');

                // 2. Tambahkan efek ketegangan (denyut pada amplop)
                cardUtama.classList.add('pulse-tension');

                // 3. Mainkan Delay 2.5 Detik
                setTimeout(() => {
                    // Hentikan getaran
                    cardUtama.classList.remove('pulse-tension');
                    
                    // Mainkan animasi memudar ke atas pada cover amplop
                    coverAmplop.classList.add('fade-out-up');

                    // Tunggu sedikit sampai amplop menghilang, lalu munculkan surat
                    setTimeout(() => {
                        coverAmplop.classList.add('d-none');
                        
                        isiSurat.classList.remove('d-none');
                        isiSurat.classList.add('fade-in-up'); // Surat muncul dari bawah

                        // Ledakkan confetti jika sukses
                        if(statusHasil === 'sukses') {
                            setTimeout(() => { fireConfetti(); }, 300); // Delay confetti sedikit agar sinkron dengan surat muncul
                        }

                    }, 500); // 500ms adalah durasi fade-out-up

                }, 2500); // 2.5 Detik Ketegangan

                // 4. API Hit di belakang layar (Siswa tidak akan sadar ini sedang berjalan)
                fetch("{{ route('sis.pengumuman.baca') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => console.log('Status dibaca tersimpan.'))
                .catch(error => console.error('Gagal mencatat status dibaca.'));
            });
        }

        // Animasi Confetti
        function fireConfetti() {
            var duration = 4 * 1000;
            var animationEnd = Date.now() + duration;
            var defaults = { startVelocity: 40, spread: 360, ticks: 60, zIndex: 9999 };

            function randomInRange(min, max) { return Math.random() * (max - min) + min; }

            var interval = setInterval(function() {
                var timeLeft = animationEnd - Date.now();

                if (timeLeft <= 0) { return clearInterval(interval); }

                var particleCount = 60 * (timeLeft / duration);
                
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
            }, 250);
        }
    });
</script>
@endsection