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

                        {{-- SUCCESS & ERROR --}}
                        @if (session('success'))
                            <div class="alert bg-gradient-success text-white alert-dismissible fade show" role="alert">
                                <span class="text-sm">{{ session('success') }}</span>
                                <button type="button" class="btn-close text-white opacity-10" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert bg-gradient-danger text-white">
                                <ul class="mb-0 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- FORM INPUT --}}
                        <form method="POST" action="{{ route('settings.erapor.season.store') }}">
                            @csrf
                            <div class="row mb-4">

                                {{-- Tahun Ajaran --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Tahun Ajaran</label>
                                    <div class="input-group input-group-outline">
                                        <select name="tahun_ajaran" class="form-select" required>
                                            @foreach($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}"
                                                    {{ old('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                                                    {{ $ta }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Semester --}}
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Semester</label>
                                    <div class="input-group input-group-outline">
                                        <select name="semester" class="form-select" required>
                                            @foreach($semesterList as $key => $label)
                                                <option value="{{ $key }}"
                                                    {{ old('semester', $defaultSemester) == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Start Date --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Mulai Input</label>
                                    <div class="input-group input-group-outline">
                                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                                    </div>
                                </div>

                                {{-- End Date --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Akhir Input</label>
                                    <div class="input-group input-group-outline">
                                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                                    </div>
                                </div>

                            </div>

                            {{-- BUTTON --}}
                            <div class="text-end">
                                <button type="submit" class="btn bg-gradient-primary">Aktifkan Season</button>
                            </div>
                        </form>

                        <hr>

                        {{-- LIST SEASON --}}
                        <div class="text-center mb-3">
                            <table class="table table-sm align-middle">
                                <thead class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                    <tr>
                                        <th>Tahun Ajaran</th>
                                        <th>Semester</th>
                                        <th>Mulai Input</th>
                                        <th>Akhir Input</th>
                                        <th>Status Input</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($seasons as $season)
                                        <tr>
                                            <td class="text-center">{{ $season->tahun_ajaran }}</td>
                                            <td class="text-center">{{ $season->semester_text }}</td>
                                            <td class="text-center">{{ $season->start_date ? \Carbon\Carbon::parse($season->start_date)->format('d/m/Y') : '-' }}</td>
                                            <td class="text-center">{{ $season->end_date ? \Carbon\Carbon::parse($season->end_date)->format('d/m/Y') : '-' }}</td>
                                            <td>
                                            {{-- Form Manual Override Status --}}
                                            <form method="POST" action="{{ route('settings.erapor.season.update', $season->id_season) }}" class="d-inline">
                                                @csrf @method('PUT')
                                                <select name="is_open" class="form-select form-select-sm w-75" style="display:inline-block;" onchange="this.form.submit()">
                                                    <option value="1" @selected($season->is_open)>Dibuka</option>
                                                    <option value="0" @selected(!$season->is_open)>Dikunci</option>
                                                </select>
                                            </form>

                                            <td class="text-center">
                                            {{-- Edit --}}
                                            <button class="btn btn-sm p-1" style="border: none; background: none;" data-bs-toggle="modal" data-bs-target="#editSeason{{ $season->id_season }}" title="Edit">
                                                <i class="fas fa-pen text-primary"></i>
                                            </button>

                                            {{-- Hapus --}}
                                            <form action="{{ route('settings.erapor.season.destroy', $season->id_season) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus season ini?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm p-1" style="border: none; background: none;" title="Hapus">
                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                </button>
                                            </form>
                                        </td>

                                            {{-- Modal Edit --}}
                                            <div class="modal fade" id="editSeason{{ $season->id_season }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('settings.erapor.season.update', $season->id_season) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Season {{ $season->tahun_ajaran }} - {{ $season->semester_text }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label>Tahun Ajaran</label>
                                                                    <input type="text" name="tahun_ajaran" class="form-control" value="{{ $season->tahun_ajaran }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label>Semester</label>
                                                                    <select name="semester" class="form-select" required>
                                                                        <option value="1" @selected($season->semester == 1)>Ganjil</option>
                                                                        <option value="2" @selected($season->semester == 2)>Genap</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label>Mulai Input</label>
                                                                    <input type="date" name="start_date" class="form-control" value="{{ $season->start_date }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label>Akhir Input</label>
                                                                    <input type="date" name="end_date" class="form-control" value="{{ $season->end_date }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
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
</main>
@endsection
