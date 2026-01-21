@extends('layouts.app') 

@section('page-title', 'Tautkan Mata Pelajaran ke Kelas dan Guru')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-lg-10 col-md-12 mx-auto">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-plus me-2"></i> Tautkan Pembelajaran Jamak</h6>
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
                            
                            <div class="alert bg-gradient-info alert-dismissible text-white fade show text-sm">
                                <i class="fas fa-info-circle me-1"></i> Centang Checkbox *Aktif* untuk mengaktifkan Mata Pelajaran di kelas tersebut.
                                <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            <form action="{{ route('master.pembelajaran.store') }}" method="POST">
                                @csrf

                                {{-- I. Pilih Mata Pelajaran --}}
                                <h6 class="text-sm font-weight-bolder my-4 text-primary"><i class="fas fa-book me-1"></i> Pilih Mata Pelajaran (Wajib)</h6>
                                <div class="mb-4">
                                    <label for="id_mapel" class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_mapel" name="id_mapel" required>
                                        <option value="">-- Pilih Mata Pelajaran --</option>
                                        @foreach ($mapel as $m)
                                            <option value="{{ $m->id_mapel }}" {{ old('id_mapel') == $m->id_mapel ? 'selected' : '' }}>
                                                {{ $m->nama_mapel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <hr class="my-4">

                                {{-- II. Tentukan Guru per Kelas --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-info"><i class="fas fa-graduation-cap me-1"></i> Status dan Guru Pengampu per Kelas</h6>

                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0" id="kelasTable">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Kelas</th>
                                                
                                                {{-- REVISI: Menambahkan Checkbox Centang Semua di Header --}}
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                                    Aktif? <br>
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" title="Centang Semua">
                                                    </div>
                                                </th>
                                                
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Guru Pengampu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($kelas as $k)
                                            <tr>
                                                <td class="align-middle">
                                                    <p class="text-sm font-weight-bold mb-0">{{ $k->nama_kelas }}</p>
                                                    <input type="hidden" name="kelas_guru[{{ $loop->index }}][id_kelas]" value="{{ $k->id_kelas }}">
                                                </td>
                                                
                                                <td class="align-middle text-center">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input class="form-check-input check-active" type="checkbox" 
                                                               value="1" 
                                                               data-row-index="{{ $loop->index }}" 
                                                               id="active_{{ $k->id_kelas }}"
                                                               name="kelas_guru[{{ $loop->index }}][active]" 
                                                               {{ old("kelas_guru.{$loop->index}.active") == 1 ? 'checked' : '' }}>
                                                    </div>
                                                </td>

                                                <td class="align-middle">
                                                    <select class="form-select form-select-sm select-guru" 
                                                            id="guru_select_{{ $loop->index }}"
                                                            name="kelas_guru[{{ $loop->index }}][id_guru]"> 
                                                        <option value="0">-- Pilih Guru / Belum Ditentukan --</option> 
                                                        @foreach ($guru as $g)
                                                            <option value="{{ $g->id_guru }}" 
                                                                    {{ old("kelas_guru.{$loop->index}.id_guru") == $g->id_guru ? 'selected' : '' }}>
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
                                    <button type="submit" class="btn bg-gradient-primary me-2">
                                        <i class="fas fa-save me-1"></i> Simpan Tautan Pembelajaran
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

    {{-- SCRIPT CENTANG SEMUA --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const checkAll = document.getElementById('checkAll');
            const checkItems = document.querySelectorAll('.check-active');

            // 1. Logika: Klik "Check All" mempengaruhi semua checkbox anak
            checkAll.addEventListener('change', function() {
                const isChecked = this.checked;
                checkItems.forEach(function(checkbox) {
                    checkbox.checked = isChecked;
                });
            });

            // 2. Logika: Jika salah satu anak di-uncheck, "Check All" juga uncheck
            checkItems.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if (!this.checked) {
                        checkAll.checked = false;
                    } else {
                        // Cek apakah semua anak sudah tercentang semua
                        const allChecked = Array.from(checkItems).every(c => c.checked);
                        checkAll.checked = allChecked;
                    }
                });
            });
        });
    </script>
@endsection