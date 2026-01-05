{{-- File: resources/views/nilai/project_index.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Input Nilai Project')

@php
   // LOGIKA TAHUN AJARAN & SEMESTER
    $tahunSekarang = date('Y');
    $bulanSekarang = date('n');

    if ($bulanSekarang < 7) {
        $defaultTA1 = $tahunSekarang - 1;
        $defaultTA2 = $tahunSekarang;
        $defaultSemester = 'Genap';
    } else {
        $defaultTA1 = $tahunSekarang;
        $defaultTA2 = $tahunSekarang + 1;
        $defaultSemester = 'Ganjil';
    }

    $defaultTahunAjaran = $defaultTA1 . '/' . $defaultTA2;
    
    $tahunMulai = 2025; 
    $tahunAkhir = date('Y') + 5; 
    $tahunAjaranList = [];
    for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }
    $semesterList = ['Ganjil', 'Genap']; 
@endphp

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />
    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-white text-capitalize ps-3 mb-0"><i class="fas fa-rocket me-2"></i> Input Nilai Project</h6>
                            <div class="pe-3">
                                <button class="btn btn-outline-white btn-sm mb-0 me-2" data-bs-toggle="modal" data-bs-target="#downloadTemplateModal">
                                    <i class="fas fa-file-excel me-1"></i> Download Template
                                </button>
                                <button class="btn bg-gradient-success btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="fas fa-file-import me-1"></i> Import Project
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2">
                        @if (session('success'))
                            <div class="alert bg-gradient-success mx-4 text-white">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert bg-gradient-danger mx-4 text-white">{{ session('error') }}</div>
                        @endif

                        {{-- FILTER UTAMA --}}
                        <div class="p-4 border-bottom">
                            <form action="{{ route('master.project.index') }}" method="GET" class="row align-items-end">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Kelas:</label>
                                    <select name="id_kelas" id="id_kelas" class="form-select" onchange="this.form.submit()">
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Mata Pelajaran:</label>
                                    <select name="id_mapel" id="id_mapel" class="form-select" {{ !request('id_kelas') ? 'disabled' : '' }} onchange="this.form.submit()">
                                        <option value="">Pilih Mapel</option>
                                        @foreach($mapel as $m)
                                            <option value="{{ $m->id_mapel }}" {{ request('id_mapel') == $m->id_mapel ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                        <label class="form-label">Semester:</label>
                                        <select name="semester" required class="form-select">
                                            @foreach($semesterList as $sem)
                                                <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Tahun Ajaran:</label>
                                        <select name="tahun_ajaran" required class="form-select">
                                            @foreach ($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}" {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                <div class="col-md-2 mb-3">
                                    <button type="submit" class="btn bg-gradient-dark w-100 mb-0">Filter</button>
                                </div>
                            </form>
                        </div>

                        {{-- TABEL INPUT --}}
                        <div class="p-4">
                            @if($siswa->isEmpty())
                                <p class="text-center text-secondary">Silakan pilih filter untuk menampilkan daftar siswa.</p>
                            @else
                                <form action="{{ route('master.project.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_kelas" value="{{ request('id_kelas') }}">
                                    <input type="hidden" name="id_mapel" value="{{ request('id_mapel') }}">
                                    <input type="hidden" name="semester" value="{{ request('semester') }}">
                                    <input type="hidden" name="tahun_ajaran" value="{{ request('tahun_ajaran') }}">

                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-center text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                                    <th class="text-xxs font-weight-bolder opacity-7">Nama Siswa</th>
                                                    <th class="text-center text-xxs font-weight-bolder opacity-7" style="width: 15%">Nilai Project</th>
                                                    <th class="text-xxs font-weight-bolder opacity-7">Tujuan Pembelajaran / Capaian</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($siswa as $i => $s)
                                                @php $p = $rapor->get($s->id_siswa); @endphp
                                                <tr>
                                                    <td class="text-center">{{ $i+1 }}</td>
                                                    <td>{{ $s->nama_siswa }}<input type="hidden" name="id_siswa[]" value="{{ $s->id_siswa }}"></td>
                                                    <td><input type="number" name="nilai[]" class="form-control text-center" value="{{ old('nilai.'.$i, optional($p)->nilai) }}" min="0" max="100"></td>
                                                    <td><textarea name="tujuan_pembelajaran[]" class="form-control text-sm" rows="2">{{ old('tujuan_pembelajaran.'.$i, optional($p)->tujuan_pembelajaran) }}</textarea></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn bg-gradient-primary">Simpan Nilai Project</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- MODAL DOWNLOAD --}}
<div class="modal fade" id="downloadTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5>Download Template Project</h5></div>
            <form action="{{ route('master.project.download') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kelas:</label>
                        <select name="id_kelas" required class="form-select ajax-select-kelas" data-target="#mapel_download_project">
                            <option value="">Pilih Kelas</option>
                            @foreach($kelas as $k) <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option> @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran:</label>
                        <select name="id_mapel" id="mapel_download_project" required class="form-select"><option value="">Pilih Kelas Dahulu</option></select>
                    </div>
                    {{-- Semester & TA --}}
                    <div class="mb-3">
                        <label class="form-label">Semester:</label>
                            <select name="semester" required class="form-select">
                                @foreach($semesterList as $sem)
                                    <option value="{{ $sem }}" {{ $defaultSemester == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                @endforeach
                            </select>
                        </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran:</label>
                            <select name="tahun_ajaran" required class="form-select">
                                @foreach ($tahunAjaranList as $ta)
                                    <option value="{{ $ta }}" {{ $defaultTahunAjaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                <div class="modal-footer"><button type="submit" class="btn bg-gradient-info">Download</button></div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL IMPORT REVISI --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-normal" id="importModalLabel">
                    <i class="fas fa-file-import me-2 text-success"></i>Import Nilai Project
                </h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('master.project.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        {{-- Row 1: Kelas --}}
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label class="form-control-label">Kelas</label>
                                <select name="id_kelas" required class="form-select ajax-select-kelas" data-target="#mapel_import_project">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelas as $k) 
                                        <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option> 
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Row 2: Mata Pelajaran --}}
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label class="form-control-label">Mata Pelajaran</label>
                                <select name="id_mapel" id="mapel_import_project" required class="form-select">
                                    <option value="">Pilih Kelas Dahulu</option>
                                </select>
                            </div>
                        </div>

                        {{-- Row 3: Semester & Tahun Ajaran (Berdampingan) --}}
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="form-control-label">Semester</label>
                                <select name="semester" required class="form-select">
                                    @foreach($semesterList as $sem)
                                        <option value="{{ $sem }}" {{ $defaultSemester == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="form-control-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran" required class="form-select">
                                    @foreach ($tahunAjaranList as $ta)
                                        <option value="{{ $ta }}" {{ $defaultTahunAjaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Row 4: File Excel --}}
                        <div class="col-12 mb-2">
                            <div class="form-group">
                                <label class="form-control-label">Pilih File Excel</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-file-excel"></i></span>
                                    <input type="file" name="file_excel" required class="form-control" accept=".xlsx, .xls">
                                </div>
                                <small class="text-xs text-muted mt-2 d-block">
                                    *Gunakan template yang telah diunduh untuk menghindari kesalahan format.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-link text-secondary mb-0" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-success mb-0">
                        <i class="fas fa-upload me-2"></i>Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.ajax-select-kelas').on('change', function() {
            let idKelas = $(this).val();
            let target = $(this).data('target');
            let dropdownMapel = $(target);
            dropdownMapel.html('<option value="">Memuat...</option>');
            if (idKelas) {
                $.ajax({
                    // Menggunakan route yang sama dengan sumatif untuk konsistensi
                    url: "{{ route('master.project.get_mapel', '') }}/" + idKelas,
                    method: "GET",
                    success: function(res) {
                        let html = '<option value="">-- Pilih Mapel --</option>';
                        res.forEach(item => { html += `<option value="${item.id_mapel}">${item.nama_mapel}</option>`; });
                        dropdownMapel.html(html);
                    }
                });
            } else {
                dropdownMapel.html('<option value="">Pilih Kelas Dahulu</option>');
            }
        });
    });
</script>
@endsection