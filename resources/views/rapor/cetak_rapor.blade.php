@extends('layouts.app')

@section('page-title', 'Cetak Rapor Siswa')

@section('content')

@php
    // --- LOGIKA TAHUN AJARAN & SEMESTER OTOMATIS ---
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
    
    $selectedTA = $tahun_ajaran ?? $defaultTahunAjaran;
    $selectedSemester = $semesterRaw ?? $defaultSemester;

    $tahunMulai = $tahunSekarang - 3; // 3 tahun ke belakang
    $tahunAkhir = $tahunSekarang + 3; // 3 tahun ke depan

    $tahunAjaranList = [];

    for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }
    $semesterList = ['Ganjil', 'Genap']; 
@endphp

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />
    <div class="container-fluid py-4 px-5">
        <div class="card shadow-xs border mb-4">
            <div class="card-header bg-gradient-info py-3 d-flex justify-content-between align-items-center">
                <h6 class="text-white mb-0"><i class="fas fa-print me-2"></i> Daftar Cetak Rapor</h6>
                
                @if($id_kelas)
                <button type="button" id="btnSync" onclick="sinkronkanSatuKelas()" class="btn btn-white btn-sm mb-0">
                    <i class="fas fa-sync-alt me-1" id="syncIcon"></i> <span id="syncText">Perbarui Status Data</span>
                </button>
                @endif
            </div>
            
            <div class="card-body">
                {{-- Form Filter --}}
                <form action="{{ route('rapornilai.cetak') }}" method="GET" class="row align-items-end mb-4">
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">Pilih Kelas</label>
                        <select name="id_kelas" class="form-select" required onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ $id_kelas == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">Semester</label>
                        <select name="semester" class="form-select" onchange="this.form.submit()">
                            @foreach($semesterList as $smt)
                                <option value="{{ $smt }}" {{ $selectedSemester == $smt ? 'selected' : '' }}>{{ $smt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">Tahun Ajaran</label>
                        <select name="tahun_ajaran" class="form-select" onchange="this.form.submit()">
                            @foreach($tahunAjaranList as $ta)
                                <option value="{{ $ta }}" {{ $selectedTA == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-dark w-100 mb-0">Tampilkan</button>
                    </div>
                </form>

                @if($id_kelas)
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                <th class="text-xxs font-weight-bolder opacity-7 ps-2">Nama Siswa</th>
                                <th class="text-center text-xxs font-weight-bolder opacity-7">Progress Mapel</th>
                                <th class="text-center text-xxs font-weight-bolder opacity-7">Catatan Wali</th>
                                <th class="text-center text-xxs font-weight-bolder opacity-7">Status Akhir</th>
                                <th class="text-center text-xxs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswaList as $idx => $s)
                            <tr>
                                <td class="text-center text-sm">{{ $idx + 1 }}</td>
                                <td class="text-sm">
                                    <span class="font-weight-bold">{{ $s->nama_siswa }}</span><br>
                                    <small class="text-secondary">{{ $s->nipd }}</small>
                                </td>
                                <td class="text-center text-sm">
                                    @if($s->status_monitoring)
                                        @php
                                            $isComplete = $s->status_monitoring->mapel_tuntas_input >= $s->status_monitoring->total_mapel_seharusnya;
                                        @endphp
                                        <span class="badge {{ $isComplete ? 'bg-gradient-success' : 'bg-light text-dark' }} cursor-pointer" 
                                            onclick="showDetailProgress('{{ $s->id_siswa }}', '{{ $s->nama_siswa }}')"
                                            style="cursor: pointer;"
                                            data-bs-toggle="tooltip" title="Klik untuk lihat detail mapel">
                                            {{ $s->status_monitoring->mapel_tuntas_input }} / {{ $s->status_monitoring->total_mapel_seharusnya }} Mapel
                                        </span>
                                    @else
                                        <span class="text-secondary text-xs italic">Belum disinkronkan</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($s->status_monitoring && $s->status_monitoring->is_catatan_wali_ready)
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="text-success mb-1" data-bs-toggle="tooltip" title="Catatan Tersedia">
                                                <i class="fas fa-check-circle fa-lg"></i>
                                            </span>
                                            <div class="text-xxs text-muted fst-italic px-2" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                "{{ $s->data_catatan->catatan_wali_kelas ?? '-' }}"
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column align-items-center" data-bs-toggle="tooltip" title="Wali kelas belum mengisi catatan">
                                            <span class="text-danger mb-1">
                                                <i class="fas fa-times-circle fa-lg"></i>
                                            </span>
                                            <span class="text-xxs text-danger font-weight-bold">Belum Ada</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($s->status_monitoring && $s->status_monitoring->status_akhir == 'Siap Cetak')
                                        <span class="badge badge-sm bg-gradient-success">Siap Cetak</span>
                                    @else
                                        <span class="badge badge-sm bg-gradient-warning">Belum Lengkap</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($s->status_monitoring && $s->status_monitoring->status_akhir == 'Siap Cetak')
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('rapornilai.cetak_proses', $s->id_siswa) }}?semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}" 
                                            target="_blank" 
                                            class="badge border-0" 
                                            style="background-color: #2196F3; color: white; padding: 8px 16px; font-weight: bold; font-size: 12px; border-radius: 8px; text-decoration: none;">
                                                View
                                            </a>

                                            <a href="{{ route('rapornilai.download_satuan', $s->id_siswa) }}?semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}" 
                                            class="badge border-0" 
                                            style="background-color: #4CAF50; color: white; padding: 8px 16px; font-weight: bold; font-size: 12px; border-radius: 8px; text-decoration: none;">
                                                Download
                                            </a>
                                        </div>
                                    @else
                                        <span class="badge" style="background-color: #f5f5f5; color: #9e9e9e; padding: 8px 16px; border-radius: 8px; font-size: 11px;">
                                            Belum Lengkap
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4">Tidak ada data siswa.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- TOMBOL CETAK MASSAL --}}
                    @if($id_kelas && count($siswaList) > 0)
                        <div class="d-flex justify-content-end p-3 gap-3">
                            {{-- Tombol PDF Massal (Ditambahkan di sisi kiri ZIP) --}}
                            <a href="{{ route('rapornilai.download_massal_pdf') }}?id_kelas={{ $id_kelas }}&semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}" 
                               class="btn bg-gradient-primary mb-0" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> Download Massal (PDF)
                            </a>

                            {{-- Tombol ZIP Massal --}}
                            <button onclick="downloadZipWithLoading('{{ route('rapornilai.download_massal') }}?id_kelas={{ $id_kelas }}&semester={{ $selectedSemester }}&tahun_ajaran={{ $selectedTA }}')" 
                                    class="btn bg-gradient-success mb-0">
                                <i class="fas fa-file-archive me-2"></i> Download Massal (ZIP)
                            </button>
                        </div>
                    @endif

                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-light mb-3"></i>
                    <p class="text-secondary">Pilih kelas untuk melihat daftar rapor.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

{{-- MODAL DETAIL PROGRESS --}}
<div class="modal fade" id="modalDetailProgress" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-gray-100">
                <h6 class="modal-title font-weight-bolder text-dark">
                    <i class="fas fa-list-check text-info me-2"></i> Detail Progress Nilai: <span id="modalStudentName" class="text-primary"></span>
                </h6>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-xxs font-weight-bolder opacity-7 ps-3">Mata Pelajaran</th>
                                <th class="text-center text-xxs font-weight-bolder opacity-7">Status Input</th>
                                <th class="text-center text-xxs font-weight-bolder opacity-7">Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody id="listDetailMapel">
                            {{-- Data via AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-gray-100">
                <button type="button" class="btn btn-sm btn-dark mb-0" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

function sinkronkanSatuKelas() {
    const btn = $('#btnSync');
    const icon = $('#syncIcon');

    Swal.fire({
        title: 'Perbarui Data?',
        text: "Sistem akan menghitung ulang progres nilai dan catatan untuk kelas ini.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Sinkronkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang Memproses',
                html: 'Mohon tunggu sebentar...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            btn.prop('disabled', true);
            icon.addClass('fa-spin');

            $.ajax({
                url: "{{ route('rapornilai.sinkronkan_kelas') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_kelas: "{{ $id_kelas }}",
                    semester: "{{ $selectedSemester }}",
                    tahun_ajaran: "{{ $selectedTA }}"
                },
                success: function(res) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1500, showConfirmButton: false })
                    .then(() => { window.location.reload(); });
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    icon.removeClass('fa-spin');
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan' });
                }
            });
        }
    });
}

function showDetailProgress(idSiswa, namaSiswa) {
    // 1. Tampilkan Modal & Loading State
    $('#modalStudentName').text(namaSiswa);
    $('#listDetailMapel').html('<tr><td colspan="3" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Mengambil data...</td></tr>');
    $('#modalDetailProgress').modal('show');

    // 2. Jalankan AJAX
    $.ajax({
        url: "{{ route('rapornilai.detail_progress') }}",
        method: "GET",
        data: {
            id_siswa: idSiswa,
            id_kelas: "{{ $id_kelas }}",
            semester: "{{ $selectedSemester }}",
            tahun_ajaran: "{{ $selectedTA }}"
        },
        success: function(res) {
            let html = '';
            if (res.data && res.data.length > 0) {
                res.data.forEach(function(item) {
                    // Warna baris transparan hijau jika lengkap, merah jika belum
                    let rowStyle = item.is_lengkap 
                        ? 'background-color: rgba(45, 206, 137, 0.05);' 
                        : 'background-color: rgba(245, 54, 88, 0.05);';
                    
                    let badgeClass = item.is_lengkap ? 'bg-gradient-success' : 'bg-gradient-danger';
                    let statusText = item.is_lengkap ? 'Lengkap' : 'Belum Input';
                    let iconStatus = item.is_lengkap 
                        ? '<i class="fas fa-check-circle text-success me-2"></i>' 
                        : '<i class="fas fa-exclamation-circle text-danger me-2"></i>';

                    html += `
                        <tr style="${rowStyle}">
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    ${iconStatus}
                                    <div class="d-flex flex-column text-start">
                                        <span class="text-sm font-weight-bold text-dark">${item.nama_mapel}</span>
                                        <span class="text-xxs text-secondary text-uppercase">${item.kategori}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-sm ${badgeClass}" style="min-width: 90px;">
                                    ${statusText}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-sm font-weight-bolder ${item.is_lengkap ? 'text-dark' : 'text-muted'}">
                                    ${item.nilai_akhir}
                                </span>
                            </td>
                        </tr>`;
                });
            } else {
                html = '<tr><td colspan="3" class="text-center py-4 text-muted">Tidak ada data mapel untuk siswa ini.</td></tr>';
            }
            // Update isi tabel modal
            $('#listDetailMapel').html(html);
        },
        error: function(xhr) {
            console.error(xhr);
            let errorText = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memuat data.';
            $('#listDetailMapel').html(`<tr><td colspan="3" class="text-center text-danger py-4">${errorText}</td></tr>`);
        }
    });
}

function downloadZipWithLoading(url) {
    Swal.fire({
        title: 'Sedang Memproses...',
        text: 'Sistem sedang menyiapkan paket ZIP. Proses ini mungkin memakan waktu tergantung jumlah siswa.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => { Swal.showLoading(); }
    });

    window.location.href = url;

    setTimeout(() => { Swal.close(); }, 5000); 
}
</script>
@endsection