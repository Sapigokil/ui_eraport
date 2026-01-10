@extends('layouts.app')

@section('page-title', 'Pengaturan Bobot Nilai')

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
                                Pengaturan Bobot Nilai
                            </h6>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4">

                        {{-- NOTIFIKASI --}}
                        @if (session('success'))
                            <div class="alert bg-gradient-success text-white alert-dismissible fade show" role="alert">
                                <span class="text-sm">{{ session('success') }}</span>
                                <button type="button" class="btn-close text-white opacity-10" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert bg-gradient-danger text-white">
                                <ul class="mb-0 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                         <form action="{{ route('pengaturan.bobot.store') }}" method="POST">
                            @csrf

                            <div class="row mb-4">

                                {{-- JUMLAH SUMATIF --}}
                                <div class="col-md-4">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">
                                        Jumlah Sumatif
                                    </label>
                                    <div class="input-group input-group-outline">
                                        <select name="jumlah_sumatif" class="form-select" required>
                                            <option value="" disabled selected>-- Pilih --</option>
                                            @for ($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}">Sumatif {{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                {{-- SEMESTER --}}
                                <div class="col-md-4">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">
                                        Semester
                                    </label>
                                    <div class="input-group input-group-outline">
                                        <select name="semester" class="form-select" required>
                                            <option value="" disabled selected>-- Pilih --</option>
                                            <option value="GANJIL">Ganjil</option>
                                            <option value="GENAP">Genap</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- TAHUN AJARAN --}}
                                <div class="col-md-4">
                                    <label class="form-label text-xs font-weight-bolder text-uppercase">
                                        Tahun Ajaran
                                    </label>
                                    <div class="input-group input-group-outline">
                                        @php
                                            $tahunSekarang = now()->year;
                                            $bulanSekarang = now()->month;

                                            // Tentukan tahun ajaran aktif
                                            $tahunAjaranAktif = $bulanSekarang >= 7
                                                ? $tahunSekarang . '/' . ($tahunSekarang + 1)
                                                : ($tahunSekarang - 1) . '/' . $tahunSekarang;

                                            $tahunMulai = $tahunSekarang - 3; // 3 tahun ke belakang
                                            $tahunAkhir = $tahunSekarang + 3; // 3 tahun ke depan

                                            $tahunAjaranList = [];

                                            for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
                                                $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
                                            }
                                        @endphp
                                        <select name="tahun_ajaran" class="form-select" required>
                                            @foreach ($tahunAjaranList as $ta)
                                                <option value="{{ $ta }}"
                                                    {{ old('tahun_ajaran', $tahunAjaranAktif) == $ta ? 'selected' : '' }}>
                                                    {{ $ta }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>


                            {{-- BOBOT SUMATIF --}}
                            <label class="form-label text-xs font-weight-bolder text-uppercase">
                                Bobot Nilai Sumatif
                            </label>
                            <div class="input-group input-group-outline mb-4">
                                <input type="number" id="bobot_sumatif"
                                       name="bobot_sumatif"
                                       class="form-control"
                                       min="0"
                                       max="100"
                                       required>
                                <span class="input-group-text">%</span>
                            </div>

                            {{-- BOBOT PROJECT --}}
                            <label class="form-label text-xs font-weight-bolder text-uppercase">
                                Bobot Nilai Project
                            </label>
                            <div class="input-group input-group-outline mb-4">
                                <input type="number" id="bobot_project"
                                       name="bobot_project"
                                       class="form-control"
                                       min="0"
                                       max="100"
                                       required>
                                <span class="input-group-text">%</span>
                            </div>

                            {{-- INFO TOTAL --}}
                            <div id="bobot-alert" class="alert alert-warning text-sm d-none">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Total bobot saat ini <strong><span id="total-bobot">0</span>%</strong>.
                                Harus tepat <strong>100%</strong> sebelum disimpan.
                            </div>

                            {{-- ACTION --}}
                            <div class="text-end">
                                <button type="submit" id="btn-simpan" class="btn bg-gradient-primary">
                                Simpan
                                </button>
                            </div>

                        </form>

                        @include('data.partials.history_bobot')
                    </div>
                </div>
            </div>
        </div>

        <x-app.footer />
    </div>
</main>
@endsection
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sumatif = document.getElementById('bobot_sumatif');
    const project = document.getElementById('bobot_project');
    const alertBox = document.getElementById('bobot-alert');
    const totalText = document.getElementById('total-bobot');
    const btnSimpan = document.getElementById('btn-simpan');

    function validateBobot(changed) {
        let s = parseInt(sumatif.value) || 0;
        let p = parseInt(project.value) || 0;

        // Cegah lebih dari 100
        if (s + p > 100) {
            if (changed === 'sumatif') {
                sumatif.value = 100 - p;
                s = 100 - p;
            } else {
                project.value = 100 - s;
                p = 100 - s;
            }
        }

        const total = s + p;
        totalText.innerText = total;

        // Kondisi total < 100
        if (total < 100) {
            alertBox.classList.remove('d-none');
            alertBox.classList.remove('alert-success');
            alertBox.classList.add('alert-warning');
            btnSimpan.disabled = true;
        }
        // Kondisi total = 100 (AMAN)
        else if (total === 100) {
            alertBox.classList.remove('alert-warning');
            alertBox.classList.add('alert-success');
            alertBox.classList.remove('d-none');
            alertBox.innerHTML = `
                <i class="fas fa-check-circle me-1"></i>
                Total bobot sudah <strong>100%</strong>. Siap disimpan.
            `;
            btnSimpan.disabled = false;
        }
    }

    sumatif.addEventListener('input', () => validateBobot('sumatif'));
    project.addEventListener('input', () => validateBobot('project'));
});
</script>
@endpush
