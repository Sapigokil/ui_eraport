@extends('layouts.app')

@section('page-title', 'Catatan Wali Kelas')

@php
    $request = request();
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
    $dataEkskul = $dataEkskulTersimpan ?? [];
@endphp

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- HEADER --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-white text-capitalize ps-3 mb-0">
                                <i class="fas fa-clipboard-check me-2"></i> Input Catatan & Absensi Wali Kelas
                            </h6>
                            <div class="pe-3">
                                <button class="btn bg-gradient-light text-dark btn-sm mb-0 me-2" data-bs-toggle="modal" data-bs-target="#downloadTemplateModal">
                                    <i class="fas fa-file-excel me-1"></i> Template
                                </button>
                                <button class="btn bg-gradient-success btn-sm mb-0 btn-import-trigger" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="fas fa-file-import me-1"></i> Import
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2">
                        @if (session('success'))
                            <div class="alert bg-gradient-success mx-4 alert-dismissible text-white fade show" role="alert">
                                <span class="text-sm"><strong>Sukses!</strong> {!! session('success') !!}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white fade show" role="alert">
                                <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        {{-- FILTER DATA (CLEAN LAYOUT) --}}
                        <div class="p-4 border-bottom">
                            <form method="GET" action="{{ route('walikelas.catatan.input') }}" class="row align-items-end mb-0">
                                <div class="col-md-3 mb-3">
                                    <label for="kelasSelect" class="form-label">Kelas:</label>
                                    <select name="id_kelas" id="kelasSelect" required class="form-select ajax-select-kelas" onchange="this.form.submit()">
                                        <option value="">Pilih Kelas</option>
                                        @foreach ($kelas as $k)
                                            <option value="{{ $k->id_kelas }}" {{ $request->id_kelas == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="siswaSelect" class="form-label">Nama Siswa:</label>
                                    <select name="id_siswa" id="siswaSelect" required class="form-select" {{ !request('id_kelas') ? 'disabled' : '' }} onchange="this.form.submit()">
                                        <option value="">Pilih Siswa</option>
                                        @foreach ($siswa as $s)
                                            <option value="{{ $s->id_siswa }}" {{ $request->id_siswa == $s->id_siswa ? 'selected' : '' }}>{{ $s->nama_siswa }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Semester:</label>
                                    <select name="semester" id="input_semester" required class="form-select" onchange="this.form.submit()">
                                        @foreach ($semesterList as $sem)
                                            <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Tahun Ajaran:</label>
                                    <select name="tahun_ajaran" id="input_tahun_ajaran" required class="form-select" onchange="this.form.submit()">
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

                        {{-- INPUT FORM AREA --}}
                        <div class="p-4" id="input-form-container">
                            @if (!$request->id_kelas || !$request->id_siswa)
                                <div class="text-center py-5 border rounded bg-gray-100">
                                    <i class="fas fa-user-graduate text-secondary mb-3 fa-2x"></i>
                                    <p class="text-secondary mb-0">Silakan pilih <strong>Kelas</strong> dan <strong>Siswa</strong> untuk mulai mengisi catatan.</p>
                                </div>
                            @else
                                <form action="{{ route('walikelas.catatan.simpan') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_kelas" value="{{ $request->id_kelas }}">
                                    <input type="hidden" name="id_siswa" value="{{ $request->id_siswa }}">
                                    <input type="hidden" name="tahun_ajaran" value="{{ $request->tahun_ajaran }}">
                                    <input type="hidden" name="semester" value="{{ $request->semester }}">

                                    <div class="row">
                                        {{-- SISI KIRI: PENGEMBANGAN DIRI --}}
                                        <div class="col-lg-7 border-end">
                                            <h6 class="text-uppercase text-primary text-xs font-weight-bolder opacity-7 mb-3 text-start">I. Aspek Pengembangan Diri</h6>
                                            
                                            <div class="mb-4 text-start">
                                                <label class="form-label font-weight-bold text-xs text-uppercase">1. Kokurikuler (Tingkat {{ $siswaTerpilih->kelas->tingkat ?? '' }})</label>
                                                <div class="input-group input-group-outline mb-2">
                                                    <select id="select-judul-kok" class="form-select border ps-2 text-sm">
                                                        <option value="">-- Pilih Template Capaian --</option>
                                                        @foreach($set_kokurikuler as $kok)
                                                            <option value="{{ $kok->id_kok }}" data-deskripsi="{{ $kok->deskripsi }}">{{ $kok->judul }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="input-group input-group-outline is-filled">
                                                    <textarea id="kokurikulerText" name="kokurikuler" rows="5" class="form-control text-sm" placeholder="Pilih template atau isi manual...">{{ old('kokurikuler', $rapor->kokurikuler ?? '') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="mb-4 text-start">
                                                <label class="form-label font-weight-bold text-dark d-flex align-items-center text-xs text-uppercase">
                                                    <i class="fas fa-star text-warning me-2"></i> 2. Ekstrakurikuler (Maks 3)
                                                </label>
                                                <div class="table-responsive border border-radius-lg shadow-sm">
                                                    <table class="table align-items-center mb-0">
                                                        <thead class="bg-dark text-white text-center">
                                                            <tr>
                                                                <th class="text-xxs font-weight-bolder text-uppercase ps-3 py-3 text-white">Ekstrakurikuler</th>
                                                                <th class="text-xxs font-weight-bolder text-uppercase py-3 text-white" width="150px">Predikat</th>
                                                                <th class="text-xxs font-weight-bolder text-uppercase py-3 text-white">Keterangan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white">
                                                            @for ($i = 0; $i < 3; $i++)
                                                                @php
                                                                    $savedId = $dataEkskul[$i]['id_ekskul'] ?? '';
                                                                    $savedPred = $dataEkskul[$i]['predikat'] ?? '';
                                                                    $savedKet = $dataEkskul[$i]['keterangan'] ?? '';
                                                                    $rowBg = ($i % 2 == 0) ? 'bg-white' : 'bg-gray-50';
                                                                @endphp
                                                                <tr class="{{ $rowBg }}">
                                                                    <td class="p-2 ps-3">
                                                                        <select name="ekskul[{{ $i }}][id_ekskul]" class="form-select border-0 text-xs font-weight-bold">
                                                                            <option value="">-- Pilih --</option>
                                                                            @foreach($ekskul as $e)
                                                                                <option value="{{ $e->id_ekskul }}" {{ $savedId == $e->id_ekskul ? 'selected' : '' }}>{{ $e->nama_ekskul }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td class="p-2">
                                                                        <select name="ekskul[{{ $i }}][predikat]" class="form-select border-0 text-xs font-weight-bold text-primary">
                                                                            <option value="">-- Pilih --</option>
                                                                            @foreach(['Sangat Baik','Baik','Cukup','Kurang'] as $p)
                                                                                <option value="{{ $p }}" {{ $savedPred == $p ? 'selected' : '' }}>{{ $p }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td class="p-2">
                                                                        <input type="text" name="ekskul[{{ $i }}][keterangan]" class="form-control form-control-sm border-0 bg-transparent text-xs" value="{{ $savedKet }}">
                                                                    </td>
                                                                </tr>
                                                            @endfor
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- SISI KANAN: ABSENSI & CATATAN --}}
                                        <div class="col-lg-5 text-start">
                                            <h6 class="text-uppercase text-primary text-xs font-weight-bolder opacity-7 mb-3">II. Absensi & Catatan Wali</h6>
                                            <div class="bg-light p-3 border-radius-lg mb-4">
                                                <label class="text-xs font-weight-bold text-dark text-uppercase">Ketidakhadiran (Hari)</label>
                                                <div class="row text-center mt-2">
                                                    <div class="col-4">
                                                        <label class="text-xxs">Sakit</label>
                                                        <input type="number" name="sakit" class="form-control form-control-sm border text-center" value="{{ $rapor->sakit ?? 0 }}">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="text-xxs">Ijin</label>
                                                        <input type="number" name="ijin" class="form-control form-control-sm border text-center" value="{{ $rapor->ijin ?? 0 }}">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="text-xxs">Alpha</label>
                                                        <input type="number" name="alpha" class="form-control form-control-sm border text-center" value="{{ $rapor->alpha ?? 0 }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label font-weight-bold text-dark text-xs text-uppercase">Catatan Wali Kelas</label>
                                                <div class="input-group input-group-outline is-filled">
                                                    <textarea name="catatan_wali_kelas" class="form-control text-sm" rows="6" placeholder="Tulis catatan perkembangan siswa di sini...">{{ $rapor->catatan_wali_kelas ?? '' }}</textarea>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn bg-gradient-success w-100 py-2 btn-simpan-catatan">
                                                <i class="fas fa-save me-2 text-xs"></i> Simpan Seluruh Data
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

{{-- MODAL IMPORT (READONLY FILTER) --}}
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-gray-100">
                <h6 class="modal-title font-weight-bolder text-dark"><i class="fas fa-file-import text-success me-2"></i> Import Catatan Wali</h6>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('walikelas.catatan.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-4 text-start">
                    <div class="mb-3">
                        <label class="form-label">Pilih Kelas</label>
                        {{-- Readonly: Mengikuti Filter Utama --}}
                        <select name="id_kelas" class="form-select bg-light border ps-2 text-sm" style="pointer-events: none;" tabindex="-1">
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row text-start mb-3">
                        <div class="col-6">
                            <label class="form-label">Semester</label>
                            <input type="text" name="semester" value="{{ request('semester', $defaultSemester) }}" class="form-control bg-light border ps-2 text-sm" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" value="{{ request('tahun_ajaran', $defaultTahunAjaran) }}" class="form-control bg-light border ps-2 text-sm" readonly>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Pilih File Excel</label>
                        <input type="file" name="file_excel" class="form-control border ps-2 text-sm" required accept=".xlsx, .xls">
                    </div>
                </div>
                <div class="modal-footer bg-gray-100">
                    <button type="button" class="btn btn-sm btn-white mb-0" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm bg-gradient-success mb-0 btn-proses-import">Upload & Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT AJAX CHECK & INTERACTION --}}
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
            // Note: Catatan check only needs semester/TA to verify season status
            let semester = $('#input_semester').val();
            let tahunAjaran = $('#input_tahun_ajaran').val();

            if(semester && tahunAjaran) {
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