{{-- File: resources/views/pkl/nilai/input.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Form Penilaian PKL')

@section('content')

<style>
    .student-list-item { cursor: pointer; transition: all 0.2s; border-left: 4px solid transparent; }
    .student-list-item:hover { background-color: #f8f9fa; }
    .student-list-item.active { background-color: #e9ecef; border-left: 4px solid #b088ff; }
    .split-left, .split-right { max-height: 70vh; overflow-y: auto; }
    .split-left::-webkit-scrollbar, .split-right::-webkit-scrollbar { width: 4px; }
    .split-left::-webkit-scrollbar-thumb, .split-right::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; text-align: center; font-weight: bold; font-size: 1rem;}
</style>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />
    
    <div class="container-fluid py-4 px-5">
        
        {{-- HEADER BANNER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-gradient-primary overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-1 pe-3 pt-3">
                        <i class="fas fa-clipboard-check text-white" style="font-size: 10rem;"></i>
                    </div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="row align-items-center text-white">
                            <div class="col-md-7">
                                <h3 class="text-white font-weight-bold mb-1">{{ $pembimbingInfo->nama_kelompok }}</h3>
                                <p class="text-white opacity-8 mb-2"><i class="fas fa-user-tie me-2"></i> Pembimbing: {{ $pembimbingInfo->nama_guru }}</p>
                                <span class="badge border border-white text-white fw-bold bg-transparent">
                                    Semester {{ $semester }} - {{ $tahun_ajaran }}
                                </span>
                            </div>
                            <div class="col-md-5 text-end mt-4 mt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-between gap-4">
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Data Masuk</span>
                                        <h4 class="text-white mb-0">{{ $rawCount }} <span class="text-sm fw-normal opacity-8">/ {{ $totalSiswa }} Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $persenRaw }}%"></div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <span class="text-xs text-uppercase font-weight-bold d-block opacity-8 mb-1">Siap Cetak</span>
                                        <h4 class="text-white mb-0">{{ $finalCount }} <span class="text-sm fw-normal opacity-8">/ {{ $totalSiswa }} Siswa</span></h4>
                                        <div class="progress mt-2 mx-auto" style="height: 4px; width: 100px; background: rgba(255,255,255,0.3);">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $persenFinal }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SPLIT SCREEN AREA --}}
        <div class="row">
            
            {{-- PANEL KIRI: DAFTAR SISWA --}}
            <div class="col-md-4 mb-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-light border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-users me-2 text-info"></i> Daftar Siswa Bimbingan</h6>
                        <a href="{{ route('pkl.nilai.index') }}" class="btn btn-sm btn-outline-secondary mb-0 py-1 px-2"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                    </div>
                    <div class="card-body p-0 split-left">
                        <ul class="list-group list-group-flush" id="studentList">
                            @forelse($dataSiswa as $siswa)
                                <li class="list-group-item student-list-item py-3 px-4" 
                                    data-id="{{ $siswa->id_penempatan }}"
                                    data-nama="{{ $siswa->nama_siswa }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 text-sm">{{ $siswa->nama_siswa }}</h6>
                                            <span class="text-xs text-secondary d-block"><i class="fas fa-building me-1"></i>{{ $siswa->tempat_pkl ?? 'Belum ada industri' }}</span>
                                        </div>
                                        <div class="status-badge-container">
                                            @if($siswa->status_penilaian === null)
                                                <span class="badge bg-secondary text-xxs px-2 py-1"><i class="fas fa-clock me-1"></i>Belum</span>
                                            @elseif($siswa->status_penilaian == 0)
                                                <span class="badge bg-warning text-xxs px-2 py-1"><i class="fas fa-edit me-1"></i>Draft</span>
                                            @else
                                                <span class="badge bg-success text-xxs px-2 py-1"><i class="fas fa-check me-1"></i>Selesai</span>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center py-5 text-secondary border-0">
                                    <i class="fas fa-user-slash fa-2x mb-2"></i><br>Tidak ada siswa bimbingan.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            {{-- PANEL KANAN: FORM PENILAIAN --}}
            <div class="col-md-8 mb-4">
                <div class="card border shadow-sm h-100">
                    
                    {{-- Layar Kosong Sebelum Diklik --}}
                    <div id="emptyFormState" class="card-body d-flex flex-column justify-content-center align-items-center h-100 text-secondary py-5">
                        <i class="fas fa-arrow-left fa-3x mb-3 opacity-3"></i>
                        <h5>Pilih Siswa Terlebih Dahulu</h5>
                        <p class="text-sm">Klik salah satu nama siswa di panel kiri untuk mulai menilai.</p>
                    </div>

                    {{-- Form Aktif --}}
                    <div id="activeFormState" class="d-none h-100 d-flex flex-column">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-xs text-secondary text-uppercase fw-bold">Menilai Siswa:</span>
                                <h5 class="mb-0 text-primary" id="headerNamaSiswa">Nama Siswa</h5>
                            </div>
                        </div>
                        
                        <div class="card-body p-4 bg-gray-100 split-right flex-grow-1" id="formScrollArea">
                            <form id="formPenilaian" onsubmit="return submitPenilaian(event)">
                                @csrf
                                <input type="hidden" name="id_penempatan" id="inputIdPenempatan">
                                <input type="hidden" name="id_guru" value="{{ $id_guru }}">

                                {{-- LOOPING BLOK TP & INDIKATOR --}}
                                @foreach($tpData as $tp)
                                    <div class="card shadow-none border mb-4">
                                        <div class="card-header bg-dark text-white p-3">
                                            <h6 class="mb-0 text-white">{{ $tp->nama_tp }}</h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-bordered mb-0 text-sm">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="40%">Indikator Penilaian</th>
                                                        <th width="15%" class="text-center">Nilai (0-100)</th>
                                                        <th width="45%">Deskripsi Otomatis</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $indikators = $indikatorData->get($tp->id, collect()); @endphp
                                                    @foreach($indikators as $ind)
                                                        <tr>
                                                            <td class="text-wrap align-middle fw-bold">{{ $ind->nama_indikator }}</td>
                                                            <td class="align-middle px-3">
                                                                <input type="number" 
                                                                       name="nilai[{{ $tp->id }}][{{ $ind->id }}]" 
                                                                       id="input_nilai_{{ $ind->id }}"
                                                                       class="form-control border bg-white form-nilai-input" 
                                                                       data-tp="{{ $tp->id }}"
                                                                       data-ind="{{ $ind->id }}"
                                                                       min="0" max="100">
                                                            </td>
                                                            <td class="align-middle bg-light p-2">
                                                                <textarea class="form-control border-0 bg-transparent text-xs text-secondary form-deskripsi-output" 
                                                                          id="desc_nilai_{{ $ind->id }}" 
                                                                          rows="3" 
                                                                          readonly
                                                                          style="resize: none;"
                                                                          placeholder="Ketik angka di kolom sebelah..."></textarea>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="bg-gray-200">
                                                    <tr>
                                                        <td colspan="3" class="p-3 border-top border-2">
                                                            <label class="text-xs text-uppercase text-secondary font-weight-bolder mb-1">
                                                                <i class="fas fa-magic text-info me-1"></i> Preview Deskripsi Rapor (Gabungan TP Ini)
                                                            </label>
                                                            <textarea id="preview_gabungan_{{ $tp->id }}" 
                                                                      class="form-control border bg-white text-secondary text-sm p-3 shadow-sm" 
                                                                      rows="2" readonly style="resize: none;" 
                                                                      placeholder="Preview kalimat akan dirakit otomatis di sini saat nilai diisi..."></textarea>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- BLOK DATA PELAKSANAAN PKL --}}
                                <div class="card shadow-none border mb-4 border-warning">
                                    <div class="card-header bg-warning text-white p-3">
                                        <h6 class="mb-0 text-white"><i class="fas fa-file-signature me-2"></i> Data Pelaksanaan PKL (Untuk Rapor)</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <label class="form-label text-xs">Program Keahlian</label>
                                                <input type="text" name="program_keahlian" id="inputProgramKeahlian" class="form-control border p-2 bg-white" placeholder="Contoh: Teknik Komputer dan Informatika">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-xs">Konsentrasi Keahlian</label>
                                                <input type="text" name="konsentrasi_keahlian" id="inputKonsentrasiKeahlian" class="form-control border p-2 bg-white" placeholder="Contoh: Rekayasa Perangkat Lunak">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3 mb-md-0">
                                                <label class="form-label text-xs">Tanggal Mulai PKL</label>
                                                <input type="date" name="tanggal_mulai" id="inputTglMulai" class="form-control border p-2 bg-white">
                                            </div>
                                            <div class="col-md-4 mb-3 mb-md-0">
                                                <label class="form-label text-xs">Tanggal Selesai PKL</label>
                                                <input type="date" name="tanggal_selesai" id="inputTglSelesai" class="form-control border p-2 bg-white">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label text-xs">Nama Instruktur (DU/DI)</label>
                                                <input type="text" name="nama_instruktur" id="inputInstruktur" class="form-control border p-2 bg-white" placeholder="Nama Instruktur Lapangan">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- BLOK KEHADIRAN & CATATAN --}}
                                <div class="card shadow-none border mb-4 border-info">
                                    <div class="card-header bg-info text-white p-3">
                                        <h6 class="mb-0 text-white"><i class="fas fa-clipboard-list me-2"></i> Kehadiran & Kesimpulan</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row mb-3">
                                            <div class="col-4">
                                                <label class="form-label text-xs">Sakit (Hari)</label>
                                                <input type="number" name="sakit" id="inputSakit" class="form-control border p-2 bg-white" value="0" min="0">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label text-xs">Izin (Hari)</label>
                                                <input type="number" name="izin" id="inputIzin" class="form-control border p-2 bg-white" value="0" min="0">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label text-xs">Alpa (Hari)</label>
                                                <input type="number" name="alpa" id="inputAlpa" class="form-control border p-2 bg-white" value="0" min="0">
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label text-xs">Catatan Pembimbing</label>
                                            <textarea name="catatan_pembimbing" id="inputCatatan" class="form-control border p-2 bg-white" rows="3" placeholder="Tuliskan pesan, kesan, atau saran untuk siswa..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- ACTION BUTTONS --}}
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div class="form-check form-switch ps-0">
                                        <input class="form-check-input ms-0 me-2 cursor-pointer" type="checkbox" id="checkFinal" name="status_penilaian" value="1" style="height:25px; width:50px;">
                                        <label class="form-check-label font-weight-bold text-dark cursor-pointer mt-1" for="checkFinal">Tandai Final (Siap Cetak Rapor)</label>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-secondary mb-0 me-2" onclick="saveAsDraft()"><i class="fas fa-save me-1"></i> Simpan Draft</button>
                                        <button type="submit" class="btn bg-gradient-success mb-0" id="btnSimpanLanjut">Simpan & Lanjut <i class="fas fa-arrow-right ms-1"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <x-app.footer />
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const masterRubrik = @json($rubrikData);
        const autoSelectId = "{{ request('id_penempatan') }}";

        function lcfirst(str) {
            if (!str) return '';
            return str.charAt(0).toLowerCase() + str.slice(1);
        }

        $(document).ready(function() {
            
            $('.student-list-item').click(function() {
                $('.student-list-item').removeClass('active');
                $(this).addClass('active');

                let idPenempatan = $(this).data('id');
                let namaSiswa = $(this).data('nama');

                $('#headerNamaSiswa').text(namaSiswa);
                $('#inputIdPenempatan').val(idPenempatan);
                $('#emptyFormState').addClass('d-none');
                $('#activeFormState').removeClass('d-none');
                $('#formScrollArea').scrollTop(0);

                $('[id^="preview_gabungan_"]').val('').removeClass('text-dark fw-bold bg-white').addClass('text-secondary bg-light');

                loadSiswaData(idPenempatan);
            });

            if (autoSelectId) {
                let targetLi = $('.student-list-item[data-id="' + autoSelectId + '"]');
                if (targetLi.length > 0) {
                    targetLi.click(); 
                    targetLi[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            $('.form-nilai-input').on('keyup change', function() {
                let idIndikator = $(this).data('ind');
                let idTp = $(this).data('tp');
                let val = parseInt($(this).val());
                let targetDesc = $('#desc_nilai_' + idIndikator);

                if(isNaN(val) || val === '') {
                    targetDesc.val('');
                    targetDesc.removeClass('text-dark fw-bold').addClass('text-secondary');
                    updatePreviewGabungan(idTp);
                    return;
                }

                if(val > 100) { $(this).val(100); val = 100; }
                if(val < 0) { $(this).val(0); val = 0; }

                let rubrikList = masterRubrik[idIndikator];
                let foundText = 'Belum ada deskripsi.';

                if(rubrikList) {
                    for (let i = 0; i < rubrikList.length; i++) {
                        let r = rubrikList[i];
                        if (val >= r.min_nilai && val <= r.max_nilai) {
                            foundText = r.deskripsi_rubrik;
                            break;
                        }
                    }
                }
                
                targetDesc.val(foundText);
                targetDesc.removeClass('text-secondary').addClass('text-dark fw-bold');

                updatePreviewGabungan(idTp);
            });
        });

        function updatePreviewGabungan(idTp) {
            let inputs = $('.form-nilai-input[data-tp="' + idTp + '"]');
            let validInputs = [];
            let maxVal = -1;
            let minVal = 101;
            let descMax = '';
            let descMin = '';

            inputs.each(function() {
                let val = parseInt($(this).val());
                if (!isNaN(val) && val !== '') {
                    let idInd = $(this).data('ind');
                    let descField = $('#desc_nilai_' + idInd).val();
                    
                    if (descField && descField !== 'Belum ada deskripsi.') {
                        validInputs.push({ nilai: val, desc: descField });
                        
                        if (val > maxVal) { maxVal = val; descMax = descField; }
                        if (val < minVal) { minVal = val; descMin = descField; }
                    }
                }
            });

            let previewBox = $('#preview_gabungan_' + idTp);

            if (validInputs.length === 0) {
                previewBox.val('');
                previewBox.removeClass('text-dark fw-bold bg-white').addClass('text-secondary bg-light');
                return;
            }

            let gabungan = "";
            let dMax = lcfirst(descMax.trim());
            let dMin = lcfirst(descMin.trim());

            if (maxVal === minVal) {
                if (validInputs.length >= 2) {
                    let d1 = lcfirst(validInputs[0].desc.trim());
                    let d2 = lcfirst(validInputs[1].desc.trim());
                    gabungan = "Ananda " + d1 + "; " + d2 + ".";
                } else {
                    gabungan = "Ananda " + dMax + ".";
                }
            } else {
                // Perubahan: Menggunakan pemisah titik koma secara langsung
                gabungan = "Ananda " + dMax + "; " + dMin + ".";
            }

            previewBox.val(gabungan);
            previewBox.removeClass('text-secondary bg-light').addClass('text-dark fw-bold bg-white');
        }

        // FUNGSI AJAX: AMBIL DATA
        function loadSiswaData(idPenempatan) {
            $('#formPenilaian')[0].reset();
            $('.form-deskripsi-output').val('').removeClass('text-dark fw-bold').addClass('text-secondary');
            $('#checkFinal').prop('checked', false);

            $.ajax({
                url: "{{ url('pkl/nilai/get-siswa') }}/" + idPenempatan,
                type: "GET",
                success: function(res) {
                    if(res.status === 'success') {
                        // Isi Catatan & Field Baru
                        if(res.catatan) {
                            $('#inputProgramKeahlian').val(res.catatan.program_keahlian);
                            $('#inputKonsentrasiKeahlian').val(res.catatan.konsentrasi_keahlian);
                            $('#inputTglMulai').val(res.catatan.tanggal_mulai);
                            $('#inputTglSelesai').val(res.catatan.tanggal_selesai);
                            $('#inputInstruktur').val(res.catatan.nama_instruktur);
                            
                            $('#inputSakit').val(res.catatan.sakit);
                            $('#inputIzin').val(res.catatan.izin);
                            $('#inputAlpa').val(res.catatan.alpa);
                            $('#inputCatatan').val(res.catatan.catatan_pembimbing);
                            if(res.catatan.status_penilaian == 1) $('#checkFinal').prop('checked', true);
                        }
                        
                        // Isi Nilai
                        if(res.nilai) {
                            for (const [id_tp, dataNilai] of Object.entries(res.nilai)) {
                                let indikators = dataNilai.data_indikator;
                                if(indikators) {
                                    for (const [id_ind, objData] of Object.entries(indikators)) {
                                        $('#input_nilai_' + id_ind).val(objData.nilai).trigger('change');
                                    }
                                }
                            }
                        }
                    }
                }
            });
        }

        function saveAsDraft() {
            $('#checkFinal').prop('checked', false);
            $('#formPenilaian').submit();
        }

        function submitPenilaian(e) {
            e.preventDefault();
            
            let formData = $('#formPenilaian').serialize();
            if(!$('#checkFinal').is(':checked')) formData += '&status_penilaian=0';

            let btn = $('#btnSimpanLanjut');
            let oldHtml = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: "{{ route('pkl.nilai.store') }}",
                type: "POST",
                data: formData,
                success: function(res) {
                    if(res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Tersimpan!',
                            timer: 1000,
                            showConfirmButton: false
                        });

                        let activeLi = $('.student-list-item.active');
                        let isFinal = $('#checkFinal').is(':checked');
                        let badgeHtml = isFinal ? '<span class="badge bg-success text-xxs px-2 py-1"><i class="fas fa-check me-1"></i>Selesai</span>' : '<span class="badge bg-warning text-xxs px-2 py-1"><i class="fas fa-edit me-1"></i>Draft</span>';
                        activeLi.find('.status-badge-container').html(badgeHtml);

                        let nextLi = activeLi.next('.student-list-item');
                        if(nextLi.length > 0) {
                            nextLi.click();
                            nextLi[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal menyimpan data.', 'error');
                },
                complete: function() {
                    btn.html(oldHtml).prop('disabled', false);
                }
            });
        }
    </script>
</main>
@endsection