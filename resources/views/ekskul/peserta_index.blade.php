@extends('layouts.app')

@section('page-title', 'Data Peserta Ekstrakurikuler')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3 mb-0">Rekapitulasi Peserta Ekstrakurikuler</h6>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2">
                        {{-- ALERT --}}
                        @if(session('success'))
                            <div class="alert alert-success text-white mx-4 text-sm">{{ session('success') }}</div>
                        @endif

                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" width="5%">No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Ekstrakurikuler</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Guru Pembimbing</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Jumlah Peserta</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ekskuls as $item)
                                    <tr>
                                        <td class="text-center text-secondary text-xs font-weight-bold">
                                            {{ $loop->iteration + $ekskuls->firstItem() - 1 }}
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $item->nama_ekskul }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-xs font-weight-bold text-secondary">
                                                {{ $item->guru->nama_guru ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-sm bg-gradient-info">
                                                {{ $item->peserta_count }} Siswa
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('ekskul.peserta.edit', $item->id_ekskul) }}" class="btn btn-sm btn-outline-warning mb-0">
                                                <i class="fas fa-user-edit me-1"></i> Kelola Peserta
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-sm text-secondary py-4">
                                            Belum ada data ekstrakurikuler.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINATION --}}
                        <div class="px-4 py-3 d-flex justify-content-end">
                            {{ $ekskuls->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-app.footer />
    </div>
</main>
@endsection