@extends('layouts.app')

@section('page-title', 'Mutasi Pindah Kelas')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- HEADER UTAMA (Gaya Banner) --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            {{-- Dekorasi Icon Besar --}}
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-exchange-alt text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-sync-alt me-2"></i> Pindah Kelas (Rolling)
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Manajemen perpindahan siswa antar rombongan belajar beserta migrasi nilai
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4 mt-3">
                        
                        {{-- ALERT SYSTEM --}}
                        <div class="mt-2">
                            @if (session('success'))
                                <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm"><strong>Sukses!</strong> {{ session('success') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                                </div>
                            @endif
                        </div>

                        {{-- FILTER KELAS ASAL --}}
                        <div class="bg-gray-50 p-3 rounded border mb-4">
                            <form action="{{ route('mutasi.pindah.index') }}" method="GET" class="row align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label text-xs fw-bold text-uppercase text-secondary mb-1">Pilih Kelas Asal</label>
                                    <div class="input-group input-group-outline bg-white rounded">
                                        <select name="id_kelas_asal" class="form-control px-3" onchange="this.form.submit()">
                                            <option value="">-- Pilih Kelas Asal --</option>
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas_asal') == $k->id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>

                        @if(request('id_kelas_asal'))
                        {{-- FORM PINDAH & TABEL SISWA --}}
                        <form action="{{ route('mutasi.pindah.store') }}" method="POST" id="formPindah">
                            @csrf
                            <input type="hidden" name="id_kelas_asal" value="{{ request('id_kelas_asal') }}">

                            <div class="card border border-light shadow-sm">
                                <div class="card-header border-bottom p-3 bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-dark font-weight-bold">
                                        <i class="fas fa-list-ul text-primary me-2"></i> Daftar Siswa Aktif
                                    </h6>
                                    <button type="button" class="btn bg-gradient-primary btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#modalTujuan">
                                        <i class="fas fa-paper-plane me-1"></i> Proses Pindah Terpilih
                                    </button>
                                </div>
                                
                                <div class="table-responsive p-0" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table align-items-center mb-0 table-hover">
                                        <thead class="bg-white sticky-top">
                                            <tr>
                                                <th class="text-center" width="5%">
                                                    <div class="form-check text-center d-flex justify-content-center">
                                                        <input class="form-check-input border-secondary" type="checkbox" id="checkAll">
                                                    </div>
                                                </th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Siswa</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NISN</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($siswaAktif as $s)
                                            <tr>
                                                <td class="text-center align-middle">
                                                    <div class="form-check text-center d-flex justify-content-center">
                                                        <input class="form-check-input border-secondary item-check" type="checkbox" name="siswa_ids[]" value="{{ $s->id_siswa }}">
                                                    </div>
                                                </td>
                                                <td class="text-sm font-weight-bold align-middle">{{ $s->nama_siswa }}</td>
                                                <td class="text-xs align-middle">{{ $s->nisn }}</td>
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-sm bg-gradient-success border-0">Aktif</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5">
                                                    <i class="fas fa-users-slash fa-3x text-secondary opacity-3 mb-3"></i>
                                                    <h6 class="text-secondary">Tidak ada siswa aktif di kelas ini.</h6>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- MODAL TUJUAN --}}
                            <div class="modal fade" id="modalTujuan" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-gray-100">
                                            <h6 class="modal-title font-weight-bolder">
                                                <i class="fas fa-random text-primary me-2"></i> Form Pindah Kelas
                                            </h6>
                                            <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-info text-dark text-xs mb-4">
                                                <i class="fas fa-info-circle me-1"></i> 
                                                <strong>Info:</strong> Nilai berjalan semester ini akan otomatis dimigrasikan jika mata pelajaran di kelas baru sama.
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-xs fw-bold text-uppercase">Kelas Tujuan</label>
                                                <div class="input-group input-group-outline bg-white rounded">
                                                    <select name="id_kelas_tujuan" class="form-control px-3" required>
                                                        <option value="">-- Pilih Kelas Tujuan --</option>
                                                        @foreach($kelas as $k)
                                                            @if($k->id_kelas != request('id_kelas_asal'))
                                                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-xs fw-bold text-uppercase">Tanggal Pindah</label>
                                                <div class="input-group input-group-outline bg-white rounded is-filled">
                                                    <input type="date" name="tgl_pindah" class="form-control px-3" required value="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-xs fw-bold text-uppercase">Alasan Perpindahan</label>
                                                <div class="input-group input-group-outline bg-white rounded">
                                                    <textarea name="alasan" class="form-control px-3 py-2" rows="2" placeholder="Contoh: Rolling kelas, penyesuaian jurusan..." required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-gray-50 border-top">
                                            <button type="button" class="btn btn-sm btn-white mb-0" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-sm bg-gradient-primary mb-0">Simpan & Proses</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @else
                        {{-- STATE KOSONG --}}
                        <div class="text-center py-6 border rounded bg-gray-50">
                            <i class="fas fa-chalkboard-teacher fa-3x text-secondary mb-3 opacity-3"></i>
                            <h6 class="text-secondary">Silakan pilih kelas terlebih dahulu</h6>
                            <p class="text-xs text-muted">Gunakan filter di atas untuk menampilkan daftar siswa.</p>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- RIWAYAT PERPINDAHAN (Ditampilkan selalu di bawah) --}}
        <div class="row mt-2 mb-4">
            <div class="col-12">
                <div class="card shadow-xs border" id="history-section">
                    <div class="card-header border-bottom p-3 bg-gray-50 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-dark font-weight-bold">
                            <i class="fas fa-history text-secondary me-2"></i> Log Aktivitas Perpindahan Kelas
                        </h6>
                    </div>
                    
                    {{-- FORM FILTER RIWAYAT --}}
                    <div class="p-3 border-bottom bg-white">
                        <form action="{{ route('mutasi.pindah.index') }}#history-section" method="GET" class="row g-2 align-items-center">
                            {{-- Simpan state id_kelas_asal agar tabel atas tidak hilang saat filter history --}}
                            @if(request('id_kelas_asal'))
                                <input type="hidden" name="id_kelas_asal" value="{{ request('id_kelas_asal') }}">
                            @endif

                            <div class="col-md-3">
                                <div class="input-group input-group-outline bg-white rounded {{ request('h_nama') ? 'is-filled' : '' }}">
                                    <input type="text" name="h_nama" class="form-control px-3 text-sm" placeholder="Cari Nama Siswa..." value="{{ request('h_nama') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="input-group input-group-outline bg-white rounded">
                                    <select name="h_kelas" class="form-control px-3 text-sm" onchange="this.form.submit()">
                                        <option value="">-- Semua Kelas --</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id_kelas }}" {{ request('h_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="input-group input-group-outline bg-white rounded">
                                    <select name="h_ta" class="form-control px-3 text-sm" onchange="this.form.submit()">
                                        <option value="">-- Semua Tahun Ajaran --</option>
                                        @foreach($tahunAjaranList as $ta)
                                            <option value="{{ $ta }}" {{ request('h_ta') == $ta ? 'selected' : '' }}>
                                                {{ $ta }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3 text-end">
                                <button type="submit" class="btn btn-sm btn-outline-primary mb-0 w-100">
                                    <i class="fas fa-filter me-1"></i> Filter Data
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- TABEL RIWAYAT --}}
                    <div class="card-body p-0">
                        <div class="table-responsive" style="min-height: 200px;">
                            <table class="table align-items-center mb-0 table-sm table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Tanggal</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Siswa</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Dari Kelas</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2"><i class="fas fa-arrow-right"></i></th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Ke Kelas</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($riwayat as $r)
                                    <tr>
                                        <td class="ps-4 align-middle text-xs text-secondary">
                                            {{ \Carbon\Carbon::parse($r->tgl_pindah)->format('d/m/Y') }}
                                        </td>
                                        <td class="align-middle text-sm font-weight-bold text-dark">
                                            {{ $r->siswa->nama_siswa ?? 'Data Terhapus' }}
                                        </td>
                                        <td class="align-middle text-xs text-secondary">
                                            {{ $r->kelasLama->nama_kelas ?? '-' }}
                                        </td>
                                        <td class="align-middle text-center">
                                            <i class="fas fa-arrow-right text-xs text-muted"></i>
                                        </td>
                                        <td class="align-middle text-xs text-primary font-weight-bold">
                                            {{ $r->kelasBaru->nama_kelas ?? '-' }}
                                        </td>
                                        <td class="align-middle text-xs text-secondary">
                                            {{ Str::limit($r->alasan, 30) }} <br>
                                            <span class="text-xxs fst-italic text-muted">Admin: {{ $r->user_input }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-sm text-secondary">
                                            <i class="fas fa-inbox fa-2x mb-2 opacity-5"></i><br>
                                            Belum ada riwayat perpindahan kelas yang sesuai.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- PAGINATION RIWAYAT --}}
                        <div class="p-3 border-top">
                            {{ $riwayat->links('vendor.pagination.soft-ui') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <x-app.footer />
</main>

<script>
    // Script Check All
    document.addEventListener("DOMContentLoaded", function() {
        const checkAll = document.getElementById('checkAll');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                var checkboxes = document.querySelectorAll('.item-check');
                for (var checkbox of checkboxes) {
                    checkbox.checked = this.checked;
                }
            });
        }
    });
</script>
@endsection