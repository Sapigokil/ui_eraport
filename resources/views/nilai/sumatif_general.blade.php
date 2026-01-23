@extends('layouts.app') 

@section('page-title', 'Input Nilai Sumatif ' . $sumatifId)

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
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas fa-edit me-2"></i> Input Nilai Sumatif {{ $sumatifId }}
                                </h6>
                                <div class="pe-3">
                                    <button class="btn bg-gradient-light text-dark btn-sm mb-0 me-2" data-bs-toggle="modal" data-bs-target="#downloadTemplateModal">
                                        <i class="fas fa-file-excel me-1"></i> Download Template
                                    </button>
                                    <button class="btn bg-gradient-success btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="fas fa-file-import me-1"></i> Import
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            {{-- FORM FILTER UTAMA (STYLE BERSIH / CLEAN) --}}
                            <div class="p-4 border-bottom">
                                <form action="{{ route('master.sumatif.s' . $sumatifId) }}" method="GET" class="row align-items-end">
                                    <input type="hidden" name="sumatif" id="input_sumatif" value="{{ $sumatifId }}">

                                    <div class="col-md-3 mb-3">
                                        <label for="id_kelas" class="form-label">Kelas:</label>
                                        <select name="id_kelas" id="id_kelas" required class="form-select ajax-select-kelas" data-target="#mapel_filter" onchange="this.form.submit()">
                                            <option value="">Pilih Kelas</option>
                                            @foreach(\App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
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

                                    <button type="submit" class="d-none"></button>
                                </form>
                            </div>

                            {{-- ALERT SYSTEM (Success/Error PHP) --}}
                            <div class="px-4 mt-3">
                                @if (session('success'))
                                    <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                        <span class="text-sm"><strong>Sukses!</strong> {!! session('success') !!}</span>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                        <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                                    </div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert bg-gradient-danger alert-dismissible text-white fade show">
                                        <strong>Gagal!</strong>
                                        <ul class="mb-0">
                                            @foreach ($errors->messages() as $field => $messages)
                                                @foreach ($messages as $message)
                                                    @if (str_contains($field, 'tujuan_pembelajaran'))
                                                        <li class="text-sm">TP mengandung karakter tidak valid.</li>
                                                    @else
                                                        <li class="text-sm">{{ $message }}</li>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                                    </div>
                                @endif
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

                            {{-- 🛑 CONTAINER UTAMA (FORM & TABEL) 🛑 --}}
                            <div id="input-form-container" class="p-4 pt-0">
                                @if(!request('id_kelas') || !request('id_mapel'))
                                    <p class="text-secondary mt-3 p-3 text-center border rounded">Pilih filter untuk menginput nilai Sumatif {{ $sumatifId }}.</p>
                                @elseif($siswa->isEmpty())
                                    <p class="text-danger mt-3 p-3 text-center border rounded">Data siswa tidak ditemukan.</p>
                                @else
                                    @if(!$seasonOpen)
                                        <div class="alert alert-warning text-sm mb-3">🔒 Input nilai dikunci karena season tidak aktif.</div>
                                    @endif
                                    
                                    <form action="{{ route('master.sumatif.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="id_kelas" value="{{ request('id_kelas') }}">
                                        <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                                        <input type="hidden" name="tahun_ajaran" value="{{ request('tahun_ajaran') }}">
                                        <input type="hidden" name="semester" value="{{ request('semester') }}">
                                        <input type="hidden" name="id_mapel" value="{{ request('id_mapel') }}">

                                        <div class="table-responsive p-0">
                                            <table class="table align-items-center mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="text-xxs font-weight-bolder opacity-7 text-center" style="width: 5%">No</th>
                                                        <th class="text-xxs font-weight-bolder opacity-7">Nama Siswa</th>
                                                        <th class="text-xxs font-weight-bolder opacity-7 text-center" style="width: 15%">Nilai</th>
                                                        <th class="text-xxs font-weight-bolder opacity-7">Tujuan Pembelajaran</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($siswa as $i => $s)
                                                    @php
                                                        $raporItem = $rapor->get($s->id_siswa);
                                                        $nilaiLama = optional($raporItem);
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center text-sm font-weight-bold">{{ $i+1 }}</td>
                                                        <td class="text-sm font-weight-bold">
                                                            {{ $s->nama_siswa }}
                                                            <input type="hidden" name="id_siswa[]" value="{{ $s->id_siswa }}">
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-outline">
                                                                <input type="number" name="nilai[]" min="0" max="100" 
                                                                       class="form-control text-center" 
                                                                       value="{{ $nilaiLama->nilai }}" 
                                                                       {{ !$seasonOpen ? 'disabled' : '' }}>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-outline">
                                                                <textarea name="tujuan_pembelajaran[]" rows="2" 
                                                                          class="form-control text-sm" 
                                                                          {{ !$seasonOpen ? 'disabled' : '' }}>{{ $nilaiLama->tujuan_pembelajaran }}</textarea>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-end mt-4">
                                            @if($seasonOpen)
                                                <button type="submit" class="btn bg-gradient-success"><i class="fas fa-save me-2"></i> Simpan Nilai Sumatif {{ $sumatifId }}</button>
                                            @endif
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>

    {{-- MODAL DOWNLOAD --}}
    <div class="modal fade" id="downloadTemplateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Download Template Sumatif {{ $sumatifId }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('master.sumatif.download') }}" method="GET"> 
                    <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kelas:</label>
                            {{-- Class ajax-select-kelas digunakan untuk trigger JS ambil mapel --}}
                            <select name="id_kelas" required class="form-select ajax-select-kelas" data-target="#mapel_download">
                                <option value="">Pilih Kelas</option>
                                @foreach(\App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mata Pelajaran:</label>
                            <select name="id_mapel" id="mapel_download" required class="form-select">
                                <option value="">Pilih Kelas Terlebih Dahulu</option>
                            </select>
                        </div>
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
                    <div class="modal-footer">
                        <button type="submit" class="btn bg-gradient-info">Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL IMPORT --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Nilai Sumatif {{ $sumatifId }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('master.sumatif.import') }}" method="POST" enctype="multipart/form-data"> 
                    @csrf
                    <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                    <div class="modal-body">
                        <div class="mb-3 text-center">
                            <p class="text-secondary font-weight-bold">Pastikan data Excel sesuai dengan template.</p>
                        </div>

                        {{-- Kolom Kelas (Read-Only) --}}
                        <div class="mb-3">
                            <label class="form-label">Kelas:</label>
                            <select name="id_kelas" required class="form-select bg-light" style="pointer-events: none;" tabindex="-1">
                                @foreach(\App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                    <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kolom Mata Pelajaran (Read-Only) --}}
                        <div class="mb-3">
                            <label class="form-label">Mata Pelajaran:</label>
                            <select name="id_mapel" id="mapel_import" required class="form-select bg-light" style="pointer-events: none;" tabindex="-1">
                                @if(request('id_mapel'))
                                    @php $selMapel = \App\Models\MataPelajaran::find(request('id_mapel')); @endphp
                                    <option value="{{ request('id_mapel') }}" selected>{{ $selMapel->nama_mapel ?? 'Mapel Terpilih' }}</option>
                                @else
                                    <option value="">Pilih Filter Terlebih Dahulu</option>
                                @endif
                            </select>
                        </div>

                        {{-- Kolom Semester (Read-Only) --}}
                        <div class="mb-3">
                            <label class="form-label">Semester:</label>
                            <input type="text" name="semester" value="{{ request('semester', $defaultSemester) }}" class="form-control bg-light" readonly>
                        </div>

                        {{-- Kolom Tahun Ajaran (Read-Only) --}}
                        <div class="mb-3">
                            <label class="form-label">Tahun Ajaran:</label>
                            <input type="text" name="tahun_ajaran" value="{{ request('tahun_ajaran', $defaultTahunAjaran) }}" class="form-control bg-light" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File Excel:</label>
                            <input type="file" name="file_excel" required class="form-control" accept=".xlsx, .xls">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn bg-gradient-success">Proses Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; color: white; font-size: 1.5rem; z-index: 9999;">
        <div class="spinner-border text-light me-3" role="status"></div> Sedang memproses...
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // --- 1. AJAX PREREQUISITE CHECK ---
            function checkPrerequisite() {
                let idKelas = $('#id_kelas').val();
                let idMapel = $('#mapel_filter').val();
                let semester = $('#input_semester').val();
                let tahunAjaran = $('#input_tahun_ajaran').val();
                let currentSumatif = $('#input_sumatif').val();

                if(semester && tahunAjaran) {
                    $.ajax({
                        url: "{{ route('master.sumatif.check_prerequisite') }}",
                        method: "GET",
                        data: {
                            id_kelas: idKelas,
                            id_mapel: idMapel,
                            semester: semester,
                            tahun_ajaran: tahunAjaran,
                            sumatif: currentSumatif
                        },
                        success: function(response) {
                            let alertContainer = $('#alert-box-container');
                            let btnImportTrigger = $('button[data-bs-target="#importModal"]');
                            
                            // 1. Update Info Season Box
                            if(response.season) {
                                $('#info-semester').text(response.season.semester);
                                $('#info-tahun').text(response.season.tahun);
                                
                                // Status Badge
                                let statusBadge = $('#info-status');
                                statusBadge.text(response.season.status);
                                if(response.season.is_open) {
                                    statusBadge.attr('class', 'badge badge-sm bg-gradient-success');
                                } else {
                                    statusBadge.attr('class', 'badge badge-sm bg-gradient-danger');
                                }
                                
                                // Rentang Tanggal (Gunakan format dari controller)
                                if(response.season.start && response.season.end) {
                                    $('#info-date-range').text(response.season.start + ' s/d ' + response.season.end);
                                } else {
                                    $('#info-date-range').text('Jadwal tidak diatur');
                                }
                                
                                $('#season-info-box').fadeIn();
                            } else {
                                $('#season-info-box').hide();
                            }

                            // 2. Logika Lock Input & Alert
                            if(response.status === 'locked_season' || response.status === 'warning') {
                                $('#prerequisite-message').html(response.message);
                                
                                // Reset class warna agar tidak bentrok
                                alertContainer.removeClass('alert-danger alert-warning text-dark text-white');

                                if(response.status === 'locked_season') {
                                    // GAYA MERAH (Akses Ditolak) - Teks Gelap sesuai preferensi gambar sebelumnya
                                    alertContainer.addClass('alert-danger text-dark');
                                    $('#alert-icon').attr('class', 'fas fa-ban me-3 mt-1 text-danger');
                                    $('#alert-title').text('AKSES DITOLAK').addClass('text-danger');
                                } else {
                                    // GAYA KUNING (Prasyarat)
                                    alertContainer.addClass('alert-warning text-dark');
                                    $('#alert-icon').attr('class', 'fas fa-exclamation-triangle me-3 mt-1 text-warning');
                                    $('#alert-title').text('PERHATIAN').removeClass('text-danger');
                                }

                                $('#prerequisite-alert').slideDown();
                                $('#input-form-container').slideUp();
                                btnImportTrigger.prop('disabled', true).addClass('opacity-5');
                            } else {
                                // Status Safe
                                $('#prerequisite-alert').slideUp();
                                $('#input-form-container').slideDown();
                                btnImportTrigger.prop('disabled', false).removeClass('opacity-5');
                            }
                        }
                    });
                }
            }

            // Jalankan check saat page load
            checkPrerequisite();

            // --- 2. DROPDOWN MAPEL DINAMIS ---
            $('.ajax-select-kelas').on('change', function() {
                let idKelas = $(this).val();
                let target = $(this).data('target');
                let dropdownMapel = $(target);

                dropdownMapel.html('<option value="">Memuat...</option>');

                if (idKelas) {
                    $.ajax({
                        url: "{{ route('master.sumatif.get_mapel', '') }}/" + idKelas,
                        method: "GET",
                        success: function(res) {
                            let html = '<option value="">-- Pilih Mapel --</option>';
                            res.forEach(item => {
                                html += `<option value="${item.id_mapel}">${item.nama_mapel}</option>`;
                            });
                            dropdownMapel.html(html);
                            // Re-check prerequisite setelah mapel berubah
                            checkPrerequisite();
                        }
                    });
                } else {
                    dropdownMapel.html('<option value="">Pilih Kelas Terlebih Dahulu</option>');
                    $('#season-info-box').hide();
                }
            });

            // Loading Overlay
            $('form').on('submit', function() {
                if($(this).attr('method') === 'POST' && !$(this).hasClass('no-loading')){
                    $('#loadingOverlay').css('display', 'flex');
                }
            });
        });
    </script>
@endsection