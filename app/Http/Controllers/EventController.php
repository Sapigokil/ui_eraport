<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * HALAMAN UTAMA
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // 1. Auto-Update: Ubah status menjadi 'draft' (tidak aktif) jika tanggal_selesai sudah terlewati
        Event::where('status', 'aktif')
            ->whereNotNull('tanggal_selesai')
            ->where('tanggal_selesai', '<', $today)
            ->update(['status' => 'draft']);

        // 2. Sorting: 
        // - Prioritas 1: Status 'aktif' di atas (1), 'draft' di bawah (2)
        // - Prioritas 2: Jarak tanggal_selesai paling dekat dengan hari ini (menggunakan absolut selisih hari)
        $events = Event::orderByRaw("CASE WHEN status = 'aktif' THEN 1 ELSE 2 END ASC")
            ->orderByRaw("ABS(DATEDIFF(tanggal_selesai, '$today')) ASC")
            ->get();

        return view('data.event_index', compact('events'));
    }

    /**
     * SIMPAN EVENT BARU (Via Modal)
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul'           => 'required|string|max:255',
            'deskripsi'       => 'required|string',
            'tanggal'         => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal',
            'kategori'        => 'required|in:Acara,Pengumuman',
            'target'          => 'required|string',
            'status'          => 'required|in:aktif,draft',
            'file_lampiran'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('file_lampiran')) {
            $file = $request->file('file_lampiran');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $lampiranPath = $file->storeAs('lampiran_event', $filename, 'public');
        }

        Event::create([
            'judul'           => $request->judul,
            'deskripsi'       => $request->deskripsi,
            'tanggal'         => $request->tanggal,
            'tanggal_selesai' => $request->tanggal_selesai,
            'kategori'        => $request->kategori,
            'target'          => $request->target,
            'status'          => $request->status,
            'lampiran'        => $lampiranPath,
        ]);

        return back()->with('success', 'Data berhasil ditambahkan.');
    }

    /**
     * UPDATE EVENT (Via Modal)
     */
    public function updateEvent(Request $request, $id)
    {
        $request->validate([
            'judul'           => 'required|string|max:255',
            'deskripsi'       => 'required|string',
            'tanggal'         => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal',
            'kategori'        => 'required|in:Acara,Pengumuman',
            'target'          => 'required|string',
            'status'          => 'required|in:aktif,draft',
            'file_lampiran'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $event = Event::findOrFail($id);
        $lampiranPath = $event->lampiran;

        if ($request->hasFile('file_lampiran')) {
            if ($lampiranPath && Storage::disk('public')->exists($lampiranPath)) {
                Storage::disk('public')->delete($lampiranPath);
            }
            
            $file = $request->file('file_lampiran');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $lampiranPath = $file->storeAs('lampiran_event', $filename, 'public');
        }

        $event->update([
            'judul'           => $request->judul,
            'deskripsi'       => $request->deskripsi,
            'tanggal'         => $request->tanggal,
            'tanggal_selesai' => $request->tanggal_selesai,
            'kategori'        => $request->kategori,
            'target'          => $request->target,
            'status'          => $request->status,
            'lampiran'        => $lampiranPath,
        ]);

        return back()->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * DELETE EVENT
     */
    public function destroyEvent($id)
    {
        $event = Event::findOrFail($id);
        
        if ($event->lampiran && Storage::disk('public')->exists($event->lampiran)) {
            Storage::disk('public')->delete($event->lampiran);
        }
        
        $event->delete();
        
        return back()->with('success', 'Data berhasil dihapus.');
    }
}