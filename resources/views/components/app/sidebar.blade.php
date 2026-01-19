<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 fixed-start" id="sidenav-main" style="background: linear-gradient(180deg, #212121, #212121);">

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
        .nav-sidebar .nav-link p { color: #052c65 !important; font-weight: 600 !important; opacity: 1 !important; }
        .nav-header { color: #000000 !important; font-weight: bold !important; opacity: 1 !important; }
        .sidebar .badge { font-weight: bold; }

        /* MENU AKTIF SIDEBAR: UNDERLINE + BOLD */
        .sidenav .nav-link.active {
            background: transparent !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            border-radius: 0 !important;
            position: relative;
        }

        #sidenav-main .collapse .nav-link::before { background-color: #ffffff !important; opacity: 0.6 !important; }
        
        /* DOT submenu AKTIF */
        #sidenav-main .collapse .nav-link.active::before {
            background-color: #ffffff !important;
            opacity: 1 !important;
            transform: scale(1.2);
        }

        #sidenav-main .collapse .nav-link::before { width: 8px; height: 8px; margin-right: 10px; }
        
        /* PANAH DROPDOWN SIDEBAR JADI PUTIH */
        #sidenav-main .nav-link[data-bs-toggle="collapse"]::after {
            color: #ffffff !important;
            border-color: #ffffff !important;
            opacity: 1 !important;
        }

        .sidebar .badge { font-weight: bold; }
        #sidenav-main { height: 100vh; overflow: hidden; }
        #sidenav-collapse-main { height: calc(100vh - 80px); overflow-y: auto; }

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

            {{-- ========================================================= --}}
            {{-- 1. DASHBOARD --}}
            {{-- ========================================================= --}}
            @can('dashboard.view')
            <li class="nav-item">
                @php $isDashboardActive = request()->routeIs('dashboard'); @endphp 
                
                <a class="nav-link {{ $isDashboardActive ? 'active' : 'text-white' }}" href="{{ route('dashboard') }}">
                    <div class="icon icon-shape icon-sm px-0 text-center d-flex align-items-center justify-content-center">
                        <i class="fas fa-chart-line text-sm {{ $isDashboardActive ? 'text-white' : 'text-white opacity-8' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            @endcan

            <hr class="horizontal light my-2">

            {{-- ========================================================= --}}
            {{-- 2. MASTER DATA (Admin Only) --}}
            {{-- ========================================================= --}}
            @can('master.view') 
            @php 
                // Definisi route spesifik agar tidak bentrok dengan 'master.nilai'
                $masterRoutes = [
                    'master.sekolah.*', 'master.guru.*', 'master.siswa.*', 'master.kelas.*', 
                    'master.mapel.*', 'master.pembelajaran.*', 'master.ekskul.*'
                ];
                $isMasterActive = request()->routeIs($masterRoutes); 
            @endphp
            
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#masterDataMenu" class="nav-link {{ $isMasterActive ? 'active' : 'text-white' }}" aria-controls="masterDataMenu" role="button" aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}">
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-database text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">Master Data</span>
                </a>

                <div class="collapse {{ $isMasterActive ? 'show' : '' }}" id="masterDataMenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.sekolah.*') ? 'active' : 'text-white' }}" href="{{ route('master.sekolah.index') }}"><span class="sidenav-mini-icon"> S </span><span class="sidenav-normal"> Data Sekolah </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.guru.*') ? 'active' : 'text-white' }}" href="{{ route('master.guru.index') }}"><span class="sidenav-mini-icon"> G </span><span class="sidenav-normal"> Data Guru </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.siswa.*') ? 'active' : 'text-white' }}" href="{{ route('master.siswa.index') }}"><span class="sidenav-mini-icon"> S </span><span class="sidenav-normal"> Data Siswa </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.kelas.*') ? 'active' : 'text-white' }}" href="{{ route('master.kelas.index') }}"><span class="sidenav-mini-icon"> K </span><span class="sidenav-normal"> Data Kelas </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.mapel.*') ? 'active' : 'text-white' }}" href="{{ route('master.mapel.index') }}"><span class="sidenav-mini-icon"> M </span><span class="sidenav-normal"> Mata Pelajaran </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.pembelajaran.*') ? 'active' : 'text-white' }}" href="{{ route('master.pembelajaran.index') }}"><span class="sidenav-mini-icon"> P </span><span class="sidenav-normal"> Pembelajaran </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.ekskul.list.*') ? 'active' : 'text-white' }}" href="{{ route('master.ekskul.list.index') }}"><span class="sidenav-mini-icon"> EL </span><span class="sidenav-normal"> List Ekskul </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.ekskul.siswa.*') ? 'active' : 'text-white' }}" href="{{ route('master.ekskul.siswa.index') }}"><span class="sidenav-mini-icon"> EP </span><span class="sidenav-normal"> Peserta Ekskul </span></a></li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- ========================================================= --}}
            {{-- 3. INPUT NILAI (Guru & Admin) --}}
            {{-- ========================================================= --}}
            @can('nilai.view')
            @php
                $nilaiRoutes = ['master.sumatif.*', 'master.project.*', 'master.catatan.*', 'master.nilaiakhir.*'];
                $isNilaiActive = request()->routeIs($nilaiRoutes); 
            @endphp

            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#dataNilaiMenu" class="nav-link {{ $isNilaiActive ? 'active' : 'text-white' }}" aria-controls="dataNilaiMenu" role="button" aria-expanded="{{ $isNilaiActive ? 'true' : 'false' }}">
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-marker text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">Input Nilai</span>
                </a>
                
                <div class="collapse {{ $isNilaiActive ? 'show' : '' }}" id="dataNilaiMenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.sumatif.s1') ? 'active' : 'text-white' }}" href="{{ route('master.sumatif.s1') }}"><span class="sidenav-mini-icon"> S1 </span><span class="sidenav-normal"> Nilai Sumatif 1 </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.sumatif.s2') ? 'active' : 'text-white' }}" href="{{ route('master.sumatif.s2') }}"><span class="sidenav-mini-icon"> S2 </span><span class="sidenav-normal"> Nilai Sumatif 2 </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.sumatif.s3') ? 'active' : 'text-white' }}" href="{{ route('master.sumatif.s3') }}"><span class="sidenav-mini-icon"> S3 </span><span class="sidenav-normal"> Nilai Sumatif 3 </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.sumatif.s4') ? 'active' : 'text-white' }}" href="{{ route('master.sumatif.s4') }}"><span class="sidenav-mini-icon"> S4 </span><span class="sidenav-normal"> Nilai Sumatif 4 </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.sumatif.s5') ? 'active' : 'text-white' }}" href="{{ route('master.sumatif.s5') }}"><span class="sidenav-mini-icon"> S5 </span><span class="sidenav-normal"> Nilai Sumatif 5 </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.project.index') ? 'active' : 'text-white' }}" href="{{ route('master.project.index') }}"><span class="sidenav-mini-icon"> P5 </span><span class="sidenav-normal"> Nilai Project </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.catatan.input') ? 'active' : 'text-white' }}" href="{{ route('master.catatan.input') }}"><span class="sidenav-normal"> Catatan Walikelas </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.nilaiakhir.index') ? 'active' : 'text-white' }}" href="{{ route('master.nilaiakhir.index') }}"><span class="sidenav-mini-icon"> NA </span><span class="sidenav-normal"> Nilai Akhir </span></a></li>
                    </ul>
                </div> 
            </li>
            @endcan

            {{-- ========================================================= --}}
            {{-- 4. LAPORAN & RAPOR (Guru & Admin) --}}
            {{-- ========================================================= --}}
            @canany(['rapor.view', 'ledger.view'])
            @php
                $raporRoutes = ['rapornilai.*', 'ledger.*'];
                $isRaporActive = request()->routeIs($raporRoutes);
            @endphp

            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#dataRaporMenu" class="nav-link {{ $isRaporActive ? 'active' : 'text-white' }}" aria-controls="dataRaporMenu" role="button" aria-expanded="{{ $isRaporActive ? 'true' : 'false' }}">
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-file-invoice text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">Data Rapor</span>
                </a>
                
                <div class="collapse {{ $isRaporActive ? 'show' : '' }}" id="dataRaporMenu">
                    <ul class="nav ms-4 ps-3">
                        
                        @can('ledger.view')
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('ledger.ledger_index') ? 'active' : 'text-white' }}" href="{{ route('ledger.ledger_index') }}">
                                <span class="sidenav-mini-icon"><i class="fas fa-table text-xs"></i></span>
                                <span class="nav-link-text ms-1">Ledger Nilai</span>
                            </a>
                        </li>
                        @endcan

                        @can('rapor.view')
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('rapornilai.cetak') ? 'active' : 'text-white' }}" href="{{ route('rapornilai.cetak') }}">
                                <span class="sidenav-mini-icon"><i class="fas fa-print text-xs"></i></span>
                                <span class="nav-link-text ms-1">Cetak Rapor</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany
            
            <hr class="horizontal light my-2">
            
            {{-- ========================================================= --}}
            {{-- 5. SETTING: ERAPOR / AKADEMIK (Admin & Admin Erapor) --}}
            {{-- ========================================================= --}}
            @can('settings.erapor.read')
            @php 
                $eraporSettingRoutes = ['settings.erapor.*'];
                $isEraporSetActive = request()->routeIs($eraporSettingRoutes);
            @endphp

            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#settingEraporMenu" class="nav-link {{ $isEraporSetActive ? 'active' : 'text-white' }}" aria-controls="settingEraporMenu" role="button" aria-expanded="{{ $isEraporSetActive ? 'true' : 'false' }}">
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sliders-h text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">Setting E-Rapor</span>
                </a>

                <div class="collapse {{ $isEraporSetActive ? 'show' : '' }}" id="settingEraporMenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.kok.index') ? 'active' : 'text-white' }}" href="{{ route('settings.erapor.kok.index') }}"><span class="sidenav-mini-icon"> K </span><span class="sidenav-normal"> Set Kokurikuler </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.bobot.index') ? 'active' : 'text-white' }}" href="{{ route('settings.erapor.bobot.index') }}"><span class="sidenav-mini-icon"> B </span><span class="sidenav-normal"> Bobot Nilai </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.input.index') ? 'active' : 'text-white' }}" href="{{ route('settings.erapor.input.index') }}"><span class="sidenav-mini-icon"> E </span><span class="sidenav-normal"> Event Dashboard </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.season.index') ? 'active' : 'text-white' }}" href="{{ route('settings.erapor.season.index') }}"><span class="sidenav-mini-icon"> S </span><span class="sidenav-normal"> Set Season </span></a></li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- ========================================================= --}}
            {{-- 6. SETTING: SYSTEM (Admin Only) --}}
            {{-- ========================================================= --}}
            @canany(['users.read', 'roles.read'])
            @php 
                $systemSettingRoutes = ['settings.system.*'];
                $isSystemSetActive = request()->routeIs($systemSettingRoutes);
            @endphp
            
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#settingSystemMenu" class="nav-link {{ $isSystemSetActive ? 'active' : 'text-white' }}" aria-controls="settingSystemMenu" role="button" aria-expanded="{{ $isSystemSetActive ? 'true' : 'false' }}">
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-shield text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">System & User</span>
                </a>

                <div class="collapse {{ $isSystemSetActive ? 'show' : '' }}" id="settingSystemMenu">
                    <ul class="nav ms-4 ps-3">
                        @can('users.read')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.system.users.index') ? 'active' : 'text-white' }}" href="{{ route('settings.system.users.index') }}"><span class="sidenav-mini-icon"> U </span><span class="sidenav-normal"> Manajemen User </span></a></li>
                        @endcan
                        
                        @can('roles.read')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.system.roles.index') ? 'active' : 'text-white' }}" href="{{ route('settings.system.roles.index') }}"><span class="sidenav-mini-icon"> R </span><span class="sidenav-normal"> Role & Permission </span></a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            {{-- ========================================================= --}}
            {{-- 7. PROFIL SAYA (SEMUA USER) --}}
            {{-- ========================================================= --}}
            @php $isProfileActive = request()->routeIs('profile.*'); @endphp
            <li class="nav-item">
                <a class="nav-link {{ $isProfileActive ? 'active' : 'text-white' }}" href="{{ route('profile.index') }}">
                    <div class="me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profil Saya</span>
                </a>
            </li>

        </ul>
    </div>

    <div class="sidenav-footer mx-4">
        {{-- Footer template default --}}
    </div>
</aside>