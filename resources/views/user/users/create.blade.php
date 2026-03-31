@extends('layouts.app') 

@section('page-title', 'Tambah Pengguna Baru')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-12">
                    
                    <div class="card my-4 border shadow-xs">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-4"><i class="fas fa-user-plus me-2"></i> Tambah Pengguna Baru</h6>
                                <div class="pe-3">
                                    <a href="{{ route('settings.system.users.index') }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body px-4 pt-4 pb-4">
                            
                            @if ($errors->any())
                                <div class="alert bg-gradient-danger text-white alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert bg-gradient-danger text-white alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('settings.system.users.store') }}" method="POST">
                                @csrf

                                {{-- OPSI PENAUTAN AKUN --}}
                                <div class="p-3 bg-light border rounded mb-4">
                                    <h6 class="text-dark font-weight-bold mb-3"><i class="fas fa-link me-1 text-primary"></i> Penautan Akun</h6>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label font-weight-bold text-secondary text-xs">Pilih Target Akun <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-4 flex-wrap">
                                                <div class="form-check p-0 d-flex align-items-center">
                                                    <input class="form-check-input m-0 border-secondary" type="radio" name="jenis_akun" id="jenis_admin" value="admin" {{ old('jenis_akun', 'admin') == 'admin' ? 'checked' : '' }} onchange="toggleJenisAkun()">
                                                    <label class="form-check-label mb-0 ms-2 cursor-pointer" for="jenis_admin">Admin / Non-Staff (Manual)</label>
                                                </div>
                                                <div class="form-check p-0 d-flex align-items-center">
                                                    <input class="form-check-input m-0 border-secondary" type="radio" name="jenis_akun" id="jenis_guru" value="guru" {{ old('jenis_akun') == 'guru' ? 'checked' : '' }} onchange="toggleJenisAkun()">
                                                    <label class="form-check-label mb-0 ms-2 cursor-pointer" for="jenis_guru">Tautkan ke Data Guru</label>
                                                </div>
                                                <div class="form-check p-0 d-flex align-items-center">
                                                    <input class="form-check-input m-0 border-secondary" type="radio" name="jenis_akun" id="jenis_siswa" value="siswa" {{ old('jenis_akun') == 'siswa' ? 'checked' : '' }} onchange="toggleJenisAkun()">
                                                    <label class="form-check-label mb-0 ms-2 cursor-pointer" for="jenis_siswa">Tautkan ke Data Siswa</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Dropdown Guru --}}
                                    <div class="row mb-2 {{ old('jenis_akun') == 'guru' ? '' : 'd-none' }}" id="section_guru">
                                        <div class="col-md-12">
                                            <label class="form-label font-weight-bold text-secondary text-xs">Pilih Guru (Yang Belum Punya Akun) <span class="text-danger">*</span></label>
                                            <select name="id_guru" id="select_guru" class="form-select border-secondary px-3 bg-white" style="height: 40px;" onchange="fillName(this)">
                                                <option value="" data-nama="">-- Pilih Guru --</option>
                                                @foreach($gurus as $g)
                                                    <option value="{{ $g->id_guru }}" data-nama="{{ $g->nama_guru }}" {{ old('id_guru') == $g->id_guru ? 'selected' : '' }}>
                                                        {{ $g->nama_guru }} (NIP: {{ $g->nip ?? '-' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Dropdown Siswa --}}
                                    <div class="row mb-2 {{ old('jenis_akun') == 'siswa' ? '' : 'd-none' }}" id="section_siswa">
                                        <div class="col-md-12">
                                            <label class="form-label font-weight-bold text-secondary text-xs">Pilih Siswa (Yang Belum Punya Akun) <span class="text-danger">*</span></label>
                                            <select name="id_siswa" id="select_siswa" class="form-select border-secondary px-3 bg-white" style="height: 40px;" onchange="fillName(this)">
                                                <option value="" data-nama="">-- Pilih Siswa --</option>
                                                @foreach($siswas as $s)
                                                    <option value="{{ $s->id_siswa }}" data-nama="{{ $s->nama_siswa }}" {{ old('id_siswa') == $s->id_siswa ? 'selected' : '' }}>
                                                        {{ $s->nama_siswa }} - {{ $s->kelas->nama_kelas ?? 'Tanpa Kelas' }} (NISN: {{ $s->nisn ?? '-' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr class="horizontal dark my-4">

                                {{-- FORM UTAMA --}}
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="name" class="form-label font-weight-bold text-secondary text-xs">Nama Lengkap Pengguna <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control border px-3" style="height: 40px;" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required>
                                        <small class="text-xs text-muted" id="name_helper">Nama akan otomatis terisi jika Anda memilih Guru/Siswa di atas.</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label font-weight-bold text-secondary text-xs">Alamat Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control border px-3" style="height: 40px;" id="email" name="email" value="{{ old('email') }}" placeholder="Contoh: budi@sekolah.com" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="password" class="form-label font-weight-bold text-secondary text-xs">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control border px-3" style="height: 40px;" id="password" name="password" placeholder="Minimal 8 karakter" required>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="role_name" class="form-label font-weight-bold text-secondary text-xs">Hak Akses (Role) <span class="text-danger">*</span></label>
                                        <select name="role_name" id="role_name" class="form-select border px-3" style="height: 40px;" required>
                                            <option value="">-- Pilih Hak Akses --</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ old('role_name') == $role->name ? 'selected' : '' }}>
                                                    {{ \Illuminate\Support\Str::title($role->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check form-switch d-flex align-items-center px-0">
                                            <input class="form-check-input ms-0 me-3" type="checkbox" id="is_active" name="is_active" checked>
                                            <label class="form-check-label mb-0 font-weight-bold text-secondary text-sm" for="is_active">Aktifkan Akun Ini Langsung</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn bg-gradient-primary">
                                        <i class="fas fa-save me-1"></i> Simpan Pengguna Baru
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
    </main>

    <script>
        function toggleJenisAkun() {
            let radioAdmin = document.getElementById('jenis_admin');
            let radioGuru = document.getElementById('jenis_guru');
            let radioSiswa = document.getElementById('jenis_siswa');
            
            let secGuru = document.getElementById('section_guru');
            let secSiswa = document.getElementById('section_siswa');
            
            let selectGuru = document.getElementById('select_guru');
            let selectSiswa = document.getElementById('select_siswa');
            let inputName = document.getElementById('name');
            let helperName = document.getElementById('name_helper');

            // Reset Select
            selectGuru.value = "";
            selectSiswa.value = "";

            if (radioAdmin.checked) {
                secGuru.classList.add('d-none');
                secSiswa.classList.add('d-none');
                
                inputName.value = "";
                inputName.readOnly = false;
                inputName.classList.remove('bg-light');
                helperName.innerText = "Silakan ketik nama lengkap pengguna secara manual.";
                
                // Matikan required di select
                selectGuru.removeAttribute('required');
                selectSiswa.removeAttribute('required');
            } 
            else if (radioGuru.checked) {
                secGuru.classList.remove('d-none');
                secSiswa.classList.add('d-none');
                
                inputName.value = "";
                inputName.readOnly = true; // Kunci agar tidak salah ketik
                inputName.classList.add('bg-light');
                helperName.innerText = "Nama akan otomatis mengikuti data Guru yang dipilih.";
                
                // Nyalakan required
                selectGuru.setAttribute('required', 'required');
                selectSiswa.removeAttribute('required');
            } 
            else if (radioSiswa.checked) {
                secGuru.classList.add('d-none');
                secSiswa.classList.remove('d-none');
                
                inputName.value = "";
                inputName.readOnly = true; // Kunci agar tidak salah ketik
                inputName.classList.add('bg-light');
                helperName.innerText = "Nama akan otomatis mengikuti data Siswa yang dipilih.";
                
                // Nyalakan required
                selectSiswa.setAttribute('required', 'required');
                selectGuru.removeAttribute('required');
            }
        }

        function fillName(selectElement) {
            // Mengambil data-nama dari option yang sedang dipilih
            let selectedOption = selectElement.options[selectElement.selectedIndex];
            let nama = selectedOption.getAttribute('data-nama');
            
            if (nama) {
                document.getElementById('name').value = nama;
            } else {
                document.getElementById('name').value = "";
            }
        }
    </script>
@endsection