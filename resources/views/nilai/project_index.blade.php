{{-- File: resources/views/nilai/project_index.blade.php --}}

@extends('layouts.app') 

{{-- HEADER DINAMIS --}}
@section('title', 'Input Nilai Project (P5)')

@php
    // --- LOGIKA TAHUN AJARAN & SEMESTER (Diambil dari Sumatif index) ---
    $request = request();
    $tahunSekarang = date('Y');
    $bulanSekarang = date('n');

    if ($bulanSekarang < 7) {
        $defaultTA1 = $tahunSekarang - 1;
        $defaultTA2 = $tahunSekarang;
        $defaultSemester = 'Genap'; // Digunakan di View, akan di-map di Controller
    } else {
        $defaultTA1 = $tahunSekarang;
        $defaultTA2 = $tahunSekarang + 1;
        $defaultSemester = 'Ganjil'; // Digunakan di View, akan di-map di Controller
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
                        {{-- HEADER DINAMIS --}}
                        {{-- ================================================================= --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas fa-folder-open me-2"></i> Input Nilai Project (P5)
                                </h6>
                                {{-- Tombol Download/Import di Project biasanya opsional --}}
                            </div>
                        </div>
                        {{-- ================================================================= --}}


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
                            
                            
                            {{-- FORM FILTER --}}
                            <div class="p-4 border-bottom">
                                {{-- 🛑 Route Filter: master.project.index (Mengarah ke ProjectController::index) 🛑 --}}
                                <form action="{{ route('master.project.index') }}" method="GET" class="row align-items-end">
                                    
                                    {{-- 1. Kelas --}}
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
                                    
                                    {{-- 2. Mapel (Project dapat di-ampu beberapa mapel) --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="id_mapel" class="form-label">Mata Pelajaran Pengampu:</label>
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

                                    {{-- 3. Semester --}}
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

                                    {{-- 4. Tahun Ajaran --}}
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
                                    
                                    <div class="col-md-2 mb-3 text-end">
                                        <button type="submit" class="btn bg-gradient-info w-100 mb-0">Tampilkan Siswa</button>
                                    </div>
                                </form>
                            </div>

                            {{-- KONTEN INPUT NILAI PROJECT --}}
                            <div class="p-4">
                                
                                @if(!$request->id_kelas || !$request->id_mapel || !$request->tahun_ajaran || !$request->semester)
                                    <p class="text-secondary mt-3 p-3 text-center border rounded">
                                        Silakan pilih **Kelas, Mata Pelajaran, Semester, dan Tahun Ajaran** di atas untuk menampilkan daftar input nilai.
                                    </p>

                                @elseif($siswa->isEmpty())
                                    <p class="text-danger mt-3 p-3 text-center border rounded">
                                        Tidak ada siswa ditemukan di kelas yang dipilih, atau filter Agama Khusus tidak cocok.
                                    </p>
                                
                                @else
                                    {{-- 🛑 Route Simpan: master.project.simpan 🛑 --}}
                                    <form action="{{ route('master.project.simpan') }}" method="POST" class="mt-4">
                                        @csrf

                                        {{-- Hidden Inputs (Wajib dikirim untuk updateOrCreate) --}}
                                        <input type="hidden" name="id_kelas" value="{{ $request->id_kelas }}">
                                        <input type="hidden" name="tahun_ajaran" value="{{ $request->tahun_ajaran }}">
                                        <input type="hidden" name="semester" value="{{ $request->semester }}">
                                        <input type="hidden" name="id_mapel" value="{{ $request->id_mapel }}">

                                        <div class="table-responsive p-0">
                                            <table class="table align-items-center mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3 text-center" style="width: 5%">No</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 25%">Nama Siswa</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 10%">Nilai Project (0-100)</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 10%">Nilai Bobot (60%)</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center" style="width: 40%">Tujuan Pembelajaran / Deskripsi</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                @foreach ($siswa as $i => $s)
                                                    @php
                                                        $raporItem = $rapor->get($s->id_siswa);
                                                        $nilaiLama = optional($raporItem);
                                                        $nilaiBobotHitung = $nilaiLama->nilai ? round($nilaiLama->nilai * 0.6, 2) : '-';
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
                                                                    value="{{ old('nilai.' . $i, $nilaiLama->nilai) }}"
                                                                    style="max-width: 100px; margin: auto;"
                                                                    onwheel="return false;"> 
                                                            </div>
                                                        </td>
                                                        
                                                        {{-- Kolom Nilai Bobot (READ-ONLY/Text) --}}
                                                        <td class="px-3 py-2 align-top text-center">
                                                            <span class="badge bg-gradient-info text-xs">{{ $nilaiBobotHitung }}</span>
                                                        </td>

                                                        <td class="px-3 py-2 align-top">
                                                            <div class="input-group input-group-outline">
                                                                <textarea
                                                                    name="tujuan_pembelajaran[]"
                                                                    rows="3"
                                                                    class="form-control text-sm text-start"
                                                                    placeholder="Masukkan deskripsi project atau tujuan pembelajaran yang dicapai"
                                                                    onfocus="this.setSelectionRange(this.value.length, this.value.length)"
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
                                                <i class="fas fa-save me-2"></i> Simpan Nilai Project
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
@endsection