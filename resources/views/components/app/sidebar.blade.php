<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 bg-slate-900 fixed-start" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            id="iconSidenav"></i>

        {{-- LINK KE DASHBOARD --}}
        <a class="navbar-brand d-flex align-items-center m-0" href="{{ route('dashboard') }}" target="_self">
            <span class="font-weight-bold text-lg text-white">E-Rapor Corporate</span>
        </a>
    </div>

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
            @can('manage-master') 
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#masterDataMenu" class="nav-link {{ $isMasterActive ? 'active' : 'text-white' }}" aria-controls="masterDataMenu" role="button" aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-database text-sm {{ $isMasterActive ? 'text-white' : 'text-dark opacity-10' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Master Data</span>
                </a>

                <div class="collapse {{ $isMasterActive ? 'show' : '' }}" id="masterDataMenu">
                    <ul class="nav ms-4 ps-3">
                        
                        {{-- SEMUA LINK MASTER DATA --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.sekolah.index') ? 'active' : 'text-white' }}" href="{{ route('master.sekolah.index') }}">
                                <span class="sidenav-mini-icon"> S </span>
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
                    'master.sumatif.*', 
                ];
                $isNilaiActive = request()->routeIs($nilaiRoutes); 
            @endphp

            @can('manage-master') 
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#dataNilaiMenu" class="nav-link {{ $isNilaiActive ? 'active' : 'text-white' }}" aria-controls="dataNilaiMenu" role="button" aria-expanded="{{ $isNilaiActive ? 'true' : 'false' }}">
                    {{-- IKON DIKEMBALIKAN --}}
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-marker text-sm {{ $isNilaiActive ? 'text-white' : 'text-dark opacity-10' }}"></i>
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
            {{-- === MENU BARU: DATA RAPOR (Menu 3) === --}}
            {{-- ============================================================= --}}
            @php
                // Semua route yang terkait dengan Rapor (Progres & Catatan Wali Kelas)
                $raporRoutes = [
                    'master.rapornilai.*',
                ];
                $isRaporActive = request()->routeIs($raporRoutes); 
            @endphp

            @can('manage-master') 
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#dataRaporMenu" class="nav-link {{ $isRaporActive ? 'active' : 'text-white' }}" aria-controls="dataRaporMenu" role="button" aria-expanded="{{ $isRaporActive ? 'true' : 'false' }}">
                    {{-- IKON DIKEMBALIKAN --}}
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-file-invoice text-sm {{ $isRaporActive ? 'text-white' : 'text-dark opacity-10' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Data Rapor</span>
                </a>
                
                <div class="collapse {{ $isRaporActive ? 'show' : '' }}" id="dataRaporMenu">
                    <ul class="nav ms-4 ps-3">
                        
                        {{-- Submenu: Dashboard Nilai Rapor (Route: master.rapornilai.index) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.rapornilai.index') ? 'active' : 'text-white' }}" 
                            href="{{ route('master.rapornilai.index') }}">
                                <span class="sidenav-mini-icon"> DR </span>
                                <span class="sidenav-normal"> Dashboard Nilai </span>
                            </a>
                        </li>
                        
                        {{-- Link: Catatan Wali Kelas (Route: master.rapornilai.wali.catatan) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.rapornilai.wali.catatan') ? 'active' : 'text-white' }}" 
                            href="{{ route('master.rapornilai.wali.catatan') }}">
                                <span class="sidenav-mini-icon"> CK </span>
                                <span class="sidenav-normal"> Catatan Wali Kelas </span>
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
                <a class="nav-link {{ $isCetakActive ? 'active' : 'text-white' }}" href="#">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-print text-sm {{ $isCetakActive ? 'text-white' : 'text-dark opacity-10' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Cetak Rapor</span>
                </a>
            </li>
            @endcan
            
            <hr class="horizontal light my-2">
            
            {{-- 6. MANAJEMEN PENGGUNA --}}
            @php 
                $managementRoutes = ['users.index', 'users.create', 'users.edit', 'roles.index', 'roles.create', 'roles.edit'];
                $isManagementActive = request()->routeIs($managementRoutes) || request()->is('pengaturan/*');
            @endphp
            
            @can('pengaturan-manage-users')
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#manajemenUserMenu" class="nav-link {{ $isManagementActive ? 'active' : 'text-white' }}" aria-controls="manajemenUserMenu" role="button" aria-expanded="{{ $isManagementActive ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-users-cog text-sm {{ $isManagementActive ? 'text-white' : 'text-dark opacity-10' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Manajemen Pengguna</span>
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

            {{-- 7. PROFIL SAYA --}}
            @php $isProfileActive = false; @endphp
            <li class="nav-item">
                <a class="nav-link {{ $isProfileActive ? 'active' : 'text-white' }}" href="#">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user text-sm {{ $isProfileActive ? 'text-white' : 'text-dark opacity-10' }}"></i>
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