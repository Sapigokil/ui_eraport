@extends('layouts.app') 

@section('page-title', 'Detail Laporan PSTS')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- TOMBOL KEMBALI & INFORMASI KELAS --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            {{-- ✅ PERBAIKAN RUTE: Menggunakan sis.psts.index --}}
            <a href="{{ route('sis.psts.index') }}" class="btn btn-sm btn-white shadow-sm mb-0 d-flex align-items-center">
                <i class="fas fa-arrow-left me-2 text-dark"></i> Kembali ke Daftar
            </a>

            <div class="text-end">
                <span class="badge bg-white text-dark border shadow-sm px-3 py-2">
                    <i class="fas fa-door-open me-1 text-primary"></i> {{ $kelas->nama_kelas ?? 'Kelas Tidak Diketahui' }}
                </span>
                <span class="badge bg-white text-dark border shadow-sm px-3 py-2 ms-2">
                    <i class="fas fa-calendar-alt me-1 text-info"></i> Semester {{ $semester == 1 ? 'Ganjil' : 'Genap' }} ({{ $ta }})
                </span>
            </div>
        </div>

        {{-- KONTEN TABEL NILAI --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header p-4 bg-light border-bottom d-flex justify-content-between align-items-center" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="mb-0 text-dark font-weight-bolder">
                    <i class="fas fa-list-alt text-primary me-2"></i> Rincian Nilai Sumatif
                </h5>
            </div>
            
            <div class="card-body px-0 pb-2">
                @if(empty($data_psts))
                    <div class="text-center py-7 text-secondary">
                        <div class="icon icon-shape bg-gray-100 text-center border-radius-circle mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-box-open fa-2x opacity-5"></i>
                        </div>
                        <h6 class="text-dark">Belum Ada Rincian Nilai</h6>
                        <p class="text-sm">Detail nilai belum dipublikasikan oleh Guru Mata Pelajaran pada semester ini.</p>
                    </div>
                @else
                    <div class="table-responsive p-0">
                        <table class="table table-bordered align-items-center mb-0 table-hover">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 5%">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 35%">Mata Pelajaran</th>
                                    
                                    {{-- Render Header Dinamis (S1, S2, STS, Project, dll) --}}
                                    @foreach($jenis_penilaian_unik as $jp)
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            @if($jp == 'Project')
                                                <span class="badge bg-gradient-success p-1">{{ $jp }}</span>
                                            @elseif(str_contains($jp, 'STS') || str_contains($jp, 'SAS'))
                                                <span class="badge bg-gradient-info p-1">{{ $jp }}</span>
                                            @else
                                                {{ $jp }}
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php $nomor_global = 1; @endphp
                                
                                @foreach($data_psts as $kelompok => $mapels)
                                    {{-- Header Kelompok Mapel (Kategori: Umum / Kejuruan / Pilihan) --}}
                                    <tr style="background-color: #f8f9fa;">
                                        <td colspan="{{ count($jenis_penilaian_unik) + 2 }}" class="py-2 ps-4 border-bottom-0">
                                            <span class="badge bg-gradient-dark text-xxs">{{ $kelompok }}</span>
                                        </td>
                                    </tr>

                                    {{-- Looping Nama Mapel --}}
                                    @foreach($mapels as $nama_mapel => $nilais)
                                        <tr>
                                            <td class="text-center text-sm text-secondary font-weight-bold">{{ $nomor_global++ }}</td>
                                            <td class="ps-4">
                                                <p class="mb-0 text-sm font-weight-bold text-dark">{{ $nama_mapel }}</p>
                                            </td>
                                            
                                            {{-- Looping Nilai Sesuai Kolom --}}
                                            @foreach($jenis_penilaian_unik as $jp)
                                                <td class="text-center align-middle">
                                                    @if(isset($nilais[$jp]))
                                                        <span class="text-sm font-weight-bold {{ $nilais[$jp] < 70 ? 'text-danger' : 'text-dark' }}">
                                                            {{ $nilais[$jp] }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-secondary opacity-4">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
    <x-app.footer />
</main>
@endsection