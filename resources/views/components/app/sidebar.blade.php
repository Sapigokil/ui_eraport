<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 fixed-start shadow-sm" id="sidenav-main" style="background-color: #25183b; border-right: 1px solid rgba(255,255,255,0.05) !important; overflow: hidden !important;">

    {{-- HEADER --}}
    <div class="sidenav-header mb-2">
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
        /* === NESTED ACCORDION SIDEBAR CSS === */
        #sidenav-collapse-main {
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }
        #sidenav-collapse-main::-webkit-scrollbar { width: 4px; }
        #sidenav-collapse-main::-webkit-scrollbar-track { background: transparent; }
        #sidenav-collapse-main::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); border-radius: 4px; }
        #sidenav-collapse-main::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.3); }

        .sidenav .navbar-nav { padding-left: 0 !important; }

        .sidenav .nav-link {
            color: rgba(255, 255, 255, 0.7) !important;
            font-weight: 500 !important;
            border-radius: 6px !important;
            margin: 0.15rem 0.25rem !important;
            padding: 0.65rem 0.5rem !important;
            transition: all 0.2s ease;
            position: relative;
        }

        .sidenav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #ffffff !important;
        }

        .navbar-nav .nav-link.active {
            background-color: transparent !important; 
            color: #ffffff !important;
            font-weight: 700 !important;
        }

        .navbar-nav > .nav-item > .nav-link.active::before {
            content: '';
            position: absolute;
            left: -0.25rem;
            top: 15%;
            height: 70%;
            width: 4px;
            background-color: #b088ff;
            border-radius: 0 4px 4px 0;
            display: block;
        }

        .navbar-nav .collapse .nav-link.active { color: #b088ff !important; }
        .navbar-nav .collapse .nav-link.active::before { display: none; }

        /* Level 2 Indentation */
        #sidenav-main .collapse .nav-link {
            margin: 0.15rem 0.25rem 0.15rem 0.5rem !important;
            padding: 0.5rem 0.5rem !important;
            font-size: 0.85rem !important;
        }

        /* Level 3 Indentation (Sub-Menu) */
        #sidenav-main .collapse .collapse .nav-link {
            margin-left: 2rem !important;
            font-size: 0.8rem !important;
        }

        #sidenav-main .nav-link[data-bs-toggle="collapse"]::after {
            color: #ffffff !important;
            opacity: 0.5;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }
        #sidenav-main .nav-link[data-bs-toggle="collapse"][aria-expanded="true"]::after { opacity: 1; }

        /* 👇 CSS KHUSUS UNTUK KATEGORI LACI (ACCORDION LEVEL 1) 👇 */
        .sidenav-category-toggle {
            padding: 0.75rem 1rem;
            margin: 0.5rem 0.5rem 0.25rem 0.5rem;
            background-color: rgba(0, 0, 0, 0.25);
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.05);
            text-decoration: none;
        }
        .sidenav-category-toggle:hover {
            background-color: rgba(0, 0, 0, 0.4);
            text-decoration: none;
        }
        .sidenav-category-toggle .cat-title {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 1px;
            color: #d9c8ff; 
        }
        .sidenav-category-toggle .cat-icon {
            font-size: 0.75rem;
            transition: transform 0.3s ease;
            color: rgba(255,255,255,0.5);
        }
        .sidenav-category-toggle[aria-expanded="true"] {
            border-left: 3px solid #b088ff; 
            background-color: rgba(0, 0, 0, 0.15);
        }
        .sidenav-category-toggle[aria-expanded="true"] .cat-icon {
            transform: rotate(180deg);
            color: #b088ff;
        }
        
        .category-wrapper {
            padding-left: 0.25rem;
            border-left: 1px dashed rgba(255,255,255,0.1);
            margin-left: 1rem;
            margin-bottom: 0.5rem;
        }

        /* Label Kategori Flat */
        .sidenav-category {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 0.25rem; 
            margin-bottom: 0.25rem; 
            padding-left: 0.75rem;
            color: #d9c8ff; 
        }

        #sidenav-main hr.horizontal.light {
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
            background: none !important;
            margin: 0.5rem 0; 
        }
    </style>

    <div class="collapse navbar-collapse px-0 w-auto" id="sidenav-collapse-main" style="height: calc(100vh - 95px); padding-bottom: 40px;">
        <ul class="navbar-nav mt-1">

            {{-- 1. DASHBOARD --}}
            <li class="nav-item">
                @php $isDashboardActive = request()->routeIs('dashboard') || request()->routeIs('siswa.dashboard'); @endphp 
                <a class="nav-link {{ $isDashboardActive ? 'active' : '' }}" href="{{ auth()->user()->hasRole('siswa') || auth()->user()->level == 'siswa' ? route('siswa.dashboard') : route('dashboard') }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-chart-line text-sm"></i>
                    </div>
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>

            {{-- ========================================================= --}}
            {{-- 2. DATA POKOK (KATEGORI UTAMA) --}}
            {{-- ========================================================= --}}
            @canany(['master.menu', 'pkl.data.menu', 'mutasi.menu'])
            @php
                $isMasterActive = request()->routeIs(['master.sekolah.*', 'master.guru.*', 'master.siswa.*', 'master.validasi_bio.*', 'master.kelas.*', 'master.mapel.*', 'master.pembelajaran.*']);
                $isDataEkskulActive = request()->routeIs(['master.ekskul.*']);
                $isPklActive = request()->routeIs(['pkl.tempat.*', 'pkl.gurusiswa.*', 'pkl.penempatan.*']);
                $isMutasiActive = request()->routeIs(['mutasi.keluar.*', 'mutasi.pindah.*']);
                $pendingBioCount = \App\Models\PengajuanBiodata::where('status', 'pending')->count();
                $subMenuHadAlert = $pendingBioCount > 0;
                
                $isCatDataPokokActive = $isMasterActive || $isDataEkskulActive || $isPklActive || $isMutasiActive;
            @endphp
            
            <li class="nav-item mt-2">
                <a class="sidenav-category-toggle" data-bs-toggle="collapse" href="#catDataPokok" role="button" aria-expanded="{{ $isCatDataPokokActive ? 'true' : 'false' }}">
                    <span class="cat-title text-uppercase">
                        Data Pokok
                        @if($subMenuHadAlert)
                            <span class="badge bg-danger ms-1 px-1 py-0" style="font-size:0.5rem;">{{ $pendingBioCount }}</span>
                        @endif
                    </span>
                    <i class="fas fa-chevron-down cat-icon"></i>
                </a>
                
                <div class="collapse {{ $isCatDataPokokActive ? 'show' : '' }} category-wrapper" id="catDataPokok" data-bs-parent="#sidenav-collapse-main">
                    <ul class="nav flex-column">
                        @can('master.menu') 
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#masterDataMenu" class="nav-link {{ $isMasterActive ? 'active' : '' }}" aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-database text-sm"></i></div>
                                <span class="nav-link-text w-100 d-flex justify-content-between align-items-center pe-4">
                                    <span>Master Data</span>
                                    @if($subMenuHadAlert) <span class="badge bg-danger py-1 px-2 shadow-sm" style="font-size: 0.55rem;">{{ $pendingBioCount }}</span> @endif
                                </span>
                            </a>
                            <div class="collapse {{ $isMasterActive ? 'show' : '' }}" id="masterDataMenu" data-bs-parent="#catDataPokok">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.sekolah.*') ? 'active' : '' }}" href="{{ route('master.sekolah.index') }}">Data Sekolah</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.guru.*') ? 'active' : '' }}" href="{{ route('master.guru.index') }}">Data Guru</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.siswa.*') ? 'active' : '' }}" href="{{ route('master.siswa.index') }}">Data Siswa</a></li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('master.validasi_bio.*') ? 'active' : '' }} d-flex justify-content-between" href="{{ route('master.validasi_bio.index') }}">
                                            Validasi Biodata @if($pendingBioCount > 0)<span class="badge bg-danger py-1 px-2" style="font-size: 0.55rem;">{{ $pendingBioCount }}</span>@endif
                                        </a>
                                    </li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.kelas.*') ? 'active' : '' }}" href="{{ route('master.kelas.index') }}">Data Kelas</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.mapel.*') ? 'active' : '' }}" href="{{ route('master.mapel.index') }}">Mata Pelajaran</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.pembelajaran.*') ? 'active' : '' }}" href="{{ route('master.pembelajaran.index') }}">Pembelajaran</a></li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('master.menu')
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#dataEkskulMenu" class="nav-link {{ $isDataEkskulActive ? 'active' : '' }}" aria-expanded="{{ $isDataEkskulActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-futbol text-sm"></i></div>
                                <span class="nav-link-text">Data Ekskul</span>
                            </a>
                            <div class="collapse {{ $isDataEkskulActive ? 'show' : '' }}" id="dataEkskulMenu" data-bs-parent="#catDataPokok">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.ekskul.list.*') ? 'active' : '' }}" href="{{ route('master.ekskul.list.index') }}">List Ekskul</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('master.ekskul.siswa.*') ? 'active' : '' }}" href="{{ route('master.ekskul.siswa.index') }}">Peserta Ekskul</a></li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('pkl.data.menu')
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#menuPkl" class="nav-link {{ $isPklActive ? 'active' : '' }}" aria-expanded="{{ $isPklActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-briefcase text-sm"></i></div>
                                <span class="nav-link-text">Data PKL</span>
                            </a>
                            <div class="collapse {{ $isPklActive ? 'show' : '' }}" id="menuPkl" data-bs-parent="#catDataPokok">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ Request::routeIs('pkl.tempat.*') ? 'active' : '' }}" href="{{ route('pkl.tempat.index') }}">Tempat PKL</a></li>
                                    <li class="nav-item"><a class="nav-link {{ Request::routeIs('pkl.gurusiswa.*') ? 'active' : '' }}" href="{{ route('pkl.gurusiswa.index') }}">Guru Pembimbing</a></li>
                                    <li class="nav-item"><a class="nav-link {{ Request::routeIs('pkl.penempatan.*') ? 'active' : '' }}" href="{{ route('pkl.penempatan.index') }}">Penempatan PKL</a></li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('mutasi.menu')
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#menuMutasi" class="nav-link {{ $isMutasiActive ? 'active' : '' }}" aria-expanded="{{ $isMutasiActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-exchange-alt text-sm"></i></div>
                                <span class="nav-link-text">Mutasi Siswa</span>
                            </a>
                            <div class="collapse {{ $isMutasiActive ? 'show' : '' }}" id="menuMutasi" data-bs-parent="#catDataPokok">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ Request::routeIs('mutasi.keluar.*') ? 'active' : '' }}" href="{{ route('mutasi.keluar.index') }}">Mutasi Keluar</a></li>
                                    <li class="nav-item"><a class="nav-link {{ Request::routeIs('mutasi.pindah.*') ? 'active' : '' }}" href="{{ route('mutasi.pindah.index') }}">Pindah Kelas</a></li>
                                </ul>
                            </div>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            {{-- ========================================================= --}}
            {{-- 3. AKADEMIK (KATEGORI UTAMA) --}}
            {{-- ========================================================= --}}
            @canany(['nilai.menu', 'ekskul.menu', 'rapor.menu', 'ledger.menu'])
            @php
                $isNilaiActive = request()->routeIs(['nilai.sumatif.*', 'nilai.project.*', 'nilai.catatan.*', 'nilai.rekap.*']); 
                $isEkskulActive = request()->routeIs(['ekskul.peserta.*', 'ekskul.nilai.*']); 
                $isWaliActive = request()->routeIs(['walikelas.*']); 
                $isRaporActive = request()->routeIs(['rapornilai.*', 'ledger.*']);
                
                $isCatAkademikActive = $isNilaiActive || $isEkskulActive || $isWaliActive || $isRaporActive;
            @endphp
            
            <li class="nav-item mt-2">
                <a class="sidenav-category-toggle" data-bs-toggle="collapse" href="#catAkademik" role="button" aria-expanded="{{ $isCatAkademikActive ? 'true' : 'false' }}">
                    <span class="cat-title text-uppercase">Akademik</span>
                    <i class="fas fa-chevron-down cat-icon"></i>
                </a>
                
                <div class="collapse {{ $isCatAkademikActive ? 'show' : '' }} category-wrapper" id="catAkademik" data-bs-parent="#sidenav-collapse-main">
                    <ul class="nav flex-column">
                        @can('nilai.menu')
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#dataNilaiMenu" class="nav-link {{ $isNilaiActive ? 'active' : '' }}" aria-expanded="{{ $isNilaiActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-marker text-sm"></i></div>
                                <span class="nav-link-text">Input Nilai</span>
                            </a>
                            <div class="collapse {{ $isNilaiActive ? 'show' : '' }}" id="dataNilaiMenu" data-bs-parent="#catAkademik">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.sumatif.s1') ? 'active' : '' }}" href="{{ route('nilai.sumatif.s1') }}">Nilai Sumatif 1</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.sumatif.s2') ? 'active' : '' }}" href="{{ route('nilai.sumatif.s2') }}">Nilai Sumatif 2</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.sumatif.s3') ? 'active' : '' }}" href="{{ route('nilai.sumatif.s3') }}">Nilai Sumatif 3</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.sumatif.s4') ? 'active' : '' }}" href="{{ route('nilai.sumatif.s4') }}">Nilai Sumatif 4</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.sumatif.s5') ? 'active' : '' }}" href="{{ route('nilai.sumatif.s5') }}">Nilai Sumatif 5</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.project.index') ? 'active' : '' }}" href="{{ route('nilai.project.index') }}">Nilai Project</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.rekap.*') ? 'active' : '' }}" href="{{ route('nilai.rekap.index') }}">Rekap Nilai</a></li>
                                </ul>
                            </div> 
                        </li>
                        @endcan

                        @can('ekskul.menu')
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#inputEkskulMenu" class="nav-link {{ $isEkskulActive ? 'active' : '' }}" aria-expanded="{{ $isEkskulActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-futbol text-sm"></i></div>
                                <span class="nav-link-text">Ekstrakurikuler</span>
                            </a>
                            <div class="collapse {{ $isEkskulActive ? 'show' : '' }}" id="inputEkskulMenu" data-bs-parent="#catAkademik">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('ekskul.peserta.*') ? 'active' : '' }}" href="{{ route('ekskul.peserta.index') }}">Peserta Ekskul</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('ekskul.nilai.*') ? 'active' : '' }}" href="{{ route('ekskul.nilai.index') }}">Input Nilai</a></li>
                                </ul>
                            </div> 
                        </li>
                        @endcan

                        @can('nilai.menu')
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#waliKelasMenu" class="nav-link {{ $isWaliActive ? 'active' : '' }}" aria-expanded="{{ $isWaliActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-user-graduate text-sm"></i></div>
                                <span class="nav-link-text">Tugas Wali Kelas</span>
                            </a>
                            <div class="collapse {{ $isWaliActive ? 'show' : '' }}" id="waliKelasMenu" data-bs-parent="#catAkademik">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('walikelas.catatan.input') ? 'active' : '' }}" href="{{ route('walikelas.catatan.input') }}">Catatan Walikelas</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('walikelas.monitoring.wali') ? 'active' : '' }}" href="{{ route('walikelas.monitoring.wali') }}">Finalisasi Nilai</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('walikelas.cakok.index') ? 'active' : '' }}" href="{{ route('walikelas.cakok.index') }}">Set Template</a></li>
                                </ul>
                            </div> 
                        </li>
                        @endcan

                        @canany(['rapor.menu', 'ledger.menu'])
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#dataRaporMenu" class="nav-link {{ $isRaporActive ? 'active' : '' }}" aria-expanded="{{ $isRaporActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-file-invoice text-sm"></i></div>
                                <span class="nav-link-text">Data Rapor</span>
                            </a>
                            <div class="collapse {{ $isRaporActive ? 'show' : '' }}" id="dataRaporMenu" data-bs-parent="#catAkademik">
                                <ul class="nav flex-column">
                                    @can('rapor.menu')
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('rapornilai.nilaiakhir.index') ? 'active' : '' }}" href="{{ route('rapornilai.nilaiakhir.index') }}">Nilai Akhir</a></li>
                                    <li class="nav-item"><a class="nav-link {{ Route::is('rapornilai.monitoring.index') ? 'active' : '' }}" href="{{ route('rapornilai.monitoring.index') }}">Monitoring Rapor</a></li>
                                    <li class="nav-item"><a class="nav-link {{ Route::is('rapornilai.cetak') ? 'active' : '' }}" href="{{ route('rapornilai.cetak') }}">Cetak Rapor</a></li>
                                    <li class="nav-item"><a class="nav-link {{ Route::is('rapornilai.cover.index') ? 'active' : '' }}" href="{{ route('rapornilai.cover.index') }}">Cetak Cover</a></li>
                                    @endcan
                                    @can('ledger.menu')
                                    <li class="nav-item"><a class="nav-link {{ Route::is('ledger.ledger_index') ? 'active' : '' }}" href="{{ route('ledger.ledger_index') }}">Ledger Nilai</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcanany
                    </ul>
                </div>
            </li>
            @endcanany

            {{-- ========================================================= --}}
            {{-- 4. PRAKERIN (KATEGORI UTAMA) --}}
            {{-- ========================================================= --}}
            @canany(['pkl.nilai.menu', 'rapor.menu'])
            @php
                $isPklNilaiActive = request()->routeIs(['pkl.nilai.index', 'pkl.nilai.rekap']); 
                $isPklRaporActive = request()->routeIs(['pkl.rapor.monitoring.index', 'pkl.rapor.cetak.index']); 
                $isCatPrakerinActive = $isPklNilaiActive || $isPklRaporActive;
            @endphp
            <li class="nav-item mt-2">
                <a class="sidenav-category-toggle" data-bs-toggle="collapse" href="#catPrakerin" role="button" aria-expanded="{{ $isCatPrakerinActive ? 'true' : 'false' }}">
                    <span class="cat-title text-uppercase">Prakerin</span>
                    <i class="fas fa-chevron-down cat-icon"></i>
                </a>
                
                <div class="collapse {{ $isCatPrakerinActive ? 'show' : '' }} category-wrapper" id="catPrakerin" data-bs-parent="#sidenav-collapse-main">
                    <ul class="nav flex-column">
                        @can('pkl.nilai.menu')
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#penilaianPrakerinMenu" class="nav-link {{ $isPklNilaiActive ? 'active' : '' }}" aria-expanded="{{ $isPklNilaiActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-clipboard-check text-sm"></i></div>
                                <span class="nav-link-text">Penilaian Prakerin</span>
                            </a>
                            <div class="collapse {{ $isPklNilaiActive ? 'show' : '' }}" id="penilaianPrakerinMenu" data-bs-parent="#catPrakerin">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('pkl.nilai.index') ? 'active' : '' }}" href="{{ route('pkl.nilai.index') }}">Input Nilai</a></li>
                                </ul>
                            </div> 
                        </li>
                        @endcan

                        @can('rapor.menu')
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#raporPrakerinMenu" class="nav-link {{ $isPklRaporActive ? 'active' : '' }}" aria-expanded="{{ $isPklRaporActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-print text-sm"></i></div>
                                <span class="nav-link-text">Data Rapor PKL</span>
                            </a>
                            <div class="collapse {{ $isPklRaporActive ? 'show' : '' }}" id="raporPrakerinMenu" data-bs-parent="#catPrakerin">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('pkl.rapor.monitoring.index') ? 'active' : '' }}" href="{{ route('pkl.rapor.monitoring.index') }}">Monitoring Rapor</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('pkl.rapor.cetak.index') ? 'active' : '' }}" href="{{ route('pkl.rapor.cetak.index') }}">Cetak Rapor</a></li>
                                </ul>
                            </div> 
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            {{-- ========================================================= --}}
            {{-- 5. PROSES AKHIR TAHUN (KATEGORI UTAMA) --}}
            {{-- ========================================================= --}}
            @can('mutasi.menu') 
            @php
                $isProsesActive = request()->routeIs(['mutasi.kelulusan_dashboard.*', 'mutasi.kelulusan.*', 'mutasi.kenaikan_dashboard.*', 'mutasi.kenaikan.*', 'mutasi.dashboard.*', 'pengumuman.*', 'mutasi.eksekusi.*', 'mutasi.riwayat.*']);
            @endphp
            <li class="nav-item mt-2">
                <a class="sidenav-category-toggle" data-bs-toggle="collapse" href="#catProsesAkhir" role="button" aria-expanded="{{ $isProsesActive ? 'true' : 'false' }}">
                    <span class="cat-title text-uppercase">Proses Akhir Tahun</span>
                    <i class="fas fa-chevron-down cat-icon"></i>
                </a>
                
                <div class="collapse {{ $isProsesActive ? 'show' : '' }} category-wrapper" id="catProsesAkhir" data-bs-parent="#sidenav-collapse-main">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#prosesAkhirMenu" class="nav-link {{ $isProsesActive ? 'active' : '' }}" aria-expanded="{{ $isProsesActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-rocket text-sm"></i></div>
                                <span class="nav-link-text">Mutasi & Tutup Tahun</span>
                            </a>
                            <div class="collapse {{ $isProsesActive ? 'show' : '' }}" id="prosesAkhirMenu" data-bs-parent="#catProsesAkhir">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('mutasi.kelulusan_dashboard.*', 'mutasi.kelulusan.*') ? 'active' : '' }}" href="{{ route('mutasi.kelulusan_dashboard.index') }}">Proses Kelulusan</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('mutasi.kenaikan_dashboard.*', 'mutasi.kenaikan.*') ? 'active' : '' }}" href="{{ route('mutasi.kenaikan_dashboard.index') }}">Proses Kenaikan</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('mutasi.dashboard.*', 'pengumuman.*') ? 'active' : '' }}" href="{{ route('mutasi.dashboard.index') }}">Pengumuman</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('mutasi.eksekusi.*') ? 'active' : '' }}" href="{{ route('mutasi.eksekusi.index') }}">Tutup Tahun Ajaran</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('mutasi.riwayat.*') ? 'active' : '' }}" href="{{ route('mutasi.riwayat.index') }}">Riwayat Eksekusi</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            
            {{-- ========================================================= --}}
            {{-- 6. PENGATURAN (KATEGORI UTAMA) --}}
            {{-- ========================================================= --}}
            @can('setting.menu')
            @php 
                $isEraporSetActive = request()->routeIs(['settings.erapor.kok.*', 'settings.erapor.bobot.*', 'settings.erapor.event.*']);
                $isPklSetActive = request()->routeIs(['settings.pkl.index', 'settings.pkl.template', 'settings.pkl.import']);
                $isSeasonActive = request()->routeIs(['settings.erapor.season.*', 'settings.pkl.season.*', 'settings.bio_season.*']);
                $isSystemSetActive = request()->routeIs(['settings.system.*']);
                
                $isCatPengaturanActive = $isEraporSetActive || $isPklSetActive || $isSeasonActive || $isSystemSetActive;
            @endphp
            <li class="nav-item mt-2">
                <a class="sidenav-category-toggle" data-bs-toggle="collapse" href="#catPengaturan" role="button" aria-expanded="{{ $isCatPengaturanActive ? 'true' : 'false' }}">
                    <span class="cat-title text-uppercase">Pengaturan</span>
                    <i class="fas fa-chevron-down cat-icon"></i>
                </a>
                
                <div class="collapse {{ $isCatPengaturanActive ? 'show' : '' }} category-wrapper" id="catPengaturan" data-bs-parent="#sidenav-collapse-main">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#settingEraporMenu" class="nav-link {{ $isEraporSetActive ? 'active' : '' }}" aria-expanded="{{ $isEraporSetActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-sliders-h text-sm"></i></div>
                                <span class="nav-link-text">Setting E-Rapor</span>
                            </a>
                            <div class="collapse {{ $isEraporSetActive ? 'show' : '' }}" id="settingEraporMenu" data-bs-parent="#catPengaturan">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.kok.index') ? 'active' : '' }}" href="{{ route('settings.erapor.kok.index') }}">Set Kokurikuler</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.bobot.index') ? 'active' : '' }}" href="{{ route('settings.erapor.bobot.index') }}">Bobot Nilai</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.event.index') ? 'active' : '' }}" href="{{ route('settings.erapor.event.index') }}">Event Dashboard</a></li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#settingPklMenu" class="nav-link {{ $isPklSetActive ? 'active' : '' }}" aria-expanded="{{ $isPklSetActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-briefcase text-sm"></i></div>
                                <span class="nav-link-text">Setting Rapor Pkl</span>
                            </a>
                            <div class="collapse {{ $isPklSetActive ? 'show' : '' }}" id="settingPklMenu" data-bs-parent="#catPengaturan">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.pkl.index') ? 'active' : '' }}" href="{{ route('settings.pkl.index') }}">Set TP PKL</a></li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#settingSeasonMenu" class="nav-link {{ $isSeasonActive ? 'active' : '' }}" aria-expanded="{{ $isSeasonActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-calendar-alt text-sm"></i></div>
                                <span class="nav-link-text">Setting Season</span>
                            </a>
                            <div class="collapse {{ $isSeasonActive ? 'show' : '' }}" id="settingSeasonMenu" data-bs-parent="#catPengaturan">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.erapor.season.*') ? 'active' : '' }}" href="{{ route('settings.erapor.season.index') }}">Season Akademik</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.pkl.season.*') ? 'active' : '' }}" href="{{ route('settings.pkl.season.index') }}">Season PKL</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.bio_season.*') ? 'active' : '' }}" href="{{ route('settings.bio_season.index') }}">Season Biodata</a></li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#settingSystemMenu" class="nav-link {{ $isSystemSetActive ? 'active' : '' }}" aria-expanded="{{ $isSystemSetActive ? 'true' : 'false' }}">
                                <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-users-cog text-sm"></i></div>
                                <span class="nav-link-text">Sistem & Maintenance</span>
                            </a>
                            <div class="collapse {{ $isSystemSetActive ? 'show' : '' }}" id="settingSystemMenu" data-bs-parent="#catPengaturan">
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.system.users.index') ? 'active' : '' }}" href="{{ route('settings.system.users.index') }}">Manajemen User</a></li>
                                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.system.roles.index') ? 'active' : '' }}" href="{{ route('settings.system.roles.index') }}">Role & Permission</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- ========================================================= --}}
            {{-- 7. LINGKUNGAN SISTEM (KATEGORI UTAMA - MENU FLAT/LANGSUNG) --}}
            {{-- ========================================================= --}}
            @can('setting.menu')
            <li class="nav-item mt-3">
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
                <a class="nav-link {{ request()->routeIs('settings.backup.*') ? 'active' : '' }}" href="{{ route('settings.backup.index') }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-floppy-disk text-sm"></i>
                    </div>
                    <span class="nav-link-text">Backup & Restore</span>
                </a>
            </li>

            {{-- 👇 PERBAIKAN: Sub-Menu ChangeLog dipindahkan ke mari 👇 --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('changelog.*') ? 'active' : '' }}" href="{{ route('changelog.index') }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-history text-sm"></i>
                    </div>
                    <span class="nav-link-text">ChangeLog</span>
                </a>
            </li>
            @endcan

            {{-- ========================================================= --}}
            {{-- 8. MENU KHUSUS SISWA (KATEGORI UTAMA) --}}
            {{-- ========================================================= --}}
            @can('siswa.menu')
                @if(auth()->user()->hasRole('siswa_erapor') || auth()->user()->level == 'siswa_erapor')
                    @php
                        $id_siswa = auth()->user()->id_siswa;
                        $notifBalikAdmin = \App\Models\PengajuanBiodata::where('id_siswa', $id_siswa)->whereIn('status', ['disetujui', 'ditolak'])->where('is_read', 0)->count();
                        $notifPengumuman = \App\Models\PengumumanSiswa::where('id_siswa', $id_siswa)->where('has_seen', 0)->where('status', 'published')->count();
                        
                        $isCatSiswaActive = request()->routeIs(['sis.biodata', 'sis.biodata.*', 'sis.psts.*', 'sis.pengumuman']);
                    @endphp

                    <li class="nav-item mt-2">
                        <a class="sidenav-category-toggle" data-bs-toggle="collapse" href="#catSiswa" role="button" aria-expanded="{{ $isCatSiswaActive ? 'true' : 'false' }}">
                            <span class="cat-title text-uppercase">
                                Ruang Siswa
                                @if(($notifBalikAdmin + $notifPengumuman) > 0)
                                    <span class="badge bg-danger ms-1 px-1 py-0" style="font-size:0.5rem;">{{ $notifBalikAdmin + $notifPengumuman }}</span>
                                @endif
                            </span>
                            <i class="fas fa-chevron-down cat-icon"></i>
                        </a>
                        
                        <div class="collapse {{ $isCatSiswaActive ? 'show' : '' }} category-wrapper" id="catSiswa" data-bs-parent="#sidenav-collapse-main">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('sis.biodata') || request()->routeIs('sis.biodata.*') ? 'active' : '' }}" href="{{ route('sis.biodata') }}">
                                        <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-id-card text-sm"></i></div>
                                        <span class="nav-link-text d-flex justify-content-between align-items-center w-100">
                                            Biodata Diri
                                            @if($notifBalikAdmin > 0) <span class="badge bg-gradient-danger py-1 px-2 shadow-sm" style="font-size: 0.55rem;">{{ $notifBalikAdmin }}</span> @endif
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('sis.psts.*') ? 'active' : '' }}" href="{{ route('sis.psts.index') }}">
                                        <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-file-invoice text-sm"></i></div>
                                        <span class="nav-link-text">Laporan PSTS</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('sis.pengumuman') ? 'active' : '' }}" href="{{ route('sis.pengumuman') }}">
                                        <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;"><i class="fas fa-envelope-open-text text-sm"></i></div>
                                        <span class="nav-link-text d-flex justify-content-between align-items-center w-100">
                                            Pengumuman
                                            @if($notifPengumuman > 0) <span class="badge bg-gradient-danger py-1 px-2 shadow-sm" style="font-size: 0.55rem;">Baru</span> @endif
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
            @endcan

            {{-- 9. PERSONAL --}}
            @if(!auth()->user()->hasRole('siswa_erapor') && auth()->user()->level != 'siswa_erapor')
            <li class="nav-item mt-2 border-top border-secondary pt-2 mx-3">
                <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.index') }}">
                    <div class="me-3 d-flex align-items-center justify-content-center" style="width: 25px;">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <span class="nav-link-text">Ubah Password</span>
                </a>
            </li>
            @endif

        </ul>
    </div>
</aside>