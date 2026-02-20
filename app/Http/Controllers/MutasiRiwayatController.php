<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiwayatKenaikanKelas;
use App\Models\Kelas; // <-- JANGAN LUPA IMPORT MODEL KELAS

class MutasiRiwayatController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil daftar Tahun Ajaran unik yang pernah ada di riwayat
        $tahunAjaranList = RiwayatKenaikanKelas::select('tahun_ajaran_lama')
                            ->distinct()
                            ->orderBy('tahun_ajaran_lama', 'desc')
                            ->pluck('tahun_ajaran_lama');

        // 2. Ambil daftar Kelas untuk Dropdown Filter
        $kelasList = Kelas::orderBy('nama_kelas', 'asc')->get();

        // 3. Query Utama dengan relasi
        $query = RiwayatKenaikanKelas::with(['siswa', 'kelasLama', 'kelasBaru'])
            ->select('riwayat_kenaikan_kelas.*');

        // 4. Filter Pencarian Text (Nama, NISN, NIPD) -> MANUAL SUBMIT
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nipd', 'like', "%{$search}%");
            });
        }

        // 5. Filter Kelas (Berdasarkan id_kelas_lama) -> AUTO SUBMIT
        if ($request->filled('id_kelas_lama')) {
            $query->where('id_kelas_lama', $request->id_kelas_lama);
        }

        // 6. Filter Tahun Ajaran Lama -> AUTO SUBMIT
        if ($request->filled('tahun_ajaran')) {
            $query->where('tahun_ajaran_lama', $request->tahun_ajaran);
        }

        // 7. Filter Status -> AUTO SUBMIT
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 8. Tentukan Jumlah Data Per Halaman (Default: 25)
        $perPage = $request->input('per_page', 25);

        // 9. Eksekusi Query dengan Pagination Dinamis
        $dataRiwayat = $query->latest('created_at')->paginate($perPage)->withQueryString();

        return view('mutasi.riwayat_index', compact('dataRiwayat', 'tahunAjaranList', 'kelasList'));
    }
}