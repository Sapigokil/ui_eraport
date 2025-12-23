@extends('layouts.app')

@section('title', 'Monitoring Nilai per Mata Pelajaran')

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
    
    // Ambil dari request jika ada, jika tidak gunakan default
    $selectedTA = request('tahun_ajaran', $defaultTahunAjaran);
    $selectedSemester = request('semester', $defaultSemester);

    $tahunMulai = 2024; 
    $tahunAkhir = date('Y') + 2; 
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
                    <div class="card my-4 shadow-xs border">
                        
                        {{-- KONTROL ATAS: HEADER --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0"><i class="fas fa-chart-line me-2"></i> Monitoring Progres Nilai Mapel</h6>
                                @if(request('id_kelas'))
                                    <button id="btnSinkronkan" class="btn bg-gradient-light me-3 mb-0">
                                        <i class="fas fa-sync-alt me-1"></i> Sinkronkan Semua
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            {{-- FILTER FORM --}}
                            <div class="p-4 border-bottom bg-light-gray">
                                <form action="{{ route('rapornilai.index') }}" method="GET" class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label font-weight-bold">Pilih Kelas:</label>
                                        <select class="form-select" name="id_kelas" required>
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach ($kelas as $k)
                                                <option value="{{ $k->id_kelas }}" {{ $id_kelas == $k->id_kelas ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label font-weight-bold">Semester:</label>
                                        <select class="form-select" name="semester">
                                            @foreach($semesterList as $smt)
                                                <option value="{{ $smt }}" {{ $selectedSemester == $smt ? 'selected' : '' }}>{{ $smt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label font-weight-bold">Tahun Ajaran:</label>
                                        <select class="form-select" name="tahun_ajaran">
                                            @foreach($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}" {{ $selectedTA == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end mb-3">
                                        <button type="submit" class="btn btn-info w-100 mb-0">Tampilkan</button>
                                    </div>
                                </form>
                            </div>

                            {{-- TABEL MONITORING MAPEL --}}
                            <div class="table-responsive p-0 mt-3">
                                @if (empty($monitoring))
                                    <p class="text-secondary text-center text-sm my-5">
                                        Pilih kelas untuk melihat progres penginputan nilai (Min. 3 Komponen per Mapel).
                                    </p>
                                @else
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Mata Pelajaran</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sudah Tuntas</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Belum Lengkap</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Progres</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($monitoring as $m)
                                            <tr>
                                                <td class="ps-3">
                                                    <h6 class="mb-0 text-sm font-weight-bold">{{ $m->nama_mapel }}</h6>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-link text-success p-0 mb-0 font-weight-bolder" 
                                                            onclick="showDetailSiswa('{{ $m->id_mapel }}', 'tuntas', '{{ $m->nama_mapel }}')">
                                                        {{ $m->tuntas }} Siswa <i class="fas fa-search ms-1 text-xs text-secondary"></i>
                                                    </button>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-link text-danger p-0 mb-0 font-weight-bolder" 
                                                            onclick="showDetailSiswa('{{ $m->id_mapel }}', 'belum', '{{ $m->nama_mapel }}')">
                                                        {{ $m->belum }} Siswa <i class="fas fa-search ms-1 text-xs text-secondary"></i>
                                                    </button>
                                                </td>
                                                <td class="align-middle text-center">
                                                    @php $persen = ($m->total_siswa > 0) ? ($m->tuntas / $m->total_siswa) * 100 : 0; @endphp
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <span class="me-2 text-xs font-weight-bold">{{ round($persen) }}%</span>
                                                        <div class="progress w-50" style="height: 6px;">
                                                            <div class="progress-bar bg-{{ $persen == 100 ? 'success' : ($persen > 50 ? 'info' : 'warning') }}" 
                                                                 role="progressbar" style="width: {{ $persen }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>

    {{-- MODAL DETAIL SISWA --}}
    <div class="modal fade" id="modalDetailSiswa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-gray-100">
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0" id="judulModal">Detail Siswa</h6>
                        <small id="subJudulModal" class="text-secondary"></small>
                    </div>
                    <button type="button" class="btn-close text-dark font-weight-bold" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-group list-group-flush" id="listSiswaDetail"></ul>
                </div>
                <div class="modal-footer bg-gray-50 p-2">
                    <button type="button" class="btn btn-sm btn-secondary mb-0" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showDetailSiswa(idMapel, tipe, namaMapel) {
            let id_kelas = "{{ $id_kelas }}";
            let semester = "{{ $selectedSemester }}";
            let tahun_ajaran = "{{ $selectedTA }}";
            
            $('#judulModal').text(tipe === 'tuntas' ? 'Siswa Sudah Tuntas' : 'Siswa Belum Lengkap');
            $('#subJudulModal').text(namaMapel);
            $('#listSiswaDetail').html('<li class="list-group-item text-center">Memuat...</li>');
            $('#modalDetailSiswa').modal('show');

            $.ajax({
                url: "{{ route('rapornilai.detail_siswa') }}",
                method: "GET",
                data: { id_mapel: idMapel, id_kelas: id_kelas, tipe: tipe, semester: semester, tahun_ajaran: tahun_ajaran },
                success: function(data) {
                    let html = '';
                    if(data.length > 0) {
                        data.forEach(siswa => {
                            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex flex-column">
                                            <span class="text-sm font-weight-bold text-dark">${siswa.nama_siswa}</span>
                                            <small class="text-xs text-secondary">NIS: ${siswa.nis || '-'}</small>
                                        </div>
                                        <i class="fas ${tipe === 'tuntas' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'}"></i>
                                     </li>`;
                        });
                    } else {
                        html = '<li class="list-group-item text-center text-secondary">Data tidak ditemukan.</li>';
                    }
                    $('#listSiswaDetail').html(html);
                },
                error: function() {
                    $('#listSiswaDetail').html('<li class="list-group-item text-danger text-center text-xs">Gagal mengambil data.</li>');
                }
            });
        }

        $(document).ready(function() {
            $('#btnSinkronkan').click(function() {
                let id_kelas = "{{ $id_kelas }}";
                let semester = "{{ $selectedSemester }}";
                let tahun_ajaran = "{{ $selectedTA }}";

                Swal.fire({
                    title: 'Sinkronisasi Data',
                    text: 'Menghitung ulang progres rapor seluruh siswa...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() }
                });

                $.ajax({
                    url: "{{ route('rapornilai.sinkronkan') }}",
                    method: "POST",
                    data: { _token: "{{ csrf_token() }}", id_kelas: id_kelas, semester: semester, tahun_ajaran: tahun_ajaran },
                    success: function(response) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', timer: 1500, showConfirmButton: false })
                        .then(() => { location.reload(); });
                    },
                    error: function() { Swal.fire('Error', 'Gagal sinkronisasi', 'error'); }
                });
            });
        });
    </script>
@endsection