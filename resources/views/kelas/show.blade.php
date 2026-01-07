{{-- File: resources/views/kelas/show.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Detail Kelas: ' . $kelas->nama_kelas)

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-eye me-2"></i> Detail Kelas: {{ $kelas->nama_kelas }}</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Tombol Aksi --}}
                            <div class="mb-4">
                                <a href="{{ route('master.kelas.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <a href="{{ route('master.kelas.edit', $kelas->id_kelas) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit Data Kelas
                                </a>
                                <a href="{{ route('master.kelas.export.single', $kelas->id_kelas) }}" class="btn btn-info text-white">
                                    <i class="fas fa-file-pdf me-1"></i> Export PDF Kelas
                                </a>
                            </div>

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder my-4 text-primary"><i class="fas fa-info-circle me-1"></i> I. Informasi Pokok Kelas</h6>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-4 text-secondary">Nama Kelas:</dt>
                                        <dd class="col-sm-8 font-weight-bold">{{ $kelas->nama_kelas }}</dd>
                                        
                                        <dt class="col-sm-4 text-secondary">Tingkat Kelas:</dt>
                                        <dd class="col-sm-8 font-weight-bold">{{ $kelas->tingkat }}</dd>
                                        
                                        <dt class="col-sm-4 text-secondary">Jurusan:</dt>
                                        <dd class="col-sm-8 font-weight-bold">{{ $kelas->jurusan ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-4 text-secondary">Wali Kelas:</dt>
                                        <dd class="col-sm-8 font-weight-bold">
                                            {{ $kelas->guru->nama_guru ?? ($kelas->wali_kelas ?? '-') }}
                                            @if($kelas->guru)
                                                <small class="text-muted">(NIP: {{ $kelas->guru->nip ?? '-' }})</small>
                                            @endif
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary">ID Guru (PK):</dt>
                                        <dd class="col-sm-8 font-weight-bold">{{ $kelas->id_guru ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-4 text-secondary">Jumlah Siswa:</dt>
                                        <dd class="col-sm-8 font-weight-bold">
                                            {{ $kelas->siswas_count ?? 0 }} Siswa
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            
                            <hr class="my-4">

                            {{-- ================================================= --}}
                            {{-- 🛑 BAGIAN BARU: II. Daftar Mata Pelajaran --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-info"><i class="fas fa-book me-1"></i> II. Daftar Mata Pelajaran</h6>
                            <hr>
                            <div class="p-3 border rounded text-dark mb-4">
                                <p class="text-sm text-muted mb-0">
                                    Data mata pelajaran untuk kelas **{{ $kelas->nama_kelas }}** belum tersedia atau belum diimplementasikan.
                                </p>
                            </div>
                            
                            {{-- ================================================= --}}
                            {{-- 🛑 BAGIAN BARU: III. Daftar Anggota Kelas --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-info"><i class="fas fa-users me-1"></i> III. Daftar Anggota Kelas ({{ $anggota->count() }} Siswa)</h6>
                            <hr>
                            
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Siswa</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">NISN</th>
                                            <th class="text-secondary opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($anggota as $s)
                                        <tr>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0">{{ $loop->iteration }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $s->nama_siswa }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <p class="text-xs font-weight-bold mb-0">{{ $s->nisn }}</p>
                                            </td>
                                            <td class="align-middle">
                                                {{-- Aksi: Hapus Anggota --}}
                                                <form action="{{ route('master.kelas.anggota.delete', $s->id_siswa) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mengeluarkan {{ $s->nama_siswa }} dari kelas ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 m-0 text-xs" title="Keluarkan dari Kelas">
                                                        <i class="fas fa-times me-1"></i> Keluarkan
                                                    </button>
                                                </form>
                                                
                                                {{-- Link ke Detail Siswa --}}
                                                <a href="{{ route('master.siswa.show', $s->id_siswa) }}" class="text-info font-weight-bold text-xs ms-3" 
                                                   data-bs-toggle="tooltip" title="Lihat Detail Siswa" target="_blank"> 
                                                    <i class="fas fa-eye me-1"></i> Detail Siswa
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                <p class="text-sm text-muted py-3 mb-0">Belum ada siswa yang terdaftar dalam kelas ini.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
        
    </main>
@endsection