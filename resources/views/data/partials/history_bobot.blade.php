<hr class="my-4">

<h6 class="text-uppercase text-sm font-weight-bolder mb-3">
    History Pengaturan Bobot Nilai
</h6>

<div class="table-responsive">
    <table class="table table-hover align-items-center mb-0 table-history-bobot">
        <thead class="bg-gradient-primary text-white text-sm">
            <tr>
                <th class="text-center">No</th>
                <th>Jumlah Sumatif</th>
                <th>Semester</th>
                <th>Tahun Ajaran</th>
                <th class="text-center">Bobot Sumatif</th>
                <th class="text-center">Bobot Project</th>
                <th class="text-center">Dibuat</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>

        <tbody class="text-sm">
            @forelse ($historyBobot as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>Sumatif {{ $item->jumlah_sumatif }}</td>
                    <td>{{ $item->semester }}</td>
                    <td>{{ $item->tahun_ajaran }}</td>
                    <td class="text-center">{{ $item->bobot_sumatif }}%</td>
                    <td class="text-center">{{ $item->bobot_project }}%</td>
                    <td class="text-center">{{ $item->created_at->format('d-m-Y H:i') }}</td>
                    <td class="text-center">

                        {{-- EDIT --}}
                        <button type="button"
                                class="btn btn-link text-warning px-1"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal{{ $item->id }}">
                            <i class="fas fa-edit"></i>
                        </button>


                        {{-- HAPUS --}}
                        <button type="button"
                                class="btn btn-link text-danger px-1"
                                data-bs-toggle="modal"
                                data-bs-target="#hapusModal{{ $item->id }}">
                            <i class="fas fa-trash"></i>
                        </button>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        Belum ada data pengaturan bobot nilai
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{-- MODAL EDIT --}}
@foreach ($historyBobot as $item)
    <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <form action="{{ route('pengaturan.bobot.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-header bg-gradient-primary">
                        <h6 class="modal-title text-white">Edit Pengaturan Bobot Nilai</h6>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body px-4 py-4">

                        <div class="row mb-4">

                            {{-- JUMLAH SUMATIF --}}
                            <div class="col-md-4">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">
                                    Jumlah Sumatif
                                </label>
                                <div class="input-group input-group-outline">
                                    <select name="jumlah_sumatif" class="form-select" required>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}"
                                                {{ $item->jumlah_sumatif == $i ? 'selected' : '' }}>
                                                Sumatif {{ $i }}
                                            </option>
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
                                        <option value="GANJIL" {{ $item->semester == 'GANJIL' ? 'selected' : '' }}>
                                            Ganjil
                                        </option>
                                        <option value="GENAP" {{ $item->semester == 'GENAP' ? 'selected' : '' }}>
                                            Genap
                                        </option>
                                    </select>
                                </div>
                            </div>

                            {{-- TAHUN AJARAN --}}
                            <div class="col-md-4">
                                <label class="form-label text-xs font-weight-bolder text-uppercase">
                                    Tahun Ajaran
                                </label>
                                <div class="input-group input-group-outline">
                                    <input type="text"
                                        name="tahun_ajaran"
                                        class="form-control"
                                        value="{{ $item->tahun_ajaran }}"
                                        required>
                                </div>
                            </div>

                        </div>

                        {{-- BOBOT SUMATIF --}}
                        <label class="form-label text-xs font-weight-bolder text-uppercase">
                            Bobot Nilai Sumatif
                        </label>
                        <div class="input-group input-group-outline mb-4">
                            <input type="number"
                                name="bobot_sumatif"
                                class="form-control"
                                min="0"
                                max="100"
                                value="{{ $item->bobot_sumatif }}"
                                required>
                            <span class="input-group-text">%</span>
                        </div>

                        {{-- BOBOT PROJECT --}}
                        <label class="form-label text-xs font-weight-bolder text-uppercase">
                            Bobot Nilai Project
                        </label>
                        <div class="input-group input-group-outline mb-4">
                            <input type="number"
                                name="bobot_project"
                                class="form-control"
                                min="0"
                                max="100"
                                value="{{ $item->bobot_project }}"
                                required>
                            <span class="input-group-text">%</span>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit"
                                class="btn bg-gradient-primary">
                            Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endforeach

    {{-- MODAL HAPUS --}}
    {{-- MODAL HAPUS --}}
@foreach ($historyBobot as $item)
    <div class="modal fade" id="hapusModal{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-gradient-danger">
                    <h6 class="modal-title text-white">
                        <i class="fas fa-trash-alt me-2"></i>
                        Konfirmasi Hapus Data
                    </h6>
                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body px-4 py-4 text-center">

                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle text-danger fs-1"></i>
                    </div>

                    <p class="mb-3">
                        Apakah Anda yakin ingin menghapus pengaturan berikut?
                    </p>

                    <div class="border rounded p-3 mb-3 text-sm">
                        <strong>Sumatif {{ $item->jumlah_sumatif }}</strong><br>
                        Semester <strong>{{ $item->semester }}</strong><br>
                        Tahun Ajaran <strong>{{ $item->tahun_ajaran }}</strong>
                    </div>

                    <p class="text-danger text-sm mb-0">
                        Data yang dihapus <strong>tidak dapat dikembalikan</strong>.
                    </p>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <form action="{{ route('pengaturan.bobot.destroy', $item->id) }}"
                        method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn bg-gradient-danger">
                            Ya, Hapus
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endforeach


</div>
