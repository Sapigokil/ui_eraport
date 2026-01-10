{{-- File: resources/views/nilai/catatan.blade.php --}}

@extends('layouts.app')

@section('page-title', 'Catatan Wali Kelas')

@php
    $request = request();
    
    // --- LOGIKA TAHUN AJARAN & SEMESTER ---
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
    
    $tahunMulai = $tahunSekarang - 3; // 3 tahun ke belakang
    $tahunAkhir = $tahunSekarang + 3; // 3 tahun ke depan

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
                    
                    {{-- HEADER OFFSET BIRU --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-white text-capitalize ps-3 mb-0">
                                <i class="fas fa-clipboard-check me-2"></i> Input Catatan & Absensi Wali Kelas
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
                        {{-- NOTIFIKASI --}}
                        @if (session('success'))
                            <div class="alert bg-gradient-success mx-4 alert-dismissible text-white fade show" role="alert">
                                <span class="text-sm"><strong>Sukses!</strong> {!! session('success') !!}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white fade show" role="alert">
                                <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        {{-- FILTER DATA --}}
                        <div class="p-4 border-bottom">
                            <form method="GET" action="{{ route('master.catatan.input') }}" class="row align-items-end">
                                <div class="col-md-3 mb-3 text-start">
                                    <label class="form-label text-xs font-weight-bold text-uppercase">Pilih Kelas</label>
                                    <select name="id_kelas" id="kelasSelect" required class="form-select border ps-2">
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach ($kelas as $k)
                                            <option value="{{ $k->id_kelas }}" {{ $request->id_kelas == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3 text-start">
                                    <label class="form-label text-xs font-weight-bold text-uppercase">Pilih Siswa</label>
                                    <select name="id_siswa" id="siswaSelect" required class="form-select border ps-2" onchange="this.form.submit()">
                                        <option value="">-- Pilih Siswa --</option>
                                        @foreach ($siswa as $s)
                                            <option value="{{ $s->id_siswa }}" {{ $request->id_siswa == $s->id_siswa ? 'selected' : '' }}>{{ $s->nama_siswa }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3 text-start">
                                    <label class="form-label text-xs font-weight-bold text-uppercase">Semester</label>
                                    <select name="semester" class="form-select border ps-2">
                                        @foreach ($semesterList as $sem)
                                            <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3 text-start">
                                    <label class="form-label text-xs font-weight-bold text-uppercase">Tahun Ajaran</label>
                                    <select name="tahun_ajaran" class="form-select border ps-2">
                                        @foreach ($tahunAjaranList as $ta)
                                            <option value="{{ $ta }}" {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <button type="submit" class="btn bg-gradient-dark w-100 mb-0 text-capitalize">Tampilkan</button>
                                </div>
                            </form>
                        </div>

                        {{-- INPUT FORM AREA --}}
                        <div class="p-4">
                            @if (!$request->id_kelas || !$request->id_siswa)
                                <div class="text-center py-5 border rounded bg-gray-100">
                                    <i class="fas fa-filter text-secondary mb-3 fa-2x"></i>
                                    <p class="text-secondary mb-0">Silakan pilih filter untuk mulai mengisi catatan rapor siswa.</p>
                                </div>
                            @else
                                <form action="{{ route('master.catatan.simpan') }}" method="POST">
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
                                                        @foreach($set_kokurikuler->where('tingkat', $siswaTerpilih->kelas->tingkat)->where('aktif', 1) as $kok)
                                                            <option value="{{ $kok->id_kok }}" data-deskripsi="{{ $kok->deskripsi }}">
                                                                {{ $kok->judul }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="input-group input-group-outline is-filled">
                                                    <textarea id="kokurikulerText" name="kokurikuler" rows="5" class="form-control text-sm" placeholder="Pilih template di atas atau isi manual...">{{ old('kokurikuler', $rapor->kokurikuler ?? '') }}</textarea>
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
                                                                            <option value="Sangat Baik" {{ $savedPred == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                                                            <option value="Baik" {{ $savedPred == 'Baik' ? 'selected' : '' }}>Baik</option>
                                                                            <option value="Cukup" {{ $savedPred == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                                                            <option value="Kurang" {{ $savedPred == 'Kurang' ? 'selected' : '' }}>Kurang</option>
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

                                            <button type="submit" class="btn bg-gradient-success w-100 py-2">
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
        <x-app.footer />
    </div>

    {{-- MODAL DOWNLOAD TEMPLATE --}}
    <div class="modal fade" id="downloadTemplateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-gray-100">
                    <h6 class="modal-title font-weight-bolder text-dark">
                        <i class="fas fa-file-excel text-primary me-2"></i> Download Template Catatan
                    </h6>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('master.catatan.template') }}" method="GET">
                    <div class="modal-body py-4 text-start">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Pilih Kelas</label>
                            <select name="id_kelas" required class="form-select border ps-2 text-sm">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row text-start">
                            <div class="col-6">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">Semester</label>
                                <select name="semester" class="form-select border ps-2 text-sm">
                                    <option value="Ganjil">Ganjil</option>
                                    <option value="Genap">Genap</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">Tahun Ajaran</label>
                                <input type="text" name="tahun_ajaran" class="form-control border ps-2 text-sm" value="{{ $defaultTahunAjaran }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-gray-100">
                        <button type="button" class="btn btn-sm btn-white mb-0" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm bg-gradient-primary mb-0">Download .xlsx</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- MODAL IMPORT CATATAN --}}
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-gray-100">
                    <h6 class="modal-title font-weight-bolder text-dark">
                        <i class="fas fa-file-import text-success me-2"></i> Import Catatan Wali
                    </h6>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('master.catatan.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body py-4 text-start">
                        {{-- Input Pilih Kelas --}}
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Pilih Kelas</label>
                            <select name="id_kelas" required class="form-select border ps-2 text-sm">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Baris Semester dan Tahun Ajaran --}}
                        <div class="row text-start mb-3">
                            <div class="col-6">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">Semester</label>
                                <select name="semester" class="form-select border ps-2 text-sm">
                                    <option value="Ganjil" {{ request('semester') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                    <option value="Genap" {{ request('semester') == 'Genap' ? 'selected' : '' }}>Genap</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">Tahun Ajaran</label>
                                <input type="text" name="tahun_ajaran" class="form-control border ps-2 text-sm" 
                                    value="{{ request('tahun_ajaran') ?? $defaultTahunAjaran }}">
                            </div>
                        </div>

                        {{-- Input Pilih File Excel --}}
                        <div class="mb-0">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Pilih File Excel</label>
                            <input type="file" name="file_excel" class="form-control border ps-2 text-sm" required accept=".xlsx, .xls">
                        </div>
                    </div>

                    <div class="modal-footer bg-gray-100">
                        <button type="button" class="btn btn-sm btn-white mb-0" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm bg-gradient-success mb-0">Upload & Proses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logika Capaian Kokurikuler Otomatis
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

        // Logika Refresh halaman saat ganti kelas
        const kelasSelect = document.getElementById('kelasSelect');
        if(kelasSelect) {
            kelasSelect.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('id_kelas', this.value);
                url.searchParams.delete('id_siswa'); 
                window.location.href = url.toString();
            });
        }
    });
</script>
@endsection