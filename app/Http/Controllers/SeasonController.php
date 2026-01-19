<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SeasonController extends Controller
{
    /**
     * Tampilkan daftar season
     */
    public function index()
    {
        $seasons = Season::orderBy('created_at', 'desc')->get();
        $activeSeason = Season::where('is_active', 1)->first();

        return view('data.partials.season', compact('seasons', 'activeSeason'));
    }

    /**
     * Simpan season baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required|string|max:20',
            'semester'     => 'required|in:1,2',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
        ]);

        Season::where('is_active', 1)->update(['is_active' => 0]);

        $season = Season::create([
            'tahun_ajaran' => $request->tahun_ajaran,
            'semester'     => $request->semester,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'is_open'      => 1,
            'is_active'    => 1,
        ]);

        return back()->with('success', 'Season berhasil dibuat & diaktifkan');
    }

    /**
     * Update status season (manual override)
     */
    public function update(Request $request, $id)
{
    $season = Season::findOrFail($id);

    // Jika ada input is_open → update status manual
    if ($request->has('is_open')) {
        $request->validate([
            'is_open' => 'required|boolean',
        ]);
        $season->update([
            'is_open' => $request->is_open,
        ]);
        return back()->with('success', 'Status season diperbarui');
    }

    // Jika ada input tahun_ajaran → update data season dari modal edit
    $request->validate([
        'tahun_ajaran' => 'required|string|max:20',
        'semester'     => 'required|in:1,2',
        'start_date'   => 'required|date',
        'end_date'     => 'required|date|after_or_equal:start_date',
    ]);

    $season->update([
        'tahun_ajaran' => $request->tahun_ajaran,
        'semester'     => $request->semester,
        'start_date'   => $request->start_date,
        'end_date'     => $request->end_date,
    ]);

    return back()->with('success', 'Season berhasil diperbarui');
}

    /**
     * Hapus season
     */
    public function destroy($id)
    {
        $season = Season::findOrFail($id);
        $season->delete();

        return back()->with('success', 'Season berhasil dihapus');
    }

    /**
     * Helper: cek season aktif saat ini
     */
    public static function currentSeason(): ?Season
    {
        $today = Carbon::today();

        return Season::where('is_active', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
    }
}
