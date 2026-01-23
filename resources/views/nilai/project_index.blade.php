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
                                <button class="btn bg-gradient-success btn-sm mb-0 btn-import-trigger" data-bs-toggle="modal" data-bs-target="#importModal">
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

                        {{-- FORM FILTER UTAMA (STYLE BERSIH / CLEAN) --}}
                        <div class="p-4 border-bottom">
                            <form action="{{ route('master.project.index') }}" method="GET" class="row align-items-end mb-0">
                                {{-- Filter Kelas --}}
                                <div class="col-md-3 mb-3">
                                    <label for="id_kelas" class="form-label">Kelas:</label>
                                    <select name="id_kelas" id="id_kelas" required class="form-select ajax-select-kelas" data-target="#mapel_filter" onchange="this.form.submit()">
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                {{-- Filter Mapel --}}
                                <div class="col-md-5 mb-3">
                                    <label for="id_mapel" class="form-label">Mata Pelajaran:</label>
                                    <select name="id_mapel" id="mapel_filter" required class="form-select" {{ !request('id_kelas') ? 'disabled' : '' }} onchange="this.form.submit()">
                                        <option value="">Pilih Mapel</option>
                                        @foreach ($mapel as $m)
                                            <option value="{{ $m->id_mapel }}" {{ request('id_mapel') == $m->id_mapel ? 'selected' : '' }}>
                                                {{ $m->nama_mapel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Semester --}}
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Semester:</label>
                                    <select name="semester" id="input_semester" required class="form-select" onchange="this.form.submit()">
                                        @foreach($semesterList as $sem)
                                            <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>
                                                {{ $sem }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Tahun Ajaran --}}
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Tahun Ajaran:</label>
                                    <select name="tahun_ajaran" id="input_tahun_ajaran" required class="form-select" onchange="this.form.submit()">
                                        @foreach ($tahunAjaranList as $ta)
                                            <option value="{{ $ta }}" {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                                                {{ $ta }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Hidden Submit Button (Untuk Trigger Onchange) --}}
                                <button type="submit" class="d-none"></button>
                            </form>
                        </div>

                        {{-- BOX INFO SEASON (Penataan Rapi & Berjarak) --}}
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
            <form action="{{ route('master.project.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kelas:</label>
                        <select name="id_kelas" class="form-select bg-light" style="pointer-events: none;" tabindex="-1" required>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran:</label>
                        <select name="id_mapel" class="form-select bg-light" style="pointer-events: none;" tabindex="-1" required>
                            @if(request('id_mapel'))
                                @php $selMapel = \App\Models\MataPelajaran::find(request('id_mapel')); @endphp
                                <option value="{{ request('id_mapel') }}" selected>{{ $selMapel->nama_mapel ?? 'Mapel Terpilih' }}</option>
                            @else
                                <option value="">Pilih Filter Terlebih Dahulu</option>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // --- 1. AJAX PREREQUISITE & SEASON CHECK ---
        function checkProjectPrerequisite() {
            let idKelas = $('#id_kelas').val();
            let idMapel = $('#mapel_filter').val();
            let semester = $('#input_semester').val();
            let tahunAjaran = $('#input_tahun_ajaran').val();

            if(semester && tahunAjaran) {
                $.ajax({
                    url: "{{ route('master.project.check_prerequisite') }}",
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
                        
                        // 1. Update Info Season Box (Termasuk Tanggal)
                        if(response.season) {
                            $('#info-semester').text(response.season.semester);
                            $('#info-tahun').text(response.season.tahun);
                            $('#info-status').text(response.season.status)
                                .attr('class', 'badge badge-sm bg-gradient-success');
                            
                            // Gabungkan tanggal start dan end
                            $('#info-date-range').text(response.season.start + ' s/d ' + response.season.end);
                            
                            $('#season-info-box').show();
                        } else {
                            $('#season-info-box').hide();
                        }

                        // 2. Logika Lock/Unlock Tampilan
                        if(response.status === 'locked_season') {
                            $('#prerequisite-message').html(response.message);
                            
                            // GANTI KE MERAH MUDA (Teks Gelap agar terbaca)
                            alertContainer.removeClass('alert-warning alert-danger text-dark').addClass('alert-danger text-dark');
                            $('#alert-icon').attr('class', 'fas fa-ban me-3 mt-1');
                            $('#alert-title').text('AKSES DITOLAK');
                            
                            $('#prerequisite-alert').slideDown();
                            $('#input-form-container').slideUp();
                            btnImport.prop('disabled', true).addClass('opacity-5');
                        } else {
                            // AMAN (Season Sesuai & Dalam Rentang Tanggal)
                            $('#prerequisite-alert').slideUp();
                            $('#input-form-container').slideDown();
                            btnImport.prop('disabled', false).removeClass('opacity-5');
                        }
                    }
                });
            }
        }

        // Jalankan check saat page load & saat filter berubah
        checkProjectPrerequisite();

        // --- 2. DROPDOWN MAPEL DINAMIS ---
        $('.ajax-select-kelas').on('change', function() {
            let idKelas = $(this).val();
            let target = $(this).data('target');
            let dropdownMapel = $(target);
            dropdownMapel.html('<option value="">Memuat...</option>');
            if (idKelas) {
                $.ajax({
                    url: "{{ route('master.project.get_mapel', '') }}/" + idKelas,
                    method: "GET",
                    success: function(res) {
                        let html = '<option value="">-- Pilih Mapel --</option>';
                        res.forEach(item => { html += `<option value="${item.id_mapel}">${item.nama_mapel}</option>`; });
                        dropdownMapel.html(html);
                        // Trigger check ulang setelah mapel dimuat
                        checkProjectPrerequisite();
                    }
                });
            } else {
                dropdownMapel.html('<option value="">Pilih Kelas Dahulu</option>');
            }
        });

        // Loading Overlay saat submit form simpan
        $('form').on('submit', function() {
            if($(this).attr('method') === 'POST' && !$(this).hasClass('no-loading')){
                $('#loadingOverlay').css('display', 'flex');
            }
        });
    });
</script>
@endsection