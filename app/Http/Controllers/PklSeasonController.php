<?php

namespace App\Http\Controllers;

use App\Models\PklSeason;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PklSeasonController extends Controller
{
    public function index()
    {
        $seasons = PklSeason::orderBy('created_at', 'desc')->get();
        $activeSeason = PklSeason::where('is_active', 1)->first();

        return view('pkl.setting.season', compact('seasons', 'activeSeason'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required|string|max:20',
            'semester'     => 'required|in:1,2',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
        ]);

        // Matikan season lain yang sedang aktif agar tidak bentrok
        PklSeason::where('is_active', 1)->update(['is_active' => 0]);

        PklSeason::create([
            'tahun_ajaran' => $request->tahun_ajaran,
            'semester'     => $request->semester,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'is_open'      => 1,
            'is_active'    => 1,
        ]);

        return back()->with('success', 'Season Rapor PKL berhasil dibuat dan otomatis diaktifkan.');
    }

    public function update(Request $request, $id)
    {
        $season = PklSeason::findOrFail($id);

        // Jika request hanya berupa toggle "Status (is_open)" dari tabel dropdown
        if ($request->has('is_open')) {
            $request->validate([
                'is_open' => 'required|boolean',
            ]);
            
            $season->update(['is_open' => $request->is_open]);
            
            return back()->with('success', 'Status keterbukaan input nilai PKL berhasil diperbarui.');
        }

        // Jika request berupa update data lengkap dari Modal Edit
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

        return back()->with('success', 'Data Season Rapor PKL berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $season = PklSeason::findOrFail($id);
        $season->delete();

        return back()->with('success', 'Season Rapor PKL berhasil dihapus dari sistem.');
    }
}