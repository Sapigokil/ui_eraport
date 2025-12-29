<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Notifikasi;

class InputController extends Controller
{
    /**
     * Menampilkan halaman input event & notifikasi
     */
    public function index()
    {
        $events = Event::orderBy('tanggal', 'desc')->get();
        $notifikasis = Notifikasi::orderBy('tanggal', 'desc')->get();

        return view('data.input_index', compact('events', 'notifikasis'));
    }

    /**
     * Menyimpan event atau notifikasi
     */
    public function storeEvent(Request $request)
    {
        $request->validate([
            'kategori'  => 'required|in:event,notifikasi',
            'deskripsi' => 'required|string',
            'tanggal'   => 'required|date',
        ]);

        if ($request->kategori === 'event') {
            Event::create([
                'deskripsi' => $request->deskripsi,
                'tanggal'   => $request->tanggal,
            ]);

            return redirect()
                ->route('pengaturan.input.index')
                ->with('success', 'Event berhasil ditambahkan');
        }

        Notifikasi::create([
            'deskripsi' => $request->deskripsi,
            'tanggal'   => $request->tanggal,
        ]);

        return redirect()
            ->route('pengaturan.input.index')
            ->with('success', 'Notifikasi berhasil ditambahkan');
    }

    /**
     * Menghapus event / notifikasi
     */
    public function destroy($id)
    {
        // cek di tabel event
        $event = Event::find($id);
        if ($event) {
            $event->delete();
            return back()->with('success', 'Event berhasil dihapus');
        }

        // cek di tabel notifikasi
        $notifikasi = Notifikasi::find($id);
        if ($notifikasi) {
            $notifikasi->delete();
            return back()->with('success', 'Notifikasi berhasil dihapus');
        }

        return back()->with('error', 'Data tidak ditemukan');
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'deskripsi' => 'required|string',
        'tanggal'   => 'required|date',
    ]);

    $event = Event::findOrFail($id);
    $event->update([
        'deskripsi' => $request->deskripsi,
        'tanggal'   => $request->tanggal,
    ]);

    return redirect()->back()->with('success', 'Event berhasil diperbarui');
}

}
