@extends('layouts.app')

@section('page-title', 'Catatan Wali Kelas')

@php
    $request = request();
    $tahunSekarang = date('Y');
    
    // Default Values Logic
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
                    
                    {{-- 1. HEADER UTAMA --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            {{-- Dekorasi Icon Besar --}}
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-clipboard-user text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-user-edit me-2"></i> Input Catatan Wali Kelas
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Kelola catatan perkembangan, ekstrakurikuler, dan absensi siswa
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
                        
                        {{-- 2. ALERT SYSTEM --}}
                        <div class="px-4 mt-2">
                            @if (session('success'))
                                <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm"><strong>Sukses!</strong> {!! session('success') !!}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                                </div>
                            @endif
                        </div>

                        {{-- 3. FILTER DATA --}}
                        <div class="p-4 border-bottom bg-gray-100">
                            {{-- Route Input Utama --}}
                            <form method="GET" action="{{ route('walikelas.catatan.input') }}" class="row align-items-end mb-0">
                                <div class="col-md-3 mb-3">
                                    <label for="kelasSelect" class="form-label font-weight-bold text-xs text-uppercase mb-1">Pilih Kelas:</label>
                                    <select name="id_kelas" id="kelasSelect" required class="form-select border ps-2 bg-white" onchange="this.form.submit()">
                                        <option value="">- Pilih Kelas -</option>
                                        @foreach ($kelas as $k)
                                            <option value="{{ $k->id_kelas }}" {{ $request->id_kelas == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label font-weight-bold text-xs text-uppercase mb-1">Semester:</label>
                                    <select name="semester" id="input_semester" required class="form-select border ps-2 bg-white" onchange="this.form.submit()">
                                        @foreach ($semesterList as $sem)
                                            <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label font-weight-bold text-xs text-uppercase mb-1">Tahun Ajaran:</label>
                                    <select name="tahun_ajaran" id="input_tahun_ajaran" required class="form-select border ps-2 bg-white" onchange="this.form.submit()">
                                        @foreach ($tahunAjaranList as $ta)
                                            <option value="{{ $ta }}" {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Hidden Input untuk menjaga siswa terpilih saat ganti semester/tahun --}}
                                @if($request->id_siswa)
                                    <input type="hidden" name="id_siswa" value="{{ $request->id_siswa }}">
                                @endif
                            </form>
                        </div>

                        {{-- 4. BOX INFO SEASON --}}
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

                        {{-- 5. ALERT PREREQUISITE --}}
                        <div id="prerequisite-alert" class="mx-4 mt-3" style="display: none;">
                            <div class="alert d-flex align-items-start border-radius-lg shadow-sm" id="alert-box-container" role="alert">
                                <i id="alert-icon" class="fas fa-lock me-3 mt-1"></i>
                                <div>
                                    <h6 class="alert-heading font-weight-bolder mb-1" id="alert-title">AKSES TERKUNCI</h6>
                                    <div class="mb-0 text-sm" id="prerequisite-message"></div>
                                </div>
                            </div>
                        </div>

                        {{-- 6. KONTEN UTAMA (Hanya Muncul Jika Kelas Dipilih) --}}
                        @if($request->id_kelas)
                            <div class="row px-4 mt-4 pb-5">
                                
                                {{-- TABEL MONITORING SISWA (KIRI) --}}
                                <div class="col-lg-4 mb-4">
                                    <div class="card border shadow-none h-100">
                                        <div class="card-header bg-light border-bottom p-3">
                                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-list-ol me-2"></i> Daftar Siswa</h6>
                                        </div>
                                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                            <table class="table align-items-center mb-0 table-hover table-striped">
                                                <thead class="sticky-top bg-white shadow-sm" style="z-index: 5;">
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Nama Siswa</th>
                                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($siswa as $s)
                                                        @php
                                                            // Cek apakah siswa ini punya data catatan di semester terpilih
                                                            // Asumsi: relasi 'catatan' sudah diload di controller
                                                            $catatanSiswa = $s->catatan->first(); 
                                                            $isActive = $request->id_siswa == $s->id_siswa;
                                                            $hasData = $catatanSiswa && ($catatanSiswa->sakit !== 0 || $catatanSiswa->ijin !== 0 || $catatanSiswa->alpha !== 0 || !empty($catatanSiswa->catatan_wali_kelas));
                                                        @endphp
                                                        <tr class="{{ $isActive ? 'bg-primary-soft border-start border-4 border-primary' : '' }} cursor-pointer" 
                                                            {{-- Route Klik Baris Tabel --}}
                                                            onclick="window.location.href='{{ route('walikelas.catatan.input', array_merge($request->query(), ['id_siswa' => $s->id_siswa])) }}'">
                                                            <td class="text-xs font-weight-bold px-3 py-3 {{ $isActive ? 'text-primary' : 'text-dark' }}">
                                                                {{ $s->nama_siswa }}
                                                                <br>
                                                                <span class="text-xxs text-secondary font-weight-normal">{{ $s->nisn }}</span>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                @if($hasData)
                                                                    <span class="badge badge-sm bg-gradient-success"><i class="fas fa-check"></i></span>
                                                                @else
                                                                    <span class="badge badge-sm bg-secondary opacity-5"><i class="fas fa-minus"></i></span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- FORM INPUT (KANAN) --}}
                                <div class="col-lg-8" id="input-form-container">
                                    @if($request->id_siswa && $siswaTerpilih)
                                        <div class="card border border-primary shadow-sm h-100">
                                            <div class="card-header bg-gradient-primary p-3 d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0 text-white"><i class="fas fa-edit me-2"></i> Input Penilaian: {{ $siswaTerpilih->nama_siswa }}</h6>
                                            </div>
                                            <div class="card-body p-4">
                                                
                                                {{-- A. REFERENSI NILAI EKSKUL (READ ONLY) --}}
                                                {{-- <div class="alert bg-gray-100 border border-light mb-4 p-3 shadow-none rounded-3">
                                                    <h6 class="text-dark text-xs font-weight-bold text-uppercase mb-2">
                                                        <i class="fas fa-star text-warning me-1"></i> Referensi Nilai Ekstrakurikuler (Read Only)
                                                    </h6>
                                                    @if(count($dataEkskulTersimpan) > 0)
                                                        <div class="table-responsive">
                                                            <table class="table table-sm mb-0">
                                                                <thead class="text-xs text-secondary text-uppercase bg-white">
                                                                    <tr>
                                                                        <th class="ps-2">Nama Ekskul</th>
                                                                        <th>Predikat</th>
                                                                        <th>Keterangan</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($dataEkskulTersimpan as $eks)
                                                                        <tr>
                                                                            <td class="text-xs font-weight-bold text-dark ps-2">{{ $eks['nama_ekskul'] }}</td>
                                                                            <td class="text-xs"><span class="badge bg-gradient-info">{{ $eks['predikat'] }}</span></td>
                                                                            <td class="text-xs text-secondary text-wrap fst-italic" style="max-width: 200px;">{{ $eks['keterangan'] ?? '-' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="text-center py-2">
                                                            <p class="text-xs text-secondary mb-0 fst-italic">Belum ada nilai ekstrakurikuler yang masuk dari Guru Pembina.</p>
                                                        </div>
                                                    @endif
                                                </div> --}}

                                                {{-- B. FORM INPUT WALI KELAS --}}
                                                {{-- Route Simpan Data --}}
                                                <form action="{{ route('walikelas.catatan.simpan') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="id_kelas" value="{{ $request->id_kelas }}">
                                                    <input type="hidden" name="id_siswa" value="{{ $request->id_siswa }}">
                                                    <input type="hidden" name="tahun_ajaran" value="{{ $request->tahun_ajaran }}">
                                                    <input type="hidden" name="semester" value="{{ $request->semester }}">

                                                    <div class="row">
                                                        {{-- Kolom Kiri Form: Kokurikuler --}}
                                                        <div class="col-md-6 border-end">
                                                            <h6 class="text-uppercase text-dark text-xs font-weight-bolder opacity-7 mb-2">I. Capaian Kokurikuler</h6>
                                                            <div class="mb-3">
                                                                <label class="form-label text-xs">Pilih Template Capaian (Opsional)</label>
                                                                <select id="select-judul-kok" class="form-select border ps-2 text-sm bg-white">
                                                                    <option value="">-- Pilih Template --</option>
                                                                    @foreach($set_kokurikuler as $kok)
                                                                        <option value="{{ $kok->id_kok }}" data-deskripsi="{{ $kok->deskripsi }}">{{ $kok->judul }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="input-group input-group-outline is-filled mb-3">
                                                                <textarea id="kokurikulerText" name="kokurikuler" class="form-control text-sm" rows="6" placeholder="Deskripsi capaian kokurikuler siswa...">{{ old('kokurikuler', $rapor->kokurikuler ?? $templateKokurikuler) }}</textarea>
                                                            </div>
                                                        </div>

                                                        {{-- Kolom Kanan Form: Absensi & Catatan --}}
                                                        <div class="col-md-6 ps-md-4">
                                                            <h6 class="text-uppercase text-dark text-xs font-weight-bolder opacity-7 mb-2">II. Ketidakhadiran (Hari)</h6>
                                                            <div class="row mb-4">
                                                                <div class="col-4">
                                                                    <div class="input-group input-group-outline is-filled">
                                                                        <label class="form-label">Sakit</label>
                                                                        <input type="number" name="sakit" class="form-control text-center font-weight-bold" value="{{ $rapor->sakit ?? 0 }}" min="0">
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="input-group input-group-outline is-filled">
                                                                        <label class="form-label">Ijin</label>
                                                                        <input type="number" name="ijin" class="form-control text-center font-weight-bold" value="{{ $rapor->ijin ?? 0 }}" min="0">
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="input-group input-group-outline is-filled">
                                                                        <label class="form-label">Alpha</label>
                                                                        <input type="number" name="alpha" class="form-control text-center font-weight-bold" value="{{ $rapor->alpha ?? 0 }}" min="0">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <h6 class="text-uppercase text-dark text-xs font-weight-bolder opacity-7 mb-2">III. Catatan Wali Kelas</h6>
                                                            <div class="input-group input-group-outline is-filled mb-4">
                                                                <textarea name="catatan_wali_kelas" class="form-control text-sm" rows="4" placeholder="Berikan catatan perkembangan akademik dan karakter siswa...">{{ $rapor->catatan_wali_kelas ?? '' }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Tombol Simpan --}}
                                                    <div class="text-end pt-3 border-top">
                                                        <button type="submit" class="btn bg-gradient-primary btn-lg w-100 mb-0 btn-simpan-catatan">
                                                            <i class="fas fa-save me-2"></i> SIMPAN DATA SISWA INI
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        {{-- EMPTY STATE (Jika belum pilih siswa) --}}
                                        <div class="card border border-dashed text-center h-100 d-flex justify-content-center align-items-center bg-gray-100" style="min-height: 400px;">
                                            <div class="py-5">
                                                <i class="fas fa-user-graduate text-secondary mb-3 fa-3x opacity-5"></i>
                                                <h5 class="text-dark font-weight-bold">Pilih Siswa</h5>
                                                <p class="text-secondary text-sm px-5">Klik salah satu nama siswa pada tabel <strong>Daftar Siswa</strong> di sebelah kiri untuk mulai menginput data.</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- EMPTY STATE UTAMA (Belum Pilih Kelas) --}}
                            <div class="p-5 text-center">
                                <i class="fas fa-school text-secondary mb-3 fa-4x opacity-3"></i>
                                <h5 class="text-dark">Silakan Pilih Kelas Terlebih Dahulu</h5>
                                <p class="text-secondary">Gunakan filter di atas untuk menampilkan daftar siswa.</p>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

{{-- MODAL IMPORT --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-normal"><i class="fas fa-file-import me-2 text-success"></i>Import Catatan</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- Route Import --}}
            <form action="{{ route('walikelas.catatan.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info text-white text-xs mb-3">
                        <i class="fas fa-info-circle me-1"></i> Pastikan file Excel sesuai dengan template.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel:</label>
                        <input type="file" name="file_excel" class="form-control border ps-2" required accept=".xlsx, .xls">
                    </div>
                    {{-- Hidden Inputs dari Filter --}}
                    <input type="hidden" name="id_kelas" value="{{ $request->id_kelas }}">
                    <input type="hidden" name="semester" value="{{ $request->semester }}">
                    <input type="hidden" name="tahun_ajaran" value="{{ $request->tahun_ajaran }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link text-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-success btn-proses-import">Upload & Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL DOWNLOAD TEMPLATE --}}
<div class="modal fade" id="downloadTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-normal"><i class="fas fa-file-excel me-2 text-success"></i>Download Template</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- Route Template --}}
            <form action="{{ route('walikelas.catatan.template') }}" method="GET">
                <div class="modal-body">
                    <p class="text-sm text-secondary">Download template Excel untuk pengisian massal.</p>
                    {{-- Hidden Inputs dari Filter --}}
                    <input type="hidden" name="id_kelas" value="{{ $request->id_kelas }}">
                    <input type="hidden" name="semester" value="{{ $request->semester }}">
                    <input type="hidden" name="tahun_ajaran" value="{{ $request->tahun_ajaran }}">
                    
                    @if(!$request->id_kelas)
                        <div class="alert alert-warning text-dark text-xs">Silakan pilih kelas terlebih dahulu di halaman utama.</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link text-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-info" {{ !$request->id_kelas ? 'disabled' : '' }}>Download</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: #e3f2fd !important; }
    .cursor-pointer { cursor: pointer; }
    /* Agar tabel scroll rapi */
    .table-responsive::-webkit-scrollbar { width: 6px; height: 6px; }
    .table-responsive::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 4px; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Template Kokurikuler Auto-Fill
        const selectJudul = document.getElementById('select-judul-kok');
        const kokurikulerText = document.getElementById('kokurikulerText');
        if(selectJudul && kokurikulerText) {
            selectJudul.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const deskripsi = selectedOption.getAttribute('data-deskripsi');
                if (deskripsi) {
                    kokurikulerText.value = deskripsi;
                    kokurikulerText.parentElement.classList.add('is-filled');
                }
            });
        }

        // 2. AJAX Prerequisite Check
        function checkCatatanPrerequisite() {
            let semester = $('#input_semester').val();
            let tahunAjaran = $('#input_tahun_ajaran').val();

            if(semester && tahunAjaran) {
                // Route Check Prerequisite
                $.ajax({
                    url: "{{ route('walikelas.catatan.check_prerequisite') }}",
                    method: "GET",
                    data: {
                        semester: semester,
                        tahun_ajaran: tahunAjaran
                    },
                    success: function(response) {
                        let alertContainer = $('#alert-box-container');
                        let btnSimpan = $('.btn-simpan-catatan');
                        let btnImport = $('.btn-import-trigger');
                        let btnProsesImport = $('.btn-proses-import');

                        // Info Season Box
                        if(response.season) {
                            $('#info-semester').text(response.season.semester);
                            $('#info-tahun').text(response.season.tahun);
                            $('#info-status').text(response.season.status)
                                .attr('class', response.season.is_open ? 'badge badge-sm bg-gradient-success' : 'badge badge-sm bg-gradient-danger');
                            
                            if(response.season.start && response.season.end) {
                                $('#info-date-range').text(response.season.start + ' s/d ' + response.season.end);
                            } else {
                                $('#info-date-range').text('-');
                            }
                            $('#season-info-box').fadeIn();
                        } else {
                            $('#season-info-box').hide();
                        }

                        // Lock Logic
                        if(response.status === 'locked_season') {
                            $('#prerequisite-message').html(response.message);
                            alertContainer.removeClass('alert-warning alert-danger text-dark').addClass('alert-danger text-dark');
                            $('#alert-icon').attr('class', 'fas fa-ban me-3 mt-1 text-danger');
                            $('#alert-title').text('AKSES DITOLAK').addClass('text-danger');
                            
                            $('#prerequisite-alert').slideDown();
                            $('#input-form-container').slideUp(); // Sembunyikan form input
                            
                            // Disable buttons
                            btnSimpan.prop('disabled', true).addClass('opacity-5');
                            btnImport.prop('disabled', true).addClass('opacity-5');
                            btnProsesImport.prop('disabled', true);
                        } else {
                            // Safe
                            $('#prerequisite-alert').slideUp();
                            $('#input-form-container').slideDown();
                            btnSimpan.prop('disabled', false).removeClass('opacity-5');
                            btnImport.prop('disabled', false).removeClass('opacity-5');
                            btnProsesImport.prop('disabled', false);
                        }
                    }
                });
            }
        }

        // Run check on load
        checkCatatanPrerequisite();
    });
</script>
@endsection