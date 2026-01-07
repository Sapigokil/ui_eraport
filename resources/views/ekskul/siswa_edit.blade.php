{{-- File: resources/views/ekskul/siswa_edit.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Edit Peserta Ekstrakurikuler: ' . $ekskul_edit->nama_ekskul)

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

        <x-app.navbar />

        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 shadow-xs border">
                        
                        {{-- HEADER --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-warning shadow-warning border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3 mb-0"><i class="fas fa-user-edit me-2"></i> Edit Peserta Ekstrakurikuler</h6>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            
                            <form action="{{ route('master.ekskul.siswa.update', $ekskul_edit->id_ekskul) }}" method="POST">
                                @csrf
                                @method('PUT') 
                                
                                {{-- Ekstrakurikuler yang diedit --}}
                                <div class="row p-4 border-bottom">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ekstrakurikuler</label>
                                        <div class="input-group input-group-outline">
                                            <input type="hidden" name="id_ekskul" value="{{ $ekskul_edit->id_ekskul }}">
                                            <input type="text" class="form-control" value="{{ $ekskul_edit->nama_ekskul }} (Pembina: {{ $ekskul_edit->guru->nama_guru ?? 'Belum Ditentukan' }})" readonly>
                                        </div>
                                    </div>
                                    
                                    {{-- Filter Kelas --}}
                                    <div class="col-md-6 mb-3">
                                        <label for="id_kelas_filter" class="form-label">Filter Siswa Berdasarkan Kelas</label>
                                        <div class="input-group input-group-outline">
                                            {{-- data-preselect dihapus, karena kita menggunakan properti 'selected' --}}
                                            <select class="form-select" id="id_kelas_filter">
                                                <option value="">-- Tampilkan Semua Kelas --</option>
                                                @foreach ($kelas as $k)
                                                    {{-- Terapkan selected jika id cocok --}}
                                                    <option value="{{ $k->id_kelas }}" {{ $k->id_kelas == $preselected_id_kelas ? 'selected' : '' }}>
                                                        {{ $k->nama_kelas }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Daftar Siswa (Seperti di Create) --}}
                                <div class="p-4">
                                    <h6 class="mb-3">Pilih Siswa Peserta (Total Siswa: {{ $siswas->count() }})</h6>
                                    
                                    @if ($siswas->isEmpty())
                                        <p class="text-danger">Tidak ada data siswa ditemukan.</p>
                                    @else
                                        <div class="table-responsive p-0">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%">
                                                            <div class="form-check ps-3">
                                                                <input class="form-check-input" type="checkbox" id="check-all">
                                                                <label class="form-check-label" for="check-all"></label>
                                                            </div>
                                                        </th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Siswa</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kelas Saat Ini</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($siswas as $siswa)
                                                        <tr class="siswa-row" data-id-kelas="{{ $siswa->id_kelas }}">
                                                            <td>
                                                                <div class="form-check ps-3">
                                                                    <input 
                                                                        class="form-check-input siswa-checkbox" 
                                                                        type="checkbox" 
                                                                        name="siswa_ids[]" 
                                                                        value="{{ $siswa->id_siswa }}" 
                                                                        id="siswa-{{ $siswa->id_siswa }}"
                                                                        {{ in_array($siswa->id_siswa, $terdaftar_ids) ? 'checked' : '' }} 
                                                                    >
                                                                    <label class="form-check-label" for="siswa-{{ $siswa->id_siswa }}"></label>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="text-sm font-weight-bold mb-0">{{ $siswa->nama_siswa }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-sm text-secondary mb-0">{{ $siswa->kelas->nama_kelas ?? 'Tanpa Kelas' }}</p>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    {{-- Hidden field untuk memastikan array tetap terkirim jika tidak ada yang dipilih --}}
                                                    <input type="hidden" name="siswa_ids[]" value="">
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="card-footer text-end pt-0 border-top">
                                    <button type="submit" class="btn bg-gradient-success mt-4 mb-0">Update Peserta Ekskul</button>
                                    <a href="{{ route('master.ekskul.siswa.index') }}" class="btn btn-secondary mt-4 mb-0">Batal</a>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>

            <x-app.footer />
        </div>
    </main>
    
    {{-- JavaScript untuk Filter Kelas --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const classFilter = document.getElementById('id_kelas_filter');
            const siswaRows = document.querySelectorAll('.siswa-row');

            // Fungsi inti untuk menerapkan filter tampilan
            function applyFilter(selectedId) {
                siswaRows.forEach(row => {
                    const rowClassId = row.dataset.idKelas;

                    if (selectedId === '' || rowClassId === selectedId) {
                        row.style.display = ''; // Tampilkan baris
                    } else {
                        row.style.display = 'none'; // Sembunyikan baris
                    }
                });
            }

            // 🛑 BARU: Terapkan filter segera saat DOM dimuat
            // Kita ambil nilai saat ini dari dropdown (yang sudah diatur oleh PHP)
            const initialSelectedId = classFilter.value;
            applyFilter(initialSelectedId); 

            // Terapkan filter saat nilai dropdown berubah
            classFilter.addEventListener('change', function() {
                applyFilter(this.value);
            });

            // Logic Check All
            const checkAll = document.getElementById('check-all');
            const checkboxes = document.querySelectorAll('.siswa-checkbox');

            // Kita revisi agar check-all hanya mempengaruhi siswa yang terlihat
            checkAll.addEventListener('click', function() {
                const isChecked = this.checked;
                checkboxes.forEach(checkbox => {
                    const row = checkbox.closest('.siswa-row');
                    // Hanya toggle checkboxes yang saat ini terlihat
                    if (row.style.display !== 'none') {
                        checkbox.checked = isChecked;
                    }
                });
            });
        });
    </script>
    @endpush
@endsection