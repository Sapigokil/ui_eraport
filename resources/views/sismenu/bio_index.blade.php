@extends('layouts.app') 

@section('page-title', 'Biodata Diri Siswa')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">

        {{-- ALERT INFORMASI READ-ONLY --}}
        <div class="alert alert-info d-flex align-items-center text-dark mb-4" role="alert">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <strong>Informasi Penting!</strong><br>
                <span class="text-sm">Halaman ini bersifat <i>Read-Only</i> (hanya baca). Jika terdapat ketidaksesuaian data, silakan menghubungi Wali Kelas atau Admin Tata Usaha sekolah untuk melakukan pembaruan data.</span>
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
</main>
@endsection