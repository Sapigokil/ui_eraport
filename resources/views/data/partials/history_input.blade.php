@if($events->count())
<hr class="horizontal dark my-4">

<h6 class="text-uppercase text-xs font-weight-bolder mb-3">
    History Upcoming Event
</h6>

<ul class="list-group">
@foreach ($events as $event)
    <li class="list-group-item d-flex justify-content-between align-items-start">
    <div class="me-3">
        <p class="mb-1 text-dark">
            {{ $event->deskripsi }}
        </p>
        <small class="text-muted">
            <i class="fa-regular fa-calendar me-1"></i>
            {{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}
        </small>
    </div>

    <div class="d-flex gap-2">
        <button type="button"
    class="border-0 bg-transparent text-warning p-0"
    data-bs-toggle="modal"
    data-bs-target="#editEvent{{ $event->id_event }}">
    <i class="fa-solid fa-pen fa-xs"></i>
</button>

        <form action="{{ route('pengaturan.input.event.delete', $event->id_event) }}"
            method="POST"
            onsubmit="return confirm('Yakin hapus event ini?')">
            @csrf
            @method('DELETE')
            <button type="submit"
        class="border-0 bg-transparent text-danger p-0">
        <i class="fa-solid fa-trash fa-xs"></i>
    </button>
        </form>
    </div>
</li>

@endforeach
</ul>
@endif

@if($notifications->count())
<hr class="horizontal dark my-4">

<h6 class="text-uppercase text-xs font-weight-bolder mb-3">
    History Notifikasi
</h6>

<ul class="list-group">
@foreach ($notifications as $notif)
    <li class="list-group-item d-flex justify-content-between align-items-start">
    <div class="me-3">
        <p class="mb-1 fw-normal text-dark">
            {{ $notif->deskripsi }}
        </p>
        <small class="text-muted">
            <i class="fa-regular fa-bell me-1"></i>
            {{ \Carbon\Carbon::parse($notif->tanggal)->format('d M Y') }}
        </small>
    </div>

    <div class="d-flex gap-2">
        {{-- EDIT --}}
        <button type="button"
    class="border-0 bg-transparent text-warning p-0"
    data-bs-toggle="modal"
    data-bs-target="#editNotif{{ $notif->id_notifikasi }}">
    <i class="fa-solid fa-pen fa-xs"></i>
</button>


        {{-- DELETE --}}
        <form action="{{ route('pengaturan.input.notifikasi.delete', $notif->id_notifikasi) }}"
            method="POST"
            onsubmit="return confirm('Yakin hapus notifikasi ini?')">
            @csrf
            @method('DELETE')
            <button type="submit"
    class="border-0 bg-transparent text-danger p-0">
    <i class="fa-solid fa-trash fa-xs"></i>
</button>

        </form>
    </div>
</li>

@endforeach
</ul>
@endif
