@extends('layouts.app') 

@section('page-title', 'Eksekusi Kelulusan')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg pt-4">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        
        <div class="mb-3">
            <a href="{{ route('mutasi.kelulusan_dashboard.index') }}" class="btn btn-sm btn-white border-secondary shadow-sm mb-0 text-dark font-weight-bold">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger text-white text-sm p-3 mb-4 shadow-sm font-weight-bold">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success text-white text-sm p-3 mb-4 shadow-sm font-weight-bold">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        {{-- BANNER HEADER DINAMIS --}}
        <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 1rem; background: {{ $bg_gradient }};">
            <div class="card-body p-3 position-relative"> 
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                
                <div class="row align-items-center position-relative z-index-1">
                    <div class="col-md-5 col-lg-6 text-white">
                        <h5 class="text-white font-weight-bolder mb-1">
                            <i class="fas fa-graduation-cap me-2"></i> {{ $kelasAsalTerpilih->nama_kelas }}
                        </h5>
                        <p class="mb-0 text-sm opacity-9">
                            TA: <b>{{ $taLama }}</b> &nbsp;|&nbsp; Wali: <b>{{ $kelasAsalTerpilih->wali_kelas ?? 'Tanpa Wali' }}</b>
                        </p>
                    </div>
                    
                    {{-- STATISTIK DI SISI KANAN BANNER --}}
                    <div class="col-md-7 col-lg-6 mt-3 mt-md-0 d-flex justify-content-md-end justify-content-between text-white">
                        <div class="text-center px-3 border-end" style="border-color: rgba(255,255,255,0.2) !important;">
                            <h4 class="text-white font-weight-bolder mb-0">{{ count($dataSiswa) }}</h4>
                            <span style="font-size: 0.65rem;" class="text-uppercase opacity-8">Siswa</span>
                        </div>
                        <div class="text-center px-3 border-end" style="border-color: rgba(255,255,255,0.2) !important;">
                            <h4 class="text-white font-weight-bolder mb-0">{{ $stat['belum'] }}</h4>
                            <span style="font-size: 0.65rem;" class="text-uppercase opacity-8"><i class="fas fa-hourglass-half"></i> Belum</span>
                        </div>
                        <div class="text-center px-3 border-end" style="border-color: rgba(255,255,255,0.2) !important;">
                            <h4 class="text-white font-weight-bolder mb-0">{{ $stat['lulus'] }}</h4>
                            <span style="font-size: 0.65rem;" class="text-uppercase opacity-8"><i class="fas fa-check"></i> Lulus</span>
                        </div>
                        <div class="text-center px-3">
                            <h4 class="text-white font-weight-bolder mb-0">{{ $stat['tidak_lulus'] }}</h4> 
                            <span style="font-size: 0.65rem;" class="text-uppercase opacity-8"><i class="fas fa-times"></i> Gagal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL DATA --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom p-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-0 text-dark font-weight-bold">Daftar Keputusan Siswa</h6>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <div class="btn-group shadow-sm">
                            <button type="button" id="btn-set-lulus" class="btn btn-xs btn-outline-success mb-0">
                                <i class="fas fa-check-double me-1"></i> Semua Lulus
                            </button>
                            <button type="button" id="btn-set-tidak-lulus" class="btn btn-xs btn-outline-danger mb-0">
                                <i class="fas fa-times-circle me-1"></i> Semua Tdk Lulus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('mutasi.kelulusan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_kelas_lama" id="id_kelas_lama" value="{{ $id_kelas_asal }}">
                <input type="hidden" name="tahun_ajaran_lama" id="tahun_ajaran_lama" value="{{ $taLama }}">

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0 table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                    <th class="text-xxs font-weight-bolder opacity-7 ps-3" style="width: 15%">NISN</th>
                                    <th class="text-xxs font-weight-bolder opacity-7 ps-3">Nama Lengkap Siswa</th>
                                    <th class="text-center text-xxs font-weight-bolder opacity-7" style="width: 25%">SKL (PDF)</th>
                                    <th class="text-center text-xxs font-weight-bolder opacity-7" style="width: 20%">Keputusan Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataSiswa as $idx => $siswa)
                                <tr class="border-bottom">
                                    <td class="text-center text-sm text-secondary">{{ $idx + 1 }}</td>
                                    <td class="text-sm font-weight-bold text-dark ps-3">{{ $siswa->nisn ?? '-' }}</td>
                                    <td class="ps-3">
                                        <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $siswa->nama_siswa }}</h6>
                                    </td>
                                    
                                    {{-- AREA UPLOAD AJAX --}}
                                    <td class="text-center p-2">
                                        <div id="skl-container-{{ $siswa->id_siswa }}" class="d-flex flex-column align-items-center justify-content-center">
                                            
                                            {{-- Baris Atas: Label Status --}}
                                            @if(!empty($siswa->file_skl))
                                                <span class="badge bg-success text-xxs px-2 py-1 shadow-sm mb-2">
                                                    <i class="fas fa-check-circle me-1"></i> Tersimpan
                                                </span>
                                            @else
                                                <span class="badge bg-secondary text-xxs px-2 py-1 opacity-5 mb-2">
                                                    <i class="fas fa-info-circle me-1"></i> Belum ada PDF
                                                </span>
                                            @endif
                                            
                                            {{-- Baris Bawah: Tombol Berjajar (Lihat PDF -> Ganti File -> Hapus) --}}
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                @if(!empty($siswa->file_skl))
                                                    <a href="{{ route('mutasi.kelulusan.view_skl', $siswa->id_siswa) }}" target="_blank" class="btn btn-xs btn-outline-info mb-0 py-1 px-2">
                                                        <i class="fas fa-external-link-alt me-1"></i> Lihat PDF
                                                    </a>
                                                @endif
                                                
                                                <label class="btn btn-xs btn-outline-primary mb-0 py-1 px-2" style="cursor: pointer;">
                                                    <i class="fas fa-upload me-1"></i> {{ !empty($siswa->file_skl) ? 'Ganti File' : 'Upload SKL' }}
                                                    <input type="file" class="d-none upload-skl-auto" data-id="{{ $siswa->id_siswa }}" accept="application/pdf">
                                                </label>

                                                @if(!empty($siswa->file_skl))
                                                    <button type="button" class="btn btn-xs btn-outline-danger mb-0 py-1 px-2 btn-delete-skl" data-id="{{ $siswa->id_siswa }}">
                                                        <i class="fas fa-trash me-1"></i> Hapus
                                                    </button>
                                                @endif
                                            </div>

                                        </div>
                                    </td>

                                    <td class="text-center p-2">
                                        <select name="tujuan[{{ $siswa->id_siswa }}]" class="form-select form-select-sm status-dropdown fw-bold text-center mx-auto" style="width: 200px;">
                                            <option value="" {{ $siswa->status_kelulusan == '' ? 'selected' : '' }}>-- Belum Diproses --</option>
                                            <option value="lulus" {{ $siswa->status_kelulusan == 'lulus' ? 'selected' : '' }}>LULUS</option>
                                            <option value="tidak_lulus" {{ $siswa->status_kelulusan == 'tidak_lulus' ? 'selected' : '' }}>TIDAK LULUS</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light border-top p-3 d-flex justify-content-between align-items-center">
                    <p class="text-xs text-secondary mb-0">
                        <i class="fas fa-info-circle me-1"></i> Setiap file PDF yang diunggah/dihapus akan otomatis diproses. Klik <b>Simpan Hasil Kelulusan</b> setelah mengatur dropdown Keputusan Akhir.
                    </p>
                    <button type="submit" class="btn btn-primary mb-0 shadow-sm px-4">
                        <i class="fas fa-save me-2"></i> Simpan Hasil Kelulusan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <x-app.footer />
</main>

<style>
    .status-dropdown { border: 1px solid #d2d6da; transition: all 0.2s; background-color: #fff; cursor: pointer; }
    .status-dropdown:focus { border-color: #5e72e4; box-shadow: 0 0 0 2px rgba(94, 114, 228, 0.2); }
    
    select option[value="lulus"] { color: #2dce89; font-weight: bold; }
    select option[value="tidak_lulus"] { color: #f5365c; font-weight: bold; }
    
    .border-lulus { border: 2px solid #2dce89 !important; color: #2dce89 !important; background-color: rgba(45, 206, 137, 0.05) !important; }
    .border-gagal { border: 2px solid #f5365c !important; color: #f5365c !important; background-color: rgba(245, 54, 92, 0.05) !important; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Logika Warna Dropdown
    const dropdowns = document.querySelectorAll('.status-dropdown');

    function updateStyle(el) {
        el.classList.remove('border-lulus', 'border-gagal');
        if(el.value === 'lulus') el.classList.add('border-lulus');
        if(el.value === 'tidak_lulus') el.classList.add('border-gagal');
    }

    dropdowns.forEach(dd => updateStyle(dd));
    dropdowns.forEach(dd => { dd.addEventListener('change', function() { updateStyle(this); }); });

    document.getElementById('btn-set-lulus').addEventListener('click', function() {
        dropdowns.forEach(dd => { dd.value = 'lulus'; updateStyle(dd); });
    });

    document.getElementById('btn-set-tidak-lulus').addEventListener('click', function() {
        dropdowns.forEach(dd => { dd.value = 'tidak_lulus'; updateStyle(dd); });
    });

    // 2. 👇 LOGIKA UPLOAD AJAX (DENGAN PENANGANAN ERROR LARAVEL) 👇
    document.querySelectorAll('.upload-skl-auto').forEach(input => {
        input.addEventListener('change', function() {
            if (!this.files.length) return;
            
            let file = this.files[0];
            let idSiswa = this.dataset.id;
            let taLama = document.getElementById('tahun_ajaran_lama').value;
            
            let formData = new FormData();
            formData.append('file_skl', file);
            formData.append('tahun_ajaran_lama', taLama);
            formData.append('_token', '{{ csrf_token() }}');
            
            // Ubah UI menjadi status Loading
            let container = document.getElementById('skl-container-' + idSiswa);
            container.innerHTML = '<span class="badge bg-warning text-xxs px-3 py-2 shadow-sm"><i class="fas fa-spinner fa-spin me-2"></i>Mengunggah...</span>';

            // Kirim ke server
            fetch(`{{ url('mutasi/kelulusan/upload-skl') }}/${idSiswa}`, {
                method: 'POST',
                headers: {
                    // Wajib agar Laravel merespon dengan format JSON jika terjadi error validasi!
                    'Accept': 'application/json' 
                },
                body: formData
            })
            .then(async response => {
                if (!response.ok) {
                    let errorData = await response.json().catch(() => null);
                    if (response.status === 422 && errorData && errorData.errors) {
                        // Kumpulkan pesan error validasi (seperti: file melebihi 5MB)
                        let errorMessages = Object.values(errorData.errors).flat().join('\n');
                        throw new Error(errorMessages);
                    }
                    throw new Error('Terjadi kesalahan sistem (Status: ' + response.status + '). Cek log server.');
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    alert('Gagal mengunggah file: ' + (data.message || 'File tidak valid.'));
                    window.location.reload();
                }
            })
            .catch(err => {
                alert('GAGAL UPLOAD:\n' + err.message);
                window.location.reload();
            });
        });
    });

    // 3. LOGIKA HAPUS SKL AJAX
    document.querySelectorAll('.btn-delete-skl').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Apakah Anda yakin ingin menghapus file SKL ini? Tindakan ini tidak dapat dibatalkan.')) return;

            let idSiswa = this.dataset.id;
            let taLama = document.getElementById('tahun_ajaran_lama').value;
            
            // Ubah UI menjadi status Loading
            let container = document.getElementById('skl-container-' + idSiswa);
            container.innerHTML = '<span class="badge bg-danger text-xxs px-3 py-2 shadow-sm"><i class="fas fa-spinner fa-spin me-2"></i>Menghapus...</span>';

            // Kirim request DELETE ke server
            fetch(`{{ url('mutasi/kelulusan/delete-skl') }}/${idSiswa}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tahun_ajaran_lama: taLama })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    alert('Gagal menghapus file: ' + (data.message || 'Terjadi kesalahan sistem.'));
                    window.location.reload();
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan koneksi saat menghapus: ' + err.message);
                window.location.reload();
            });
        });
    });
});
</script>
@endsection