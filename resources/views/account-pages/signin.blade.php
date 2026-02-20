<x-app-layout>

    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-md-6 d-flex flex-column mx-auto">
                            <div class="card card-plain mt-8">
                                <div class="card-header pb-0 text-left bg-transparent">
                                    <h3 class="font-weight-black text-dark display-6">Selamat Datang</h3>
                                    <p class="mb-0">Silakan masukkan NIP/NISN dan Password.</p>
                                </div>
                                <div class="card-body">
                                    
                                    {{-- Tampilkan Error Jika Login Gagal --}}
                                    @if($errors->any())
                                        <div class="alert alert-danger text-dark text-sm mb-3">
                                            {{ $errors->first('message') ?: $errors->first() }}
                                        </div>
                                    @endif

                                    {{-- Tampilkan Sukses Jika Habis Daftar --}}
                                    @if(session('success'))
                                        <div class="alert alert-success text-dark text-sm mb-3">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    {{-- FORM LOGIN --}}
                                    <form role="form" method="POST" action="{{ route('signin') }}">
                                        @csrf
                                        
                                        {{-- Input Username (NIP/NISN) --}}
                                        <label>NIP / NISN</label>
                                        <div class="mb-3">
                                            <input type="text" name="username" class="form-control" 
                                                placeholder="Masukkan NIP atau NISN" 
                                                value="{{ old('username') }}" 
                                                required autofocus>
                                        </div>
                                        
                                        {{-- Input Password --}}
                                        <label>Password</label>
                                        <div class="mb-3">
                                            <input type="password" name="password" class="form-control" 
                                                placeholder="Masukkan Password" 
                                                required>
                                        </div>
                                        
                                        {{-- Remember Me --}}
                                        <div class="d-flex align-items-center">
                                            <div class="form-check form-check-info text-left mb-0">
                                                <input class="form-check-input" type="checkbox" name="rememberMe" id="flexCheckDefault">
                                                <label class="font-weight-normal text-dark mb-0" for="flexCheckDefault">
                                                    Ingat Saya
                                                </label>
                                            </div>
                                            <a href="{{ route('password.request') }}" class="text-xs font-weight-bold ms-auto">
                                                Lupa Password?
                                            </a>
                                        </div>

                                        {{-- Tombol Login --}}
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-dark w-100 mt-4 mb-3">Masuk</button>
                                        </div>
                                    </form>
                                </div>

                                {{-- FOOTER CARD: Link ke Register --}}
                                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                    <p class="mb-4 text-xs mx-auto">
                                        Belum punya akun?
                                        <a href="{{ route('sign-up') }}" class="text-dark font-weight-bold">Daftar Akun</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Gambar Samping --}}
                        <div class="col-md-6">
                            <div class="position-absolute w-40 top-0 end-0 h-100 d-md-block d-none">
                                <div class="oblique-image position-absolute fixed-top ms-auto h-100 z-index-0 bg-cover ms-n8"
                                    style="background-image:url('../assets/img/image-sign-in.jpg')">
                                    <div class="blur mt-12 p-4 text-center border border-white border-radius-md position-absolute fixed-bottom m-4">
                                        <h2 class="mt-3 text-dark font-weight-bold">E-Rapor System</h2>
                                        <p class="text-dark text-sm mt-3">Platform Akademik Terintegrasi Sekolah.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

</x-app-layout>