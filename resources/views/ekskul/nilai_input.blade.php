@extends('layouts.app')

@section('page-title', 'Input Nilai - ' . $headerData->nama_ekskul)

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-4">
        
        {{-- 1. HEADER BANNER (Updated Style) --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-primary overflow-hidden position-relative">
                    {{-- Background Icon Decoration --}}
                    <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                        <i class="fas fa-clipboard-check text-white" style="font-size: 10rem;"></i>
                    </div>
                    
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="row align-items-center text-white">
                            <div class="col-md-7">
                                <h3 class="text-white font-weight-bold mb-1">{{ $headerData->nama_ekskul }}</h3>
                                <p class="text-white opacity-8 mb-2">
                                    <i class="fas fa-user-tie me-2"></i> Pembimbing: {{ $headerData->pembimbing }}
                                </p>
                                
                                <span class="badge border border-white text-white fw-bold bg-transparent">
                                    Semester {{ $headerData->semester }} - {{ $headerData->tahun_ajaran }}
                                </span>
                            </div>
                            <div class="col-md-5 text-end mt-4 mt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-between gap-4">
                                    {{-- Statistik Penilaian --}}
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Progress Penilaian</span>
                                        <h4 class="text-white mb-0">
                                            {{ $headerData->dinilai }} <span class="text-sm fw-normal opacity-8">/ {{ $headerData->total }} Siswa</span>
                                        </h4>
                                        <div class="progress mt-2 mx-auto" style="height: 6px; width: 150px; background: rgba(255,255,255,0.2);">
                                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $headerData->persen }}%"></div>
                                        </div>
                                        <small class="text-white opacity-8 text-xxs mt-1">{{ $headerData->persen }}% Selesai</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. TOOLBAR: FILTER & BULK INPUT --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border shadow-xs">
                    <div class="card-body p-3">
                        <div class="row g-3 align-items-end">
                            
                            {{-- Filter Kelas --}}
                            <div class="col-md-3">
                                <label class="form-label font-weight-bold text-xs text-uppercase mb-1 text-primary">Filter Kelas</label>
                                <select id="filter-kelas" class="form-select border-primary ps-2 bg-white fw-bold">
                                    <option value="all">-- Tampilkan Semua Kelas --</option>
                                    @foreach($listKelas as $k)
                                        <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-9">
                                <div class="bg-gray-100 p-2 border-radius-md border border-light">
                                    <label class="form-label font-weight-bold text-xxs text-uppercase mb-1 text-secondary ms-1">Isi Nilai Massal (Hanya yang dicentang)</label>
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <select id="bulk-predikat" class="form-select form-select-sm bg-white border">
                                                <option value="">- Pilih Predikat -</option>
                                                <option value="Sangat Baik">Sangat Baik</option>
                                                <option value="Baik">Baik</option>
                                                <option value="Cukup">Cukup</option>
                                                <option value="Kurang">Kurang</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" id="bulk-keterangan" class="form-control form-control-sm border ps-2" placeholder="Tulis Keterangan massal...">
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" id="btn-apply-bulk" class="btn btn-sm bg-gradient-dark w-100 mb-0">
                                                <i class="fas fa-fill-drip me-1"></i> Terapkan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. TABEL INPUT --}}
        <div class="row">
            <div class="col-12">
                <form action="{{ route('ekskul.nilai.store') }}" method="POST" id="form-nilai">
                    @csrf
                    {{-- TAMBAHAN: Input Hidden untuk JSON --}}
                    <input type="hidden" name="bulk_json_data" id="bulk_json_data">

                    <input type="hidden" name="id_ekskul" value="{{ $ekskul->id_ekskul }}">
                    <input type="hidden" name="semester" value="{{ $semesterRaw }}">
                    <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">

                    <div class="card border-radius-xl shadow-sm">
                        {{-- Sticky Header Table --}}
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table align-items-center mb-0 table-hover" id="table-siswa">
                                <thead class="bg-gray-100 sticky-top" style="z-index: 10;">
                                    <tr>
                                        <th class="text-center ps-4" width="5%">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input border-dark" type="checkbox" id="check-all">
                                            </div>
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Siswa / Kelas</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="20%">Predikat</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="40%">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siswa as $index => $s)
                                        @php
                                            $nilai = $existing_nilai[$s->id_siswa] ?? null;
                                            $predikat = $nilai->predikat ?? '';
                                            $keterangan = $nilai->keterangan ?? '';
                                            $idKelas = $s->siswa->id_kelas;
                                        @endphp
                                        <tr class="row-siswa" data-kelas="{{ $idKelas }}">
                                            <td class="text-center align-middle">
                                                <div class="form-check d-flex justify-content-center">
                                                    {{-- Checkbox Individual --}}
                                                    <input class="form-check-input check-item border-secondary" 
                                                           type="checkbox" 
                                                           name="selected_ids[]" 
                                                           value="{{ $s->id_siswa }}">
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $s->siswa->nama_siswa }}</h6>
                                                    <p class="text-xs text-secondary mb-0">
                                                        <span class="badge badge-sm bg-gray-200 text-dark me-1">{{ $s->siswa->kelas->nama_kelas ?? '-' }}</span>
                                                        {{ $s->siswa->nisn }}
                                                    </p>
                                                    <input type="hidden" name="nilai[{{ $s->id_siswa }}][id_kelas]" value="{{ $idKelas }}">
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <select name="nilai[{{ $s->id_siswa }}][predikat]" class="form-select border px-2 text-center input-predikat bg-white">
                                                    <option value="">- Pilih -</option>
                                                    <option value="Sangat Baik" {{ $predikat == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                                    <option value="Baik" {{ $predikat == 'Baik' ? 'selected' : '' }}>Baik</option>
                                                    <option value="Cukup" {{ $predikat == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                                    <option value="Kurang" {{ $predikat == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                                </select>
                                            </td>
                                            <td class="align-middle">
                                                <input type="text" name="nilai[{{ $s->id_siswa }}][keterangan]" 
                                                       class="form-control border px-2 py-1 text-sm input-keterangan" 
                                                       value="{{ $keterangan }}" 
                                                       placeholder="Catatan...">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer: Info & Tombol Simpan --}}
                        <div class="card-footer bg-white border-top sticky-bottom z-index-1 p-3 d-flex justify-content-between align-items-center">
                            <div class="text-xs text-secondary">
                                <i class="fas fa-info-circle me-1"></i> Hanya data yang <b>dicentang</b> yang akan disimpan.
                            </div>
                            <button type="submit" class="btn bg-gradient-primary mb-0 shadow-lg" id="btn-simpan">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan (<span id="count-checked">0</span>)
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <x-app.footer />
    </div>
</main>

{{-- SweetAlert2 & Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        let isDirty = false; // Flag perubahan data
        let currentFilter = 'all';

        // --- 1. FILTER KELAS LOGIC ---
        $('#filter-kelas').on('change', function() {
            let newFilter = $(this).val();
            
            // Cek jika ada perubahan belum disimpan
            if (isDirty) {
                Swal.fire({
                    title: 'Perubahan Belum Disimpan!',
                    // REVISI TEKS
                    text: "Anda memiliki data yang telah diubah namun belum disimpan. Mengganti filter akan ada resiko data tidak tersimpan. Lanjutkan?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ganti Filter',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        applyFilter(newFilter);
                        // Reset dirty flag setelah ganti filter (karena data lama dianggap dibuang/disetujui hilang)
                        isDirty = false; 
                    } else {
                        // Kembalikan dropdown ke nilai sebelumnya
                        $(this).val(currentFilter);
                    }
                });
            } else {
                applyFilter(newFilter);
            }
        });

        function applyFilter(kelasId) {
            currentFilter = kelasId;
            
            // 1. Reset: Lepaskan SEMUA centang (baik yang tampil maupun tersembunyi)
            $('.check-item').prop('checked', false);

            // 2. Logic Tampil/Sembunyi Baris
            if (kelasId === 'all') {
                $('.row-siswa').show();
            } else {
                $('.row-siswa').hide(); // Sembunyikan semua dulu
                $('.row-siswa[data-kelas="' + kelasId + '"]').show(); // Munculkan yang sesuai
            }

            // 3. Auto-Check: Centang HANYA yang tampil (Visible)
            // Ini memenuhi request: "lepaskan semua, centang hanya yang tampil"
            $('.row-siswa:visible').find('.check-item').prop('checked', true);
            
            // Update status "Select All" di header agar sinkron
            $('#check-all').prop('checked', true);
            
            updateCheckedCount();
        }

        // --- 2. CHECKBOX LOGIC (SELECT ALL MANUAL) ---
        // Select All hanya berlaku untuk baris yang VISIBLE
        $('#check-all').on('change', function() {
            let isChecked = $(this).is(':checked');
            
            // Hanya target baris yang visible
            $('.row-siswa:visible').each(function() {
                $(this).find('.check-item').prop('checked', isChecked);
            });
            
            updateCheckedCount();
        });

        // Update count saat checkbox item diklik manual
        $(document).on('change', '.check-item', function() {
            updateCheckedCount();
            
            // Logic: Jika uncheck satu, header uncheck. Jika semua visible checked, header check.
            let visibleRows = $('.row-siswa:visible').length;
            let checkedVisibleRows = $('.row-siswa:visible').find('.check-item:checked').length;
            
            // Hindari centang header jika tidak ada row yang visible
            if (visibleRows > 0) {
                $('#check-all').prop('checked', (visibleRows === checkedVisibleRows));
            } else {
                $('#check-all').prop('checked', false);
            }
        });

        function updateCheckedCount() {
            let count = $('.check-item:checked').length;
            $('#count-checked').text(count);
        }

        // --- 3. BULK APPLY LOGIC ---
        $('#btn-apply-bulk').on('click', function() {
            let predikat = $('#bulk-predikat').val();
            let keterangan = $('#bulk-keterangan').val();
            let appliedCount = 0;

            // Loop hanya row yang checked DAN visible
            $('.row-siswa:visible').find('.check-item:checked').each(function() {
                let row = $(this).closest('tr');
                
                // Isi nilai hanya jika input bulk tidak kosong
                if(predikat) row.find('.input-predikat').val(predikat);
                if(keterangan) row.find('.input-keterangan').val(keterangan);
                
                appliedCount++;
            });

            if(appliedCount > 0) {
                isDirty = true; // Tandai ada perubahan
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
                });
                Toast.fire({ icon: 'success', title: appliedCount + ' Siswa berhasil diupdate (Draft)' });
            } else {
                Swal.fire('Info', 'Silakan centang siswa terlebih dahulu sebelum menerapkan nilai massal.', 'info');
            }
        });

        // --- 4. DETEKSI PERUBAHAN (DIRTY STATE) ---
        // Mendeteksi perubahan pada input form
        $(document).on('change input', '.input-predikat, .input-keterangan', function() {
            isDirty = true;
            
            // Fitur tambahan: Jika user mengisi nilai manual, otomatis centang checkbox baris tsb
            // tapi HANYA jika nilainya tidak kosong
            if ($(this).val() !== '') {
                let row = $(this).closest('tr');
                let checkbox = row.find('.check-item');
                if (!checkbox.is(':checked')) {
                    checkbox.prop('checked', true);
                    updateCheckedCount();
                }
            }
        });

        // --- 5. FORM SUBMIT HANDLER (REVISI JSON) ---
        $('#form-nilai').on('submit', function(e) {
            let checkedCount = $('.check-item:checked').length;

            if (checkedCount === 0) {
                e.preventDefault();
                Swal.fire('Peringatan', 'Belum ada siswa yang dipilih (dicentang) untuk disimpan.', 'warning');
                return false;
            }

            // --- LOGIC BARU: Konversi ke JSON ---
            let dataToSave = [];

            // Loop hanya row yang dicentang
            $('.row-siswa').each(function() {
                let checkbox = $(this).find('.check-item');
                
                if (checkbox.is(':checked')) {
                    let idSiswa = checkbox.val();
                    let idKelas = $(this).data('kelas'); // Pastikan tr punya data-kelas
                    
                    // Ambil nilai dari input
                    let predikat = $(this).find('.input-predikat').val();
                    let keterangan = $(this).find('.input-keterangan').val();

                    dataToSave.push({
                        id_siswa: idSiswa,
                        id_kelas: idKelas,
                        predikat: predikat,
                        keterangan: keterangan
                    });
                }
            });

            // Masukkan JSON ke input hidden
            $('#bulk_json_data').val(JSON.stringify(dataToSave));

            // Disable SEMUA input asli agar tidak dikirim ganda & tidak kena limit PHP
            $('.input-predikat, .input-keterangan, .check-item').prop('disabled', true);

            // Reset dirty flag
            isDirty = false;
            return true;
        });
        
        // --- INISIALISASI AWAL ---
        // Jalankan filter 'all' saat load agar defaultnya tercentang semua sesuai request
        applyFilter('all');
    });
</script>
@endsection