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
                
                {{-- Link diarahkan ke route('dashboard') --}}
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
                $masterRoutes = ['master.students.index', 'master.teachers.index', 'master.classes.index', 'master.subjects.index'];
                $isMasterActive = request()->routeIs($masterRoutes) || request()->is('master-data/*'); 
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
                        
                        {{-- SEMUA LINK MASTER DATA DIARAHKAN KE # --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.school.index') ? 'active' : 'text-white' }}" href="{{ route('master.sekolah.index') }}">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal"> Data Sekolah </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.teachers.index') ? 'active' : 'text-white' }}" href="{{ route('master.guru.index') }}">
                                <span class="sidenav-mini-icon"> G </span>
                                <span class="sidenav-normal"> Data Guru </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.students.index') ? 'active' : 'text-white' }}" href="#">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal"> Data Siswa </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.classes.index') ? 'active' : 'text-white' }}" href="#">
                                <span class="sidenav-mini-icon"> K </span>
                                <span class="sidenav-normal"> Data Kelas </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.subjects.index') ? 'active' : 'text-white' }}" href="#">
                                <span class="sidenav-mini-icon"> M </span>
                                <span class="sidenav-normal"> Mata Pelajaran </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.learning.index') ? 'active' : 'text-white' }}" href="#">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal"> Data Pembelajaran </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.extracurricular.index') ? 'active' : 'text-white' }}" href="#">
                                <span class="sidenav-mini-icon"> E </span>
                                <span class="sidenav-normal"> Ekstrakulikuler </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- 3. INPUT NILAI --}}
            @php $isInputNilaiActive = false; @endphp
            {{-- @can('input-nilai') --}}
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#inputNilaiMenu" class="nav-link {{ $isInputNilaiActive ? 'active' : 'text-white' }}" aria-controls="inputNilaiMenu" role="button" aria-expanded="{{ $isInputNilaiActive ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-marker text-sm {{ $isInputNilaiActive ? 'text-white' : 'text-dark opacity-10' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Input Nilai</span>
                </a>

                <div class="collapse {{ $isInputNilaiActive ? 'show' : '' }}" id="inputNilaiMenu">
                    <ul class="nav ms-4 ps-3">
                        {{-- SUB MENU INPUT NILAI DIARAHKAN KE # --}}
                        <li class="nav-item"><a class="nav-link {{ false ? 'active' : 'text-white' }}" href="#">Nilai Akademik</a></li>
                        <li class="nav-item"><a class="nav-link {{ false ? 'active' : 'text-white' }}" href="#">Catatan Wali</a></li>
                    </ul>
                </div>
            </li>
            {{-- @endcan --}}

            {{-- 4. LAPORAN NILAI --}}
            @php $isLaporanActive = false; @endphp
            {{-- @can('view-laporan') --}}
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#laporanNilaiMenu" class="nav-link {{ $isLaporanActive ? 'active' : 'text-white' }}" aria-controls="laporanNilaiMenu" role="button" aria-expanded="{{ $isLaporanActive ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-poll text-sm {{ $isLaporanActive ? 'text-white' : 'text-dark opacity-10' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Laporan Nilai</span>
                </a>

                <div class="collapse {{ $isLaporanActive ? 'show' : '' }}" id="laporanNilaiMenu">
                    <ul class="nav ms-4 ps-3">
                        {{-- SUB MENU LAPORAN DIARAHKAN KE # --}}
                        <li class="nav-item"><a class="nav-link {{ false ? 'active' : 'text-white' }}" href="#">Rekap Nilai</a></li>
                        <li class="nav-item"><a class="nav-link {{ false ? 'active' : 'text-white' }}" href="#">Absensi</a></li>
                    </ul>
                </div>
            </li>
            {{-- @endcan --}}

            {{-- 5. CETAK RAPOR --}}
            @php $isCetakActive = false; @endphp
            {{-- @can('cetak-dokumen') --}}
            <li class="nav-item">
                <a class="nav-link {{ $isCetakActive ? 'active' : 'text-white' }}" href="#">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-print text-sm {{ $isCetakActive ? 'text-white' : 'text-dark opacity-10' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Cetak Rapor</span>
                </a>
            </li>
            {{-- @endcan --}}

            <hr class="horizontal light my-2">

            {{-- 6. MANAJEMEN PENGGUNA (LINK AKTIF) --}}
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
                        {{-- SUB MENU USER INDEX: Href AKTIF --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : 'text-white' }}" href="{{ route('users.index') }}">
                                <span class="sidenav-mini-icon"> U </span>
                                <span class="sidenav-normal"> Daftar User </span>
                            </a>
                        </li>
                        {{-- SUB MENU ROLE INDEX: Href AKTIF --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('roles.index') ? 'active' : 'text-white' }}" href="{{ route('roles.index') }}">
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