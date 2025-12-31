{{-- File: resources/views/guru/show.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Detail Guru: ' . $guru->nama_guru)

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-eye me-2"></i> Detail Data Guru: {{ $guru->nama_guru }}</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Tombol Aksi --}}
                            <div class="mb-4">
                                <a href="{{ route('master.guru.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <a href="{{ route('master.guru.edit', $guru->id_guru) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit Data
                                </a>
                            </div>

                            @php
                                // Perbaikan relasi detailGuru
                                $detail = $guru->detailGuru ?? new \App\Models\DetailGuru();
                                
                                $formatDate = function($date) {
                                    return $date ? \Carbon\Carbon::parse($date)->isoFormat('D MMMM YYYY') : '-';
                                };
                                $formatBool = function($value) {
                                    return $value == 1 ? '<span class="text-success font-weight-bold">Ya</span>' : '<span class="text-danger font-weight-bold">Tidak</span>';
                                };
                                $formatStatus = function($status) {
                                    if (strtolower($status) == 'aktif') {
                                        $class = 'bg-gradient-success';
                                    } elseif (strtolower($status) == 'nonaktif') {
                                        $class = 'bg-gradient-secondary';
                                    } else {
                                        $class = 'bg-gradient-warning';
                                    }
                                    return "<span class='badge badge-sm {$class} text-white'>" . ucfirst($status) . "</span>";
                                };
                            @endphp

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-primary">I. Data Pokok & Akun</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Nama Lengkap:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $guru->nama_guru }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">NIP:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $guru->nip ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">NUPTK:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $guru->nuptk ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Jenis Kelamin:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $guru->jenis_kelamin }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Jenis PTK:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $guru->jenis_ptk ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Status Aktif:</dt>
                                        <dd class="col-sm-7">{!! $formatStatus($guru->status) !!}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Role Sistem:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $guru->role }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">NIK:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nik ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">No. KK:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->no_kk ?? '-' }}</dd>
                                    </dl>
                                </div>
                            </div>
                            
                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-info">II. Detail Pribadi & Keluarga</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Tempat/Tgl Lahir:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->tempat_lahir ?? '-' }}, {{ $formatDate($detail->tanggal_lahir) }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Agama:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->agama ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Status Perkawinan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->status_perkawinan ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Nama Ibu Kandung:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nama_ibu_kandung ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Kewarganegaraan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->kewarganegaraan ?? '-' }}</dd>

                                        <dt class="col-sm-5 text-secondary">Karpeg:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->karpeg ?? '-' }}</dd>

                                        <dt class="col-sm-5 text-secondary">Karis/Karsu:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->karis_karsu ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Nama Suami/Istri:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nama_suami_istri ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">NIP Suami/Istri:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nip_suami_istri ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Pekerjaan Suami/Istri:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->pekerjaan_suami_istri ?? '-' }}</dd>
                                    </dl>
                                </div>
                            </div>
                            
                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-danger">III. Alamat & Kontak</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    {{-- Baris 1: Alamat Lengkap (Full Width) --}}
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
                                        
                                        <dt class="col-sm-5 text-secondary">Kecamatan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->kecamatan ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Kode Pos:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->kode_pos ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">No. HP:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->no_hp ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">No. Telepon:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->no_telp ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Email:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->email ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Lintang:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->lintang ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Bujur:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->bujur ?? '-' }}</dd>
                                    </dl>
                                </div>
                            </div>
                            
                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-success">IV. Data Kepegawaian & Gaji</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Status Kepegawaian:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->status_kepegawaian ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Pangkat/Gol:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->pangkat_gol ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">Sumber Gaji:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->sumber_gaji ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">NPWP:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->npwp ?? '-' }}</dd>

                                        <dt class="col-sm-5 text-secondary">Nama Wajib Pajak:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nama_wajib_pajak ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">SK CPNS/Tgl:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->sk_cpns ?? '-' }} ({{ $formatDate($detail->tgl_cpns) }})</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">TMT PNS:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatDate($detail->tmt_pns) }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">SK Pengangkatan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->sk_pengangkatan ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-5 text-secondary">TMT Pengangkatan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $formatDate($detail->tmt_pengangkatan) }}</dd>

                                        <dt class="col-sm-5 text-secondary">Lembaga Pengangkatan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->lembaga_pengangkatan ?? '-' }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-warning">V. Tugas & Sertifikasi</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Tugas Tambahan:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->tugas_tambahan ?? '-' }}</dd>

                                        <dt class="col-sm-5 text-secondary">NUKS:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nuks ?? '-' }}</dd>

                                        <dt class="col-sm-5 text-secondary">Lisensi Kepsek:</dt>
                                        <dd class="col-sm-7">{!! $formatBool($detail->lisensi_kepsek) !!}</dd>

                                        <dt class="col-sm-5 text-secondary">Diklat Kepengawasan:</dt>
                                        <dd class="col-sm-7">{!! $formatBool($detail->diklat_kepengawasan) !!}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-5 text-secondary">Keahlian Braille:</dt>
                                        <dd class="col-sm-7">{!! $formatBool($detail->keahlian_braille) !!}</dd>

                                        <dt class="col-sm-5 text-secondary">Keahlian Isyarat:</dt>
                                        <dd class="col-sm-7">{!! $formatBool($detail->keahlian_isyarat) !!}</dd>

                                        <dt class="col-sm-5 text-secondary">Nama Bank:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->bank ?? '-' }}</dd>

                                        <dt class="col-sm-5 text-secondary">Nomor Rekening:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->norek_bank ?? '-' }}</dd>

                                        <dt class="col-sm-5 text-secondary">Nama Pemilik Rek:</dt>
                                        <dd class="col-sm-7 font-weight-bold">{{ $detail->nama_rek ?? '-' }}</dd>
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
    
    {{-- FORM TERSEMBUNYI UNTUK UPLOAD CSV (Menggunakan form lama yang diakses via dropdown) --}}
    <form id="form_import_guru_csv" action="{{ route('master.guru.import') }}" method="POST" enctype="multipart/form-data" class="d-none">
        @csrf
        <input type="file" name="file" accept=".csv, .txt" onchange="if(this.files.length > 0) { showProcessingAlert(); this.form.submit(); }">
    </form>

    {{-- FORM TERSEMBUNYI UNTUK UPLOAD EXCEL GURU (Menuju route baru) --}}
    <form id="form_import_guru_xlsx" action="{{ route('master.guru.import.xlsx') }}" method="POST" enctype="multipart/form-data" class="d-none">
        @csrf
        <input type="file" name="file" accept=".xlsx, .xls" onchange="if(this.files.length > 0) { showProcessingAlert(); this.form.submit(); }">
    </form>
    
    {{-- SCRIPT JAVASCRIPT UNTUK POPUP PROGRESS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Fungsi Pop-up Peringatan Progress
            window.showProcessingAlert = function() {
                const existingAlert = document.getElementById('processingAlert');
                if (existingAlert) return;

                const alertHtml = `
                    <div class="alert bg-gradient-warning text-white text-center shadow-lg" role="alert" id="processingAlert" style="position: fixed; top: 20%; left: 50%; transform: translate(-50%, -50%); z-index: 1050; padding: 20px; border-radius: 10px;">
                        <h4 class="alert-heading text-white">PROSES IMPORT SEDANG BERJALAN</h4>
                        <p>Mohon tunggu. Proses ini mungkin memakan waktu beberapa saat. **Jangan tutup atau refresh halaman browser ini!**</p>
                        <div class="spinner-border text-white" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', alertHtml);
            }
            
            // Fungsi Menghilangkan Pop-up Peringatan (tidak dipanggil karena page refresh)
            window.hideProcessingAlert = function() {
                const alert = document.getElementById('processingAlert');
                if (alert) {
                    alert.remove();
                }
            }
        });
    </script>
@endsection