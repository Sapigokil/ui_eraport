{{-- File: resources/views/data/kok_index.blade.php --}}

@extends('layouts.app') 

@section('page-title', 'Template Kokurikuler')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        {{-- HEADER OFFSET BIRU --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0">Template Capaian Kokurikuler</h6>
                                <button type="button" class="btn btn-white me-3 mb-0" data-bs-toggle="modal" data-bs-target="#createModal">
                                    <i class="fas fa-plus me-1"></i> Tambah Template
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body px-0 pb-2">
                            {{-- NOTIFIKASI --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm"><strong>Sukses!</strong> {{ session('success') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tingkat</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Judul</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Deskripsi</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Aktif</th>
                                            <th class="text-secondary opacity-7 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data as $index => $item)
                                        <tr>
                                            <td class="ps-4">
                                                <p class="text-xs font-weight-bold mb-0">{{ $index + 1 }}</p>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm border border-info text-info bg-transparent">
                                                    Kelas {{ $item->tingkat }}
                                                </span>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0 text-wrap" style="max-width: 200px;">{{ $item->judul }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs text-secondary mb-0 text-wrap" style="max-width: 350px;">
                                                    {{ Str::limit($item->deskripsi, 90) }}
                                                </p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <form action="{{ route('pengaturan.kok.toggle', $item->id_kok) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="border-0 bg-transparent p-0">
                                                        <span class="badge badge-sm {{ $item->aktif ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                            {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="align-middle text-center">
                                                <button class="btn btn-link text-primary font-weight-bold text-xs p-0 m-0" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal{{ $item->id_kok }}">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>
                                                
                                                <form action="{{ route('pengaturan.kok.destroy', $item->id_kok) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 m-0 ms-3 text-xs" 
                                                            onclick="return confirm('Yakin hapus template tingkat {{ $item->tingkat }} ini?')">
                                                        <i class="fas fa-trash-alt me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        {{-- MODAL EDIT --}}
                                        <div class="modal fade" id="editModal{{ $item->id_kok }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content shadow-lg border-0">
                                                    <div class="modal-header bg-gray-100">
                                                        <h6 class="modal-title font-weight-bolder text-dark">
                                                            <i class="fas fa-edit text-info me-2"></i> Edit Template
                                                        </h6>
                                                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('pengaturan.kok.update', $item->id_kok) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body py-4 text-start">
                                                            <label class="form-label text-xs font-weight-bolder text-uppercase">Tingkat Kelas</label>
                                                            <div class="input-group input-group-outline is-filled mb-4">
                                                                <select name="tingkat" class="form-control" required>
                                                                    <option value="10" {{ $item->tingkat == '10' ? 'selected' : '' }}>Tingkat 10</option>
                                                                    <option value="11" {{ $item->tingkat == '11' ? 'selected' : '' }}>Tingkat 11</option>
                                                                    <option value="12" {{ $item->tingkat == '12' ? 'selected' : '' }}>Tingkat 12</option>
                                                                </select>
                                                            </div>

                                                            <label class="form-label text-xs font-weight-bolder text-uppercase">Judul Template</label>
                                                            <div class="input-group input-group-outline is-filled mb-4">
                                                                <input type="text" name="judul" class="form-control" value="{{ $item->judul }}" required>
                                                            </div>

                                                            <label class="form-label text-xs font-weight-bolder text-uppercase">Deskripsi Capaian</label>
                                                            <div class="input-group input-group-outline is-filled mb-4">
                                                                <textarea name="deskripsi" class="form-control" rows="5" required>{{ $item->deskripsi }}</textarea>
                                                            </div>

                                                            <div class="d-flex align-items-center">
                                                                <div class="form-check form-switch ps-0">
                                                                    <input class="form-check-input ms-auto" type="checkbox" name="aktif" {{ $item->aktif ? 'checked' : '' }}>
                                                                </div>
                                                                <span class="ms-3 text-sm font-weight-bold text-dark text-uppercase">Status Aktif</span>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-gray-100">
                                                            <button type="button" class="btn btn-sm btn-white mb-0" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-sm bg-gradient-info mb-0">Update Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="fas fa-folder-open text-secondary mb-2 fa-2x"></i>
                                                <p class="text-xs text-secondary mb-0">Belum ada data template kokurikuler.</p>
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

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-gray-100">
                    <h6 class="modal-title font-weight-bolder text-dark">
                        <i class="fas fa-plus-circle text-primary me-2"></i> Tambah Template Kokurikuler
                    </h6>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pengaturan.kok.store') }}" method="POST">
                    @csrf
                    <div class="modal-body py-4">
                        <label class="form-label text-xs font-weight-bolder text-uppercase">Tingkat Kelas</label>
                        <div class="input-group input-group-outline mb-4">
                            <select name="tingkat" class="form-control" required>
                                <option value="">-- Pilih Tingkat --</option>
                                <option value="10" {{ (isset($kok) && $kok->tingkat == '10') ? 'selected' : '' }}>Tingkat 10</option>
                                <option value="11" {{ (isset($kok) && $kok->tingkat == '11') ? 'selected' : '' }}>Tingkat 11</option>
                                <option value="12" {{ (isset($kok) && $kok->tingkat == '12') ? 'selected' : '' }}>Tingkat 12</option>
                            </select>
                        </div>

                        <label class="form-label text-xs font-weight-bolder text-uppercase">Judul Template</label>
                        <div class="input-group input-group-outline mb-4">
                            <input type="text" name="judul" class="form-control" placeholder="Masukkan judul template..." required>
                        </div>

                        <label class="form-label text-xs font-weight-bolder text-uppercase">Deskripsi Capaian</label>
                        <div class="input-group input-group-outline mb-4">
                            <textarea name="deskripsi" class="form-control" rows="5" placeholder="Tuliskan deskripsi..." required></textarea>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="form-check form-switch ps-0">
                                <input class="form-check-input ms-auto" type="checkbox" name="aktif" checked>
                            </div>
                            <span class="ms-3 text-sm font-weight-bold text-dark text-uppercase">Status Aktif</span>
                        </div>
                    </div>
                    <div class="modal-footer bg-gray-100">
                        <button type="button" class="btn btn-sm btn-white mb-0" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm bg-gradient-primary mb-0">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection