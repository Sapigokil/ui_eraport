{{-- File: resources/views/pkl/gurusiswa/index.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Data Kelompok Bimbingan PKL')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        
                        {{-- HEADER BANNER --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-users-cog me-2"></i> Data Kelompok Bimbingan PKL</h6>
                                <div class="pe-3">
                                    <a href="{{ route('pkl.gurusiswa.setup', ['tahun_ajaran' => $tahun_ajaran, 'semester' => $semester]) }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-plus me-1"></i> Atur Kelompok Baru
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4 mt-2">
                            
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- TOGGLE MODE VIEW (Pills) --}}
                            <div class="nav-wrapper position-relative end-0 mb-4">
                                <ul class="nav nav-pills nav-fill p-1 bg-light border" role="tablist" style="border-radius: 0.5rem;">
                                    <li class="nav-item">
                                        <a class="nav-link mb-0 px-0 py-2 {{ $mode == 'guru' ? 'active bg-primary text-white shadow-sm' : 'text-dark' }}" 
                                           href="{{ route('pkl.gurusiswa.index', ['mode' => 'guru', 'tahun_ajaran' => $tahun_ajaran, 'semester' => $semester]) }}">
                                            <i class="fas fa-user-tie me-2"></i> Mode Guru Pembimbing
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link mb-0 px-0 py-2 {{ $mode == 'kelas' ? 'active bg-primary text-white shadow-sm' : 'text-dark' }}" 
                                           href="{{ route('pkl.gurusiswa.index', ['mode' => 'kelas', 'tahun_ajaran' => $tahun_ajaran, 'semester' => $semester]) }}">
                                            <i class="fas fa-chalkboard-teacher me-2"></i> Mode Kelas Siswa
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            {{-- Form Filter Dinamis --}}
                            <div class="p-2 border rounded mb-4 bg-light">
                                <form action="{{ route('pkl.gurusiswa.index') }}" method="GET" class="mb-0">
                                    <input type="hidden" name="mode" value="{{ $mode }}">
                                    <div class="row g-2 align-items-center">
                                        
                                        <div class="col-md-{{ $mode == 'kelas' ? '3' : '5' }}">
                                            <select name="tahun_ajaran" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">-- Pilih Tahun Ajaran --</option>
                                                @foreach($tahunAjaranList as $ta)
                                                    <option value="{{ $ta }}" {{ request('tahun_ajaran', $tahun_ajaran) == $ta ? 'selected' : '' }}>
                                                        {{ $ta }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-{{ $mode == 'kelas' ? '3' : '5' }}">
                                            <select name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="1" {{ request('semester', $semester) == 1 ? 'selected' : '' }}>Ganjil (1)</option>
                                                <option value="2" {{ request('semester', $semester) == 2 ? 'selected' : '' }}>Genap (2)</option>
                                            </select>
                                        </div>

                                        {{-- Dropdown Kelas khusus untuk Mode Kelas --}}
                                        @if($mode == 'kelas')
                                            <div class="col-md-4">
                                                <select name="id_kelas" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="">-- Pilih Kelas untuk dilacak --</option>
                                                    @foreach($kelas_list as $k)
                                                        <option value="{{ $k->id_kelas }}" {{ $id_kelas == $k->id_kelas ? 'selected' : '' }}>
                                                            {{ $k->nama_kelas }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <div class="col-md-2">
                                            @if(request()->hasAny(['tahun_ajaran', 'semester', 'id_kelas']))
                                                <a href="{{ route('pkl.gurusiswa.index', ['mode' => $mode]) }}" class="btn btn-icon btn-sm btn-outline-secondary w-100 mb-0" title="Reset Filter">
                                                    <i class="fas fa-undo me-1"></i> Reset
                                                </a>
                                            @endif
                                        </div>

                                    </div>
                                </form>
                            </div>
                            
                            {{-- ========================================== --}}
                            {{-- TAMPILAN MODE 1: GURU PEMBIMBING (Merge Cell) --}}
                            {{-- ========================================== --}}
                            @if($mode == 'guru')
                                <div class="table-responsive p-0 border rounded mb-4">
                                    <table class="table table-bordered align-items-center mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="5%">No</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Guru Pembimbing</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="10%">Tingkat</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="15%">Jurusan</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Siswa</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="20%">Penempatan</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="10%">Aksi</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @php $globalNo = 1; @endphp
                                            @forelse ($dataKelompok as $guruIndex => $guruData)
                                                
                                                @php
                                                    $siswaByTingkat = $guruData->daftar_siswa->groupBy('tingkat');
                                                    $totalSiswaGuru = $guruData->jumlah_siswa;
                                                    $isFirstRowGuru = true;
                                                @endphp

                                                @foreach ($siswaByTingkat as $tingkat => $siswaTingkat)
                                                    @php
                                                        $siswaByJurusan = $siswaTingkat->groupBy('jurusan');
                                                        $totalSiswaTingkat = $siswaTingkat->count();
                                                        $isFirstRowTingkat = true;
                                                    @endphp

                                                    @foreach ($siswaByJurusan as $jurusan => $siswaJurusan)
                                                        @php
                                                            $totalSiswaJurusan = $siswaJurusan->count();
                                                            $isFirstRowJurusan = true;
                                                        @endphp

                                                        @foreach ($siswaJurusan as $siswa)
                                                            <tr>
                                                                @if ($isFirstRowGuru)
                                                                    <td class="text-center align-middle border-end" rowspan="{{ $totalSiswaGuru }}">
                                                                        <p class="text-sm font-weight-bold mb-0">{{ $globalNo++ }}</p>
                                                                    </td>
                                                                    <td class="align-middle border-end bg-light" rowspan="{{ $totalSiswaGuru }}">
                                                                        <div class="d-flex flex-column justify-content-center px-2">
                                                                            <h6 class="mb-0 text-sm text-dark"><i class="fas fa-user-tie me-2 text-primary"></i>{{ $guruData->nama_guru }}</h6>
                                                                            <p class="text-xs text-secondary mb-0 ms-4">{{ $totalSiswaGuru }} Siswa Dibimbing</p>
                                                                        </div>
                                                                    </td>
                                                                @endif

                                                                @if ($isFirstRowTingkat)
                                                                    <td class="align-middle text-center border-end" rowspan="{{ $totalSiswaTingkat }}">
                                                                        <span class="text-sm font-weight-bold text-dark">{{ $tingkat }}</span>
                                                                    </td>
                                                                @endif

                                                                @if ($isFirstRowJurusan)
                                                                    <td class="align-middle text-center border-end" rowspan="{{ $totalSiswaJurusan }}">
                                                                        <span class="text-sm text-dark">{{ $jurusan }}</span>
                                                                    </td>
                                                                @endif

                                                                <td class="align-middle border-end px-3">
                                                                    <span class="text-sm text-dark">{{ $siswa->nama_siswa }}</span>
                                                                </td>
                                                                
                                                                <td class="align-middle px-3 border-end">
                                                                    @if($siswa->tempat_pkl)
                                                                        <span class="text-xs text-dark">{{ $siswa->tempat_pkl }}</span>
                                                                    @else
                                                                        <span class="text-xs text-warning fst-italic">Belum Diset</span>
                                                                    @endif
                                                                </td>

                                                                @if ($isFirstRowGuru)
                                                                    <td class="align-middle text-center" rowspan="{{ $totalSiswaGuru }}">
                                                                        <a href="{{ route('pkl.gurusiswa.setup', ['id_guru' => $guruData->id_guru, 'tahun_ajaran' => $tahun_ajaran, 'semester' => $semester]) }}" class="btn btn-sm btn-outline-info mb-0">
                                                                            <i class="fas fa-edit"></i> Edit
                                                                        </a>
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                            
                                                            @php
                                                                $isFirstRowGuru = false;
                                                                $isFirstRowTingkat = false;
                                                                $isFirstRowJurusan = false;
                                                            @endphp
                                                        @endforeach
                                                    @endforeach
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">
                                                        <i class="fas fa-users fa-3x mb-3 text-light"></i><br>
                                                        <p class="text-sm text-secondary mb-0">Belum ada data kelompok bimbingan pada periode ini.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            {{-- ========================================== --}}
                            {{-- TAMPILAN MODE 2: TRACKING KELAS DENGAN EDIT MASSAL --}}
                            {{-- ========================================== --}}
                            @if($mode == 'kelas')
                                @if(!$id_kelas)
                                    <div class="alert bg-light text-secondary text-center border mt-4">
                                        <i class="fas fa-search fa-2x mb-2 text-secondary opacity-5"></i><br>
                                        Silakan pilih <strong>Kelas</strong> pada menu filter di atas untuk melihat dan mengedit data.
                                    </div>
                                @else
                                    {{-- Wrapper Form Edit Massal --}}
                                    <form action="{{ route('pkl.gurusiswa.store_massal') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="tahun_ajaran" value="{{ $tahun_ajaran }}">
                                        <input type="hidden" name="semester" value="{{ $semester }}">
                                        <input type="hidden" name="id_kelas" value="{{ $id_kelas }}">

                                        {{-- Kontrol Aksi Edit --}}
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 text-dark">Data Siswa Kelas {{ $kelas_list->where('id_kelas', $id_kelas)->first()->nama_kelas ?? '' }}</h6>
                                            
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-info mb-0 me-2" id="btnToggleEdit" onclick="toggleEditMode()">
                                                    <i class="fas fa-edit me-1"></i> Aktifkan Mode Edit
                                                </button>
                                                <button type="submit" class="btn btn-sm bg-gradient-success mb-0 d-none" id="btnSimpanMassal">
                                                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                                                </button>
                                            </div>
                                        </div>

                                        <div class="table-responsive p-0 border rounded mb-4">
                                            <table class="table table-bordered align-items-center mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="5%">No</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Siswa</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Status Tracking</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="35%">Guru Pembimbing</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Penempatan (Industri)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($dataSiswa as $index => $siswa)
                                                        <tr>
                                                            <td class="text-center align-middle text-sm">{{ $index + 1 }}</td>
                                                            <td class="align-middle px-3 border-end">
                                                                <span class="text-sm font-weight-bold text-dark">{{ $siswa->nama_siswa }}</span>
                                                            </td>
                                                            
                                                            <td class="align-middle text-center border-end">
                                                                @if($siswa->nama_guru)
                                                                    <span class="badge bg-gradient-success">Sudah Dibimbing</span>
                                                                @else
                                                                    <span class="badge bg-gradient-danger">Belum Plotting</span>
                                                                @endif
                                                            </td>
                                                            
                                                            {{-- Kolom Guru Pembimbing (Bisa Switch ke Dropdown) --}}
                                                            <td class="align-middle px-3 border-end">
                                                                {{-- View Mode (Text) --}}
                                                                <div class="guru-text-view">
                                                                    @if($siswa->nama_guru)
                                                                        <span class="text-sm text-dark"><i class="fas fa-check-circle text-success me-1"></i> {{ $siswa->nama_guru }}</span>
                                                                    @else
                                                                        <span class="text-xs text-danger fst-italic">Belum ada pembimbing</span>
                                                                    @endif
                                                                </div>
                                                                
                                                                {{-- Edit Mode (Dropdown) --}}
                                                                <div class="guru-edit-view d-none">
                                                                    <select name="id_guru_pilihan[{{ $siswa->id_siswa }}]" class="form-select form-select-sm border px-2">
                                                                        <option value="">-- Hapus/Kosongkan --</option>
                                                                        @foreach($guru_list as $g)
                                                                            <option value="{{ $g->id_guru }}" {{ $siswa->id_guru == $g->id_guru ? 'selected' : '' }}>
                                                                                {{ $g->nama_guru }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </td>
                                                            
                                                            <td class="align-middle px-3">
                                                                @if($siswa->tempat_pkl)
                                                                    <span class="text-sm text-dark">{{ $siswa->tempat_pkl }}</span>
                                                                @elseif($siswa->nama_guru && !$siswa->tempat_pkl)
                                                                    <span class="text-xs text-warning fst-italic">Guru Belum Set Lokasi</span>
                                                                @else
                                                                    <span class="text-xs text-secondary">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center py-4 text-secondary">
                                                                Tidak ada data siswa ditemukan untuk kelas ini.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                @endif
                            @endif

                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
        
    </main>

    <script>
        // Fungsi untuk mengubah tampilan kolom Guru Pembimbing (Teks vs Dropdown)
        function toggleEditMode() {
            let textViews = document.querySelectorAll('.guru-text-view');
            let editViews = document.querySelectorAll('.guru-edit-view');
            let btnToggle = document.getElementById('btnToggleEdit');
            let btnSimpan = document.getElementById('btnSimpanMassal');

            // Cek apakah saat ini sedang dalam mode edit (dengan melihat apakah dropdown sedang tampil)
            let isEditing = !editViews[0].classList.contains('d-none');

            if (isEditing) {
                // Matikan Mode Edit
                textViews.forEach(el => el.classList.remove('d-none'));
                editViews.forEach(el => el.classList.add('d-none'));
                
                btnToggle.innerHTML = '<i class="fas fa-edit me-1"></i> Aktifkan Mode Edit';
                btnToggle.classList.replace('btn-secondary', 'btn-outline-info');
                btnSimpan.classList.add('d-none');
            } else {
                // Aktifkan Mode Edit
                textViews.forEach(el => el.classList.add('d-none'));
                editViews.forEach(el => el.classList.remove('d-none'));
                
                btnToggle.innerHTML = '<i class="fas fa-times me-1"></i> Batal Edit';
                btnToggle.classList.replace('btn-outline-info', 'btn-secondary');
                btnSimpan.classList.remove('d-none');
            }
        }
    </script>
@endsection