@extends('layouts.app') 

@section('page-title', 'Eksekusi Kenaikan Kelas')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg pt-4">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        <div class="mb-3">
            <a href="{{ route('mutasi.kenaikan_dashboard.index') }}" class="btn btn-sm btn-white border-secondary shadow-sm mb-0 text-dark font-weight-bold">
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
                            <i class="fas fa-chalkboard-teacher me-2"></i> {{ $kelasAsalTerpilih->nama_kelas }}
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
                            <h4 class="text-white font-weight-bolder mb-0">{{ $stat['naik'] }}</h4>
                            <span style="font-size: 0.65rem;" class="text-uppercase opacity-8"><i class="fas fa-level-up-alt"></i> Naik</span>
                        </div>
                        <div class="text-center px-3">
                            <h4 class="text-white font-weight-bolder mb-0">{{ $stat['tinggal'] }}</h4>
                            <span style="font-size: 0.65rem;" class="text-uppercase opacity-8"><i class="fas fa-level-down-alt"></i> Gagal</span>
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
                        <h6 class="mb-0 text-dark font-weight-bold">Daftar Keputusan Kenaikan Kelas</h6>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <div class="btn-group shadow-sm">
                            <button type="button" id="btn-set-naik" class="btn btn-xs btn-outline-primary mb-0">
                                <i class="fas fa-level-up-alt me-1"></i> Semua Naik Kelas
                            </button>
                            <button type="button" id="btn-set-tinggal" class="btn btn-xs btn-outline-danger mb-0">
                                <i class="fas fa-level-down-alt me-1"></i> Semua Tinggal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('mutasi.kenaikan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_kelas_lama" value="{{ $id_kelas_asal }}">
                <input type="hidden" name="tahun_ajaran_lama" value="{{ $taLama }}">
                <input type="hidden" name="tahun_ajaran_baru" value="{{ $taBaru }}">

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
                                        <select name="tujuan[{{ $siswa->id_siswa }}]" class="form-select form-select-sm status-dropdown fw-bold text-center mx-auto" style="width: 250px;">
                                            <option value="" {{ $siswa->status_kenaikan == 'belum' ? 'selected' : '' }}>-- Belum Diproses --</option>
                                            
                                            <option value="tinggal" {{ $siswa->status_kenaikan == 'tinggal_kelas' ? 'selected' : '' }}>
                                                ✖ TINGGAL KELAS ({{ $kelasAsalTerpilih->nama_kelas }})
                                            </option>
                                            
                                            <optgroup label="Naik Ke Kelas:">
                                                @foreach($pilihanKelasTujuan as $kt)
                                                    @if($kt->id_kelas != $id_kelas_asal)
                                                        <option value="{{ $kt->id_kelas }}" {{ $siswa->id_kelas_tujuan == $kt->id_kelas ? 'selected' : '' }}>
                                                            ✔️ NAIK KE {{ $kt->nama_kelas }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
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
                        <i class="fas fa-info-circle me-1"></i> Data akan disimpan sebagai <b>Draf</b> hingga proses eksekusi di Akhir Tahun Ajaran.
                    </p>
                    <button type="submit" class="btn btn-primary mb-0 shadow-sm px-4">
                        <i class="fas fa-save me-2"></i> Simpan Draf Kenaikan
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
    
    select option[value="tinggal"] { color: #f5365c; font-weight: bold; }
    select optgroup option { color: #5e72e4; font-weight: bold; }
    
    .border-naik { border: 2px solid #5e72e4 !important; color: #5e72e4 !important; background-color: rgba(94, 114, 228, 0.05) !important; }
    .border-tinggal { border: 2px solid #f5365c !important; color: #f5365c !important; background-color: rgba(245, 54, 92, 0.05) !important; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const dropdowns = document.querySelectorAll('.status-dropdown');
    const defaultKelasTujuan = "{{ $idKelasDefaultTujuan }}";

    function updateStyle(el) {
        el.classList.remove('border-naik', 'border-tinggal');
        if(el.value === 'tinggal') {
            el.classList.add('border-tinggal');
        } else if(el.value !== "") {
            el.classList.add('border-naik');
        }
    }

    dropdowns.forEach(dd => updateStyle(dd));

    dropdowns.forEach(dd => {
        dd.addEventListener('change', function() { updateStyle(this); });
    });

    document.getElementById('btn-set-naik').addEventListener('click', function() {
        if(!defaultKelasTujuan) {
            alert('Sistem tidak menemukan kelas tujuan otomatis (Gagal prediksi). Silakan pilih kelas manual untuk setiap siswa.');
            return;
        }
        dropdowns.forEach(dd => {
            dd.value = defaultKelasTujuan;
            updateStyle(dd);
        });
    });

    document.getElementById('btn-set-tinggal').addEventListener('click', function() {
        dropdowns.forEach(dd => {
            dd.value = 'tinggal';
            updateStyle(dd);
        });
    });
});
</script>
@endsection