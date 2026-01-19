@extends('layouts.app')

@section('page-title', 'Pengaturan Event & Notifikasi')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">

                <div class="card my-4">

                    {{-- HEADER BIRU --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3 mb-0">
                                Pengaturan Event & Notifikasi
                            </h6>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4">

                        {{-- NOTIFIKASI SUCCESS --}}
                        @if (session('success'))
                            <div class="alert bg-gradient-success text-white alert-dismissible fade show" role="alert">
                                <span class="text-sm">{{ session('success') }}</span>
                                <button type="button" class="btn-close text-white opacity-10" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- NOTIFIKASI ERROR --}}
                        @if ($errors->any())
                            <div class="alert bg-gradient-danger text-white">
                                <ul class="mb-0 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- FORM INPUT --}}
                        <form action="{{ route('input.store') }}" method="POST">
                        @csrf

                            <div class="row mb-4">

                                {{-- DESKRIPSI --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">
                                        Deskripsi
                                    </label>
                                    <div class="input-group input-group-outline">
                                        <textarea name="deskripsi"
                                                  class="form-control"
                                                  rows="3"
                                                  required>{{ old('deskripsi') }}</textarea>
                                    </div>
                                </div>

                                {{-- TANGGAL --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">
                                        Tanggal
                                    </label>
                                    <div class="input-group input-group-outline">
                                        <input type="date"
                                               name="tanggal"
                                               class="form-control"
                                               value="{{ old('tanggal') }}"
                                               required>
                                    </div>
                                </div>

                                {{-- KATEGORI --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">
                                        Kategori
                                    </label>
                                    <div class="input-group input-group-outline">
                                        <select name="kategori"
                                            id="kategori"
                                            class="form-select"
                                            required>
                                            <option value="" disabled selected>Pilih Kategori</option>
                                            <option value="event" {{ old('kategori') == 'event' ? 'selected' : '' }}>
                                                Upcoming Event
                                            </option>
                                            <option value="notifikasi" {{ old('kategori') == 'notifikasi' ? 'selected' : '' }}>
                                                Notifikasi
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                {{-- JADWALKAN (UPCOMING EVENT) --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">
                                        Jadwalkan
                                    </label>
                                    <div class="input-group input-group-outline">
                                        <select name="jadwalkan"
                                                id="jadwalkan"
                                                class="form-select">
                                            <option value="" selected> Pilih Jadwal </option>
                                            <option value="1_hari">1 Hari</option>
                                            <option value="3_hari">3 Hari</option>
                                            <option value="7_hari">7 Hari</option>
                                            <option value="15_hari">15 Hari</option>
                                            <option value="1_bulan">1 Bulan</option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            {{-- ACTION --}}
                            <div class="text-end">
                                <button type="submit" class="btn bg-gradient-primary">
                                Simpan
                                </button>
                            </div>
                        </form>

                    

{{-- HISTORY EVENT & NOTIFIKASI --}}
@include('data.partials.history_input')

</div>
   
                    </div>
                </div>
            </div>
        </div>
        <x-app.footer />
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const kategori   = document.getElementById('kategori');
    const jadwalkan  = document.getElementById('jadwalkan');

    function toggleJadwal() {
    if (kategori.value === 'event') {
        jadwalkan.disabled = false;
        jadwalkan.required = true;
    } else {
        jadwalkan.disabled = true;
        jadwalkan.required = false;
        jadwalkan.value = '';
    }
}

    // saat kategori berubah
    kategori.addEventListener('change', toggleJadwal);

    // saat reload (old value)
    toggleJadwal();
});
</script>

@endsection
