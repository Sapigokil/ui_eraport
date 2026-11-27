@extends('layouts.app')

@section('page-title', 'Modul Smart Based Assessment')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <style>
        .alert-warning {
            background-image: linear-gradient(310deg, #fb8c00 0%, #fdb03d 100%) !important;
            color: #fff !important; 
            border: none !important; 
        }
        .bg-gradient-danger {
            background-image: linear-gradient(310deg, #ea0606 0%, #ff667c 100%) !important;
            color: #fff !important;
        }
        .bg-gradient-success {
            background-image: linear-gradient(310deg, #17ad37 0%, #98ec2d 100%) !important;
            color: #fff !important;
        }
        .bg-gradient-warning {
            background-image: linear-gradient(310deg, #f53939 0%, #fbcf33 100%) !important;
            color: #fff !important;
        }
        .sort-header {
            color: #6c757d;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .sort-header:hover {
            color: #344767;
        }
        .kode-uji-badge {
            background-color: #344767;
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-block;
        }
    </style>

    <div class="container-fluid py-4 px-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- 1. HEADER UTAMA BANNER --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 overflow-hidden position-relative">
                            <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                                <i class="fas fa-laptop-code text-white" style="font-size: 8rem;"></i>
                            </div>

                            <div class="d-flex justify-content-between align-items-center position-relative z-index-1 px-3">
                                <div>
                                    <h6 class="text-white text-capitalize mb-0">
                                        <i class="fas fa-list me-2"></i> Daftar Ujian SBA
                                    </h6>
                                    <p class="text-white text-xs opacity-8 mb-0 ms-4 ps-1">
                                        Kelola daftar ujian, pencarian data, pengaturan waktu, dan soal untuk siswa.
                                    </p>
                                </div>
                                <div>
                                    <a href="{{ route('sba.create') }}" class="btn btn-white text-primary mb-0 shadow-sm">
                                        <i class="fas fa-plus me-2"></i> Buat Ujian Baru
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-2 mt-2">
                        
                        {{-- ALERT SYSTEM --}}
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

                        @php
                            $hasViewAll = $canViewAll ?? auth()->user()->can('sba.view_all');
                            if (isset($selectedAuthorId)) {
                                $activeAuthorId = ($selectedAuthorId === '' || $selectedAuthorId === 'all')
                                    ? 'all'
                                    : (string) $selectedAuthorId;
                            } elseif ($hasViewAll) {
                                $rawUserId = request('user_id');
                                if (request()->has('user_id') && ($rawUserId === null || $rawUserId === '' || $rawUserId === 'all')) {
                                    $activeAuthorId = 'all';
                                } elseif (request()->filled('user_id')) {
                                    $activeAuthorId = (string) $rawUserId;
                                } else {
                                    $activeAuthorId = (string) auth()->id();
                                }
                            } else {
                                $activeAuthorId = (string) auth()->id();
                            }
                        @endphp

                        {{-- PANEL FILTER & SEARCH --}}
                        <form action="{{ route('sba.index') }}" method="GET" class="row g-2 mb-3 align-items-center">
                            {{-- Pertahankan parameter sorting jika ada --}}
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">

                            {{-- Filter Kategori Uji --}}
                            <div class="col-md-2">
                                <select name="kategori_uji" class="form-select form-select-sm border px-2 py-1" onchange="this.form.submit()">
                                    <option value="">-- Semua Kategori --</option>
                                    @foreach(['PSTS', 'PSAS', 'Sumatif', 'Umum'] as $katOption)
                                        <option value="{{ $katOption }}" {{ request('kategori_uji') === $katOption ? 'selected' : '' }}>
                                            {{ $katOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Mata Pelajaran --}}
                            <div class="{{ $hasViewAll ? 'col-md-2' : 'col-md-3' }}">
                                <select name="id_mapel" class="form-select form-select-sm border px-2 py-1" onchange="this.form.submit()">
                                    <option value="">-- Semua Mapel --</option>
                                    <option value="none" {{ request('id_mapel') === 'none' ? 'selected' : '' }}>Tanpa Mapel (Umum)</option>
                                    @foreach($mapels ?? [] as $mapel)
                                        <option value="{{ $mapel->id_mapel }}" {{ (string) request('id_mapel') === (string) $mapel->id_mapel ? 'selected' : '' }}>
                                            {{ $mapel->nama_mapel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Author Dropdown (Hanya tampil jika punya permission sba.view_all) --}}
                            @if($hasViewAll)
                                <div class="col-md-2">
                                    <select name="user_id" class="form-select form-select-sm border px-2 py-1" onchange="this.form.submit()">
                                        <option value="all" {{ $activeAuthorId === 'all' ? 'selected' : '' }}>-- Semua Author --</option>
                                        @foreach($authors as $author)
                                            <option value="{{ $author->id }}" {{ $activeAuthorId === (string) $author->id ? 'selected' : '' }}>
                                                {{ $author->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            {{-- Filter Keysearch --}}
                            <div class="{{ $hasViewAll ? 'col-md-3' : 'col-md-4' }}">
                                <div class="input-group input-group-sm border rounded">
                                    <span class="input-group-text bg-transparent border-0 pe-1">
                                        <i class="fas fa-search text-secondary text-xs"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-0 ps-1" 
                                           placeholder="Cari Kode Uji, Judul, atau Deskripsi..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>

                            {{-- Tombol Submit & Reset Filter --}}
                            <div class="col-md-1 d-flex gap-1">
                                <button type="submit" class="btn btn-sm btn-primary mb-0 w-100 px-2" style="background-color: #6f42c1; border-color: #6f42c1;" title="Cari / Filter">
                                    <i class="fas fa-filter"></i>
                                </button>
                                @if(request()->hasAny(['search', 'user_id', 'kategori_uji', 'id_mapel', 'per_page', 'sort_by']))
                                    <a href="{{ route('sba.index') }}" class="btn btn-sm btn-outline-secondary mb-0 px-2" title="Reset Filter">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                @endif
                            </div>

                            {{-- Per Page Selector --}}
                            <div class="col-md-2 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <span class="text-xs text-secondary">Tampil:</span>
                                    <select name="per_page" class="form-select form-select-sm border px-2 py-1 w-auto" 
                                            style="padding-right: 1.8rem !important; min-width: 65px;" 
                                            onchange="this.form.submit()">
                                        @foreach([10, 25, 50, 100] as $pageOption)
                                            <option value="{{ $pageOption }}" {{ request('per_page', 10) == $pageOption ? 'selected' : '' }}>
                                                {{ $pageOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>

                        {{-- HELPER UNTUK URL SORTING --}}
                        @php
                            function getSortUrl($column) {
                                $currentSort = request('sort_by', 'id');
                                $currentOrder = request('sort_order', 'desc');
                                $nextOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
                                return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_order' => $nextOrder]);
                            }

                            function getSortIcon($column) {
                                if (request('sort_by', 'id') !== $column) {
                                    return '<i class="fas fa-sort opacity-4 ms-1 text-xxs"></i>';
                                }
                                return request('sort_order', 'desc') === 'asc' 
                                    ? '<i class="fas fa-sort-up ms-1 text-primary text-xs"></i>' 
                                    : '<i class="fas fa-sort-down ms-1 text-primary text-xs"></i>';
                            }
                        @endphp

                        {{-- TABEL UJIAN RATA KIRI --}}
                        <div class="table-responsive p-0">
                            <table class="table align-items-center justify-content-center mb-0 border">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-start ps-3" width="5%">No</th>
                                        
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-start ps-3" width="13%">
                                            <a href="{{ getSortUrl('kode_uji') }}" class="sort-header">
                                                Kode Uji {!! getSortIcon('kode_uji') !!}
                                            </a>
                                        </th>
                                        
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-start ps-3" width="24%">
                                            <a href="{{ getSortUrl('judul') }}" class="sort-header">
                                                Judul / Deskripsi {!! getSortIcon('judul') !!}
                                            </a>
                                        </th>
                                        
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-start ps-3" width="19%">
                                            <a href="{{ getSortUrl('id_mapel') }}" class="sort-header">
                                                Mapel - Kategori / Author {!! getSortIcon('id_mapel') !!}
                                            </a>
                                        </th>
                                        
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-start ps-3" width="16%">
                                            <a href="{{ getSortUrl('waktu_mulai') }}" class="sort-header">
                                                Waktu / Durasi {!! getSortIcon('waktu_mulai') !!}
                                            </a>
                                        </th>
                                        
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="11%">
                                            <a href="{{ getSortUrl('status') }}" class="sort-header justify-content-center">
                                                Status / Hasil {!! getSortIcon('status') !!}
                                            </a>
                                        </th>
                                        
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="12%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ujis as $index => $uji)
                                    @php
                                        $jumlahPerluDinilai = $uji->butuhPenilaianManual() ? ($uji->hasils_selesai_count ?? 0) : 0;
                                        $namaMapel = $uji->mapel ? $uji->mapel->nama_mapel : 'Umum / Tanpa Mapel';
                                        $kategoriLabel = $uji->kategori_uji ?? 'Umum';
                                    @endphp
                                    <tr>
                                        {{-- NO --}}
                                        <td class="text-start ps-3 text-sm align-middle">
                                            {{ ($ujis instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $ujis->firstItem() + $index : $loop->iteration }}
                                        </td>

                                        {{-- 1. KODE UJI (BADGE JELAS & MUDAH DIBACA) --}}
                                        <td class="text-start ps-3 align-middle">
                                            <span class="kode-uji-badge shadow-sm">
                                                {{ strtoupper($uji->kode_uji) }}
                                            </span>
                                        </td>

                                        {{-- 2. JUDUL / DESKRIPSI (20 Char + ...) --}}
                                        <td class="text-start ps-3 align-middle">
                                            <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $uji->judul }}</h6>
                                            <p class="text-xs text-secondary mb-0">
                                                @if($uji->deskripsi)
                                                    {{ Str::limit(strip_tags($uji->deskripsi), 20, '...') }}
                                                @else
                                                    <span class="text-muted italic">Tidak ada deskripsi</span>
                                                @endif
                                            </p>
                                        </td>

                                        {{-- 3. MAPEL - KATEGORI & AUTHOR --}}
                                        <td class="text-start ps-3 align-middle">
                                            <div class="d-flex flex-column">
                                                <span class="text-xs font-weight-bold text-dark mb-1">
                                                    <i class="fas fa-book text-primary me-1"></i>
                                                    {{ $namaMapel }} - <span class="badge bg-light text-primary border text-xxs px-2 py-0">{{ $kategoriLabel }}</span>
                                                </span>
                                                <span class="text-xs text-secondary">
                                                    <i class="fas fa-user-circle me-1"></i>
                                                    {{ $uji->author->name ?? 'Administrator' }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- 4. WAKTU / DURASI --}}
                                        <td class="text-start ps-3 align-middle">
                                            <span class="font-weight-bold text-dark text-xs d-block">
                                                @if($uji->waktu_mulai && $uji->waktu_selesai)
                                                    {{ $uji->waktu_mulai->format('d/m/Y H:i') }} - {{ $uji->waktu_selesai->format('d/m/Y H:i') }}
                                                @elseif($uji->waktu_mulai)
                                                    Mulai: {{ $uji->waktu_mulai->format('d/m/Y H:i') }}
                                                @else
                                                    <span class="text-muted">Fleksibel / Tanpa Jadwal</span>
                                                @endif
                                            </span>
                                            <span class="text-xs text-secondary">
                                                <i class="far fa-clock me-1 text-info"></i> Durasi: <strong>{{ $uji->durasi_menit > 0 ? $uji->durasi_menit . ' Menit' : 'Tanpa Batas' }}</strong>
                                            </span>
                                        </td>

                                        {{-- 5. STATUS / JUMLAH HASIL JAWABAN & PENANDA PERLU DINILAI --}}
                                        <td class="align-middle text-center text-sm">
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                @if($uji->status == 'published')
                                                    <span class="badge badge-sm bg-gradient-success">Published</span>
                                                @elseif($uji->status == 'closed')
                                                    <span class="badge badge-sm bg-gradient-danger">Closed</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">Draft</span>
                                                @endif

                                                {{-- LINK KLIKABLE MENUJU HALAMAN HASIL ASESI --}}
                                                <a href="{{ route('sba.hasil.index', $uji->id) }}" class="badge bg-light text-dark border text-xxs font-weight-bold px-2 py-1 text-decoration-none shadow-xs" title="Klik untuk lihat daftar peserta & skor">
                                                    <i class="fas fa-users me-1 text-primary"></i> {{ $uji->hasils_count ?? $uji->hasils()->count() }} Hasil <i class="fas fa-chevron-right text-xxs ms-1 opacity-5"></i>
                                                </a>

                                                {{-- PENANDA ANTREAN PENILAIAN MANUAL (ESAI / PG TANPA KUNCI) --}}
                                                @if($jumlahPerluDinilai > 0)
                                                    <a href="{{ route('sba.hasil.index', ['uji_id' => $uji->id, 'status_pengerjaan' => 'selesai']) }}" 
                                                       class="badge bg-warning text-dark text-xxs font-weight-bold px-2 py-1 text-decoration-none shadow-xs" 
                                                       title="Terdapat soal esai atau PG tanpa kunci yang menunggu penilaian guru">
                                                        <i class="fas fa-exclamation-circle me-1"></i> {{ $jumlahPerluDinilai }} Perlu Dinilai
                                                    </a>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- 6. ACTION BUTTONS --}}
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center align-items-center">
                                                {{-- Tombol Copy Link --}}
                                                <button type="button" class="btn btn-outline-info btn-sm mb-0 px-2 shadow-sm me-1 btn-copy-link" 
                                                        data-url="{{ route('sba.uji.halaman', $uji->kode_uji) }}" 
                                                        title="Salin Link Ujian">
                                                    <i class="fas fa-link"></i>
                                                </button>

                                                {{-- Tombol Duplikat Ujian & Soal --}}
                                                <form action="{{ route('sba.duplicate', $uji->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary btn-sm mb-0 px-2 shadow-sm me-1" 
                                                            title="Duplikat Ujian & Soal (Kode Baru)" 
                                                            onclick="return confirm('Duplikat ujian \'{{ addslashes($uji->judul) }}\' beserta seluruh soal dan kunci jawabannya?')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </form>

                                                {{-- Tombol Edit --}}
                                                <a href="{{ route('sba.edit', $uji->id) }}" class="btn btn-outline-warning btn-sm mb-0 px-2 shadow-sm me-1" title="Edit Pengaturan & Soal">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                {{-- Tombol Hapus --}}
                                                <form action="{{ route('sba.destroy', $uji->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm mb-0 px-2 shadow-sm" title="Hapus Ujian" onclick="return confirm('Apakah Anda yakin ingin menghapus ujian ini beserta seluruh hasil jawaban peserta?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-search fa-3x text-secondary mb-3 opacity-5"></i>
                                                <h6 class="text-secondary font-weight-normal">Data ujian tidak ditemukan.</h6>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINATION INTERAKTIF --}}
                        @if($ujis instanceof \Illuminate\Pagination\LengthAwarePaginator && $ujis->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-sm text-secondary">
                                    Menampilkan {{ $ujis->firstItem() }} sampai {{ $ujis->lastItem() }} dari total {{ $ujis->total() }} data
                                </div>
                                <div>
                                    {{ $ujis->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-app.footer />
</main>

{{-- SCRIPT COPY LINK TO CLIPBOARD --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-copy-link').forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            navigator.clipboard.writeText(url).then(() => {
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check text-success"></i>';
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin link:', err);
            });
        });
    });
});
</script>
@endsection