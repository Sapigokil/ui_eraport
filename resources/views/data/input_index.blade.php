@extends('layouts.app')

@section('title', 'Pengaturan Event & Notifikasi')

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
                        <form action="{{ route('pengaturan.input.store') }}" method="POST">
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
                                <div class="col-md-6 mb-3">
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
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">
                                        Kategori
                                    </label>
                                    <div class="input-group input-group-outline">
                                        <select name="kategori" class="form-select" required>
                                            <option value="" disabled selected>-- Pilih Kategori --</option>
                                            <option value="event" {{ old('kategori') == 'event' ? 'selected' : '' }}>
                                                Event
                                            </option>
                                            <option value="notifikasi" {{ old('kategori') == 'notifikasi' ? 'selected' : '' }}>
                                                Notifikasi
                                            </option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            {{-- ACTION --}}
                            <div class="text-end">
                                <button type="submit" class="btn bg-gradient-primary">
                                    <i class="fas fa-plus me-1"></i> Simpan
                                </button>
                            </div>
                        </form>

                        {{-- LIST EVENT --}}
                        @if($events->count())
                            <hr class="horizontal dark my-4">

                            <h6 class="text-uppercase text-xs font-weight-bolder mb-3">
                                Daftar Event
                            </h6>

                            <ul class="list-group">
                                @foreach ($events as $event)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $event->deskripsi }}</strong><br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}
                                            </small>
                                        </div>

                                        <div class="d-flex gap-4">
                                    <!-- EDIT BUTTON -->
                                <button
                                    type="button"
                                    class="btn btn-sm p-0 border-0 bg-transparent text-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editEvent{{ $event->id_event }}">
                                    <i class="fa-solid fa-pen" style="font-size:14px;"></i>
                                </button>

                                <!-- MODAL EDIT -->
                                <div class="modal fade" id="editEvent{{ $event->id_event }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">

                                            <form action="{{ route('pengaturan.input.update', $event->id_event) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Event</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <textarea name="deskripsi" class="form-control mb-2" required>{{ $event->deskripsi }}</textarea>

                                                    <input type="date"
                                                        name="tanggal"
                                                        class="form-control"
                                                        value="{{ $event->tanggal }}"
                                                        required>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        Batal
                                                    </button>
                                                    <button type="submit" class="btn btn-warning">
                                                        Simpan
                                                    </button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>

                                    <!-- DELETE -->
                                    <form action="{{ route('pengaturan.input.delete', $event->id_event) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin hapus event ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-sm p-0 border-0 bg-transparent text-danger"
                                            style="line-height: 1;">
                                            <i class="fa-solid fa-trash"
                                            style="font-size: 14px; transition: 0.2s;"
                                            onmouseover="this.style.transform='scale(1.1)'"
                                            onmouseout="this.style.transform='scale(1)'">
                                            </i>
                                        </button>
                                    </form>
                                </div>

                                    </li>
                                @endforeach
                            </ul>
                        @endif

                    </div>
                </div>

            </div>
        </div>

        <x-app.footer />
    </div>
</main>
@endsection
