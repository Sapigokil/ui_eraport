<x-app-layout>
    {{-- Background Section --}}
    <div class="page-header align-items-start min-vh-100" 
         style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80');">
        <span class="mask bg-gradient-dark opacity-6"></span>
        
        <div class="container my-auto">
            <div class="row">
                <div class="col-lg-6 col-md-8 col-12 mx-auto">
                    <div class="card z-index-0 fadeIn3 fadeInBottom shadow-lg">
                        
                        {{-- Header Card --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-info shadow-info border-radius-lg py-3 pe-1">
                                <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Aktivasi Akun E-Rapor</h4>
                                <p class="text-white text-sm text-center px-3">
                                    Verifikasi data NIP/NISN Anda untuk memulai
                                </p>
                            </div>
                        </div>

                        <div class="card-body">
                            {{-- Alert Error Global --}}
                            @if($errors->has('msg'))
                                <div class="alert alert-warning text-dark text-sm mb-3" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i> {{ $errors->first('msg') }}
                                </div>
                            @endif

                            <form role="form" class="text-start" method="POST" action="{{ route('sign-up.store') }}">
                                @csrf
                                
                                {{-- SECTION 1: VERIFIKASI IDENTITAS --}}
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">1. Verifikasi Identitas</h6>
                                
                                <div class="row">
                                    {{-- Tipe Akun --}}
                                    <div class="col-md-4 mb-3">
                                        <div class="input-group input-group-outline filled">
                                            <label class="form-label">Saya adalah</label>
                                            <select name="tipe_akun" class="form-control" style="appearance: auto;" required>
                                                <option value="siswa" {{ old('tipe_akun') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                                <option value="guru" {{ old('tipe_akun') == 'guru' ? 'selected' : '' }}>Guru</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Nomor Induk --}}
                                    <div class="col-md-8 mb-3">
                                        <div class="input-group input-group-outline mb-1">
                                            <label class="form-label">Nomor Induk (NIP / NISN)</label>
                                            <input type="text" name="nomor_induk" class="form-control" value="{{ old('nomor_induk') }}" required>
                                        </div>
                                        @error('nomor_induk') 
                                            <span class="text-danger text-xs ms-1"><i class="fas fa-times-circle"></i> {{ $message }}</span> 
                                        @enderror
                                    </div>
                                </div>

                                {{-- Tanggal Lahir (Kunci Keamanan) --}}
                                <div class="mb-3">
                                    <label class="form-label text-xs mb-1 ms-1 font-weight-bold">Tanggal Lahir (Wajib sesuai data sekolah)</label>
                                    <div class="input-group input-group-outline">
                                        <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}" required>
                                    </div>
                                    @error('tanggal_lahir') 
                                        <span class="text-danger text-xs ms-1"><i class="fas fa-times-circle"></i> {{ $message }}</span> 
                                    @enderror
                                </div>

                                <hr class="horizontal dark my-3">

                                {{-- SECTION 2: BUAT AKUN --}}
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">2. Buat Kredensial Login</h6>

                                <div class="mb-3">
                                    <div class="input-group input-group-outline mb-1">
                                        <label class="form-label">Email Aktif</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                    </div>
                                    @error('email') 
                                        <span class="text-danger text-xs ms-1">{{ $message }}</span> 
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="input-group input-group-outline mb-1">
                                            <label class="form-label">Password Baru</label>
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                        @error('password') 
                                            <span class="text-danger text-xs ms-1">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">Ulangi Password</label>
                                            <input type="password" name="password_confirmation" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                {{-- Checkbox Terms --}}
                                <div class="form-check form-check-info text-start ps-0">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" checked required>
                                    <label class="form-check-label" for="flexCheckDefault">
                                        Saya setuju dengan <a href="javascript:;" class="text-dark font-weight-bolder">Syarat & Ketentuan</a>
                                    </label>
                                </div>

                                {{-- Submit Button --}}
                                <div class="text-center">
                                    <button type="submit" class="btn bg-gradient-info w-100 my-4 mb-2">Daftar & Aktivasi Akun</button>
                                </div>

                                <p class="mt-4 text-sm text-center">
                                    Sudah punya akun?
                                    <a href="{{ route('login') }}" class="text-info text-gradient font-weight-bold">Masuk disini</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Footer Simple --}}
        <footer class="footer position-absolute bottom-2 py-2 w-100">
            <div class="container">
                <div class="row align-items-center justify-content-lg-between">
                    <div class="col-12 col-md-6 my-auto">
                        <div class="copyright text-center text-sm text-white text-lg-start">
                            © <script>document.write(new Date().getFullYear())</script>, E-Rapor System
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</x-app-layout>