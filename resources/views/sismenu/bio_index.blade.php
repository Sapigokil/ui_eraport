@extends('layouts.app') 

@section('page-title', 'Biodata Diri Siswa')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">

        {{-- 1. ALERT PENGAJUAN PENDING --}}
        @if($pengajuanPending)
            <div class="alert alert-warning d-flex align-items-center text-dark mb-4 shadow-sm" role="alert">
                <i class="fas fa-user-clock fa-2x me-3"></i>
                <div>
                    <strong>Pengajuan Sedang Diproses!</strong><br>
                    <span class="text-sm">Perubahan biodata yang Anda ajukan sedang menunggu validasi dari Tata Usaha / Admin. Anda tidak dapat mengajukan perubahan baru hingga pengajuan sebelumnya disetujui atau ditolak.</span>
                </div>
            </div>
        @else
            {{-- 2. ALERT INFORMASI BACA-SAJA --}}
            <div class="alert alert-info d-flex align-items-center text-dark mb-4 shadow-sm" role="alert">
                <i class="fas fa-info-circle fa-2x me-3"></i>
                <div>
                    <strong>Informasi Penting!</strong><br>
                    <span class="text-sm">Halaman ini bersifat <i>Read-Only</i>. Jika terdapat ketidaksesuaian data, silakan klik tombol "Ajukan Perubahan Data" di bawah.</span>
                </div>
            </div>
        @endif

        {{-- Ganti bagian Alert Riwayat Terakhir dengan kode ini --}}
        @if(!$pengajuanPending && isset($riwayatTerakhir) && $riwayatTerakhir->is_read == 0)
            <div id="alertResponAdmin" class="alert {{ $riwayatTerakhir->status == 'disetujui' ? 'alert-success' : 'alert-danger' }} alert-dismissible fade show d-flex align-items-start text-dark mb-4 shadow-sm" role="alert">
                <i class="fas {{ $riwayatTerakhir->status == 'disetujui' ? 'fa-check-circle' : 'fa-times-circle' }} fa-2x me-3 mt-1"></i>
                <div class="w-100">
                    <strong>Pengajuan Sebelumnya {{ ucfirst($riwayatTerakhir->status) }}</strong>
                    <br>
                    <span class="text-sm">Pengajuan perubahan data Anda telah diproses oleh Admin.</span>
                    
                    @if(!empty($riwayatTerakhir->keterangan_admin))
                        <div class="mt-2 p-2 rounded" style="background-color: rgba(255,255,255,0.4); border-left: 3px solid #344767;">
                            <strong class="text-sm">Catatan Admin:</strong><br>
                            <span class="text-sm">{{ $riwayatTerakhir->keterangan_admin }}</span>
                        </div>
                    @endif
                </div>
                {{-- Tombol Close --}}
                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close" onclick="tandaiSudahDibaca({{ $riwayatTerakhir->id_pengajuan }})">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Script AJAX di bagian bawah file --}}
        <script>
            function tandaiSudahDibaca(id) {
                fetch("{{ url('/biodata/mark-as-read') }}/" + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        console.log("Notifikasi ditandai sudah dibaca");
                        // Opsional: Hapus badge di sidebar secara manual jika tidak ingin nunggu refresh
                    }
                });
            }
        </script>

        {{-- BUTTON LINK MENUJU HALAMAN EDIT --}}
        {{-- <div class="d-flex justify-content-end mb-3">
            @if(!$pengajuanPending)
                <a href="{{ route('sis.biodata.edit') }}" class="btn bg-gradient-primary shadow-sm">
                    <i class="fas fa-edit me-2"></i> Lengkapi / Ubah Biodata
                </a>
            @else
                <button type="button" class="btn btn-secondary shadow-sm" disabled>
                    <i class="fas fa-lock me-2"></i> Menunggu Persetujuan...
                </button>
            @endif
        </div> --}}

        {{-- PANEL STATUS PERIODE BIODATA & TOMBOL AKSI --}}
        <div class="card shadow-sm border-0 mt-4 mb-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap">
                
                <div class="d-flex align-items-center mb-3 mb-md-0">
                    @if($isBiodataOpen)
                        <div class="icon-shape bg-gradient-success shadow-success text-center rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-door-open text-white text-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-dark font-weight-bold">Periode Pembaruan Dibuka</h6>
                            <p class="text-sm text-secondary mb-0">
                                Berakhir pada: <strong class="text-dark">{{ \Carbon\Carbon::parse($bioSeason->tanggal_akhir)->translatedFormat('d F Y - H:i') }} WIB</strong>
                            </p>
                        </div>
                    @else
                        <div class="icon-shape bg-gradient-danger shadow-danger text-center rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-lock text-white text-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-dark font-weight-bold">Periode Pembaruan Ditutup</h6>
                            <p class="text-sm text-secondary mb-0">
                                @if($bioSeason && $bioSeason->tanggal_mulai && $bioSeason->tanggal_akhir)
                                    Jadwal Akses: {{ \Carbon\Carbon::parse($bioSeason->tanggal_mulai)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($bioSeason->tanggal_akhir)->translatedFormat('d M Y') }}
                                @else
                                    Jadwal pembaruan belum ditentukan oleh Admin.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                <div>
                    @if($isBiodataOpen)
                        @if($pengajuanPending)
                            <button class="btn bg-gradient-secondary mb-0 shadow-sm" disabled>
                                <i class="fas fa-hourglass-half me-1"></i> Menunggu Persetujuan...
                            </button>
                        @else
                            <a href="{{ route('sis.biodata.edit') }}" class="btn bg-gradient-primary mb-0 shadow-sm px-4 py-2">
                                <i class="fas fa-edit me-2"></i> Lengkapi / Ubah Biodata
                            </a>
                        @endif
                    @else
                        <button class="btn btn-outline-secondary mb-0 px-4 py-2" disabled>
                            <i class="fas fa-ban me-2"></i> Form Terkunci
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            {{-- KOLOM KIRI: DATA UTAMA & SEKOLAH --}}
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow-sm border h-100">
                    <div class="card-header bg-light border-bottom p-3">
                        <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-user-graduate me-2"></i> Identitas Sekolah</h6>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="avatar avatar-xl bg-gradient-primary rounded-circle mb-3 mx-auto shadow" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                            <h1 class="text-white mb-0">{{ substr($siswa->nama_siswa, 0, 1) }}</h1>
                        </div>
                        <h5 class="font-weight-bolder text-dark mb-0">{{ $siswa->nama_siswa }}</h5>
                        <p class="text-sm text-secondary mb-3">{{ $siswa->kelas->nama_kelas ?? 'Belum Punya Kelas' }}</p>

                        <ul class="list-group text-start text-sm mt-4">
                            <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">NISN:</strong> &nbsp; {{ $siswa->nisn ?? '-' }}</li>
                            <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">NIPD:</strong> &nbsp; {{ $siswa->nipd ?? '-' }}</li>
                            <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Jenis Kelamin:</strong> &nbsp; {{ $siswa->jenis_kelamin == 'L' ? 'Laki-Laki' : ($siswa->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</li>
                            <li class="list-group-item border-0 ps-0 pb-0 text-sm"><strong class="text-dark">Status Siswa:</strong> &nbsp; 
                                @if($siswa->status == 'aktif')
                                    <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                @else
                                    <span class="badge badge-sm bg-gradient-secondary">{{ ucfirst($siswa->status) }}</span>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: DATA DETAIL, ORANG TUA, WALI --}}
            <div class="col-lg-8 col-md-12">
                
                {{-- TABEL DATA PRIBADI --}}
                <div class="card shadow-sm border mb-4">
                    <div class="card-header bg-light border-bottom p-3">
                        <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-address-card me-2"></i> Data Pribadi Lengkap</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td width="30%" class="font-weight-bold text-secondary text-sm">Tempat, Tanggal Lahir</td>
                                        <td width="2%">:</td>
                                        <td class="text-sm text-dark">{{ $siswa->detail->tempat_lahir ?? '-' }}, {{ $siswa->detail->tanggal_lahir ? \Carbon\Carbon::parse($siswa->detail->tanggal_lahir)->format('d F Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-secondary text-sm">Agama</td>
                                        <td>:</td>
                                        <td class="text-sm text-dark">{{ $siswa->detail->agama ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-secondary text-sm">NIK Siswa</td>
                                        <td>:</td>
                                        <td class="text-sm text-dark">{{ $siswa->detail->nik ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-secondary text-sm">Anak Ke-berapa</td>
                                        <td>:</td>
                                        <td class="text-sm text-dark">{{ $siswa->detail->anak_ke_berapa ?? '-' }} dari {{ $siswa->detail->jml_saudara_kandung ?? '-' }} bersaudara</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-secondary text-sm">Email / No. HP</td>
                                        <td>:</td>
                                        <td class="text-sm text-dark">{{ $siswa->detail->email ?? '-' }} / {{ $siswa->detail->no_hp ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-secondary text-sm">Alamat Lengkap</td>
                                        <td>:</td>
                                        <td class="text-sm text-dark">
                                            {{ $siswa->detail->alamat ?? '-' }}<br>
                                            RT {{ $siswa->detail->rt ?? '-' }} / RW {{ $siswa->detail->rw ?? '-' }}, Dusun {{ $siswa->detail->dusun ?? '-' }}<br>
                                            Kel. {{ $siswa->detail->kelurahan ?? '-' }}, Kec. {{ $siswa->detail->kecamatan ?? '-' }}, Kode Pos: {{ $siswa->detail->kode_pos ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-secondary text-sm">Jenis Tinggal</td>
                                        <td>:</td>
                                        <td class="text-sm text-dark">{{ $siswa->detail->jenis_tinggal ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-secondary text-sm">Alat Transportasi</td>
                                        <td>:</td>
                                        <td class="text-sm text-dark">{{ $siswa->detail->alat_transportasi ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- TABEL DATA AYAH --}}
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border h-100">
                            <div class="card-header bg-light border-bottom p-3">
                                <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-male me-2"></i> Data Ayah</h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td width="40%" class="font-weight-bold text-secondary text-sm">Nama Ayah</td>
                                            <td width="5%">:</td>
                                            <td class="text-sm text-dark">{{ $siswa->detail->nama_ayah ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-secondary text-sm">NIK Ayah</td>
                                            <td>:</td>
                                            <td class="text-sm text-dark">{{ $siswa->detail->nik_ayah ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-secondary text-sm">Tahun Lahir</td>
                                            <td>:</td>
                                            <td class="text-sm text-dark">{{ $siswa->detail->tahun_lahir_ayah ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-secondary text-sm">Pendidikan</td>
                                            <td>:</td>
                                            <td class="text-sm text-dark">{{ $siswa->detail->jenjang_pendidikan_ayah ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-secondary text-sm">Pekerjaan</td>
                                            <td>:</td>
                                            <td class="text-sm text-dark">{{ $siswa->detail->pekerjaan_ayah ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- TABEL DATA IBU --}}
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border h-100">
                            <div class="card-header bg-light border-bottom p-3">
                                <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-female me-2"></i> Data Ibu</h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td width="40%" class="font-weight-bold text-secondary text-sm">Nama Ibu</td>
                                            <td width="5%">:</td>
                                            <td class="text-sm text-dark">{{ $siswa->detail->nama_ibu ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-secondary text-sm">NIK Ibu</td>
                                            <td>:</td>
                                            <td class="text-sm text-dark">{{ $siswa->detail->nik_ibu ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-secondary text-sm">Tahun Lahir</td>
                                            <td>:</td>
                                            <td class="text-sm text-dark">{{ $siswa->detail->tahun_lahir_ibu ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-secondary text-sm">Pendidikan</td>
                                            <td>:</td>
                                            <td class="text-sm text-dark">{{ $siswa->detail->jenjang_pendidikan_ibu ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-secondary text-sm">Pekerjaan</td>
                                            <td>:</td>
                                            <td class="text-sm text-dark">{{ $siswa->detail->pekerjaan_ibu ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABEL DATA WALI (HANYA MUNCUL JIKA ADA) --}}
                @if(!empty($siswa->detail->nama_wali))
                <div class="card shadow-sm border mb-4">
                    <div class="card-header bg-light border-bottom p-3">
                        <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-user-shield me-2"></i> Data Wali Siswa</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td width="30%" class="font-weight-bold text-secondary text-sm">Nama Wali</td>
                                        <td width="2%">:</td>
                                        <td class="text-sm text-dark">{{ $siswa->detail->nama_wali ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-secondary text-sm">NIK Wali</td>
                                        <td>:</td>
                                        <td class="text-sm text-dark">{{ $siswa->detail->nik_wali ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-secondary text-sm">Pekerjaan Wali</td>
                                        <td>:</td>
                                        <td class="text-sm text-dark">{{ $siswa->detail->pekerjaan_wali ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
    
    <x-app.footer />
    {{-- Script AJAX Penanda Sudah Dibaca --}}
    <script>
        function tandaiSudahDibaca(id) {
            // Gabungkan URL dasar dengan ID secara langsung
            const targetUrl = "{{ route('sis.biodata.read', '') }}/" + id;

            console.log("Mencoba mengirim request ke: ", targetUrl);

            fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Tampilkan respon dari server (Sukses atau Error) di Console
                console.log("Respon dari Server: ", data);
                
                if(!data.success) {
                    alert("Gagal menghilangkan notif: " + data.message);
                }
            })
            .catch(error => {
                console.error("Terjadi masalah jaringan/koneksi: ", error);
            });
        }
    </script>
</main>
@endsection