{{-- File: resources/views/pkl/rapor/index.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Cetak Rapor PKL')

@section('content')

@php
    $totalSiswa = isset($finalSiswaList) ? count($finalSiswaList) : 0;
    $finalCount = isset($finalSiswaList) ? $finalSiswaList->whereIn('status_rapor', ['final', 'cetak'])->count() : 0;
    $rawCount   = isset($finalSiswaList) ? $finalSiswaList->where('status_rapor', '!=', 'belum_generate')->count() : 0;
    
    // Logika tombol massal (Hidden if 0)
    $countBelumGenerate = isset($finalSiswaList) ? $finalSiswaList->where('status_rapor', 'belum_generate')->count() : 0;
    $countDraft         = isset($finalSiswaList) ? $finalSiswaList->where('status_rapor', 'draft')->count() : 0;

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
                                    
                                    {{-- STAT 1: DATA MASUK --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Rapor Digenerate</span>
                                        <h4 class="text-white mb-0">{{ $rawCount }} <span class="text-sm fw-normal opacity-8">/ {{ $totalSiswa }} Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $persenRaw }}%"></div>
                                        </div>
                                    </div>

                                    {{-- STAT 2: SIAP CETAK --}}
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

        {{-- PANEL SET INPUT TANGGAL CETAK --}}
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
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-list-ul me-2"></i> Daftar Rapor PKL Siswa</h6>
                            
                            {{-- ======================================= --}}
                            {{-- SMART BULK ACTION BUTTONS               --}}
                            {{-- ======================================= --}}
                            <div class="d-flex flex-wrap gap-2">
                                
                                <a href="{{ route('pkl.rapor.monitoring.index', ['buka_kelas' => $id_kelas]) }}" target="_blank" class="btn btn-sm btn-outline-info mb-0">
                                    <i class="fas fa-search-location me-2"></i> Cek Monitoring
                                </a>

                                @if($countBelumGenerate > 0 || $countDraft > 0)
                                    <button onclick="bulkAction('generate', 'Generate / Perbarui Data')" class="btn btn-sm bg-gradient-secondary mb-0">
                                        <i class="fas fa-sync-alt me-1"></i> Generate / Perbarui
                                    </button>
                                @endif

                                @if($finalCount > 0)
                                    <button onclick="bulkAction('unlock', 'Buka Kunci (Kembali ke Draft)')" class="btn btn-sm btn-outline-danger mb-0">
                                        <i class="fas fa-lock-open me-1"></i> Unlock
                                    </button>
                                    
                                    <button onclick="bulkDownloadMerge()" class="btn btn-sm btn-primary mb-0">
                                        <i class="fas fa-file-pdf me-1"></i> Download Massal
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
                                        {{-- TH MASTER CHECKBOX --}}
                                        <th class="text-center" style="width: 3%">
                                            <div class="form-check d-flex justify-content-center mb-0">
                                                <input class="form-check-input border-secondary" type="checkbox" id="checkAll" onclick="toggleAll(this)">
                                            </div>
                                        </th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 5%">No</th>
                                        <th class="ps-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 25%">Nama Siswa</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Guru Pembimbing</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kesiapan Guru</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Rapor</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Terakhir Update</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi Satuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($finalSiswaList as $idx => $s)
                                    <tr>
                                        {{-- TD CHECKBOX SATUAN --}}
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center mb-0">
                                                <input class="form-check-input border-secondary check-siswa" type="checkbox" id="check-siswa-{{ $s->id_siswa }}" value="{{ $s->id_siswa }}" onclick="toggleSingle()">
                                            </div>
                                        </td>
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

                                        {{-- GURU PEMBIMBING --}}
                                        <td class="text-center align-middle">
                                            <span class="text-xs font-weight-bold text-dark">
                                                <i class="fas fa-user-tie text-secondary me-1"></i> {{ $s->nama_guru }}
                                            </span>
                                        </td>

                                        {{-- KESIAPAN GURU --}}
                                        <td class="text-center align-middle">
                                            @if($s->status_rapor == 'belum_generate' || $s->status_rapor == 'draft')
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

                                        {{-- STATUS DATA --}}
                                        <td class="text-center align-middle">
                                            @if($s->status_rapor == 'belum_generate')
                                                <span class="badge badge-sm bg-gradient-light text-secondary border">BELUM ADA</span>
                                            @elseif($s->status_rapor == 'draft')
                                                <span class="badge badge-sm bg-gradient-info">DRAFT</span>
                                            @elseif($s->status_rapor == 'final')
                                                <span class="badge badge-sm bg-gradient-success">SIAP CETAK</span>
                                            @elseif($s->status_rapor == 'cetak')
                                                <span class="badge badge-sm bg-gradient-dark">SUDAH DICETAK</span>
                                            @endif
                                        </td>

                                        {{-- TANGGAL UPDATE --}}
                                        <td class="text-center align-middle">
                                            @if($s->last_update)
                                                <span class="text-xs font-weight-bold d-block">{{ \Carbon\Carbon::parse($s->last_update)->format('d M Y') }}</span>
                                                <span class="text-xxs text-secondary">{{ \Carbon\Carbon::parse($s->last_update)->format('H:i') }} WIB</span>
                                            @else
                                                <span class="text-xs text-secondary">-</span>
                                            @endif
                                        </td>

                                        {{-- AKSI SATUAN --}}
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                
                                                @if($s->is_ready_print)
                                                    <button onclick="cetakSatuan('{{ $s->id_siswa }}')" class="btn btn-xs bg-gradient-primary mb-0 px-3" data-bs-toggle="tooltip" title="Cetak PDF">
                                                        <i class="fas fa-print me-1"></i> Cetak
                                                    </button>
                                                    <button onclick="unlockRapor('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" class="btn btn-xs btn-outline-danger mb-0 px-3" data-bs-toggle="tooltip" title="Buka Kunci untuk Edit/Update">
                                                        <i class="fas fa-lock"></i> Buka Kunci
                                                    </button>

                                                @else
                                                    @if($s->status_guru == 'siap')
                                                        @if($s->status_rapor == 'draft')
                                                            <button onclick="generateRaporAdmin('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" class="btn btn-xs bg-gradient-warning mb-0 px-3" data-bs-toggle="tooltip" title="Tarik data nilai terbaru dari Guru">
                                                                <i class="fas fa-sync-alt me-1"></i> Perbarui Data
                                                            </button>
                                                        @else
                                                            <button onclick="generateRaporAdmin('{{ $s->id_siswa }}', '{{ addslashes($s->nama_siswa) }}')" class="btn btn-xs bg-gradient-secondary mb-0 px-3">
                                                                <i class="fas fa-cog me-1"></i> Generate
                                                            </button>
                                                        @endif
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
                                        <td colspan="8" class="text-center py-5 text-secondary">
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

{{-- SCRIPT JAVASCRIPT --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Inisialisasi Tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { 
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ==========================================
    // LOGIKA TANGGAL CETAK
    // ==========================================
    function getTglCetak() {
        const tgl = document.getElementById('tgl_cetak_global').value;
        if(!tgl) {
            Swal.fire('Perhatian!', 'Silakan pilih Tanggal Cetak Rapor terlebih dahulu pada kotak kuning.', 'warning');
            return null;
        }
        return tgl;
    }

    // ==========================================
    // LOGIKA MASTER CHECKBOX
    // ==========================================
    function toggleAll(source) {
        let checkboxes = document.querySelectorAll('.check-siswa');
        checkboxes.forEach(function(cb) {
            cb.checked = source.checked;
        });
    }

    function toggleSingle() {
        let total = document.querySelectorAll('.check-siswa').length;
        let checked = document.querySelectorAll('.check-siswa:checked').length;
        let checkAllBox = document.getElementById('checkAll');
        
        if(checkAllBox) {
            checkAllBox.checked = (total === checked && total > 0);
        }
    }

    function getCheckedIds() {
        let ids = [];
        document.querySelectorAll('.check-siswa:checked').forEach(function(checkbox) {
            ids.push(checkbox.value);
        });
        return ids;
    }

    // ==========================================
    // SMART BULK ACTION (FETCH API MASSAL)
    // ==========================================
    function bulkAction(actionType, actionName) {
        var selectedIds = getCheckedIds();
        
        if (selectedIds.length === 0) {
            Swal.fire({
                title: 'Perhatian!',
                text: 'Silakan centang minimal 1 siswa terlebih dahulu untuk melakukan aksi massal.',
                icon: 'warning',
                confirmButtonColor: '#344767'
            });
            return;
        }

        var targetUrl = "";
        var iconColor = "#344767";

        if (actionType === 'generate') {
            targetUrl = "{{ route('pkl.rapor.generate_massal') }}";
        } else if (actionType === 'unlock') {
            targetUrl = "{{ route('pkl.rapor.unlock_massal') }}";
            iconColor = "#ea0606";
        }

        Swal.fire({
            title: actionName + ' Massal',
            text: `Sistem akan memproses ${selectedIds.length} siswa yang Anda centang. Lanjutkan?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan!',
            confirmButtonColor: iconColor
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({title: 'Memproses...', text: 'Jangan tutup halaman ini.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                
                fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id_siswa_array: selectedIds,
                        id_kelas: "{{ $id_kelas }}", 
                        semester: "{{ $semesterRaw }}",
                        tahun_ajaran: "{{ $tahun_ajaran }}"
                    })
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    if (res.status >= 200 && res.status < 300) {
                        Swal.fire('Selesai!', res.body.message, res.body.status === 'success' ? 'success' : 'warning').then(() => { location.reload(); });
                    } else {
                        throw new Error(res.body.message || 'Terjadi kesalahan sistem internal.');
                    }
                })
                .catch(error => {
                    Swal.fire('Gagal!', error.message, 'error');
                });
            }
        });
    }

    // ==========================================
    // BULK DOWNLOAD PDF MERGE
    // ==========================================
    function bulkDownloadMerge() {
        var selectedIds = getCheckedIds();
        
        if (selectedIds.length === 0) {
            Swal.fire('Perhatian!', 'Silakan centang minimal 1 siswa untuk di-download.', 'warning');
            return;
        }

        const tgl = getTglCetak();
        if(!tgl) return;

        var idsString = selectedIds.join(',');
        var downloadUrl = "{{ route('pkl.rapor.download_massal_merge') }}?id_kelas={{ $id_kelas }}&semester={{ $semesterRaw }}&tahun_ajaran={{ $tahun_ajaran }}&tgl_cetak=" + tgl + "&ids=" + idsString;
        
        window.open(downloadUrl, '_blank');
        
        Swal.fire({
            title: 'Mempersiapkan PDF...',
            text: 'PDF sedang digabungkan dan akan segera terunduh di tab baru.',
            icon: 'info'
        });
    }

    // ==========================================
    // AJAX ACTION HELPER (SATUAN)
    // ==========================================
    function actionAjax(url, idSiswa) {
        Swal.fire({title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id_siswa: idSiswa,
                id_kelas: "{{ $id_kelas }}", 
                semester: "{{ $semesterRaw }}",
                tahun_ajaran: "{{ $tahun_ajaran }}"
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            if (res.status >= 200 && res.status < 300) {
                Swal.fire('Selesai!', res.body.message || 'Sukses.', res.body.status === 'success' ? 'success' : 'warning').then(() => { location.reload(); });
            } else {
                throw new Error(res.body.message || 'Terjadi kesalahan sistem internal.');
            }
        })
        .catch(error => {
            Swal.fire('Gagal!', error.message, 'error');
        });
    }

    // CETAK SATUAN
    function cetakSatuan(idSiswa) {
        const tgl = getTglCetak();
        if(!tgl) return;

        const url = "{{ route('pkl.rapor.cetak_proses', ':id') }}"
            .replace(':id', idSiswa) + 
            `?semester={{ $semesterRaw }}&tahun_ajaran={{ $tahun_ajaran }}&tgl_cetak=${tgl}`;
        
        window.open(url, '_blank');
    }

    // 1. GENERATE ADMIN (SATUAN) / PERBARUI DATA
    function generateRaporAdmin(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Tarik & Finalisasi Rapor?',
            text: `Sistem akan menarik data nilai terbaru dari Guru Pembimbing untuk ${namaSiswa}, memvalidasi kelengkapannya, dan langsung menguncinya. Lanjutkan?`,
            icon: 'info', showCancelButton: true, confirmButtonText: 'Ya, Lanjutkan!', confirmButtonColor: '#344767'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('pkl.rapor.generate') }}", idSiswa); });
    }

    // 2. UNLOCK RAPOR (SATUAN)
    function unlockRapor(idSiswa, namaSiswa) {
        Swal.fire({
            title: 'Buka Kunci?',
            text: `Rapor ${namaSiswa} akan dihanguskan dan statusnya dikembalikan ke DRAFT agar Guru Pembimbing bisa merevisi datanya.`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Buka Kunci!', confirmButtonColor: '#ea0606'
        }).then((res) => { if(res.isConfirmed) actionAjax("{{ route('pkl.rapor.unlock') }}", idSiswa); });
    }
</script>
@endsection