@extends('layouts.app')

{{-- Menentukan Title Berdasarkan Mode (Edit atau Tambah) --}}
@php
    $isEdit = isset($tempat);
    $pageTitle = $isEdit ? 'Edit Tempat PKL' : 'Tambah Tempat PKL';
@endphp

@section('page-title', $pageTitle)

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-md-12">
                    <div class="card my-4 shadow-xs border">
                        
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas {{ $isEdit ? 'fa-edit' : 'fa-plus-circle' }} me-2"></i> {{ $pageTitle }}
                                </h6>
                                <div class="pe-3">
                                    <a href="{{ route('pkl.tempat.index') }}" class="btn btn-white btn-sm mb-0">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 py-4">
                            
                            @if ($errors->any())
                                <div class="alert bg-gradient-danger text-white alert-dismissible fade show" role="alert">
                                    <strong>Peringatan!</strong> Terdapat kesalahan pada input Anda:
                                    <ul class="mb-0 mt-2 text-sm">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            {{-- Form Utama (Kondisional Action dan Method) --}}
                            <form action="{{ $isEdit ? route('pkl.tempat.update', $tempat->id) : route('pkl.tempat.store') }}" method="POST">
                                @csrf
                                @if($isEdit)
                                    @method('PUT')
                                @endif

                                {{-- BAGIAN 1: DATA PERUSAHAAN --}}
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Informasi Perusahaan</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold">Nama Perusahaan <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_perusahaan" class="form-control border px-3" value="{{ old('nama_perusahaan', $tempat->nama_perusahaan ?? '') }}" required placeholder="Contoh: PT. Teknologi Maju">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold">Bidang Usaha</label>
                                        {{-- Input dengan Datalist Dinamis --}}
                                        <input list="bidangUsahaList" name="bidang_usaha" class="form-control border px-3" value="{{ old('bidang_usaha', $tempat->bidang_usaha ?? '') }}" placeholder="Ketik atau pilih bidang usaha">
                                        <datalist id="bidangUsahaList">
                                            @foreach($bidangUsahas as $bidang)
                                                <option value="{{ $bidang }}">
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold">Nama Pimpinan / Direktur</label>
                                        <input type="text" name="nama_pimpinan" class="form-control border px-3" value="{{ old('nama_pimpinan', $tempat->nama_pimpinan ?? '') }}" placeholder="Nama pimpinan instansi">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold">Kota / Kabupaten</label>
                                        <input type="text" name="kota" class="form-control border px-3" value="{{ old('kota', $tempat->kota ?? '') }}" placeholder="Contoh: Salatiga">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label font-weight-bold">Alamat Lengkap <span class="text-danger">*</span></label>
                                        <textarea name="alamat_perusahaan" rows="3" class="form-control border px-3" required placeholder="Alamat lengkap perusahaan">{{ old('alamat_perusahaan', $tempat->alamat_perusahaan ?? '') }}</textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold">No. Telp Perusahaan</label>
                                        <input type="text" name="no_telp_perusahaan" class="form-control border px-3" value="{{ old('no_telp_perusahaan', $tempat->no_telp_perusahaan ?? '') }}" placeholder="No telepon instansi">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold">Email Perusahaan</label>
                                        <input type="email" name="email_perusahaan" class="form-control border px-3" value="{{ old('email_perusahaan', $tempat->email_perusahaan ?? '') }}" placeholder="email@perusahaan.com">
                                    </div>
                                </div>

                                <hr class="horizontal dark my-4">

                                {{-- BAGIAN 2: DATA INSTRUKTUR & PIC --}}
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Pembimbing Lapangan & Sekolah</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold">Nama Instruktur (Industri) <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_instruktur" class="form-control border px-3" value="{{ old('nama_instruktur', $tempat->nama_instruktur ?? '') }}" required placeholder="Nama pembimbing dari perusahaan">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold">No. Telp Instruktur</label>
                                        <input type="text" name="no_telp_instruktur" class="form-control border px-3" value="{{ old('no_telp_instruktur', $tempat->no_telp_instruktur ?? '') }}" placeholder="No HP/WA Instruktur">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label font-weight-bold">PIC Sekolah (Guru Penghubung)</label>
                                        <select name="guru_id" class="form-select border px-3">
                                            <option value="">-- Pilih Guru PIC (Opsional) --</option>
                                            @foreach($gurus as $guru)
                                                <option value="{{ $guru->id_guru }}" {{ old('guru_id', $tempat->guru_id ?? '') == $guru->id_guru ? 'selected' : '' }}>
                                                    {{ $guru->nama_guru }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <hr class="horizontal dark my-4">

                                {{-- BAGIAN 3: DATA MOU & STATUS --}}
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Legalitas (MOU) & Status</h6>
                                <div class="row align-items-center">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label font-weight-bold">No. Surat MOU</label>
                                        <input type="text" name="no_surat_mou" class="form-control border px-3" value="{{ old('no_surat_mou', $tempat->no_surat_mou ?? '') }}" placeholder="Contoh: 001/MOU/2024">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label font-weight-bold">Tanggal MOU</label>
                                        <input type="date" name="tanggal_mou" class="form-control border px-3" value="{{ old('tanggal_mou', isset($tempat) && $tempat->tanggal_mou ? $tempat->tanggal_mou->format('Y-m-d') : '') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label font-weight-bold d-block">Status Akses PKL</label>
                                        <div class="form-check form-switch mt-2">
                                            {{-- Kondisi checked: Jika buat baru (default ON) atau jika sedang diedit dan statusnya aktif --}}
                                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" {{ old('is_active', $tempat->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="isActive">Perusahaan Aktif Menerima PKL</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn bg-gradient-primary mb-0">
                                            <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data Baru' }}
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
    </main>
@endsection