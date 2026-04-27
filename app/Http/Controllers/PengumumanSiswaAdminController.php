<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengumumanSiswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class PengumumanSiswaAdminController extends Controller
{
    public function indexNaikLulus(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $query = PengumumanSiswa::with(['siswa', 'siswa.kelas']);

        if ($request->filled('id_kelas')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_kelas', $request->id_kelas);
            });
        }

        if ($request->filled('status_baca')) {
            $query->where('has_seen', $request->status_baca == 'sudah' ? 1 : 0);
        }

        // Filter Berdasarkan Status (Published/Hold)
        if ($request->filled('status_publish')) {
            $query->where('status', $request->status_publish);
        }

        $pengumumanList = $query->orderBy('created_at', 'desc')->paginate(20);

        $statTotal = PengumumanSiswa::count();
        $statSudahBaca = PengumumanSiswa::where('has_seen', 1)->count();
        $statBelumBaca = $statTotal - $statSudahBaca;
        $persenBaca = $statTotal > 0 ? round(($statSudahBaca / $statTotal) * 100) : 0;

        return view('mutasi.pengumuman.naiklulus_index', compact(
            'pengumumanList', 'kelas', 'statTotal', 'statSudahBaca', 'statBelumBaca', 'persenBaca'
        ));
    }

    /**
     * Aksi Massal untuk Mengubah Status Pengumuman
     */
    public function updateStatusMassal(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'action' => 'required|in:published,hold'
        ]);

        PengumumanSiswa::whereIn('id', $request->ids)->update([
            'status'     => $request->action,
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Status pengumuman berhasil diperbarui.']);
    }
}