{{-- File: resources/views/guru/edit.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Edit Data Guru: ' . $guru->nama_guru)

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-warning shadow-warning border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-edit me-2"></i> Edit Data Guru: {{ $guru->nama_guru }}</h6>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            <form method="POST" action="{{ route('master.guru.update', $guru->id_guru) }}">
                                @csrf
                                @method('PUT') 
                                
                                @php
                                    $detail = $guru->detailGuru ?? new \App\Models\DetailGuru();
                                @endphp
                                <input type="hidden" name="detail_id" value="{{ $detail->id_detail_guru ?? '' }}">

                                {{-- Tombol Aksi --}}
                                <div class="mb-4">
                                    <button type="submit" class="btn bg-gradient-primary me-2">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                    <a href="{{ route('master.guru.show', $guru->id_guru) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Batal / Kembali
                                    </a>
                                </div>
                                
                                {{-- Notifikasi Error Validasi --}}
                                @if (session('error'))
                                    <div class="alert alert-danger text-sm">{{ session('error') }}</div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert alert-danger text-sm">
                                        Perhatian: Ada kesalahan validasi pada formulir. Silakan periksa kolom yang ditandai merah.
                                    </div>
                                @endif

                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-primary">I. Data Pokok Guru (Tabel Guru)</h6>
                                <hr>
                                {{-- Baris 1: Nama, NIP, NUPTK --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_guru" class="form-label">Nama Lengkap</label>
                                        <input type="text" name="nama_guru" class="form-control rounded-pill py-2 @error('nama_guru') is-invalid @enderror" 
                                               value="{{ old('nama_guru', $guru->nama_guru) }}" required>
                                        @error('nama_guru') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nip" class="form-label">NIP</label>
                                        <input type="text" name="nip" class="form-control rounded-pill py-2 @error('nip') is-invalid @enderror" 
                                               value="{{ old('nip', $guru->nip) }}">
                                        @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nuptk" class="form-label">NUPTK</label>
                                        <input type="text" name="nuptk" class="form-control rounded-pill py-2 @error('nuptk') is-invalid @enderror" 
                                               value="{{ old('nuptk', $guru->nuptk) }}">
                                        @error('nuptk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                {{-- Baris 2: Jenis Kelamin, Status, Jenis PTK --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="form-select rounded-pill py-2 @error('jenis_kelamin') is-invalid @enderror" required>
                                            @php $jk = old('jenis_kelamin', $guru->jenis_kelamin); @endphp
                                            <option value="Laki-laki" {{ $jk == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="Perempuan" {{ $jk == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                        @error('jenis_kelamin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="status" class="form-label">Status Keaktifan</label>
                                        <select name="status" class="form-select rounded-pill py-2 @error('status') is-invalid @enderror" required>
                                            @php $status = old('status', $guru->status); @endphp
                                            <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                            <option value="nonaktif" {{ $status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                        </select>
                                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="jenis_ptk" class="form-label">Jenis PTK</label>
                                        <input type="text" name="jenis_ptk" class="form-control rounded-pill py-2" 
                                               value="{{ old('jenis_ptk', $guru->jenis_ptk) }}">
                                    </div>
                                </div>
                                {{-- Baris 3: NIK, No. KK, Role/ID Pembelajaran (ReadOnly) --}}
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <label for="nik" class="form-label">NIK</label>
                                        <input type="text" name="nik" class="form-control rounded-pill py-2" 
                                               value="{{ old('nik', $detail->nik) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="no_kk" class="form-label">No. KK</label>
                                        <input type="text" name="no_kk" class="form-control rounded-pill py-2" 
                                               value="{{ old('no_kk', $detail->no_kk) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="role" class="form-label">Role Sistem / ID Pembelajaran</label>
                                        <input type="text" class="form-control rounded-pill py-2" 
                                               value="{{ $guru->role ?? '-' }} / {{ $guru->id_pembelajaran ?? '-' }}" readonly>
                                    </div>
                                </div>

                                <hr class="my-4">

                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-info">II. Detail Pribadi & Keluarga (Tabel DetailGuru)</h6>
                                <hr>
                                {{-- Baris 4: Tempat Lahir, Tanggal Lahir, Agama --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" class="form-control rounded-pill py-2" 
                                               value="{{ old('tempat_lahir', $detail->tempat_lahir) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" class="form-control rounded-pill py-2 @error('tanggal_lahir') is-invalid @enderror" 
                                               value="{{ old('tanggal_lahir', $detail->tanggal_lahir) }}">
                                        @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="agama" class="form-label">Agama</label>
                                        <input type="text" name="agama" class="form-control rounded-pill py-2" 
                                               value="{{ old('agama', $detail->agama) }}">
                                    </div>
                                </div>
                                {{-- Baris 5: Status Perkawinan, Nama Ibu Kandung, Kewarganegaraan --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="status_perkawinan" class="form-label">Status Perkawinan</label>
                                        <input type="text" name="status_perkawinan" class="form-control rounded-pill py-2" 
                                               value="{{ old('status_perkawinan', $detail->status_perkawinan) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_ibu_kandung" class="form-label">Nama Ibu Kandung</label>
                                        <input type="text" name="nama_ibu_kandung" class="form-control rounded-pill py-2" 
                                               value="{{ old('nama_ibu_kandung', $detail->nama_ibu_kandung) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="kewarganegaraan" class="form-label">Kewarganegaraan</label>
                                        <input type="text" name="kewarganegaraan" class="form-control rounded-pill py-2" 
                                               value="{{ old('kewarganegaraan', $detail->kewarganegaraan) }}">
                                    </div>
                                </div>
                                {{-- Baris 6: Suami/Istri, Pekerjaan, NIP Suami/Istri --}}
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_suami_istri" class="form-label">Nama Suami/Istri</label>
                                        <input type="text" name="nama_suami_istri" class="form-control rounded-pill py-2" 
                                               value="{{ old('nama_suami_istri', $detail->nama_suami_istri) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="pekerjaan_suami_istri" class="form-label">Pekerjaan Suami/Istri</label>
                                        <input type="text" name="pekerjaan_suami_istri" class="form-control rounded-pill py-2" 
                                               value="{{ old('pekerjaan_suami_istri', $detail->pekerjaan_suami_istri) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nip_suami_istri" class="form-label">NIP Suami/Istri</label>
                                        <input type="text" name="nip_suami_istri" class="form-control rounded-pill py-2" 
                                               value="{{ old('nip_suami_istri', $detail->nip_suami_istri) }}">
                                    </div>
                                </div>
                                

                                <hr class="my-4">

                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-danger">III. Alamat & Kontak</h6>
                                <hr>
                                {{-- Baris 7: Alamat Lengkap (12 kolom) --}}
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="alamat" class="form-label">Alamat Lengkap</label>
                                        <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $detail->alamat) }}</textarea>
                                        @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                {{-- Baris 8: RT, RW, Kode Pos --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="rt" class="form-label">RT</label>
                                        <input type="text" name="rt" class="form-control rounded-pill py-2" value="{{ old('rt', $detail->rt) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="rw" class="form-label">RW</label>
                                        <input type="text" name="rw" class="form-control rounded-pill py-2" value="{{ old('rw', $detail->rw) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="kode_pos" class="form-label">Kode Pos</label>
                                        <input type="text" name="kode_pos" class="form-control rounded-pill py-2" value="{{ old('kode_pos', $detail->kode_pos) }}">
                                    </div>
                                </div>
                                {{-- Baris 9: Dusun, Kelurahan, Kecamatan (Disesuaikan urutan) --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="dusun" class="form-label">Dusun</label>
                                        <input type="text" name="dusun" class="form-control rounded-pill py-2" value="{{ old('dusun', $detail->dusun) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="kelurahan" class="form-label">Kelurahan</label>
                                        <input type="text" name="kelurahan" class="form-control rounded-pill py-2" value="{{ old('kelurahan', $detail->kelurahan) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="kecamatan" class="form-label">Kecamatan</label>
                                        <input type="text" name="kecamatan" class="form-control rounded-pill py-2" value="{{ old('kecamatan', $detail->kecamatan) }}">
                                    </div>
                                </div>
                                {{-- Baris 10: No. HP, Email, No. Telp --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="no_hp" class="form-label">No. HP</label>
                                        <input type="text" name="no_hp" class="form-control rounded-pill py-2" value="{{ old('no_hp', $detail->no_hp) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control rounded-pill py-2 @error('email') is-invalid @enderror" 
                                               value="{{ old('email', $detail->email) }}">
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="no_telp" class="form-label">No. Telepon (Rumah)</label>
                                        <input type="text" name="no_telp" class="form-control rounded-pill py-2" value="{{ old('no_telp', $detail->no_telp) }}">
                                    </div>
                                </div>
                                {{-- Baris 11: Lintang, Bujur (2 kolom) --}}
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <label for="lintang" class="form-label">Lintang</label>
                                        <input type="text" name="lintang" class="form-control rounded-pill py-2" value="{{ old('lintang', $detail->lintang) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="bujur" class="form-label">Bujur</label>
                                        <input type="text" name="bujur" class="form-control rounded-pill py-2" value="{{ old('bujur', $detail->bujur) }}">
                                    </div>
                                </div>


                                <hr class="my-4">

                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-success">IV. Data Kepegawaian & Gaji</h6>
                                <hr>
                                {{-- Baris 12: Status Kepegawaian, Pangkat/Gol, Sumber Gaji (Data Dasar Kepegawaian) --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="status_kepegawaian" class="form-label">Status Kepegawaian</label>
                                        <input type="text" name="status_kepegawaian" class="form-control rounded-pill py-2" value="{{ old('status_kepegawaian', $detail->status_kepegawaian) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="pangkat_gol" class="form-label">Pangkat/Golongan</label>
                                        <input type="text" name="pangkat_gol" class="form-control rounded-pill py-2" value="{{ old('pangkat_gol', $detail->pangkat_gol) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="sumber_gaji" class="form-label">Sumber Gaji</label>
                                        <input type="text" name="sumber_gaji" class="form-control rounded-pill py-2" value="{{ old('sumber_gaji', $detail->sumber_gaji) }}">
                                    </div>
                                </div>
                                {{-- Baris 13: NPWP, Nama WP, Karpeg (Data Pendukung Gaji/Kepegawaian) --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="npwp" class="form-label">NPWP</label>
                                        <input type="text" name="npwp" class="form-control rounded-pill py-2" value="{{ old('npwp', $detail->npwp) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_wajib_pajak" class="form-label">Nama Wajib Pajak</label>
                                        <input type="text" name="nama_wajib_pajak" class="form-control rounded-pill py-2" value="{{ old('nama_wajib_pajak', $detail->nama_wajib_pajak) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="karpeg" class="form-label">Karpeg</label>
                                        <input type="text" name="karpeg" class="form-control rounded-pill py-2" value="{{ old('karpeg', $detail->karpeg) }}">
                                    </div>
                                </div>
                                {{-- Baris 14: SK CPNS, Tgl CPNS, TMT PNS (Kronologi SK PNS) --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="sk_cpns" class="form-label">SK CPNS</label>
                                        <input type="text" name="sk_cpns" class="form-control rounded-pill py-2" value="{{ old('sk_cpns', $detail->sk_cpns) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tgl_cpns" class="form-label">Tgl CPNS</label>
                                        <input type="date" name="tgl_cpns" class="form-control rounded-pill py-2" value="{{ old('tgl_cpns', $detail->tgl_cpns) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tmt_pns" class="form-label">TMT PNS</label>
                                        <input type="date" name="tmt_pns" class="form-control rounded-pill py-2" value="{{ old('tmt_pns', $detail->tmt_pns) }}">
                                    </div>
                                </div>
                                {{-- Baris 15: SK Pengangkatan, TMT Pengangkatan, Lembaga Pengangkatan --}}
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <label for="sk_pengangkatan" class="form-label">SK Pengangkatan</label>
                                        <input type="text" name="sk_pengangkatan" class="form-control rounded-pill py-2" value="{{ old('sk_pengangkatan', $detail->sk_pengangkatan) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tmt_pengangkatan" class="form-label">TMT Pengangkatan</label>
                                        <input type="date" name="tmt_pengangkatan" class="form-control rounded-pill py-2" value="{{ old('tmt_pengangkatan', $detail->tmt_pengangkatan) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="lembaga_pengangkatan" class="form-label">Lembaga Pengangkatan</label>
                                        <input type="text" name="lembaga_pengangkatan" class="form-control rounded-pill py-2" value="{{ old('lembaga_pengangkatan', $detail->lembaga_pengangkatan) }}">
                                    </div>
                                </div>


                                <hr class="my-4">

                                {{-- ================================================= --}}
                                <h6 class="text-sm font-weight-bolder mb-3 text-warning">V. Tugas & Bank</h6>
                                <hr>

                                <h6 class="text-xs font-weight-bolder text-secondary mb-3">5A. Tugas dan Sertifikasi Khusus</h6>
                                {{-- Baris 16: Tugas Tambahan, NUKS, Karis/Karsu --}}
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="tugas_tambahan" class="form-label">Tugas Tambahan</label>
                                        <input type="text" name="tugas_tambahan" class="form-control rounded-pill py-2" value="{{ old('tugas_tambahan', $detail->tugas_tambahan) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="nuks" class="form-label">NUKS</label>
                                        <input type="text" name="nuks" class="form-control rounded-pill py-2" value="{{ old('nuks', $detail->nuks) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="karis_karsu" class="form-label">Karis/Karsu</label>
                                        <input type="text" name="karis_karsu" class="form-control rounded-pill py-2" value="{{ old('karis_karsu', $detail->karis_karsu) }}">
                                    </div>
                                </div>

                                {{-- Baris 17: Checkbox Status Khusus (d-flex & align-items-center untuk perataan vertikal) --}}
                                <div class="row">
                                    <div class="col-md-4 d-flex align-items-center mb-3">
                                        <div class="form-check form-switch ms-3 my-auto">
                                            <input class="form-check-input" type="checkbox" id="lisensi_kepsek" name="lisensi_kepsek" value="1" {{ old('lisensi_kepsek', $detail->lisensi_kepsek) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="lisensi_kepsek">Memiliki Lisensi Kepsek</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center mb-3">
                                        <div class="form-check form-switch ms-3 my-auto">
                                            <input class="form-check-input" type="checkbox" id="diklat_kepengawasan" name="diklat_kepengawasan" value="1" {{ old('diklat_kepengawasan', $detail->diklat_kepengawasan) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="diklat_kepengawasan">Mengikuti Diklat Kepengawasan</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center mb-3">
                                        <div class="form-check form-switch ms-3 my-auto">
                                            <input class="form-check-input" type="checkbox" id="keahlian_braille" name="keahlian_braille" value="1" {{ old('keahlian_braille', $detail->keahlian_braille) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="keahlian_braille">Keahlian Braille</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 d-flex align-items-center mb-3">
                                        <div class="form-check form-switch ms-3 my-auto">
                                            <input class="form-check-input" type="checkbox" id="keahlian_isyarat" name="keahlian_isyarat" value="1" {{ old('keahlian_isyarat', $detail->keahlian_isyarat) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="keahlian_isyarat">Keahlian Isyarat</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <h6 class="text-xs font-weight-bolder text-secondary mt-4 mb-3">5B. Informasi Bank</h6>
                                {{-- Baris 18: Bank, No. Rekening, Nama Pemilik Rek --}}
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <label for="bank" class="form-label">Nama Bank</label>
                                        <input type="text" name="bank" class="form-control rounded-pill py-2" value="{{ old('bank', $detail->bank) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="norek_bank" class="form-label">Nomor Rekening</label>
                                        <input type="text" name="norek_bank" class="form-control rounded-pill py-2" value="{{ old('norek_bank', $detail->norek_bank) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_rek" class="form-label">Nama Pemilik Rekening</label>
                                        <input type="text" name="nama_rek" class="form-control rounded-pill py-2" value="{{ old('nama_rek', $detail->nama_rek) }}">
                                    </div>
                                </div>

                                <div class="text-end mt-4">
                                    <a href="{{ route('master.guru.show', $guru->id_guru) }}" class="btn btn-outline-secondary me-2">Batal</a>
                                    <button type="submit" class="btn bg-gradient-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
        
    </main>
@endsection