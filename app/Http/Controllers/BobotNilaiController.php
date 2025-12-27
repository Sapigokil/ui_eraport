<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BobotNilai;

class BobotNilaiController extends Controller
{
    public function index(Request $request)
    {
        // Generate list tahun ajaran (5 tahun ke belakang & depan)
        $currentYear = now()->year;
        $tahunAjaranList = [];

        for ($i = 0; $i < 5; $i++) {
            $start = $currentYear + $i;
            $tahunAjaranList[] = $start . '/' . ($start + 1);
        }

        $defaultTahunAjaran = $request->tahun_ajaran ?? $tahunAjaranList[0];

        return view('data.bobot_index', compact(
            'tahunAjaranList',
            'defaultTahunAjaran'
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
                'tahun_ajaran' => now()->year,
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

}
