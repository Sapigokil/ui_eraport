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