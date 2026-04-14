@extends('layouts.app') 

@section('page-title', 'Setting Season Biodata Siswa')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    
    <x-app.navbar />
    
    <div class="container-fluid py-4 px-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- HEADER --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative d-flex justify-content-between align-items-center px-4">
                            <div>
                                <h6 class="text-white text-capitalize mb-0">
                                    <i class="fas fa-calendar-alt me-2"></i> Jadwal Pemutakhiran Biodata
                                </h6>
                                <p class="text-white text-xs opacity-8 mb-0 mt-1">
                                    Atur rentang tanggal akses fitur edit profil untuk seluruh siswa.
                                </p>
                            </div>
                            <div class="opacity-4">
                                <i class="fas fa-user-clock" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        
                        {{-- Pesan Notifikasi & Error --}}
                        @if (session('success'))
                            <div class="alert bg-gradient-success alert-dismissible text-white fade show mb-4" role="alert">
                                <span class="text-sm"><i class="fas fa-check-circle me-2"></i><strong>Berhasil!</strong> {{ session('success') }}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert bg-gradient-danger alert-dismissible text-white fade show mb-4" role="alert">
                                <span class="text-sm"><i class="fas fa-exclamation-triangle me-2"></i><strong>Gagal Menyimpan:</strong></span>
                                <ul class="mb-0 text-sm ps-4 mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        {{-- STATUS SAAT INI --}}
                        <div class="mb-4 p-3 rounded" style="background-color: #f8f9fa; border: 1px dashed #dee2e6;">
                            <h6 class="text-sm font-weight-bold text-secondary mb-2">Status Portal Saat Ini:</h6>
                            @if($season)
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $start = \Carbon\Carbon::parse($season->tanggal_mulai);
                                    $end = \Carbon\Carbon::parse($season->tanggal_akhir);
                                    $isOpen = $now->between($start, $end);
                                @endphp

                                @if($season->is_active && $isOpen)
                                    <span class="badge bg-gradient-success px-3 py-2 text-xs"><i class="fas fa-door-open me-1"></i> PORTAL DIBUKA (Sedang Berjalan)</span>
                                @elseif($season->is_active && !$isOpen)
                                    <span class="badge bg-gradient-warning px-3 py-2 text-xs text-dark"><i class="fas fa-hourglass-end me-1"></i> AKTIF TAPI KEDALUWARSA (Diluar Waktu)</span>
                                @else
                                    <span class="badge bg-gradient-danger px-3 py-2 text-xs"><i class="fas fa-lock me-1"></i> PORTAL DITUTUP (Disable)</span>
                                @endif
                            @else
                                <span class="badge bg-secondary px-3 py-2 text-xs">BELUM DIATUR</span>
                            @endif
                        </div>

                        {{-- FORM PENGATURAN --}}
                        <form action="{{ route('settings.bio_season.store') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label font-weight-bold text-dark text-sm">Set Waktu Mulai</label>
                                    <div class="input-group input-group-outline bg-white rounded-2 is-filled">
                                        <input type="datetime-local" name="tanggal_mulai" class="form-control px-3" value="{{ old('tanggal_mulai', $season ? \Carbon\Carbon::parse($season->tanggal_mulai)->format('Y-m-d\TH:i') : '') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label font-weight-bold text-dark text-sm">Set Waktu Selesai</label>
                                    <div class="input-group input-group-outline bg-white rounded-2 is-filled">
                                        <input type="datetime-local" name="tanggal_akhir" class="form-control px-3" value="{{ old('tanggal_akhir', $season ? \Carbon\Carbon::parse($season->tanggal_akhir)->format('Y-m-d\TH:i') : '') }}" required>
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark my-3">

                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-2">
                                <div class="mb-3 mb-md-0">
                                    <label class="form-label font-weight-bold text-dark text-sm mb-1">Mode Portal</label>
                                    <div class="form-check form-switch ps-0">
                                        <input class="form-check-input ms-0 mt-1" type="checkbox" id="isActiveSwitch" name="is_active" value="1" {{ old('is_active', $season->is_active ?? 0) ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                        <label class="form-check-label text-sm ms-3 mt-1 font-weight-bold text-dark" for="isActiveSwitch" id="statusLabel">
                                            {{ old('is_active', $season->is_active ?? 0) ? 'Active (Portal Dibuka)' : 'Disable (Portal Ditutup)' }}
                                        </label>
                                    </div>
                                    <small class="text-xs text-muted">Jika di-disable, siswa tidak bisa mengubah data meskipun masih dalam rentang waktu.</small>
                                </div>

                                <div class="d-flex">
                                    {{-- TOMBOL RESET (Hanya muncul jika $season sudah ada di database) --}}
                                    @if($season)
                                        <button type="button" class="btn btn-outline-danger btn-lg shadow-sm mb-0 me-2" data-bs-toggle="modal" data-bs-target="#modalReset">
                                            <i class="fas fa-trash-restore me-1"></i> Reset
                                        </button>
                                    @endif

                                    <button type="submit" class="btn bg-gradient-dark btn-lg shadow-sm mb-0">
                                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <x-app.footer />
    </div>

    {{-- MODAL KONFIRMASI RESET --}}
    @if($season)
    <div class="modal fade" id="modalReset" tabindex="-1" aria-labelledby="modalResetLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-danger">
                    <h5 class="modal-title font-weight-bold text-white" id="modalResetLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i> Konfirmasi Reset
                    </h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="icon-shape bg-light shadow-sm text-center rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-trash-alt text-danger fa-2x"></i>
                    </div>
                    <h5 class="text-dark font-weight-bold">Hapus Pengaturan Jadwal?</h5>
                    <p class="text-sm text-secondary mb-0">Tindakan ini akan menghapus jadwal saat ini secara permanen. Portal otomatis akan kembali ke status <strong>BELUM DIATUR</strong> dan form siswa akan <strong>TERKUNCI</strong>. Anda yakin?</p>
                </div>
                <div class="modal-footer justify-content-center border-top-0 pb-4 pt-0">
                    <button type="button" class="btn btn-white shadow-sm mb-0" data-bs-dismiss="modal">Tidak, Batalkan</button>
                    <form action="{{ route('settings.bio_season.reset') }}" method="POST" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn bg-gradient-danger shadow-sm mb-0">Ya, Hapus Jadwal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Script untuk mengubah teks label switch secara dinamis --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const switchInput = document.getElementById('isActiveSwitch');
            const statusLabel = document.getElementById('statusLabel');

            switchInput.addEventListener('change', function () {
                if (this.checked) {
                    statusLabel.textContent = 'Active (Portal Dibuka)';
                    statusLabel.classList.add('text-success');
                } else {
                    statusLabel.textContent = 'Disable (Portal Ditutup)';
                    statusLabel.classList.remove('text-success');
                }
            });
            
            // Trigger warna saat pertama load
            if(switchInput.checked) statusLabel.classList.add('text-success');
        });
    </script>
</main>
@endsection