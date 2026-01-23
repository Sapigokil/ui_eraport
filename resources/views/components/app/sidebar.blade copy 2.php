<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 fixed-start" id="sidenav-main" style="background:
linear-gradient(180deg, #212121, #212121);">

    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            id="iconSidenav"></i>

        {{-- LINK KE DASHBOARD --}}
        <a class="navbar-brand d-flex align-items-center m-0" href="{{ route('dashboard') }}" target="_self">
            <span class="font-weight-bold text-lg text-white">E-Rapor Corporate</span>
        </a>
    </div>

    <style>
    /* Mengatasi teks yang memudar pada menu sidebar */
        .nav-sidebar .nav-link p {
            color: #052c65 !important; /* Biru Navy yang sangat gelap agar tajam */
            font-weight: 600 !important; /* Membuat font sedikit lebih tebal */
            opacity: 1 !important;      /* Memastikan tidak ada efek transparansi */
        }

        /* Mengatasi teks pada header sidebar (jika ada) */
        .nav-header {
            color: #000000 !important; /* Hitam pekat untuk label kategori */
            font-weight: bold !important;
            opacity: 1 !important;
        }

        /* Mengatasi teks kecil (badge atau info tambahan) */
        .sidebar .badge {
            font-weight: bold;
        }

        /* MENU AKTIF SIDEBAR: UNDERLINE + BOLD */
        .sidenav .nav-link.active {
            background: transparent !important;   /* hapus block */
            color: #ffffff !important;
            font-weight: 700 !important;           /* bold */
            border-radius: 0 !important;
            position: relative;
        }

        #sidenav-main .collapse .nav-link::before {
            background-color: #ffffff !important; /* PUTIH */
            opacity: 0.6 !important;
        }

        /* DOT submenu AKTIF */
        #sidenav-main .collapse .nav-link.active::before {
            background-color: #ffffff !important;
            opacity: 1 !important;
            transform: scale(1.2);
        }

        #sidenav-main .collapse .nav-link::before {
            width: 8px;
            height: 8px;
            margin-right: 10px;
        }

        /* PANAH DROPDOWN SIDEBAR JADI PUTIH */
        #sidenav-main .nav-link[data-bs-toggle="collapse"]::after {
            color: #ffffff !important;
            border-color: #ffffff !important;
            opacity: 1 !important;
        }

        .sidebar .badge {
            font-weight: bold;
        }

        #sidenav-main {
            height: 100vh;
            overflow: hidden;
        }

        #sidenav-collapse-main {
            height: calc(100vh - 80px);
            overflow-y: auto;
        }

        /* GARIS PEMISAH SIDEBAR */
        #sidenav-main hr.horizontal {
        border-top: 1px solid #ffffff !important;
        background: none !important;
        opacity: 0.6;
        }


    </style>


    <hr class="horizontal light my-2">

    <div class="collapse navbar-collapse px-4 w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">

            {{-- 1. DASHBOARD --}}
            <li class="nav-item">
                @php $isDashboardActive = request()->routeIs('dashboard'); @endphp 
                
                <a class="nav-link {{ $isDashboardActive ? 'active' : 'text-white' }}" href="{{ route('dashboard') }}">
                    <div class="icon icon-shape icon-sm px-0 text-center d-flex align-items-center justify-content-center">
                        <i class="fas fa-chart-line text-sm {{ $isDashboardActive ? 'text-white' : 'text-white opacity-8' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            <hr class="horizontal light my-2">

            {{-- ****************************************************** --}}
            {{-- MENU UTAMA E-RAPOR --}}
            {{-- ****************************************************** --}}
            
            {{-- Logika untuk menentukan apakah menu Master Data harus aktif/terbuka --}}
            @php 
                $masterRoutes = [
                    'master.sekolah.index', 'master.guru.index', 'master.siswa.index', 'master.kelas.index', 
                    'master.mapel.index', 'master.pembelajaran.index', 'master.ekskul.list.*', 'master.ekskul.siswa.*' 
                ];
                $isMasterActive = request()->routeIs($masterRoutes); 
            @endphp
            
            {{-- 2. MASTER DATA --}}
            @can('master.view') 
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#masterDataMenu" class="nav-link {{ $isMasterActive ? 'active' : 'text-white' }}" aria-controls="masterDataMenu" role="button" aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}">
            <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fas fa-database text-white"></i>
            </div>

                    <span class="nav-link-text ms-1">Master Data</span>
                </a>

                <div class="collapse {{ $isMasterActive ? 'show' : '' }}" id="masterDataMenu">
                    <ul class="nav ms-4 ps-3">
                        
                        {{-- SEMUA LINK MASTER DATA --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.sekolah.index') ? 'active' : 'text-white' }}" href="{{ route('master.sekolah.index') }}">
                                <span class="sidenav-mini-icon"> S </span>
                                <!-- <div class="icon icon-shape icon-xs shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-building text-dark"></i>
                                </div> -->
                                <span class="sidenav-normal"> Data Sekolah </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.guru.index') ? 'active' : 'text-white' }}" href="{{ route('master.guru.index') }}">
                                <span class="sidenav-mini-icon"> G </span>
                                <span class="sidenav-normal"> Data Guru </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.siswa.index') ? 'active' : 'text-white' }}" href="{{ route('master.siswa.index') }}">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal"> Data Siswa </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.kelas.index') ? 'active' : 'text-white' }}" href="{{ route('master.kelas.index') }}">
                                <span class="sidenav-mini-icon"> K </span>
                                <span class="sidenav-normal"> Data Kelas </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.mapel.index') ? 'active' : 'text-white' }}" href="{{ route('master.mapel.index') }}">
                                <span class="sidenav-mini-icon"> M </span>
                                <span class="sidenav-normal"> Mata Pelajaran </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.pembelajaran.index') ? 'active' : 'text-white' }}" href="{{ route('master.pembelajaran.index') }}">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal"> Data Pembelajaran </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.ekskul.list.*') ? 'active' : 'text-white' }}" 
                            href="{{ route('master.ekskul.list.index') }}">
                                <span class="sidenav-mini-icon"> EL </span>
                                <span class="sidenav-normal"> List Ekstrakurikuler </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.ekskul.siswa.*') ? 'active' : 'text-white' }}" 
                            href="{{ route('master.ekskul.siswa.index') }}">
                                <span class="sidenav-mini-icon"> EP </span>
                                <span class="sidenav-normal"> Data Peserta Ekskul </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- ============================================================= --}}
            {{-- === MENU BARU: INPUT NILAI (Menu 2) === --}}
            {{-- ============================================================= --}}
            @php
                $nilaiRoutes = [
                    'master.sumatif.*', 'master.project.*', 'master.catatan.*', 'master.nilaiakhir.*'
                ];
                $isNilaiActive = request()->routeIs($nilaiRoutes); 
            @endphp

            @can('master.view') 
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#dataNilaiMenu" class="nav-link {{ $isNilaiActive ? 'active' : 'text-white' }}" aria-controls="dataNilaiMenu" role="button" aria-expanded="{{ $isNilaiActive ? 'true' : 'false' }}">
                    {{-- IKON DIKEMBALIKAN --}}
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-marker text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">Input Nilai</span>
                </a>
                
                <div class="collapse {{ $isNilaiActive ? 'show' : '' }}" id="dataNilaiMenu">
                    <ul class="nav ms-4 ps-3">
                        @php
                            $nilaiRoutes = [
                                'master.sumatif.*', 
                            ];
                            $isNilaiActive = request()->routeIs($nilaiRoutes); 
                        @endphp
                        {{-- Submenu: Nilai Sumatif 1 (Route: master.sumatif.s1) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.sumatif.s1') ? 'active' : 'text-white' }}" 
                            href="{{ route('master.sumatif.s1') }}">
                                <span class="sidenav-mini-icon"> S1 </span>
                                <span class="sidenav-normal"> Nilai Sumatif 1 </span>
                            </a>
                        </li>
                        
                        {{-- Submenu: Nilai Sumatif 2 (Route: master.sumatif.s2) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.sumatif.s2') ? 'active' : 'text-white' }}" 
                            href="{{ route('master.sumatif.s2') }}">
                                <span class="sidenav-mini-icon"> S2 </span>
                                <span class="sidenav-normal"> Nilai Sumatif 2 </span>
                            </a>
                        </li>
                        
                        {{-- Submenu: Nilai Sumatif 3 (Route: master.sumatif.s3) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.sumatif.s3') ? 'active' : 'text-white' }}" 
                            href="{{ route('master.sumatif.s3') }}">
                                <span class="sidenav-mini-icon"> S3 </span>
                                <span class="sidenav-normal"> Nilai Sumatif 3 </span>
                            </a>
                        </li>
                        
                        {{-- Submenu: Nilai Project (Route: master.sumatif.project) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.project.index') ? 'active' : 'text-white' }}" 
                            href="{{ route('master.project.index') }}">
                                <span class="sidenav-mini-icon"> NP </span>
                                <span class="sidenav-normal"> Nilai Project </span>
                            </a>
                        </li>

                        {{-- 🛑 SUB MENU BARU: CATATAN WALIKELAS 🛑 --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.catatan.input') ? 'active' : 'text-white' }}" href="{{ route('master.catatan.input') }}">
                                <span class="sidenav-normal"> Catatan Walikelas </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.nilaiakhir.index') ? 'active' : 'text-white' }}" 
                            href="{{ route('master.nilaiakhir.index') }}">
                                <span class="sidenav-mini-icon"> NA </span>
                                <span class="sidenav-normal"> Nilai Akhir </span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>
            @endcan

            {{-- ============================================================= --}}
            {{-- === UPDATE MENU: DATA RAPOR === --}}
            {{-- ============================================================= --}}
            @php
                // Definisi route agar menu otomatis terbuka (expand) saat diakses
                $raporRoutes = [
                    'rapornilai.index',      
                    'rapornilai.cetak',      
                    'rapornilai.cetak_proses' 
                ];
                $isRaporActive = request()->routeIs($raporRoutes); 
            @endphp

            @can('master.view') 
            @php
                // Logika untuk mengecek apakah sedang di halaman rapor agar menu otomatis terbuka
                $isRaporActive = request()->routeIs('rapornilai.*', 'ledger.*');
            @endphp

            <li class="nav-item">
                {{-- Main Menu Toggle --}}
                <a data-bs-toggle="collapse" href="#dataRaporMenu" 
                    class="nav-link {{ $isRaporActive ? 'active' : 'text-white' }}" 
                    aria-controls="dataRaporMenu" role="button" 
                    aria-expanded="{{ $isRaporActive ? 'true' : 'false' }}">
                    
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        {{-- Warna ikon berubah jadi putih jika aktif (mengikuti gaya Material Dashboard Anda) --}}
                        <i class="fas fa-file-invoice text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">Data Rapor</span>
                </a>
                
                {{-- Submenu Items --}}
                <div class="collapse {{ $isRaporActive ? 'show' : '' }}" id="dataRaporMenu">
                    <ul class="nav ms-4 ps-3">
                        
                        {{-- Menu Monitoring (Yang sudah ada)
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('rapornilai.index') ? 'active' : '' }}" href="{{ route('rapornilai.index') }}">
                                <span class="sidenav-mini-icon">
                                    <i class="fas fa-desktop-invoice text-white"></i>
                                </span>
                                <span class="nav-link-text ms-1 text-white">Monitoring Rapor</span>
                            </a>
                        </li> --}}

                        {{-- Menu Ledger Nilai --}}
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('ledger.ledger_index') ? 'active' : '' }}" href="{{ route('ledger.ledger_index') }}">
                                <span class="sidenav-mini-icon">
                                    <i class="fas fa-desktop-invoice text-white"></i>
                                </span>
                                <span class="nav-link-text ms-1 text-white">Ledger Nilai</span>
                            </a>
                        </li>

                        {{-- MENU BARU: Cetak Rapor --}}
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('rapornilai.cetak') ? 'active' : '' }}" href="{{ route('rapornilai.cetak') }}">
                            <span class="sidenav-mini-icon">
                                <i class="fas fa-desktop-invoice text-white"></i>
                            </span>
                                <span class="nav-link-text ms-1 text-white">Cetak Rapor</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>
            @endcan
            
            {{-- 4. LAPORAN NILAI --}}
            @php $isLaporanActive = request()->routeIs(['laporan.rekap', 'laporan.absensi']); @endphp
            @can('view-laporan') 
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#laporanNilaiMenu" class="nav-link {{ $isLaporanActive ? 'active' : 'text-white' }}" aria-controls="laporanNilaiMenu" role="button" aria-expanded="{{ $isLaporanActive ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-poll text-sm {{ $isLaporanActive ? 'text-white' : 'text-dark opacity-10' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Laporan Nilai</span>
                </a>

                <div class="collapse {{ $isLaporanActive ? 'show' : '' }}" id="laporanNilaiMenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('laporan.rekap') ? 'active' : 'text-white' }}" href="#">Rekap Nilai</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('laporan.absensi') ? 'active' : 'text-white' }}" href="#">Absensi</a></li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- 5. CETAK RAPOR --}}
            @php $isCetakActive = request()->routeIs('cetak.rapor'); @endphp
            @can('cetak-dokumen') 
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('master.rapornilai.cetak') ? 'active' : 'text-white' }}" 
                href="{{ route('master.rapornilai.cetak') }}">
                    <span class="sidenav-mini-icon"> CR </span>
                    <span class="sidenav-normal"> Cetak Rapor </span>
                </a>
            </li>
            @endcan
            
            <hr class="horizontal light my-2">
            
            {{-- 6. MANAJEMEN PENGGUNA --}}
            @php 
                $managementRoutes = ['master.users.*',
                                    'master.roles.*'];
                $isManagementActive = request()->routeIs($managementRoutes)
            @endphp
            
            @can('pengaturan-manage-users')
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#manajemenUserMenu" class="nav-link {{ $isManagementActive ? 'active' : 'text-white' }}" aria-controls="manajemenUserMenu" role="button" aria-expanded="{{ $isManagementActive ? 'true' : 'false' }}">
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-users-cog text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">Manajemen User</span>
                </a>

                <div class="collapse {{ $isManagementActive ? 'show' : '' }}" id="manajemenUserMenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.users.index') ? 'active' : 'text-white' }}" href="{{ route('master.users.index') }}">
                                <span class="sidenav-mini-icon"> U </span>
                                <span class="sidenav-normal"> Daftar User </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.roles.index') ? 'active' : 'text-white' }}" href="{{ route('master.roles.index') }}">
                                <span class="sidenav-mini-icon"> R </span>
                                <span class="sidenav-normal"> Role & Izin </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- 🛑 6. MENU BARU: PENGATURAN 🛑 --}}
            @php 
                $pengaturanRoutes = ['pengaturan.kok.index',
                                    'pengaturan.bobot.index',
                                    'pengaturan.input.index',];
                $isPengaturanActive = request()->routeIs($pengaturanRoutes);
            @endphp
            @can('pengaturan-manage-users') {{-- Gunakan permission yang sesuai --}}
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#pengaturanMenu" class="nav-link {{ $isPengaturanActive ? 'active' : 'text-white' }}" aria-controls="pengaturanMenu" role="button" aria-expanded="{{ $isPengaturanActive ? 'true' : 'false' }}">
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-cogs text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">Pengaturan</span>
                </a>

                <div class="collapse {{ $isPengaturanActive ? 'show' : '' }}" id="pengaturanMenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('pengaturan.kok.index') ? 'active' : 'text-white' }}" href="{{ route('pengaturan.kok.index') }}">
                                <span class="sidenav-mini-icon"> K </span>
                                <span class="sidenav-normal"> Kokurikuler </span>
                            </a>
                        </li>
                        {{-- Pengaturan Bobot Nilai --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('pengaturan.bobot.index') ? 'active' : 'text-white' }}" href="{{ route('pengaturan.bobot.index') }}">
                                <span class="sidenav-mini-icon"> B </span>
                                <span class="sidenav-normal"> Bobot Nilai </span>
                            </a>
                        </li>
                        {{-- Pengaturan Input Event Dashboard --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('pengaturan.input.index') ? 'active' : 'text-white' }}" href="{{ route('pengaturan.input.index') }}">
                                <span class="sidenav-mini-icon"> B </span>
                                <span class="sidenav-normal"> Input Event </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            

            {{-- 7. PROFIL SAYA --}}
            @php $isProfileActive = false; @endphp
            <li class="nav-item">
                <a class="nav-link {{ $isProfileActive ? 'active' : 'text-white' }}" href="{{ route('profile.index') }}">
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profil Saya</span>
                </a>
            </li>

        </ul>
        @endcan
    </div>

    <div class="sidenav-footer mx-4">
        {{-- Footer template default --}}
    </div>
</aside>