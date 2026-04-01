{{-- File: resources/views/kelas/edit.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Edit Kelas: ' . $kelas->nama_kelas)

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-warning shadow-warning border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-edit me-2"></i> Edit Data Kelas: {{ $kelas->nama_kelas }}</h6>
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
                            
                            {{-- Notifikasi Success --}}
                            @if (session('success'))
                                <div class="alert alert-success text-dark mb-4">{{ session('success') }}</div>
                            @endif

                            {{-- Form Update --}}
                            <form action="{{ route('master.kelas.update', $kelas->id_kelas) }}" method="POST">
                                @csrf
                                @method('PUT')

                                {{-- I. Informasi Pokok Kelas --}}
                                <h6 class="text-sm font-weight-bolder my-4 text-primary"><i class="fas fa-school me-1"></i> Data Kelas</h6>
                                <div class="row">
                                    
                                    {{-- Nama Kelas --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required>
                                    </div>
                                    
                                    {{-- Tingkat --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="tingkat" class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                                        <select class="form-select" id="tingkat" name="tingkat" required>
                                            <option value="">-- Pilih Tingkat --</option>
                                            @php
                                                $tingkatOptions = ['10', '11', '12'];
                                            @endphp
                                            @foreach ($tingkatOptions as $t)
                                                <option value="{{ $t }}" {{ old('tingkat', $kelas->tingkat) == $t ? 'selected' : '' }}>
                                                    {{ $t }} ({{ $t == 10 ? 'Sepuluh' : ($t == 11 ? 'Sebelas' : 'Dua Belas') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Jurusan --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="jurusan" class="form-label">Jurusan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="jurusan" name="jurusan" value="{{ old('jurusan', $kelas->jurusan) }}" required placeholder="Contoh: IPA, IPS, RPL">
                                    </div>

                                    {{-- Wali Kelas (Dropdown dari Guru) --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="id_guru" class="form-label">Wali Kelas <span class="text-danger">*</span></label>
                                        
                                        <select class="form-select" name="id_guru" id="id_guru" required>
                                            <option value="">-- Pilih Wali Kelas --</option>
                                            @foreach ($guru as $g)
                                                <option value="{{ $g->id_guru }}" 
                                                    {{ (old('id_guru') == $g->id_guru) || (isset($kelas) && $kelas->id_guru == $g->id_guru) ? 'selected' : '' }}>
                                                    {{ $g->nama_guru }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 🛑 PENAMBAHAN: Program Keahlian --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="prog_keahlian" class="form-label">Program Keahlian</label>
                                        <input type="text" class="form-control" id="prog_keahlian" name="prog_keahlian" value="{{ old('prog_keahlian', $kelas->prog_keahlian) }}" placeholder="Contoh: Teknik Komputer dan Informatika">
                                    </div>

                                    {{-- 🛑 PENAMBAHAN: Konsentrasi Keahlian --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="kons_keahlian" class="form-label">Konsentrasi Keahlian</label>
                                        <input type="text" class="form-control" id="kons_keahlian" name="kons_keahlian" value="{{ old('kons_keahlian', $kelas->kons_keahlian) }}" placeholder="Contoh: Rekayasa Perangkat Lunak">
                                    </div>

                                </div>
                                
                                <hr class="my-4">

                                {{-- II. Informasi Status Siswa --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-info"><i class="fas fa-users-cog me-1"></i> Status Anggota Kelas</h6>
                                
                                <div class="p-3 border rounded text-dark"> 
                                    <p class="mb-0 text-sm">
                                        <i class="fas fa-info-circle me-1 text-info"></i> Jumlah siswa dihitung otomatis. Anggota kelas dikelola melalui menu **Data Siswa** atau halaman **Detail Kelas** ini.
                                        <br>
                                        <a href="{{ route('master.kelas.show', $kelas->id_kelas) }}" class="text-warning text-xs mt-1 d-inline-block">
                                            <i class="fas fa-eye me-1"></i> Lihat Anggota Kelas Saat Ini
                                        </a>
                                    </p>
                                </div>
                                
                                <div class="mt-4 pt-2 border-top">
                                    <button type="submit" class="btn bg-gradient-warning me-2">
                                        <i class="fas fa-save me-1"></i> Perbarui Data Kelas
                                    </button>
                                    <a href="{{ route('master.kelas.index') }}" class="btn btn-outline-secondary me-2">
                                        Kembali
                                    </a>
                                    <a href="{{ route('master.kelas.show', $kelas->id_kelas) }}" class="btn btn-outline-info">
                                        Lihat Detail
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

            if(idGuruSelect && waliKelasInput) {
                function updateWaliKelas() {
                    const selectedOption = idGuruSelect.options[idGuruSelect.selectedIndex];
                    waliKelasInput.value = selectedOption.value.startsWith('--') ? '' : selectedOption.value.trim(); 
                }

                idGuruSelect.addEventListener('change', updateWaliKelas);
                updateWaliKelas();
            }
        });
    </script>
@endsection