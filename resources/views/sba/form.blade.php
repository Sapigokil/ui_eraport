@extends('layouts.app')

@section('page-title', isset($uji) ? 'Edit Asesmen & Soal SBA' : 'Buat Asesmen & Soal Baru')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <style>
        .btn-export-excel-ungu {
            background-color: #ffffff !important;
            border: 1px solid #6f42c1 !important;
            color: #6f42c1 !important;
        }
        .btn-export-excel-ungu:hover,
        .btn-export-excel-ungu:focus {
            background-color: #f8f5ff !important;
            border-color: #59339d !important;
            color: #59339d !important;
        }
    </style>

    <div class="container-fluid py-4 px-4">
        {{-- ENCTYPE MULTIPART/FORM-DATA PENTING UNTUK UPLOAD GAMBAR --}}
        <form action="{{ isset($uji) ? route('sba.update', $uji->id) : route('sba.store') }}" 
              method="POST" 
              id="form-ujian-sba" 
              enctype="multipart/form-data">
            @csrf
            @if(isset($uji))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-12">
                    
                    {{-- MENAMPILKAN ERROR VALIDASI (FALLBACK SERVER) --}}
                    @if ($errors->any())
                        <div class="alert bg-gradient-danger text-white alert-dismissible fade show text-sm py-2 mb-4" role="alert">
                            <strong class="d-block mb-1">Terjadi Kesalahan!</strong>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close text-lg py-2 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    {{-- ALERT ERROR VALIDASI / GAGAL SIMPAN VIA AJAX (TANPA RELOAD) --}}
                    <div id="ajax-error-alert" class="alert bg-gradient-danger text-white alert-dismissible fade show text-sm py-3 px-4 mb-4 d-none shadow-sm" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-circle text-lg me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block mb-1" id="ajax-error-title">Gagal Menyimpan Asesmen!</strong>
                                <ul class="mb-0 ps-3" id="ajax-error-list"></ul>
                                <small class="d-block mt-2 opacity-9">
                                    <i class="fas fa-shield-alt me-1"></i> Seluruh butir soal dan lampiran gambar Anda tetap aman dan tidak hilang. Silakan perbaiki data di atas lalu simpan kembali.
                                </small>
                            </div>
                            <button type="button" class="btn-close text-lg py-2 opacity-10" onclick="document.getElementById('ajax-error-alert').classList.add('d-none')" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>

                    {{-- ALERT NOTIFIKASI HASIL IMPORT EXCEL / PENYESUAIAN BOBOT DI VIEW --}}
                    <div id="alert-import-success" class="alert bg-gradient-success text-white alert-dismissible fade show text-sm py-2 mb-4 d-none" role="alert">
                        <span id="alert-import-success-text"></span>
                        <button type="button" class="btn-close text-lg py-2 opacity-10" onclick="this.parentElement.classList.add('d-none')" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="card my-4 shadow-xs border">
                        {{-- HEADER UTAMA (Gaya Banner) --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                                <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                    <i class="fas fa-file-signature text-white" style="font-size: 8rem;"></i>
                                </div>

                                <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                    <div>
                                        <h6 class="text-white text-capitalize mb-0">
                                            <i class="fas fa-edit me-2"></i> {{ isset($uji) ? 'Edit Asesmen & Soal SBA' : 'Buat Asesmen & Soal Baru' }}
                                        </h6>
                                        <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                            Pengaturan informasi dasar asesmen dan penyusunan butir soal.
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('sba.index') }}" class="btn btn-outline-white btn-sm mb-0 px-3">
                                            <i class="fas fa-arrow-left me-1"></i> Kembali
                                        </a>
                                        <button type="submit" class="btn btn-white text-primary btn-sm mb-0 shadow-sm px-3 fw-bold btn-submit-sba">
                                            <i class="fas fa-save me-1"></i> {{ isset($uji) ? 'Simpan Perubahan' : 'Simpan Asesmen' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 py-3">
                            {{-- SECTION 1: PENGATURAN ASESMEN --}}
                            <h6 class="mb-3 text-primary border-bottom pb-2 text-sm"><i class="fas fa-cog me-1"></i> Bagian 1: Pengaturan Asesmen</h6>
                            
                            {{-- INFORMASI AUTHOR / PEMBUAT ASESMEN --}}
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="p-2 px-3 bg-light border rounded d-flex align-items-center">
                                        <i class="fas fa-user-circle text-primary me-2 text-lg"></i>
                                        <span class="text-xs text-secondary me-2">Pembuat Asesmen (Author):</span>
                                        <strong class="text-xs text-dark">
                                            {{ isset($uji) && $uji->author ? $uji->author->name : (auth()->user()->name ?? 'Administrator') }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- KOLOM KIRI: Informasi Dasar --}}
                                <div class="col-md-7 pe-md-4">
                                    <div class="form-group mb-2">
                                        <label for="judul" class="form-control-label text-xs mb-1">Judul Asesmen <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm border px-2 py-1" id="judul" name="judul" 
                                               value="{{ old('judul', $uji->judul ?? '') }}" 
                                               placeholder="Contoh: Penilaian Tengah Semester - Matematika" required>
                                    </div>

                                    {{-- DROPDOWN KATEGORI UJI & MATA PELAJARAN --}}
                                    @php
                                        $selectedKategori = old('kategori_uji', $uji->kategori_uji ?? 'Umum');
                                        $selectedMapel = old('id_mapel', isset($uji) ? $uji->id_mapel : ($defaultMapelId ?? null));
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-5 form-group mb-2">
                                            <label for="kategori_uji" class="form-control-label text-xs mb-1">Kategori Uji <span class="text-danger">*</span></label>
                                            <select class="form-select form-select-sm border px-2 py-1 @error('kategori_uji') is-invalid @enderror" id="kategori_uji" name="kategori_uji" required>
                                                <option value="Umum" {{ $selectedKategori == 'Umum' ? 'selected' : '' }}>Umum</option>
                                                <option value="PSTS" {{ $selectedKategori == 'PSTS' ? 'selected' : '' }}>PSTS</option>
                                                <option value="PSAS" {{ $selectedKategori == 'PSAS' ? 'selected' : '' }}>PSAS</option>
                                                <option value="Sumatif" {{ $selectedKategori == 'Sumatif' ? 'selected' : '' }}>Sumatif</option>
                                            </select>
                                        </div>
                                        <div class="col-md-7 form-group mb-2">
                                            <label for="id_mapel" class="form-control-label text-xs mb-1">Mata Pelajaran</label>
                                            <select class="form-select form-select-sm border px-2 py-1 @error('id_mapel') is-invalid @enderror" id="id_mapel" name="id_mapel">
                                                <option value="" {{ empty($selectedMapel) ? 'selected' : '' }}>-- Tanpa Mata Pelajaran (Umum) --</option>
                                                @foreach($mapels ?? [] as $mapel)
                                                    <option value="{{ $mapel->id_mapel }}" {{ (string) $selectedMapel === (string) $mapel->id_mapel ? 'selected' : '' }}>
                                                        {{ $mapel->nama_mapel }}{{ !empty($mapel->nama_singkat) ? ' (' . $mapel->nama_singkat . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group mb-2">
                                        <label for="kode_uji" class="form-control-label text-xs mb-1">Kode Asesmen <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm border px-2 py-1 text-uppercase fw-bold @error('kode_uji') is-invalid @enderror" 
                                                   id="kode_uji" name="kode_uji" 
                                                   value="{{ old('kode_uji', $uji->kode_uji ?? strtoupper(\Illuminate\Support\Str::random(6))) }}" 
                                                   placeholder="Contoh: MAT2026" required maxlength="12" pattern="[A-Za-z0-9]{1,12}"
                                                   title="Hanya huruf (A-Z) dan angka (0-9), maksimal 12 karakter">
                                            <button type="button" class="btn btn-outline-secondary mb-0 px-3" id="btn-generate-kode" title="Acak Kode Otomatis (6 Karakter)">
                                                <i class="fas fa-sync-alt me-1"></i> Acak
                                            </button>
                                        </div>
                                        <small id="kode-uji-status" class="text-xxs d-block mt-1 fw-semibold d-none"></small>
                                        <small class="text-xxs text-secondary d-block mt-1">Bisa diisi kode custom (hanya huruf A-Z dan angka 0-9, maksimal 12 karakter, tanpa spasi).</small>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="deskripsi" class="form-control-label text-xs mb-1">Deskripsi / Petunjuk</label>
                                        <textarea class="form-control form-control-sm border px-2 py-1" id="deskripsi" name="deskripsi" rows="3" 
                                                  placeholder="Masukkan petunjuk pengerjaan (opsional)...">{{ old('deskripsi', $uji->deskripsi ?? '') }}</textarea>
                                    </div>
                                </div>

                                {{-- KOLOM KANAN: Pengaturan Waktu & Sistem --}}
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-md-6 form-group mb-2">
                                            <label for="waktu_mulai" class="form-control-label text-xs mb-1">Waktu Mulai</label>
                                            <input type="datetime-local" class="form-control form-control-sm border px-2 py-1" id="waktu_mulai" name="waktu_mulai" 
                                                   value="{{ old('waktu_mulai', isset($uji) && $uji->waktu_mulai ? $uji->waktu_mulai->format('Y-m-d\TH:i') : '') }}">
                                        </div>
                                        <div class="col-md-6 form-group mb-2">
                                            <label for="waktu_selesai" class="form-control-label text-xs mb-1">Waktu Selesai</label>
                                            <input type="datetime-local" class="form-control form-control-sm border px-2 py-1" id="waktu_selesai" name="waktu_selesai" 
                                                   value="{{ old('waktu_selesai', isset($uji) && $uji->waktu_selesai ? $uji->waktu_selesai->format('Y-m-d\TH:i') : '') }}">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 form-group mb-2">
                                            <label for="durasi_menit" class="form-control-label text-xs mb-1">Durasi (Menit) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control form-control-sm border px-2 py-1" id="durasi_menit" name="durasi_menit" 
                                                   value="{{ old('durasi_menit', $uji->durasi_menit ?? 0) }}" min="0" placeholder="0 = Tanpa Batas" required>
                                            <small class="text-xxs text-secondary">0 = Tanpa batas</small>
                                        </div>
                                        <div class="col-md-6 form-group mb-2">
                                            <label for="status" class="form-control-label text-xs mb-1">Status</label>
                                            <select class="form-select form-select-sm border px-2 py-1" id="status" name="status">
                                                <option value="draft" {{ old('status', $uji->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft (Sembunyi)</option>
                                                <option value="published" {{ old('status', $uji->status ?? '') == 'published' ? 'selected' : '' }}>Published (Aktif)</option>
                                                <option value="closed" {{ old('status', $uji->status ?? '') == 'closed' ? 'selected' : '' }}>Closed (Tutup)</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- PENGATURAN KEAMANAN & PERCOBAAN --}}
                                    <div class="row border-top pt-2 mt-1">
                                        {{-- KOLOM KIRI: Mode Ujian (Open Book / Strict Mode) & Opsi Umum --}}
                                        <div class="col-md-6">
                                            <div class="p-2 bg-light border rounded mb-2">
                                                <div class="form-check form-switch ps-0 mb-1">
                                                    <input class="form-check-input ms-0" type="checkbox" id="open_book" name="open_book" value="1" 
                                                        {{ old('open_book', $uji->open_book ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-xs ms-2 mb-0 fw-bold text-dark" for="open_book">Mode Open Book</label>
                                                </div>
                                                
                                                <small id="text-open-book-desc" class="text-xxs d-block {{ old('open_book', $uji->open_book ?? false) ? 'text-success fw-bold' : 'text-secondary' }}" style="line-height: 1.3;">
                                                    {{ old('open_book', $uji->open_book ?? false) 
                                                        ? 'Izinkan peserta berpindah tab / minimize tanpa pelanggaran' 
                                                        : 'Peserta dilarang berpindah / minimize tab selama uji berlangsung' }}
                                                </small>

                                                <div id="wrapper-toleransi" class="form-group mb-0 border-top pt-2 mt-2 {{ old('open_book', $uji->open_book ?? false) ? 'd-none' : '' }}">
                                                    <label for="toleransi_pelanggaran" class="form-control-label text-xs mb-1">Toleransi Pelanggaran</label>
                                                    <input type="number" class="form-control form-control-sm border px-2 py-1 bg-white" id="toleransi_pelanggaran" name="toleransi_pelanggaran" 
                                                        value="{{ old('toleransi_pelanggaran', $uji->toleransi_pelanggaran ?? 3) }}" min="0" required>
                                                    <small class="text-xxs text-secondary d-block">Batas minimize/pindah tab</small>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column gap-2 mt-2">
                                                <div class="form-check form-switch ps-0 mb-0">
                                                    <input class="form-check-input ms-0" type="checkbox" id="acak_soal" name="acak_soal" value="1" 
                                                        {{ old('acak_soal', $uji->acak_soal ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-xs ms-2 mb-0" for="acak_soal">Acak Soal</label>
                                                </div>
                                                <div class="form-check form-switch ps-0 mb-0">
                                                    <input class="form-check-input ms-0" type="checkbox" id="tampilkan_nilai" name="tampilkan_nilai" value="1" 
                                                        {{ old('tampilkan_nilai', $uji->tampilkan_nilai ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-xs ms-2 mb-0" for="tampilkan_nilai">Tampilkan Nilai</label>
                                                </div>
                                                <div class="form-check form-switch ps-0 mb-0">
                                                    <input class="form-check-input ms-0" type="checkbox" id="buka_untuk_umum" name="buka_untuk_umum" value="1" 
                                                        {{ old('buka_untuk_umum', $uji->buka_untuk_umum ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-xs ms-2 mb-0" for="buka_untuk_umum">Buka untuk Umum</label>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- KOLOM KANAN: Grup Pengulangan & Maks. Percobaan --}}
                                        <div class="col-md-6">
                                            <div class="p-2 bg-light border rounded h-100">
                                                <div class="form-check form-switch ps-0 mb-1">
                                                    <input class="form-check-input ms-0" type="checkbox" id="ulangi_jika_gagal" name="ulangi_jika_gagal" value="1" 
                                                           {{ old('ulangi_jika_gagal', $uji->ulangi_jika_gagal ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-xs ms-2 mb-0 fw-bold text-dark" for="ulangi_jika_gagal">Ulangi Jika Gagal</label>
                                                </div>
                                                <small class="text-xxs text-secondary d-block mb-2" style="line-height: 1.3;">
                                                    Izinkan peserta mengulang ujian jika gagal/pelanggaran
                                                </small>

                                                <div id="wrapper-maks-percobaan" class="form-group mb-0 border-top pt-2 mt-2 {{ old('ulangi_jika_gagal', $uji->ulangi_jika_gagal ?? false) ? '' : 'd-none' }}">
                                                    <label for="maksimal_percobaan" class="form-control-label text-xs mb-1">Maks. Percobaan</label>
                                                    <input type="number" class="form-control form-control-sm border px-2 py-1 bg-white" id="maksimal_percobaan" name="maksimal_percobaan" 
                                                           value="{{ old('maksimal_percobaan', $uji->maksimal_percobaan ?? 1) }}" min="1" required>
                                                    <small class="text-xxs text-secondary d-block">Total kuota kesempatan ujian</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- INFORMASI TOTAL DAN AUTO ADJUST --}}
                            <div class="row mb-3 mt-2" id="box-rekap-soal">
                                <div class="col-12">
                                    <div class="p-3 bg-gray-50 border rounded d-flex flex-column flex-md-row justify-content-between align-items-center shadow-sm">
                                        <div class="mb-2 mb-md-0">
                                            <span class="text-sm fw-bold text-dark"><i class="fas fa-chart-pie me-1 text-primary"></i> Rekap Soal:</span>
                                            <span class="ms-3 text-sm text-secondary">Total Soal: <strong id="summary-total-soal" class="text-primary text-lg">0</strong></span>
                                            <span class="ms-3 text-sm text-secondary">Total Poin: <strong id="summary-total-poin" class="text-info text-lg">0</strong> <span id="poin-warning" class="text-danger text-xs d-none">(Melebihi 100!)</span></span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary mb-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportSoalExcel">
                                                <i class="fas fa-file-excel me-1"></i> Import Soal Excel
                                            </button>
                                            <button type="button" class="btn btn-sm bg-gradient-info mb-0 shadow-sm" id="btn-sesuaikan-poin" title="Bagikan bobot poin secara merata dengan total 100">
                                                <i class="fas fa-magic me-1"></i> Sesuaikan Poin (Otomatis)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 2: PENYUSUNAN SOAL --}}
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mt-4 mb-3">
                                <h6 class="mb-0 text-info text-sm"><i class="fas fa-list-ol me-1"></i> Bagian 2: Daftar Soal</h6>
                                <span class="text-xs text-secondary"><i class="fas fa-info-circle"></i> Tahan & geser icon grid atau gunakan tombol panah untuk memindah posisi.</span>
                            </div>
                            
                            <div class="bg-gray-50 rounded p-2 border" id="soal-container" style="min-height: 200px;">
                                {{-- Container untuk list soal dinamis --}}
                            </div>
                            
                            <div class="text-center mt-3 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-sm mb-0 px-4 shadow-sm" onclick="addSoal(null, 'end')">
                                    <i class="fas fa-plus me-1"></i> Tambah Soal Baru di Akhir
                                </button>
                            </div>

                        </div>

                        {{-- TOMBOL SUBMIT UTAMA (EXPORT DI KIRI, BATAL & SIMPAN DI KANAN) --}}
                        <div class="card-footer bg-light border-top py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <button type="button" id="btn-export-soal-excel" class="btn btn-sm btn-export-excel-ungu mb-0 shadow-sm" title="Export daftar soal saat ini ke file Excel (.xlsx) sesuai format template import">
                                    <i class="fas fa-file-export me-1"></i> Export Soal Excel
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('sba.index') }}" class="btn btn-outline-secondary btn-sm mb-0">Batal</a>
                                <button type="submit" class="btn bg-gradient-primary btn-sm mb-0 shadow-sm btn-submit-sba">
                                    <i class="fas fa-save me-1"></i> {{ isset($uji) ? 'Simpan Perubahan' : 'Simpan Asesmen' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <x-app.footer />
</main>

{{-- MODAL PERINGATAN TOTAL BOBOT NILAI TIDAK SAMA DENGAN 100 --}}
<div class="modal fade" id="modalPeringatanBobot" tabindex="-1" aria-labelledby="modalPeringatanBobotLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-gradient-warning border-0 py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0" id="modalPeringatanBobotLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i> Konfirmasi Total Bobot Nilai
                </h6>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-white text-start">
                <p class="text-dark text-sm mb-3" style="line-height: 1.5;">
                    Total bobot nilai seluruh soal saat ini <strong class="text-danger">tidak berjumlah 100 poin</strong>. Silakan periksa ringkasan penyusunan soal berikut sebelum menyimpan:
                </p>

                <div class="row g-2 mb-3 text-center">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <span class="d-block text-secondary text-uppercase" style="font-size: 0.7rem; font-weight: 700;">Jumlah Soal</span>
                            <strong class="fs-4 text-primary" id="modal-bobot-jumlah-soal">0</strong>
                            <span class="d-block text-xxs text-secondary">Butir Soal</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <span class="d-block text-secondary text-uppercase" style="font-size: 0.7rem; font-weight: 700;">Total Bobot Nilai</span>
                            <strong class="fs-4 text-danger" id="modal-bobot-total-poin">0</strong>
                            <span class="d-block text-xxs text-secondary">Target Standar: <strong>100 Poin</strong></span>
                        </div>
                    </div>
                </div>

                <div class="p-2 px-3 mb-0 rounded border-start border-4 border-warning bg-light text-dark text-xs" id="modal-bobot-keterangan">
                </div>
            </div>

            <div class="modal-footer border-top bg-light py-2 px-4 d-flex justify-content-end align-items-center flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary fw-bold px-3 mb-0" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" id="btn-modal-tetap-simpan" class="btn btn-sm bg-gradient-info text-white fw-bold px-3 shadow-sm mb-0">
                    <i class="fas fa-save me-1"></i> Tetap Simpan
                </button>
                <button type="button" id="btn-modal-sesuaikan-bobot" class="btn btn-sm bg-gradient-primary text-white fw-bold px-3 shadow-sm mb-0">
                    <i class="fas fa-magic me-1"></i> Sesuaikan Bobot dan Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL IMPORT SOAL EXCEL (SELARAS DENGAN TEMA SBA) --}}
<div class="modal fade" id="modalImportSoalExcel" tabindex="-1" aria-labelledby="modalImportSoalExcelLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-gradient-primary border-0 py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0" id="modalImportSoalExcelLabel">
                    <i class="fas fa-file-excel me-2"></i> Import Soal dari Excel
                </h6>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-white text-start">
                <div id="import-excel-error-box" class="alert bg-gradient-danger text-white small py-2 px-3 mb-4 rounded-3 border-0 d-none">
                    <strong class="d-block mb-1" id="import-excel-error-title"></strong>
                    <ul class="mb-0 ps-3" id="import-excel-error-list"></ul>
                </div>

                {{-- 1. BAGIAN PERTAMA: UNDUH FORMAT TEMPLATE --}}
                <div class="mb-4 p-3 bg-light rounded border">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-gradient-primary me-2">1</span>
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">Unduh Format Template</h6>
                    </div>
                    <p class="text-secondary text-xs mb-2" style="line-height: 1.5;">
                        Gunakan file template resmi dengan header kolom wajib:<br>
                        <code class="text-primary fw-bold">no, tipe_soal, pertanyaan, opsi_a..e, kunci_jawaban, bobot_nilai</code><br>
                        <span class="text-xxs text-muted d-block mt-1">
                            • Untuk <strong>benar_salah</strong>: isi pernyataan pada kolom berawalan <code>opsi_</code> (bisa menambah kolom baru seperti <code>opsi_6</code>, <code>opsi_7</code>, dst. sesuai jumlah pernyataan), kunci dipisah koma (misal <code>B,S,B</code>), dan bobot dipisah koma (misal <code>10,10,20</code>) atau total (misal <code>40</code>).
                        </span>
                    </p>
                    <a href="{{ route('sba.soal.template') }}" class="btn btn-sm bg-gradient-info text-white fw-bold w-100 mb-0 shadow-xs">
                        <i class="fas fa-download me-1"></i> Download Template Excel (.xlsx)
                    </a>
                </div>

                {{-- 2. BAGIAN KEDUA: UNGGAH FILE TEMPLATE (AJAX TO VIEW) --}}
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-gradient-primary me-2">2</span>
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">Unggah File Template</h6>
                    </div>

                    <div class="p-2 px-3 mb-3 rounded border-start border-4 border-info bg-light text-dark text-xs">
                        <i class="fas fa-info-circle text-info me-1"></i>
                        Hasil import akan dimuat ke form terlebih dahulu (bersifat sementara) agar dapat Anda tinjau sebelum menekan tombol <strong>Simpan Asesmen</strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary text-uppercase mb-1" style="font-size: 0.7rem; font-weight: 700;">Mode Penempatan Soal di Form</label>
                        <div class="d-flex flex-column gap-2 p-2 px-3 bg-light rounded border">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="import_mode" id="modeReplace" value="replace" checked>
                                <label class="form-check-label text-xs fw-bold text-dark cursor-pointer mb-0" for="modeReplace">
                                    Ganti / Timpa semua soal yang ada di form saat ini
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="import_mode" id="modeAppend" value="append">
                                <label class="form-check-label text-xs fw-bold text-dark cursor-pointer mb-0" for="modeAppend">
                                    Tambahkan di urutan bawah soal yang sudah ada
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="input_excel_soal" class="form-label text-secondary text-uppercase mb-1" style="font-size: 0.7rem; font-weight: 700;">Pilih File Excel (.xlsx / .xls / .csv)</label>
                        <input type="file" id="input_excel_soal" class="form-control form-control-sm border px-2 py-1" accept=".xlsx, .xls, .csv">
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-top bg-light py-2 px-4 gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary fw-bold px-3 mb-0" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btn-proses-import-excel" class="btn btn-sm bg-gradient-primary text-white px-4 fw-bold shadow-sm mb-0">
                    <i class="fas fa-cloud-upload-alt me-1"></i> Muat Soal ke Form
                </button>
            </div>
        </div>
    </div>
</div>

{{-- TEMPLATE SOAL (Hidden) --}}
<template id="soal-template">
    <div class="soal-item card mb-3 shadow-none border border-light" draggable="false" style="transition: transform 0.2s, border-color 0.2s;">
        <input type="hidden" class="soal-id" value="">
        
        <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
            <div class="cursor-pointer drag-handle px-1 text-secondary d-flex align-items-center" title="Tahan dan geser untuk memindahkan urutan" style="cursor: grab;">
                <i class="fas fa-grip-vertical me-2"></i>
                <span class="text-sm font-weight-bold text-dark mb-0">Soal #<span class="soal-number">1</span></span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm mb-0 px-2 py-1 btn-move-up text-xs shadow-sm me-1" title="Geser ke Atas">
                    <i class="fas fa-chevron-up"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-0 px-2 py-1 btn-move-down text-xs shadow-sm me-2" title="Geser ke Bawah">
                    <i class="fas fa-chevron-down"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm mb-0 px-2 py-1 btn-remove-soal text-xs shadow-sm" title="Hapus Soal Ini">
                    <i class="fas fa-trash-alt me-1"></i> Hapus
                </button>
            </div>
        </div>
        
        <div class="card-body p-3">
            <div class="row g-3 align-items-start">
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="form-control-label text-xs mb-1">Tipe Soal</label>
                        <select class="form-select form-select-sm border px-2 py-1 soal-tipe" required>
                            <option value="pg">Pilihan Ganda</option>
                            <option value="pg_kompleks">PG Kompleks (Banyak Jawaban)</option>
                            <option value="benar_salah">Pernyataan Benar Salah</option>
                            <option value="esai">Uraian / Isian</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-control-label text-xs mb-1 label-bobot-utama">Bobot (Poin)</label>
                        <input type="number" class="form-control form-control-sm border px-2 py-1 soal-bobot" value="10" min="0" required>
                        <small class="text-xxs text-info d-none info-bobot-bs mt-1" style="line-height: 1.25;">
                            *Otomatis dijumlahkan dari bobot tiap pernyataan di bawah.
                        </small>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <div class="form-group mb-2">
                        <label class="form-control-label text-xs mb-1 label-pertanyaan-utama">Pertanyaan <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm border px-2 py-1 soal-pertanyaan" rows="4" placeholder="Ketikkan teks pertanyaan di sini..." required style="resize: vertical; min-height: 90px; max-height: 250px;"></textarea>
                    </div>

                    {{-- SLOT INPUT GAMBAR SOAL & PREVIEW --}}
                    <div class="form-group mb-0 border-top pt-2 mt-2">
                        <label class="form-control-label text-xs mb-1 text-dark">
                            <i class="fas fa-image me-1 text-primary"></i> Lampiran Gambar Soal (Opsional)
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="file" class="form-control form-control-sm border px-2 py-1 soal-media" accept="image/*">
                            <input type="hidden" class="soal-remove-media" value="0">
                        </div>
                        <small class="text-xxs text-secondary d-block mt-1">*Format: JPG, JPEG, PNG, WEBP (Maksimal 2MB)</small>

                        <div class="media-preview-container mt-2 position-relative d-none" style="max-width: 250px;">
                            <img src="" class="img-thumbnail rounded media-preview-img shadow-xs" style="max-height: 140px; width: auto;" alt="Preview Soal">
                            <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-1 px-2 py-1 btn-delete-media" title="Hapus Gambar Ini">
                                <i class="fas fa-times me-1"></i> Hapus Gambar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CONTAINER OPSI JAWABAN (PG & PG KOMPLEKS + GAMBAR PER OPSI) --}}
            <div class="opsi-wrapper border-top pt-3 mt-3">
                <label class="form-control-label text-xs mb-2 text-primary d-block">
                    <i class="fas fa-check-circle me-1"></i> Pilihan Jawaban, Lampiran Gambar Opsi & Kunci 
                    <span class="text-secondary fw-normal ms-1">(Klik bulatan "KUNCI" untuk menandai jawaban yang benar)</span>
                </label>
                
                <div class="opsi-container row gx-2">
                    @foreach(['A', 'B', 'C', 'D', 'E'] as $index => $huruf)
                    <div class="col-md-12 mb-2 opsi-item" data-opsi-index="{{ $index }}">
                        <div class="d-flex align-items-stretch">
                            <div class="bg-light border border-secondary rounded-start d-flex flex-column align-items-center justify-content-center px-2 py-1" style="min-width: 60px;">
                                <small class="text-xxs font-weight-bolder text-secondary mb-1" style="line-height: 1;">KUNCI</small>
                                <input type="radio" class="form-check-input kunci-radio border-secondary m-0 bg-white" value="{{ $index }}" style="width: 1.25rem; height: 1.25rem; cursor:pointer;" title="Jadikan Jawaban Benar">
                                <input type="checkbox" class="form-check-input kunci-checkbox border-secondary m-0 bg-white d-none" value="{{ $index }}" style="width: 1.25rem; height: 1.25rem; cursor:pointer;" title="Jadikan Jawaban Benar">
                            </div>
                            
                            <div class="bg-secondary text-white border-top border-bottom border-secondary d-flex align-items-center px-3 font-weight-bold">
                                {{ $huruf }}
                            </div>

                            <div class="flex-grow-1 border border-secondary border-start-0 rounded-end bg-white p-2">
                                <div class="row g-2 align-items-start">
                                    {{-- KOLOM KIRI: ISIAN KETIKAN OPSI --}}
                                    <div class="col-12 col-md-7">
                                        <textarea class="form-control form-control-sm border px-2 py-1 opsi-teks" rows="2" placeholder="Masukkan teks untuk opsi {{ $huruf }} (atau kosongkan jika hanya memakai gambar)..." style="resize: vertical; min-height: 58px;"></textarea>
                                    </div>

                                    {{-- KOLOM KANAN: INSERT GAMBAR OPSI & PREVIEW --}}
                                    <div class="col-12 col-md-5">
                                        <input type="file" class="form-control form-control-sm border px-2 py-1 opsi-media-input" accept="image/*">
                                        <input type="hidden" class="opsi-remove-media" value="0">
                                        <small class="text-xxs text-muted d-block mt-1">Gambar opsi (Opsional: JPG, PNG, WEBP maks. 2MB)</small>

                                        <div class="opsi-media-preview-container mt-2 position-relative d-inline-block d-none">
                                            <img src="" class="img-thumbnail rounded opsi-media-preview-img shadow-xs" style="max-height: 95px; width: auto;" alt="Preview Opsi {{ $huruf }}">
                                            <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-1 px-2 py-1 btn-delete-opsi-media" title="Hapus Gambar Opsi {{ $huruf }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- CONTAINER PERNYATAAN BENAR / SALAH (DINAMIS) --}}
            <div class="bs-wrapper border-top pt-3 mt-3 d-none">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <label class="form-control-label text-xs mb-0 text-primary d-block">
                            <i class="fas fa-check-double me-1"></i> Daftar Pernyataan, Kunci & Bobot per Pernyataan
                        </label>
                        <small class="text-xxs text-secondary">Pilih kunci <strong>Benar</strong> atau <strong>Salah</strong> dan tentukan bobot poin pada masing-masing pernyataan.</small>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-primary mb-0 btn-add-bs-row">
                        <i class="fas fa-plus me-1"></i> Tambah Pernyataan
                    </button>
                </div>

                <div class="bs-rows-container d-flex flex-column gap-2">
                    {{-- Baris pernyataan Benar/Salah dirender dinamis di sini --}}
                </div>
            </div>
        </div>

        <div class="card-footer bg-light py-1 text-center border-top">
            <button type="button" class="btn btn-sm btn-link text-primary mb-0 shadow-none btn-insert-soal text-xs py-1">
                <i class="fas fa-plus-circle me-1"></i> Sisipkan Soal Baru di Bawah Ini
            </button>
        </div>
    </div>
</template>

{{-- TEMPLATE SATU BARIS PERNYATAAN BENAR / SALAH --}}
<template id="bs-row-template">
    <div class="bs-row p-2 bg-light border rounded">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <span class="badge bg-secondary bs-row-num">1</span>
            </div>
            <div class="col">
                <textarea class="form-control form-control-sm border bg-white px-2 py-1 bs-teks" rows="1" placeholder="Tuliskan teks pernyataan..." style="resize: vertical;"></textarea>
            </div>
            <div class="col-md-auto">
                <div class="d-flex align-items-center gap-2 bg-white border rounded px-2 py-1">
                    <small class="text-xxs fw-bold text-secondary me-1">KUNCI:</small>
                    <div class="form-check form-check-inline mb-0 me-1">
                        <input class="form-check-input bs-kunci-benar" type="radio" value="benar" style="cursor: pointer;">
                        <label class="form-check-label text-xs fw-bold text-success mb-0" style="cursor: pointer;">Benar</label>
                    </div>
                    <div class="form-check form-check-inline mb-0 me-0">
                        <input class="form-check-input bs-kunci-salah" type="radio" value="salah" style="cursor: pointer;">
                        <label class="form-check-label text-xs fw-bold text-danger mb-0" style="cursor: pointer;">Salah</label>
                    </div>
                </div>
            </div>
            <div class="col-md-auto" style="width: 120px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text text-xxs bg-white px-2">Poin</span>
                    <input type="number" class="form-control form-control-sm border bg-white text-center fw-bold px-1 bs-bobot" value="10" min="0">
                </div>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-danger btn-xs mb-0 px-2 py-1 btn-remove-bs-row" title="Hapus Pernyataan Ini">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<style>
    .soal-item.dragging {
        opacity: 0.5;
        border: 2px dashed #0b57d0 !important;
        transform: scale(0.99);
    }
    .soal-item.drag-over {
        border-top: 3px solid #0b57d0 !important;
        margin-top: 0.5rem;
    }
    .kunci-radio:checked, .kunci-checkbox:checked {
        background-color: #17ad37;
        border-color: #17ad37;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    /* ========================================================================
     * VALIDASI, GENERATOR & CEK REAL-TIME KODE ASESMEN (A-Z, 0-9, MAX 12)
     * ======================================================================== */
    const inputKodeUji = document.getElementById('kode_uji');
    const btnGenerateKode = document.getElementById('btn-generate-kode');
    const kodeStatusEl = document.getElementById('kode-uji-status');
    const ignoreUjiId = @json(isset($uji) ? $uji->id : null);
    let checkKodeTimer = null;

    function cekKetersediaanKode(kode) {
        if (!kodeStatusEl || !inputKodeUji) return;

        if (!kode) {
            kodeStatusEl.classList.add('d-none');
            inputKodeUji.classList.remove('is-invalid', 'is-valid');
            return;
        }

        const params = new URLSearchParams({ kode: kode });
        if (ignoreUjiId) {
            params.append('ignore_id', ignoreUjiId);
        }

        fetch(`{{ route('sba.check_kode') }}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            kodeStatusEl.classList.remove('d-none', 'text-success', 'text-danger');
            if (data.available) {
                kodeStatusEl.classList.add('text-success');
                kodeStatusEl.innerHTML = `<i class="fas fa-check-circle me-1"></i> ${data.message}`;
                inputKodeUji.classList.remove('is-invalid');
            } else {
                kodeStatusEl.classList.add('text-danger');
                kodeStatusEl.innerHTML = `<i class="fas fa-times-circle me-1"></i> ${data.message}`;
                inputKodeUji.classList.add('is-invalid');
            }
        })
        .catch(() => {});
    }

    if (inputKodeUji) {
        inputKodeUji.addEventListener('input', function() {
            this.value = this.value
                .toUpperCase()
                .replace(/[^A-Z0-9]/g, '')
                .slice(0, 12);

            clearTimeout(checkKodeTimer);
            const val = this.value;
            checkKodeTimer = setTimeout(() => cekKetersediaanKode(val), 350);
        });
    }

    if (btnGenerateKode && inputKodeUji) {
        btnGenerateKode.addEventListener('click', function() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let randomCode = '';
            for (let i = 0; i < 6; i++) {
                randomCode += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            inputKodeUji.value = randomCode;
            cekKetersediaanKode(randomCode);
        });
    }

    /* ========================================================================
     * MANAJEMEN SOAL DINAMIS, PERNYATAAN BENAR/SALAH, DRAG-AND-DROP & MEDIA
     * ======================================================================== */
    const container = document.getElementById('soal-container');
    const template = document.getElementById('soal-template');
    const bsRowTemplate = document.getElementById('bs-row-template');
    let draggedItem = null;

    function syncOpsiRequiredState(soalItem) {
        const tipe = soalItem.querySelector('.soal-tipe').value;
        const opsiItems = soalItem.querySelectorAll('.opsi-item');

        if (!['pg', 'pg_kompleks'].includes(tipe)) {
            opsiItems.forEach(optEl => {
                optEl.querySelector('.opsi-teks')?.removeAttribute('required');
            });
            return;
        }

        opsiItems.forEach((optEl, idx) => {
            const ta = optEl.querySelector('.opsi-teks');
            const previewContainer = optEl.querySelector('.opsi-media-preview-container');
            const hasImage = previewContainer && !previewContainer.classList.contains('d-none');

            if (idx < 2 && !hasImage) {
                ta.setAttribute('required', 'true');
            } else {
                ta.removeAttribute('required');
            }
        });
    }

    function recalcBobotBenarSalah(soalItem) {
        if (!soalItem || soalItem.querySelector('.soal-tipe').value !== 'benar_salah') return;
        let total = 0;
        soalItem.querySelectorAll('.bs-bobot').forEach(inp => {
            total += parseInt(inp.value, 10) || 0;
        });
        soalItem.querySelector('.soal-bobot').value = total;
        updateSummary();
    }

    function addBsRow(soalItem, rowData = null) {
        const rowsContainer = soalItem.querySelector('.bs-rows-container');
        const clone = bsRowTemplate.content.cloneNode(true);
        const rowEl = clone.querySelector('.bs-row');

        const teksInput = rowEl.querySelector('.bs-teks');
        const radioBenar = rowEl.querySelector('.bs-kunci-benar');
        const radioSalah = rowEl.querySelector('.bs-kunci-salah');
        const bobotInput = rowEl.querySelector('.bs-bobot');
        const btnRemove = rowEl.querySelector('.btn-remove-bs-row');

        if (rowData) {
            teksInput.value = rowData.teks || '';
            bobotInput.value = (rowData.bobot !== undefined && rowData.bobot !== null) ? rowData.bobot : 10;
            if (rowData.kunci === 'benar') {
                radioBenar.checked = true;
            } else if (rowData.kunci === 'salah') {
                radioSalah.checked = true;
            }
        }

        bobotInput.addEventListener('input', () => recalcBobotBenarSalah(soalItem));

        btnRemove.addEventListener('click', function() {
            if (rowsContainer.querySelectorAll('.bs-row').length > 1) {
                rowEl.remove();
                updateSoalNames();
                recalcBobotBenarSalah(soalItem);
            }
        });

        rowsContainer.appendChild(rowEl);
        updateSoalNames();
        recalcBobotBenarSalah(soalItem);
    }

    const existingSoals = @json(isset($uji) ? $uji->soals : []);
    
    if (existingSoals.length > 0) {
        existingSoals.forEach(soal => addSoal(null, 'end', soal));
    } else {
        addSoal(); 
    }

    function addSoal(referenceElement = null, position = 'after', data = null) {
        const clone = template.content.cloneNode(true);
        const soalItem = clone.querySelector('.soal-item');
        
        setupSoalEvents(soalItem);

        if (data) {
            if(data.id) {
                soalItem.querySelector('.soal-id').value = data.id;
            }
            soalItem.querySelector('.soal-tipe').value = data.tipe;
            soalItem.querySelector('.soal-bobot').value = data.bobot_nilai || 10;
            soalItem.querySelector('.soal-pertanyaan').value = data.pertanyaan;
            
            if (data.media) {
                const previewContainer = soalItem.querySelector('.media-preview-container');
                const previewImg = soalItem.querySelector('.media-preview-img');
                previewImg.src = `{{ asset('storage') }}/${data.media}`;
                previewContainer.classList.remove('d-none');
            }

            if (data.tipe === 'benar_salah') {
                const opsiList = Array.isArray(data.opsi_jawaban) ? data.opsi_jawaban : [];
                const kunciList = Array.isArray(data.kunci_jawaban) ? data.kunci_jawaban : [];

                if (opsiList.length > 0) {
                    opsiList.forEach((item, idx) => {
                        addBsRow(soalItem, {
                            teks: item.teks ?? '',
                            bobot: item.bobot ?? 10,
                            kunci: kunciList[idx] ?? ''
                        });
                    });
                } else {
                    addBsRow(soalItem);
                    addBsRow(soalItem);
                }
            } else if (['pg', 'pg_kompleks'].includes(data.tipe)) {
                const opsiItems = soalItem.querySelectorAll('.opsi-item');
                const kunciRadios = soalItem.querySelectorAll('.kunci-radio');
                const kunciCheckboxes = soalItem.querySelectorAll('.kunci-checkbox');
                
                let opsiData = Array.isArray(data.opsi_jawaban) ? data.opsi_jawaban : [];
                let opsiMediaData = Array.isArray(data.opsi_media) ? data.opsi_media : [];
                let kunciData = Array.isArray(data.kunci_jawaban) ? data.kunci_jawaban : [];
                
                opsiItems.forEach((optEl, i) => {
                    const ta = optEl.querySelector('.opsi-teks');
                    if (opsiData[i]) ta.value = opsiData[i];

                    if (opsiMediaData[i]) {
                        const prevContainer = optEl.querySelector('.opsi-media-preview-container');
                        const prevImg = optEl.querySelector('.opsi-media-preview-img');
                        prevImg.src = `{{ asset('storage') }}/${opsiMediaData[i]}`;
                        prevContainer.classList.remove('d-none');
                    }
                });
                
                if (data.tipe === 'pg') {
                    kunciRadios.forEach((r, i) => {
                        if (kunciData.includes(String(i)) || kunciData.includes(i)) r.checked = true;
                    });
                } else {
                    kunciCheckboxes.forEach((c, i) => {
                        if (kunciData.includes(String(i)) || kunciData.includes(i)) c.checked = true;
                    });
                }
            }
        }

        toggleOpsiVisibility(soalItem);

        if (referenceElement) {
            if (position === 'after') referenceElement.after(soalItem);
            else referenceElement.before(soalItem);
        } else {
            container.appendChild(soalItem);
        }
        
        updateSoalNames();
        
        if(!data && referenceElement) {
            soalItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        updateSummary();
    }

    window.addSoal = addSoal;

    function setupSoalEvents(item) {
        item.querySelector('.btn-remove-soal').addEventListener('click', function() {
            if (document.querySelectorAll('.soal-item').length > 1) {
                const soalId = item.querySelector('.soal-id').value;
                if(soalId) {
                    fetch(`{{ url('admin/sba/soal') }}/${soalId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                }
                item.remove();
                updateSoalNames();
                updateSummary();
            }
        });

        item.querySelector('.btn-add-bs-row').addEventListener('click', function() {
            addBsRow(item);
        });

        // Media Gambar Utama Soal
        const fileInput = item.querySelector('.soal-media');
        const previewContainer = item.querySelector('.media-preview-container');
        const previewImg = item.querySelector('.media-preview-img');
        const removeMediaInput = item.querySelector('.soal-remove-media');
        const btnDeleteMedia = item.querySelector('.btn-delete-media');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                    removeMediaInput.value = "0";
                }
                reader.readAsDataURL(file);
            }
        });

        btnDeleteMedia.addEventListener('click', function() {
            fileInput.value = "";
            previewImg.src = "";
            previewContainer.classList.add('d-none');
            removeMediaInput.value = "1";
        });

        // Media Gambar Per Pilihan Jawaban (Opsi A - E)
        item.querySelectorAll('.opsi-item').forEach(optEl => {
            const optFileInput = optEl.querySelector('.opsi-media-input');
            const optPreviewContainer = optEl.querySelector('.opsi-media-preview-container');
            const optPreviewImg = optEl.querySelector('.opsi-media-preview-img');
            const optRemoveInput = optEl.querySelector('.opsi-remove-media');
            const btnDeleteOptMedia = optEl.querySelector('.btn-delete-opsi-media');

            optFileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        optPreviewImg.src = e.target.result;
                        optPreviewContainer.classList.remove('d-none');
                        optRemoveInput.value = "0";
                        syncOpsiRequiredState(item);
                    };
                    reader.readAsDataURL(file);
                }
            });

            btnDeleteOptMedia.addEventListener('click', function() {
                optFileInput.value = "";
                optPreviewImg.src = "";
                optPreviewContainer.classList.add('d-none');
                optRemoveInput.value = "1";
                syncOpsiRequiredState(item);
            });
        });

        item.querySelector('.btn-move-up').addEventListener('click', function() {
            const prev = item.previousElementSibling;
            if (prev && prev.classList.contains('soal-item')) {
                container.insertBefore(item, prev);
                updateSoalNames();
                item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });

        item.querySelector('.btn-move-down').addEventListener('click', function() {
            const next = item.nextElementSibling;
            if (next && next.classList.contains('soal-item')) {
                container.insertBefore(next, item);
                updateSoalNames();
                item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });

        item.querySelector('.btn-insert-soal').addEventListener('click', function() {
            addSoal(item, 'after');
        });

        item.querySelector('.soal-tipe').addEventListener('change', function() {
            toggleOpsiVisibility(item);
            updateSummary();
        });

        item.querySelector('.soal-bobot').addEventListener('input', updateSummary);

        // DRAG & DROP EVENTS
        const handle = item.querySelector('.drag-handle');
        handle.addEventListener('mousedown', () => item.setAttribute('draggable', 'true'));
        handle.addEventListener('mouseup', () => item.setAttribute('draggable', 'false'));
        handle.addEventListener('mouseleave', () => item.setAttribute('draggable', 'false'));

        item.addEventListener('dragstart', function(e) {
            draggedItem = item;
            e.dataTransfer.effectAllowed = 'move';
            setTimeout(() => item.classList.add('dragging'), 0);
        });

        item.addEventListener('dragend', function() {
            draggedItem.classList.remove('dragging');
            draggedItem = null;
            item.setAttribute('draggable', 'false');
            document.querySelectorAll('.soal-item').forEach(el => el.classList.remove('drag-over'));
            updateSoalNames(); 
        });

        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if(draggedItem !== item) {
                item.classList.add('drag-over');
            }
            const afterElement = getDragAfterElement(container, e.clientY);
            if (afterElement == null) {
                container.appendChild(draggedItem);
            } else {
                container.insertBefore(draggedItem, afterElement);
            }
        });

        item.addEventListener('dragleave', function() {
            item.classList.remove('drag-over');
        });
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.soal-item:not(.dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function toggleOpsiVisibility(item) {
        const tipe = item.querySelector('.soal-tipe').value;
        const opsiWrapper = item.querySelector('.opsi-wrapper');
        const bsWrapper = item.querySelector('.bs-wrapper');
        const radios = item.querySelectorAll('.kunci-radio');
        const checkboxes = item.querySelectorAll('.kunci-checkbox');
        const opsiTexts = item.querySelectorAll('.opsi-teks');
        const bobotUtamaInput = item.querySelector('.soal-bobot');
        const labelBobotUtama = item.querySelector('.label-bobot-utama');
        const infoBobotBs = item.querySelector('.info-bobot-bs');
        const labelPertanyaan = item.querySelector('.label-pertanyaan-utama');
        const textareaPertanyaan = item.querySelector('.soal-pertanyaan');

        // Reset atribut BS terlebih dahulu
        item.querySelectorAll('.bs-teks').forEach(el => el.removeAttribute('required'));
        item.querySelectorAll('.bs-kunci-benar').forEach(el => el.removeAttribute('required'));

        if (tipe === 'benar_salah') {
            opsiWrapper.classList.add('d-none');
            bsWrapper.classList.remove('d-none');

            opsiTexts.forEach(el => el.removeAttribute('required'));
            radios.forEach(el => el.removeAttribute('required'));
            checkboxes.forEach(el => el.removeAttribute('required'));

            bobotUtamaInput.readOnly = true;
            bobotUtamaInput.classList.add('bg-light');
            labelBobotUtama.innerText = 'Total Bobot Soal';
            infoBobotBs.classList.remove('d-none');
            labelPertanyaan.innerHTML = 'Penjelasan / Paragraf / Cerita Stimulus <span class="text-danger">*</span>';
            textareaPertanyaan.placeholder = 'Ketikkan penjelasan, cerita, atau paragraf bacaan sebelum daftar pernyataan...';

            if (item.querySelectorAll('.bs-row').length === 0) {
                addBsRow(item);
                addBsRow(item);
            }

            item.querySelectorAll('.bs-teks').forEach(el => el.setAttribute('required', 'true'));
            item.querySelectorAll('.bs-kunci-benar').forEach(el => el.setAttribute('required', 'true'));

            recalcBobotBenarSalah(item);
        } else if (tipe === 'esai') {
            opsiWrapper.classList.add('d-none');
            bsWrapper.classList.add('d-none');

            opsiTexts.forEach(el => el.removeAttribute('required'));
            radios.forEach(el => el.removeAttribute('required'));
            checkboxes.forEach(el => el.removeAttribute('required'));

            bobotUtamaInput.readOnly = false;
            bobotUtamaInput.classList.remove('bg-light');
            labelBobotUtama.innerText = 'Bobot (Poin)';
            infoBobotBs.classList.add('d-none');
            labelPertanyaan.innerHTML = 'Pertanyaan <span class="text-danger">*</span>';
            textareaPertanyaan.placeholder = 'Ketikkan teks pertanyaan di sini...';
        } else {
            opsiWrapper.classList.remove('d-none');
            bsWrapper.classList.add('d-none');

            bobotUtamaInput.readOnly = false;
            bobotUtamaInput.classList.remove('bg-light');
            labelBobotUtama.innerText = 'Bobot (Poin)';
            infoBobotBs.classList.add('d-none');
            labelPertanyaan.innerHTML = 'Pertanyaan <span class="text-danger">*</span>';
            textareaPertanyaan.placeholder = 'Ketikkan teks pertanyaan di sini...';

            syncOpsiRequiredState(item);

            if (tipe === 'pg') {
                radios.forEach(el => { el.classList.remove('d-none'); el.setAttribute('required', 'true'); });
                checkboxes.forEach(el => { el.classList.add('d-none'); el.removeAttribute('required'); });
                checkboxes.forEach(el => el.checked = false); 
            } else if (tipe === 'pg_kompleks') {
                checkboxes.forEach(el => { el.classList.remove('d-none'); }); 
                radios.forEach(el => { el.classList.add('d-none'); el.removeAttribute('required'); });
                radios.forEach(el => el.checked = false);
            }
        }
        updateSoalNames();
    }

    function updateSoalNames() {
        document.querySelectorAll('.soal-item').forEach((item, index) => {
            item.querySelector('.soal-number').innerText = index + 1;
            
            const soalIdEl = item.querySelector('.soal-id');
            if(soalIdEl) soalIdEl.name = `soals[${index}][id]`;

            item.querySelector('.soal-tipe').name = `soals[${index}][tipe]`;
            item.querySelector('.soal-bobot').name = `soals[${index}][bobot_nilai]`;
            item.querySelector('.soal-pertanyaan').name = `soals[${index}][pertanyaan]`;
            
            item.querySelector('.soal-media').name = `soals[${index}][media]`;
            item.querySelector('.soal-remove-media').name = `soals[${index}][remove_media]`;
            
            item.querySelectorAll('.opsi-item').forEach((optEl, optIdx) => {
                optEl.querySelector('.opsi-teks').name = `soals[${index}][opsi_jawaban][${optIdx}]`;
                optEl.querySelector('.opsi-media-input').name = `soals[${index}][opsi_media][${optIdx}]`;
                optEl.querySelector('.opsi-remove-media').name = `soals[${index}][remove_opsi_media][${optIdx}]`;
            });
            item.querySelectorAll('.kunci-radio').forEach(el => el.name = `soals[${index}][kunci_jawaban][]`);
            item.querySelectorAll('.kunci-checkbox').forEach(el => el.name = `soals[${index}][kunci_jawaban][]`);

            // Penamaan atribut pernyataan Benar/Salah
            item.querySelectorAll('.bs-row').forEach((row, pIdx) => {
                row.querySelector('.bs-row-num').innerText = pIdx + 1;
                row.querySelector('.bs-teks').name = `soals[${index}][pernyataan_bs][${pIdx}][teks]`;
                row.querySelector('.bs-bobot').name = `soals[${index}][pernyataan_bs][${pIdx}][bobot]`;

                const radioBenar = row.querySelector('.bs-kunci-benar');
                const radioSalah = row.querySelector('.bs-kunci-salah');
                const labelBenar = radioBenar.nextElementSibling;
                const labelSalah = radioSalah.nextElementSibling;

                const radioName = `soals[${index}][pernyataan_bs][${pIdx}][kunci]`;
                const idBenar = `bs_benar_${index}_${pIdx}`;
                const idSalah = `bs_salah_${index}_${pIdx}`;

                radioBenar.name = radioName;
                radioBenar.id = idBenar;
                labelBenar.setAttribute('for', idBenar);

                radioSalah.name = radioName;
                radioSalah.id = idSalah;
                labelSalah.setAttribute('for', idSalah);
            });
        });
    }

    function hitungStatistikSoal() {
        const bobotInputs = document.querySelectorAll('.soal-bobot');
        let totalPoin = 0;
        bobotInputs.forEach(input => {
            totalPoin += parseInt(input.value) || 0;
        });
        return {
            jumlahSoal: bobotInputs.length,
            totalPoin: totalPoin
        };
    }

    function updateSummary() {
        const { jumlahSoal, totalPoin } = hitungStatistikSoal();
        document.getElementById('summary-total-soal').innerText = jumlahSoal;
        
        const poinEl = document.getElementById('summary-total-poin');
        const warningEl = document.getElementById('poin-warning');
        
        poinEl.innerText = totalPoin;
        
        if (totalPoin > 100) {
            poinEl.classList.remove('text-info', 'text-success');
            poinEl.classList.add('text-danger');
            warningEl.classList.remove('d-none');
        } else if (totalPoin === 100) {
            poinEl.classList.remove('text-info', 'text-danger');
            poinEl.classList.add('text-success');
            warningEl.classList.add('d-none');
        } else {
            poinEl.classList.remove('text-success', 'text-danger');
            poinEl.classList.add('text-info');
            warningEl.classList.add('d-none');
        }
    }

    function sesuaikanBobotOtomatis() {
        const soalItems = document.querySelectorAll('.soal-item');
        const n = soalItems.length;
        if (n === 0) return;

        // Hitung total unit penilaian (1 unit per soal biasa, 1 unit per pernyataan pada soal benar_salah)
        let totalUnits = 0;
        soalItems.forEach(item => {
            const tipe = item.querySelector('.soal-tipe').value;
            if (tipe === 'benar_salah') {
                totalUnits += Math.max(1, item.querySelectorAll('.bs-bobot').length);
            } else {
                totalUnits += 1;
            }
        });

        const basePoin = Math.floor(100 / totalUnits);
        const remainder = 100 % totalUnits;
        let currentUnit = 0;

        soalItems.forEach(item => {
            const tipe = item.querySelector('.soal-tipe').value;
            if (tipe === 'benar_salah') {
                const bsBobots = item.querySelectorAll('.bs-bobot');
                bsBobots.forEach(inp => {
                    currentUnit++;
                    inp.value = (currentUnit === totalUnits) ? (basePoin + remainder) : basePoin;
                });
                recalcBobotBenarSalah(item);
            } else {
                currentUnit++;
                item.querySelector('.soal-bobot').value = (currentUnit === totalUnits) ? (basePoin + remainder) : basePoin;
            }
        });
        
        updateSummary();
    }

    // ========================================================================
    // EXPORT SOAL SAAT INI KE EXCEL (.XLSX) SESUAI FORMAT TEMPLATE IMPORT
    // ========================================================================
    const btnExportSoalExcel = document.getElementById('btn-export-soal-excel');
    if (btnExportSoalExcel) {
        btnExportSoalExcel.addEventListener('click', async function() {
            const soalItems = document.querySelectorAll('.soal-item');
            if (soalItems.length === 0) {
                alert('Belum ada butir soal untuk di-export.');
                return;
            }

            const payloadSoals = [];
            soalItems.forEach(item => {
                const tipe = item.querySelector('.soal-tipe').value;
                const pertanyaan = item.querySelector('.soal-pertanyaan').value;
                const bobotNilai = item.querySelector('.soal-bobot').value;
                const mediaPreview = item.querySelector('.media-preview-container');
                const hasMedia = (mediaPreview && !mediaPreview.classList.contains('d-none')) ? '1' : '0';

                const soalObj = {
                    tipe: tipe,
                    pertanyaan: pertanyaan,
                    bobot_nilai: bobotNilai,
                    has_media: hasMedia,
                    opsi_jawaban: [],
                    has_opsi_media: [],
                    kunci_jawaban: [],
                    pernyataan_bs: []
                };

                if (tipe === 'benar_salah') {
                    item.querySelectorAll('.bs-row').forEach(row => {
                        const teks = row.querySelector('.bs-teks').value;
                        const bobot = row.querySelector('.bs-bobot').value;
                        const radioBenar = row.querySelector('.bs-kunci-benar');
                        const radioSalah = row.querySelector('.bs-kunci-salah');
                        let kunci = '';
                        if (radioBenar && radioBenar.checked) kunci = 'benar';
                        else if (radioSalah && radioSalah.checked) kunci = 'salah';

                        soalObj.pernyataan_bs.push({
                            teks: teks,
                            kunci: kunci,
                            bobot: bobot
                        });
                    });
                } else if (['pg', 'pg_kompleks'].includes(tipe)) {
                    item.querySelectorAll('.opsi-item').forEach((optEl, optIdx) => {
                        const teksOpsi = optEl.querySelector('.opsi-teks').value;
                        const optPrev = optEl.querySelector('.opsi-media-preview-container');
                        const hasOptMedia = (optPrev && !optPrev.classList.contains('d-none')) ? '1' : '0';

                        soalObj.opsi_jawaban.push(teksOpsi);
                        soalObj.has_opsi_media.push(hasOptMedia);

                        if (tipe === 'pg') {
                            const r = optEl.querySelector('.kunci-radio');
                            if (r && r.checked) {
                                soalObj.kunci_jawaban.push(String(optIdx));
                            }
                        } else {
                            const c = optEl.querySelector('.kunci-checkbox');
                            if (c && c.checked) {
                                soalObj.kunci_jawaban.push(String(optIdx));
                            }
                        }
                    });
                }

                payloadSoals.push(soalObj);
            });

            const originalHtml = btnExportSoalExcel.innerHTML;
            btnExportSoalExcel.disabled = true;
            btnExportSoalExcel.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengekspor...';

            try {
                const kodeUjiVal = inputKodeUji ? inputKodeUji.value.trim() : '';
                const response = await fetch("{{ route('sba.soal.export') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json'
                    },
                    body: JSON.stringify({
                        kode_uji: kodeUjiVal,
                        soals: payloadSoals
                    })
                });

                if (!response.ok) {
                    let errMsg = 'Gagal mengekspor soal ke Excel.';
                    try {
                        const errData = await response.json();
                        if (errData && errData.message) errMsg = errData.message;
                    } catch (_) {}
                    throw new Error(errMsg);
                }

                const blob = await response.blob();
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                a.download = kodeUjiVal !== ''
                    ? `Export_Soal_SBA_${kodeUjiVal}.xlsx`
                    : 'Export_Soal_SBA.xlsx';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(downloadUrl);
            } catch (err) {
                alert(err.message || 'Terjadi kesalahan saat mengekspor soal.');
            } finally {
                btnExportSoalExcel.disabled = false;
                btnExportSoalExcel.innerHTML = originalHtml;
            }
        });
    }

    // ========================================================================
    // AJAX IMPORT EXCEL TO VIEW (TEMPORARY DI FORM)
    // ========================================================================
    const btnProsesImport = document.getElementById('btn-proses-import-excel');
    const inputExcelSoal = document.getElementById('input_excel_soal');
    const errorBox = document.getElementById('import-excel-error-box');
    const errorTitle = document.getElementById('import-excel-error-title');
    const errorList = document.getElementById('import-excel-error-list');
    const alertImportSuccess = document.getElementById('alert-import-success');
    const alertImportSuccessText = document.getElementById('alert-import-success-text');

    if (btnProsesImport) {
        btnProsesImport.addEventListener('click', function() {
            errorBox.classList.add('d-none');
            errorList.innerHTML = '';

            if (!inputExcelSoal.files || inputExcelSoal.files.length === 0) {
                errorTitle.innerText = 'File belum dipilih!';
                errorList.innerHTML = '<li>Silakan pilih file Excel (.xlsx, .xls, atau .csv) terlebih dahulu.</li>';
                errorBox.classList.remove('d-none');
                return;
            }

            const formData = new FormData();
            formData.append('excel_file', inputExcelSoal.files[0]);

            const originalBtnHtml = btnProsesImport.innerHTML;
            btnProsesImport.disabled = true;
            btnProsesImport.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';

            fetch("{{ route('sba.soal.import_preview') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw data;
                }
                return data;
            })
            .then(data => {
                const mode = document.querySelector('input[name="import_mode"]:checked').value;

                if (mode === 'replace') {
                    container.innerHTML = '';
                }

                data.soals.forEach(soalData => {
                    addSoal(null, 'end', soalData);
                });

                updateSoalNames();
                updateSummary();

                inputExcelSoal.value = '';
                const modalEl = document.getElementById('modalImportSoalExcel');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }

                alertImportSuccessText.innerHTML = `<strong>Berhasil Dimuat!</strong> ${data.message}`;
                alertImportSuccess.classList.remove('d-none');
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            })
            .catch(err => {
                errorTitle.innerText = err.message || 'Terjadi kesalahan saat memproses file Excel.';
                if (err.errors && Array.isArray(err.errors)) {
                    err.errors.forEach(msg => {
                        const li = document.createElement('li');
                        li.innerText = msg;
                        errorList.appendChild(li);
                    });
                } else if (err.errors && typeof err.errors === 'object') {
                    Object.values(err.errors).flat().forEach(msg => {
                        const li = document.createElement('li');
                        li.innerText = msg;
                        errorList.appendChild(li);
                    });
                }
                errorBox.classList.remove('d-none');
            })
            .finally(() => {
                btnProsesImport.disabled = false;
                btnProsesImport.innerHTML = originalBtnHtml;
            });
        });
    }

    // ========================================================================
    // TOGGLE OTOMATIS ULANGI JIKA GAGAL & MAKS. PERCOBAAN
    // ========================================================================
    const switchUlangi = document.getElementById('ulangi_jika_gagal');
    const wrapperPercobaan = document.getElementById('wrapper-maks-percobaan');
    const inputPercobaan = document.getElementById('maksimal_percobaan');
    const switchOpenBook = document.getElementById('open_book');
    const wrapperToleransi = document.getElementById('wrapper-toleransi');
    const descOpenBook = document.getElementById('text-open-book-desc');

    if (switchUlangi && wrapperPercobaan && inputPercobaan) {
        switchUlangi.addEventListener('change', function() {
            if (this.checked) {
                wrapperPercobaan.classList.remove('d-none');
                if (parseInt(inputPercobaan.value) <= 1 || !inputPercobaan.value) {
                    inputPercobaan.value = 2;
                }
                inputPercobaan.min = 2;
            } else {
                wrapperPercobaan.classList.add('d-none');
                inputPercobaan.min = 1;
                inputPercobaan.value = 1;
            }
        });
    }

    if (switchOpenBook && wrapperToleransi && descOpenBook) {
        switchOpenBook.addEventListener('change', function() {
            if (this.checked) {
                wrapperToleransi.classList.add('d-none');
                descOpenBook.innerText = 'Izinkan peserta berpindah tab / minimize tanpa pelanggaran';
                descOpenBook.classList.remove('text-secondary');
                descOpenBook.classList.add('text-success', 'fw-bold');
            } else {
                wrapperToleransi.classList.remove('d-none');
                descOpenBook.innerText = 'Peserta dilarang berpindah / minimize tab selama uji berlangsung';
                descOpenBook.classList.remove('text-success', 'fw-bold');
                descOpenBook.classList.add('text-secondary');
            }
        });
    }

    const btnSesuaikanPoin = document.getElementById('btn-sesuaikan-poin');
    btnSesuaikanPoin.addEventListener('click', function() {
        sesuaikanBobotOtomatis();
        
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-check me-1"></i> Berhasil Disesuaikan';
        this.classList.replace('bg-gradient-info', 'bg-gradient-success');
        setTimeout(() => {
            this.innerHTML = originalText;
            this.classList.replace('bg-gradient-success', 'bg-gradient-info');
        }, 2000);
    });

    // ========================================================================
    // INTERCEPT SUBMIT FORM: MODAL BOBOT & SIMPAN VIA AJAX (ZERO PAGE RELOAD)
    // ========================================================================
    const formUjianSba = document.getElementById('form-ujian-sba');
    const modalBobotEl = document.getElementById('modalPeringatanBobot');
    const btnModalSesuaikanBobot = document.getElementById('btn-modal-sesuaikan-bobot');
    const btnModalTetapSimpan = document.getElementById('btn-modal-tetap-simpan');
    const ajaxErrorAlert = document.getElementById('ajax-error-alert');
    const ajaxErrorTitle = document.getElementById('ajax-error-title');
    const ajaxErrorList = document.getElementById('ajax-error-list');
    const submitButtons = document.querySelectorAll('.btn-submit-sba');
    let isSubmitting = false;

    function formatErrorKeyLabel(key) {
        const matchSoal = key.match(/^soals\.(\d+)\.(.+)$/);
        if (matchSoal) {
            const nomorSoal = parseInt(matchSoal[1], 10) + 1;
            return `Soal #${nomorSoal}`;
        }
        return null;
    }

    function tampilkanErrorAjax(title, messages = []) {
        ajaxErrorTitle.textContent = title;
        ajaxErrorList.innerHTML = '';

        if (messages.length > 0) {
            messages.forEach(msg => {
                const li = document.createElement('li');
                li.textContent = msg;
                ajaxErrorList.appendChild(li);
            });
        } else {
            const li = document.createElement('li');
            li.textContent = 'Periksa kembali isian formulir Anda.';
            ajaxErrorList.appendChild(li);
        }

        ajaxErrorAlert.classList.remove('d-none');
        ajaxErrorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    async function kirimFormViaAjax() {
        if (isSubmitting) return;
        isSubmitting = true;

        ajaxErrorAlert.classList.add('d-none');
        updateSoalNames();

        const originalButtonHtmls = [];
        submitButtons.forEach((btn, idx) => {
            originalButtonHtmls[idx] = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
        });

        try {
            const formData = new FormData(formUjianSba);

            const response = await fetch(formUjianSba.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            let data = null;
            try {
                data = await response.json();
            } catch (_) {
                data = null;
            }

            if (response.ok && data && data.status === 'success') {
                window.location.href = data.redirect || "{{ route('sba.index') }}";
                return;
            }

            if (response.status === 422 && data && data.errors) {
                const formattedMessages = [];
                Object.entries(data.errors).forEach(([field, msgs]) => {
                    if (field === 'kode_uji' && inputKodeUji) {
                        inputKodeUji.classList.add('is-invalid');
                    }
                    const prefix = formatErrorKeyLabel(field);
                    (Array.isArray(msgs) ? msgs : [msgs]).forEach(m => {
                        formattedMessages.push(prefix ? `${prefix}: ${m}` : m);
                    });
                });

                tampilkanErrorAjax(
                    'Gagal Menyimpan! Terdapat data yang belum sesuai:',
                    formattedMessages
                );
            } else if (response.status === 419) {
                tampilkanErrorAjax(
                    'Sesi Keamanan (CSRF) Berakhir',
                    ['Silakan buka tab baru untuk login ulang jika diperlukan, lalu tekan kembali tombol Simpan Asesmen tanpa me-refresh halaman ini agar soal tidak hilang.']
                );
            } else if (response.status === 413) {
                tampilkanErrorAjax(
                    'Ukuran File Terlalu Besar',
                    ['Total ukuran gambar soal dan pilihan jawaban yang diunggah melebihi batas server. Silakan perkecil ukuran gambar (maksimal 2MB per gambar) lalu coba simpan kembali.']
                );
            } else {
                tampilkanErrorAjax(
                    'Gagal Menyimpan Asesmen!',
                    [(data && data.message) ? data.message : `Terjadi kesalahan pada server (Kode HTTP ${response.status}).`]
                );
            }
        } catch (networkErr) {
            tampilkanErrorAjax(
                'Koneksi Terputus!',
                ['Gagal terhubung ke server. Periksa koneksi internet Anda lalu klik tombol Simpan kembali.']
            );
        } finally {
            isSubmitting = false;
            submitButtons.forEach((btn, idx) => {
                btn.disabled = false;
                btn.innerHTML = originalButtonHtmls[idx];
            });
        }
    }

    function submitFormSetelahKonfirmasi() {
        const bsModalBobot = bootstrap.Modal.getInstance(modalBobotEl);
        if (bsModalBobot) {
            bsModalBobot.hide();
        }
        kirimFormViaAjax();
    }

    if (formUjianSba && modalBobotEl) {
        formUjianSba.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!formUjianSba.checkValidity()) {
                formUjianSba.reportValidity();
                return;
            }

            const { jumlahSoal, totalPoin } = hitungStatistikSoal();

            if (totalPoin !== 100) {
                document.getElementById('modal-bobot-jumlah-soal').innerText = jumlahSoal;
                document.getElementById('modal-bobot-total-poin').innerText = totalPoin + ' Poin';

                const ketEl = document.getElementById('modal-bobot-keterangan');
                if (totalPoin < 100) {
                    const kurang = 100 - totalPoin;
                    ketEl.innerHTML = `<i class="fas fa-info-circle text-warning me-1"></i> Total bobot nilai dari <strong>${jumlahSoal} soal</strong> saat ini masih <strong>kurang ${kurang} poin</strong> dari standar nilai maksimal 100.`;
                } else {
                    const lebih = totalPoin - 100;
                    ketEl.innerHTML = `<i class="fas fa-info-circle text-warning me-1"></i> Total bobot nilai dari <strong>${jumlahSoal} soal</strong> saat ini <strong>melebihi ${lebih} poin</strong> dari standar nilai maksimal 100.`;
                }

                const bsModalBobot = bootstrap.Modal.getOrCreateInstance(modalBobotEl);
                bsModalBobot.show();
                return;
            }

            kirimFormViaAjax();
        });

        btnModalTetapSimpan?.addEventListener('click', function() {
            submitFormSetelahKonfirmasi();
        });

        btnModalSesuaikanBobot?.addEventListener('click', function() {
            sesuaikanBobotOtomatis();
            submitFormSetelahKonfirmasi();
        });
    }

    updateSummary();
});
</script>
@endsection