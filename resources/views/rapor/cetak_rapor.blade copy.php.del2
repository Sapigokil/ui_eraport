@extends('layouts.app')

@section('page-title', 'Cetak Rapor Siswa')

@section('content')

@php
    // --- LOGIKA DEFAULT PERIODE (Agar dropdown tidak kosong saat pertama buka) ---
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
    
    // Ambil dari request controller atau gunakan default
    $selectedTA = $tahun_ajaran ?? $defaultTahunAjaran;
    $selectedSemester = $semesterRaw ?? $defaultSemester;

    // List Tahun Ajaran (3 tahun ke belakang & depan)
    $tahunMulai = $tahunSekarang - 3; 
    $tahunAkhir = $tahunSekarang + 3; 
    $tahunAjaranList = [];
    for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }
    $semesterList = ['Ganjil', 'Genap']; 
@endphp

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-xs border mb-4">
                    
                    {{-- HEADER CARD --}}
                    <div class="card-header bg-gradient-info py-3 d-flex justify-content-between align-items-center">
                        <h6 class="text-white mb-0">
                            <i class="fas fa-print me-2"></i> Cetak & Finalisasi Rapor
                        </h6>
                    </div>
                    
                    <div class="card-body px-0 pb-2">
                        
                        {{-- 1. FORM FILTER (Auto Submit) --}}
                        <div class="px-4 py-3 border-bottom bg-gray-100">
                            <form action="{{ route('rapornilai.cetak') }}" method="GET">
                                <div class="row align-items-end mb-0">
                                    
                                    {{-- Filter Kelas --}}
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold text-xs text-uppercase">Kelas</label>
                                        <select name="id_kelas" class="form-select bg-white" required onchange="this.form.submit()">
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas', $id_kelas) == $k->id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Filter Semester --}}
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold text-xs text-uppercase">Semester</label>
                                        <select name="semester" class="form-select bg-white" onchange="this.form.submit()">
                                            @foreach($semesterList as $smt)
                                                <option value="{{ $smt }}" {{ request('semester', $selectedSemester) == $smt ? 'selected' : '' }}>
                                                    {{ $smt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Filter Tahun Ajaran --}}
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold text-xs text-uppercase">Tahun Ajaran</label>
                                        <select name="tahun_ajaran" class="form-select bg-white" onchange="this.form.submit()">
                                            @foreach($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}" {{ request('tahun_ajaran', $selectedTA) == $ta ? 'selected' : '' }}>
                                                    {{ $ta }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                <button type="submit" class="d-none"></button>
                            </form>
                        </div>

                        {{-- 2. INFORMASI SISTEM (ALERT) --}}
                        @if($id_kelas)
                        <div class="px-4 mt-3">
                            <div class="alert alert-light border-start border-info border-4 shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 text-info">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading fw-bold mb-1 text-dark text-sm">Mekanisme Cetak Rapor:</h6>
                                        <ul class="mb-0 text-xs text-secondary ps-3">
                                            <li>Tombol <strong>"Generate & Kunci"</strong> akan menyimpan kondisi nilai saat ini (Snapshot).</li>
                                            <li>Anda hanya bisa mencetak rapor jika status sudah <strong>FINAL / TERKUNCI</strong>.</li>
                                            <li>Jika ada revisi nilai, klik tombol <strong>"Revisi / Buka Kunci"</strong>, perbaiki data, lalu kunci kembali.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. TABEL DAFTAR SISWA --}}
                        <div class="table-responsive p-0 mt-3">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Siswa</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Rapor</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Update Terakhir</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($siswaList as $idx => $s)
                                    <tr>
                                        <td class="text-center text-sm text-secondary">{{ $idx + 1 }}</td>
                                        <td class="text-sm">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $s->nama_siswa }}</h6>
                                                <p class="text-xs text-secondary mb-0">
                                                    {{ $s->nisn ? 'NISN: '.$s->nisn : ($s->nipd ? 'NIPD: '.$s->nipd : '-') }}
                                                </p>
                                            </div>
                                        </td>
                                        
                                        {{-- KOLOM STATUS --}}
                                        <td class="text-center align-middle">
                                            @if($s->is_locked)
                                                <span class="badge badge-sm bg-gradient-success">
                                                    <i class="fas fa-lock me-1"></i> FINAL / TERKUNCI
                                                </span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-secondary">
                                                    <i class="fas fa-edit me-1"></i> DRAFT / EDITABLE
                                                </span>
                                            @endif
                                        </td>

                                        {{-- KOLOM TANGGAL UPDATE --}}
                                        <td class="text-center align-middle text-xs text-secondary">
                                            @if($s->last_update)
                                                <span class="d-block font-weight-bold text-dark">{{ \Carbon\Carbon::parse($s->last_update)->format('d M Y') }}</span>
                                                <span>{{ \Carbon\Carbon::parse($s->last_update)->format('H:i') }} WIB</span>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- KOLOM AKSI --}}
                                        <td class="text-center align-middle">
                                            @if($s->is_locked)
                                                {{-- JIKA SUDAH FINAL: Tampilkan Tombol Cetak & Revisi --}}
                                                <div class="d-flex justify-content-center gap-2">
                                                    {{-- Cetak PDF --}}
                                                    <a href="{{ route('rapornilai.cetak_proses', $s->id_siswa) }}?semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}" 
                                                       target="_blank" 
                                                       class="btn btn-xs bg-gradient-info mb-0 px-3"
                                                       data-bs-toggle="tooltip" title="Download PDF Rapor">
                                                        <i class="fas fa-print me-1"></i> Cetak
                                                    </a>

                                                    {{-- Tombol Buka Kunci / Revisi --}}
                                                    <button type="button" 
                                                            onclick="unlockRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" 
                                                            class="btn btn-xs btn-outline-danger mb-0 px-3"
                                                            data-bs-toggle="tooltip" title="Buka Kunci untuk Edit Nilai">
                                                        <i class="fas fa-lock-open me-1"></i> Revisi
                                                    </button>
                                                </div>
                                            @else
                                                {{-- JIKA MASIH DRAFT: Tampilkan Tombol Generate --}}
                                                <button type="button" 
                                                        onclick="generateRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" 
                                                        class="btn btn-xs bg-gradient-primary mb-0 px-4">
                                                    <i class="fas fa-save me-1"></i> Generate & Kunci
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-secondary">
                                            <i class="fas fa-user-slash fa-2x mb-3 opacity-5"></i>
                                            <p class="mb-0 text-sm">Tidak ada siswa ditemukan di kelas ini.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- 4. TOMBOL DOWNLOAD MASSAL (FOOTER) --}}
                        @if(count($siswaList) > 0)
                        <div class="d-flex justify-content-end p-4 gap-3 bg-gray-100 mt-2 border-top">
                            <a href="{{ route('rapornilai.download_massal_pdf') }}?id_kelas={{ $id_kelas }}&semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}" 
                               class="btn btn-outline-primary btn-sm mb-0" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> Download Semua (1 File PDF)
                            </a>

                            <button onclick="downloadZipWithLoading('{{ route('rapornilai.cetak_massal') }}?id_kelas={{ $id_kelas }}&semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}')" 
                                    class="btn bg-gradient-success btn-sm mb-0">
                                <i class="fas fa-file-archive me-2"></i> Download Semua (ZIP)
                            </button>
                        </div>
                        @endif

                        @else
                        {{-- STATE BELUM PILIH KELAS --}}
                        <div class="text-center py-6">
                            <div class="icon icon-shape bg-gradient-info shadow-info text-center border-radius-xl mb-3">
                                <i class="fas fa-search fa-lg opacity-10" aria-hidden="true"></i>
                            </div>
                            <h5 class="mt-2">Pilih Kelas Terlebih Dahulu</h5>
                            <p class="text-secondary text-sm">Silakan pilih kelas, semester, dan tahun ajaran pada filter di atas.</p>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <x-app.footer />
    </div>
</main>

{{-- SCRIPTS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Inisialisasi Tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // --- FUNGSI 1: GENERATE / KUNCI RAPOR ---
    function generateRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Kunci Data Rapor?',
            html: `Anda akan memproses rapor untuk <b>${namaSiswa}</b>.<br>
                   <span class="text-danger text-xs">Nilai dan catatan akan disalin dan dikunci.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kunci Data!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Data...',
                    html: 'Mohon tunggu, sedang menyalin snapshot nilai.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "{{ route('rapornilai.generate_rapor') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_siswa: idSiswa,
                        semester: "{{ $selectedSemester }}",
                        tahun_ajaran: "{{ $selectedTA }}"
                    },
                    success: function(res) {
                        Swal.fire('Berhasil!', res.message, 'success')
                        .then(() => { location.reload(); });
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            }
        });
    }

    // --- FUNGSI 2: UNLOCK / REVISI RAPOR ---
    function unlockRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Buka Kunci / Revisi?',
            html: `Status rapor <b>${namaSiswa}</b> akan diubah menjadi <b>DRAFT</b>.<br>
                   <span class="text-muted text-xs">Guru Mapel & Wali Kelas dapat mengedit data kembali.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Buka Kunci!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Membuka Kunci...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "{{ route('rapornilai.unlock_rapor') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_siswa: idSiswa,
                        semester: "{{ $selectedSemester }}",
                        tahun_ajaran: "{{ $selectedTA }}"
                    },
                    success: function(res) {
                        Swal.fire('Terbuka!', res.message, 'success')
                        .then(() => { location.reload(); });
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            }
        });
    }

    // --- FUNGSI 3: DOWNLOAD ZIP ANIMATION ---
    function downloadZipWithLoading(url) {
        Swal.fire({
            title: 'Sedang Mengompres...',
            text: 'Sistem sedang menyiapkan file ZIP berisi rapor siswa yang berstatus FINAL.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Trigger download
        window.location.href = url;

        // Tutup loading setelah delay (estimasi)
        setTimeout(() => { Swal.close(); }, 5000); 
    }
</script>

@endsection