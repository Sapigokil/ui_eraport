{{-- File: resources/views/pembelajaran/edit.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Edit Tautan Pembelajaran Mapel: ' . $mapel_edit->nama_mapel)

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-lg-10 col-md-12 mx-auto">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-warning shadow-warning border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-edit me-2"></i> Edit Tautan Pembelajaran Jamak</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Notifikasi Error --}}
                            @if ($errors->any())
                                <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            
                            {{-- Notifikasi Info --}}
                            <div class="alert bg-gradient-info alert-dismissible text-white fade show text-sm">
                                <i class="fas fa-info-circle me-1"></i> Centang Checkbox *Aktif* untuk mengaktifkan Mata Pelajaran di kelas tersebut. Menandakan Mata Pelajaran itu tersedia bagi kelas.
                                <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            {{-- Form Update --}}
                            <form action="{{ route('master.pembelajaran.update', $pembelajaran_awal->id_pembelajaran) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                {{-- I. Mata Pelajaran yang Diedit --}}
                                <h6 class="text-sm font-weight-bolder my-4 text-primary"><i class="fas fa-book me-1"></i> Mata Pelajaran yang Diedit</h6>
                                <div class="mb-4">
                                    <p class="h5 font-weight-bold text-dark">{{ $mapel_edit->nama_mapel }}</p>
                                    <p class="text-muted text-sm">({{ $mapel_edit->nama_singkat }})</p>
                                    {{-- Hidden Input untuk ID Mapel --}}
                                    <input type="hidden" name="id_mapel" value="{{ $mapel_edit->id_mapel }}">
                                </div>
                                
                                <hr class="my-4">

                                {{-- II. Tentukan Guru per Kelas --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-info"><i class="fas fa-graduation-cap me-1"></i> Status dan Guru Pengampu per Kelas</h6>

                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Kelas</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Aktif?</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Guru Pengampu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($kelas as $k)
                                                @php
                                                    // 1. Cek apakah ada record Pembelajaran untuk Mapel/Kelas ini
                                                    $is_active = $existing_pembelajaran->has($k->id_kelas);
                                                    $current_guru_id = $is_active ? $existing_pembelajaran[$k->id_kelas]->id_guru : 0;
                                                    
                                                    // 2. Gunakan old input jika ada error validasi
                                                    $old_active = old("kelas_guru.{$loop->index}.active");
                                                    $old_guru = old("kelas_guru.{$loop->index}.id_guru");

                                                    // 3. Tentukan status akhir: Prioritas Old Input > Data Database > Default
                                                    $final_active = ($old_active !== null) ? ($old_active == 1) : $is_active;
                                                    $final_guru_id = ($old_guru !== null) ? $old_guru : $current_guru_id;
                                                @endphp
                                            <tr>
                                                <td class="align-middle">
                                                    <p class="text-sm font-weight-bold mb-0">{{ $k->nama_kelas }}</p>
                                                    {{-- Hidden Input untuk ID Kelas --}}
                                                    <input type="hidden" name="kelas_guru[{{ $loop->index }}][id_kelas]" value="{{ $k->id_kelas }}">
                                                </td>
                                                
                                                {{-- Checkbox Active --}}
                                                <td class="align-middle text-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input check-active" type="checkbox" 
                                                               value="1" 
                                                               id="active_{{ $k->id_kelas }}"
                                                               name="kelas_guru[{{ $loop->index }}][active]" 
                                                               {{ $final_active ? 'checked' : '' }}>
                                                    </div>
                                                </td>

                                                <td class="align-middle">
                                                    {{-- Pilihan Guru Selalu Aktif --}}
                                                    <select class="form-select form-select-sm select-guru" 
                                                            id="guru_select_{{ $k->id_kelas }}"
                                                            name="kelas_guru[{{ $loop->index }}][id_guru]"> 
                                                        
                                                        <option value="0">-- Pilih Guru / Belum Ditentukan --</option> 
                                                        @foreach ($guru as $g)
                                                            <option value="{{ $g->id_guru }}" 
                                                                    {{ $final_guru_id == $g->id_guru ? 'selected' : '' }}>
                                                                {{ $g->nama_guru }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="text-center">Tidak ada data kelas yang ditemukan.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-4 pt-2 border-top">
                                    <button type="submit" class="btn bg-gradient-warning me-2">
                                        <i class="fas fa-save me-1"></i> Perbarui Tautan Pembelajaran
                                    </button>
                                    <a href="{{ route('master.pembelajaran.index') }}" class="btn btn-outline-secondary">
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
@endsection