{{-- File: resources/views/ekskul/siswa_index.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Daftar Peserta Ekstrakurikuler')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

        <x-app.navbar />

        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 shadow-xs border">
                        
                        {{-- KONTROL ATAS: HEADER --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0"><i class="fas fa-users me-2"></i> Daftar Peserta Ekstrakurikuler</h6>
                                {{-- Mengarah ke Create --}}
                                <a href="{{ route('master.ekskul.siswa.create') }}" class="btn bg-gradient-light me-3 mb-0">Tambah Peserta Ekskul</a>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            {{-- PENEMPATAN NOTIFIKASI --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm">{{ session('success') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm">{{ session('error') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            
                            {{-- FILTER FORM --}}
                            <div class="p-4 border-bottom">
                                <form action="{{ route('master.ekskul.siswa.index') }}" method="GET" class="row">
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="id_ekskul" class="form-label">Ekstrakurikuler:</label>
                                        <select class="form-select" id="id_ekskul" name="id_ekskul">
                                            <option value="">-- Semua Ekstrakurikuler --</option>
                                            @foreach ($ekskuls as $ekskul)
                                                <option value="{{ $ekskul->id_ekskul }}" 
                                                    {{ $ekskul->id_ekskul == $filter_id_ekskul ? 'selected' : '' }}>
                                                    {{ $ekskul->nama_ekskul }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="id_kelas" class="form-label">Kelas:</label>
                                        <select class="form-select" id="id_kelas" name="id_kelas" onchange="this.form.submit()">
                                            <option value="">-- Semua Kelas --</option>
                                            @foreach ($kelas as $k)
                                                <option value="{{ $k->id_kelas }}" 
                                                    {{ $k->id_kelas == $filter_id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="nama_siswa" class="form-label">Cari Nama Siswa:</label>
                                        <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" 
                                               value="{{ $filter_nama_siswa ?? '' }}" placeholder="Masukkan nama siswa...">
                                    </div>

                                    <div class="col-12 pt-2">
                                        <button type="submit" class="btn btn-info mb-0 me-2">Tampilkan Data</button>
                                        <a href="{{ route('master.ekskul.siswa.index') }}" class="btn btn-secondary mb-0">Reset Filter</a>
                                    </div>
                                </form>
                            </div>

                            {{-- TABEL DATA DATAR --}}
                            <div class="table-responsive p-0 mt-3">
                                @if ($peserta->isEmpty())
                                    <p class="text-secondary text-center text-sm my-3">
                                        Tidak ada peserta yang terdaftar dengan kriteria filter ini.
                                    </p>
                                @else
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Nama Siswa</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kelas</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Ekstrakurikuler</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                            @foreach ($peserta as $p)
                                                <tr>
                                                    <td>
                                                        <p class="text-sm font-weight-bold mb-0 ps-3">{{ $p->siswa->nama_siswa ?? 'Siswa Hilang' }}</p>
                                                    </td>
                                                    <td>
                                                        <p class="text-sm text-secondary mb-0">{{ $p->siswa->kelas->nama_kelas ?? 'Tanpa Kelas' }}</p>
                                                    </td>
                                                    <td>
                                                        <p class="text-sm text-secondary mb-0">{{ $p->ekskul->nama_ekskul ?? 'Ekskul Hilang' }}</p>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        
                                                        {{-- 🛑 Tombol Edit Dihilangkan --}}
                                                        
                                                        {{-- Tombol Hapus (Tinggal Tombol Ini Saja) --}}
                                                        <form action="{{ route('master.ekskul.siswa.destroy', $p->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link text-danger text-gradient px-3 mb-0" onclick="return confirm('Apakah Anda yakin ingin menghapus peserta ini dari ekstrakurikuler?')">
                                                                <i class="far fa-trash-alt me-2"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                            
                            {{-- Custom Pagination Links --}}
                            <div class="d-flex justify-content-center p-3"> 
                                {{ $peserta->links('vendor.pagination.bootstrap-5') }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <x-app.footer />
        </div>
    </main>
@endsection