@extends('layouts.app') 

@section('page-title', 'Rekap Finalisasi Nilai')

{{-- CSS KHUSUS: CLEAN & READABLE --}}
<style>
    /* Table Header: Gradient & White Text (Jelas & Tegas) */
    .th-header { 
        color: #fff !important; 
        text-transform: uppercase; 
        font-size: 0.7rem !important; 
        letter-spacing: 0.5px;
        vertical-align: middle !important;
        border-right: 1px solid rgba(255,255,255,0.2) !important;
        font-weight: 700 !important;
    }
    
    /* Input Styling */
    .input-nilai {
        border: 1px solid #d2d6da !important;
        border-radius: 4px !important;
        font-weight: 600 !important;
        color: #344767 !important;
        text-align: center;
    }
    .input-nilai:focus {
        border-color: #e91e63 !important; 
        box-shadow: 0 0 0 2px rgba(233, 30, 99, 0.1) !important;
        outline: none;
    }
    .input-nilai:disabled {
        background-color: #e9ecef !important;
        color: #6c757d !important;
    }
    textarea.form-control:disabled {
        background-color: #e9ecef !important;
        color: #6c757d !important;
    }
    .bg-read-only { background-color: #f8f9fa !important; }
</style>

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-lg border-0">
                    
                    {{-- HEADER PAGE --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-white text-capitalize ps-3 mb-0">
                                <i class="fas fa-file-signature me-2"></i> Rekap & Finalisasi Nilai Akhir
                            </h6>
                            <div class="pe-3">
                                @if(isset($seasonOpen) && $seasonOpen)
                                    <span class="badge bg-white text-dark shadow-sm">
                                        <i class="fas fa-lock-open me-1 text-success"></i> Input Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger text-white border border-white shadow-sm">
                                        <i class="fas fa-lock me-1"></i> Terkunci
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2">
                        
                        {{-- ALERT --}}
                        @if (session('success'))
                            <div class="alert alert-success text-white mx-4 font-weight-bold shadow-sm"><i class="fas fa-check-circle me-2"></i> {!! session('success') !!}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger text-white mx-4 font-weight-bold shadow-sm"><i class="fas fa-times-circle me-2"></i> {{ session('error') }}</div>
                        @endif

                        {{-- BOX INFO SEASON --}}
                        <div class="mx-4 mt-3">
                            <div class="card bg-gray-100 border-0 shadow-none">
                                <div class="card-body p-3 d-flex align-items-center flex-wrap">
                                    <span class="text-xs font-weight-bold text-uppercase text-secondary me-3">
                                        <i class="fas fa-info-circle me-1"></i> Periode Akademik:
                                    </span>
                                    
                                    @if(isset($seasonDetail) && $seasonDetail)
                                        <div class="d-flex align-items-center me-4">
                                            <span class="badge badge-sm bg-gradient-dark me-2">{{ $seasonDetail->semester == 1 ? 'GANJIL' : 'GENAP' }}</span>
                                            <span class="badge badge-sm bg-gradient-dark me-2">{{ $seasonDetail->tahun_ajaran }}</span>
                                            @if($seasonDetail->is_open)
                                                <span class="badge badge-sm bg-gradient-success">OPEN</span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-secondary">CLOSED</span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center border-start ps-4">
                                            <i class="fas fa-clock text-dark me-2"></i>
                                            <span class="text-xs font-weight-bold text-secondary me-2">Batas Waktu:</span>
                                            <span class="text-xs text-dark font-weight-bolder">
                                                {{ \Carbon\Carbon::parse($seasonDetail->start_date)->format('d M Y') }} 
                                                <span class="text-secondary mx-1">-</span> 
                                                {{ \Carbon\Carbon::parse($seasonDetail->end_date)->format('d M Y') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-danger text-xs font-weight-bold">
                                            {{ $seasonMessage ?? 'Jadwal Season belum diatur.' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- FILTER AREA --}}
                        <div class="p-4 border-bottom">
                            <form action="{{ route('master.rekap.index') }}" method="GET" class="row align-items-end mb-0">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs fw-bold text-secondary text-uppercase">Kelas</label>
                                    <select name="id_kelas" class="form-select ajax-select-kelas ps-2 border" onchange="this.form.submit()">
                                        <option value="">- Pilih Kelas -</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id_kelas }}" {{ $id_kelas == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs fw-bold text-secondary text-uppercase">Mata Pelajaran</label>
                                    <select name="id_mapel" class="form-select ps-2 border" onchange="this.form.submit()" {{ empty($mapelList) ? 'disabled' : '' }}>
                                        <option value="">- Pilih Mapel -</option>
                                        @foreach($mapelList as $m)
                                            <option value="{{ $m->id_mapel }}" {{ $id_mapel == $m->id_mapel ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs fw-bold text-secondary text-uppercase">Semester</label>
                                    <select name="semester" class="form-select ps-2 border" onchange="this.form.submit()">
                                        @foreach($semesterList as $smt)
                                            <option value="{{ $smt }}" {{ $semesterRaw == $smt ? 'selected' : '' }}>{{ $smt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs fw-bold text-secondary text-uppercase">Tahun Ajaran</label>
                                    <select name="tahun_ajaran" class="form-select ps-2 border" onchange="this.form.submit()">
                                        @foreach($tahunAjaranList as $ta)
                                            <option value="{{ $ta }}" {{ $tahun_ajaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="d-none"></button>
                            </form>
                        </div>

                        {{-- NOTIFIKASI BOBOT --}}
                        <div class="mx-4 mt-3">
                            <div class="alert bg-gradient-warning text-white shadow-sm border-radius-lg p-3" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="me-3"><i class="fas fa-balance-scale fa-2x"></i></div>
                                    <div>
                                        <span class="font-weight-bold text-uppercase text-xs opacity-9">Komposisi Penilaian (Bobot):</span>
                                        <div class="mt-1 d-flex flex-wrap align-items-center gap-2">
                                            @if(isset($bobotInfo) && $bobotInfo)
                                                <span class="badge bg-white text-dark shadow-sm">Sumatif: {{ $bobotInfo->bobot_sumatif }}%</span>
                                                <span class="badge bg-white text-dark shadow-sm">Project: {{ $bobotInfo->bobot_project }}%</span>
                                                <span class="text-xs fw-bold text-white ms-2">(Min. Input Sumatif: {{ $bobotInfo->jumlah_sumatif }})</span>
                                            @else
                                                <span class="badge bg-danger text-white">Bobot belum diatur</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TABEL UTAMA (SELALU MUNCUL JIKA KELAS DIPILIH) --}}
                        <div class="p-4 pt-3">
                            @if(!empty($dataSiswa))
                                <form action="{{ route('master.rekap.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_kelas" value="{{ $id_kelas }}">
                                    <input type="hidden" name="id_mapel" value="{{ $id_mapel }}">
                                    <input type="hidden" name="semester" value="{{ $semesterRaw }}">
                                    <input type="hidden" name="tahun_ajaran" value="{{ $tahun_ajaran }}">

                                    <div class="table-responsive p-0">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    {{-- IDENTITAS --}}
                                                    <th rowspan="2" class="th-header bg-gradient-secondary text-center" style="width: 5%">No</th>
                                                    <th rowspan="2" class="th-header bg-gradient-secondary ps-3" style="width: 20%">Siswa</th>
                                                    
                                                    {{-- SUMATIF --}}
                                                    <th colspan="7" class="th-header bg-gradient-info text-center">SUMATIF (PENGETAHUAN)</th>
                                                    
                                                    {{-- PROJECT --}}
                                                    <th colspan="2" class="th-header bg-gradient-success text-center">PROJECT (P5)</th>
                                                    
                                                    {{-- AKHIR --}}
                                                    <th rowspan="2" class="th-header bg-gradient-primary text-center">AKHIR</th>
                                                    <th rowspan="2" class="th-header bg-gradient-primary text-center">CAPAIAN KOMPETENSI</th>
                                                    <th rowspan="2" class="th-header bg-gradient-secondary text-center">STATUS</th>
                                                </tr>
                                                <tr>
                                                    {{-- Sub Header --}}
                                                    <th class="th-header bg-gradient-info text-center opacity-8">S1</th>
                                                    <th class="th-header bg-gradient-info text-center opacity-8">S2</th>
                                                    <th class="th-header bg-gradient-info text-center opacity-8">S3</th>
                                                    <th class="th-header bg-gradient-info text-center opacity-8">S4</th>
                                                    <th class="th-header bg-gradient-info text-center opacity-8">S5</th>
                                                    <th class="th-header bg-gradient-info text-center font-weight-bolder">RATA</th>
                                                    <th class="th-header bg-gradient-info text-center font-weight-bolder">BOBOT</th>

                                                    <th class="th-header bg-gradient-success text-center opacity-8">NILAI</th>
                                                    <th class="th-header bg-gradient-success text-center font-weight-bolder">BOBOT</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dataSiswa as $i => $s)
                                                <tr class="border-bottom hover:bg-gray-100">
                                                    <td class="text-center text-sm text-secondary font-weight-bold">{{ $i + 1 }}</td>
                                                    <td class="px-3">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $s->nama_siswa }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ $s->nisn }}</p>
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="text-center text-xs text-secondary font-weight-bold">{{ $s->s1 }}</td>
                                                    <td class="text-center text-xs text-secondary font-weight-bold">{{ $s->s2 }}</td>
                                                    <td class="text-center text-xs text-secondary font-weight-bold">{{ $s->s3 }}</td>
                                                    <td class="text-center text-xs text-secondary font-weight-bold">{{ $s->s4 }}</td>
                                                    <td class="text-center text-xs text-secondary font-weight-bold">{{ $s->s5 }}</td>
                                                    
                                                    <td class="text-center text-sm font-weight-bolder text-info bg-read-only border-start">{{ $s->rata_s }}</td>
                                                    <td class="text-center text-sm font-weight-bolder text-dark bg-read-only">{{ $s->bobot_s_v }}</td>
                                                    
                                                    <td class="text-center text-sm font-weight-bold text-success bg-read-only border-start">{{ $s->nilai_p }}</td>
                                                    <td class="text-center text-sm font-weight-bolder text-dark bg-read-only">{{ $s->bobot_p_v }}</td>
                                                    
                                                    <td class="align-middle text-center p-2 border-start">
                                                        <div class="input-group input-group-outline input-group-sm justify-content-center">
                                                            <input type="number" name="data[{{ $s->id_siswa }}][nilai_akhir]" class="form-control input-nilai" value="{{ $s->nilai_akhir }}" data-rumus="{{ $s->na_rumus }}" min="0" max="100" style="max-width: 70px;" {{ !$seasonOpen ? 'disabled' : '' }}>
                                                        </div>
                                                    </td>

                                                    <td class="align-middle p-2">
                                                        <div class="input-group input-group-outline">
                                                            <textarea name="data[{{ $s->id_siswa }}][deskripsi]" class="form-control text-secondary" rows="2" style="min-width: 220px;" {{ !$seasonOpen ? 'disabled' : '' }}>{{ $s->deskripsi }}</textarea>
                                                        </div>
                                                    </td>

                                                    <td class="text-center align-middle">
                                                        @if($s->is_saved)
                                                            <span class="badge badge-sm bg-gradient-success">TERSIMPAN</span>
                                                        @else
                                                            <span class="badge badge-sm bg-gradient-secondary">DRAFT</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4 align-items-center">
                                        <div class="text-xs text-secondary">
                                            @if(!$seasonOpen)
                                                <i class="fas fa-lock me-1 text-danger"></i> <span class="text-danger font-weight-bold">Mode Baca Saja (Read Only)</span> - Season Terkunci.
                                            @else
                                                <i class="fas fa-info-circle me-1"></i> Nilai Akhir otomatis dihitung.
                                            @endif
                                        </div>
                                        <div class="d-flex gap-2">
                                            {{-- TOMBOL RESET (Hanya jika aktif) --}}
                                            @if($seasonOpen)
                                            <button type="button" class="btn btn-outline-dark btn-sm mb-0" onclick="resetKeRumus()">
                                                <i class="fas fa-undo me-1"></i> Reset Original
                                            </button>
                                            @endif

                                            {{-- TOMBOL SIMPAN (Disable jika terkunci) --}}
                                            @if($seasonOpen)
                                                <button type="submit" class="btn bg-gradient-primary btn-lg mb-0 shadow-lg">
                                                    <i class="fas fa-save me-2"></i> SIMPAN PERUBAHAN
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-secondary btn-lg mb-0" disabled>
                                                    <i class="fas fa-lock me-2"></i> TERKUNCI
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                </form>
                            @elseif($id_kelas && $id_mapel)
                                <div class="text-center py-5 border rounded bg-gray-50 border-dashed m-3">
                                    <i class="fas fa-user-slash text-secondary mb-3 fa-3x opacity-5"></i>
                                    <h5 class="text-secondary font-weight-bold">Tidak Ada Data Siswa</h5>
                                    <p class="text-secondary text-sm mb-0">Pastikan siswa sudah terdaftar di kelas ini.</p>
                                </div>
                            @else
                                <div class="text-center py-5 border rounded bg-gray-50 border-dashed m-3">
                                    <i class="fas fa-filter text-primary mb-3 fa-3x opacity-5"></i>
                                    <h5 class="text-dark font-weight-bold">Filter Data Diperlukan</h5>
                                    <p class="text-secondary text-sm mb-0">Silakan pilih <strong>Kelas</strong> dan <strong>Mata Pelajaran</strong> terlebih dahulu.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

<script>
    function resetKeRumus() {
        if(confirm('Yakin ingin mereset semua nilai editan kembali ke perhitungan rumus otomatis?')) {
            document.querySelectorAll('.input-nilai').forEach(input => {
                input.value = input.getAttribute('data-rumus');
            });
        }
    }
</script>
@endsection