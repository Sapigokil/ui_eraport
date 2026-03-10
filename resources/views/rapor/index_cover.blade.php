{{-- File: resources/views/rapor/index_cover.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Cetak Cover & Identitas Rapor')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- CARD FILTER --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-body p-3">
                <form action="{{ route('rapornilai.cover.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-10">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Pilih Kelas</label>
                        <select name="id_kelas" class="form-select border-secondary" required onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas untuk Menampilkan Daftar Siswa --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas', $id_kelas) == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }} (Wali: {{ $k->wali_kelas ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="submit" class="btn btn-primary w-100 mb-0"><i class="fas fa-search me-1"></i> Cari Data</button>
                    </div>
                </form>
            </div>
        </div>

        @if($id_kelas && $kelasAktif)
        {{-- KONTEN TABEL SISWA --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border">
                    <div class="card-header p-3 bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-book-open me-2"></i> Daftar Cetak Cover Kelas {{ $kelasAktif->nama_kelas }}</h6>
                                <span class="text-xs text-secondary">Total: {{ count($siswaList) }} Siswa Aktif</span>
                            </div>
                            
                            @if(count($siswaList) > 0)
                            <a href="{{ route('rapornilai.cover.cetak_massal') }}?id_kelas={{ $id_kelas }}" 
                               class="btn btn-sm btn-outline-primary mb-0" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> Cetak Massal (1 Kelas)
                            </a>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                        <th class="ps-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 50%">Nama Siswa</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 25%">NISN / NIPD</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($siswaList as $idx => $s)
                                    <tr>
                                        <td class="text-center text-sm text-secondary">{{ $idx + 1 }}</td>
                                        <td class="ps-3">
                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $s->nama_siswa }}</h6>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="text-xs text-secondary font-weight-bold">{{ $s->nisn ?? '-' }} / {{ $s->nipd ?? '-' }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="{{ route('rapornilai.cover.cetak_satuan', $s->id_siswa) }}" 
                                               target="_blank" class="btn btn-xs bg-gradient-primary mb-0 px-3">
                                                <i class="fas fa-print me-1"></i> Cetak Cover
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-secondary">
                                            <i class="fas fa-users-slash fa-2x mb-3 opacity-5"></i><br>
                                            Tidak ada data siswa ditemukan di kelas ini.
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
        @else
        <div class="text-center py-6">
            <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-xl mb-3">
                <i class="fas fa-address-book fa-lg opacity-10" aria-hidden="true"></i>
            </div>
            <h5 class="mt-2">Pilih Kelas Terlebih Dahulu</h5>
            <p class="text-sm text-secondary">Gunakan filter di atas untuk memuat daftar siswa yang akan dicetak Cover-nya.</p>
        </div>
        @endif

    </div>
    <x-app.footer />
</main>
@endsection