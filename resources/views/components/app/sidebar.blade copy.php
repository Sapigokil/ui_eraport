<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 bg-slate-900 fixed-start" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand d-flex align-items-center m-0"
            href="{{ route('dashboard') }}" target="_self"> 
            <span class="font-weight-bold text-lg text-white">E-Rapor Corporate</span>
        </a>
    </div>
    
    <hr class="horizontal light my-2">

    <div class="collapse navbar-collapse px-4 w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            
            {{-- DASHBOARD (Menu Tunggal) --}}
            <li class="nav-item">
                <a class="nav-link {{ is_current_route('dashboard') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}">
                    <div class="icon icon-shape icon-sm px-0 text-center d-flex align-items-center justify-content-center">
                        <i class="fas fa-chart-line text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard Utama</span>
                </a>
            </li>
            
            <hr class="horizontal light my-2">

            {{-- 1. MASTER DATA (Collapsible) --}}
            @php
                $isMasterActive = is_current_route(['master.siswa', 'master.guru', 'master.mapel']);
            @endphp
            @can('master.view') 
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#masterDataMenu" class="nav-link {{ $isMasterActive ? 'active' : '' }}" aria-controls="masterDataMenu" role="button" aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-database {{ $isMasterActive ? 'text-white' : 'text-dark' }} text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Master Data</span>
                </a>
                <div class="collapse {{ $isMasterActive ? 'show' : '' }}" id="masterDataMenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('master.siswa') ? 'active' : '' }}" href="{{ route('master.siswa') }}">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal"> Data Siswa </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('master.guru') ? 'active' : '' }}" href="{{ route('master.guru') }}">
                                <span class="sidenav-mini-icon"> G </span>
                                <span class="sidenav-normal"> Data Guru & Pegawai </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('master.mapel') ? 'active' : '' }}" href="{{ route('master.mapel') }}">
                                <span class="sidenav-mini-icon"> M </span>
                                <span class="sidenav-normal"> Mata Pelajaran & Kelas </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- 2. INPUT NILAI (Collapsible) --}}
            @php
                $isInputNilaiActive = is_current_route(['nilai.input_akademik', 'nilai.catatan_wali']);
            @endphp
            @can('input-nilai')
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#inputNilaiMenu" class="nav-link {{ $isInputNilaiActive ? 'active' : '' }}" aria-controls="inputNilaiMenu" role="button" aria-expanded="{{ $isInputNilaiActive ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-marker {{ $isInputNilaiActive ? 'text-white' : 'text-dark' }} text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Input Nilai</span>
                </a>
                <div class="collapse {{ $isInputNilaiActive ? 'show' : '' }}" id="inputNilaiMenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('nilai.input_akademik') ? 'active' : '' }}" href="{{ route('nilai.input_akademik') }}">
                                <span class="sidenav-mini-icon"> A </span>
                                <span class="sidenav-normal"> Akademik & Sikap </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('nilai.catatan_wali') ? 'active' : '' }}" href="{{ route('nilai.catatan_wali') }}">
                                <span class="sidenav-mini-icon"> C </span>
                                <span class="sidenav-normal"> Catatan Wali Kelas </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            
            {{-- 3. LAPORAN NILAI (Collapsible) --}}
            @php
                $isLaporanNilaiActive = is_current_route(['laporan.rekap_nilai', 'laporan.absensi']);
            @endphp
            @can('view-laporan')
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#laporanNilaiMenu" class="nav-link {{ $isLaporanNilaiActive ? 'active' : '' }}" aria-controls="laporanNilaiMenu" role="button" aria-expanded="{{ $isLaporanNilaiActive ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-poll {{ $isLaporanNilaiActive ? 'text-white' : 'text-dark' }} text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Laporan Nilai</span>
                </a>
                <div class="collapse {{ $isLaporanNilaiActive ? 'show' : '' }}" id="laporanNilaiMenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('laporan.rekap_nilai') ? 'active' : '' }}" href="{{ route('laporan.rekap_nilai') }}">
                                <span class="sidenav-mini-icon"> R </span>
                                <span class="sidenav-normal"> Rekap Nilai Akhir </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('laporan.absensi') ? 'active' : '' }}" href="{{ route('laporan.absensi') }}">
                                <span class="sidenav-mini-icon"> A </span>
                                <span class="sidenav-normal"> Laporan Absensi </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- 4. CETAK (Collapsible) --}}
            @php
                $isCetakActive = is_current_route(['cetak.rapor', 'cetak.legger']);
            @endphp
            @can('cetak-dokumen')
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#cetakMenu" class="nav-link {{ $isCetakActive ? 'active' : '' }}" aria-controls="cetakMenu" role="button" aria-expanded="{{ $isCetakActive ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-print {{ $isCetakActive ? 'text-white' : 'text-dark' }} text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Cetak Dokumen</span>
                </a>
                <div class="collapse {{ $isCetakActive ? 'show' : '' }}" id="cetakMenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('cetak.rapor') ? 'active' : '' }}" href="{{ route('cetak.rapor') }}">
                                <span class="sidenav-mini-icon"> R </span>
                                <span class="sidenav-normal"> Cetak Rapor Final </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('cetak.legger') ? 'active' : '' }}" href="{{ route('cetak.legger') }}">
                                <span class="sidenav-mini-icon"> L </span>
                                <span class="sidenav-normal"> Cetak Legger Nilai </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            
            <hr class="horizontal light my-2">

            {{-- PENGATURAN SISTEM (MANAJEMEN PENGGUNA) --}}
            @php
                $isUserManagementActive = is_current_route(['users.index', 'roles.index', 'profile']);
            @endphp
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#settingsMenu" class="nav-link {{ $isUserManagementActive ? 'active' : '' }}" aria-controls="settingsMenu" role="button" aria-expanded="{{ $isUserManagementActive ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-cogs {{ $isUserManagementActive ? 'text-white' : 'text-dark' }} text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Pengaturan & Akun</span>
                </a>
                
                <div class="collapse {{ $isUserManagementActive ? 'show' : '' }}" id="settingsMenu">
                    <ul class="nav ms-4 ps-3">
                        {{-- @can('manage-users') --}}
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal"> Manajemen Pengguna </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('roles.index') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                                <span class="sidenav-mini-icon"> R </span>
                                <span class="sidenav-normal"> Role & Izin </span>
                            </a>
                        </li>
                        {{-- @endcan --}}
                        <li class="nav-item">
                            <a class="nav-link {{ is_current_route('profile') ? 'active' : '' }}" href="{{ route('profile') }}">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal"> Profil Saya </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
        </ul>
    </div>
    <div class="sidenav-footer mx-4 ">
        {{-- Footer default dari template --}}
    </div>
</aside>