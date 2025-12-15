{{-- File: resources/views/nilai/sum1_index.blade.php --}}

@extends('layouts.app') 

{{-- HEADER DINAMIS --}}
@section('title', 'Input Nilai Sumatif ' . $sumatifId)

@php
    // --- LOGIKA TAHUN AJARAN & SEMESTER (Dibiarkan tetap) ---
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
                        
                        {{-- ================================================================= --}}
                        {{-- HEADER DINAMIS + TOMBOL BARU --}}
                        {{-- ================================================================= --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas fa-edit me-2"></i> Input Nilai Sumatif {{ $sumatifId }}
                                </h6>

                                <div class="pe-3">
                                    {{-- Tombol Pertama: Download Template (Memicu Modal) --}}
                                    <button class="btn bg-gradient-light text-dark btn-sm mb-0 me-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#downloadTemplateModal">
                                        <i class="fas fa-file-excel me-1"></i> Download Template
                                    </button>
                                    
                                    {{-- Tombol Kedua: Import (Memicu Modal Import) --}}
                                    <button class="btn bg-gradient-success btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="fas fa-file-import me-1"></i> Import
                                    </button>
                                </div>
                            </div>
                        </div>
                        {{-- ================================================================= --}}


                        <div class="card-body px-0 pb-2">
                            
                            {{-- 🛑 TEMPATKAN NOTIFIKASI DI SINI (Di dalam card-body) 🛑 --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm"><strong>Sukses!</strong> {!! session('success') !!}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            
                            {{-- TAMBAHKAN BLOK ERROR DI SINI --}}
                            @if (session('error'))
                                <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            {{-- 🛑 END NOTIFIKASI 🛑 --}}
                            
                            
                            {{-- FORM FILTER (Filter sumatif.s1 disesuaikan ke route master.sumatif.s1) --}}
                            <div class="p-4 border-bottom">
                                <form action="{{ route('master.sumatif.s1') }}" method="GET" class="row align-items-end">
                                    
                                    {{-- 1. PILIH SUMATIF (READ ONLY) --}}
                                    <div class="col-md-2 mb-3">
                                        <label for="sumatif" class="form-label">Pilih Sumatif:</label>
                                        <select name="sumatif" id="sumatif" required class="form-select" disabled>
                                            <option value="{{ $sumatifId }}" selected>Sumatif {{ $sumatifId }}</option>
                                        </select>
                                        <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                                    </div>

                                    {{-- 2. Kelas --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_kelas" class="form-label">Kelas:</label>
                                        <select name="id_kelas" id="id_kelas" required class="form-select" onchange="this.form.submit()">
                                            <option value="">Pilih Kelas</option>
                                            @foreach(\App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                                <option value="{{ $k->id_kelas }}" 
                                                    {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- 3. Mapel --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_mapel" class="form-label">Mata Pelajaran:</label>
                                        <select name="id_mapel" id="id_mapel" required class="form-select" {{ !request('id_kelas') ? 'disabled' : '' }}>
                                            <option value="">Pilih Mapel</option>
                                            @foreach ($mapel as $m)
                                                <option value="{{ $m->id_mapel }}" 
                                                    {{ request('id_mapel') == $m->id_mapel ? 'selected' : '' }}>
                                                    {{ $m->nama_mapel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 4. Semester --}}
                                    <div class="col-md-2 mb-3">
                                        <label for="semester" class="form-label">Semester:</label>
                                        <select name="semester" id="semester" required class="form-select">
                                            @foreach($semesterList as $sem)
                                                <option value="{{ $sem }}" 
                                                    {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>
                                                    {{ $sem }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 5. Tahun Ajaran --}}
                                    <div class="col-md-2 mb-3">
                                        <label for="tahun_ajaran" class="form-label">Tahun Ajaran:</label>
                                        <select name="tahun_ajaran" id="tahun_ajaran" required class="form-select">
                                            @foreach ($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}" 
                                                    {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                                                    {{ $ta }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn bg-gradient-info w-25 mb-0">Tampilkan Siswa</button>
                                    </div>
                                </form>
                            </div>

                            {{-- KONTEN INPUT NILAI --}}
                            <div class="p-4">
                                
                                @if(!$request->id_kelas || !$request->sumatif || !$request->id_mapel || !$request->tahun_ajaran || !$request->semester)
                                    <p class="text-secondary mt-3 p-3 text-center border rounded">
                                        Silakan pilih **Kelas, Mata Pelajaran, Semester, dan Tahun Ajaran** di atas untuk menampilkan daftar input nilai.
                                    </p>

                                @elseif($siswa->isEmpty())
                                    <p class="text-danger mt-3 p-3 text-center border rounded">
                                        Tidak ada siswa ditemukan di kelas yang dipilih, atau filter Agama Khusus tidak cocok.
                                    </p>
                                
                                @else
                                    <form action="{{ route('master.sumatif.store') }}" method="POST" class="mt-4">
                                        @csrf

                                        {{-- Hidden Inputs --}}
                                        <input type="hidden" name="id_kelas" value="{{ $request->id_kelas }}">
                                        <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                                        <input type="hidden" name="tahun_ajaran" value="{{ $request->tahun_ajaran }}">
                                        <input type="hidden" name="semester" value="{{ $request->semester }}">
                                        <input type="hidden" name="id_mapel" value="{{ $request->id_mapel }}">

                                        <div class="table-responsive p-0">
                                            <table class="table align-items-center mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3 text-center" style="width: 5%">No</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Nama Siswa</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 10%">Nilai (0-100)</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center" style="width: 40%">Tujuan Pembelajaran</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                @foreach ($siswa as $i => $s)
                                                    {{-- 🛑 KOREKSI: Gunakan $s->id_siswa untuk mengambil data dari koleksi $rapor 🛑 --}}
                                                    @php
                                                        // Menggunakan get() untuk mencari berdasarkan kunci id_siswa
                                                        $raporItem = $rapor->get($s->id_siswa);
                                                        // Menggunakan optional() untuk menghindari error jika record tidak ditemukan
                                                        $nilaiLama = optional($raporItem);
                                                    @endphp
                                                    <tr>
                                                        <td class="ps-3 py-2 text-sm font-weight-bold align-top">{{ $i+1 }}</td>
                                                        <td class="py-2 text-sm font-weight-bold align-top">
                                                            {{ $s->nama_siswa }}
                                                            <input type="hidden" name="id_siswa[]" value="{{ $s->id_siswa }}">
                                                        </td>
                                                        <td class="px-3 py-2 align-top">
                                                            <div class="input-group input-group-outline">
                                                                <input type="number"
                                                                    name="nilai[]"
                                                                    min="0" max="100"
                                                                    class="form-control text-center"
                                                                    {{-- 🛑 KOREKSI PENGAMBILAN NILAI: $nilaiLama->nilai 🛑 --}}
                                                                    value="{{ old('nilai.' . $i, $nilaiLama->nilai) }}"
                                                                    style="max-width: 80px; margin: auto;"
                                                                    onwheel="return false;"> 
                                                            </div>
                                                        </td>
                                                        <td class="px-3 py-2 align-top">
                                                            <div class="input-group input-group-outline">
                                                                <textarea
                                                                    name="tujuan_pembelajaran[]"
                                                                    rows="3"
                                                                    class="form-control text-sm text-start"
                                                                    placeholder="Cth: TP 1, TP 2, ... atau deskripsi singkat"
                                                                    onfocus="this.setSelectionRange(this.value.length, this.value.length)"
                                                                    {{-- Hapus spasi antar tag <textarea> untuk menghindari input whitespace/spasi kosong --}}
                                                                >@php echo trim(old('tujuan_pembelajaran.' . $i, $nilaiLama->tujuan_pembelajaran)); @endphp</textarea>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="text-end mt-4">
                                            <button type="submit" class="btn bg-gradient-success">
                                                <i class="fas fa-save me-2"></i> Simpan Nilai Sumatif {{ $sumatifId }}
                                            </button>
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

    {{-- ================================================================= --}}
    {{-- MODAL POP-UP DOWNLOAD TEMPLATE (Tidak Berubah) --}}
    {{-- ================================================================= --}}
    {{-- ... (Konten Modal Download Template) ... --}}
    <div class="modal fade" id="downloadTemplateModal" tabindex="-1" aria-labelledby="downloadTemplateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="downloadTemplateModalLabel">Download Template Sumatif {{ $sumatifId }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('master.sumatif.download') }}" method="GET"> 
                    <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                    
                    <div class="modal-body">
                        <p class="text-secondary">Gunakan template ini untuk mengimport nilai kedalam Data Nilai.</p>
                        
                        <div class="mb-3">
                            <label for="id_kelas_modal" class="form-label">Kelas:</label>
                            <select name="id_kelas" id="id_kelas_modal" required class="form-select">
                                <option value="">Pilih Kelas</option>
                                @foreach(\App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="id_mapel_modal" class="form-label">Mata Pelajaran:</label>
                            <select name="id_mapel" id="id_mapel_modal" required class="form-select">
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach(\App\Models\MataPelajaran::all() as $m)
                                    <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="semester_modal" class="form-label">Semester:</label>
                            <select name="semester" id="semester_modal" required class="form-select">
                                @foreach($semesterList as $sem)
                                    <option value="{{ $sem }}" {{ $defaultSemester == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tahun_ajaran_modal" class="form-label">Tahun Ajaran:</label>
                            <select name="tahun_ajaran" id="tahun_ajaran_modal" required class="form-select">
                                @foreach ($tahunAjaranList as $ta)
                                    <option value="{{ $ta }}" {{ $defaultTahunAjaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn bg-gradient-info">Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- MODAL POP-UP IMPORT NILAI (Revisi dengan Filter) --}}
    {{-- ================================================================= --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Nilai Sumatif {{ $sumatifId }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('master.sumatif.import') }}" method="POST" enctype="multipart/form-data"> 
                    @csrf
                    <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                    
                    <div class="modal-body">
                        <p class="text-secondary font-weight-bold text-center">
                            Pastikan Excel sesuai dengan Template yang telah diunduh.
                        </p>
                        
                        {{-- 1. Kelas --}}
                        <div class="mb-3">
                            <label for="id_kelas_import" class="form-label">Kelas:</label>
                            <select name="id_kelas" id="id_kelas_import" required class="form-select">
                                <option value="">Pilih Kelas</option>
                                @foreach(\App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 2. Mapel --}}
                        <div class="mb-3">
                            <label for="id_mapel_import" class="form-label">Mata Pelajaran:</label>
                            <select name="id_mapel" id="id_mapel_import" required class="form-select">
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach(\App\Models\MataPelajaran::all() as $m)
                                    <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. Semester --}}
                        <div class="mb-3">
                            <label for="semester_import" class="form-label">Semester:</label>
                            <select name="semester" id="semester_import" required class="form-select">
                                @foreach($semesterList as $sem)
                                    <option value="{{ $sem }}" {{ $defaultSemester == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- 4. Tahun Ajaran --}}
                        <div class="mb-3">
                            <label for="tahun_ajaran_import" class="form-label">Tahun Ajaran:</label>
                            <select name="tahun_ajaran" id="tahun_ajaran_import" required class="form-select">
                                @foreach ($tahunAjaranList as $ta)
                                    <option value="{{ $ta }}" {{ $defaultTahunAjaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 5. File Excel --}}
                        <div class="mb-3">
                            <label for="file_excel" class="form-label text-start d-block">Pilih File Excel:</label>
                            <input type="file" name="file_excel" id="file_excel" required class="form-control" accept=".xlsx, .xls">
                        </div>

                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn bg-gradient-success">Lanjutkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 🛑 LOADING OVERLAY DAN SCRIPT HARUS DILUAR TAG MAIN 🛑 --}}
    {{-- LOADING OVERLAY --}}
    <div id="loadingOverlay" style="
        display: none; 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0, 0, 0, 0.7); 
        justify-content: center; 
        align-items: center; 
        color: white; 
        font-size: 1.5rem; 
        z-index: 9999;
    ">
        <div class="spinner-border text-light me-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        Sedang memproses import nilai... Mohon tunggu.
    </div>

    {{-- SCRIPT JAVASCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const importForm = document.querySelector('#importModal form');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            if (importForm) {
                importForm.addEventListener('submit', function() {
                    // Tampilkan Progress Window
                    loadingOverlay.style.display = 'flex';
                    // Non-aktifkan tombol untuk mencegah double submit
                    this.querySelector('button[type="submit"]').disabled = true;
                });
            }
            
            // Sembunyikan progress window jika ada pesan error/success dari redirect
            // Cek di sini agar overlay tidak muncul jika ada flash message dari Controller
            @if (session('error') || session('success'))
                loadingOverlay.style.display = 'none';
            @endif
        });
    </script>
@endsection