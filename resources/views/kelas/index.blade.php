{{-- File: resources/views/kelas/index.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Data Master Kelas')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-school me-2"></i> Data Master Kelas</h6>
                                <div class="pe-3">
                                    <a href="{{ route('master.kelas.create') }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-plus me-1"></i> Tambah Kelas
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert alert-success text-dark mb-4">{{ session('success') }}</div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger text-dark mb-4">{{ session('error') }}</div>
                            @endif

                            {{-- Tombol Export --}}
                            <div class="d-flex justify-content-end mb-3">
                                <div class="dropdown me-2">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuExport" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-file-export me-1"></i> Export Data
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuExport">
                                        <li><a class="dropdown-item" href="{{ route('master.kelas.export.pdf') }}">PDF</a></li>
                                        <li><a class="dropdown-item" href="{{ route('master.kelas.export.csv') }}">CSV</a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Kelas</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tingkat</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jurusan</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Wali Kelas</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah Siswa</th>
                                            <th class="text-secondary opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($kelas as $k)
                                        <tr>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0">{{ $loop->iteration }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $k->nama_kelas }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <p class="text-xs font-weight-bold mb-0">{{ $k->tingkat }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <p class="text-xs font-weight-bold mb-0">{{ $k->jurusan }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <p class="text-xs font-weight-bold mb-0">{{ $k->wali_kelas ?? '-' }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                {{-- 🛑 REVISI: Mengganti badge menjadi teks normal --}}
                                                <p class="text-xs font-weight-bold mb-0">{{ $k->siswas_count ?? 0 }} Siswa</p>
                                            </td>
                                            <td class="align-middle">
                                                {{-- Aksi: SHOW (Lihat Detail) --}}
                                                <a href="{{ route('master.kelas.show', $k->id_kelas) }}" class="text-info font-weight-bold text-xs me-2" data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="fas fa-eye me-1"></i> Lihat
                                                </a>
                                                
                                                {{-- Aksi: EDIT (Edit Data) --}}
                                                <a href="{{ route('master.kelas.edit', $k->id_kelas) }}" class="text-primary font-weight-bold text-xs me-2" data-bs-toggle="tooltip" title="Edit Data">
                                                    <i class="fas fa-pencil-alt me-1"></i> Edit
                                                </a>

                                                {{-- Aksi: DELETE (Hapus Data) --}}
                                                <form action="{{ route('master.kelas.destroy', $k->id_kelas) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus kelas {{ $k->nama_kelas }}? Ini akan menghapus data kelas dan anggota terkait.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 m-0 text-xs" 
                                                            onclick="return confirm('Yakin hapus kelas {{ $k->nama_kelas }}? Ini akan menghapus data kelas dan anggota terkait.')" title="Hapus Data">
                                                        <i class="fas fa-trash-alt me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data kelas yang ditemukan.</td>
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