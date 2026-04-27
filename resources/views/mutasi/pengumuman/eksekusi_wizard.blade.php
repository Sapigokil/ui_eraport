@extends('layouts.app') 

@section('page-title', 'Eksekusi Tutup Tahun Ajaran')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg pt-4">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        <div class="mb-4">
            <a href="{{ route('mutasi.eksekusi.index') }}?tahun_ajaran={{ $ta }}" class="btn btn-sm btn-white border shadow-sm text-dark font-weight-bold">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard Utama
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0" style="border-radius: 1rem;">
                    
                    {{-- ========================================== --}}
                    {{-- LANGKAH 3: TAMPILAN HASIL (RESULT) --}}
                    {{-- ========================================== --}}
                    @if(session('sukses_eksekusi'))
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                            </div>
                            <h3 class="font-weight-bolder text-dark mb-2">Tutup Tahun Berhasil!</h3>
                            <p class="text-secondary mb-4">Data master siswa untuk Tahun Ajaran <b>{{ $ta }}</b> telah diperbarui secara permanen.</p>
                            
                            <div class="row justify-content-center mb-4">
                                <div class="col-md-5">
                                    <div class="p-3 bg-light border-radius-md text-start shadow-sm border border-success">
                                        <h6 class="text-success font-weight-bolder mb-1"><i class="fas fa-graduation-cap me-2"></i>Lulus / Alumni</h6>
                                        <h3 class="mb-0 text-dark">{{ session('hasil_lulus') }} <span class="text-sm text-secondary font-weight-normal">Siswa</span></h3>
                                    </div>
                                </div>
                                <div class="col-md-5 mt-3 mt-md-0">
                                    <div class="p-3 bg-light border-radius-md text-start shadow-sm border border-primary">
                                        <h6 class="text-primary font-weight-bolder mb-1"><i class="fas fa-exchange-alt me-2"></i>Mutasi Kelas</h6>
                                        <h3 class="mb-0 text-dark">{{ session('hasil_pindah') }} <span class="text-sm text-secondary font-weight-normal">Siswa</span></h3>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('mutasi.eksekusi.index') }}" class="btn btn-success shadow-sm px-5">
                                Selesai
                            </a>
                        </div>

                    {{-- ========================================== --}}
                    {{-- LANGKAH 1: TAMPILAN PRATINJAU & VALIDASI --}}
                    {{-- ========================================== --}}
                    @else
                        <div class="card-header bg-gradient-danger p-4 border-radius-top-lg">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x text-white me-3"></i>
                                <div>
                                    <h4 class="text-white mb-0">Validasi Eksekusi Mutasi Akhir Tahun</h4>
                                    <p class="text-white opacity-8 text-sm mb-0">Tahun Ajaran: {{ $ta }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            
                            {{-- 👇 PERBAIKAN: Penangkap Pesan Error Server (Wajib Ada) 👇 --}}
                            @if(session('error'))
                                <div class="alert text-white font-weight-bold shadow-sm mb-4" style="background-color: #e63946; border-left: 6px solid #8e001c;">
                                    <h6 class="text-white mb-1"><i class="fas fa-times-circle me-1"></i> EKSEKUSI GAGAL:</h6>
                                    {{ session('error') }}
                                </div>
                            @endif

                            {{-- Area Loading --}}
                            <div id="loading-area" class="text-center d-none py-5">
                                <div class="spinner-border text-danger mb-3" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h5 class="text-dark font-weight-bolder mb-1">Mengeksekusi Data...</h5>
                                <p class="text-secondary text-sm">Mohon jangan tutup halaman ini. Sistem sedang memindahkan data ke tabel master.</p>
                            </div>

                            {{-- Area Form & Pratinjau --}}
                            <div id="form-area">
                                
                                <div class="alert text-dark text-sm p-4 mb-4 shadow-sm" style="background-color: #fff5f5; border-left: 6px solid #f5365c;">
                                    <h6 class="text-danger mb-2 font-weight-bolder"><i class="fas fa-exclamation-triangle me-1"></i> Peringatan Kritis!</h6>
                                    Anda akan memproses <b class="text-danger">{{ $rekap['total_eksekusi'] }} draf Mutasi</b> ke tabel Master Siswa. Tindakan ini bersifat permanen dan <b class="text-danger">tidak dapat diurungkan (Undo)</b>. Harap teliti kembali data siswa yang Tinggal Kelas atau Tidak Lulus di bawah ini.
                                </div>

                                {{-- REKAP GLOBAL --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded shadow-sm">
                                            <h6 class="text-uppercase text-secondary font-weight-bolder text-xs mb-2">Total Kelulusan</h6>
                                            <div class="d-flex justify-content-between text-sm mb-1">
                                                <span class="font-weight-bold">Lulus:</span> <span class="badge bg-success">{{ $rekap['total_lulus'] }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between text-sm">
                                                <span class="font-weight-bold">Tidak Lulus:</span> <span class="badge bg-danger">{{ $rekap['total_tidak_lulus'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded shadow-sm">
                                            <h6 class="text-uppercase text-secondary font-weight-bolder text-xs mb-2">Total Kenaikan</h6>
                                            <div class="d-flex justify-content-between text-sm mb-1">
                                                <span class="font-weight-bold">Naik Kelas:</span> <span class="badge bg-primary">{{ $rekap['total_naik'] }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between text-sm">
                                                <span class="font-weight-bold">Tinggal Kelas:</span> <span class="badge bg-danger">{{ $rekap['total_tinggal'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="horizontal dark my-4">

                                {{-- DATA TERBUKA: KELULUSAN --}}
                                <div class="mb-5">
                                    <h5 class="text-dark font-weight-bold mb-3 border-bottom pb-2">
                                        <i class="fas fa-graduation-cap text-warning me-2"></i> Rincian Kelulusan per Kelas
                                    </h5>
                                    
                                    @if(count($detailKelulusan) > 0)
                                        <div class="border rounded shadow-sm p-3">
                                            @foreach($detailKelulusan as $idx => $dk)
                                                <div class="mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="mb-0 text-dark font-weight-bolder text-md">{{ $dk['nama_kelas'] }} <span class="text-sm font-weight-normal text-secondary ms-1">({{ $dk['total'] }} Draf Siswa)</span></h6>
                                                        @if($dk['tidak_lulus'] > 0)
                                                            <span class="badge bg-danger text-sm px-3 py-2"><i class="fas fa-exclamation-triangle me-1"></i> {{ $dk['tidak_lulus'] }} Tidak Lulus</span>
                                                        @endif
                                                    </div>

                                                    {{-- Sorotan Siswa Tidak Lulus --}}
                                                    @if($dk['tidak_lulus'] > 0)
                                                        <div class="mb-3 p-3 rounded" style="background-color: #fff5f5; border-left: 4px solid #f5365c;">
                                                            <span class="text-sm font-weight-bold text-danger d-block mb-2">
                                                                <i class="fas fa-exclamation-circle me-1"></i> Perhatian! Data Siswa Tidak Lulus:
                                                            </span>
                                                            <ul class="text-sm text-dark font-weight-bold mb-0 ps-3">
                                                                @foreach($dk['list_tidak_lulus'] as $nama)
                                                                    <li>{{ $nama }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                    
                                                    <div class="ps-3 border-start border-3 border-success bg-light p-2 rounded">
                                                        <p class="text-sm mb-0 text-dark font-weight-bold">
                                                            <i class="fas fa-check text-xs me-2 text-success"></i> Lulus ({{ $dk['lulus'] }} Siswa)
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-3 border rounded text-center bg-light text-secondary">
                                            Belum ada draf kelulusan yang diproses.
                                        </div>
                                    @endif
                                    
                                    <div class="text-end mt-2">
                                        <a href="{{ route('mutasi.kelulusan_dashboard.index', ['tahun_ajaran' => $ta]) }}" class="text-warning text-sm font-weight-bold">
                                            <i class="fas fa-edit"></i> Revisi Data Kelulusan Jika Ada Kesalahan
                                        </a>
                                    </div>
                                </div>

                                {{-- DATA TERBUKA: KENAIKAN --}}
                                <div class="mb-5">
                                    <h5 class="text-dark font-weight-bold mb-3 border-bottom pb-2">
                                        <i class="fas fa-level-up-alt text-primary me-2"></i> Rincian Kenaikan per Kelas
                                    </h5>
                                    
                                    @if(count($detailKenaikan) > 0)
                                        <div class="border rounded shadow-sm p-3">
                                            @foreach($detailKenaikan as $idx => $dn)
                                                <div class="mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="mb-0 text-dark font-weight-bolder text-md">{{ $dn['nama_kelas_asal'] }} <span class="text-sm font-weight-normal text-secondary ms-1">({{ $dn['total'] }} Draf Siswa)</span></h6>
                                                        @if($dn['tinggal'] > 0)
                                                            <span class="badge bg-danger text-sm px-3 py-2"><i class="fas fa-exclamation-triangle me-1"></i> {{ $dn['tinggal'] }} Tinggal Kelas</span>
                                                        @endif
                                                    </div>

                                                    {{-- Sorotan Siswa Tinggal Kelas --}}
                                                    @if($dn['tinggal'] > 0)
                                                        <div class="mb-3 p-3 rounded" style="background-color: #fff5f5; border-left: 4px solid #f5365c;">
                                                            <span class="text-sm font-weight-bold text-danger d-block mb-2">
                                                                <i class="fas fa-exclamation-circle me-1"></i> Perhatian! Data Siswa Tinggal Kelas:
                                                            </span>
                                                            <ul class="text-sm text-dark font-weight-bold mb-0 ps-3">
                                                                @foreach($dn['list_tinggal'] as $nama)
                                                                    <li>{{ $nama }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(count($dn['naik_detail']) > 0)
                                                        <div class="ps-3 border-start border-3 border-primary bg-light p-2 rounded">
                                                            @foreach($dn['naik_detail'] as $nd)
                                                                <p class="text-sm mb-1 text-dark font-weight-bold">
                                                                    <i class="fas fa-arrow-right text-xs me-2 text-primary"></i> Naik ke <b>{{ $nd['nama_kelas_baru'] }}</b> ({{ $nd['jumlah'] }} Siswa)
                                                                </p>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-3 border rounded text-center bg-light text-secondary">
                                            Belum ada draf kenaikan yang diproses.
                                        </div>
                                    @endif

                                    <div class="text-end mt-2">
                                        <a href="{{ route('mutasi.kenaikan_dashboard.index', ['tahun_ajaran' => $ta]) }}" class="text-primary text-sm font-weight-bold">
                                            <i class="fas fa-edit"></i> Revisi Data Kenaikan Jika Ada Kesalahan
                                        </a>
                                    </div>
                                </div>

                                <hr class="horizontal dark my-4">

                                {{-- ZONA EKSEKUSI --}}
                                <div class="p-4 rounded border border-2 border-danger" style="background-color: #fdf2f2;">
                                    <form id="form-eksekusi" action="{{ route('mutasi.eksekusi.proses') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="tahun_ajaran" value="{{ $ta }}">
                                        
                                        <div class="mb-4 text-center">
                                            <h5 class="text-danger font-weight-bolder mb-3"><i class="fas fa-lock me-2"></i> ZONA EKSEKUSI FINAL</h5>
                                            <label class="form-label font-weight-bold text-dark text-md">Ketik <span class="badge bg-danger" style="font-size: 14px; letter-spacing: 1px;">PROSES</span> di bawah ini untuk mengonfirmasi bahwa data sudah benar:</label>
                                            <div class="d-flex justify-content-center">
                                                <div style="width: 100%; max-width: 400px;">
                                                    <input type="text" id="input-konfirmasi" name="konfirmasi" class="form-control form-control-lg border-2 border-danger text-center font-weight-bolder text-danger shadow-sm" placeholder="Ketik di sini..." required autocomplete="off" style="letter-spacing: 3px; font-size: 1.2rem;">
                                                    <small class="text-danger mt-2 d-block font-weight-bold" id="error-konfirmasi" style="display: none !important;">Kata kunci salah! Ketik persis: PROSES</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-center">
                                            <button type="button" id="btn-proses" class="btn btn-danger btn-lg shadow-sm mb-0 px-5" style="border-radius: 50px;">
                                                <i class="fas fa-bolt me-2"></i> Eksekusi Data Master Sekarang
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                            
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
    <x-app.footer />
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const btnProses = document.getElementById('btn-proses');
        const inputKonfirmasi = document.getElementById('input-konfirmasi');
        const formArea = document.getElementById('form-area');
        const loadingArea = document.getElementById('loading-area');
        const formEksekusi = document.getElementById('form-eksekusi');
        const errorText = document.getElementById('error-konfirmasi');

        if(btnProses) {
            btnProses.addEventListener('click', function(e) {
                if (inputKonfirmasi.value !== "PROSES") {
                    errorText.style.setProperty('display', 'block', 'important');
                    inputKonfirmasi.classList.add('is-invalid');
                    return;
                }
                
                errorText.style.setProperty('display', 'none', 'important');
                inputKonfirmasi.classList.remove('is-invalid');
                
                formArea.classList.add('d-none');
                loadingArea.classList.remove('d-none');

                setTimeout(() => {
                    formEksekusi.submit();
                }, 500);
            });

            inputKonfirmasi.addEventListener("keypress", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    btnProses.click();
                }
            });
        }
    });
</script>
@endsection