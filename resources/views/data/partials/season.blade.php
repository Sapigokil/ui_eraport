@extends('layouts.app')

@section('page-title', 'Set Season Input Nilai')

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
                <div class="card my-4">

                    {{-- HEADER BIRU --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3 mb-0">Set Season Input Nilai</h6>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4">

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

                        {{-- FORM INPUT SEASON BARU --}}
                        <div class="bg-gray-100 border-radius-lg p-3 mb-4">
                            <h6 class="text-sm font-weight-bold mb-3"><i class="fas fa-plus-circle me-1"></i> Buat Season Baru</h6>
                            <form method="POST" action="{{ route('settings.erapor.season.store') }}">
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
                                        <input type="date" name="start_date" class="form-control border ps-2" value="{{ old('start_date') }}" required>
                                    </div>

                                    {{-- End Date --}}
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label text-xs font-weight-bolder text-uppercase mb-1">Akhir Input</label>
                                        <input type="date" name="end_date" class="form-control border ps-2" value="{{ old('end_date') }}" required>
                                    </div>

                                    <div class="col-md-1 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn bg-gradient-primary w-100 mb-0" title="Simpan Season Baru">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <hr class="horizontal dark my-4">

                        {{-- TABEL SEASON --}}
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Tahun Ajaran</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Semester</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Periode Input</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($seasons as $season)
                                        <tr>
                                            {{-- Tahun Ajaran --}}
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $season->tahun_ajaran }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Semester --}}
                                            <td>
                                                <span class="text-xs font-weight-bold">{{ $season->semester_text }}</span>
                                            </td>
                                            {{-- Periode --}}
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    {{ $season->start_date ? \Carbon\Carbon::parse($season->start_date)->format('d/m/Y') : '-' }} 
                                                    <span class="text-xxs mx-1">s/d</span> 
                                                    {{ $season->end_date ? \Carbon\Carbon::parse($season->end_date)->format('d/m/Y') : '-' }}
                                                </span>
                                            </td>
                                            {{-- Status (Dropdown Langsung) --}}
                                            <td class="align-middle text-center">
                                                <form method="POST" action="{{ route('settings.erapor.season.update', $season->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="is_open" 
                                                            class="form-select form-select-sm border-0 text-center fw-bold text-dark shadow-none cursor-pointer {{ $season->is_open ? 'bg-gradient-success' : 'bg-secondary' }}" 
                                                            style="width: 100px; margin: 0 auto; background-image: none;"
                                                            onchange="this.form.submit()"
                                                            title="Klik untuk ubah status">
                                                        <option value="1" class="text-dark bg-white" {{ $season->is_open ? 'selected' : '' }}>AKTIF</option>
                                                        <option value="0" class="text-dark bg-white" {{ !$season->is_open ? 'selected' : '' }}>TUTUP</option>
                                                    </select>
                                                </form>
                                            </td>
                                            {{-- Aksi --}}
                                            <td class="align-middle text-center">
                                                {{-- Tombol Edit --}}
                                                <button type="button" class="btn btn-sm btn-warning px-3 mb-0 me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editSeasonModal{{ $season->id }}"
                                                        title="Edit Data Season">
                                                    <i class="fas fa-pencil-alt text-white"></i>
                                                </button>

                                                {{-- Tombol Hapus --}}
                                                <form action="{{ route('settings.erapor.season.destroy', $season->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger px-3 mb-0" 
                                                            title="Hapus Season"
                                                            onclick="if(confirm('Apakah Anda yakin ingin menghapus season ini?')) { this.closest('form').submit(); }">
                                                        <i class="fas fa-trash-alt text-white"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div> {{-- card-body --}}
                </div> {{-- card --}}
            </div>
        </div>
        <x-app.footer />
    </div>

    {{-- LOOPING MODAL EDIT (Ditaruh diluar tabel agar struktur valid) --}}
    @foreach($seasons as $season)
    <div class="modal fade" id="editSeasonModal{{ $season->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('settings.erapor.season.update', $season->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Season</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" class="form-control border ps-2" value="{{ $season->tahun_ajaran }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-select border ps-2" required>
                                <option value="1" {{ $season->semester == '1' ? 'selected' : '' }}>Ganjil</option>
                                <option value="2" {{ $season->semester == '2' ? 'selected' : '' }}>Genap</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Mulai Input</label>
                                <input type="date" name="start_date" class="form-control border ps-2" value="{{ $season->start_date }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Akhir Input</label>
                                <input type="date" name="end_date" class="form-control border ps-2" value="{{ $season->end_date }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach

</main>
@endsection