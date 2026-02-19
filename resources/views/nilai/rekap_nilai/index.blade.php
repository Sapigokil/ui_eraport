@extends('layouts.app') 

@section('page-title', 'Rekap Finalisasi Nilai Guru')

{{-- CSS KHUSUS: CLEAN & READABLE --}}
<style>
    /* Table Header */
    .th-header { 
        color: #fff !important; 
        text-transform: uppercase; 
        font-size: 0.7rem !important; 
        letter-spacing: 0.5px;
        vertical-align: middle !important;
        border-right: 1px solid rgba(255,255,255,0.2) !important;
        font-weight: 700 !important;
    }
    
    /* Background Readonly Columns */
    .bg-read-only { background-color: #f8f9fa !important; }
    
    /* Capaian Text Styling */
    .text-capaian {
        font-size: 0.75rem !important;
        line-height: 1.5 !important;
        color: #344767 !important;
        white-space: normal !important; 
        text-align: justify;
    }

    /* Clickable Cells */
    .td-clickable {
        padding: 0 !important; 
        vertical-align: middle !important;
    }
    
    .cell-link {
        display: block;
        width: 100%;
        height: 100%;
        padding: 10px 5px; 
        text-decoration: none;
        color: inherit;
        font-weight: bold;
        transition: all 0.2s ease;
    }

    .cell-link:hover {
        background-color: #e3f2fd; 
        color: #1976d2 !important;
        cursor: pointer;
    }

    /* Custom Border for Gatekeeper */
    .border-start-5 {
        border-left: 5px solid !important;
    }
</style>

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- 1. CARD FILTER --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-body p-3">
                <form action="{{ route('master.rekap.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Pilih Kelas</label>
                        <select name="id_kelas" class="form-select border-secondary ps-2" onchange="this.form.submit()">
                            <option value="">- Pilih Kelas -</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ $id_kelas == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Mata Pelajaran</label>
                        <select name="id_mapel" class="form-select border-secondary ps-2" onchange="this.form.submit()" {{ empty($mapelList) ? 'disabled' : '' }}>
                            <option value="">- Pilih Mapel -</option>
                            @foreach($mapelList as $m)
                                <option value="{{ $m->id_mapel }}" {{ $id_mapel == $m->id_mapel ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Semester</label>
                        <select name="semester" class="form-select border-secondary ps-2" onchange="this.form.submit()">
                            @foreach($semesterList as $smt)
                                <option value="{{ $smt }}" {{ $semesterRaw == $smt ? 'selected' : '' }}>{{ $smt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Tahun Ajaran</label>
                        <select name="tahun_ajaran" class="form-select border-secondary ps-2" onchange="this.form.submit()">
                            @foreach($tahunAjaranList as $ta)
                                <option value="{{ $ta }}" {{ $tahun_ajaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if(!empty($dataSiswa))
            
            {{-- 2. HEADER BANNER --}}
            @php
                $selectedKelas = $kelas->firstWhere('id_kelas', $id_kelas);
                $selectedMapel = collect($mapelList)->firstWhere('id_mapel', $id_mapel);
                
                $totalSiswa = count($dataSiswa);
                $sudahSimpan = collect($dataSiswa)->where('is_saved', 1)->count();
                $persenSimpan = $totalSiswa > 0 ? round(($sudahSimpan / $totalSiswa) * 100) : 0;
            @endphp

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0 bg-gradient-primary overflow-hidden position-relative">
                        <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                            <i class="fas fa-file-signature text-white" style="font-size: 10rem;"></i>
                        </div>
                        <div class="card-body p-4 position-relative z-index-1">
                            <div class="row align-items-center text-white">
                                <div class="col-md-8">
                                    <h3 class="text-white font-weight-bold mb-1">{{ $selectedKelas->nama_kelas ?? 'Kelas Tidak Dikenal' }}</h3>
                                    <p class="text-white opacity-8 mb-2">
                                        <i class="fas fa-book-open me-2"></i> {{ $selectedMapel->nama_mapel ?? 'Mapel Tidak Dikenal' }}
                                    </p>
                                    
                                    <span class="badge border border-white text-white fw-bold bg-transparent">
                                        Semester {{ $semesterRaw }} - {{ $tahun_ajaran }}
                                    </span>
                                </div>
                                <div class="col-md-4 text-end mt-4 mt-md-0">
                                    <div class="d-flex justify-content-md-end justify-content-between gap-4">
                                        <div class="text-center">
                                            <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Status Finalisasi</span>
                                            <h4 class="text-white mb-0">{{ $sudahSimpan }} <span class="text-sm fw-normal opacity-8">/ {{ $totalSiswa }} Siswa</span></h4>
                                            <div class="progress mt-2 mx-auto" style="height: 4px; width: 120px; background: rgba(255,255,255,0.3);">
                                                <div class="progress-bar bg-white" role="progressbar" style="width: {{ $persenSimpan }}%"></div>
                                            </div>
                                            <small class="text-white opacity-8 text-xxs mt-1">{{ $persenSimpan }}% Tersimpan</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- NEW: INFO BOBOT NILAI --}}
            @if($bobotInfo)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border border-light bg-gray-50">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-md me-3">
                                <i class="fas fa-balance-scale text-white text-lg opacity-10"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-sm font-weight-bold text-dark">Informasi Bobot Penilaian (Semester {{ $semesterRaw }})</h6>
                                <p class="text-xs text-secondary mb-0">
                                    Hitungan Nilai Akhir (NA) otomatis dikalkulasi berdasarkan: 
                                    <b>{{ $bobotInfo->bobot_sumatif }}%</b> Rata-rata Sumatif Harian <i class="fas fa-plus mx-1 text-muted text-xxs"></i> 
                                    <b>{{ $bobotInfo->bobot_project }}%</b> Nilai Akhir Semester (Sumatif Akhir).
                                    <span class="d-block mt-1 text-info font-weight-bold">
                                        <i class="fas fa-info-circle me-1"></i> Anda wajib menginput minimal <b>{{ $bobotInfo->jumlah_sumatif }} Nilai Sumatif Harian</b> (S1, S2, dst).
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 3. AREA AKSI / TRIGGER (GATEKEEPER STYLE BERLAPIS) --}}
            <div class="row mb-4">
                <div class="col-12">
                    @php
                        // Logika Hierarki Status
                        $isLocked = $isLocked ?? false;
                        $canSave  = false;

                        if (!$seasonOpen) {
                            $gateColor = 'danger';
                            $gateIcon = 'fas fa-door-closed';
                            $statusMessage = $seasonMessage; // Dari Controller (Jadwal Ditutup)
                        } elseif ($isLocked) {
                            $gateColor = 'warning';
                            $gateIcon = 'fas fa-lock';
                            $statusMessage = 'Data telah dikunci (Status: Final/Cetak). Hubungi Wali Kelas jika ada revisi nilai.';
                        } else {
                            $canSave = true; // Hanya true jika season open DAN tidak dilock
                            $gateColor = 'primary';
                            $gateIcon = 'fas fa-door-open';
                            $statusMessage = 'Sistem siap melakukan kalkulasi dan simpan snapshot nilai akhir.';
                        }
                    @endphp

                    <div class="card shadow-sm border border-start-5 border-{{ $gateColor }}">
                        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-3">
                            <div class="pe-md-4 mb-3 mb-md-0">
                                <h5 class="mb-1 text-dark font-weight-bold">
                                    <i class="fas fa-check-circle me-2 text-{{ $gateColor }}"></i> Aksi Finalisasi
                                </h5>
                                <p class="text-sm text-{{ $gateColor }} font-weight-bold mb-0">
                                    <i class="{{ $gateIcon }} me-1"></i> {{ $statusMessage }}
                                </p>
                            </div>
                            <div>
                                @if($canSave)
                                    <button type="button" onclick="confirmSimpan()" class="btn btn-primary bg-gradient-primary btn-lg mb-0 shadow-sm w-100">
                                        <i class="fas fa-save me-2"></i> SIMPAN FINALISASI
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary btn-lg mb-0 cursor-not-allowed w-100 opacity-6" disabled>
                                        <i class="{{ $gateIcon }} me-2"></i> TERKUNCI
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. TABEL REKAP --}}
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border">
                        <div class="card-body p-0">
                            <form id="formSimpanRekap" action="{{ route('master.rekap.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id_kelas" value="{{ $id_kelas }}">
                                <input type="hidden" name="id_mapel" value="{{ $id_mapel }}">
                                <input type="hidden" name="semester" value="{{ $semesterRaw }}">
                                <input type="hidden" name="tahun_ajaran" value="{{ $tahun_ajaran }}">

                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="th-header bg-gradient-secondary text-center" style="width: 5%">No</th>
                                                <th rowspan="2" class="th-header bg-gradient-secondary ps-3" style="width: 20%">Siswa</th>
                                                <th colspan="7" class="th-header bg-gradient-info text-center">NILAI SUMATIF</th>
                                                <th colspan="2" class="th-header bg-gradient-success text-center">NILAI PROJECT</th>
                                                <th rowspan="2" class="th-header bg-gradient-primary text-center">NILAI AKHIR</th>
                                                <th rowspan="2" class="th-header bg-gradient-primary text-center" style="min-width: 250px;">CAPAIAN KOMPETENSI</th>
                                                <th rowspan="2" class="th-header bg-gradient-secondary text-center">STATUS</th>
                                            </tr>
                                            <tr>
                                                <th class="th-header bg-gradient-info text-center opacity-8">S1</th>
                                                <th class="th-header bg-gradient-info text-center opacity-8">S2</th>
                                                <th class="th-header bg-gradient-info text-center opacity-8">S3</th>
                                                <th class="th-header bg-gradient-info text-center opacity-8">S4</th>
                                                <th class="th-header bg-gradient-info text-center opacity-8">S5</th>
                                                <th class="th-header bg-gradient-info text-center">RATA</th>
                                                <th class="th-header bg-gradient-info text-center">BOBOT</th>
                                                <th class="th-header bg-gradient-success text-center opacity-8">PROJ</th>
                                                <th class="th-header bg-gradient-success text-center">BOBOT</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dataSiswa as $i => $s)
                                            <tr class="border-bottom">
                                                <td class="text-center text-sm font-weight-bold">{{ $i + 1 }}</td>
                                                <td class="px-3">
                                                    <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $s->nama_siswa }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $s->nisn }}</p>
                                                </td>
                                                <td class="text-center text-xs td-clickable">
                                                    <a href="{{ route('master.sumatif.s1', ['id_kelas' => $id_kelas, 'id_mapel' => $id_mapel, 'semester' => $semesterRaw, 'tahun_ajaran' => $tahun_ajaran]) }}" class="cell-link text-secondary" target="_blank">{{ $s->s1 }}</a>
                                                </td>
                                                <td class="text-center text-xs td-clickable">
                                                    <a href="{{ route('master.sumatif.s2', ['id_kelas' => $id_kelas, 'id_mapel' => $id_mapel, 'semester' => $semesterRaw, 'tahun_ajaran' => $tahun_ajaran]) }}" class="cell-link text-secondary" target="_blank">{{ $s->s2 }}</a>
                                                </td>
                                                <td class="text-center text-xs td-clickable">
                                                    <a href="{{ route('master.sumatif.s3', ['id_kelas' => $id_kelas, 'id_mapel' => $id_mapel, 'semester' => $semesterRaw, 'tahun_ajaran' => $tahun_ajaran]) }}" class="cell-link text-secondary" target="_blank">{{ $s->s3 }}</a>
                                                </td>
                                                <td class="text-center text-xs td-clickable">
                                                    <a href="{{ route('master.sumatif.s4', ['id_kelas' => $id_kelas, 'id_mapel' => $id_mapel, 'semester' => $semesterRaw, 'tahun_ajaran' => $tahun_ajaran]) }}" class="cell-link text-secondary" target="_blank">{{ $s->s4 }}</a>
                                                </td>
                                                <td class="text-center text-xs td-clickable">
                                                    <a href="{{ route('master.sumatif.s5', ['id_kelas' => $id_kelas, 'id_mapel' => $id_mapel, 'semester' => $semesterRaw, 'tahun_ajaran' => $tahun_ajaran]) }}" class="cell-link text-secondary" target="_blank">{{ $s->s5 }}</a>
                                                </td>
                                                <td class="text-center text-sm font-weight-bolder text-info bg-read-only border-start">{{ $s->rata_s }}</td>
                                                <td class="text-center text-sm font-weight-bolder text-dark bg-read-only">{{ $s->bobot_s_v }}</td>
                                                <td class="text-center text-sm font-weight-bold td-clickable border-start">
                                                    <a href="{{ route('master.project.index', ['id_kelas' => $id_kelas, 'id_mapel' => $id_mapel, 'semester' => $semesterRaw, 'tahun_ajaran' => $tahun_ajaran]) }}" class="cell-link text-success" target="_blank">{{ $s->nilai_p }}</a>
                                                </td>
                                                <td class="text-center text-sm font-weight-bolder text-dark bg-read-only">{{ $s->bobot_p_v }}</td>
                                                <td class="align-middle text-center p-2 border-start bg-read-only">
                                                    <h6 class="mb-0 text-sm font-weight-bolder text-primary">{{ $s->nilai_akhir }}</h6>
                                                    <input type="hidden" name="data[{{ $s->id_siswa }}][nilai_akhir]" value="{{ $s->nilai_akhir }}">
                                                </td>
                                                <td class="align-middle p-3">
                                                    <div class="text-capaian">{{ $s->deskripsi }}</div>
                                                    <input type="hidden" name="data[{{ $s->id_siswa }}][deskripsi]" value="{{ $s->deskripsi }}">
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-sm {{ $s->is_saved ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                        {{ $s->is_saved ? 'TERSIMPAN' : 'DRAFT' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- EMPTY STATE --}}
            <div class="card shadow-sm border">
                <div class="card-body text-center py-5">
                    <i class="fas fa-filter text-primary mb-3 fa-3x opacity-5"></i>
                    <h5 class="text-dark font-weight-bold">Filter Data Diperlukan</h5>
                    <p class="text-secondary text-sm mb-0">Silakan pilih <strong>Kelas</strong> dan <strong>Mata Pelajaran</strong> untuk menampilkan rekap nilai.</p>
                </div>
            </div>
        @endif

    </div>
    <x-app.footer />
</main>

{{-- OVERLAY LOADING --}}
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; color: white; font-size: 1.5rem; z-index: 999999;">
    <div class="d-flex flex-column align-items-center">
        <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status"></div> 
        <span>Sedang menyimpan data...</span>
    </div>
</div>

{{-- SCRIPT LOGIC --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Init Tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) })

    // FUNGSI MANUAL UNTUK SIMPAN REKAP
    function confirmSimpan() {
        if (confirm('Apakah Anda yakin data nilai sudah benar? Data akan disimpan sebagai nilai akhir rapor.')) {
            // Tampilkan Overlay
            $('#loadingOverlay').attr('style', 'display: flex !important; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; color: white; font-size: 1.5rem; z-index: 999999;');
            
            // Submit Form
            setTimeout(function() {
                document.getElementById('formSimpanRekap').submit();
            }, 100);
        }
    }
</script>
@endsection