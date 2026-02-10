<x-guest-layout>
    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-100">
                
                {{-- 1. BACKGROUND MODERN (Ringan, Tanpa Gambar Besar) --}}
                <div class="position-absolute top-0 start-0 w-100 h-100" 
                     style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); z-index: -1;">
                     {{-- Opsi lain: Background Mesh Gradient (lihat CSS di bawah) --}}
                </div>

                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-auto">
                            
                            {{-- 2. CARD LOGIN (Dibuat Pop-up dengan Shadow) --}}
                            <div class="card shadow-lg mt-5 border-0 rounded-3">
                                <div class="card-header pb-0 text-center bg-white border-0 pt-4">
                                    {{-- Ikon atau Logo Kecil (Opsional) --}}
                                    <div class="mb-3">
                                        <i class="fas fa-school fa-3x text-dark"></i>
                                    </div>
                                    <h4 class="font-weight-bold text-dark">E-Rapor SMK</h4>
                                    <p class="mb-0 text-secondary text-sm">Masuk untuk melanjutkan</p>
                                </div>

                                <div class="card-body">
                                    {{-- Alert Status / Error --}}
                                    @if (session('status'))
                                        <div class="alert alert-success text-white text-sm p-2 mb-3" role="alert">
                                            {{ session('status') }}
                                        </div>
                                    @endif
                                    @error('message')
                                        <div class="alert alert-danger text-white text-sm p-2 mb-3" role="alert">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <form role="form" method="POST" action="sign-in">
                                        @csrf
                                        
                                        <div class="mb-3">
                                            <label class="form-label text-xs font-weight-bold text-uppercase text-secondary">Email / Username</label>
                                            <input type="text" 
                                                   id="login" 
                                                   name="login" 
                                                   class="form-control form-control-lg ps-3"
                                                   placeholder="Ketik username anda"
                                                   value="{{ old('login') }}"
                                                   required autofocus>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="form-label text-xs font-weight-bold text-uppercase text-secondary">Password</label>
                                            </div>
                                            <input type="password" 
                                                   id="password" 
                                                   name="password"
                                                   class="form-control form-control-lg ps-3" 
                                                   placeholder="Ketik password"
                                                   required>
                                        </div>

                                        {{-- Tombol Login --}}
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-dark btn-lg w-100 mt-4 mb-0">Masuk Aplikasi</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="card-footer text-center pt-0 px-lg-2 px-1 bg-white border-0 pb-4">
                                    <p class="mb-4 text-sm mx-auto text-secondary">
                                        Belum punya akun?
                                        <a href="{{ route('sign-up') }}" class="text-dark font-weight-bold text-decoration-none">Daftar Sekarang</a>
                                    </p>
                                </div>
                            </div>
                            
                            {{-- Footer Copyright Kecil --}}
                            <div class="text-center mt-3 text-sm text-secondary">
                                &copy; {{ date('Y') }} E-Rapor SMK by 
                                <a href="https://campus.web.id" class="text-secondary font-weight-bold text-decoration-none" target="_blank">
                                    CampusCreative
                                </a>. 
                                All rights reserved.
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-guest-layout>