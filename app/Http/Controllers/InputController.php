<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Notifikasi;

class InputController extends Controller
{
    /**
     * HALAMAN UTAMA
     */
    public function index()
    {
        $events = Event::orderBy('tanggal', 'desc')->get();
        $notifications = Notifikasi::orderBy('tanggal', 'desc')->get();

        return view('data.input_index', compact('events', 'notifications'));
    }

    /**
     * SIMPAN EVENT / NOTIFIKASI (SATU FORM)
     */
    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required|in:event,notifikasi',
            'tanggal'  => 'required|date',
        ]);

        if ($request->kategori === 'event') {
            $request->validate([
                'deskripsi' => 'required|string',
                'jadwalkan' => 'required|in:1_hari,3_hari,7_hari,15_hari,1_bulan',
            ]);

            Event::create([
                'deskripsi' => $request->deskripsi,
                'tanggal'   => $request->tanggal,
                'jadwalkan' => $request->jadwalkan,
            ]);

            return back()->with('success', 'Event berhasil ditambahkan');
        }

        // NOTIFIKASI
        $request->validate([
        'deskripsi' => 'required|string',
        'tanggal'   => 'required|date',
    ]);

        Notifikasi::create([
        'deskripsi' => $request->deskripsi,
        'tanggal'   => $request->tanggal,
        'kategori'  => 'notifikasi',
    ]);

        return back()->with('success', 'Notifikasi berhasil ditambahkan');
    }

    /**
     * UPDATE EVENT
     */
    public function updateEvent(Request $request, $id)
    {
        $request->validate([
            'deskripsi' => 'required|string',
            'tanggal'   => 'required|date',
        ]);

        Event::findOrFail($id)->update([
            'deskripsi' => $request->deskripsi,
            'tanggal'   => $request->tanggal,
        ]);

        return back()->with('success', 'Event berhasil diperbarui');
    }

    /**
     * UPDATE NOTIFIKASI
     */
    public function updateNotifikasi(Request $request, $id)
    {
        $request->validate([
            'judul'   => 'required|string',
            'pesan'   => 'required|string',
            'tanggal' => 'required|date',
        ]);

        Notifikasi::findOrFail($id)->update([
            'judul'   => $request->judul,
            'pesan'   => $request->pesan,
            'tanggal' => $request->tanggal,
        ]);

        return back()->with('success', 'Notifikasi berhasil diperbarui');
    }

    /**
     * DELETE EVENT
     */
    public function destroyEvent($id)
    {
        Event::findOrFail($id)->delete();
        return back()->with('success', 'Event berhasil dihapus');
    }

    /**
     * DELETE NOTIFIKASI
     */
    public function destroyNotifikasi($id)
    {
        Notifikasi::findOrFail($id)->delete();
        return back()->with('success', 'Notifikasi berhasil dihapus');
    }
}
