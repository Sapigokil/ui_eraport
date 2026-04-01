{{-- File: resources/views/pkl/rapor/index.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Cetak Rapor PKL')

@section('content')

@php
    $totalSiswa = isset($finalSiswaList) ? count($finalSiswaList) : 0;
    $finalCount = isset($finalSiswaList) ? $finalSiswaList->whereIn('status_rapor', ['final', 'cetak'])->count() : 0;
    $rawCount   = isset($finalSiswaList) ? $finalSiswaList->where('status_rapor', '!=', 'belum_generate')->count() : 0;
    $persenFinal = $totalSiswa > 0 ? round(($finalCount / $totalSiswa) * 100) : 0;
    $persenRaw   = $totalSiswa > 0 ? round(($rawCount / $totalSiswa) * 100) : 0;
@endphp

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- CARD FILTER --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-body p-3">
                <form action="{{ route('pkl.rapor.cetak.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Pilih Kelas</label>
                        <select name="id_kelas" class="form-select border-secondary" required onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas', $id_kelas) == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }} ({{ $k->wali_kelas ?? 'Tanpa Wali' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Semester</label>
                        <select name="semester" class="form-select border-secondary" onchange="this.form.submit()">
                            @foreach($semesterList as $smt)
                                <option value="{{ $smt }}" {{ request('semester', $semesterRaw) == $smt ? 'selected' : '' }}>{{ $smt == 1 ? 'Ganjil' : 'Genap' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Tahun Ajaran</label>
                        <select name="tahun_ajaran" class="form-select border-secondary" onchange="this.form.submit()">
                            @foreach($tahunAjaranList as $ta)
                                <option value="{{ $ta }}" {{ request('tahun_ajaran', $tahun_ajaran) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="submit" class="btn btn-primary w-100 mb-0"><i class="fas fa-sync-alt me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        @if($id_kelas && $kelasAktif)
        
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
                                <h3 class="text-white font-weight-bold mb-1">{{ $kelasAktif->nama_kelas }}</h3>
                                <p class="text-white opacity-8 mb-2"><i class="fas fa-user-tie me-2"></i> Wali Kelas: {{ $kelasAktif->wali_kelas }}</p>
                                <span class="badge border border-white text-white fw-bold bg-transparent">
                                    Semester {{ $semesterRaw == 1 ? 'Ganjil' : 'Genap' }} - {{ $tahun_ajaran }}
                                </span>
                            </div>
                            <div class="col-md-5 text-end mt-4 mt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-between gap-4">
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Rapor Digenerate</span>
                                        <h4 class="text-white mb-0">{{ $rawCount }} <span class="text-sm fw-normal opacity-8">/ {{ $totalSiswa }} Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $persenRaw }}%"></div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Siap Cetak</span>
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

        {{-- REVISI: PANEL SET INPUT TANGGAL CETAK --}}
        <div class="row mb-4">
            <div class="col-md-5">
                <div class="card shadow-xs border border-warning">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-light shadow-none text-center border-radius-md me-3">
                                <i class="fas fa-calendar-alt text-warning opacity-10"></i>
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label mb-0 text-xs font-weight-bold text-uppercase">Set Tanggal Cetak Rapor</label>
                                <input type="date" id="tgl_cetak_global" class="form-control form-control-sm border-warning" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <p class="text-xxs text-secondary mt-2 mb-0 italic">
                            <i class="fas fa-info-circle me-1"></i> Tanggal ini akan muncul sebagai titimangsa (tanda tangan) di PDF.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- KONTEN TABEL SISWA --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border">
                    <div class="card-header p-3 bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-list-ul me-2"></i> Daftar Rapor PKL Siswa</h6>
                            
                            <div class="d-flex gap-2">
                                <a href="{{ route('pkl.rapor.monitoring.index', ['buka_kelas' => $id_kelas]) }}" target="_blank" class="btn btn-sm btn-outline-info mb-0">
                                    <i class="fas fa-search-location me-2"></i> Cek Monitoring Penilaian
                                </a>

                                @if($finalCount > 0)
                                <button onclick="cetakMassal()" class="btn btn-sm btn-outline-primary mb-0">
                                    <i class="fas fa-file-pdf me-2"></i> Download & Merge PDF
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                        <th class="ps-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 30%">Nama Siswa</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kesiapan Guru</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Rapor</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Terakhir Update</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($finalSiswaList as $idx => $s)
                                    <tr>
                                        <td class="text-center text-sm text-secondary">{{ $idx + 1 }}</td>
                                        <td class="ps-3">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $s->nama_siswa }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $s->nisn }}</p>
                                                @if($s->status_siswa == 'history_moved')
                                                    <span class="badge badge-xxs bg-gradient-secondary mt-1 w-auto" style="width: fit-content;">
                                                        <i class="fas fa-history me-1"></i> Data Arsip (Mutasi/Alumni)
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        
                                        <td class="text-center align-middle">
                                            @if($s->status_rapor == 'belum_generate')
                                                @if($s->status_guru == 'siap')
                                                    <span class="text-xs font-weight-bold text-success">
                                                        <i class="fas fa-check-circle me-1"></i> Data Siap
                                                    </span>
                                                @elseif($s->status_guru == 'belum_siap')
                                                    <span class="text-xs font-weight-bold text-warning">
                                                        <i class="fas fa-edit me-1"></i> Draft Guru
                                                    </span>
                                                @else
                                                    <span class="text-xs font-weight-bold text-danger">
                                                        <i class="fas fa-times-circle me-1"></i> Belum Ada
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-xs font-weight-bold text-secondary">
                                                    <i class="fas fa-link me-1"></i> Tersinkron
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center align-middle">
                                            @if($s->status_rapor == 'belum_generate')
                                                <span class="badge badge-sm bg-gradient-light text-secondary border">BELUM DIGENERATE</span>
                                            @elseif($s->status_rapor == 'draft')
                                                <span class="badge badge-sm bg-gradient-info">DRAFT</span>
                                            @elseif($s->status_rapor == 'final')
                                                <span class="badge badge-sm bg-gradient-success">SIAP CETAK</span>
                                            @elseif($s->status_rapor == 'cetak')
                                                <span class="badge badge-sm bg-gradient-dark">SUDAH DICETAK</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($s->last_update)
                                                <span class="text-xs font-weight-bold d-block">{{ \Carbon\Carbon::parse($s->last_update)->format('d M Y') }}</span>
                                                <span class="text-xxs text-secondary">{{ \Carbon\Carbon::parse($s->last_update)->format('H:i') }} WIB</span>
                                            @else
                                                <span class="text-xs text-secondary">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                @if($s->is_ready_print)
                                                    <button onclick="cetakSatuan('{{ $s->id_siswa }}')" class="btn btn-xs bg-gradient-primary mb-0 px-3" data-bs-toggle="tooltip" title="Cetak PDF">
                                                        <i class="fas fa-print me-1"></i> Cetak
                                                    </button>
                                                    <button onclick="unlockRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" class="btn btn-xs btn-outline-danger mb-0 px-3" data-bs-toggle="tooltip" title="Buka Kunci untuk Edit/Update">
                                                        <i class="fas fa-lock"></i> Buka Kunci
                                                    </button>
                                                @elseif($s->status_rapor == 'draft')
                                                    <button onclick="regenerateRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" class="btn btn-xs bg-gradient-warning mb-0 px-3" data-bs-toggle="tooltip" title="Tarik data nilai terbaru dari Guru">
                                                        <i class="fas fa-sync-alt me-1"></i> Perbarui Data
                                                    </button>
                                                    <button onclick="finalisasiRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" class="btn btn-xs bg-gradient-info mb-0 px-3">
                                                        <i class="fas fa-check-circle me-1"></i> Finalisasi
                                                    </button>
                                                @else
                                                    @if($s->status_guru == 'siap')
                                                        <button onclick="generateRaporAdmin('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" class="btn btn-xs bg-gradient-secondary mb-0 px-3">
                                                            <i class="fas fa-cog me-1"></i> Generate
                                                        </button>
                                                    @else
                                                        <button disabled class="btn btn-xs bg-light text-secondary border mb-0 px-3" data-bs-toggle="tooltip" title="Guru belum memfinalisasi nilai anak ini.">
                                                            <i class="fas fa-lock me-1"></i> Menunggu Guru
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-secondary">
                                            <i class="fas fa-folder-open fa-2x mb-3 opacity-5"></i><br>
                                            Tidak ada data siswa ditemukan untuk periode ini.
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
                <i class="fas fa-search fa-lg opacity-10" aria-hidden="true"></i>
            </div>
            <h5 class="mt-2">Pilih Kelas Terlebih Dahulu</h5>
            <p class="text-sm text-secondary">Silakan gunakan filter di atas untuk menampilkan daftar rapor siswa.</p>
        </div>
        @endif

    </div>
    <x-app.footer />
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) })

    // REVISI: FUNGSI CEK TANGGAL DAN REDIRECT CETAK
    function getTglCetak() {
        const tgl = $('#tgl_cetak_global').val();
        if(!tgl) {
            Swal.fire('Perhatian!', 'Silakan pilih Tanggal Cetak Rapor terlebih dahulu pada kotak kuning.', 'warning');
            return null;
        }
        return tgl;
    }

    function cetakSatuan(idSiswa) {
        const tgl = getTglCetak();
        if(!tgl) return;

        const url = "{{ route('pkl.rapor.cetak_proses', ':id') }}"
            .replace(':id', idSiswa) + 
            `?semester={{ $semesterRaw }}&tahun_ajaran={{ $tahun_ajaran }}&tgl_cetak=${tgl}`;
        
        window.open(url, '_blank');
    }

    function cetakMassal() {
        const tgl = getTglCetak();
        if(!tgl) return;

        const url = "{{ route('pkl.rapor.download_massal_merge') }}?" + 
            `id_kelas={{ $id_kelas }}&semester={{ $semesterRaw }}&tahun_ajaran={{ $tahun_ajaran }}&tgl_cetak=${tgl}`;
        
        window.open(url, '_blank');
    }

    // AJAX ACTION HELPER
    function actionAjax(url, idSiswa) {
        Swal.fire({title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
        $.ajax({
            url: url,
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id_siswa: idSiswa,
                id_kelas: "{{ $id_kelas }}", 
                semester: "{{ $semesterRaw }}",
                tahun_ajaran: "{{ $tahun_ajaran }}"
            },
            success: function(res) {
                if(res.status === 'success') {
                    Swal.fire('Berhasil!', res.message || 'Sukses.', 'success').then(() => { location.reload(); });
                } else {
                    Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                Swal.fire('Error!', msg, 'error');
            }
        });
    }

    function generateRaporAdmin(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Generate Rapor PKL?',
            text: `Sistem akan menarik data nilai dari guru pembimbing untuk ${namaSiswa}.`,
            icon: 'info', showCancelButton: true, confirmButtonText: 'Ya, Generate!', confirmButtonColor: '#344767'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('pkl.rapor.generate') }}", idSiswa); });
    }

    function regenerateRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Perbarui Nilai?',
            text: `Update nilai akan menarik ulang data dari guru pembimbing dan menimpa draf rapor yang ada. Lanjutkan?`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Update!', confirmButtonColor: '#fb8c00'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('pkl.rapor.generate') }}", idSiswa); });
    }

    function finalisasiRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Finalisasi Rapor?',
            text: `Pastikan draft nilai sudah benar. Status rapor ${namaSiswa} akan dikunci menjadi SIAP CETAK.`,
            icon: 'success', showCancelButton: true, confirmButtonText: 'Ya, Finalisasi!', confirmButtonColor: '#17ad37'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('pkl.rapor.finalisasi') }}", idSiswa); });
    }

    function unlockRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Buka Kunci Rapor?',
            text: `Status rapor ${namaSiswa} akan dikembalikan ke DRAFT agar datanya bisa ditarik ulang.`,
            icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Buka Kunci!', confirmButtonColor: '#ea0606'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('pkl.rapor.unlock') }}", idSiswa); });
    }
</script>
@endsection