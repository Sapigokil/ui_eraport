{{-- File: resources/views/mapel/index.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Data Master Mata Pelajaran')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-book me-2"></i> Data Master Mata Pelajaran</h6>
                                <div class="pe-3">
                                    <a href="{{ route('master.mapel.create') }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-plus me-1"></i> Tambah Mapel
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Mata Pelajaran</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Singkatan</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kategori</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Urutan</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pengampu</th>
                                            <th class="text-secondary opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($mapel as $m)
                                        <tr>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0">{{ $loop->iteration }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $m->nama_mapel }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <p class="text-xs font-weight-bold mb-0">{{ $m->nama_singkat }}</p>
                                            </td>
                                            <td class="align-middle">
                                                {{-- Menampilkan Kategori berdasarkan nilai numerik (sesuai MapelController::create) --}}
                                                @php
                                                    $kategoriMap = [
                                                        1 => 'Umum',
                                                        2 => 'Kejuruan',
                                                        3 => 'Pilihan',
                                                        4 => 'Mulok',
                                                    ];
                                                @endphp
                                                <p class="text-xs font-weight-bold mb-0">{{ $kategoriMap[$m->kategori] ?? 'Tidak Diketahui' }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <p class="text-xs font-weight-bold mb-0">{{ $m->urutan }}</p>
                                            </td>
                                            <td class="align-middle">
                                                {{-- Relasi ke Guru Pengampu --}}
                                                <p class="text-xs font-weight-bold mb-0">{{ $m->guru->nama_guru ?? '-' }}</p>
                                            </td>
                                            <td class="align-middle">
                                                {{-- Aksi: EDIT (Edit Data) --}}
                                                <a href="{{ route('master.mapel.edit', $m->id_mapel) }}" class="text-primary font-weight-bold text-xs me-2" data-bs-toggle="tooltip" title="Edit Data">
                                                    <i class="fas fa-pencil-alt me-1"></i> Edit
                                                </a>

                                                {{-- Aksi: DELETE (Hapus Data) --}}
                                                <form action="{{ route('master.mapel.destroy', $m->id_mapel) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus mata pelajaran {{ $m->nama_mapel }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 m-0 text-xs" title="Hapus Data">
                                                        <i class="fas fa-trash-alt me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data mata pelajaran yang ditemukan.</td>
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