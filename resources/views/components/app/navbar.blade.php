<nav class="navbar navbar-main navbar-expand-lg mx-5 px-0 shadow-none rounded" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-2 d-flex justify-content-between align-items-center">

        {{-- =========================================================
             BAGIAN KIRI (Urutan: Hamburger -> Breadcrumb)
             ========================================================= --}}
        <div class="d-flex align-items-center">
            
            {{-- 1. HAMBURGER MENU (Mobile Only / d-xl-none) --}}
            {{-- Pindahkan dari dalam <ul> kanan ke sini --}}
            <div class="d-xl-none me-3 d-flex align-items-center">
                <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                    <div class="sidenav-toggler-inner">
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                    </div>
                </a>
            </div>

            {{-- 2. BREADCRUMB & JUDUL HALAMAN --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-1 pb-0 pt-1 px-0 me-sm-6 me-5">
                    <li class="breadcrumb-item text-sm">
                        <a class="opacity-5 text-dark" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">
                        @yield('page-title', 'Dashboard')
                    </li>
                </ol>
                <h6 class="font-weight-bold mb-0">
                    @yield('page-title', 'Dashboard')
                </h6>
            </nav>
        </div>

        {{-- 3. KOSONG (SPACER OTOMATIS OLEH justify-content-between) --}}


        {{-- =========================================================
             BAGIAN KANAN (Urutan Kanan ke Kiri: Profile -> Logout)
             ========================================================= --}}
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 justify-content-end" id="navbar">
            
            <ul class="navbar-nav justify-content-end">
                
                {{-- 2. LOGOUT (Urutan ke-2 dari kanan) --}}
                <li class="nav-item d-flex align-items-center me-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        {{-- PERUBAHAN DI SINI: --}}
                        {{-- 1. 'border border-dark' : Menambahkan garis tepi hitam --}}
                        {{-- 2. 'rounded' : Membuat sudut sedikit melengkung (opsional) --}}
                        {{-- 3. 'px-3' : Memberikan jarak kiri-kanan (ganti px-0) --}}
                        <a href="login" onclick="event.preventDefault(); this.closest('form').submit();" 
                        class="nav-link text-body font-weight-bold border border-dark rounded px-3">
                            
                            <i class="fa fa-user me-sm-1"></i> 
                            <span class="d-sm-inline d-none">Log out</span>
                        </a>
                    </form>
                </li>

                {{-- 1. PROFILE PENGGUNA (Urutan ke-1 paling kanan) --}}
                <li class="nav-item ps-2 d-flex align-items-center">
                    <a href="{{ route('profile.index') }}" class="nav-link text-body p-0">
                        <img src="{{ asset('assets/img/team-2.jpg') }}" class="avatar avatar-sm" alt="avatar" />
                    </a>
                </li>

                {{-- Item lain seperti Setting/Notif sudah dihapus sesuai permintaan "Kosong" --}}
            </ul>
        </div>

    </div>
</nav>