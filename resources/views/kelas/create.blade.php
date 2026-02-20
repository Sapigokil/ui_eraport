{{-- File: resources/views/kelas/create.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Tambah Data Kelas Baru')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-plus me-2"></i> Tambah Kelas Baru</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Notifikasi Error (jika ada) --}}
                            @if ($errors->any())
                                <div class="alert alert-danger text-dark">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('master.kelas.store') }}" method="POST">
                                @csrf

                                {{-- I. Informasi Pokok Kelas --}}
                                <h6 class="text-sm font-weight-bolder my-4 text-primary"><i class="fas fa-school me-1"></i> Data Kelas</h6>
                                <div class="row">
                                    
                                    {{-- Nama Kelas --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" required>
                                    </div>
                                    
                                    {{-- Tingkat --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="tingkat" class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                                        <select class="form-select" id="tingkat" name="tingkat" required>
                                            <option value="">-- Pilih Tingkat --</option>
                                            <option value="10" {{ old('tingkat') == '10' ? 'selected' : '' }}>10 (Sepuluh)</option>
                                            <option value="11" {{ old('tingkat') == '11' ? 'selected' : '' }}>11 (Sebelas)</option>
                                            <option value="12" {{ old('tingkat') == '12' ? 'selected' : '' }}>12 (Dua Belas)</option>
                                        </select>
                                    </div>

                                    {{-- Jurusan --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="jurusan" class="form-label">Jurusan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="jurusan" name="jurusan" value="{{ old('jurusan') }}" required placeholder="Contoh: IPA, IPS, RPL">
                                    </div>

                                    {{-- Wali Kelas (Dropdown dari Guru) --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="id_guru" class="form-label">Wali Kelas <span class="text-danger">*</span></label>
                                        
                                        {{-- PERBAIKAN: Tambahkan name="id_guru" dan value gunakan ID --}}
                                        <select class="form-select" name="id_guru" id="id_guru" required>
                                            <option value="">-- Pilih Wali Kelas --</option>
                                            @foreach ($guru as $g)
                                                <option value="{{ $g->id_guru }}" 
                                                    {{-- Logic untuk Edit (jika ada data kelas) atau Old Input (jika validasi gagal) --}}
                                                    {{ (old('id_guru') == $g->id_guru) || (isset($kelas) && $kelas->id_guru == $g->id_guru) ? 'selected' : '' }}>
                                                    {{ $g->nama_guru }}
                                                </option>
                                            @endforeach
                                        </select>

                                        {{-- Input hidden 'wali_kelas' DIHAPUS saja, karena Controller sudah otomatis mengisi nama --}}
                                    </div>
                                </div>
                                
                                <hr class="my-4">

                                {{-- II. Informasi Status Siswa (Revisi Style: Teks Netral) --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-info"><i class="fas fa-users-cog me-1"></i> Status Anggota Kelas</h6>
                                
                                <div class="p-3 border rounded text-dark"> 
                                    <p class="mb-0 text-sm">
                                        <i class="fas fa-info-circle me-1 text-info"></i> Siswa dapat ditautkan ke kelas ini melalui menu **Data Siswa**.
                                    </p>
                                </div>
                                
                                <div class="mt-4 pt-2 border-top">
                                    <button type="submit" class="btn bg-gradient-success me-2">
                                        <i class="fas fa-save me-1"></i> Simpan Kelas Baru
                                    </button>
                                    <a href="{{ route('master.kelas.index') }}" class="btn btn-outline-secondary">
                                        Batal
                                    </a>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
        
    </main>
    
    <script>
        // Skrip untuk menyalin nama guru terpilih ke input 'wali_kelas'
        document.addEventListener('DOMContentLoaded', function () {
            const idGuruSelect = document.getElementById('id_guru_select');
            const waliKelasInput = document.getElementById('wali_kelas_text');

            function updateWaliKelas() {
                const selectedOption = idGuruSelect.options[idGuruSelect.selectedIndex];
                // Mengambil nilai dari option (yang kini berisi nama guru)
                waliKelasInput.value = selectedOption.value.startsWith('--') ? '' : selectedOption.value.trim(); 
            }

            idGuruSelect.addEventListener('change', updateWaliKelas);

            // Jalankan saat load jika ada data old()
            updateWaliKelas();
        });
    </script>
@endsection