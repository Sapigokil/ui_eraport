{{-- File: resources/views/pkl/nilai/index.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Dashboard Penilaian PKL')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    
    <x-app.navbar />
    
    <div class="container-fluid py-4 px-5">
        
        {{-- HEADER BANNER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-primary overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                        <i class="fas fa-clipboard-check text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="row align-items-center text-white">
                            <div class="col-md-7">
                                <h3 class="text-white font-weight-bold mb-1">Dashboard Penilaian PKL</h3>
                                <span class="badge border border-white text-white fw-bold bg-transparent">
                                    Tahun Ajaran {{ $tahun_ajaran }} - Semester {{ $semester }}
                                </span>
                            </div>
                            <div class="col-md-5 text-end mt-4 mt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-between gap-4">
                                    {{-- STAT 1: DATA MASUK --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Progress Masuk</span>
                                        <h4 class="text-white mb-0">{{ $rawCount }} <span class="text-sm fw-normal opacity-8">/ {{ $totalSiswa }} Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $persenRaw }}%"></div>
                                        </div>
                                    </div>
                                    {{-- STAT 2: SIAP CETAK --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Final / Siap Cetak</span>
                                        <h4 class="text-white mb-0">{{ $finalCount }} <span class="text-sm fw-normal opacity-8">/ {{ $totalSiswa }} Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $persenFinal }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card my-4 border shadow-xs">
                    
                    {{-- FILTER BOX (AUTO SUBMIT) --}}
                    <div class="card-header bg-gray-100 border-bottom p-3">
                        <form action="{{ route('pkl.nilai.index') }}" method="GET" class="row align-items-end" id="formFilterData">
                            
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">Tahun Ajaran</label>
                                <select name="tahun_ajaran" class="form-select border ps-2 bg-white" onchange="this.form.submit()">
                                    @foreach($tahunAjaranList as $ta)
                                        <option value="{{ $ta }}" {{ $tahun_ajaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">Semester</label>
                                <select name="semester" class="form-select border ps-2 bg-white" onchange="this.form.submit()">
                                    <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Ganjil</option>
                                    <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Genap</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">Kelas</label>
                                <select name="id_kelas" class="form-select border ps-2 bg-white" onchange="this.form.submit()">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($kelasList as $kls)
                                        <option value="{{ $kls->id_kelas }}" {{ $id_kelas == $kls->id_kelas ? 'selected' : '' }}>{{ $kls->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            {{-- PERCABANGAN ROLE UNTUK FILTER PEMBIMBING --}}
                            @hasanyrole('developer|admin_erapor|guru_erapor')
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Pembimbing</label>
                                    <select name="id_guru" class="form-select border ps-2 bg-white" onchange="this.form.submit()">
                                        <option value="">-- Semua Guru --</option>
                                        @foreach($guruList as $g)
                                            <option value="{{ $g->id_guru }}" {{ $id_guru == $g->id_guru ? 'selected' : '' }}>{{ $g->nama_guru }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="id_guru" value="{{ auth()->user()->id_guru }}">
                            @endhasanyrole
                            
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">Tempat PKL</label>
                                <select name="id_tempat" class="form-select border ps-2 bg-white" onchange="this.form.submit()">
                                    <option value="">-- Semua Tempat --</option>
                                    @foreach($tempatList as $tpt)
                                        <option value="{{ $tpt->id }}" {{ $id_tempat == $tpt->id ? 'selected' : '' }}>{{ Str::limit($tpt->nama_perusahaan, 25) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2 mb-md-0">
                                <label class="form-label text-xs font-weight-bolder text-uppercase text-primary">Status Nilai</label>
                                <select name="status_penilaian" class="form-select border ps-2 bg-white border-primary" onchange="this.form.submit()">
                                    <option value="">-- Semua Status --</option>
                                    <option value="belum" {{ isset($status_penilaian) && $status_penilaian === 'belum' ? 'selected' : '' }}>Belum Dinilai</option>
                                    <option value="0" {{ isset($status_penilaian) && $status_penilaian === '0' ? 'selected' : '' }}>Draft</option>
                                    <option value="1" {{ isset($status_penilaian) && $status_penilaian === '1' ? 'selected' : '' }}>Final</option>
                                </select>
                            </div>

                        </form>
                    </div>

                    {{-- ALERTS NOTIFIKASI IMPORT --}}
                    @if (session('success'))
                        <div class="alert bg-gradient-success alert-dismissible text-white fade show mx-4 mt-3 mb-0" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert bg-gradient-danger alert-dismissible text-white fade show mx-4 mt-3 mb-0" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- TOOLBAR EXPORT / IMPORT EXCEL --}}
                    <div class="d-flex justify-content-between align-items-center p-3 px-4 border-bottom flex-wrap">
                        <h6 class="mb-0 text-dark">Data Bimbingan PKL</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            
                            {{-- TOMBOL EXPORT DATA (Bebas dari Filter Guru) --}}
                            <a href="{{ route('pkl.nilai.export_rekap', request()->query()) }}" class="btn btn-sm btn-dark mb-0 shadow-sm" data-toggle="tooltip" title="Download Excel Laporan sesuai filter saat ini">
                                <i class="fas fa-download me-1"></i> Export Data
                            </a>

                            {{-- TOMBOL IMPORT EXCEL (MEMBUKA MODAL) --}}
                            @if($id_guru)
                                <button type="button" class="btn btn-sm btn-info mb-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                                    <i class="fas fa-upload me-1"></i> Import Nilai Excel
                                </button>
                            @else
                                <button class="btn btn-sm btn-secondary mb-0" disabled data-toggle="tooltip" title="Pilih spesifik 1 Pembimbing di Filter terlebih dahulu untuk melakukan Import Nilai">
                                    <i class="fas fa-upload me-1"></i> Import Nilai Excel
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- TABEL SISWA --}}
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Siswa</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kelas</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Guru Pembimbing</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tempat Magang</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Nilai</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dataSiswa as $siswa)
                                        <tr>
                                            <td class="text-center text-sm">{{ $loop->iteration }}</td>
                                            <td><h6 class="mb-0 text-sm">{{ $siswa->nama_siswa }}</h6></td>
                                            <td class="text-sm">{{ $siswa->nama_kelas }}</td>
                                            <td class="text-sm"><span class="text-secondary"><i class="fas fa-user-tie me-1"></i>{{ $siswa->nama_guru }}</span></td>
                                            <td class="text-sm text-wrap"><span class="text-secondary"><i class="fas fa-building me-1"></i>{{ $siswa->tempat_pkl ?? 'Belum Diatur' }}</span></td>
                                            <td class="align-middle text-center">
                                                @if($siswa->status_penilaian === null)
                                                    <span class="badge bg-secondary text-xxs">Belum</span>
                                                @elseif($siswa->status_penilaian == 0)
                                                    <span class="badge bg-warning text-xxs">Draft</span>
                                                @else
                                                    <span class="badge bg-success text-xxs">Final</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('pkl.nilai.input', ['tahun_ajaran' => $tahun_ajaran, 'semester' => $semester, 'id_guru' => $siswa->id_guru, 'id_penempatan' => $siswa->id_penempatan]) }}" class="btn btn-sm btn-outline-primary mb-0 px-3 py-1">
                                                    <i class="fas fa-edit me-1"></i> Input Nilai
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-secondary">
                                                <i class="fas fa-search fa-2x mb-3 opacity-5"></i><br>
                                                Tidak ada data ditemukan. Silakan sesuaikan filter pencarian di atas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <x-app.footer />
    </div>
</main>

{{-- MODAL IMPORT EXCEL BERTINGKAT --}}
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pkl.nilai.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tahun_ajaran" value="{{ $tahun_ajaran }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
                <input type="hidden" name="id_guru" value="{{ $id_guru }}">
                
                <div class="modal-header bg-light border-bottom-0 pb-2">
                    <h5 class="modal-title text-dark" id="modalImportExcelLabel"><i class="fas fa-file-excel text-success me-2"></i> Import Nilai PKL</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body pt-2">
                    
                    {{-- LANGKAH 1: DOWNLOAD TEMPLATE --}}
                    <div class="mb-4 p-3 bg-gray-100 border rounded">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-dark rounded-circle me-2 d-flex justify-content-center align-items-center" style="width: 24px; height: 24px; font-size: 10px;">1</span>
                            <h6 class="text-sm font-weight-bold mb-0 text-dark">Unduh Template Excel</h6>
                        </div>
                        <p class="text-xs text-secondary mb-3 ms-4">Unduh template Excel yang telah berisi daftar nama siswa bimbingan Anda. Jangan mengubah format kolom terutama <code>ID_SISTEM</code>.</p>
                        <div class="ms-4">
                            <a href="{{ route('pkl.nilai.export', ['tahun_ajaran' => $tahun_ajaran, 'semester' => $semester, 'id_guru' => $id_guru]) }}" class="btn btn-sm btn-success mb-0 shadow-sm w-100">
                                <i class="fas fa-download me-1"></i> Unduh Template Excel
                            </a>
                        </div>
                    </div>

                    {{-- LANGKAH 2: UPLOAD HASIL --}}
                    <div class="p-3 border rounded shadow-none">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-dark rounded-circle me-2 d-flex justify-content-center align-items-center" style="width: 24px; height: 24px; font-size: 10px;">2</span>
                            <h6 class="text-sm font-weight-bold mb-0 text-dark">Unggah Hasil Pengisian</h6>
                        </div>
                        <p class="text-xs text-secondary mb-3 ms-4">Pilih file Excel yang telah Anda isi nilainya, lalu tekan tombol <b>Mulai Import</b>.</p>
                        
                        <div class="ms-4">
                            <label for="file_import" class="form-label font-weight-bold text-xs d-none">Upload File</label>
                            <input class="form-control border px-3 py-2 text-sm" type="file" id="file_import" name="file_import" accept=".xlsx, .xls, .csv" required>
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer bg-light border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary mb-0" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary mb-0"><i class="fas fa-upload me-1"></i> Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Tooltip Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection