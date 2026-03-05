{{-- File: resources/views/pkl/setting/season.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Set Season Rapor PKL')

@section('content')
@php
    $tahunSekarang = date('Y');
    $bulanSekarang = date('n');

    // Tentukan default semester & tahun ajaran
    if ($bulanSekarang < 7) {
        $defaultTA1 = $tahunSekarang - 1;
        $defaultTA2 = $tahunSekarang;
        $defaultSemester = 2; // Genap
    } else {
        $defaultTA1 = $tahunSekarang;
        $defaultTA2 = $tahunSekarang + 1;
        $defaultSemester = 1; // Ganjil
    }
    $defaultTahunAjaran = $defaultTA1 . '/' . $defaultTA2;

    // Daftar Tahun Ajaran (3 tahun sebelumnya & 3 tahun ke depan)
    $tahunMulai = $tahunSekarang - 3;
    $tahunAkhir = $tahunSekarang + 3;
    $tahunAjaranList = [];
    for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }

    $semesterList = [1 => 'Ganjil', 2 => 'Genap'];
@endphp

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />
    
    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 border shadow-xs">

                    {{-- HEADER BIRU --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-white text-capitalize ps-3 mb-0"><i class="fas fa-calendar-alt me-2"></i> Set Season Rapor PKL</h6>
                            <div class="pe-3">
                                <a href="{{ route('settings.pkl.index') }}" class="btn btn-sm btn-light mb-0 text-primary">
                                    <i class="fas fa-table me-1"></i> Ke Setting Rubrik
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4 mt-3">

                        {{-- NOTIFIKASI --}}
                        @if (session('success'))
                            <div class="alert bg-gradient-success text-white alert-dismissible fade show" role="alert">
                                <span class="text-sm"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}</span>
                                <button type="button" class="btn-close text-white opacity-10" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert bg-gradient-danger text-white alert-dismissible fade show" role="alert">
                                <ul class="mb-0 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close text-white opacity-10" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="alert bg-light text-dark border mb-4 text-sm">
                            <i class="fas fa-info-circle text-info me-2"></i> <strong>PANDUAN:</strong> Gunakan menu ini untuk mengatur rentang waktu kapan Pembimbing/Instruktur diizinkan untuk menginput atau mengubah nilai PKL siswa.
                        </div>

                        {{-- FORM INPUT SEASON BARU --}}
                        <div class="bg-gray-100 border-radius-lg p-3 mb-4 border">
                            <h6 class="text-sm font-weight-bold mb-3"><i class="fas fa-plus-circle me-1"></i> Buat Season PKL Baru</h6>
                            <form method="POST" action="{{ route('settings.pkl.season.store') }}">
                                @csrf
                                <div class="row">
                                    {{-- Tahun Ajaran --}}
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label text-xs font-weight-bolder text-uppercase mb-1">Tahun Ajaran</label>
                                        <select name="tahun_ajaran" class="form-select border ps-2" required>
                                            @foreach($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}" {{ old('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                                                    {{ $ta }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Semester --}}
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label text-xs font-weight-bolder text-uppercase mb-1">Semester</label>
                                        <select name="semester" class="form-select border ps-2" required>
                                            @foreach($semesterList as $key => $label)
                                                <option value="{{ $key }}" {{ old('semester', $defaultSemester) == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Start Date --}}
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label text-xs font-weight-bolder text-uppercase mb-1">Mulai Input</label>
                                        <input type="date" name="start_date" class="form-control border ps-2 bg-white" value="{{ old('start_date') }}" required>
                                    </div>

                                    {{-- End Date --}}
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label text-xs font-weight-bolder text-uppercase mb-1">Batas Akhir Input</label>
                                        <input type="date" name="end_date" class="form-control border ps-2 bg-white" value="{{ old('end_date') }}" required>
                                    </div>

                                    <div class="col-md-1 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn bg-gradient-primary w-100 mb-0 shadow-sm" title="Simpan Season Baru">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <hr class="horizontal dark my-4">

                        {{-- TABEL SEASON --}}
                        <div class="table-responsive p-0 border rounded">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Tahun Ajaran</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Semester</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Periode Buka Input</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Akses (Manual)</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($seasons as $season)
                                        <tr>
                                            {{-- Tahun Ajaran --}}
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><i class="fas fa-calendar-check text-info me-2"></i>{{ $season->tahun_ajaran }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Semester --}}
                                            <td>
                                                <span class="badge {{ $season->semester == 1 ? 'bg-gradient-info' : 'bg-gradient-warning' }} text-xxs font-weight-bold">{{ $season->semester_text }}</span>
                                            </td>
                                            {{-- Periode --}}
                                            <td class="align-middle text-center">
                                                <span class="text-dark text-sm font-weight-bold">
                                                    {{ $season->start_date ? \Carbon\Carbon::parse($season->start_date)->format('d M Y') : '-' }} 
                                                    <span class="text-xs text-secondary mx-2"><i class="fas fa-arrow-right"></i></span> 
                                                    {{ $season->end_date ? \Carbon\Carbon::parse($season->end_date)->format('d M Y') : '-' }}
                                                </span>
                                            </td>
                                            {{-- Status (Dropdown Langsung) --}}
                                            <td class="align-middle text-center">
                                                <form method="POST" action="{{ route('settings.pkl.season.update', $season->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="is_open" 
                                                            class="form-select form-select-sm border-0 text-center fw-bold text-dark shadow-sm cursor-pointer {{ $season->is_open ? 'bg-gradient-success text-white' : 'bg-gradient-secondary text-white' }}" 
                                                            style="width: 120px; margin: 0 auto; background-image: none; border-radius: 20px;"
                                                            onchange="this.form.submit()"
                                                            title="Klik untuk ubah status secara manual">
                                                        <option value="1" class="text-dark bg-white" {{ $season->is_open ? 'selected' : '' }}>✓ DIBUKA</option>
                                                        <option value="0" class="text-dark bg-white" {{ !$season->is_open ? 'selected' : '' }}>✕ DITUTUP</option>
                                                    </select>
                                                </form>
                                            </td>
                                            {{-- Aksi --}}
                                            <td class="align-middle text-center">
                                                {{-- Tombol Edit --}}
                                                <button type="button" class="btn btn-sm btn-outline-warning px-3 py-1 mb-0 me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editSeasonModal{{ $season->id }}"
                                                        title="Edit Data Season">
                                                    <i class="fas fa-pencil-alt"></i> Edit
                                                </button>

                                                {{-- Tombol Hapus --}}
                                                <form action="{{ route('settings.pkl.season.destroy', $season->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger px-3 py-1 mb-0" 
                                                            title="Hapus Season"
                                                            onclick="if(confirm('Peringatan: Menghapus season akan menghilangkan referensi periode waktu. Lanjutkan?')) { this.closest('form').submit(); }">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-secondary text-sm">Belum ada Season PKL yang dibuat.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div> {{-- card-body --}}
                </div> {{-- card --}}
            </div>
        </div>
        <x-app.footer />
    </div>

    {{-- LOOPING MODAL EDIT --}}
    @foreach($seasons as $season)
    <div class="modal fade" id="editSeasonModal{{ $season->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('settings.pkl.season.update', $season->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Season Rapor PKL</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" class="form-control border ps-2" value="{{ $season->tahun_ajaran }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Semester</label>
                            <select name="semester" class="form-select border ps-2" required>
                                <option value="1" {{ $season->semester == '1' ? 'selected' : '' }}>Ganjil</option>
                                <option value="2" {{ $season->semester == '2' ? 'selected' : '' }}>Genap</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label font-weight-bold">Tanggal Mulai Buka Input</label>
                                <input type="date" name="start_date" class="form-control border ps-2" value="{{ $season->start_date }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label font-weight-bold">Tanggal Terakhir Input</label>
                                <input type="date" name="end_date" class="form-control border ps-2" value="{{ $season->end_date }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn bg-gradient-success">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach

</main>
@endsection