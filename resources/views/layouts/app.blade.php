<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    {{-- CSRF Token untuk AJAX Request --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @if (config('app.is_demo'))
        <title itemprop="name">
            E-Rapor SMK
        </title>
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@CreativeTim" />
        <meta name="twitter:creator" content="@CreativeTim" />
        <meta name="twitter:title" content="Corporate UI Dashboard Laravel by Creative Tim & UPDIVISION" />
        <meta name="twitter:description" content="Fullstack tool for building Laravel apps with hundreds of UI components and ready-made CRUDs" />
        <meta name="twitter:image" content="https://s3.amazonaws.com/creativetim_bucket/products/737/original/corporate-ui-dashboard-laravel.jpg?1695288974" />
        <meta name="twitter:url" content="https://www.creative-tim.com/live/corporate-ui-dashboard-laravel" />
        <meta name="description" content="Fullstack tool for building Laravel apps with hundreds of UI components and ready-made CRUDs">
        <meta name="keywords" content="creative tim, updivision, html dashboard, laravel, api, html css dashboard laravel, Corporate UI Dashboard Laravel, Corporate UI Laravel, Corporate Dashboard Laravel, UI Dashboard Laravel, Laravel admin, laravel dashboard, Laravel dashboard, laravel admin, web dashboard, bootstrap 5 dashboard laravel, bootstrap 5, css3 dashboard, bootstrap 5 admin laravel, frontend, responsive bootstrap 5 dashboard, corporate dashboard laravel, Corporate UI Dashboard Laravel">
        <meta property="og:app_id" content="655968634437471">
        <meta property="og:type" content="product">
        <meta property="og:title" content="Corporate UI Dashboard Laravel by Creative Tim & UPDIVISION">
        <meta property="og:url" content="https://www.creative-tim.com/live/corporate-ui-dashboard-laravel">
        <meta property="og:image" content="https://s3.amazonaws.com/creativetim_bucket/products/737/original/corporate-ui-dashboard-laravel.jpg?1695288974">
        <meta property="product:price:amount" content="FREE">
        <meta property="product:price:currency" content="USD">
        <meta property="product:availability" content="in Stock">
        <meta property="product:brand" content="Creative Tim">
        <meta property="product:category" content="Admin &amp; Dashboards">
        <meta name="data-turbolinks-track" content="false">
    @endif
    
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    
    <title>
        @yield('title', 'E-Rapor SMK')
    </title>
    
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Noto+Sans:300,400,500,600,700,800|PT+Mono:300,400,500,600,700" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    {{-- Main CSS --}}
    <link id="pagestyle" href="{{ asset('assets/css/corporate-ui-dashboard.css?v=1.0.0') }}" rel="stylesheet" />

</head>

<body class="g-sidenav-show bg-gray-100">
    {{-- 👇 PITA MODE SIMULASI MODERN 👇 --}}
    @if(session('mode_simulasi') === true)
        <div class="simulasi-banner d-flex justify-content-center align-items-center">
            <div class="pulse-icon me-2 me-md-3"><i class="fas fa-flask"></i></div>
            <div class="banner-text text-center text-md-start">
                <span class="fw-bolder text-sm tracking-wide">MODE SIMULASI AKTIF</span>
                <span class="mx-2 d-none d-md-inline opacity-6">|</span>
                <span class="fw-normal text-xs d-none d-md-inline opacity-8">Semua perubahan hanya tersimpan di database demo (tidak merusak data asli).</span>
            </div>
            <a href="{{ route('settings.toggle.simulasi') }}" class="btn-exit-simulasi ms-3 ms-md-4">
                <span class="d-none d-md-inline">KEMBALI KE ASLI</span>
                <span class="d-inline d-md-none">KELUAR</span>
                <i class="fas fa-sign-out-alt ms-1"></i>
            </a>
        </div>

        <style>
            /* Menurunkan body agar navbar tidak tertutup pita */
            body { padding-top: 46px !important; }
            
            /* Styling Modern Banner */
            .simulasi-banner {
                position: fixed;
                top: 0; left: 0; width: 100%;
                background: linear-gradient(87deg, #f5365c 0%, #f56036 100%);
                color: #ffffff;
                z-index: 999999;
                padding: 8px 15px;
                box-shadow: 0 4px 15px -3px rgba(245, 54, 92, 0.4);
                backdrop-filter: blur(5px);
            }
            .tracking-wide { letter-spacing: 1.5px; }
            
            /* Styling Tombol Keluar yang Elegan */
            .btn-exit-simulasi {
                background: rgba(255, 255, 255, 0.15);
                border: 1px solid rgba(255, 255, 255, 0.4);
                color: #ffffff !important;
                padding: 4px 14px;
                border-radius: 50px;
                font-size: 0.7rem;
                font-weight: 700;
                letter-spacing: 0.5px;
                text-decoration: none;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                white-space: nowrap;
            }
            .btn-exit-simulasi:hover {
                background: #ffffff;
                color: #f5365c !important;
                transform: translateY(-1px);
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }

            /* Animasi Berdetak untuk Ikon Flask */
            .pulse-icon { animation: pulse 2s infinite; font-size: 1.1rem; }
            @keyframes pulse {
                0% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.2); opacity: 0.7; }
                100% { transform: scale(1); opacity: 1; }
            }
        </style>
    @endif
    {{-- 👆 END PITA SIMULASI 👆 --}}
    @php
        $topSidenavArray = ['wallet', 'profile'];
        $topSidenavTransparent = ['signin', 'signup'];
        $topSidenavRTL = ['RTL'];
    @endphp

    @if (in_array(request()->route()->getName(), $topSidenavArray))
        <x-sidenav-top />
    @elseif(in_array(request()->route()->getName(), $topSidenavTransparent))
        {{-- No Sidebar for Auth Pages --}}
    @elseif(in_array(request()->route()->getName(), $topSidenavRTL))
        {{-- RTL Sidebar --}}
    @else
        <x-app.sidebar />
    @endif

    {{-- CONTENT YIELD --}}
    @yield('content')

    {{-- CUSTOM STYLES DARI ANDA --}}
    <style>
        /* Kontainer Utama Sidebar */
        .sidebar {
            background-color: #E3F2FD !important; /* Biru sangat muda (Soft Blue) */
            color: #0D47A1 !important;            /* Biru tua agar teks sangat jelas */
            border-right: 1px solid #BBDEFB !important;
        }

        /* Judul atau Header di Sidebar */
        .sidebar-header {
            background-color: #BBDEFB; /* Sedikit lebih gelap dari background */
            color: #01579B;            /* Teks biru pekat */
            font-weight: bold;
            padding: 15px;
        }

        /* Menu Link */
        .sidebar-link {
            color: #1565C0;            /* Warna teks menu */
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            transition: 0.3s;
        }

        /* Efek saat Menu Disorot (Hover) atau Aktif */
        .sidebar-link:hover, .sidebar-link.active {
            background-color: #2196F3; /* Biru cerah saat dipilih */
            color: #ffffff;            /* Teks menjadi putih agar kontras */
        }

        /* Icon di Sidebar (Jika ada) */
        .sidebar-link i {
            color: #1976D2;            /* Warna icon sedikit lebih gelap */
            margin-right: 10px;
        }
    </style>

    {{-- CONFIGURATOR / FIXED PLUGIN --}}
    <div class="fixed-plugin">
        <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
            <i class="fa fa-cog py-2"></i>
        </a>
        <div class="card shadow-lg ">
            <div class="card-header pb-0 pt-3 ">
                <div class="float-start">
                    <h5 class="mt-3 mb-0">Corporate UI Configurator</h5>
                    <p>See our dashboard options.</p>
                </div>
                <div class="float-end mt-4">
                    <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
            </div>
            <hr class="horizontal dark my-1">
            <div class="card-body pt-sm-3 pt-0">
                <div>
                    <h6 class="mb-0">Sidebar Colors</h6>
                </div>
                <a href="javascript:void(0)" class="switch-trigger background-color">
                    <div class="badge-colors my-2 text-start">
                        <span class="badge filter bg-gradient-primary active" data-color="primary" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
                    </div>
                </a>
                <div class="mt-3">
                    <h6 class="mb-0">Sidenav Type</h6>
                    <p class="text-sm">Choose between 2 different sidenav types.</p>
                </div>
                <div class="d-flex">
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2 active" data-class="bg-slate-900" onclick="sidebarType(this)">Dark</button>
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2 ms-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
                </div>
                <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
                <div class="mt-3">
                    <h6 class="mb-0">Navbar Fixed</h6>
                </div>
                <div class="form-check form-switch ps-0">
                    <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
                </div>
                <hr class="horizontal dark my-sm-4">
                <a class="btn bg-gradient-dark w-100" target="_blank" href="https://www.creative-tim.com/product/corporate-ui-dashboard-laravel">Free Download</a>
                <a class="btn btn-outline-dark w-100" target="_blank" href="https://www.creative-tim.com/learning-lab/bootstrap/installation-guide/corporate-ui-dashboard">View documentation</a>
                <div class="w-100 text-center">
                    <a class="github-button" target="_blank" href="https://github.com/creativetimofficial/corporate-ui-dashboard-laravel" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star creativetimofficial/corporate-ui-dashboard on GitHub">Star</a>
                    <h6 class="mt-3">Thank you for sharing!</h6>
                    <a href="https://twitter.com/intent/tweet?text=Check%20Corporate%20UI%20Dashboard%20made%20by%20%40CreativeTim%20%26%20%40UPDIVISION%20%23webdesign%20%23dashboard%20%23bootstrap5%20%23laravel&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fcorporate-ui-dashboard-laravel" class="btn btn-dark mb-0 me-2" target="_blank">
                        <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/corporate-ui-dashboard-laravel" class="btn btn-dark mb-0 me-2" target="_blank">
                        <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 👇 MODAL INFO SIMULASI (POP-UP TENGAH) 👇 --}}
    @if(session('simulasi_toggled'))
    <div class="modal fade" id="simulasiInfoModal" tabindex="-1" aria-labelledby="simulasiInfoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 9999999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                
                @if(session('simulasi_toggled') == 'on')
                    {{-- TAMPILAN JIKA SIMULASI MENYALA --}}
                    <div class="modal-header bg-gradient-warning p-3">
                        <h5 class="modal-title text-white font-weight-bold" id="simulasiInfoModalLabel">
                            <i class="fas fa-flask me-2"></i> Mode Simulasi Diaktifkan
                        </h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="icon icon-shape icon-xl bg-gradient-warning shadow text-center border-radius-md mb-4 mx-auto d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                            <i class="fas fa-database text-white" style="font-size: 1.8rem;"></i>
                        </div>
                        <h5 class="mb-3 text-dark font-weight-bolder">Anda memasuki lingkungan Sandbox!</h5>
                        <p class="text-sm text-secondary mb-0" style="text-align: justify; line-height: 1.6;">
                            Mulai saat ini, seluruh aktivitas Anda (Tambah, Edit, Hapus data) akan diarahkan ke <strong>Database Simulasi</strong>. 
                            <br><br>
                            Anda bebas melakukan uji coba, pelatihan, atau menguji fitur baru tanpa perlu khawatir merusak atau mengubah data Rapor yang asli. Pita indikator di atas layar akan terus muncul sebagai pengingat.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center p-3 border-top-0">
                        <button type="button" class="btn bg-gradient-warning w-100 mb-0 btn-lg shadow-sm" data-bs-dismiss="modal">SAYA MENGERTI, LANJUTKAN</button>
                    </div>

                @else
                    {{-- TAMPILAN JIKA SIMULASI DIMATIKAN --}}
                    <div class="modal-header bg-gradient-info p-3">
                        <h5 class="modal-title text-white font-weight-bold" id="simulasiInfoModalLabel">
                            <i class="fas fa-power-off me-2"></i> Mode Simulasi Dimatikan
                        </h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="icon icon-shape icon-xl bg-gradient-info shadow text-center border-radius-md mb-4 mx-auto d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                            <i class="fas fa-check-circle text-white" style="font-size: 1.8rem;"></i>
                        </div>
                        <h5 class="mb-3 text-dark font-weight-bolder">Kembali ke Database Utama</h5>
                        <p class="text-sm text-secondary mb-0" style="text-align: justify; line-height: 1.6;">
                            Anda telah keluar dari mode simulasi. Sistem sekarang terhubung kembali dengan <strong>Database Asli E-Rapor</strong>.
                            <br><br>
                            <span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle me-1"></i> Perhatian:</span> Segala perubahan data yang Anda lakukan mulai dari sekarang akan langsung berdampak pada sistem akademik sekolah secara permanen.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center p-3 border-top-0">
                        <button type="button" class="btn bg-gradient-info w-100 mb-0 btn-lg shadow-sm" data-bs-dismiss="modal">TUTUP & BEKERJA DENGAN HATI-HATI</button>
                    </div>
                @endif

            </div>
        </div>
    </div>
    
    {{-- Trigger Modal dengan Javascript Murni (Tanpa jQuery agar tidak konflik) --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var simulasiModalElement = document.getElementById('simulasiInfoModal');
            if(simulasiModalElement) {
                var simulasiModal = new bootstrap.Modal(simulasiModalElement);
                simulasiModal.show();
            }
        });
    </script>
    @endif
    {{-- 👆 END MODAL INFO SIMULASI 👆 --}}

    {{-- ========================================================== --}}
    {{-- CORE JS FILES (JANGAN DIHAPUS ATAU DIKOMENTAR) --}}
    {{-- ========================================================== --}}
    
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/swiper-bundle.min.js') }}" type="text/javascript"></script>
    
    {{-- Plugins Tambahan --}}
    <script src="https://cdn.sheetjs.com/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    {{-- Script Inisialisasi Swiper (Jika Ada) --}}
    <script>
        if (document.getElementsByClassName('mySwiper')) {
            var swiper = new Swiper(".mySwiper", {
                effect: "cards",
                grabCursor: true,
                initialSlide: 1,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
        };
    </script>

    {{-- Script Inisialisasi Scrollbar --}}
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    
    {{-- 🛑 PENTING: SCRIPT INI WAJIB AKTIF AGAR SIDEBAR & NAVBAR BERFUNGSI --}}
    <script src="{{ asset('assets/js/corporate-ui-dashboard.min.js?v=1.0.0') }}"></script>
    
    @stack('scripts')
    @stack('js')
</body>

</html>