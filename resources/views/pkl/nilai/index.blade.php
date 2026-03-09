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
                                <p class="text-white opacity-8 mb-2"><i class="fas fa-filter me-2"></i> Filter otomatis menyimpan. Pilih parameter untuk melihat data spesifik.</p>
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
                            
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">Pembimbing</label>
                                <select name="id_guru" class="form-select border ps-2 bg-white" onchange="this.form.submit()">
                                    <option value="">-- Semua Guru --</option>
                                    @foreach($guruList as $g)
                                        <option value="{{ $g->id_guru }}" {{ $id_guru == $g->id_guru ? 'selected' : '' }}>{{ $g->nama_guru }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">Tempat PKL</label>
                                <select name="id_tempat" class="form-select border ps-2 bg-white" onchange="this.form.submit()">
                                    <option value="">-- Semua Tempat --</option>
                                    @foreach($tempatList as $tpt)
                                        <option value="{{ $tpt->id }}" {{ $id_tempat == $tpt->id ? 'selected' : '' }}>{{ Str::limit($tpt->nama_perusahaan, 35) }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </form>
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
                                                {{-- TOMBOL SAKTI: Membawa id_guru dan id_penempatan ke halaman input --}}
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
@endsection