<hr class="my-4">

<h6 class="text-uppercase text-sm font-weight-bolder mb-3">
    History Pengaturan Bobot Nilai
</h6>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-items-center mb-0">
        <thead class="bg-gradient-primary text-white text-sm">
            <tr>
                <th class="text-center">No</th>
                <th>Jumlah Sumatif</th>
                <th>Semester</th>
                <th>Tahun Ajaran</th>
                <th class="text-center">Bobot Sumatif</th>
                <th class="text-center">Bobot Project</th>
                <th class="text-center">Dibuat</th>
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
                    <td class="text-center">
                        {{ $item->created_at->format('d-m-Y H:i') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        Belum ada data pengaturan bobot nilai
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
