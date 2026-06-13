@extends('layouts.app')

@section('page-title', 'Pengaturan Event & Pengumuman')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border mb-4">
                    
                    {{-- HEADER BANNER --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-bullhorn text-white" style="font-size: 8rem;"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-4">
                                <div>
                                    <h4 class="text-white font-weight-bold mb-1">
                                        <i class="fas fa-calendar-alt me-2"></i> Event & Pengumuman
                                    </h4>
                                    <p class="text-white text-sm opacity-8 mb-0 ms-4 ps-1">
                                        Kelola pengumuman dan acara yang tampil di dashboard.
                                    </p>
                                </div>
                                <div>
                                    <button class="btn bg-white text-primary mb-0" data-bs-toggle="modal" data-bs-target="#modalTambahEvent">
                                        <i class="fas fa-plus me-1"></i> Tambah Baru
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2 mt-2">
                        
                        {{-- NOTIFIKASI SUCCESS & ERROR --}}
                        <div class="px-4">
                            @if (session('success'))
                                <div class="alert bg-gradient-success text-white alert-dismissible fade show" role="alert">
                                    <span class="text-sm">{{ session('success') }}</span>
                                    <button type="button" class="btn-close text-white opacity-10" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert bg-gradient-danger text-white alert-dismissible fade show">
                                    <ul class="mb-0 text-sm">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close text-white opacity-10" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                                </div>
                            @endif
                        </div>

                        {{-- TABEL DATA EVENT --}}
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 30%">Informasi Data</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kategori / Target</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu Pelaksanaan</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($events as $idx => $event)
                                        <tr>
                                            <td class="text-center text-sm text-secondary">{{ $idx + 1 }}</td>
                                            <td class="px-3">
                                                <div class="d-flex flex-column">
                                                    <h6 class="mb-1 text-sm text-dark font-weight-bold">{{ $event->judul }}</h6>
                                                    <span class="text-xs text-secondary text-wrap" style="max-width: 300px;">
                                                        {{ Str::limit($event->deskripsi, 60) }}
                                                    </span>
                                                    @if($event->lampiran)
                                                        <a href="{{ asset('storage/' . $event->lampiran) }}" target="_blank" class="text-xs text-info mt-1"><i class="fas fa-paperclip me-1"></i>Lihat Lampiran</a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($event->kategori == 'Acara')
                                                    <span class="badge badge-sm bg-gradient-info text-uppercase mb-1 d-block w-auto mx-auto" style="max-width: 100px;">Acara</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-warning text-uppercase mb-1 d-block w-auto mx-auto" style="max-width: 100px;">Pengumuman</span>
                                                @endif
                                                <span class="text-xs text-secondary font-weight-bold"><i class="fas fa-users me-1"></i>{{ ucfirst($event->target) }}</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="text-sm text-dark font-weight-bold d-block">{{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}</span>
                                                <span class="text-xs text-secondary">s/d {{ \Carbon\Carbon::parse($event->tanggal_selesai)->format('d M Y') }}</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($event->status == 'aktif')
                                                    <span class="badge badge-sm bg-success border text-white"><i class="fas fa-check-circle me-1"></i> AKTIF</span>
                                                @else
                                                    <span class="badge badge-sm bg-light text-secondary border"><i class="fas fa-ban me-1"></i> TIDAK AKTIF / DRAFT</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <button class="btn btn-link text-dark px-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditEvent{{ $event->id_event }}" title="Edit">
                                                    <i class="fas fa-edit text-lg"></i>
                                                </button>
                                                <form action="{{ route('settings.erapor.event.destroy', $event->id_event) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger px-2 mb-0" title="Hapus">
                                                        <i class="fas fa-trash-alt text-lg"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        {{-- MODAL EDIT EVENT --}}
                                        <div class="modal fade" id="modalEditEvent{{ $event->id_event }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-light">
                                                        <h5 class="modal-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Data</h5>
                                                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('settings.erapor.event.update', $event->id_event) }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body text-start">
                                                            <div class="row">
                                                                <div class="col-md-12 mb-3">
                                                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Judul</label>
                                                                    <div class="input-group input-group-outline is-filled">
                                                                        <input type="text" name="judul" class="form-control" value="{{ $event->judul }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 mb-3">
                                                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Deskripsi / Isi</label>
                                                                    <div class="input-group input-group-outline is-filled">
                                                                        <textarea name="deskripsi" class="form-control" rows="4" required>{{ $event->deskripsi }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Tanggal Mulai</label>
                                                                    <div class="input-group input-group-outline is-filled">
                                                                        <input type="date" name="tanggal" class="form-control" value="{{ $event->tanggal }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Tanggal Selesai</label>
                                                                    <div class="input-group input-group-outline is-filled">
                                                                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ $event->tanggal_selesai }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 mb-3">
                                                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Kategori</label>
                                                                    <div class="input-group input-group-outline is-filled">
                                                                        <select name="kategori" class="form-select border ps-2" required>
                                                                            <option value="Acara" {{ $event->kategori == 'Acara' ? 'selected' : '' }}>Acara / Event</option>
                                                                            <option value="Pengumuman" {{ $event->kategori == 'Pengumuman' ? 'selected' : '' }}>Pengumuman Penting</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 mb-3">
                                                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Target Penerima</label>
                                                                    <div class="input-group input-group-outline is-filled">
                                                                        <select name="target" class="form-select border ps-2" required>
                                                                            <option value="semua" {{ $event->target == 'semua' ? 'selected' : '' }}>Semua Pengguna</option>
                                                                            <option value="guru" {{ $event->target == 'guru' ? 'selected' : '' }}>Khusus Guru / Wali</option>
                                                                            <option value="siswa" {{ $event->target == 'siswa' ? 'selected' : '' }}>Khusus Siswa</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 mb-3">
                                                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Status Tayang</label>
                                                                    <div class="input-group input-group-outline is-filled">
                                                                        <select name="status" class="form-select border ps-2" required>
                                                                            <option value="aktif" {{ $event->status == 'aktif' ? 'selected' : '' }}>Aktif (Tampil)</option>
                                                                            <option value="draft" {{ $event->status == 'draft' ? 'selected' : '' }}>Draft (Sembunyikan)</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 mb-2">
                                                                    <label class="form-label text-xs font-weight-bolder text-uppercase">Ganti Lampiran (Kosongkan jika tidak diubah)</label>
                                                                    <div class="input-group input-group-outline">
                                                                        <input type="file" name="file_lampiran" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                                                    </div>
                                                                    <small class="text-xs text-muted">Format: PDF/JPG/PNG. Maks: 5MB.</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-link text-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn bg-gradient-primary">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-secondary">
                                                <i class="fas fa-folder-open fa-3x mb-3 opacity-5"></i><br>
                                                Belum ada data Acara atau Pengumuman.
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
        <x-app.footer />
    </div>
</main>

{{-- MODAL TAMBAH EVENT BARU --}}
<div class="modal fade" id="modalTambahEvent" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Acara / Pengumuman Baru</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('settings.erapor.event.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body text-start">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Judul</label>
                            <div class="input-group input-group-outline">
                                <input type="text" name="judul" class="form-control" required placeholder="Contoh: Rapat Wali Kelas">
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Deskripsi / Isi</label>
                            <div class="input-group input-group-outline">
                                <textarea name="deskripsi" class="form-control" rows="4" required placeholder="Tuliskan detail..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Tanggal Mulai</label>
                            <div class="input-group input-group-outline is-filled">
                                <input type="date" name="tanggal" class="form-control input-tanggal-mulai" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Tanggal Selesai</label>
                            <div class="input-group input-group-outline is-filled">
                                <input type="date" name="tanggal_selesai" class="form-control input-tanggal-selesai" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Kategori</label>
                            <div class="input-group input-group-outline is-filled">
                                <select name="kategori" class="form-select border ps-2" required>
                                    <option value="Pengumuman">Pengumuman Penting</option>
                                    <option value="Acara">Acara / Event</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Target Penerima</label>
                            <div class="input-group input-group-outline is-filled">
                                <select name="target" class="form-select border ps-2" required>
                                    <option value="semua">Semua Pengguna</option>
                                    <option value="guru">Khusus Guru / Wali</option>
                                    <option value="siswa">Khusus Siswa</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Status Tayang</label>
                            <div class="input-group input-group-outline is-filled">
                                <select name="status" class="form-select border ps-2" required>
                                    <option value="aktif">Aktif (Tampil)</option>
                                    <option value="draft">Draft (Sembunyikan)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label text-xs font-weight-bolder text-uppercase">Upload Lampiran (Opsional)</label>
                            <div class="input-group input-group-outline">
                                <input type="file" name="file_lampiran" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <small class="text-xs text-muted">Format: PDF/JPG/PNG. Maks: 5MB.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link text-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputMulaiList = document.querySelectorAll('.input-tanggal-mulai');

    inputMulaiList.forEach(function(inputMulai) {
        inputMulai.addEventListener('change', function() {
            const form = this.closest('form');
            const inputSelesai = form.querySelector('.input-tanggal-selesai');
            
            if (inputSelesai) {
                inputSelesai.min = this.value;
                if (!inputSelesai.value || inputSelesai.value < this.value) {
                    inputSelesai.value = this.value;
                }
            }
        });
    });
});
</script>
@endsection