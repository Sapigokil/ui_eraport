@extends('layouts.app') 

@section('page-title', 'Proses Kelulusan Siswa')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        {{-- TOMBOL KEMBALI --}}
        <div class="mb-3">
            <a href="{{ route('mutasi.dashboard_akhir.index') }}" class="btn btn-sm btn-white border-secondary shadow-sm mb-0 text-dark font-weight-bold">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
            </a>
        </div>

        {{-- BLOK PENAMPIL ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger text-dark font-weight-bold shadow-sm mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> PERHATIAN:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger text-dark font-weight-bold shadow-sm mb-4" role="alert">
                <i class="fas fa-times-circle me-2"></i> GAGAL: {{ session('error') }}
            </div>
        @endif
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-warning overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                        <i class="fas fa-graduation-cap text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <h3 class="text-white font-weight-bold mb-1">Proses Kelulusan</h3>
                        <p class="text-white opacity-8 mb-0">Tahun Ajaran: {{ $taLama }} | Kelas: {{ $kelasAsalTerpilih->nama_kelas }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($dataSiswa->isNotEmpty())
        <form id="formKelulusan" action="{{ route('mutasi.kelulusan.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id_kelas_lama" value="{{ $id_kelas_asal }}">
            <input type="hidden" name="tahun_ajaran_lama" value="{{ $taLama }}">

            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-light pb-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-dark font-weight-bold">Daftar Siswa Akhir</h6>
                    <span class="badge bg-dark">Total: {{ $dataSiswa->count() }} Siswa</span>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%;">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 40%;">Nama Siswa / NISN</th>
                                    <th class="text-uppercase text-dark text-xxs font-weight-bolder opacity-9 bg-gray-100" style="width: 55%;">Status Kelulusan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataSiswa as $i => $siswa)
                                <tr class="border-bottom">
                                    <td class="align-middle text-center text-sm font-weight-bold">{{ $i + 1 }}</td>
                                    <td class="align-middle px-3">
                                        <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $siswa->nama_siswa }}</h6>
                                        <p class="text-xs text-secondary mb-0">{{ $siswa->nisn }}</p>
                                    </td>
                                    <td class="align-middle px-3 bg-gray-50">
                                        {{-- Default Lulus --}}
                                        <select name="tujuan[{{ $siswa->id_siswa }}]" class="form-select form-select-sm border-secondary font-weight-bold text-success" onchange="updateWarna(this)">
                                            <option value="lulus" class="text-success font-weight-bold" selected>
                                                LULUS (Ubah Status ke Alumni)
                                            </option>
                                            <option value="tinggal_kelas" class="text-danger font-weight-bold">
                                                TIDAK LULUS (Tinggal di {{ $kelasAsalTerpilih->nama_kelas }})
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-end border-top">
                    {{-- Ubah warna tombol menjadi btn-warning bg-gradient-warning --}}
                    <button type="button" onclick="confirmProses()" class="btn btn-warning bg-gradient-warning btn-lg mb-0 shadow-sm text-white">
                        <i class="fas fa-user-graduate me-2"></i> EKSEKUSI KELULUSAN
                    </button>
                </div>
            </div>
        </form>
        @else
        <div class="alert alert-info text-white">Tidak ada siswa aktif yang perlu diproses kelulusannya di kelas ini.</div>
        @endif

    </div>
    <x-app.footer />
</main>

{{-- JS untuk membedakan warna --}}
<script>
    function updateWarna(sel) {
        if(sel.value === 'lulus') {
            sel.className = "form-select form-select-sm border-secondary font-weight-bold text-success";
        } else {
            sel.className = "form-select form-select-sm border-secondary font-weight-bold text-danger";
        }
    }

    function confirmProses() {
        if (confirm('PERHATIAN!\n\nProses ini akan mengubah status siswa menjadi LULUS/ALUMNI. Data kelas mereka akan dinonaktifkan. Lanjutkan?')) {
            document.getElementById('formKelulusan').submit();
        }
    }
</script>
@endsection