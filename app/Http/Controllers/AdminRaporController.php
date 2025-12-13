<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\Rapor;


class AdminRaporController extends Controller
{
    public function inputRapor(Request $request)
    {
        $kelas = Kelas::all();
        $mapel = MataPelajaran::all();
        
        // VARIABEL $tahunAjaran Dihapus dari Controller!
        // $tahunAjaran = [ ... ];
        // $siswa dan $rapor tetap kosong jika filter belum lengkap

        $siswa = collect();
        $rapor = collect();

        // 1. Tentukan nama variabel yang benar dari request
        $idKelas = $request->id_kelas;
        $idMapel = $request->id_mapel;
        $tahunAjaranFilter = $request->tahun_ajaran; 
        $semesterFilter = $request->semester;


        // 2. Jika SEMUA filter dipilih
        if ($idKelas && $idMapel && $tahunAjaranFilter && $semesterFilter) {

            // Gunakan nilai string dari view/DB
            $semesterDB = $semesterFilter; 

            $siswa = Siswa::where('id_kelas', $idKelas)->get();

            $rapor = Rapor::where('id_kelas', $idKelas)
                ->where('id_mapel', $idMapel)
                // Menggunakan id_tahun_ajaran (nama kolom DB) = $tahunAjaranFilter (nilai dari View)
                ->where('id_tahun_ajaran', $tahunAjaranFilter) 
                ->where('semester', $semesterDB) 
                ->get()
                ->keyBy('id_siswa');
        }


        return view('input.rapor', [
            'kelas'         => $kelas,
            'mapel'         => $mapel,
            // VARIABEL $tahunAjaran Dihapus dari array compact!
            // 'tahunAjaran'   => $tahunAjaran, 
            'siswa'         => $siswa,
            'rapor'         => $rapor,
            'request'       => $request
        ]);
    }

    public function simpanRapor(Request $request)
    {
        $request->validate([
        'id_kelas'          => 'required',
        'id_mapel'          => 'required',
        'id_tahun_ajaran'   => 'required',
        'semester'          => 'required',
        'id_siswa'          => 'required|array',
    ]);
    $semester = $request->semester == 'ganjil' ? 1 : 2;

        foreach ($request->id_siswa as $index => $id_siswa) {

            $nilai = $request->nilai[$index];
            $capaian = $request->capaian[$index];

            // Jika keduanya kosong, skip simpan
            if ($nilai === null && (!$capaian || trim($capaian) === "")) {
            continue;
            }

            Rapor::updateOrCreate(
                [
                    'id_siswa'        => $id_siswa,
                    'id_mapel'        => $request->id_mapel,
                    'id_tahun_ajaran' => $request->id_tahun_ajaran,
                    'semester'        => $semester,
                ],
                [
                    'id_kelas' => $request->id_kelas,
                    'nilai'    => $nilai,
                    'capaian'  => $capaian,
                ]
            );
        }

        // Redirect agar data tampil kembali setelah saving
        return redirect()->route('input.rapor', [
            'id_kelas'        => $request->id_kelas,
            'id_mapel'        => $request->id_mapel,
            'id_tahun_ajaran' => $request->id_tahun_ajaran,
            'semester'        => $request->semester,
        ])->with('success', 'Nilai berhasil disimpan!');
    }

    public function indexProgress(Request $request) 
    {
        // --- Data Pendukung ---
        $kelasList = Kelas::all(); 
        $mapelList = MataPelajaran::all(); 

        // --- LOGIKA QUERY DENGAN FILTER ---
        $query = Rapor::select([
            'id_kelas', 
            'id_mapel', 
            'id_tahun_ajaran', 
            'semester',
            DB::raw('COUNT(id_rapor) as total_siswa_dinilai') 
        ]);
        
        // Terapkan Filter KELAS (Tanpa Default)
        if ($request->id_kelas) {
            $query->where('id_kelas', $request->id_kelas);
        } 
        
        // Terapkan Filter Mata Pelajaran (Tanpa Default)
        if ($request->id_mapel) {
            $query->where('id_mapel', $request->id_mapel);
        }
        
        // Terapkan Filter Tahun Ajaran (Murni berdasarkan Request dari View)
        if ($request->tahun_ajaran) {
            $query->where('id_tahun_ajaran', $request->tahun_ajaran);
        } 
        
        // Terapkan Filter Semester (Murni berdasarkan Request dari View)
        if ($request->semester) {
            $query->where('semester', $request->semester);
        } 
        
        $progressData = $query->groupBy('id_kelas', 'id_mapel', 'id_tahun_ajaran', 'semester')
                            ->get();

        // Mapping Data
        $kelasMap = Kelas::pluck('nama_kelas', 'id_kelas');
        $mapelMap = MataPelajaran::pluck('nama_mapel', 'id_mapel');
        $siswaPerKelas = Siswa::select('id_kelas', DB::raw('COUNT(*) as total'))->groupBy('id_kelas')->get()->keyBy('id_kelas');

        $progress = $progressData->map(function ($item) use ($kelasMap, $mapelMap, $siswaPerKelas) {
            // ... (Logika mapping tetap sama) ...
            $totalSiswaDiKelas = $siswaPerKelas[$item->id_kelas]->total ?? 0;
            
            return [
                'nama_kelas' => $kelasMap[$item->id_kelas] ?? 'Kelas Dihapus',
                'nama_mapel' => $mapelMap[$item->id_mapel] ?? 'Mapel Dihapus',
                'tahun_ajaran' => $item->id_tahun_ajaran,
                'semester' => $item->semester,
                'dinilai' => $item->total_siswa_dinilai,
                'total_siswa' => $totalSiswaDiKelas,
                'persen' => $totalSiswaDiKelas > 0 ? round(($item->total_siswa_dinilai / $totalSiswaDiKelas) * 100) : 0,
                'id_kelas' => $item->id_kelas, 
                'id_mapel' => $item->id_mapel,
            ];
        });


        // Kirim semua data yang diperlukan ke view
        return view('input.dbrapor', compact(
            'progress', 
            'kelasList', 
            'mapelList'
        ));
    }
}
