@extends('layouts.app') 

@section('page-title', 'Review Pengajuan: ' . ($pengajuan->siswa->nama_siswa ?? ''))

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    
    <x-app.navbar />
    
    <div class="container-fluid py-4 px-5"> 
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="text-dark font-weight-bolder mb-0">Review Pengajuan Biodata</h5>
            <a href="{{ route('master.validasi_bio.index') }}" class="btn btn-sm btn-white shadow-sm border mb-0 d-flex align-items-center">
                <i class="fas fa-arrow-left me-2 text-dark"></i> Kembali ke Antrean
            </a>
        </div>

        @php
            $siswa = $pengajuan->siswa;
            $detail = $siswa->detail ?? new \App\Models\DetailSiswa();
            $kelas = $siswa->kelas ?? null;
            $ekskul = $siswa->ekskul ?? null;

            // Helper Format Default
            $formatDate = fn($date) => $date ? \Carbon\Carbon::parse($date)->isoFormat('D MMMM YYYY') : '-';
            $formatTingkat = fn($tingkat) => match($tingkat) { 10 => 'Kelas X', 11 => 'Kelas XI', 12 => 'Kelas XII', default => '-' };
            $formatVal = fn($val) => empty($val) ? '-' : $val;

            // ====================================================================
            // 💡 FUNGSI AJAIB UNTUK INLINE DIFF (MENAMPILKAN PERUBAHAN DATA)
            // ====================================================================
            
            // 1. Fungsi untuk mencoret data lama jika ada pengajuan baru
            $oldStyle = function($fieldKey) use ($data_perubahan) {
                return isset($data_perubahan[$fieldKey]) 
                    ? 'text-decoration-line-through text-danger opacity-6 font-weight-bold' 
                    : 'font-weight-bold text-dark';
            };

            // 2. Fungsi untuk merender kotak data baru beserta tombol V/X
            $renderDiff = function($fieldKey) use ($data_perubahan) {
                if(isset($data_perubahan[$fieldKey])) {
                    $baru = empty($data_perubahan[$fieldKey]['baru']) || $data_perubahan[$fieldKey]['baru'] == '-' ? '(Dikosongkan)' : htmlspecialchars($data_perubahan[$fieldKey]['baru']);
                    
                    return '
                    <div class="mt-2 p-2 rounded border border-info shadow-sm" style="background-color: #e8f4f8;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-info font-weight-bold text-sm">
                                <i class="fas fa-level-up-alt fa-rotate-90 me-1"></i> Baru: <span class="text-dark">'.$baru.'</span>
                            </span>
                            <div class="btn-group shadow-none ms-2">
                                <input type="radio" class="btn-check" name="keputusan['.$fieldKey.']" id="terima_'.$fieldKey.'" value="terima" checked>
                                <label class="btn btn-outline-success btn-sm mb-0 px-2 py-1" for="terima_'.$fieldKey.'" title="Terima Perubahan"><i class="fas fa-check"></i></label>
                                
                                <input type="radio" class="btn-check" name="keputusan['.$fieldKey.']" id="tolak_'.$fieldKey.'" value="tolak">
                                <label class="btn btn-outline-danger btn-sm mb-0 px-2 py-1" for="tolak_'.$fieldKey.'" title="Tolak Perubahan"><i class="fas fa-times"></i></label>
                            </div>
                        </div>
                    </div>';
                }
                return '';
            };
        @endphp

        <div class="row">
            <div class="col-12">
                
                {{-- Form Utama Membungkus Seluruh Profil --}}
                <form action="{{ route('master.validasi_bio.proses', $pengajuan->id_pengajuan) }}" method="POST">
                    @csrf
                    
                    <div class="card shadow-sm border mb-4">
                        
                        {{-- HEADER CARD --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center pe-4">
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas fa-user-edit me-2"></i> Kroscek & Validasi Profil: {{ $siswa->nama_siswa }}
                                </h6>
                                <span class="badge bg-white text-dark shadow-sm">
                                    {{ count($data_perubahan) }} Kolom Diubah
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4 mt-3">
                            
                            {{-- PANEL AKSI MASSAL --}}
                            <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded" style="background-color: #f8f9fa; border: 1px dashed #dee2e6;">
                                <div>
                                    <h6 class="mb-0 text-sm font-weight-bold text-dark"><i class="fas fa-bolt text-warning me-2"></i>Aksi Cepat (Massal)</h6>
                                    <span class="text-xs text-secondary">Ambil keputusan untuk {{ count($data_perubahan) }} kolom yang diubah sekaligus.</span>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm bg-gradient-success mb-0 me-2 shadow-sm" onclick="terimaSemuaKolom()">
                                        <i class="fas fa-check-double me-1"></i> Terima Semua
                                    </button>
                                    <button type="button" class="btn btn-sm bg-gradient-danger mb-0 shadow-sm" onclick="tolakSemuaKolom()">
                                        <i class="fas fa-times-circle me-1"></i> Tolak Semua
                                    </button>
                                </div>
                            </div>

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-primary">I. Data Pokok & Kelas</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-4 text-secondary">Nama Siswa:</dt>
                                        <dd class="col-sm-8 font-weight-bold text-dark">{{ $siswa->nama_siswa }}</dd>
                                        
                                        <dt class="col-sm-4 text-secondary">NISN / NIPD:</dt>
                                        <dd class="col-sm-8 font-weight-bold text-dark">{{ $siswa->nisn ?? '-' }} / {{ $siswa->nipd ?? '-' }}</dd>
                                        
                                        <dt class="col-sm-4 text-secondary">Jenis Kelamin:</dt>
                                        <dd class="col-sm-8 font-weight-bold text-dark">{{ $siswa->jenis_kelamin }}</dd>

                                        {{-- CONTOH PENERAPAN INLINE DIFF (Hanya pada kolom detail) --}}
                                        <dt class="col-sm-4 text-secondary mt-2">NIK:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('nik') }}">{{ $detail->nik ?? '-' }}</span>
                                            {!! $renderDiff('nik') !!}
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-4 text-secondary">Kelas / Tingkat:</dt>
                                        <dd class="col-sm-8 font-weight-bold text-dark">{{ $kelas->nama_kelas ?? '-' }} ({{ $formatTingkat($siswa->tingkat) }})</dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Sekolah Asal:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('sekolah_asal') }}">{{ $formatVal($detail->sekolah_asal) }}</span>
                                            {!! $renderDiff('sekolah_asal') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">No. Seri Ijazah:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('no_seri_ijazah') }}">{{ $formatVal($detail->no_seri_ijazah) }}</span>
                                            {!! $renderDiff('no_seri_ijazah') !!}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-info">II. Detail Pribadi & Fisik</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-4 text-secondary mt-2">Tempat Lahir:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('tempat_lahir') }}">{{ $detail->tempat_lahir ?? '-' }}</span>
                                            {!! $renderDiff('tempat_lahir') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Tanggal Lahir:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('tanggal_lahir') }}">{{ $formatDate($detail->tanggal_lahir) }}</span>
                                            {!! $renderDiff('tanggal_lahir') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Agama:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('agama') }}">{{ $detail->agama ?? '-' }}</span>
                                            {!! $renderDiff('agama') !!}
                                        </dd>

                                        <dt class="col-sm-4 text-secondary mt-2">No. Akta Lahir:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('no_regis_akta_lahir') }}">{{ $detail->no_regis_akta_lahir ?? '-' }}</span>
                                            {!! $renderDiff('no_regis_akta_lahir') !!}
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-4 text-secondary mt-2">Anak ke-:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('anak_ke_berapa') }}">{{ $detail->anak_ke_berapa ?? '-' }}</span>
                                            {!! $renderDiff('anak_ke_berapa') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Jml Saudara:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('jml_saudara_kandung') }}">{{ $detail->jml_saudara_kandung ?? '-' }}</span>
                                            {!! $renderDiff('jml_saudara_kandung') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Berat Badan:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('bb') }}">{{ $detail->bb ? $detail->bb . ' kg' : '-' }}</span>
                                            {!! $renderDiff('bb') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Tinggi Badan:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('tb') }}">{{ $detail->tb ? $detail->tb . ' cm' : '-' }}</span>
                                            {!! $renderDiff('tb') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Lingkar Kepala:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('lingkar_kepala') }}">{{ $detail->lingkar_kepala ? $detail->lingkar_kepala . ' cm' : '-' }}</span>
                                            {!! $renderDiff('lingkar_kepala') !!}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-danger">III. Alamat & Transportasi</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <span class="text-secondary text-sm font-weight-bold">Alamat Lengkap (Jalan/Gg/Blok):</span><br>
                                    <span class="{{ $oldStyle('alamat') }} text-md">{{ $detail->alamat ?? '-' }}</span>
                                    <div class="w-50">
                                        {!! $renderDiff('alamat') !!}
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-4 text-secondary mt-2">RT:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('rt') }}">{{ $detail->rt ?? '-' }}</span>
                                            {!! $renderDiff('rt') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">RW:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('rw') }}">{{ $detail->rw ?? '-' }}</span>
                                            {!! $renderDiff('rw') !!}
                                        </dd>

                                        <dt class="col-sm-4 text-secondary mt-2">Dusun/Kampung:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('dusun') }}">{{ $detail->dusun ?? '-' }}</span>
                                            {!! $renderDiff('dusun') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Kelurahan/Desa:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('kelurahan') }}">{{ $detail->kelurahan ?? '-' }}</span>
                                            {!! $renderDiff('kelurahan') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Kecamatan:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('kecamatan') }}">{{ $detail->kecamatan ?? '-' }}</span>
                                            {!! $renderDiff('kecamatan') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Kode Pos:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('kode_pos') }}">{{ $detail->kode_pos ?? '-' }}</span>
                                            {!! $renderDiff('kode_pos') !!}
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row text-sm mb-0">
                                        <dt class="col-sm-4 text-secondary mt-2">Jenis Tinggal:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('jenis_tinggal') }}">{{ $detail->jenis_tinggal ?? '-' }}</span>
                                            {!! $renderDiff('jenis_tinggal') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Transportasi:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('alat_transportasi') }}">{{ $detail->alat_transportasi ?? '-' }}</span>
                                            {!! $renderDiff('alat_transportasi') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">No. HP / WA:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('no_hp') }}">{{ $detail->no_hp ?? '-' }}</span>
                                            {!! $renderDiff('no_hp') !!}
                                        </dd>
                                        
                                        <dt class="col-sm-4 text-secondary mt-2">Email:</dt>
                                        <dd class="col-sm-8 mt-2">
                                            <span class="{{ $oldStyle('email') }}">{{ $detail->email ?? '-' }}</span>
                                            {!! $renderDiff('email') !!}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <hr class="my-4">

                            {{-- ================================================= --}}
                            <h6 class="text-sm font-weight-bolder mb-3 text-success">IV. Data Orang Tua/Wali</h6>
                            <div class="row">
                                {{-- AYAH --}}
                                <div class="col-md-4 border-end">
                                    <h6 class="text-xs font-weight-bold mb-2 text-dark bg-gray-100 p-2 rounded text-center">DATA AYAH KANDUNG</h6>
                                    <dl class="row text-sm mb-0 px-2">
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Nama Ayah:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('nama_ayah') }}">{{ $detail->nama_ayah ?? '-' }}</span>
                                            {!! $renderDiff('nama_ayah') !!}
                                        </dd>
                                        
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">NIK Ayah:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('nik_ayah') }}">{{ $detail->nik_ayah ?? '-' }}</span>
                                            {!! $renderDiff('nik_ayah') !!}
                                        </dd>
                                        
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Tahun Lahir:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('tahun_lahir_ayah') }}">{{ $detail->tahun_lahir_ayah ?? '-' }}</span>
                                            {!! $renderDiff('tahun_lahir_ayah') !!}
                                        </dd>
                                        
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Pendidikan:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('jenjang_pendidikan_ayah') }}">{{ $detail->jenjang_pendidikan_ayah ?? '-' }}</span>
                                            {!! $renderDiff('jenjang_pendidikan_ayah') !!}
                                        </dd>

                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Pekerjaan:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('pekerjaan_ayah') }}">{{ $detail->pekerjaan_ayah ?? '-' }}</span>
                                            {!! $renderDiff('pekerjaan_ayah') !!}
                                        </dd>

                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Penghasilan:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('penghasilan_ayah') }}">{{ $detail->penghasilan_ayah ?? '-' }}</span>
                                            {!! $renderDiff('penghasilan_ayah') !!}
                                        </dd>
                                    </dl>
                                </div>
                                
                                {{-- IBU --}}
                                <div class="col-md-4 border-end">
                                    <h6 class="text-xs font-weight-bold mb-2 text-dark bg-gray-100 p-2 rounded text-center">DATA IBU KANDUNG</h6>
                                    <dl class="row text-sm mb-0 px-2">
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Nama Ibu:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('nama_ibu') }}">{{ $detail->nama_ibu ?? '-' }}</span>
                                            {!! $renderDiff('nama_ibu') !!}
                                        </dd>
                                        
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">NIK Ibu:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('nik_ibu') }}">{{ $detail->nik_ibu ?? '-' }}</span>
                                            {!! $renderDiff('nik_ibu') !!}
                                        </dd>
                                        
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Tahun Lahir:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('tahun_lahir_ibu') }}">{{ $detail->tahun_lahir_ibu ?? '-' }}</span>
                                            {!! $renderDiff('tahun_lahir_ibu') !!}
                                        </dd>
                                        
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Pendidikan:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('jenjang_pendidikan_ibu') }}">{{ $detail->jenjang_pendidikan_ibu ?? '-' }}</span>
                                            {!! $renderDiff('jenjang_pendidikan_ibu') !!}
                                        </dd>

                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Pekerjaan:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('pekerjaan_ibu') }}">{{ $detail->pekerjaan_ibu ?? '-' }}</span>
                                            {!! $renderDiff('pekerjaan_ibu') !!}
                                        </dd>

                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Penghasilan:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('penghasilan_ibu') }}">{{ $detail->penghasilan_ibu ?? '-' }}</span>
                                            {!! $renderDiff('penghasilan_ibu') !!}
                                        </dd>
                                    </dl>
                                </div>
                                
                                {{-- WALI --}}
                                <div class="col-md-4">
                                    <h6 class="text-xs font-weight-bold mb-2 text-dark bg-gray-100 p-2 rounded text-center">DATA WALI (OPSIONAL)</h6>
                                    <dl class="row text-sm mb-0 px-2">
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Nama Wali:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('nama_wali') }}">{{ $detail->nama_wali ?? '-' }}</span>
                                            {!! $renderDiff('nama_wali') !!}
                                        </dd>
                                        
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">NIK Wali:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('nik_wali') }}">{{ $detail->nik_wali ?? '-' }}</span>
                                            {!! $renderDiff('nik_wali') !!}
                                        </dd>
                                        
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Tahun Lahir:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('tahun_lahir_wali') }}">{{ $detail->tahun_lahir_wali ?? '-' }}</span>
                                            {!! $renderDiff('tahun_lahir_wali') !!}
                                        </dd>
                                        
                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Pendidikan:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('jenjang_pendidikan_wali') }}">{{ $detail->jenjang_pendidikan_wali ?? '-' }}</span>
                                            {!! $renderDiff('jenjang_pendidikan_wali') !!}
                                        </dd>

                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Pekerjaan:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('pekerjaan_wali') }}">{{ $detail->pekerjaan_wali ?? '-' }}</span>
                                            {!! $renderDiff('pekerjaan_wali') !!}
                                        </dd>

                                        <dt class="col-12 text-secondary mt-2 border-bottom pb-1">Penghasilan:</dt>
                                        <dd class="col-12 mt-1">
                                            <span class="{{ $oldStyle('penghasilan_wali') }}">{{ $detail->penghasilan_wali ?? '-' }}</span>
                                            {!! $renderDiff('penghasilan_wali') !!}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <hr class="my-4">

                            {{-- Catatan Penolakan & Tombol Submit --}}
                            <div class="row mt-4 bg-gray-50 p-4 rounded border">
                                <div class="col-md-8">
                                    <label class="form-label text-sm font-weight-bold text-dark"><i class="fas fa-comment-dots text-info me-2"></i>Catatan untuk Siswa (Opsional)</label>
                                    <textarea class="form-control bg-white" name="keterangan_admin" rows="2" placeholder="Tuliskan alasan jika ada data yang Anda tolak..."></textarea>
                                </div>
                                <div class="col-md-4 d-flex align-items-end justify-content-end mt-3 mt-md-0">
                                    <button type="submit" class="btn bg-gradient-dark btn-lg mb-0 shadow-sm w-100">
                                        <i class="fas fa-save me-2"></i> Simpan Validasi
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
                
            </div>
        </div>
        
    </div>
    <x-app.footer />

    {{-- SCRIPT AKSI MASSAL --}}
    <script>
        function terimaSemuaKolom() {
            const radioTerima = document.querySelectorAll('input[type="radio"][value="terima"]');
            radioTerima.forEach(radio => radio.checked = true);
        }

        function tolakSemuaKolom() {
            const radioTolak = document.querySelectorAll('input[type="radio"][value="tolak"]');
            radioTolak.forEach(radio => radio.checked = true);
        }
    </script>
</main>
@endsection