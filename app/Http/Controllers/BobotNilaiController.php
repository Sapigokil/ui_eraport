<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BobotNilai;

class BobotNilaiController extends Controller
{
    public function index(Request $request)
    {
        $historyBobot = BobotNilai::orderBy('created_at', 'desc')->get();

        $editData = null;
        if ($request->filled('edit')) {
            $editData = BobotNilai::findOrFail($request->edit);
        }

        return view('data.bobot_index', compact(
            'editData',
            'historyBobot'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jumlah_sumatif' => 'required|integer|min:1|max:5',
            'bobot_sumatif'  => 'required|integer|min:0|max:100',
            'bobot_project'  => 'required|integer|min:0|max:100',
        ]);

        if (($request->bobot_sumatif + $request->bobot_project) !== 100) {
            return back()->withErrors([
                'total' => 'Total bobot Sumatif + Project harus 100%'
            ]);
        }

         $semester = in_array(date('n'), [7,8,9,10,11,12])
            ? 'GANJIL'
            : 'GENAP';
        $tahunAwal   = now()->year;
        $tahunAjaran = $tahunAwal . '/' . ($tahunAwal + 1);

        BobotNilai::updateOrCreate(
            [
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester' => $request->semester,
            ],
            [
                'jumlah_sumatif' => $request->jumlah_sumatif,
                'bobot_sumatif'  => $request->bobot_sumatif,
                'bobot_project'  => $request->bobot_project,
            ]
        );

        return back()->with('success', 'Bobot nilai berhasil disimpan.');
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'jumlah_sumatif' => 'required|integer|min:1|max:5',
        'semester'       => 'required',
        'tahun_ajaran'   => 'required',
        'bobot_sumatif'  => 'required|integer|min:0|max:100',
        'bobot_project'  => 'required|integer|min:0|max:100',
    ]);

    if (($request->bobot_sumatif + $request->bobot_project) !== 100) {
        return back()->withErrors([
            'total' => 'Total bobot Sumatif + Project harus 100%'
        ]);
    }

    $bobot = BobotNilai::findOrFail($id);

    $bobot->update([
        'jumlah_sumatif' => $request->jumlah_sumatif,
        'semester'       => $request->semester,
        'tahun_ajaran'   => $request->tahun_ajaran,
        'bobot_sumatif'  => $request->bobot_sumatif,
        'bobot_project'  => $request->bobot_project,
    ]);

    return redirect()
        ->route('pengaturan.bobot.index')
        ->with('success', 'Data bobot berhasil diperbarui');
}


    public function destroy($id)
    {
        BobotNilai::findOrFail($id)->delete();

        return redirect()
            ->route('pengaturan.bobot.index')
            ->with('success', 'Data bobot berhasil dihapus');
    }


}
