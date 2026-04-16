@extends('layouts.app')

@section('page-title', 'Form Proses Mutasi Siswa')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    {{-- CSS Custom untuk Scrollbar Area Kelas & Siswa --}}
    <style>
        .scrollable-area {
            max-height: 550px;
            overflow-y: auto;
        }
        /* Custom Scrollbar agar rapi */
        .scrollable-area::-webkit-scrollbar {
            width: 6px;
        }
        .scrollable-area::-webkit-scrollbar-track {
            background: #f1f1f1; 
            border-radius: 4px;
        }
        .scrollable-area::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 4px;
        }
        .scrollable-area::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
        
        /* Efek klik kelas */
        .class-item {
            cursor: pointer;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        .class-item:hover {
            background-color: #f8f9fa;
            border-left: 4px solid #cb0c9f;
        }
        .class-item.active {
            background-image: linear-gradient(310deg, #7928CA 0%, #FF0080 100%);
            color: white !important;
            border-left: 4px solid #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .class-item.active .text-secondary {
            color: #e2e8f0 !important;
        }
    </style>

    <div class="container-fluid py-4 px-5">
        
        {{-- Header & Tombol Kembali --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="text-dark font-weight-bolder mb-0">Proses Mutasi Siswa Keluar</h5>
                <p class="text-sm text-secondary mb-0">Pilih kelas di sebelah kiri, lalu pilih siswa yang akan dimutasi.</p>
            </div>
            <a href="{{ route('mutasi.keluar.index') }}" class="btn btn-sm btn-white text-dark mb-0 shadow-sm border">
                <i class="fas fa-arrow-left me-1"></i> Batal & Kembali
            </a>
        </div>

        {{-- Tampil Error Validasi dari Controller --}}
        @if ($errors->any())
            <div class="alert bg-gradient-danger text-white text-sm mb-4 shadow-sm" role="alert">
                <strong>Gagal Menyimpan!</strong> Periksa kembali isian Anda:
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- LAYOUT 2 KOLOM --}}
        <div class="row">
            
            {{-- KOLOM KIRI: DAFTAR KELAS --}}
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light border-bottom p-3">
                        <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-chalkboard text-primary me-2"></i> 1. Daftar Kelas</h6>
                    </div>
                    <div class="card-body p-0 scrollable-area">
                        <ul class="list-group list-group-flush" id="listKelas">
                            @if(isset($kelas) && count($kelas) > 0)
                                @foreach($kelas as $k)
                                    <li class="list-group-item class-item p-3 border-bottom" data-id="{{ $k->id_kelas }}">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape icon-sm bg-gradient-primary shadow text-center border-radius-sm me-3">
                                                <i class="fas fa-users text-white text-xs opacity-10"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-sm font-weight-bold class-name">{{ $k->nama_kelas }}</h6>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <div class="text-center py-4 text-sm text-secondary">Data kelas tidak tersedia.</div>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: DAFTAR SISWA --}}
            <div class="col-lg-8 col-md-7 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-user-graduate text-info me-2"></i> 2. Daftar Siswa Aktif</h6>
                        <span id="labelKelasTerpilih" class="badge bg-gradient-secondary">-</span>
                    </div>
                    <div class="card-body p-0 scrollable-area" id="areaSiswa">
                        
                        {{-- Placeholder awal sebelum kelas di-klik --}}
                        <div class="text-center py-7" id="placeholderSiswa">
                            <i class="fas fa-hand-point-left fa-3x text-secondary mb-3 opacity-3"></i>
                            <h6 class="text-secondary font-weight-normal">Silakan pilih kelas di panel sebelah kiri.</h6>
                        </div>

                        {{-- Tempat Data Siswa (Di-inject via JS) --}}
                        <ul class="list-group list-group-flush d-none" id="listSiswa">
                            </ul>

                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <x-app.footer />

    {{-- ========================================== --}}
    {{-- MODAL FORM MUTASI (MUNCUL SAAT KLIK SISWA) --}}
    {{-- ========================================== --}}
    <div class="modal fade" id="modalMutasi" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gray-100">
                    <h6 class="modal-title font-weight-bolder text-dark">
                        <i class="fas fa-sign-out-alt text-danger me-2"></i> Formulir Proses Mutasi
                    </h6>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('mutasi.keluar.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        
                        <div class="alert bg-gradient-warning text-white text-xs mb-4 shadow-sm" style="border:none;" role="alert">
                            <strong>Peringatan!</strong> Siswa akan dikeluarkan dari kelas saat ini. Data akademik akan tetap tersimpan sebagai arsip.
                        </div>

                        {{-- Input Hidden ID Siswa --}}
                        <input type="hidden" name="id_siswa" id="modal_id_siswa">
                        
                        {{-- NAMA SISWA READONLY --}}
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase text-dark">Nama Siswa</label>
                            <div class="input-group input-group-outline is-filled">
                                <input type="text" class="form-control bg-gray-100 font-weight-bold text-primary" id="modal_nama_siswa" readonly>
                            </div>
                        </div>

                        <div class="row">
                            {{-- TANGGAL MUTASI --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-dark">Tgl Mutasi <span class="text-danger">*</span></label>
                                <div class="input-group input-group-outline is-filled">
                                    <input type="date" name="tgl_mutasi" class="form-control" required value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            {{-- JENIS MUTASI --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-xs font-weight-bold text-uppercase text-dark">Jenis Mutasi <span class="text-danger">*</span></label>
                                <select name="jenis_mutasi" class="form-control form-select bg-white border px-3" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Pindah Sekolah">Pindah Sekolah</option>
                                    <option value="Mengundurkan Diri">Mengundurkan Diri</option>
                                    <option value="Putus Sekolah">Putus Sekolah</option>
                                    <option value="Meninggal Dunia">Meninggal Dunia</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        {{-- SEKOLAH TUJUAN --}}
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase text-dark">Sekolah Tujuan <span class="text-lowercase text-muted">(Opsional)</span></label>
                            <div class="input-group input-group-outline">
                                <input type="text" name="sekolah_tujuan" class="form-control" placeholder="Contoh: SMAN 1 Jakarta">
                            </div>
                        </div>

                        {{-- ALASAN / CATATAN --}}
                        <div class="mb-2">
                            <label class="form-label text-xs font-weight-bold text-uppercase text-dark">Catatan / Alasan <span class="text-danger">*</span></label>
                            <div class="input-group input-group-outline">
                                <textarea name="alasan" class="form-control" rows="3" required placeholder="Tulis alasan rinci..."></textarea>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-gray-50">
                        <button type="button" class="btn btn-sm btn-white mb-0 shadow-sm border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm bg-gradient-danger mb-0 shadow-sm">Simpan Mutasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPT AJAX & LOGIKA UI --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            const classItems = document.querySelectorAll('.class-item');
            const placeholderSiswa = document.getElementById('placeholderSiswa');
            const listSiswa = document.getElementById('listSiswa');
            const labelKelasTerpilih = document.getElementById('labelKelasTerpilih');

            // EVENT: Saat Kelas Di-klik
            classItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    
                    // 1. Ganti state aktif pada kelas (Warna UI)
                    classItems.forEach(el => el.classList.remove('active'));
                    this.classList.add('active');

                    // Ambil ID dan Nama kelas yang di klik
                    const idKelas = this.getAttribute('data-id');
                    const namaKelas = this.querySelector('.class-name').innerText;
                    
                    // Update label di header kanan
                    labelKelasTerpilih.innerText = "Kelas " + namaKelas;
                    labelKelasTerpilih.classList.replace('bg-gradient-secondary', 'bg-gradient-info');

                    // 2. Ubah area kanan menjadi loading
                    placeholderSiswa.classList.remove('d-none');
                    listSiswa.classList.add('d-none');
                    placeholderSiswa.innerHTML = `
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h6 class="text-secondary font-weight-normal">Memuat data siswa kelas ${namaKelas}...</h6>
                    `;

                    // 3. Fetch AJAX Data Siswa
                    let urlAjax = "{{ route('mutasi.keluar.get_siswa', ':id') }}";
                    urlAjax = urlAjax.replace(':id', idKelas);

                    fetch(urlAjax)
                        .then(response => {
                            if (!response.ok) throw new Error('Network Error');
                            return response.json();
                        })
                        .then(res => {
                            // Sembunyikan placeholder/loading
                            placeholderSiswa.classList.add('d-none');
                            listSiswa.classList.remove('d-none');
                            listSiswa.innerHTML = ''; // Bersihkan list sebelumnya

                            // 4. Render HTML Siswa
                            if (res.data && res.data.length > 0) {
                                res.data.forEach(siswa => {
                                    
                                    // Handle null values
                                    const nisnStr = siswa.nisn ? siswa.nisn : 'Belum diisi';
                                    const nipdStr = siswa.nipd ? siswa.nipd : 'Belum diisi';

                                    const li = document.createElement('li');
                                    li.className = 'list-group-item px-4 py-3 border-bottom d-flex justify-content-between align-items-center';
                                    li.innerHTML = `
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-gray-200 text-dark rounded-circle me-3">
                                                ${siswa.nama_siswa.charAt(0)}
                                            </div>
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">${siswa.nama_siswa}</h6>
                                                <span class="text-xs text-secondary">NISN: ${nisnStr} &nbsp;|&nbsp; NIPD: ${nipdStr}</span>
                                            </div>
                                        </div>
                                        <button class="btn btn-sm bg-gradient-danger mb-0 shadow-sm btn-mutasi" 
                                            data-id="${siswa.id_siswa}" 
                                            data-nama="${siswa.nama_siswa}"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalMutasi">
                                            Proses Mutasi
                                        </button>
                                    `;
                                    listSiswa.appendChild(li);
                                });
                            } else {
                                // Jika kelas kosong
                                placeholderSiswa.classList.remove('d-none');
                                listSiswa.classList.add('d-none');
                                placeholderSiswa.innerHTML = `
                                    <i class="fas fa-users-slash fa-3x text-secondary mb-3 opacity-3"></i>
                                    <h6 class="text-secondary font-weight-normal">Tidak ada siswa aktif di kelas ini.</h6>
                                `;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                            placeholderSiswa.innerHTML = `
                                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3 opacity-7"></i>
                                <h6 class="text-danger font-weight-normal">Gagal terhubung ke server.</h6>
                            `;
                        });
                });
            });

            // EVENT DELEGATION: Tangkap klik pada tombol "Proses Mutasi" yang di-generate oleh JS
            // Kita tidak bisa pakai click listener biasa karena tombol ini baru muncul belakangan.
            document.addEventListener('click', function(e) {
                if(e.target && e.target.closest('.btn-mutasi')) {
                    const btn = e.target.closest('.btn-mutasi');
                    const idSiswa = btn.getAttribute('data-id');
                    const namaSiswa = btn.getAttribute('data-nama');

                    // Masukkan ke Modal
                    document.getElementById('modal_id_siswa').value = idSiswa;
                    document.getElementById('modal_nama_siswa').value = namaSiswa;
                }
            });

        });
    </script>
</main>
@endsection