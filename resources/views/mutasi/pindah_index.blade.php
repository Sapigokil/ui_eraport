@extends('layouts.app')

@section('page-title', 'Mutasi Pindah Kelas')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        {{-- HEADER BANNER --}}
        <div class="card border shadow-xs mb-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize mb-0">
                            <i class="fas fa-exchange-alt me-2"></i> Pindah Kelas (Rolling)
                        </h6>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <form action="{{ route('mutasi.pindah.index') }}" method="GET" class="row align-items-end">
                    <div class="col-md-5">
                        <label class="form-label text-xs fw-bold">Pilih Kelas Asal</label>
                        <select name="id_kelas_asal" class="form-select border ps-2" onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}" {{ request('id_kelas_asal') == $k->id_kelas ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if(request('id_kelas_asal'))
        <div class="row">
            <div class="col-12">
                <form action="{{ route('mutasi.pindah.store') }}" method="POST" id="formPindah">
                    @csrf
                    <input type="hidden" name="id_kelas_asal" value="{{ request('id_kelas_asal') }}">

                    <div class="card shadow-xs border">
                        <div class="card-header border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-dark font-weight-bold">Daftar Siswa</h6>
                            <button type="button" class="btn bg-gradient-dark btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#modalTujuan">
                                <i class="fas fa-paper-plane me-1"></i> Proses Pindah Terpilih
                            </button>
                        </div>
                        
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center" width="5%">
                                            <div class="form-check text-center">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                            </div>
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Siswa</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NISN</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($siswaAktif as $s)
                                    <tr>
                                        <td class="text-center">
                                            <div class="form-check text-center">
                                                <input class="form-check-input item-check" type="checkbox" name="siswa_ids[]" value="{{ $s->id_siswa }}">
                                            </div>
                                        </td>
                                        <td class="text-sm font-weight-bold">{{ $s->nama_siswa }}</td>
                                        <td class="text-xs">{{ $s->nisn }}</td>
                                        <td><span class="badge badge-sm bg-gradient-success">Aktif</span></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">Tidak ada siswa di kelas ini.</td>
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
                                <div class="modal-header">
                                    <h6 class="modal-title">Form Pindah Kelas</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info text-white text-xs">
                                        <i class="fas fa-info-circle me-1"></i> 
                                        Nilai berjalan semester ini akan otomatis dimigrasikan jika mata pelajaran di kelas baru tersedia.
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-xs fw-bold">Kelas Tujuan</label>
                                        <select name="id_kelas_tujuan" class="form-select border ps-2" required>
                                            <option value="">-- Pilih Kelas Tujuan --</option>
                                            @foreach($kelas as $k)
                                                {{-- Hide kelas asal agar tidak pilih kelas sendiri --}}
                                                @if($k->id_kelas != request('id_kelas_asal'))
                                                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-xs fw-bold">Tanggal Pindah</label>
                                        <input type="date" name="tgl_pindah" class="form-control border ps-2" required value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-xs fw-bold">Alasan</label>
                                        <textarea name="alasan" class="form-control border ps-2" rows="2" placeholder="Contoh: Rolling kelas, Salah masuk kelas..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-link text-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn bg-gradient-primary">Simpan Perpindahan</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
        @endif

    </div>
    <x-app.footer />
</main>

<script>
    // Script Check All
    document.getElementById('checkAll').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.item-check');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });
</script>
@endsection