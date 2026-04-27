@extends('layouts.app') 

@section('page-title', 'Eksekusi Kelulusan')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg pt-4">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        <div class="mb-3">
            <a href="{{ route('mutasi.kelulusan_dashboard.index') }}" class="btn btn-sm btn-white border-secondary shadow-sm mb-0 text-dark font-weight-bold">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
            </a>
        </div>

        {{-- BANNER HEADER DINAMIS --}}
        <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 1rem; background: {{ $bg_gradient }};">
            <div class="card-body p-3 position-relative"> 
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                
                <div class="row align-items-center position-relative z-index-1">
                    <div class="col-md-5 col-lg-6 text-white">
                        <h5 class="text-white font-weight-bolder mb-1">
                            <i class="fas fa-graduation-cap me-2"></i> {{ $kelasAsalTerpilih->nama_kelas }}
                        </h5>
                        <p class="mb-0 text-sm opacity-9">
                            TA: <b>{{ $taLama }}</b> &nbsp;|&nbsp; Wali: <b>{{ $kelasAsalTerpilih->wali_kelas ?? 'Tanpa Wali' }}</b>
                        </p>
                    </div>
                    
                    {{-- STATISTIK DI SISI KANAN BANNER --}}
                    <div class="col-md-7 col-lg-6 mt-3 mt-md-0 d-flex justify-content-md-end justify-content-between text-white">
                        <div class="text-center px-3 border-end" style="border-color: rgba(255,255,255,0.2) !important;">
                            <h4 class="text-white font-weight-bolder mb-0">{{ count($dataSiswa) }}</h4>
                            <span style="font-size: 0.65rem;" class="text-uppercase opacity-8">Siswa</span>
                        </div>
                        <div class="text-center px-3 border-end" style="border-color: rgba(255,255,255,0.2) !important;">
                            <h4 class="text-white font-weight-bolder mb-0">{{ $stat['belum'] }}</h4>
                            <span style="font-size: 0.65rem;" class="text-uppercase opacity-8"><i class="fas fa-hourglass-half"></i> Belum</span>
                        </div>
                        <div class="text-center px-3 border-end" style="border-color: rgba(255,255,255,0.2) !important;">
                            <h4 class="text-white font-weight-bolder mb-0">{{ $stat['lulus'] }}</h4>
                            <span style="font-size: 0.65rem;" class="text-uppercase opacity-8"><i class="fas fa-check"></i> Lulus</span>
                        </div>
                        <div class="text-center px-3">
                            <h4 class="text-white font-weight-bolder mb-0">{{ $stat['tidak_lulus'] }}</h4> 
                            <span style="font-size: 0.65rem;" class="text-uppercase opacity-8"><i class="fas fa-times"></i> Gagal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL DATA --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom p-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-0 text-dark font-weight-bold">Daftar Keputusan Siswa</h6>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <div class="btn-group shadow-sm">
                            <button type="button" id="btn-set-lulus" class="btn btn-xs btn-outline-success mb-0">
                                <i class="fas fa-check-double me-1"></i> Semua Lulus
                            </button>
                            <button type="button" id="btn-set-tidak-lulus" class="btn btn-xs btn-outline-danger mb-0">
                                <i class="fas fa-times-circle me-1"></i> Semua Tdk Lulus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('mutasi.kelulusan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_kelas_lama" value="{{ $id_kelas_asal }}">
                <input type="hidden" name="tahun_ajaran_lama" value="{{ $taLama }}">

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0 table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                    <th class="text-xxs font-weight-bolder opacity-7 ps-3" style="width: 15%">NISN</th>
                                    <th class="text-xxs font-weight-bolder opacity-7 ps-3">Nama Lengkap Siswa</th>
                                    <th class="text-center text-xxs font-weight-bolder opacity-7" style="width: 25%">Keputusan Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataSiswa as $idx => $siswa)
                                <tr>
                                    <td class="text-center text-sm text-secondary">{{ $idx + 1 }}</td>
                                    <td class="text-sm font-weight-bold text-dark ps-3">{{ $siswa->nisn ?? '-' }}</td>
                                    <td class="ps-3">
                                        <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $siswa->nama_siswa }}</h6>
                                    </td>
                                    <td class="text-center p-2">
                                        <select name="tujuan[{{ $siswa->id_siswa }}]" class="form-select form-select-sm status-dropdown fw-bold text-center mx-auto" style="width: 200px;">
                                            <option value="" {{ $siswa->status_kelulusan == '' ? 'selected' : '' }}>-- Belum Diproses --</option>
                                            <option value="lulus" {{ $siswa->status_kelulusan == 'lulus' ? 'selected' : '' }}>LULUS</option>
                                            <option value="tidak_lulus" {{ $siswa->status_kelulusan == 'tidak_lulus' ? 'selected' : '' }}>TIDAK LULUS</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light border-top p-3 d-flex justify-content-between align-items-center">
                    <p class="text-xs text-secondary mb-0">
                        <i class="fas fa-info-circle me-1"></i> Hanya siswa dengan status <b>Lulus/Tidak Lulus</b> yang akan dicatat ke riwayat (Draf).
                    </p>
                    <button type="submit" class="btn btn-primary mb-0 shadow-sm px-4">
                        <i class="fas fa-save me-2"></i> Simpan Hasil Kelulusan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <x-app.footer />
</main>

<style>
    .status-dropdown { border: 1px solid #d2d6da; transition: all 0.2s; background-color: #fff; }
    .status-dropdown:focus { border-color: #5e72e4; box-shadow: 0 0 0 2px rgba(94, 114, 228, 0.2); }
    
    select option[value="lulus"] { color: #2dce89; font-weight: bold; }
    select option[value="tidak_lulus"] { color: #f5365c; font-weight: bold; }
    
    .border-lulus { border: 2px solid #2dce89 !important; color: #2dce89 !important; background-color: rgba(45, 206, 137, 0.05) !important; }
    .border-gagal { border: 2px solid #f5365c !important; color: #f5365c !important; background-color: rgba(245, 54, 92, 0.05) !important; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const dropdowns = document.querySelectorAll('.status-dropdown');

    function updateStyle(el) {
        el.classList.remove('border-lulus', 'border-gagal');
        if(el.value === 'lulus') el.classList.add('border-lulus');
        if(el.value === 'tidak_lulus') el.classList.add('border-gagal');
    }

    dropdowns.forEach(dd => updateStyle(dd));

    dropdowns.forEach(dd => {
        dd.addEventListener('change', function() { updateStyle(this); });
    });

    document.getElementById('btn-set-lulus').addEventListener('click', function() {
        dropdowns.forEach(dd => {
            dd.value = 'lulus';
            updateStyle(dd);
        });
    });

    document.getElementById('btn-set-tidak-lulus').addEventListener('click', function() {
        dropdowns.forEach(dd => {
            dd.value = 'tidak_lulus';
            updateStyle(dd);
        });
    });
});
</script>
@endsection