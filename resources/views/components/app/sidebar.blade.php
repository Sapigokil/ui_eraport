<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 fixed-start shadow-sm" id="sidenav-main" style="background-color: #25183b; border-right: 1px solid rgba(255,255,255,0.05) !important;">

    <div class="sidenav-header mb-3">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" id="iconSidenav"></i>

        <a class="navbar-brand d-flex align-items-center m-0 pl-3 pt-4 pb-3" href="{{ route('dashboard') }}" target="_self" style="width: 100%;">
            <div class="d-flex flex-column justify-content-center">
                <span class="font-weight-bolder text-white text-uppercase" style="font-size: 1.2rem; letter-spacing: 1px;">
                    E-RAPOR
                </span>
                <div class="d-flex align-items-center mt-1">
                    <span class="text-white opacity-8 font-weight-bold" style="font-size: 0.75rem;">
                        SMKN 1 Salatiga
                    </span>
                    <span class="badge ms-2" style="background-color: rgba(255,255,255,0.15); color: #fff; font-size: 0.6rem; padding: 4px 6px; border-radius: 4px;">
                        v{{ config('app_history.current_version') }}
                    </span>
                </div>
            </div>
        </a>
    </div>

    <style>
        /* === MINIMALIST CLASSIC DARK PURPLE SIDEBAR CSS === */
        
        /* Custom Scrollbar */
        #sidenav-collapse-main::-webkit-scrollbar { width: 4px; }
        #sidenav-collapse-main::-webkit-scrollbar-track { background: transparent; }
        #sidenav-collapse-main::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 4px; }
        #sidenav-collapse-main::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }

        /* Mencegah default padding UL bawaan Bootstrap */
        .sidenav .navbar-nav {
            padding-left: 0 !important;
        }

        /* Menu Item Standar (Level 1) - SUPER RAPAT KIRI */
        .sidenav .nav-link {
            color: rgba(255, 255, 255, 0.7) !important;
            font-weight: 500 !important;
            border-radius: 6px !important;
            margin: 0.15rem 0.25rem !important; /* Margin luar sangat tipis */
            padding: 0.65rem 0.5rem !important; /* Padding dalam (kiri) ditipiskan */
            transition: all 0.2s ease;
            position: relative;
        }

        /* Hover Effect */
        .sidenav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #ffffff !important;
        }

        /* Menu Aktif Induk (Level 1) */
        .navbar-nav > .nav-item > .nav-link.active {
            background-color: transparent !important; 
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: none !important;
        }

        /* Indikator Garis Kiri untuk Menu Induk Aktif - Ditempelkan ke ujung kiri */
        .navbar-nav > .nav-item > .nav-link.active::before {
            content: '';
            position: absolute;
            left: -0.25rem; /* Menyesuaikan margin luar agar pas di tepi */
            top: 15%;
            height: 70%;
            width: 4px;
            background-color: #b088ff;
            border-radius: 0 4px 4px 0;
            display: block;
        }

        /* Submenu Aktif (Level 2) */
        .navbar-nav .collapse .nav-link.active {
            background-color: transparent !important; 
            color: #b088ff !important;
            font-weight: 700 !important;
            box-shadow: none !important;
        }

        .navbar-nav .collapse .nav-link.active::before {
            display: none;
        }

        /* Sub-Menu Dropdown Styling - SUPER RAPAT KIRI */
        #sidenav-main .collapse .nav-link {
            margin: 0.15rem 0.25rem 0.15rem 1.5rem !important; /* Jarak indent kiri dikurangi dari 2.8rem jadi 1.5rem */
            padding: 0.5rem 0.5rem !important; /* Padding dalam (kiri) dikurangi */
            font-size: 0.85rem !important;
        }

        /* Panah Dropdown Sidebar */
        #sidenav-main .nav-link[data-bs-toggle="collapse"]::after {
            color: #ffffff !important;
            opacity: 0.5;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }
        #sidenav-main .nav-link[data-bs-toggle="collapse"][aria-expanded="true"]::after {
            opacity: 1;
        }

        /* Ikon Menu */
        .sidenav .nav-link .icon, .sidenav .nav-link i.fas {
            color: inherit !important;
            opacity: 0.7;
            transition: all 0.2s ease;
        }
        .sidenav .nav-link:hover .icon, .sidenav .nav-link:hover i.fas,
        .sidenav .nav-link.active .icon, .sidenav .nav-link.active i.fas {
            opacity: 1;
        }

        /* Label Kategori - SUPER RAPAT KIRI */
        .sidenav-category {
            font-size: 0.7rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 0.5px;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            padding-left: 0.75rem; /* Teks label kategori digeser mentok kiri */
        }

        /* Garis Pemisah (Divider) */
        #sidenav-main hr.horizontal.light {
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
            background: none !important;
        }
    </style>

    <div class="collapse navbar-collapse px-0 w-auto" id="sidenav-collapse-main" style="height: calc(100vh - 100px); overflow-x: hidden;">
        <ul class="navbar-nav">

            {{-- ========================================================= --}}
            {{-- 1. DASHBOARD --}}
            {{-- ========================================================= --}}
            @can('dashboard.view')
            <li class="nav-item">
                @php $isDashboardActive = request()->routeIs('dashboard'); @endphp 
                <a class="nav-link {{ $isDashboardActive ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-chart-line text-sm"></i>
                    </div>
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            @endcan

            {{-- ========================================================= --}}
            {{-- 2. DATA POKOK (Master, PKL, Mutasi) --}}
            {{-- ========================================================= --}}
            @canany(['master.menu', 'pkl.data.menu', 'mutasi.menu'])
            <li class="nav-item mt-3">
                <div class="sidenav-category text-uppercase">Data Pokok</div>
            </li>
            @endcanany

            {{-- MASTER DATA --}}
            @can('master.menu') 
            @php 
                $masterRoutes = [
                    'master.sekolah.*', 'master.guru.*', 'master.siswa.*', 'master.kelas.*', 
                    'master.mapel.*', 'master.pembelajaran.*', 'master.ekskul.*'
                ];
                $isMasterActive = request()->routeIs($masterRoutes); 
            @endphp
            
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#masterDataMenu" class="nav-link {{ $isMasterActive ? 'active' : '' }}" aria-controls="masterDataMenu" role="button" aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-database text-sm"></i>
                    </div>
                    <span class="nav-link-text">Master Data</span>
                </a>

                <div class="collapse {{ $isMasterActive ? 'show' : '' }}" id="masterDataMenu">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.sekolah.*') ? 'active' : '' }}" href="{{ route('master.sekolah.index') }}"><span class="sidenav-normal"> Data Sekolah </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.guru.*') ? 'active' : '' }}" href="{{ route('master.guru.index') }}"><span class="sidenav-normal"> Data Guru </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.siswa.*') ? 'active' : '' }}" href="{{ route('master.siswa.index') }}"><span class="sidenav-normal"> Data Siswa </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.kelas.*') ? 'active' : '' }}" href="{{ route('master.kelas.index') }}"><span class="sidenav-normal"> Data Kelas </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.mapel.*') ? 'active' : '' }}" href="{{ route('master.mapel.index') }}"><span class="sidenav-normal"> Mata Pelajaran </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.pembelajaran.*') ? 'active' : '' }}" href="{{ route('master.pembelajaran.index') }}"><span class="sidenav-normal"> Pembelajaran </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.ekskul.list.*') ? 'active' : '' }}" href="{{ route('master.ekskul.list.index') }}"><span class="sidenav-normal"> List Ekskul </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.ekskul.siswa.*') ? 'active' : '' }}" href="{{ route('master.ekskul.siswa.index') }}"><span class="sidenav-normal"> Peserta Ekskul </span></a></li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- DATA PKL --}}
            @can('pkl.data.menu')
            @php
                $pklRoutes = ['pkl.tempat.*', 'pkl.gurusiswa.*', 'pkl.penempatan.*']; 
                $isPklActive = request()->routeIs($pklRoutes); 
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#menuPkl" class="nav-link {{ $isPklActive ? 'active' : '' }}" aria-controls="menuPkl" role="button" aria-expanded="{{ $isPklActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-briefcase text-sm"></i>
                    </div>
                    <span class="nav-link-text">Data PKL</span>
                </a>
                
                <div class="collapse {{ $isPklActive ? 'show' : '' }}" id="menuPkl">
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('pkl.tempat.*') ? 'active' : '' }}" href="{{ route('pkl.tempat.index') }}">
                                <span class="sidenav-normal"> Tempat PKL </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('pkl.gurusiswa.*') ? 'active' : '' }}" href="{{ route('pkl.gurusiswa.index') }}">
                                <span class="sidenav-normal"> Guru Pembimbing </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('pkl.penempatan.*') ? 'active' : '' }}" href="{{ route('pkl.penempatan.index') }}">
                                <span class="sidenav-normal"> Penempatan PKL </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- MUTASI & KENAIKAN --}}
            @can('mutasi.menu')
            @php
                $mutasiRoutes = ['mutasi.keluar.*', 'mutasi.pindah.*', 'mutasi.kenaikan.*', 'mutasi.kelulusan.*', 'mutasi.dashboard_akhir.*', 'mutasi.riwayat.*'];
                $isMutasiActive = request()->routeIs($mutasiRoutes); 
            @endphp
            
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#menuMutasi" class="nav-link {{ $isMutasiActive ? 'active' : '' }}" aria-controls="menuMutasi" role="button" aria-expanded="{{ $isMutasiActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-exchange-alt text-sm"></i>
                    </div>
                    <span class="nav-link-text">Mutasi Siswa</span>
                </a>
                
                <div class="collapse {{ $isMutasiActive ? 'show' : '' }}" id="menuMutasi">
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('mutasi.keluar.*') ? 'active' : '' }}" href="{{ route('mutasi.keluar.index') }}">
                                <span class="sidenav-normal"> Mutasi Keluar </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('mutasi.pindah.*') ? 'active' : '' }}" href="{{ route('mutasi.pindah.index') }}">
                                <span class="sidenav-normal"> Pindah Kelas </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('mutasi.dashboard_akhir.*', 'mutasi.kenaikan.*', 'mutasi.kelulusan.*') ? 'active' : '' }}" href="{{ route('mutasi.dashboard_akhir.index') }}">
                                <span class="sidenav-normal"> Kenaikan Kelulusan </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('mutasi.riwayat.*') ? 'active' : '' }}" href="{{ route('mutasi.riwayat.index') }}">
                                <span class="sidenav-normal"> Riwayat Kenaikan </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            
            <hr class="horizontal light my-2">

            {{-- ========================================================= --}}
            {{-- 4. AKADEMIK (GURU & WALI KELAS) --}}
            {{-- ========================================================= --}}
            @canany(['nilai.menu', 'ekskul.menu', 'rapor.menu', 'ledger.menu'])
            <li class="nav-item mt-3">
                <div class="sidenav-category text-uppercase">Akademik</div>
            </li>
            @endcanany

            {{-- INPUT NILAI --}}
            @can('nilai.menu')
            @php
                $nilaiRoutes = ['nilai.sumatif.*', 'nilai.project.*', 'nilai.catatan.*', 'nilai.rekap.*'];
                $isNilaiActive = request()->routeIs($nilaiRoutes); 
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#dataNilaiMenu" class="nav-link {{ $isNilaiActive ? 'active' : '' }}" aria-controls="dataNilaiMenu" role="button" aria-expanded="{{ $isNilaiActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-marker text-sm"></i>
                    </div>
                    <span class="nav-link-text">Input Nilai</span>
                </a>
                <div class="collapse {{ $isNilaiActive ? 'show' : '' }}" id="dataNilaiMenu">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.sumatif.s1') ? 'active' : '' }}" href="{{ route('nilai.sumatif.s1') }}"><span class="sidenav-normal"> Nilai Sumatif 1 </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.sumatif.s2') ? 'active' : '' }}" href="{{ route('nilai.sumatif.s2') }}"><span class="sidenav-normal"> Nilai Sumatif 2 </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.sumatif.s3') ? 'active' : '' }}" href="{{ route('nilai.sumatif.s3') }}"><span class="sidenav-normal"> Nilai Sumatif 3 </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.sumatif.s4') ? 'active' : '' }}" href="{{ route('nilai.sumatif.s4') }}"><span class="sidenav-normal"> Nilai Sumatif 4 </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.sumatif.s5') ? 'active' : '' }}" href="{{ route('nilai.sumatif.s5') }}"><span class="sidenav-normal"> Nilai Sumatif 5 </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.project.index') ? 'active' : '' }}" href="{{ route('nilai.project.index') }}"><span class="sidenav-normal"> Nilai Project </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.rekap.*') ? 'active' : '' }}" href="{{ route('nilai.rekap.index') }}"><span class="sidenav-normal"> Rekap Nilai </span></a></li>
                    </ul>
                </div> 
            </li>
            @endcan

            {{-- EKSTRAKURIKULER --}}
            @can('ekskul.menu')
            @php
                $ekskulActiveRoutes = ['ekskul.peserta.*', 'ekskul.nilai.*'];
                $isEkskulActive = request()->routeIs($ekskulActiveRoutes); 
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#inputEkskulMenu" class="nav-link {{ $isEkskulActive ? 'active' : '' }}" aria-controls="inputEkskulMenu" role="button" aria-expanded="{{ $isEkskulActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-futbol text-sm"></i>
                    </div>
                    <span class="nav-link-text">Ekstrakurikuler</span>
                </a>
                <div class="collapse {{ $isEkskulActive ? 'show' : '' }}" id="inputEkskulMenu">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('ekskul.peserta.*') ? 'active' : '' }}" href="{{ route('ekskul.peserta.index') }}"><span class="sidenav-normal"> Peserta Ekskul </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('ekskul.nilai.*') ? 'active' : '' }}" href="{{ route('ekskul.nilai.index') }}"><span class="sidenav-normal"> Input Nilai </span></a></li>
                    </ul>
                </div> 
            </li>
            @endcan

            {{-- WALI KELAS --}}
            @can('nilai.menu')
            @php
                $waliRoutes = ['walikelas.*'];
                $isWaliActive = request()->routeIs($waliRoutes); 
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#waliKelasMenu" class="nav-link {{ $isWaliActive ? 'active' : '' }}" aria-controls="waliKelasMenu" role="button" aria-expanded="{{ $isWaliActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-user-graduate text-sm"></i>
                    </div>
                    <span class="nav-link-text">Tugas Wali Kelas</span>
                </a>
                <div class="collapse {{ $isWaliActive ? 'show' : '' }}" id="waliKelasMenu">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('walikelas.catatan.input') ? 'active' : '' }}" href="{{ route('walikelas.catatan.input') }}"><span class="sidenav-normal">Catatan Walikelas</span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('walikelas.monitoring.wali') ? 'active' : '' }}" href="{{ route('walikelas.monitoring.wali') }}"><span class="sidenav-normal">Finalisasi Nilai</span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('walikelas.cakok.index') ? 'active' : '' }}" href="{{ route('walikelas.cakok.index') }}"><span class="sidenav-normal">Set Template</span></a></li>
                    </ul>
                </div> 
            </li>
            @endcan

            {{-- LAPORAN & RAPOR --}}
            @canany(['rapor.menu', 'ledger.menu'])
            @php
                $raporRoutes = ['rapornilai.*', 'ledger.*'];
                $isRaporActive = request()->routeIs($raporRoutes);
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#dataRaporMenu" class="nav-link {{ $isRaporActive ? 'active' : '' }}" aria-controls="dataRaporMenu" role="button" aria-expanded="{{ $isRaporActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-file-invoice text-sm"></i>
                    </div>
                    <span class="nav-link-text">Data Rapor</span>
                </a>
                <div class="collapse {{ $isRaporActive ? 'show' : '' }}" id="dataRaporMenu">
                    <ul class="nav">
                        @can('rapor.menu')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('rapornilai.nilaiakhir.index') ? 'active' : '' }}" href="{{ route('rapornilai.nilaiakhir.index') }}"><span class="sidenav-normal">Nilai Akhir</span></a></li>
                        <li class="nav-item"><a class="nav-link {{ Route::is('rapornilai.monitoring.index') ? 'active' : '' }}" href="{{ route('rapornilai.monitoring.index') }}"><span class="sidenav-normal">Monitoring Rapor</span></a></li>
                        <li class="nav-item"><a class="nav-link {{ Route::is('rapornilai.cetak') ? 'active' : '' }}" href="{{ route('rapornilai.cetak') }}"><span class="sidenav-normal">Cetak Rapor</span></a></li>
                        <li class="nav-item"><a class="nav-link {{ Route::is('rapornilai.cover.index') ? 'active' : '' }}" href="{{ route('rapornilai.cover.index') }}"><span class="sidenav-normal">Cetak Cover Rapor</span></a></li>
                        @endcan
                        @can('ledger.menu')
                        <li class="nav-item"><a class="nav-link {{ Route::is('ledger.ledger_index') ? 'active' : '' }}" href="{{ route('ledger.ledger_index') }}"><span class="sidenav-normal">Ledger Nilai</span></a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            <hr class="horizontal light my-2">

            {{-- ========================================================= --}}
            {{-- 5. PRAKERIN / PKL --}}
            {{-- ========================================================= --}}
            @canany(['pkl.nilai.menu', 'rapor.menu'])
            <li class="nav-item mt-3">
                <div class="sidenav-category text-uppercase">Prakerin</div>
            </li>
            @endcanany

            {{-- Penilaian Prakerin --}}
            @can('pkl.nilai.menu')
            @php
                $pklNilaiRoutes = ['pkl.nilai.index', 'pkl.nilai.rekap'];
                $isPklNilaiActive = request()->routeIs($pklNilaiRoutes); 
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#penilaianPrakerinMenu" class="nav-link {{ $isPklNilaiActive ? 'active' : '' }}" aria-controls="penilaianPrakerinMenu" role="button" aria-expanded="{{ $isPklNilaiActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-clipboard-check text-sm"></i>
                    </div>
                    <span class="nav-link-text">Penilaian Prakerin</span>
                </a>
                <div class="collapse {{ $isPklNilaiActive ? 'show' : '' }}" id="penilaianPrakerinMenu">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('pkl.nilai.index') ? 'active' : '' }}" href="{{ route('pkl.nilai.index') }}"><span class="sidenav-normal"> Input Nilai </span></a></li>
                    </ul>
                </div> 
            </li>
            @endcan

            {{-- Data Rapor PKL --}}
            @can('rapor.menu')
            @php
                $pklRaporRoutes = ['pkl.rapor.monitoring.index', 'pkl.rapor.cetak.index'];
                $isPklRaporActive = request()->routeIs($pklRaporRoutes); 
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#raporPrakerinMenu" class="nav-link {{ $isPklRaporActive ? 'active' : '' }}" aria-controls="raporPrakerinMenu" role="button" aria-expanded="{{ $isPklRaporActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-print text-sm"></i>
                    </div>
                    <span class="nav-link-text">Data Rapor</span>
                </a>
                <div class="collapse {{ $isPklRaporActive ? 'show' : '' }}" id="raporPrakerinMenu">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('pkl.rapor.monitoring.index') ? 'active' : '' }}" href="{{ route('pkl.rapor.monitoring.index') }}"><span class="sidenav-normal"> Monitoring Rapor </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('pkl.rapor.cetak.index') ? 'active' : '' }}" href="{{ route('pkl.rapor.cetak.index') }}"><span class="sidenav-normal"> Cetak Rapor </span></a></li>
                    </ul>
                </div> 
            </li>
            @endcan
            
            <hr class="horizontal light my-2">
            
            {{-- ========================================================= --}}
            {{-- 6. PENGATURAN (Admin 1 Pintu) --}}
            {{-- ========================================================= --}}
            @can('setting.menu')
            <li class="nav-item mt-3">
                <div class="sidenav-category text-uppercase">Pengaturan</div>
            </li>

            {{-- Setting E-Rapor --}}
            @php 
                $eraporSettingRoutes = ['settings.erapor.*'];
                $isEraporSetActive = request()->routeIs($eraporSettingRoutes);
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#settingEraporMenu" class="nav-link {{ $isEraporSetActive ? 'active' : '' }}" aria-controls="settingEraporMenu" role="button" aria-expanded="{{ $isEraporSetActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-sliders-h text-sm"></i>
                    </div>
                    <span class="nav-link-text">Setting E-Rapor</span>
                </a>
                <div class="collapse {{ $isEraporSetActive ? 'show' : '' }}" id="settingEraporMenu">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.kok.index') ? 'active' : '' }}" href="{{ route('settings.erapor.kok.index') }}"><span class="sidenav-normal"> Set Kokurikuler </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.bobot.index') ? 'active' : '' }}" href="{{ route('settings.erapor.bobot.index') }}"><span class="sidenav-normal"> Bobot Nilai </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.event.index') ? 'active' : '' }}" href="{{ route('settings.erapor.event.index') }}"><span class="sidenav-normal"> Event Dashboard </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.season.index') ? 'active' : '' }}" href="{{ route('settings.erapor.season.index') }}"><span class="sidenav-normal"> Set Season </span></a></li>
                    </ul>
                </div>
            </li>

            {{-- Setting Rapor PKL --}}
            @php 
                $pklSettingRoutes = ['settings.pkl.*'];
                $isPklSetActive = request()->routeIs($pklSettingRoutes);
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#settingPklMenu" class="nav-link {{ $isPklSetActive ? 'active' : '' }}" aria-controls="settingPklMenu" role="button" aria-expanded="{{ $isPklSetActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-sliders-h text-sm"></i>
                    </div>
                    <span class="nav-link-text">Setting Rapor Pkl</span>
                </a>
                <div class="collapse {{ $isPklSetActive ? 'show' : '' }}" id="settingPklMenu">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.pkl.index') ? 'active' : '' }}" href="{{ route('settings.pkl.index') }}"><span class="sidenav-normal"> Set Tujuan pembelajaran </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.pkl.season.*') ? 'active' : '' }}" href="{{ route('settings.pkl.season.index') }}"><span class="sidenav-normal"> Set Season PKL </span></a></li>
                    </ul>
                </div>
            </li>

            {{-- System & User --}}
            @php 
                $systemSettingRoutes = ['settings.system.*'];
                $isSystemSetActive = request()->routeIs($systemSettingRoutes);
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#settingSystemMenu" class="nav-link {{ $isSystemSetActive ? 'active' : '' }}" aria-controls="settingSystemMenu" role="button" aria-expanded="{{ $isSystemSetActive ? 'true' : 'false' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-user-shield text-sm"></i>
                    </div>
                    <span class="nav-link-text">System & User</span>
                </a>
                <div class="collapse {{ $isSystemSetActive ? 'show' : '' }}" id="settingSystemMenu">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.system.users.index') ? 'active' : '' }}" href="{{ route('settings.system.users.index') }}"><span class="sidenav-normal"> Manajemen User </span></a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.system.roles.index') ? 'active' : '' }}" href="{{ route('settings.system.roles.index') }}"><span class="sidenav-normal"> Role & Permission </span></a></li>
                    </ul>
                </div>
            </li>

            {{-- 👇 TOMBOL TOGGLE SIMULASI DI SIDEBAR 👇 --}}
            <li class="nav-item mt-4">
                <div class="sidenav-category text-uppercase">Lingkungan Sistem</div>
            </li>

            <li class="nav-item">
                <a href="{{ route('settings.toggle.simulasi') }}" class="nav-link" style="{{ session('mode_simulasi') === true ? 'background-color: rgba(245, 54, 92, 0.15) !important;' : '' }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas {{ session('mode_simulasi') === true ? 'fa-power-off text-danger' : 'fa-flask text-info' }} text-sm"></i>
                    </div>
                    <span class="nav-link-text fw-bold {{ session('mode_simulasi') === true ? 'text-danger' : '' }}">
                        {{ session('mode_simulasi') === true ? 'Akhiri Simulasi' : 'Mulai Simulasi' }}
                    </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('settings.simulasi.*') ? 'active' : '' }}" href="{{ route('settings.simulasi.index') }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-cogs text-sm"></i>
                    </div>
                    <span class="nav-link-text">Pengaturan Simulasi</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('settings.simulasi.*') ? 'active' : '' }}" href="{{ route('settings.backup.index') }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        {{-- <i class="fas fa-cogs text-sm"></i> --}}
                        <i class="fas fa-floppy-disk"></i>
                    </div>
                    <span class="nav-link-text">Backup & Restore</span>
                </a>
            </li>
            {{-- 👆 END TOMBOL SIDEBAR 👆 --}}

            @endcan 

            <hr class="horizontal light my-2">

            {{-- ========================================================= --}}
            {{-- 7. PERSONAL --}}
            {{-- ========================================================= --}}
            <li class="nav-item mt-3">
                <div class="sidenav-category text-uppercase">Personal</div>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.index') }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <span class="nav-link-text">Profil Saya</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Request::routeIs('changelog.index') ? 'active' : '' }}" href="{{ route('changelog.index') }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-history text-sm"></i>
                    </div>
                    <span class="nav-link-text">ChangeLog</span>
                </a>
            </li>

        </ul>
    </div>
</aside>