<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiwayatKenaikanKelas;
use App\Models\Kelas; 

class MutasiRiwayatController extends Controller
{
    public function index(Request $request)
    {
        // 1. 👇 PERUBAHAN: Hanya ambil TA dari data yang sudah final (Agar Dropdown tidak kosong datanya) 👇
        $tahunAjaranList = RiwayatKenaikanKelas::where('status_eksekusi', 'final')
                            ->select('tahun_ajaran_lama')
                            ->distinct()
                            ->orderBy('tahun_ajaran_lama', 'desc')
                            ->pluck('tahun_ajaran_lama');

        // Set Default TA ke yang paling terbaru jika belum memilih
        $taDefault = $tahunAjaranList->first() ?? date('Y') . '/' . (date('Y') + 1);
        $ta = $request->input('tahun_ajaran', $taDefault);

        // 2. Ekstrak daftar Tingkat yang unik dari master Kelas
        $tingkatList = Kelas::pluck('nama_kelas')->map(function($nama) {
            preg_match('/^\d+/', $nama, $matches);
            return !empty($matches) ? (int)$matches[0] : null;
        })->filter()->unique()->sort()->values();

        // 3. 👇 PERUBAHAN KRUSIAL: Gembok Query agar mutlak HANYA menarik yang Final 👇
        $query = RiwayatKenaikanKelas::with(['siswa', 'kelasLama', 'kelasBaru'])
            ->where('tahun_ajaran_lama', $ta)
            ->where('status_eksekusi', 'final'); 

        // 4. Filter Pencarian Text 
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nipd', 'like', "%{$search}%");
            });
        }

        // 5. Filter berdasarkan Tingkat 
        if ($request->filled('tingkat')) {
            $reqTingkat = $request->tingkat;
            $kelasIds = Kelas::get()->filter(function($k) use ($reqTingkat) {
                preg_match('/^\d+/', $k->nama_kelas, $matches);
                return (!empty($matches) && (int)$matches[0] == $reqTingkat);
            })->pluck('id_kelas');
            
            $query->whereIn('id_kelas_lama', $kelasIds);
        }

        // 6. Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 7. Ambil semua data
        $allRiwayat = $query->latest('created_at')->get();

        // 8. Kelompokkan data berdasarkan Kelas Asal
        $groupedRiwayat = $allRiwayat->groupBy('id_kelas_lama')->map(function($items, $id_kelas) {
            $namaKelas = $items->first()->kelasLama->nama_kelas ?? 'Kelas Tidak Diketahui / Dihapus';
            
            return [
                'id_kelas' => $id_kelas ?: 'unknown',
                'nama_kelas' => $namaKelas,
                'total_siswa' => $items->count(),
                'lulus' => $items->where('status', 'lulus')->count(),
                'tidak_lulus' => $items->where('status', 'tidak_lulus')->count(),
                'naik' => $items->where('status', 'naik_kelas')->count(),
                'tinggal' => $items->where('status', 'tinggal_kelas')->count(),
                'mutasi_keluar' => $items->where('status', 'mutasi_keluar')->count(), 
                'detail_siswa' => $items 
            ];
        })->sortBy('nama_kelas')->values(); 

        return view('mutasi.riwayat_index', compact('groupedRiwayat', 'tahunAjaranList', 'tingkatList', 'ta'));
    }
}