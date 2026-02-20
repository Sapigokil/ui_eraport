@extends('layouts.app') 

@section('page-title', 'Proses Kenaikan Kelas')

@section('content')
<style>
    /* Custom Colors untuk Dropdown Kenaikan Kelas */
    .select-tinggal { color: #dc3545 !important; font-weight: bold !important; } /* Merah */
    .select-naik-sama { color: #6f42c1 !important; font-weight: bold !important; } /* Ungu */
    .select-naik-beda { color: #fd7e14 !important; font-weight: bold !important; } /* Orange */
</style>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        {{-- TOMBOL KEMBALI --}}
        <div class="mb-3">
            <a href="{{ route('mutasi.dashboard_akhir.index') }}" class="btn btn-sm btn-white border-secondary shadow-sm mb-0 text-dark font-weight-bold">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
            </a>
        </div>
        
        {{-- HEADER BANNER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-primary overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                        <i class="fas fa-layer-group text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="row align-items-center text-white">
                            <div class="col-md-8">
                                <h3 class="text-white font-weight-bold mb-1">Proses Kenaikan Kelas</h3>
                                <p class="text-white opacity-8 mb-0">
                                    <i class="fas fa-info-circle me-1"></i> Proses massal pemindahan siswa ke tingkat selanjutnya pada akhir tahun ajaran.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 1. FILTER KELAS ASAL --}}
        {{-- <div class="card shadow-sm border mb-4">
            <div class="card-body p-4">
                <form action="{{ route('mutasi.kenaikan.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Pilih Kelas Asal (Tingkat Saat Ini)</label>
                        <select name="id_kelas_asal" class="form-select border-secondary ps-2" onchange="this.form.submit()">
                            <option value="">- Pilih Kelas -</option>
                            @foreach($kelasAsalList as $k)
                                <option value="{{ $k->id_kelas }}" {{ $id_kelas_asal == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <p class="text-sm text-muted mb-0 mt-2">
                            *Hanya menampilkan kelas aktif di bawah tingkat kelulusan. Siswa kelas akhir diproses pada menu <b>Kelulusan</b>.
                        </p>
                    </div>
                </form>
            </div>
        </div> --}}

        {{-- 2. AREA PROSES KENAIKAN --}}
        @if($id_kelas_asal && $dataSiswa->isNotEmpty())
        
        <form id="formKenaikanKelas" action="{{ route('mutasi.kenaikan.store') }}" method="POST">
            @csrf
            {{-- DATA HIDDEN UNTUK RIWAYAT --}}
            <input type="hidden" name="id_kelas_lama" value="{{ $id_kelas_asal }}">
            <input type="hidden" name="tahun_ajaran_lama" value="{{ $taLama }}">
            <input type="hidden" name="tahun_ajaran_baru" value="{{ $taBaru }}">

            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-light pb-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-dark font-weight-bold">Daftar Siswa Aktif: {{ $kelasAsalTerpilih->nama_kelas }}</h6>
                        <span class="text-xs text-secondary">Total: <b>{{ $dataSiswa->count() }}</b> Siswa akan diproses.</span>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-secondary mb-1">TA Lama: {{ $taLama }}</span>
                        <i class="fas fa-arrow-right text-secondary mx-2"></i>
                        <span class="badge bg-primary mb-1">TA Baru: {{ $taBaru }}</span>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%;">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 35%;">Nama Siswa / NISN</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 15%;">Status Saat Ini</th>
                                    <th class="text-uppercase text-primary text-xxs font-weight-bolder opacity-9 bg-gray-100" style="width: 45%;">Tetapkan Kelas Tujuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataSiswa as $i => $siswa)
                                <tr class="border-bottom">
                                    <td class="align-middle text-center text-sm font-weight-bold">{{ $i + 1 }}</td>
                                    <td class="align-middle px-3">
                                        <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $siswa->nama_siswa }}</h6>
                                        <p class="text-xs text-secondary mb-0">{{ $siswa->nisn }}</p>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                    </td>
                                    <td class="align-middle px-3 bg-gray-50">
                                        @php
                                            // Menentukan warna awal saat halaman dimuat
                                            $initialColor = 'select-naik-sama'; // Default ungu (Naik Kelas Sesuai Jurusan)
                                            if (!$idKelasDefaultTujuan) {
                                                $initialColor = 'select-tinggal'; // Jika auto-mapping gagal, merah
                                            }
                                        @endphp
                                        
                                        {{-- Tambahkan onchange="updateColor(this)" pada tag select --}}
                                        <select name="tujuan[{{ $siswa->id_siswa }}]" class="form-select form-select-sm border-secondary {{ $initialColor }}" onchange="updateSelectColor(this)">
                                            @foreach($pilihanKelasTujuan as $tujuan)
                                                @php
                                                    $isSelected = false;
                                                    $optColorClass = '';

                                                    // 1. Kondisi Tinggal Kelas (Merah)
                                                    if ($tujuan->id_kelas == $id_kelas_asal) {
                                                        $optColorClass = 'select-tinggal';
                                                        if (!$idKelasDefaultTujuan) $isSelected = true;
                                                    } 
                                                    // 2. Kondisi Naik Kelas - Jurusan Sama / Default (Ungu)
                                                    elseif ($idKelasDefaultTujuan && $tujuan->id_kelas == $idKelasDefaultTujuan) {
                                                        $optColorClass = 'select-naik-sama';
                                                        $isSelected = true;
                                                    } 
                                                    // 3. Kondisi Naik Kelas - Beda Jurusan / Lainnya (Orange)
                                                    else {
                                                        $optColorClass = 'select-naik-beda';
                                                    }
                                                @endphp

                                                {{-- Simpan class warna di data-color untuk dibaca JavaScript --}}
                                                <option value="{{ $tujuan->id_kelas }}" data-color="{{ $optColorClass }}" class="{{ $optColorClass }}" {{ $isSelected ? 'selected' : '' }}>
                                                    {{ $tujuan->nama_kelas }} 
                                                    {{ $tujuan->id_kelas == $id_kelas_asal ? ' -- (TINGGAL KELAS)' : ' -- (NAIK KELAS)' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-end border-top">
                    <button type="button" onclick="confirmProses()" class="btn btn-primary bg-gradient-primary btn-lg mb-0 shadow-sm">
                        <i class="fas fa-save me-2"></i> PROSES KENAIKAN KELAS
                    </button>
                </div>
            </div>
        </form>

        @elseif($id_kelas_asal && $dataSiswa->isEmpty())
        <div class="card shadow-sm border mt-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-users-slash text-danger mb-3 fa-3x opacity-5"></i>
                <h5 class="text-dark font-weight-bold">Tidak Ada Siswa Aktif</h5>
                <p class="text-secondary text-sm mb-0">Semua siswa di kelas ini mungkin sudah diluluskan, dimutasi, atau belum ada data siswa yang diinput.</p>
            </div>
        </div>
        @endif

    </div>
    <x-app.footer />
</main>

{{-- OVERLAY LOADING --}}
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; color: white; font-size: 1.5rem; z-index: 999999;">
    <div class="d-flex flex-column align-items-center">
        <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status"></div> 
        <span>Sedang Memproses Kenaikan Kelas...</span>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function confirmProses() {
        if (confirm('PERINGATAN!\n\nApakah Anda yakin ingin memproses kenaikan kelas ini? Data kelas seluruh siswa yang dipilih akan langsung di-update. Pastikan tahun ajaran dan kelas tujuan sudah benar!')) {
            $('#loadingOverlay').attr('style', 'display: flex !important; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; color: white; font-size: 1.5rem; z-index: 999999;');
            
            setTimeout(function() {
                document.getElementById('formKenaikanKelas').submit();
            }, 100);
        }
    }

    // Fungsi untuk merubah warna dropdown sesuai opsi yang dipilih
    function updateSelectColor(selectElement) {
        // 1. Hapus semua class warna yang mungkin menempel sebelumnya
        selectElement.classList.remove('select-tinggal', 'select-naik-sama', 'select-naik-beda');
        
        // 2. Ambil elemen <option> yang sedang dipilih
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        
        // 3. Baca atribut 'data-color' dari <option> tersebut
        var newColorClass = selectedOption.getAttribute('data-color');
        
        // 4. Pasang class warna baru ke elemen <select> induknya
        if (newColorClass) {
            selectElement.classList.add(newColorClass);
        }
    }
</script>
@endsection