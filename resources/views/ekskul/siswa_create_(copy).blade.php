{{-- File: resources/views/ekskul/siswa_create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah Peserta Ekskul')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

        <x-app.navbar />

        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 shadow-xs border">
                        {{-- KONTROL ATAS: HEADER --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-user-plus me-2"></i> Tambah Peserta Ekstrakurikuler</h6>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            {{-- NOTIFIKASI ERRORS --}}
                            @if ($errors->any())
                                <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white fade show" role="alert">
                                    <span class="text-sm">
                                        Data gagal disimpan karena ada kesalahan input:
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <form action="{{ route('master.ekskul.siswa.store') }}" method="POST" class="p-4">
                                @csrf

                                {{-- 🛑 SOLUSI: Hidden Field Sentinel. Memastikan array siswa_ids selalu terkirim, 
                                     agar validasi 'required' tidak gagal karena field hilang. --}}
                                <input type="hidden" name="siswa_ids[]" value="">

                                <input type="hidden" name="id_kelas_filter" id="id_kelas_filter" value="">

                                {{-- Field 1: Pilih Ekskul (REQUIRED) --}}
                                <div class="mb-4">
                                    <label for="id_ekskul" class="form-label">Pilih Ekstrakurikuler <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_ekskul" name="id_ekskul" required>
                                        <option value="" disabled selected>-- Pilih Ekstrakurikuler --</option>
                                        @foreach ($ekskuls as $ekskul)
                                            <option value="{{ $ekskul->id_ekskul }}" 
                                                {{ old('id_ekskul') == $ekskul->id_ekskul ? 'selected' : '' }}>
                                                {{ $ekskul->nama_ekskul }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_ekskul')
                                        <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <hr class="horizontal dark my-3">
                                
                                {{-- Area Filter dan Checkbox Siswa --}}
                                <h6 class="font-weight-bold mb-3">Pilih Siswa Peserta</h6>
                                
                                {{-- Filter Kelas --}}
                                <div class="mb-3">
                                    <label for="filter_kelas" class="form-label">Filter Siswa Berdasarkan Kelas</label>
                                    <select class="form-select" id="filter_kelas">
                                        <option value="" disabled selected>-- Pilih Kelas untuk Menampilkan Siswa --</option> 
                                        @foreach ($kelas as $k)
                                            <option value="{{ $k->id_kelas }}">Kelas {{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Daftar Siswa (Tabel) --}}
                                <div class="table-responsive p-0 mt-3">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Nama Siswa</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center w-10">
                                                    <div class="form-check text-center p-0">
                                                        <input class="form-check-input my-0" type="checkbox" id="check_all_siswa">
                                                        <label class="form-check-label text-xs font-weight-bold d-block" for="check_all_siswa">Pilih Semua</label>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="siswa_list_area">
                                            <tr>
                                                <td colspan="2"><p class="text-secondary text-sm m-0 ps-3">Silakan pilih kelas terlebih dahulu.</p></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                @error('siswa_ids')
                                    <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                @enderror
                                
                                <div class="d-flex justify-content-end mt-4">
                                    <a href="{{ route('master.ekskul.siswa.index') }}" class="btn btn-secondary me-2">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Peserta</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <x-app.footer />
        </div>
    </main>

    {{-- SCRIPTS KHUSUS UNTUK FILTER DAN CHECKBOX --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterKelas = document.getElementById('filter_kelas');
            const idKelasFilterHidden = document.getElementById('id_kelas_filter'); 
            const siswaListArea = document.getElementById('siswa_list_area');
            const checkAllCheckbox = document.getElementById('check_all_siswa');
            
            const allSiswas = @json($siswas); 

            function renderSiswaCheckboxes() {
                const selectedKelasId = filterKelas.value;
                let htmlContent = '';
                let count = 0;
                
                idKelasFilterHidden.value = selectedKelasId;

                if (selectedKelasId === "") {
                    htmlContent = `
                        <tr>
                            <td colspan="2">
                                <p class="text-secondary text-sm m-0 ps-3">Silakan pilih kelas terlebih dahulu.</p>
                            </td>
                        </tr>
                    `;
                    siswaListArea.innerHTML = htmlContent;
                    checkAllCheckbox.disabled = true;
                    return; 
                }

                allSiswas.forEach(siswa => {
                    const matchClass = siswa.id_kelas == selectedKelasId;

                    if (matchClass) {
                        count++;
                        htmlContent += `
                            <tr>
                                <td class="align-middle text-sm ps-3">
                                    ${siswa.nama_siswa} (<span class="text-xs text-secondary">${siswa.kelas.nama_kelas || 'Tanpa Kelas'}</span>)
                                </td>
                                
                                <td class="align-middle text-center">
                                    <div class="form-check">
                                        <input class="form-check-input my-0 mx-auto siswa-checkbox" type="checkbox" 
                                               value="${siswa.id_siswa}" 
                                               id="siswa_${siswa.id_siswa}" 
                                               name="siswa_ids[]">
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                });

                if (count === 0) {
                    htmlContent = `
                        <tr>
                            <td colspan="2">
                                <p class="text-secondary text-sm m-0 ps-3">Tidak ada siswa yang ditemukan di kelas ini.</p>
                            </td>
                        </tr>
                    `;
                    checkAllCheckbox.disabled = true;
                } else {
                    checkAllCheckbox.disabled = false;
                }

                siswaListArea.innerHTML = htmlContent;
                checkAllCheckbox.checked = false;
            }

            checkAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                siswaListArea.querySelectorAll('.siswa-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });

            filterKelas.addEventListener('change', renderSiswaCheckboxes);
        });
    </script>
    @endpush
@endsection