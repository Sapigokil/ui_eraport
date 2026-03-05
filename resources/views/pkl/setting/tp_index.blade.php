{{-- File: resources/views/pkl/setting/tp_index.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Pengaturan Rubrik Rapor PKL')

@section('content')

<style>
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    .tp-block {
        transition: transform 0.2s ease-in-out;
    }
</style>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    
    <x-app.navbar />
    
    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 border shadow-xs">
                    
                    {{-- HEADER BANNER --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="text-white text-capitalize ps-3 mb-2 mb-md-0"><i class="fas fa-table me-2"></i> Pengaturan Rubrik Rapor PKL</h6>
                            <div class="pe-3 d-flex gap-2">
                                {{-- Tombol Download Template --}}
                                <a href="{{ route('settings.pkl.template') }}" class="btn btn-sm btn-outline-light mb-0" title="Download Template Excel Kosong">
                                    <i class="fas fa-download me-1"></i> Template
                                </a>
                                {{-- Tombol Buka Modal Import --}}
                                <button type="button" class="btn btn-sm btn-success mb-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                                    <i class="fas fa-file-excel me-1"></i> Import Excel
                                </button>
                                {{-- Tombol Tambah Manual --}}
                                <button type="button" class="btn btn-sm btn-light mb-0 text-primary" onclick="tambahTabelTpBaru()">
                                    <i class="fas fa-plus me-1"></i> Tambah Manual
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pb-2 px-4 mt-2">
                        
                        @if (session('success'))
                            <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="alert bg-light text-dark border mb-4 text-sm">
                            <i class="fas fa-info-circle text-info me-2"></i> <strong>PANDUAN:</strong> Atur rentang nilai batas bawah (Kolom Min) di <strong>Header Kolom</strong>. Gunakan tombol panah di sudut kanan atas kotak untuk menggeser urutan Tujuan Pembelajaran. Anda juga dapat menggunakan fitur Import Excel untuk mempercepat pengisian.
                        </div>

                        {{-- FORM UTAMA --}}
                        <form action="{{ route('settings.pkl.store_massal') }}" method="POST" id="formRubrikPkl">
                            @csrf
                            
                            <div id="tpContainer">
                                
                                {{-- RENDER DATA DARI DATABASE --}}
                                @forelse($tpData as $tp)
                                    @php 
                                        $uidTp = $tp->id; 
                                        $indikatorList = $indikatorData->get($uidTp, collect());
                                        
                                        $firstInd = $indikatorList->first();
                                        $sb_range = $b_range = $c_range = $k_range = null;
                                        if($firstInd) {
                                            $rDataFirst = $rubrikData->get($firstInd->id, collect())->keyBy('predikat');
                                            $sb_range = $rDataFirst->get('Sangat Baik');
                                            $b_range = $rDataFirst->get('Baik');
                                            $c_range = $rDataFirst->get('Cukup');
                                            $k_range = $rDataFirst->get('Perlu Bimbingan');
                                        }
                                    @endphp
                                    <div class="tp-block border rounded mb-5 p-3 shadow-sm bg-white position-relative" id="block_tp_{{ $uidTp }}">
                                        
                                        {{-- Aksi Toolbar Blok TP --}}
                                        <div class="position-absolute top-0 end-0 m-2 mt-3 d-flex gap-1 z-index-2">
                                            <button type="button" class="btn btn-sm btn-secondary mb-0 px-3" onclick="geserAtas(this)" title="Geser Urutan Ke Atas"><i class="fas fa-arrow-up"></i></button>
                                            <button type="button" class="btn btn-sm btn-secondary mb-0 px-3" onclick="geserBawah(this)" title="Geser Urutan Ke Bawah"><i class="fas fa-arrow-down"></i></button>
                                            <button type="button" class="btn btn-sm btn-danger mb-0 px-3" onclick="hapusTabelTpDariDB('{{ $uidTp }}')" title="Hapus Keseluruhan"><i class="fas fa-trash"></i></button>
                                        </div>

                                        <div class="row mb-3 align-items-center">
                                            <div class="col-md-8">
                                                <label class="form-label font-weight-bold text-dark text-uppercase mb-0">Judul Tujuan Pembelajaran</label>
                                                <div class="input-group input-group-outline mb-2">
                                                    <input type="text" name="tp[{{ $uidTp }}][nama_tp]" class="form-control fw-bold" value="{{ $tp->nama_tp }}" required placeholder="Contoh: Soft Skills Dunia Kerja">
                                                </div>
                                                
                                                <label class="form-label font-weight-bold text-dark text-uppercase mb-0 text-xs text-secondary">Label dalam Rapor</label>
                                                <div class="input-group input-group-outline">
                                                    <input type="text" name="tp[{{ $uidTp }}][label_tp]" class="form-control form-control-sm text-secondary" value="{{ $tp->label_tp ?? '' }}" placeholder="Label yang akan tampil saat cetak Rapor">
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end align-self-end">
                                                <button type="button" class="btn btn-sm btn-outline-info mb-0" onclick="tambahBarisIndikator('{{ $uidTp }}')">
                                                    <i class="fas fa-plus"></i> Tambah Indikator
                                                </button>
                                            </div>
                                        </div>

                                        <div class="table-responsive border">
                                            <table class="table table-bordered mb-0 table-sm" id="table_tp_{{ $uidTp }}">
                                                <thead class="bg-gradient-secondary text-white text-center align-middle">
                                                    <tr>
                                                        <th width="20%" class="text-xs" rowspan="2">Indikator</th>
                                                        <th width="20%" class="text-xs bg-success py-2">Sangat Baik</th>
                                                        <th width="20%" class="text-xs bg-info py-2">Baik</th>
                                                        <th width="20%" class="text-xs bg-warning text-dark py-2">Cukup</th>
                                                        <th width="20%" class="text-xs bg-danger py-2">Perlu Bimbingan</th>
                                                        <th width="5%" class="text-xs" rowspan="2">Aksi</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-success p-1">
                                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                                <input type="number" name="tp[{{ $uidTp }}][range][sangat_baik][max]" class="form-control form-control-sm text-center max-input fw-bold bg-light text-dark px-1 py-0 border-0" style="width:35px; height:24px;" value="{{ $sb_range->max_nilai ?? 100 }}" data-level="1" readonly>
                                                                <span class="text-white fw-bold">-</span>
                                                                <input type="number" name="tp[{{ $uidTp }}][range][sangat_baik][min]" class="form-control form-control-sm text-center min-input fw-bold bg-white text-dark px-1 py-0 border-0 shadow-sm" style="width:35px; height:24px;" value="{{ $sb_range->min_nilai ?? 90 }}" data-level="1" onkeyup="kalkulasiDomino(this)">
                                                            </div>
                                                        </th>
                                                        <th class="bg-info p-1">
                                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                                <input type="number" name="tp[{{ $uidTp }}][range][baik][max]" class="form-control form-control-sm text-center max-input fw-bold bg-light text-dark px-1 py-0 border-0" style="width:35px; height:24px;" value="{{ $b_range->max_nilai ?? 89 }}" data-level="2" readonly>
                                                                <span class="text-white fw-bold">-</span>
                                                                <input type="number" name="tp[{{ $uidTp }}][range][baik][min]" class="form-control form-control-sm text-center min-input fw-bold bg-white text-dark px-1 py-0 border-0 shadow-sm" style="width:35px; height:24px;" value="{{ $b_range->min_nilai ?? 80 }}" data-level="2" onkeyup="kalkulasiDomino(this)">
                                                            </div>
                                                        </th>
                                                        <th class="bg-warning p-1">
                                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                                <input type="number" name="tp[{{ $uidTp }}][range][cukup][max]" class="form-control form-control-sm text-center max-input fw-bold bg-light text-dark px-1 py-0 border-0" style="width:35px; height:24px;" value="{{ $c_range->max_nilai ?? 79 }}" data-level="3" readonly>
                                                                <span class="text-dark fw-bold">-</span>
                                                                <input type="number" name="tp[{{ $uidTp }}][range][cukup][min]" class="form-control form-control-sm text-center min-input fw-bold bg-white text-dark px-1 py-0 border-0 shadow-sm" style="width:35px; height:24px;" value="{{ $c_range->min_nilai ?? 70 }}" data-level="3" onkeyup="kalkulasiDomino(this)">
                                                            </div>
                                                        </th>
                                                        <th class="bg-danger p-1">
                                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                                <input type="number" name="tp[{{ $uidTp }}][range][kurang][max]" class="form-control form-control-sm text-center max-input fw-bold bg-light text-dark px-1 py-0 border-0" style="width:35px; height:24px;" value="{{ $k_range->max_nilai ?? 69 }}" data-level="4" readonly>
                                                                <span class="text-white fw-bold">-</span>
                                                                <input type="number" name="tp[{{ $uidTp }}][range][kurang][min]" class="form-control form-control-sm text-center min-input fw-bold bg-light text-dark px-1 py-0 border-0" style="width:35px; height:24px;" value="0" data-level="4" readonly>
                                                            </div>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($indikatorList as $ind)
                                                        @php 
                                                            $uidInd = $ind->id; 
                                                            $rData = $rubrikData->get($uidInd, collect())->keyBy('predikat');
                                                            $sb = $rData->get('Sangat Baik');
                                                            $b = $rData->get('Baik');
                                                            $c = $rData->get('Cukup');
                                                            $k = $rData->get('Perlu Bimbingan');
                                                        @endphp
                                                        <tr id="row_ind_{{ $uidInd }}">
                                                            <td class="align-top p-2 bg-light">
                                                                <textarea name="tp[{{ $uidTp }}][indikator][{{ $uidInd }}][nama]" class="form-control form-control-sm border p-1" rows="4" placeholder="Nama Indikator..." required>{{ $ind->nama_indikator }}</textarea>
                                                            </td>
                                                            <td class="align-top p-2 border-start">
                                                                <textarea name="tp[{{ $uidTp }}][indikator][{{ $uidInd }}][rubrik][sangat_baik][deskripsi_rubrik]" class="form-control form-control-sm border p-1 text-xs" rows="4" placeholder="Deskripsi...">{{ $sb->deskripsi_rubrik ?? '' }}</textarea>
                                                            </td>
                                                            <td class="align-top p-2 border-start">
                                                                <textarea name="tp[{{ $uidTp }}][indikator][{{ $uidInd }}][rubrik][baik][deskripsi_rubrik]" class="form-control form-control-sm border p-1 text-xs" rows="4" placeholder="Deskripsi...">{{ $b->deskripsi_rubrik ?? '' }}</textarea>
                                                            </td>
                                                            <td class="align-top p-2 border-start">
                                                                <textarea name="tp[{{ $uidTp }}][indikator][{{ $uidInd }}][rubrik][cukup][deskripsi_rubrik]" class="form-control form-control-sm border p-1 text-xs" rows="4" placeholder="Deskripsi...">{{ $c->deskripsi_rubrik ?? '' }}</textarea>
                                                            </td>
                                                            <td class="align-top p-2 border-start">
                                                                <textarea name="tp[{{ $uidTp }}][indikator][{{ $uidInd }}][rubrik][kurang][deskripsi_rubrik]" class="form-control form-control-sm border p-1 text-xs" rows="4" placeholder="Deskripsi...">{{ $k->deskripsi_rubrik ?? '' }}</textarea>
                                                            </td>
                                                            <td class="text-center p-2 border-start align-middle">
                                                                <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 mb-0" onclick="hapusBaris('{{ $uidInd }}')"><i class="fas fa-times"></i></button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-secondary" id="emptyStateMsg">
                                        <i class="fas fa-table fa-3x mb-3 opacity-5"></i><br>
                                        Belum ada pengaturan rubrik.<br>Silakan klik "Tambah Manual" atau "Import Excel".
                                    </div>
                                @endforelse

                            </div> {{-- End TP Container --}}

                            <div class="text-end mt-4 pt-3 border-top">
                                <button type="submit" class="btn bg-gradient-success btn-lg shadow-sm">
                                    <i class="fas fa-save me-2"></i> Simpan Semua Perubahan
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <x-app.footer />
    </div>

    {{-- MODAL IMPORT EXCEL --}}
    <div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportExcelLabel"><i class="fas fa-file-excel text-success me-2"></i> Import Rubrik dari Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('settings.pkl.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info text-dark text-sm">
                            <strong>Penting:</strong> Pastikan Anda menggunakan format file dari tombol <strong>Template</strong>. Mengunggah file akan menambahkan atau menimpa indikator yang memiliki nama sama persis.
                        </div>
                        <div class="mb-3">
                            <label for="file_excel" class="form-label font-weight-bold">Pilih File Excel (.xlsx / .csv)</label>
                            <input class="form-control border p-2" type="file" id="file_excel" name="file_excel" accept=".xlsx, .xls, .csv" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn bg-gradient-success">Mulai Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- TEMPLATE HIDDEN UNTUK INDIKATOR ROW --}}
    <table style="display:none;">
        <tbody id="template_indikator_row">
            <tr id="row_ind_TMP_IND_ID">
                <td class="align-top p-2 bg-light">
                    <textarea name="tp[TMP_TP_ID][indikator][TMP_IND_ID][nama]" class="form-control form-control-sm border p-1" rows="4" placeholder="Nama Indikator..." required></textarea>
                </td>
                <td class="align-top p-2 border-start">
                    <textarea name="tp[TMP_TP_ID][indikator][TMP_IND_ID][rubrik][sangat_baik][deskripsi_rubrik]" class="form-control form-control-sm border p-1 text-xs" rows="4" placeholder="Deskripsi..."></textarea>
                </td>
                <td class="align-top p-2 border-start">
                    <textarea name="tp[TMP_TP_ID][indikator][TMP_IND_ID][rubrik][baik][deskripsi_rubrik]" class="form-control form-control-sm border p-1 text-xs" rows="4" placeholder="Deskripsi..."></textarea>
                </td>
                <td class="align-top p-2 border-start">
                    <textarea name="tp[TMP_TP_ID][indikator][TMP_IND_ID][rubrik][cukup][deskripsi_rubrik]" class="form-control form-control-sm border p-1 text-xs" rows="4" placeholder="Deskripsi..."></textarea>
                </td>
                <td class="align-top p-2 border-start">
                    <textarea name="tp[TMP_TP_ID][indikator][TMP_IND_ID][rubrik][kurang][deskripsi_rubrik]" class="form-control form-control-sm border p-1 text-xs" rows="4" placeholder="Deskripsi..."></textarea>
                </td>
                <td class="text-center p-2 border-start align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 mb-0" onclick="hapusBaris('TMP_IND_ID')"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- TEMPLATE HIDDEN UNTUK BLOCK TP BARU --}}
    <div id="template_tp_block" style="display:none;">
        <div class="tp-block border border-info rounded mb-5 p-3 shadow-sm bg-white position-relative" id="block_tp_TMP_TP_ID">
            <div class="position-absolute top-0 end-0 m-2 mt-3 d-flex gap-1 z-index-2">
                <button type="button" class="btn btn-sm btn-secondary mb-0 px-3" onclick="geserAtas(this)" title="Geser Urutan Ke Atas"><i class="fas fa-arrow-up"></i></button>
                <button type="button" class="btn btn-sm btn-secondary mb-0 px-3" onclick="geserBawah(this)" title="Geser Urutan Ke Bawah"><i class="fas fa-arrow-down"></i></button>
                <button type="button" class="btn btn-sm btn-danger mb-0 px-3" onclick="hapusTabelTpLokal('TMP_TP_ID')" title="Hapus"><i class="fas fa-trash"></i></button>
            </div>
            
            <div class="row mb-3 align-items-center">
                <div class="col-md-8">
                    <label class="form-label font-weight-bold text-dark text-uppercase mb-0">Judul Tujuan Pembelajaran</label>
                    <div class="input-group input-group-outline mb-2">
                        <input type="text" name="tp[TMP_TP_ID][nama_tp]" class="form-control fw-bold" required placeholder="Ketik Tujuan Pembelajaran...">
                    </div>
                    <label class="form-label font-weight-bold text-dark text-uppercase mb-0 text-xs text-secondary">Label dalam Rapor</label>
                    <div class="input-group input-group-outline">
                        <input type="text" name="tp[TMP_TP_ID][label_tp]" class="form-control form-control-sm text-secondary" placeholder="Label yang akan tampil saat cetak Rapor">
                    </div>
                </div>
                <div class="col-md-4 text-end align-self-end">
                    <button type="button" class="btn btn-sm btn-outline-info mb-0" onclick="tambahBarisIndikator('TMP_TP_ID')">
                        <i class="fas fa-plus"></i> Tambah Indikator
                    </button>
                </div>
            </div>
            <div class="table-responsive border">
                <table class="table table-bordered mb-0 table-sm" id="table_tp_TMP_TP_ID">
                    <thead class="bg-gradient-secondary text-white text-center align-middle">
                        <tr>
                            <th width="20%" class="text-xs" rowspan="2">Indikator</th>
                            <th width="20%" class="text-xs bg-success py-2">Sangat Baik</th>
                            <th width="20%" class="text-xs bg-info py-2">Baik</th>
                            <th width="20%" class="text-xs bg-warning text-dark py-2">Cukup</th>
                            <th width="20%" class="text-xs bg-danger py-2">Perlu Bimbingan</th>
                            <th width="5%" class="text-xs" rowspan="2">Aksi</th>
                        </tr>
                        <tr>
                            <th class="bg-success p-1">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <input type="number" name="tp[TMP_TP_ID][range][sangat_baik][max]" class="form-control form-control-sm text-center max-input fw-bold bg-light text-dark px-1 py-0 border-0" style="width:35px; height:24px;" value="100" data-level="1" readonly>
                                    <span class="text-white fw-bold">-</span>
                                    <input type="number" name="tp[TMP_TP_ID][range][sangat_baik][min]" class="form-control form-control-sm text-center min-input fw-bold bg-white text-dark px-1 py-0 border-0 shadow-sm" style="width:35px; height:24px;" value="90" data-level="1" onkeyup="kalkulasiDomino(this)">
                                </div>
                            </th>
                            <th class="bg-info p-1">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <input type="number" name="tp[TMP_TP_ID][range][baik][max]" class="form-control form-control-sm text-center max-input fw-bold bg-light text-dark px-1 py-0 border-0" style="width:35px; height:24px;" value="89" data-level="2" readonly>
                                    <span class="text-white fw-bold">-</span>
                                    <input type="number" name="tp[TMP_TP_ID][range][baik][min]" class="form-control form-control-sm text-center min-input fw-bold bg-white text-dark px-1 py-0 border-0 shadow-sm" style="width:35px; height:24px;" value="80" data-level="2" onkeyup="kalkulasiDomino(this)">
                                </div>
                            </th>
                            <th class="bg-warning p-1">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <input type="number" name="tp[TMP_TP_ID][range][cukup][max]" class="form-control form-control-sm text-center max-input fw-bold bg-light text-dark px-1 py-0 border-0" style="width:35px; height:24px;" value="79" data-level="3" readonly>
                                    <span class="text-dark fw-bold">-</span>
                                    <input type="number" name="tp[TMP_TP_ID][range][cukup][min]" class="form-control form-control-sm text-center min-input fw-bold bg-white text-dark px-1 py-0 border-0 shadow-sm" style="width:35px; height:24px;" value="70" data-level="3" onkeyup="kalkulasiDomino(this)">
                                </div>
                            </th>
                            <th class="bg-danger p-1">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <input type="number" name="tp[TMP_TP_ID][range][kurang][max]" class="form-control form-control-sm text-center max-input fw-bold bg-light text-dark px-1 py-0 border-0" style="width:35px; height:24px;" value="69" data-level="4" readonly>
                                    <span class="text-white fw-bold">-</span>
                                    <input type="number" name="tp[TMP_TP_ID][range][kurang][min]" class="form-control form-control-sm text-center min-input fw-bold bg-light text-dark px-1 py-0 border-0" style="width:35px; height:24px;" value="0" data-level="4" readonly>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SCRIPTS JQUERY --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function kalkulasiDomino(element) {
            let val = parseInt($(element).val());
            if(isNaN(val)) return;

            let level = parseInt($(element).data('level')); 
            let tr = $(element).closest('tr');

            if(level === 1) {
                tr.find('input.max-input[data-level="2"]').val(val - 1);
            } else if (level === 2) {
                tr.find('input.max-input[data-level="3"]').val(val - 1);
            } else if (level === 3) {
                tr.find('input.max-input[data-level="4"]').val(val - 1);
            }
        }

        function geserAtas(btn) {
            let block = $(btn).closest('.tp-block');
            if (block.prev('.tp-block').length) {
                block.insertBefore(block.prev('.tp-block'));
                block.stop().css("background-color", "#fff3cd").animate({ backgroundColor: "#ffffff"}, 1000);
            }
        }

        function geserBawah(btn) {
            let block = $(btn).closest('.tp-block');
            if (block.next('.tp-block').length) {
                block.insertAfter(block.next('.tp-block'));
                block.stop().css("background-color", "#fff3cd").animate({ backgroundColor: "#ffffff"}, 1000);
            }
        }

        function tambahTabelTpBaru() {
            $('#emptyStateMsg').hide();
            let uniqueId = 'new_tp_' + Date.now();
            let htmlTemplate = $('#template_tp_block').html();
            
            htmlTemplate = htmlTemplate.replace(/TMP_TP_ID/g, uniqueId);
            $('#tpContainer').append(htmlTemplate);

            for(let i=0; i<4; i++){
                tambahBarisIndikator(uniqueId);
            }
        }

        function tambahBarisIndikator(tpId) {
            let indUniqueId = 'new_ind_' + Date.now() + Math.floor(Math.random() * 100);
            let rowTemplate = $('#template_indikator_row').html();
            
            rowTemplate = rowTemplate.replace(/TMP_TP_ID/g, tpId).replace(/TMP_IND_ID/g, indUniqueId);
            $('#table_tp_' + tpId + ' tbody').append(rowTemplate);
        }

        function hapusBaris(indId) {
            $('#row_ind_' + indId).remove();
        }

        function hapusTabelTpLokal(tpId) {
            $('#block_tp_' + tpId).remove();
            if($('.tp-block').length === 0) $('#emptyStateMsg').show();
        }

        function hapusTabelTpDariDB(tpId) {
            if(confirm('Peringatan: Menghapus Tujuan Pembelajaran ini akan menghapus semua data rubrik dan berpotensi merusak data rapor siswa jika sudah dinilai! Lanjutkan?')) {
                let deleteUrl = "{{ route('settings.pkl.destroy_tp', '') }}/" + tpId;
                let formHtml = `<form id="deleteFormTp_${tpId}" action="${deleteUrl}" method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>`;
                $('body').append(formHtml);
                $('#deleteFormTp_' + tpId).submit();
            }
        }
    </script>
</main>
@endsection