{{-- File: resources/views/nilai/sum1_index.blade.php --}}

@extends('layouts.app') 

@section('page-title', 'Input Nilai Sumatif ' . $sumatifId)

@php
    // LOGIKA TAHUN AJARAN & SEMESTER
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
    
    $tahunMulai = $tahunSekarang - 3; // 3 tahun ke belakang
    $tahunAkhir = $tahunSekarang + 3; // 3 tahun ke depan

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
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas fa-edit me-2"></i> Input Nilai Sumatif {{ $sumatifId }}
                                </h6>
                                <div class="pe-3">
                                    <button class="btn bg-gradient-light text-dark btn-sm mb-0 me-2" data-bs-toggle="modal" data-bs-target="#downloadTemplateModal">
                                        <i class="fas fa-file-excel me-1"></i> Download Template
                                    </button>
                                    <button class="btn bg-gradient-success btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="fas fa-file-import me-1"></i> Import
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm"><strong>Sukses!</strong> {!! session('success') !!}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                                </div>
                            @endif
                            
                            @if (session('error'))
                                <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                                </div>
                            @endif
                            {{-- Notifikasi untuk karakter TP --}}
                            @if ($errors->any())
                                <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white fade show">
                                    <strong>Gagal!</strong>
                                    <ul class="mb-0">
                                        @foreach ($errors->messages() as $field => $messages)
                                            @foreach ($messages as $message)
                                                @if (str_contains($field, 'tujuan_pembelajaran'))
                                                    <li class="text-sm">
                                                        Tujuan Pembelajaran mengandung karakter tidak valid.
                                                    </li>
                                                @else
                                                    <li class="text-sm">{{ $message }}</li>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert">&times;</button>
                                </div>
                            @endif

                            
                            {{-- Form Filter --}}
                            <div class="p-4 border-bottom">
                                <form action="{{ route('master.sumatif.s1') }}" method="GET" class="row align-items-end">
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Sumatif:</label>
                                        <select name="sumatif" required class="form-select" disabled>
                                            <option value="{{ $sumatifId }}" selected>Sumatif {{ $sumatifId }}</option>
                                        </select>
                                        <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="id_kelas" class="form-label">Kelas:</label>
                                        <select name="id_kelas" id="id_kelas" required class="form-select" onchange="this.form.submit()">
                                            <option value="">Pilih Kelas</option>
                                            @foreach(\App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label for="id_mapel" class="form-label">Mata Pelajaran:</label>
                                        <select name="id_mapel" id="id_mapel" required class="form-select" {{ !request('id_kelas') ? 'disabled' : '' }} onchange="this.form.submit()">
                                            <option value="">Pilih Mapel</option>
                                            @foreach ($mapel as $m)
                                                <option value="{{ $m->id_mapel }}" {{ request('id_mapel') == $m->id_mapel ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Semester:</label>
                                        <select name="semester" required class="form-select">
                                            @foreach($semesterList as $sem)
                                                <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Tahun Ajaran:</label>
                                        <select name="tahun_ajaran" required class="form-select">
                                            @foreach ($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}" {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn bg-gradient-info w-25 mb-0">Tampilkan Siswa</button>
                                    </div>
                                </form>
                            </div>

                            <div class="p-4">
                                @if(!request('id_kelas') || !request('id_mapel'))
                                    <p class="text-secondary mt-3 p-3 text-center border rounded">Silakan lengkapi filter untuk mengisi nilai.</p>
                                @elseif($siswa->isEmpty())
                                    <p class="text-danger mt-3 p-3 text-center border rounded">Tidak ada siswa ditemukan.</p>
                                @else
                                @if(!$seasonOpen)
                                    <div class="alert alert-warning text-sm mb-3">
                                        🔒 Input nilai dikunci karena season tidak aktif.
                                    </div>
                                @endif
                                    <form action="{{ route('master.sumatif.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="id_kelas" value="{{ request('id_kelas') }}">
                                        <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                                        <input type="hidden" name="tahun_ajaran" value="{{ request('tahun_ajaran') }}">
                                        <input type="hidden" name="semester" value="{{ request('semester') }}">
                                        <input type="hidden" name="id_mapel" value="{{ request('id_mapel') }}">

                                        <div class="table-responsive p-0">
                                            <table class="table align-items-center mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="text-xxs font-weight-bolder opacity-7 text-center" style="width: 5%">No</th>
                                                        <th class="text-xxs font-weight-bolder opacity-7">Nama Siswa</th>
                                                        <th class="text-xxs font-weight-bolder opacity-7 text-center" style="width: 15%">Nilai (0-100)</th>
                                                        <th class="text-xxs font-weight-bolder opacity-7">Tujuan Pembelajaran</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($siswa as $i => $s)
                                                    @php
                                                        $raporItem = $rapor->get($s->id_siswa);
                                                        $nilaiLama = optional($raporItem);
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center text-sm font-weight-bold">{{ $i+1 }}</td>
                                                        <td class="text-sm font-weight-bold">
                                                            {{ $s->nama_siswa }}
                                                            <input type="hidden" name="id_siswa[]" value="{{ $s->id_siswa }}">
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-outline">
                                                                <input type="number" name="nilai[]" min="0" max="100" class="form-control text-center" 
                                                                value="{{ old('nilai.' . $i, $nilaiLama->nilai) }}"
                                                                {{ !$seasonOpen ? 'disabled' : '' }}>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-outline">
                                                                <textarea name="tujuan_pembelajaran[]" rows="2" class="form-control text-sm" {{ !$seasonOpen ? 'disabled' : '' }}>
                                                                @php echo trim(old('tujuan_pembelajaran.' . $i, $nilaiLama->tujuan_pembelajaran)); @endphp
                                                                </textarea>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-end mt-4">
                                        @if($seasonOpen)
                                            <button type="submit" class="btn bg-gradient-success">
                                                <i class="fas fa-save me-2"></i> Simpan Nilai
                                            </button>
                                        @endif
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

    {{-- MODAL DOWNLOAD --}}
    <div class="modal fade" id="downloadTemplateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Download Template Sumatif {{ $sumatifId }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('master.sumatif.download') }}" method="GET"> 
                    <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kelas:</label>
                            <select name="id_kelas" required class="form-select ajax-select-kelas" data-target="#mapel_download">
                                <option value="">Pilih Kelas</option>
                                @foreach(\App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mata Pelajaran:</label>
                            <select name="id_mapel" id="mapel_download" required class="form-select">
                                <option value="">Pilih Kelas Terlebih Dahulu</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester:</label>
                            <select name="semester" required class="form-select">
                                @foreach($semesterList as $sem)
                                    <option value="{{ $sem }}" {{ $defaultSemester == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tahun Ajaran:</label>
                            <select name="tahun_ajaran" required class="form-select">
                                @foreach ($tahunAjaranList as $ta)
                                    <option value="{{ $ta }}" {{ $defaultTahunAjaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn bg-gradient-info">Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL IMPORT --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Nilai Sumatif {{ $sumatifId }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('master.sumatif.import') }}" method="POST" enctype="multipart/form-data"> 
                    @csrf
                    <input type="hidden" name="sumatif" value="{{ $sumatifId }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kelas:</label>
                            <select name="id_kelas" required class="form-select ajax-select-kelas" data-target="#mapel_import">
                                <option value="">Pilih Kelas</option>
                                @foreach(\App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mata Pelajaran:</label>
                            <select name="id_mapel" id="mapel_import" required class="form-select">
                                <option value="">Pilih Kelas Terlebih Dahulu</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester:</label>
                            <select name="semester" required class="form-select">
                                @foreach($semesterList as $sem)
                                    <option value="{{ $sem }}" {{ $defaultSemester == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tahun Ajaran:</label>
                            <select name="tahun_ajaran" required class="form-select">
                                @foreach ($tahunAjaranList as $ta)
                                    <option value="{{ $ta }}" {{ $defaultTahunAjaran == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih File Excel:</label>
                            <input type="file" name="file_excel" required class="form-control" accept=".xlsx, .xls">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn bg-gradient-success">Proses Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- AJAX SCRIPT --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.ajax-select-kelas').on('change', function() {
                let idKelas = $(this).val();
                let target = $(this).data('target');
                let dropdownMapel = $(target);

                dropdownMapel.html('<option value="">Memuat...</option>');

                if (idKelas) {
                    $.ajax({
                        // Gunakan nama route lengkap sesuai route:list Anda
                        url: "{{ route('master.sumatif.get_mapel', '') }}/" + idKelas,
                        method: "GET",
                        success: function(res) {
                            let html = '<option value="">-- Pilih Mapel --</option>';
                            if (res.length > 0) {
                                res.forEach(item => {
                                    html += `<option value="${item.id_mapel}">${item.nama_mapel}</option>`;
                                });
                            } else {
                                html = '<option value="">Tidak ada mapel di kelas ini</option>';
                            }
                            dropdownMapel.html(html);
                        },
                        error: function(xhr) {
                            // Lihat error di Console Browser (F12)
                            console.log(xhr.responseText);
                            dropdownMapel.html('<option value="">Gagal memuat mapel</option>');
                        }
                    });
                } else {
                    dropdownMapel.html('<option value="">Pilih Kelas Terlebih Dahulu</option>');
                }
            });
        });
    </script>
@endsection