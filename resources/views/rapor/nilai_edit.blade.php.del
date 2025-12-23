{{-- File: resources/views/rapor/nilai_edit.blade.php --}}

@extends('layouts.app')

@section('title', 'Edit Nilai Akhir')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

        <x-app.navbar />

        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 shadow-xs border">
                        
                        {{-- HEADER --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-warning shadow-warning border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3 mb-0"><i class="fas fa-pencil-alt me-2"></i> Edit Nilai Akhir (Nilai Rapor)</h6>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            {{-- NOTIFIKASI --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm">{{ session('success') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            @if ($errors->any())
                                <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            {{-- FORM FILTER (Filter di sini menggunakan method GET untuk menampilkan data) --}}
                            <div class="p-4 border-bottom">
                                {{-- Aksi form tetap mengarah ke controller yang sama untuk memuat data --}}
                                <form action="{{ route('nilai.input.akhir') }}" method="GET" class="row align-items-end">
                                    
                                    {{-- Filter Kelas --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_kelas" class="form-label">Kelas:</label>
                                        <select class="form-select" id="id_kelas" name="id_kelas" required>
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach ($kelas as $k)
                                                <option value="{{ $k->id_kelas }}" 
                                                    {{ $request->id_kelas == $k->id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Filter Mata Pelajaran --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_mapel" class="form-label">Mata Pelajaran:</label>
                                        <select class="form-select" id="id_mapel" name="id_mapel" required>
                                            <option value="">-- Pilih Mapel --</option>
                                            @foreach ($mapel as $m)
                                                <option value="{{ $m->id_mapel }}" 
                                                    {{ $request->id_mapel == $m->id_mapel ? 'selected' : '' }}>
                                                    {{ $m->nama_mapel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Filter Tahun Ajaran --}}
                                    <div class="col-md-2 mb-3">
                                        <label for="id_tahun_ajaran" class="form-label">Tahun Ajaran:</label>
                                        <select class="form-select" id="id_tahun_ajaran" name="id_tahun_ajaran" required>
                                            <option value="">-- Pilih TA --</option>
                                            @foreach ($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}" 
                                                    {{ $request->id_tahun_ajaran == $ta ? 'selected' : '' }}>
                                                    {{ $ta }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Filter Semester --}}
                                    <div class="col-md-2 mb-3">
                                        <label for="semester" class="form-label">Semester:</label>
                                        <select class="form-select" id="semester" name="semester" required>
                                            <option value="">-- Pilih Semester --</option>
                                            @foreach ($semesterList as $s)
                                                <option value="{{ $s }}" {{ $request->semester == $s ? 'selected' : '' }}>{{ $s }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <button type="submit" class="btn btn-primary w-100 mb-0">Tampilkan Data</button>
                                    </div>
                                </form>
                            </div>
                            
                            {{-- FORM INPUT NILAI (Form ini menggunakan method POST untuk simpan/update) --}}
                            @if ($siswa->isNotEmpty() && $request->id_kelas && $request->id_mapel && $request->id_tahun_ajaran && $request->semester)
                                <form action="{{ route('nilai.simpan.akhir') }}" method="POST">
                                    @csrf

                                    {{-- Hidden Fields untuk Filter yang akan disimpan --}}
                                    <input type="hidden" name="id_kelas" value="{{ $request->id_kelas }}">
                                    <input type="hidden" name="id_mapel" value="{{ $request->id_mapel }}">
                                    <input type="hidden" name="id_tahun_ajaran" value="{{ $request->id_tahun_ajaran }}">
                                    <input type="hidden" name="semester" value="{{ $request->semester }}">

                                    <div class="table-responsive p-0 mt-3">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 5%">No.</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Siswa</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="width: 15%">Nilai (0-100)</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="width: 50%">Deskripsi Capaian Kompetensi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($siswa as $index => $s)
                                                    @php
                                                        // Ambil data nilai yang sudah ada (mode EDIT)
                                                        $nilaiLama = $rapor->get($s->id_siswa);
                                                        // Memuat nilai yang sudah ada, atau old(), atau kosong.
                                                        $nilaiValue = $nilaiLama ? $nilaiLama->nilai : old('nilai.' . $index);
                                                        $capaianValue = $nilaiLama ? $nilaiLama->capaian : old('capaian.' . $index);
                                                    @endphp
                                                    <tr>
                                                        <td class="ps-3 text-sm font-weight-bold align-top">{{ $index + 1 }}.</td>
                                                        <td>
                                                            <p class="text-sm font-weight-bold mb-0">{{ $s->nama_siswa }}</p>
                                                            {{-- Hidden field ID SISWA --}}
                                                            <input type="hidden" name="id_siswa[]" value="{{ $s->id_siswa }}">
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-outline">
                                                                <input type="number" 
                                                                       class="form-control" 
                                                                       name="nilai[]" 
                                                                       min="0" 
                                                                       max="100" 
                                                                       value="{{ $nilaiValue }}" 
                                                                       placeholder="0-100">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-outline">
                                                                <textarea class="form-control" 
                                                                          name="capaian[]" 
                                                                          rows="2" 
                                                                          maxlength="255"
                                                                          placeholder="Capaian kompetensi">{{ $capaianValue }}</textarea>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="card-footer text-end pt-0 border-top mt-4">
                                        <button type="submit" class="btn bg-gradient-success mt-4 mb-0"><i class="fas fa-save me-2"></i> Update Nilai Akhir</button>
                                    </div>
                                </form>
                            @elseif ($request->isMethod('GET') && $siswa->isEmpty() && $request->id_kelas)
                                <p class="text-danger text-center text-sm my-3">
                                    Tidak ada data siswa ditemukan di kelas ini, atau filter belum lengkap.
                                </p>
                            @else
                                <p class="text-secondary text-center text-sm my-3">
                                    Silakan pilih Kelas, Mata Pelajaran, Tahun Ajaran, dan Semester untuk memuat data nilai.
                                </p>
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            <x-app.footer />
        </div>
    </main>
@endsection