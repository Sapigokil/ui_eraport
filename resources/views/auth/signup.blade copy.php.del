<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Daftar Akun | E-Rapor</title>
    
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Noto+Sans:300,400,500,600,700,800" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link id="pagestyle" href="{{ asset('assets/css/corporate-ui-dashboard.css') }}" rel="stylesheet" />
    
    <style>
        /* Class bantuan untuk menyembunyikan form update di awal */
        .d-none-custom { display: none !important; }
    </style>
</head>

<body class="bg-gray-100">
    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 d-flex flex-column mx-auto">
                            
                            <div class="card card-plain mt-5">
                                <div class="card-header pb-0 text-left bg-transparent">
                                    <h3 class="font-weight-black text-dark display-6">Aktivasi Akun</h3>
                                </div>
                                
                                <div class="card-body">

                                    {{-- =========================== --}}
                                    {{-- STEP 3: SUKSES REGISTRASI/UPDATE --}}
                                    {{-- =========================== --}}
                                    @if(session('success_register'))
                                        <div class="text-center mb-4">
                                            <div class="icon icon-shape icon-lg bg-gradient-success shadow text-center border-radius-lg mb-3 mx-auto">
                                                <i class="fas fa-check fa-2x mt-2"></i>
                                            </div>
                                            <h4 class="text-gradient text-success mt-2">Berhasil!</h4>
                                            <p class="text-sm">{{ session('success_register') }}</p>
                                        </div>
                                        <div class="text-center">
                                            <a href="{{ route('sign-in') }}" class="btn btn-dark w-100 mt-2">Masuk ke Aplikasi</a>
                                        </div>

                                    @else
                                        
                                        {{-- ERROR GLOBAL (Pencarian tidak ketemu) --}}
                                        @if($errors->has('msg'))
                                            <div class="alert alert-danger text-sm mb-3">
                                                <i class="fas fa-times-circle me-2"></i> {{ $errors->first('msg') }}
                                            </div>
                                        @endif

                                        {{-- =========================== --}}
                                        {{-- STEP 2: LOGIC FORM --}}
                                        {{-- =========================== --}}
                                        @if(isset($step_dua) && $step_dua == true)
                                            
                                            {{-- A. JIKA USER DUPLIKAT (Reset Password Flow) --}}
                                            @if(isset($is_duplicate) && $is_duplicate == true)
                                                
                                                {{-- 1. ALERT DUPLIKAT (Muncul Awal) --}}
                                                <div id="duplicateAlertBlock">
                                                    {{-- Style mengikuti preferensi Anda (text-dark) --}}
                                                    <div class="alert alert-warning text-sm mb-3">
                                                        <strong>Akun ditemukan!</strong><br>
                                                        Data atas nama <b>{{ $data_ditemukan['nama'] }}</b> sudah terdaftar. 
                                                        Klik tombol di bawah untuk mereset password atau memperbarui data akun.
                                                    </div>
                                                    <div class="text-center">
                                                        {{-- TOMBOL TRIGGER JS --}}
                                                        <button type="button" onclick="showUpdateForm()" class="btn btn-outline-dark w-100">
                                                            Reset Password
                                                        </button>
                                                        <a href="{{ route('sign-up') }}" class="btn btn-link text-dark text-sm mt-2">Kembali</a>
                                                    </div>
                                                </div>

                                                {{-- 2. FORM UPDATE (Tersembunyi Awalnya via class d-none-custom) --}}
                                                <div id="updateFormBlock" class="d-none-custom">
                                                    <div class="alert alert-info text-sm mb-4">
                                                        <strong>Mode Reset Akun</strong><br>
                                                        Masukkan Username, Email, dan Password baru untuk akun <b>{{ $data_ditemukan['nama'] }}</b>.
                                                    </div>
                                                    
                                                    {{-- FORM PUT UNTUK UPDATE --}}
                                                    <form role="form" method="POST" action="{{ route('sign-up.update') }}">
                                                        @csrf
                                                        @method('PUT') {{-- Penting: Method PUT untuk Update --}}

                                                        <input type="hidden" name="name" value="{{ $data_ditemukan['nama'] }}">
                                                        <input type="hidden" name="tipe_akun" value="{{ $data_ditemukan['role'] }}">
                                                        <input type="hidden" name="id_ref" value="{{ $data_ditemukan['id_ref'] }}">
                                                        
                                                        {{-- Input Fields --}}
                                                        <div class="mb-3">
                                                            <label class="form-label">Username Baru</label>
                                                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" minlength="8" required>
                                                            <small class="text-muted text-xs">Minimal 8 karakter</small>
                                                            @error('username') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Email Baru</label>
                                                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                                            @error('email') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Password Baru</label>
                                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" required>
                                                            <small class="text-muted text-xs">Minimal 8 karakter</small>
                                                            @error('password') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                                                        </div>

                                                        <div class="text-center">
                                                            <button type="submit" class="btn btn-dark w-100 mt-3">Simpan Perubahan</button>
                                                            <a href="{{ route('sign-up') }}" class="btn btn-link text-dark text-sm mt-2">Batal</a>
                                                        </div>
                                                    </form>
                                                </div>

                                            @else
                                                {{-- B. JIKA USER BARU (REGISTRASI NORMAL) --}}
                                                <div class="alert alert-success text-sm mb-4">
                                                    <strong>Selamat datang {{ $data_ditemukan['nama'] }}</strong>, silahkan lengkapi data berikut.
                                                </div>

                                                <form role="form" method="POST" action="{{ route('sign-up.store') }}">
                                                    @csrf
                                                    
                                                    <input type="hidden" name="name" value="{{ $data_ditemukan['nama'] }}">
                                                    <input type="hidden" name="tipe_akun" value="{{ $data_ditemukan['role'] }}">
                                                    <input type="hidden" name="id_ref" value="{{ $data_ditemukan['id_ref'] }}">
                                                    <input type="hidden" name="nomor_induk" value="{{ $old_input['nomor_induk'] ?? '' }}">

                                                    <div class="mb-3">
                                                        <label class="form-label">Username</label>
                                                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Buat Username Unik" value="{{ old('username') }}" minlength="8" required>
                                                        <small class="text-muted text-xs">Minimal 8 karakter</small>
                                                        @error('username') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@contoh.com" value="{{ old('email') }}" required>
                                                        @error('email') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Password</label>
                                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="********" minlength="8" required>
                                                        <small class="text-muted text-xs">Minimal 8 karakter</small>
                                                        @error('password') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                                                    </div>

                                                    <div class="text-center">
                                                        <button type="submit" class="btn btn-dark w-100 mt-3">Proses Registrasi</button>
                                                        <a href="{{ route('sign-up') }}" class="btn btn-link text-dark text-sm mt-2">Batal</a>
                                                    </div>
                                                </form>

                                            @endif {{-- End check duplicate --}}

                                        @else
                                            {{-- =========================== --}}
                                            {{-- STEP 1: FORM PENCARIAN --}}
                                            {{-- =========================== --}}
                                            <p class="mb-3">Masukkan NIP/NISN dan Tanggal Lahir untuk verifikasi.</p>

                                            <form role="form" method="POST" action="{{ route('sign-up.check') }}">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">Tipe Akun <span class="badge bg-gradient-secondary text-xxs ms-2">Terbatas</span></label>
                                                    
                                                    {{-- ======================================================= --}}
                                                    {{-- [TEMPORARY LOCK] --}}
                                                    {{-- Opsi dropdown dikunci ke Guru dengan pointer-events: none --}}
                                                    {{-- Hapus style tersebut & hapus komentar Blade di opsi siswa --}}
                                                    {{-- untuk mengembalikan ke fungsi aslinya. --}}
                                                    {{-- ======================================================= --}}
                                                    <select name="tipe_akun" class="form-control form-select" required style="pointer-events: none; background-color: #e9ecef; color: #6c757d;">
                                                        <option value="guru" selected>Guru</option>
                                                        
                                                        {{-- KODE ASLI YANG DISEMBUNYIKAN: --}}
                                                        {{-- <option value="siswa" {{ old('tipe_akun') == 'siswa' ? 'selected' : '' }}>Siswa</option> --}}
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nomor Induk (NIP/NUPTK/NIS/NISN)</label>
                                                    <input type="text" name="nomor_induk" class="form-control @error('nomor_induk') is-invalid @enderror" value="{{ old('nomor_induk') }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Tanggal Lahir</label>
                                                    <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir') }}" required>
                                                </div>
                                                <div class="text-center">
                                                    <button type="submit" class="btn btn-dark w-100 mt-4 mb-3">Cari Data</button>
                                                </div>
                                            </form>
                                            <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                                <p class="mb-4 text-xs mx-auto">
                                                    Sudah punya akun? <a href="{{ route('sign-in') }}" class="text-dark font-weight-bold">Masuk disini</a>
                                                </p>
                                            </div>
                                        @endif 
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- Script untuk memunculkan Form Update --}}
    <script>
        function showUpdateForm() {
            // Sembunyikan Alert Peringatan
            document.getElementById('duplicateAlertBlock').classList.add('d-none-custom');
            // Tampilkan Form Input Update
            document.getElementById('updateFormBlock').classList.remove('d-none-custom');
        }
    </script>

    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/corporate-ui-dashboard.min.js') }}"></script>
</body>
</html>