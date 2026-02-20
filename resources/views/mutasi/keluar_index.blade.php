@extends('layouts.app')

@section('page-title', 'Mutasi Siswa Keluar')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <style>
        /* Gradient Oranye (Warning) */
        .alert-warning {
            background-image: linear-gradient(310deg, #fb8c00 0%, #fdb03d 100%) !important;
            color: #fff !important; /* Memastikan teks putih terbaca */
            border: none !important; /* Menghilangkan border default */
        }

        /* (Opsional) Tambahan jika ingin memastikan bg-gradient-danger dan success juga ada */
        .bg-gradient-danger {
            background-image: linear-gradient(310deg, #ea0606 0%, #ff667c 100%) !important;
            color: #fff !important;
        }

        .bg-gradient-success {
            background-image: linear-gradient(310deg, #17ad37 0%, #98ec2d 100%) !important;
            color: #fff !important;
        }
    </style>

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- 1. HEADER UTAMA (Gaya Banner) --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            {{-- Dekorasi Icon Besar --}}
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-sign-out-alt text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-exchange-alt me-2"></i> Mutasi Siswa Keluar
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Manajemen siswa pindah sekolah, mengundurkan diri, atau putus sekolah
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-2">
                        
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

                        <div class="row mt-3">
                            {{-- BAGIAN KIRI: FORM PENCARIAN & TABEL SISWA AKTIF --}}
                            <div class="col-lg-8 mb-4">
                                <div class="card border border-light shadow-sm h-100">
                                    <div class="card-header bg-gray-100 border-bottom p-3">
                                        <h6 class="mb-0 text-dark font-weight-bold">
                                            <i class="fas fa-user-check text-primary me-2"></i> Pilih Siswa Aktif
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        {{-- Filter Kelas --}}
                                        <form action="{{ route('mutasi.keluar.index') }}" method="GET" class="row align-items-end mb-4">
                                            <div class="col-md-8">
                                                <label class="form-label text-xs font-weight-bold text-uppercase">Filter Kelas</label>
                                                <div class="input-group input-group-outline">
                                                    <select name="id_kelas" class="form-control" onchange="this.form.submit()">
                                                        <option value="">-- Pilih Kelas --</option>
                                                        @foreach($kelas as $k)
                                                            <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                                                {{ $k->nama_kelas }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </form>

                                        {{-- Tabel Siswa --}}
                                        @if(request('id_kelas'))
                                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                                <table class="table align-items-center mb-0">
                                                    <thead class="bg-white sticky-top">
                                                        <tr>
                                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Nama Siswa</th>
                                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">NISN</th>
                                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($siswaAktif as $s)
                                                            <tr>
                                                                <td class="ps-3 text-sm font-weight-bold text-dark">{{ $s->nama_siswa }}</td>
                                                                <td class="text-center text-xs">{{ $s->nisn }}</td>
                                                                <td class="text-center">
                                                                    <button class="btn btn-sm bg-gradient-danger mb-0" 
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#modalMutasi"
                                                                            data-id="{{ $s->id_siswa }}"
                                                                            data-nama="{{ $s->nama_siswa }}">
                                                                        <i class="fas fa-sign-out-alt me-1"></i> Proses
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center py-5 text-sm text-secondary">
                                                                    <i class="fas fa-user-slash fa-2x mb-2 opacity-5"></i><br>
                                                                    Tidak ada siswa aktif di kelas ini.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-5 border rounded bg-gray-50">
                                                <i class="fas fa-chalkboard-teacher fa-3x text-secondary mb-3 opacity-3"></i>
                                                <h6 class="text-secondary">Silakan pilih kelas terlebih dahulu</h6>
                                                <p class="text-xs text-muted">Gunakan filter di atas untuk menampilkan daftar siswa.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- BAGIAN KANAN: RIWAYAT MUTASI TERAKHIR --}}
                            <div class="col-lg-4 mb-4">
                                <div class="card border border-light shadow-sm h-100">
                                    <div class="card-header bg-gray-100 border-bottom p-3">
                                        <h6 class="mb-0 text-dark font-weight-bold">
                                            <i class="fas fa-history text-success me-2"></i> Riwayat Mutasi
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="timeline timeline-one-side" style="max-height: 600px; overflow-y: auto;">
                                            @forelse($riwayat as $r)
                                                <div class="timeline-block mb-3">
                                                    <span class="timeline-step">
                                                        <i class="fas fa-user-minus text-danger text-gradient"></i>
                                                    </span>
                                                    <div class="timeline-content">
                                                        <h6 class="text-dark text-sm font-weight-bold mb-0">
                                                            {{ $r->siswa->nama_siswa ?? 'Siswa Terhapus' }}
                                                        </h6>
                                                        <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                                            <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($r->tgl_mutasi)->format('d M Y') }} 
                                                            <br>
                                                            <span class="badge badge-sm bg-gradient-light text-dark mt-1 border">
                                                                {{ $r->jenis_mutasi }}
                                                            </span>
                                                        </p>
                                                        <div class="bg-gray-50 border p-2 border-radius-md mt-2">
                                                            <p class="text-xs text-secondary mb-0 fst-italic">
                                                                "{{ Str::limit($r->alasan, 60) }}"
                                                            </p>
                                                            @if($r->sekolah_tujuan)
                                                                <hr class="horizontal dark my-1">
                                                                <p class="text-xxs text-dark mb-0">
                                                                    <strong>Tujuan:</strong> {{ $r->sekolah_tujuan }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        
                                                        {{-- Tombol Batal --}}
                                                        <form action="{{ route('mutasi.keluar.destroy', $r->id) }}" method="POST" class="mt-2 text-end" onsubmit="return confirm('PERINGATAN: Membatalkan mutasi akan mengembalikan status siswa menjadi AKTIF, namun siswa tersebut BELUM MEMILIKI KELAS. Anda harus memasukkannya kembali ke kelas melalui menu Anggota Kelas. Lanjutkan?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link text-danger text-xxs p-0 mb-0">
                                                                <i class="fas fa-undo me-1"></i> Batalkan Mutasi
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-4">
                                                    <p class="text-sm text-secondary">Belum ada data mutasi keluar.</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

{{-- MODAL FORM MUTASI --}}
<div class="modal fade" id="modalMutasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gray-100">
                <h6 class="modal-title font-weight-bolder text-dark">
                    <i class="fas fa-edit text-danger me-2"></i> Form Proses Mutasi Keluar
                </h6>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('mutasi.keluar.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning text-dark text-xs mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <strong>PERHATIAN!</strong><br>
                                Siswa ini akan dikeluarkan dari kelas aktif dan statusnya berubah menjadi "Keluar". Data rapor lama akan tetap tersimpan.
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="id_siswa" id="modal_id_siswa">
                    
                    <div class="mb-3">
                        <label class="form-label text-xs font-weight-bold text-uppercase">Nama Siswa</label>
                        <div class="input-group input-group-outline is-filled">
                            <input type="text" class="form-control bg-gray-100" id="modal_nama_siswa" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Tanggal Keluar</label>
                            <div class="input-group input-group-outline is-filled">
                                <input type="date" name="tgl_mutasi" class="form-control" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Jenis Mutasi</label>
                            <div class="input-group input-group-outline is-filled">
                                <select name="jenis_mutasi" class="form-control" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Pindah Sekolah">Pindah Sekolah</option>
                                    <option value="Mengundurkan Diri">Mengundurkan Diri</option>
                                    <option value="Putus Sekolah">Putus Sekolah</option>
                                    <option value="Meninggal Dunia">Meninggal Dunia</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-xs font-weight-bold text-uppercase">Sekolah Tujuan (Jika Pindah)</label>
                        <div class="input-group input-group-outline">
                            <input type="text" name="sekolah_tujuan" class="form-control" placeholder="Nama Sekolah Tujuan...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-xs font-weight-bold text-uppercase">Alasan / Catatan</label>
                        <div class="input-group input-group-outline">
                            <textarea name="alasan" class="form-control" rows="3" required placeholder="Jelaskan alasan keluar secara rinci..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-gray-100">
                    <button type="button" class="btn btn-sm btn-white mb-0" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm bg-gradient-danger mb-0">Simpan & Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT MODAL --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalMutasi = document.getElementById('modalMutasi');
        if(modalMutasi) {
            modalMutasi.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var idSiswa = button.getAttribute('data-id');
                var namaSiswa = button.getAttribute('data-nama');

                var inputId = modalMutasi.querySelector('#modal_id_siswa');
                var inputNama = modalMutasi.querySelector('#modal_nama_siswa');

                if(inputId) inputId.value = idSiswa;
                if(inputNama) inputNama.value = namaSiswa;
            });
        }
    });
</script>
@endsection