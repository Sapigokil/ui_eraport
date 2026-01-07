{{-- File: resources/views/ekskul/list_index.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'List Master Ekstrakurikuler')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    {{-- Card Wrapper Sesuai Guru/Index --}}
                    <div class="card my-4 shadow-xs border"> 
                        
                        {{-- KONTROL ATAS: HEADER DENGAN Z-INDEX-2 --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2"> 
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-running me-2"></i> List Master Ekstrakurikuler</h6>
                                <div class="pe-3">
                                    {{-- Tombol Tambah Ekskul Baru --}}
                                    <a href="{{ route('master.ekskul.list.create') }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-plus me-1"></i> Tambah Ekskul Baru
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body px-0 pb-2">
                            
                            {{-- NOTIFIKASI DI DALAM card-body (Sesuai Guru/Index) --}}
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
                            
                            {{-- Area Tabel --}}
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Ekskul</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jadwal</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pembina (Guru)</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Jml. Peserta</th>
                                            <th class="text-secondary opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ekskul as $e)
                                        <tr>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0">{{ $loop->iteration }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $e->nama_ekskul }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <p class="text-xs font-weight-bold mb-0">{{ $e->jadwal_ekskul ?? '-' }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <p class="text-xs font-weight-bold mb-0">{{ $e->guru->nama_guru ?? 'Belum Ditentukan' }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0">{{ $e->siswaEkskul->count() }}</p>
                                            </td>
                                            <td class="align-middle">
                                                {{-- Aksi: EDIT --}}
                                                <a href="{{ route('master.ekskul.list.edit', $e->id_ekskul) }}" class="btn btn-link text-warning p-0 m-0 text-xs me-2" title="Edit Data Ekskul">
                                                    <i class="fas fa-pencil-alt me-1"></i> Edit
                                                </a>

                                                {{-- Aksi: DELETE --}}
                                                <form action="{{ route('master.ekskul.list.destroy', $e->id_ekskul) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus ekskul {{ $e->nama_ekskul }}? Semua tautan siswa akan terhapus.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 m-0 text-xs" title="Hapus Data Ekskul">
                                                        <i class="fas fa-trash-alt me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Belum ada data Ekstrakurikuler yang ditambahkan.</td>
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