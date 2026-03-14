@extends('layouts.app') 

@section('page-title', 'Pengaturan Mode Simulasi')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                {{-- ALERT MESSAGES --}}
                @if (session('success'))
                    <div class="alert bg-gradient-success alert-dismissible text-white fade show shadow-sm" role="alert">
                        <span class="text-sm"><strong><i class="fas fa-check-circle me-2"></i>Berhasil!</strong> {{ session('success') }}</span>
                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert bg-gradient-danger alert-dismissible text-white fade show shadow-sm" role="alert">
                        <span class="text-sm"><strong><i class="fas fa-exclamation-triangle me-2"></i>Gagal!</strong> {{ session('error') }}</span>
                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                    </div>
                @endif

                {{-- 1. BAGIAN EDUKASI (INFORMASI FITUR) --}}
                <div class="card shadow-sm border-0 mb-4 bg-gradient-dark overflow-hidden position-relative">
                    {{-- Dekorasi Latar Belakang --}}
                    <div class="position-absolute top-0 end-0 opacity-1 mt-n3 me-n3">
                        <i class="fas fa-flask text-white" style="font-size: 15rem;"></i>
                    </div>

                    <div class="card-body p-4 p-md-5 position-relative z-index-1">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon icon-shape bg-white text-center border-radius-md shadow-sm me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-info-circle text-dark text-lg"></i>
                            </div>
                            <h4 class="text-white mb-0">Apa itu Mode Simulasi (Sandbox)?</h4>
                        </div>
                        <p class="text-white opacity-8 mb-4 text-sm" style="line-height: 1.6; text-align: justify; max-width: 85%;">
                            Mode Simulasi adalah fitur keamanan tingkat lanjut yang memungkinkan Admin dan Guru melakukan uji coba sistem, pelatihan input nilai, hingga demonstrasi tanpa rasa takut. 
                            Ketika mode ini aktif, seluruh aktivitas perubahan data (Tambah, Edit, Hapus) akan diarahkan ke sebuah <strong>Database Bayangan (Demo)</strong>. Data nilai siswa dan rapor yang asli akan tetap aman dan tidak tersentuh sama sekali.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-shield-alt text-success mt-1 me-2 text-lg"></i>
                                    <div>
                                        <h6 class="text-white text-sm mb-0">Aman 100%</h6>
                                        <span class="text-xs text-white opacity-6">Data asli tidak akan terganggu.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-chalkboard-teacher text-info mt-1 me-2 text-lg"></i>
                                    <div>
                                        <h6 class="text-white text-sm mb-0">Ideal Untuk Training</h6>
                                        <span class="text-xs text-white opacity-6">Bebas uji coba fitur E-Rapor.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-sync-alt text-warning mt-1 me-2 text-lg"></i>
                                    <div>
                                        <h6 class="text-white text-sm mb-0">Reset Kapan Saja</h6>
                                        <span class="text-xs text-white opacity-6">Kembalikan data demo seperti semula.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. BAGIAN AKSI (SINKRONISASI) --}}
                <div class="card shadow-sm border border-light">
                    <div class="card-header bg-light border-bottom p-3">
                        <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-cogs me-2"></i> Manajemen Database Simulasi</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            
                            {{-- Info Terakhir Sync --}}
                            <div class="mb-4 mb-md-0 pe-md-4">
                                <h6 class="text-dark font-weight-bold mb-1">Reset & Sinkronisasi Ulang</h6>
                                <p class="text-sm text-secondary mb-2" style="line-height: 1.5; max-width: 500px;">
                                    Tindakan ini akan <strong>menghapus seluruh data simulasi saat ini</strong>, lalu menyalin ulang (Copy-Paste) seluruh data terbaru dari Database Asli ke Database Simulasi.
                                </p>
                                <div class="d-flex align-items-center mt-3">
                                    <div class="icon icon-sm bg-gradient-secondary text-center border-radius-sm text-white me-2 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-history text-xs"></i>
                                    </div>
                                    <div>
                                        <span class="text-xs font-weight-bold text-dark d-block">Terakhir Disinkronkan:</span>
                                        @if($lastSync)
                                            <span class="text-sm text-success font-weight-bold">{{ \Carbon\Carbon::parse($lastSync)->translatedFormat('l, d F Y - H:i:s') }} WIB</span>
                                        @else
                                            <span class="text-sm text-danger font-weight-bold">Belum Pernah Dilakukan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Eksekusi --}}
                            <div class="text-md-end border-md-start ps-md-4">
                                <form id="formSyncDatabase" action="{{ route('settings.simulasi.sync') }}" method="POST">
                                    @csrf
                                    <button type="button" onclick="confirmSync()" class="btn btn-lg bg-gradient-danger shadow-danger mb-0 px-4">
                                        <i class="fas fa-sync fa-spin-hover me-2"></i> SINKRONKAN SEKARANG
                                    </button>
                                    <span class="d-block text-xxs text-secondary mt-2"><i class="fas fa-exclamation-circle text-warning me-1"></i> Proses ini memakan waktu beberapa detik.</span>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <x-app.footer />
</main>

{{-- OVERLAY LOADING (GELAP & ELEGAN) --}}
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); justify-content: center; align-items: center; color: white; font-size: 1.2rem; z-index: 9999999; flex-direction: column;">
    <div class="spinner-border text-danger mb-4" style="width: 4rem; height: 4rem; border-width: 0.35em;" role="status"></div> 
    <h4 class="text-white font-weight-bold tracking-wide mb-1" style="letter-spacing: 2px;">MENYINKRONKAN DATABASE</h4>
    <p class="text-sm text-secondary opacity-8">Sedang menyalin struktur dan data. Mohon jangan tutup halaman ini...</p>
</div>

<style>
    .fa-spin-hover:hover {
        animation: fa-spin 2s infinite linear;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function confirmSync() {
        if (confirm('PERINGATAN KERAS!\n\nSeluruh data uji coba/demo yang ada di Database Simulasi saat ini akan DIHAPUS PERMANEN dan diganti dengan data terbaru dari Database Asli.\n\nApakah Anda yakin ingin melanjutkan sinkronisasi ini?')) {
            
            // Tampilkan Overlay Loading
            $('#loadingOverlay').attr('style', 'display: flex !important; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); justify-content: center; align-items: center; flex-direction: column; z-index: 9999999;');
            
            // Berikan sedikit delay agar UI loading sempat ter-render sebelum PHP mengeksekusi query berat
            setTimeout(function() {
                document.getElementById('formSyncDatabase').submit();
            }, 300);
        }
    }
</script>
@endsection