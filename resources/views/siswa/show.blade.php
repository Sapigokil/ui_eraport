{{-- File: resources/views/siswa/show.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Detail Siswa: ' . $siswa->nama_siswa)

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-eye me-2"></i> Detail Data Siswa: {{ $siswa->nama_siswa }}</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Tombol Aksi --}}
                            <div class="mb-4">
                                <a href="{{ route('master.siswa.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <a href="{{ route('master.siswa.edit', $siswa->id_siswa) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit Data
                                </a>
                            </div>

                            @php
                                // Akses relasi detail (sesuai Model Siswa), Kelas, dan Ekskul
                                $detail = $siswa->detail ?? new \App\Models\DetailSiswa();
                                $kelas = $siswa->kelas ?? null;
                                $ekskul = $siswa->ekskul ?? null;

                                $formatDate = function($date) {
                                    return $date ? \Carbon\Carbon::parse($date)->isoFormat('D MMMM YYYY') : '-';
                                };
                                $formatTingkat = function($tingkat) {
                                    return match($tingkat) {
                                        10 => 'Kelas X',
                                        11 => 'Kelas XI',
                                        12 => 'Kelas XII',
                                        default => '-',
                                    };
                                };
                                $formatVal = fn($val) => empty($val) ? '-' : $val; // Helper untuk nilai kosong
                            @endphp

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-primary">I. Data Pokok & Kelas</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Nama Siswa:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $siswa->nama_siswa }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">NISN:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $siswa->nisn ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">NIPD:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $siswa->nipd ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Jenis Kelamin:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $siswa->jenis_kelamin }}</dd>

                                        <dt class="col-sm-5 text-secondary">NIK:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nik ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Kelas / Tingkat:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $kelas->nama_kelas ?? '-' }} ({{ $formatTingkat($siswa->tingkat) }})</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Ekskul:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $ekskul->nama_ekskul ?? '-' }}</dd>

                                        <dt class="col-sm-5 text-secondary">Rombel Dapodik:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->rombel) }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Sekolah Asal:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->sekolah_asal) }}</dd>

                                        <dt class="col-sm-5 text-secondary">No. Seri Ijazah:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->no_seri_ijazah) }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">No. Peserta UN:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->no_peserta_ujian_nasional) }}</dd>
                                        
                                        {{-- SKHUN diletakkan di Grup I bersama data pokok ujian lainnya --}}
                                        <dt class="col-sm-5 text-secondary">SKHUN:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->skhun) }}</dd>
                                    </dl>
                                </div>
                            </div>
                            
                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-info">II. Detail Pribadi & Fisik</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Tempat/Tgl Lahir:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->tempat_lahir ?? '-' }}, {{ $formatDate($detail->tanggal_lahir) }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Agama:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->agama ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">No. Reg. Akta Lahir:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->no_regis_akta_lahir ?? '-' }}</dd>

                                        <dt class="col-sm-5 text-secondary">Kebutuhan Khusus:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->kebutuhan_khusus ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Anak ke-:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->anak_ke_berapa ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Jml Sdr Kandung:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->jml_saudara_kandung ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Berat Badan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->bb ? $detail->bb . ' kg' : '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Tinggi Badan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->tb ? $detail->tb . ' cm' : '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Lingkar Kepala:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->lingkar_kepala ? $detail->lingkar_kepala . ' cm' : '-' }}</dd>
                                    </dl>
                                </div>
                            </div>
                            
                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-danger">III. Alamat & Transportasi</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="text-sm mb-3"><span class="text-secondary me-1">Alamat Lengkap:</span> <span class="font-weight-bold">{{ $detail->alamat ?? '-' }}</span></p>
                                </div>
                                
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">RT/RW:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->rt ?? '-' }} / {{ $detail->rw ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Dusun:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->dusun ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Kelurahan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->kelurahan ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Kecamatan/Kode Pos:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->kecamatan ?? '-' }} ({{ $detail->kode_pos ?? '-' }})</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Lintang/Bujur:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->lintang ?? '-' }} / {{ $detail->bujur ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Jenis Tinggal:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->jenis_tinggal ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Alat Transportasi:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->alat_transportasi ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Jarak Rumah:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->jarak_rumah ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">No. HP/Telepon:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->no_hp ?? '-' }} / {{ $detail->telepon ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Email:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->email ?? '-' }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-success">IV. Data Orang Tua/Wali</h6>
                            <div class="row">
                                {{-- AYAH --}}
                                <div class="col-md-4">
                                    <h6 class="text-xs font-weight-bold mb-2 text-dark">AYAH</h6>
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Nama:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nama_ayah ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">NIK:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nik_ayah ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">Lahir/Didik:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->tahun_lahir_ayah ?? '-' }} / {{ $detail->jenjang_pendidikan_ayah ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">Pekerjaan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->pekerjaan_ayah ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">Penghasilan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->penghasilan_ayah ?? '-' }}</dd>
                                    </dl>
                                </div>
                                {{-- IBU --}}
                                <div class="col-md-4">
                                    <h6 class="text-xs font-weight-bold mb-2 text-dark">IBU</h6>
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Nama:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nama_ibu ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">NIK:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nik_ibu ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">Lahir/Didik:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->tahun_lahir_ibu ?? '-' }} / {{ $detail->jenjang_pendidikan_ibu ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">Pekerjaan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->pekerjaan_ibu ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">Penghasilan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->penghasilan_ibu ?? '-' }}</dd>
                                    </dl>
                                </div>
                                {{-- WALI --}}
                                <div class="col-md-4">
                                    <h6 class="text-xs font-weight-bold mb-2 text-dark">WALI</h6>
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Nama:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nama_wali ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">NIK:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nik_wali ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">Lahir/Didik:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->tahun_lahir_wali ?? '-' }} / {{ $detail->jenjang_pendidikan_wali ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">Pekerjaan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->pekerjaan_wali ?? '-' }}</dd>
                                        <dt class="col-sm-5 text-secondary">Penghasilan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->penghasilan_wali ?? '-' }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-warning">V. Data Beasiswa & Rekening</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Penerima KPS:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->penerima_kps) }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">No. KPS:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->no_kps) }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">No. KKS:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->no_kks) }}</dd>

                                        <dt class="col-sm-5 text-secondary">Penerima KIP:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->penerima_kip) }}</dd>

                                        <dt class="col-sm-5 text-secondary">No. KIP/Nama:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->no_kip) }} / {{ $formatVal($detail->nama_kip) }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Layak PIP (Usulan):</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->layak_pip_usulan) }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Alasan Layak PIP:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->alasan_layak_pip) }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        {{-- Data Bank --}}
                                        <dt class="col-sm-5 text-secondary">Nama Bank:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->bank) }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Nomor Rekening:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->no_rek_bank) }}</dd>

                                        <dt class="col-sm-5 text-secondary">Nama Pemilik Rek:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatVal($detail->rek_atas_nama) }}</dd>
                                    </dl>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
        
    </main>
@endsection