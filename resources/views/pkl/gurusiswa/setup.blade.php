{{-- File: resources/views/pkl/gurusiswa/setup.blade.php --}}
@extends('layouts.app') 

@section('page-title', 'Atur Kelompok Bimbingan PKL')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4 border shadow-xs">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-cogs me-2"></i> Atur Kelompok Bimbingan PKL</h6>
                                <div class="pe-3">
                                    <a href="{{ route('pkl.gurusiswa.index', ['tahun_ajaran' => $tahun_ajaran, 'semester' => $semester]) }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4 mt-3">
                            
                            {{-- Form Utama Penyimpanan --}}
                            <form action="{{ route('pkl.gurusiswa.store') }}" method="POST" id="formSetup">
                                @csrf
                                <input type="hidden" name="tahun_ajaran" id="tahun_ajaran_input" value="{{ $tahun_ajaran }}">
                                <input type="hidden" name="semester" id="semester_input" value="{{ $semester }}">

                                <div class="row bg-light border rounded p-3 mb-4 mx-0">
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold">Tahun Ajaran</label>
                                        <input type="text" class="form-control form-control-sm bg-white" value="{{ $tahun_ajaran }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold">Semester</label>
                                        <input type="text" class="form-control form-control-sm bg-white" value="{{ $semester == 1 ? 'Ganjil' : 'Genap' }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="id_guru" class="form-label font-weight-bold">Guru Pembimbing <span class="text-danger">*</span></label>
                                        <select name="id_guru" id="id_guru" class="form-select form-select-sm border px-2" required onchange="changeGuru(this.value)">
                                            <option value="">-- Pilih Guru Pembimbing --</option>
                                            @foreach($guru_list as $g)
                                                <option value="{{ $g->id_guru }}" {{ $id_guru == $g->id_guru ? 'selected' : '' }}>
                                                    {{ $g->nama_guru }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Tabel Daftar Siswa Bimbingan --}}
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 text-dark">Daftar Siswa Bimbingan</h6>
                                    
                                    <button type="button" class="btn btn-sm btn-info mb-0" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
                                        <i class="fas fa-user-plus me-1"></i> Tambah Siswa
                                    </button>
                                </div>

                                @if(!$id_guru)
                                    <div class="alert bg-gradient-warning text-white text-sm py-2 px-3 border-radius-md" style="opacity: 0.9;">
                                        <i class="fas fa-info-circle me-2"></i> <strong>Tips:</strong> Pilih Guru Pembimbing terlebih dahulu untuk melihat daftar siswa yang sudah pernah ditambahkan sebelumnya.
                                    </div>
                                @endif

                                <div class="table-responsive border rounded mb-4">
                                    <table class="table align-items-center mb-0" id="tabelSiswaUtama">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="5%">No</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Siswa</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Tingkat</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Jurusan</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Kelas</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="10%">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($siswa_terpilih as $index => $row)
                                                <tr id="row_siswa_{{ $row->id_siswa }}">
                                                    <td class="text-center align-middle row-number text-sm font-weight-bold">{{ $index + 1 }}</td>
                                                    <td class="align-middle">
                                                        <h6 class="mb-0 text-sm">{{ $row->nama_siswa }}</h6>
                                                        <input type="hidden" name="id_siswa[]" value="{{ $row->id_siswa }}">
                                                    </td>
                                                    <td class="align-middle text-center"><span class="badge bg-gradient-info text-xxs">{{ $row->tingkat }}</span></td>
                                                    <td class="align-middle text-center"><span class="badge bg-gradient-secondary text-xxs">{{ $row->jurusan }}</span></td>
                                                    <td class="align-middle text-center"><span class="text-xs font-weight-bold">{{ $row->nama_kelas }}</span></td>
                                                    <td class="align-middle text-center">
                                                        <button type="button" class="btn btn-sm btn-danger mb-0 px-3 py-1" onclick="hapusBaris('{{ $row->id_siswa }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr id="row_kosong">
                                                    <td colspan="6" class="text-center py-4 text-secondary text-sm">
                                                        Belum ada siswa yang ditambahkan. Silakan klik tombol "Tambah Siswa" di atas.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end mb-4">
                                    <button type="submit" class="btn bg-gradient-success" id="btnSimpan">
                                        <i class="fas fa-save me-1"></i> Simpan Kelompok
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

    {{-- MODAL TAMBAH SISWA --}}
    <div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-list me-2"></i> Pilih Siswa Magang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="filter_kelas" class="form-label font-weight-bold">Filter Berdasarkan Kelas</label>
                            <select id="filter_kelas" class="form-select border px-2" onchange="loadSiswaModal(this.value)">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas_list as $k)
                                    <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table align-items-center mb-0" id="tabelModalSiswa">
                            <thead class="bg-light position-sticky top-0 z-index-1">
                                <tr>
                                    <th class="text-center" width="10%">
                                        <div class="form-check d-flex justify-content-center m-0">
                                            <input class="form-check-input border-secondary" type="checkbox" id="checkAll" onchange="toggleCheckAll(this)">
                                        </div>
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Siswa</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">NISN</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Status Bimbingan</th>
                                </tr>
                            </thead>
                            <tbody id="bodyModalSiswa">
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-sm text-secondary">Silakan pilih kelas terlebih dahulu.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn bg-gradient-info" onclick="tambahkanKeDaftarUtama()">Tambahkan Terpilih</button>
                </div>
            </div>
        </div>
    </div>

    {{-- KODE JAVASCRIPT DIPINDAHKAN KE SINI (Sesuai Struktur Template Anda) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function changeGuru(id_guru) {
            let ta = $('#tahun_ajaran_input').val();
            let sm = $('#semester_input').val();
            
            let url = "{{ route('pkl.gurusiswa.setup') }}?tahun_ajaran=" + ta + "&semester=" + sm;
            if(id_guru) {
                url += "&id_guru=" + id_guru;
            }
            window.location.href = url;
        }

        function loadSiswaModal(id_kelas) {
            let body = $('#bodyModalSiswa');
            let ta = $('#tahun_ajaran_input').val();
            let sm = $('#semester_input').val();

            if(!id_kelas) {
                body.html('<tr><td colspan="4" class="text-center py-4 text-sm text-secondary">Silakan pilih kelas terlebih dahulu.</td></tr>');
                return;
            }

            body.html('<tr><td colspan="4" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Mengambil data...</td></tr>');

            $.ajax({
                url: "{{ route('pkl.gurusiswa.get_siswa') }}",
                method: "GET",
                data: { 
                    id_kelas: id_kelas, 
                    tahun_ajaran: ta, 
                    semester: sm 
                },
                success: function(data) {
                    if(data.length === 0) {
                        body.html('<tr><td colspan="4" class="text-center py-4 text-sm text-secondary">Tidak ada siswa di kelas ini.</td></tr>');
                        return;
                    }

                    let existingIds = [];
                    $('input[name="id_siswa[]"]').each(function() {
                        existingIds.push(parseInt($(this).val()));
                    });

                    let htmlContent = '';

                    data.forEach(siswa => {
                        let isDisabled = '';
                        let statusBadge = '<span class="badge bg-gradient-success text-xxs">Tersedia</span>';
                        
                        if (existingIds.includes(siswa.id_siswa)) {
                            isDisabled = 'disabled checked';
                            statusBadge = '<span class="badge bg-gradient-info text-xxs">Sudah di List Anda</span>';
                        } else if (siswa.is_used) {
                            isDisabled = 'disabled';
                            statusBadge = '<span class="badge bg-gradient-warning text-xxs">Dimiliki Guru Lain</span>';
                        }

                        htmlContent += `
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="form-check d-flex justify-content-center m-0">
                                        <input class="form-check-input border-secondary check-siswa" type="checkbox" value="${siswa.id_siswa}" 
                                            data-nama="${siswa.nama_siswa}" 
                                            data-tingkat="${siswa.tingkat}" 
                                            data-jurusan="${siswa.jurusan}" 
                                            data-kelas="${siswa.nama_kelas}"
                                            ${isDisabled}>
                                    </div>
                                </td>
                                <td class="align-middle"><h6 class="mb-0 text-sm">${siswa.nama_siswa}</h6></td>
                                <td class="text-center align-middle text-xs">${siswa.nisn ?? '-'}</td>
                                <td class="text-center align-middle">${statusBadge}</td>
                            </tr>
                        `;
                    });

                    body.html(htmlContent);
                },
                error: function(xhr, status, error) {
                    console.log("Error AJAX:", xhr.responseText);
                    body.html('<tr><td colspan="4" class="text-center py-4 text-danger">Gagal memuat data. Silakan cek console/Network.</td></tr>');
                }
            });
        }

        function toggleCheckAll(source) {
            $('.check-siswa:not([disabled])').prop('checked', source.checked);
        }

        function tambahkanKeDaftarUtama() {
            let selectedCheckboxes = $('.check-siswa:checked:not([disabled])');
            let tbodyUtama = $('#tabelSiswaUtama tbody');

            if(selectedCheckboxes.length === 0) {
                alert('Silakan centang minimal satu siswa terlebih dahulu.');
                return;
            }

            $('#row_kosong').remove();

            selectedCheckboxes.each(function() {
                let id = $(this).val();
                let nama = $(this).data('nama');
                let tingkat = $(this).data('tingkat');
                let jurusan = $(this).data('jurusan');
                let kelas = $(this).data('kelas');

                if($('#row_siswa_' + id).length === 0) {
                    let tr = `
                        <tr id="row_siswa_${id}">
                            <td class="text-center align-middle row-number text-sm font-weight-bold">0</td>
                            <td class="align-middle">
                                <h6 class="mb-0 text-sm">${nama}</h6>
                                <input type="hidden" name="id_siswa[]" value="${id}">
                            </td>
                            <td class="align-middle text-center"><span class="badge bg-gradient-info text-xxs">${tingkat}</span></td>
                            <td class="align-middle text-center"><span class="badge bg-gradient-secondary text-xxs">${jurusan}</span></td>
                            <td class="align-middle text-center"><span class="text-xs font-weight-bold">${kelas}</span></td>
                            <td class="align-middle text-center">
                                <button type="button" class="btn btn-sm btn-danger mb-0 px-3 py-1" onclick="hapusBaris('${id}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbodyUtama.append(tr);
                }
            });

            urutkanNomorTabel();
            $('[data-bs-dismiss="modal"]').click();
            $('#filter_kelas').val('');
            $('#checkAll').prop('checked', false);
            $('#bodyModalSiswa').html('<tr><td colspan="4" class="text-center py-4 text-sm text-secondary">Silakan pilih kelas terlebih dahulu.</td></tr>');
        }

        function hapusBaris(id_siswa) {
            $('#row_siswa_' + id_siswa).remove();
            urutkanNomorTabel();

            if($('#tabelSiswaUtama tbody tr').length === 0) {
                $('#tabelSiswaUtama tbody').html(`
                    <tr id="row_kosong">
                        <td colspan="6" class="text-center py-4 text-secondary text-sm">
                            Belum ada siswa yang ditambahkan. Silakan klik tombol "Tambah Siswa" di atas.
                        </td>
                    </tr>
                `);
            }
        }

        function urutkanNomorTabel() {
            $('#tabelSiswaUtama tbody tr:not(#row_kosong)').each(function(index) {
                $(this).find('.row-number').text(index + 1);
            });
        }
    </script>
@endsection