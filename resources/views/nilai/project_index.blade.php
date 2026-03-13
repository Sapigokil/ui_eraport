@extends('layouts.app') 

@section('page-title', 'Input Nilai Project')

@php
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
    $tahunMulai = $tahunSekarang - 3;
    $tahunAkhir = $tahunSekarang + 3;

    $tahunAjaranList = [];
    for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }
    $semesterList = ['Ganjil', 'Genap']; 
@endphp

@section('content')
{{-- CSS TAMBAHAN: Hilangkan Spinner pada Input Number --}}
<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />
    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- HEADER YANG DIPERBAIKI --}}
                    @php
                        $namaMapelProject = 'Mata Pelajaran'; 
                        if(request('id_mapel') && isset($mapel)) {
                            $found = $mapel->firstWhere('id_mapel', request('id_mapel'));
                            if($found) {
                                $namaMapelProject = $found->nama_mapel;
                            }
                        }
                    @endphp

                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-rocket text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-rocket me-2"></i> Input Nilai Project
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Kelola Project <strong>{{ $namaMapelProject }}</strong>
                                    </p>
                                </div>
                                <div class="pe-3">
                                    <button class="btn btn-outline-white btn-sm mb-0 me-2" data-bs-toggle="modal" data-bs-target="#downloadTemplateModal">
                                        <i class="fas fa-file-excel me-1"></i> Template
                                    </button>
                                    <button class="btn bg-white text-primary btn-sm mb-0 btn-import-trigger" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="fas fa-file-import me-1"></i> Import
                                    </button>
                                </div>
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

                        {{-- FORM FILTER DINAMIS BERDASARKAN ROLE --}}
                        <div class="p-4 border-bottom bg-gray-100">
                            <form id="mainFilterForm" action="{{ route('nilai.project.index') }}" method="GET" class="row align-items-end mb-0">
                                
                                {{-- KOTAK DEBUG/FILTER ID GURU --}}
                                <div class="col-md-2 mb-3 d-none">
                                    <label class="form-label text-primary font-weight-bolder text-uppercase text-xs"><i class="fas fa-bug"></i> Filter Guru</label>
                                    <select name="id_guru" class="form-select border ps-2 bg-white border-primary" onchange="handleFilterChange('guru')" {{ $isGuru ? 'disabled' : '' }}>
                                        <option value="">Semua Guru</option>
                                        @foreach($guruList as $g)
                                            <option value="{{ $g->id_guru }}" {{ $id_guru_filter == $g->id_guru ? 'selected' : '' }}>{{ $g->nama_guru }}</option>
                                        @endforeach
                                    </select>
                                    @if($isGuru) <input type="hidden" name="id_guru" id="guru_terkunci" value="{{ $id_guru_filter }}"> @endif
                                </div>

                                @if($isGuru)
                                    {{-- 🟢 ALUR GURU: MAPEL -> KELAS --}}
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label font-weight-bolder text-uppercase text-xs">Mata Pelajaran (Guru)</label>
                                        <select name="id_mapel" id="mapel_filter_guru" required class="form-select border ps-2 bg-white" onchange="handleFilterChange('mapel_guru')">
                                            <option value="">-- Pilih Mapel --</option>
                                            @foreach ($mapel as $m)
                                                <option value="{{ $m->id_mapel }}" {{ request('id_mapel') == $m->id_mapel ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label font-weight-bolder text-uppercase text-xs">Kelas</label>
                                        <select name="id_kelas" id="kelas_filter_guru" required class="form-select border ps-2 bg-white" {{ !request('id_mapel') ? 'disabled' : '' }} onchange="handleFilterChange('umum')">
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach ($kelas as $k)
                                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    {{-- 🔴 ALUR ADMIN: KELAS -> MAPEL --}}
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label font-weight-bolder text-uppercase text-xs">Kelas (Admin)</label>
                                        <select name="id_kelas" id="id_kelas" required class="form-select border ps-2 bg-white ajax-select-kelas" data-target="#mapel_filter" onchange="handleFilterChange('kelas_admin')">
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label font-weight-bolder text-uppercase text-xs">Mata Pelajaran</label>
                                        <select name="id_mapel" id="mapel_filter" required class="form-select border ps-2 bg-white" {{ !request('id_kelas') ? 'disabled' : '' }} onchange="handleFilterChange('umum')">
                                            <option value="">-- Pilih Mapel --</option>
                                            @foreach ($mapel as $m)
                                                <option value="{{ $m->id_mapel }}" {{ request('id_mapel') == $m->id_mapel ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="col-md-2 mb-3">
                                    <label class="form-label font-weight-bolder text-uppercase text-xs">Semester</label>
                                    <select name="semester" id="input_semester" required class="form-select border ps-2 bg-white" onchange="handleFilterChange('umum')">
                                        @foreach($semesterList as $sem)
                                            <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label font-weight-bolder text-uppercase text-xs">Tahun Ajaran</label>
                                    <select name="tahun_ajaran" id="input_tahun_ajaran" required class="form-select border ps-2 bg-white" onchange="handleFilterChange('umum')">
                                        @foreach ($tahunAjaranList as $ta)
                                            <option value="{{ $ta }}" {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="d-none"></button>
                            </form>
                        </div>

                        {{-- BOX INFO SEASON --}}
                        <div id="season-info-box" class="mx-4 mt-3" style="display: none;">
                            <div class="d-flex align-items-center bg-light border-radius-lg p-3 border shadow-xs">
                                <div class="d-flex align-items-center flex-wrap">
                                    <span class="text-xs font-weight-bolder text-uppercase text-secondary me-3">Detail Season:</span>
                                    
                                    <div class="d-flex align-items-center me-4">
                                        <span class="badge badge-sm bg-gradient-dark me-2" id="info-semester">-</span>
                                        <span class="badge badge-sm bg-gradient-dark me-2" id="info-tahun">-</span>
                                        <span id="info-status" class="badge badge-sm">-</span>
                                    </div>

                                    <div class="d-flex align-items-center border-start ps-4">
                                        <i class="fas fa-calendar-check text-primary me-2"></i>
                                        <span class="text-xs font-weight-bold text-dark me-2">JADWAL INPUT:</span>
                                        <span class="text-xs text-primary font-weight-bolder" id="info-date-range" style="letter-spacing: 0.5px;">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ALERT PREREQUISITE --}}
                        <div id="prerequisite-alert" class="mx-4 mt-3" style="display: none;">
                            <div class="alert d-flex align-items-start border-radius-lg shadow-sm" id="alert-box-container" role="alert">
                                <i id="alert-icon" class="fas fa-lock me-3 mt-1"></i>
                                <div>
                                    <h6 class="alert-heading font-weight-bolder mb-1" id="alert-title">AKSES TERKUNCI</h6>
                                    <div class="mb-0 text-sm" id="prerequisite-message"></div>
                                </div>
                            </div>
                        </div>

                        {{-- TABEL INPUT --}}
                        <div class="p-4" id="input-form-container">
                            @if(!request('id_kelas') || !request('id_mapel'))
                                <p class="text-center text-secondary">Pilih filter untuk menginput nilai Project.</p>
                            @elseif($siswa->isEmpty())
                                <p class="text-danger mt-3 p-3 text-center border rounded">Data siswa tidak ditemukan di kelas/mapel ini.</p>
                            @else
                                <form action="{{ route('nilai.project.store') }}" method="POST">
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
                                                    <td class="text-center text-sm">{{ $i+1 }}</td>
                                                    <td class="text-sm font-weight-bold">{{ $s->nama_siswa }}<input type="hidden" name="id_siswa[]" value="{{ $s->id_siswa }}"></td>
                                                    <td>
                                                        <div class="input-group input-group-outline">
                                                            <input type="number" name="nilai[]" class="form-control text-center" 
                                                                value="{{ old('nilai.'.$i, optional($p)->nilai) }}" min="0" max="100" required>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-outline">
                                                            <textarea name="tujuan_pembelajaran[]" class="form-control text-sm" rows="2">{{ old('tujuan_pembelajaran.'.$i, optional($p)->tujuan_pembelajaran) }}</textarea>
                                                        </div>
                                                    </td>
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
            <div class="modal-header"><h5 class="modal-title">Download Template Project</h5></div>
            <form action="{{ route('nilai.project.download') }}" method="GET">
                <div class="modal-body">
                    {{-- Logika Dropdown Modal disesuaikan Role --}}
                    @if($isGuru)
                        <div class="mb-3">
                            <label class="form-label">Mata Pelajaran (Guru):</label>
                            <select name="id_mapel" id="modal_mapel_guru" required class="form-select ajax-mapel-modal-guru">
                                <option value="">Pilih Mapel</option>
                                @foreach($mapel as $m)
                                    <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kelas:</label>
                            <select name="id_kelas" id="modal_kelas_guru" required class="form-select">
                                <option value="">Pilih Mapel Terlebih Dahulu</option>
                            </select>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">Kelas (Admin):</label>
                            <select name="id_kelas" required class="form-select ajax-select-kelas-modal" data-target="#mapel_download_project">
                                <option value="">Pilih Kelas</option>
                                @foreach(\App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mata Pelajaran:</label>
                            <select name="id_mapel" id="mapel_download_project" required class="form-select">
                                <option value="">Pilih Kelas Terlebih Dahulu</option>
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Semester:</label>
                        <input type="text" name="semester" value="{{ request('semester', $defaultSemester) }}" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran:</label>
                        <input type="text" name="tahun_ajaran" value="{{ request('tahun_ajaran', $defaultTahunAjaran) }}" class="form-control bg-light" readonly>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn bg-gradient-info">Download</button></div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL IMPORT --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-normal"><i class="fas fa-file-import me-2 text-success"></i>Import Nilai Project</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('nilai.project.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <p class="text-secondary font-weight-bold">Pastikan data Excel sesuai dengan filter yang aktif saat ini.</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas Aktif:</label>
                        <select name="id_kelas" class="form-select bg-light" style="pointer-events: none;" tabindex="-1" required>
                            @if(request('id_kelas'))
                                @php $selKls = \App\Models\Kelas::find(request('id_kelas')); @endphp
                                <option value="{{ request('id_kelas') }}" selected>{{ $selKls->nama_kelas ?? 'Kelas Terpilih' }}</option>
                            @else
                                <option value="">Pilih Filter Utama Dahulu</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mapel Aktif:</label>
                        <select name="id_mapel" class="form-select bg-light" style="pointer-events: none;" tabindex="-1" required>
                            @if(request('id_mapel'))
                                @php $selMapel = \App\Models\MataPelajaran::find(request('id_mapel')); @endphp
                                <option value="{{ request('id_mapel') }}" selected>{{ $selMapel->nama_mapel ?? 'Mapel Terpilih' }}</option>
                            @else
                                <option value="">Pilih Filter Utama Dahulu</option>
                            @endif
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Semester:</label>
                            <input type="text" name="semester" value="{{ request('semester', $defaultSemester) }}" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Ajaran:</label>
                            <input type="text" name="tahun_ajaran" value="{{ request('tahun_ajaran', $defaultTahunAjaran) }}" class="form-control bg-light" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel:</label>
                        <input type="file" name="file_excel" required class="form-control" accept=".xlsx, .xls">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link text-secondary mb-0" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-success mb-0">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- OVERLAY LOADING BARU --}}
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; color: white; font-size: 1.5rem; z-index: 999999;">
    <div class="d-flex flex-column align-items-center">
        <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status"></div> 
        <span>Sedang memproses data...</span>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // SCRIPT UX BARU: Mencegah Reset Mapel Saat Ganti Kelas
    function handleFilterChange(source) {
        if (source === 'guru') {
            $('#kelas_filter_guru').val('');
            $('#mapel_filter_guru').val('');
            $('#id_kelas').val('');
            $('#mapel_filter').val('');
        } else if (source === 'mapel_guru') {
            // Jika guru ganti mapel (filter utama), kosongkan opsi kelas
            $('#kelas_filter_guru').val('');
        } else if (source === 'kelas_admin') {
            // Jika admin ganti kelas (filter utama), kosongkan opsi mapel
            $('#mapel_filter').val('');
        }
        
        // Tampilkan overlay loading
        $('#loadingOverlay').find('span').text('Memuat filter...');
        $('#loadingOverlay').css('display', 'flex');
        
        $('#mainFilterForm').submit();
    }

    $(document).ready(function() {
        // --- 1. AJAX PREREQUISITE & SEASON CHECK ---
        function checkProjectPrerequisite() {
            let idKelas = $('#id_kelas').length ? $('#id_kelas').val() : $('#kelas_filter_guru').val();
            let idMapel = $('#mapel_filter').length ? $('#mapel_filter').val() : $('#mapel_filter_guru').val();
            let semester = $('#input_semester').val();
            let tahunAjaran = $('#input_tahun_ajaran').val();

            if(semester && tahunAjaran) {
                $.ajax({
                    url: "{{ route('nilai.project.check_prerequisite') }}",
                    method: "GET",
                    data: {
                        id_kelas: idKelas,
                        id_mapel: idMapel,
                        semester: semester,
                        tahun_ajaran: tahunAjaran
                    },
                    success: function(response) {
                        let alertContainer = $('#alert-box-container');
                        let btnImport = $('.btn-import-trigger');
                        
                        if(response.season) {
                            $('#info-semester').text(response.season.semester);
                            $('#info-tahun').text(response.season.tahun);
                            $('#info-status').text(response.season.status).attr('class', 'badge badge-sm bg-gradient-success');
                            $('#info-date-range').text(response.season.start + ' s/d ' + response.season.end);
                            $('#season-info-box').show();
                        } else {
                            $('#season-info-box').hide();
                        }

                        if(response.status === 'locked_season') {
                            $('#prerequisite-message').html(response.message);
                            alertContainer.removeClass('alert-warning alert-danger text-dark').addClass('alert-danger text-dark');
                            $('#alert-icon').attr('class', 'fas fa-ban me-3 mt-1');
                            $('#alert-title').text('AKSES DITOLAK');
                            
                            $('#prerequisite-alert').slideDown();
                            $('#input-form-container').slideUp();
                            btnImport.prop('disabled', true).addClass('opacity-5');
                        } else {
                            $('#prerequisite-alert').slideUp();
                            $('#input-form-container').slideDown();
                            btnImport.prop('disabled', false).removeClass('opacity-5');
                        }
                    }
                });
            }
        }
        checkProjectPrerequisite();

        // --- 2. AJAX DROPDOWN UNTUK MODAL (ADMIN FLOW: Kelas -> Mapel) ---
        $('.ajax-select-kelas-modal').on('change', function() {
            let idKelas = $(this).val();
            let target = $(this).data('target');
            let dropdownMapel = $(target);

            dropdownMapel.html('<option value="">Memuat...</option>');
            if (idKelas) {
                $.ajax({
                    url: "{{ route('nilai.project.get_mapel', '') }}/" + idKelas,
                    method: "GET",
                    success: function(res) {
                        let html = '<option value="">-- Pilih Mapel --</option>';
                        res.forEach(item => { html += `<option value="${item.id_mapel}">${item.nama_mapel}</option>`; });
                        dropdownMapel.html(html);
                    }
                });
            } else {
                dropdownMapel.html('<option value="">Pilih Kelas Terlebih Dahulu</option>');
            }
        });

        // --- 3. AJAX DROPDOWN UNTUK MODAL (GURU FLOW: Mapel -> Kelas) ---
        $('.ajax-mapel-modal-guru').on('change', function() {
            let idMapel = $(this).val();
            let idGuru = "{{ $isGuru ? $id_guru_filter : '' }}"; 
            let dropdownKelas = $('#modal_kelas_guru');

            dropdownKelas.html('<option value="">Memuat...</option>');
            if (idMapel && idGuru) {
                let urlFetch = "{{ route('nilai.project.get_kelas_guru', ['id_mapel' => '__MAPEL__', 'id_guru' => '__GURU__']) }}";
                urlFetch = urlFetch.replace('__MAPEL__', idMapel).replace('__GURU__', idGuru);

                $.ajax({
                    url: urlFetch,
                    method: "GET",
                    success: function(res) {
                        let html = '<option value="">-- Pilih Kelas --</option>';
                        res.forEach(item => { html += `<option value="${item.id_kelas}">${item.nama_kelas}</option>`; });
                        dropdownKelas.html(html);
                    },
                    error: function(err) {
                        console.error("AJAX Error: ", err);
                        dropdownKelas.html('<option value="">Gagal memuat kelas</option>');
                    }
                });
            } else {
                dropdownKelas.html('<option value="">Pilih Mapel Terlebih Dahulu</option>');
            }
        });

        // Loading Overlay untuk Form Save / Import
        $('form').not('#mainFilterForm').on('submit', function() {
            if($(this).attr('method') === 'POST' && !$(this).hasClass('no-loading')){
                $('#loadingOverlay').find('span').text('Sedang menyimpan data...');
                $('#loadingOverlay').css('display', 'flex');
            }
        });

        // Disable scroll mouse on input number
        $('form').on('focus', 'input[type=number]', function (e) {
            $(this).on('wheel.disableScroll', function (e) { e.preventDefault(); })
        });
        $('form').on('blur', 'input[type=number]', function (e) {
            $(this).off('wheel.disableScroll');
        });
    });
</script>
@endsection