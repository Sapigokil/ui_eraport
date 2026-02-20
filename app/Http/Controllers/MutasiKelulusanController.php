<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RiwayatKenaikanKelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MutasiKelulusanController extends Controller
{
    public function index(Request $request)
    {
        $id_kelas_asal = $request->id_kelas_asal;
        
        $kelasAsalTerpilih = Kelas::find($id_kelas_asal);
        if (!$kelasAsalTerpilih) {
            return redirect()->route('mutasi.dashboard_akhir.index')->with('error', 'Pilih kelas terlebih dahulu.');
        }

        // Ambil Siswa Aktif Saja
        $dataSiswa = Siswa::where('id_kelas', $id_kelas_asal)
            ->where('status', 'aktif')
            ->orderBy('nama_siswa', 'asc')
            ->get();

        $tahunSekarang = date('Y');
        $taLama = ($tahunSekarang - 1) . '/' . $tahunSekarang;

        return view('mutasi.kelulusan_index', compact(
            'kelasAsalTerpilih',
            'dataSiswa',
            'id_kelas_asal',
            'taLama'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kelas_lama'     => 'required|exists:kelas,id_kelas',
            'tahun_ajaran_lama' => 'required',
            'tujuan'            => 'required|array', // isinya: 'lulus' atau 'tinggal_kelas'
        ]);

        $id_kelas_lama = $request->id_kelas_lama;
        $dataTujuan = $request->tujuan; 
        $adminName = Auth::user()->name ?? 'Admin Sistem';

        DB::beginTransaction();
        try {
            $countLulus = 0;
            $countTinggal = 0;

            foreach ($dataTujuan as $id_siswa => $keputusan) {
                // 1. Catat ke tabel Riwayat
                RiwayatKenaikanKelas::create([
                    'id_siswa'          => $id_siswa,
                    'id_kelas_lama'     => $id_kelas_lama,
                    // Jika lulus, kelas baru null/0. Jika tinggal, kelas baru tetap kelas lama
                    'id_kelas_baru'     => ($keputusan == 'lulus') ? 0 : $id_kelas_lama, 
                    'tahun_ajaran_lama' => $request->tahun_ajaran_lama,
                    'tahun_ajaran_baru' => 'LULUS', // Penanda tahun kelulusan
                    'status'            => $keputusan, // 'lulus' atau 'tinggal_kelas'
                    'user_admin'        => $adminName
                ]);

                // 2. Update Data Master Siswa
                if ($keputusan == 'lulus') {
                    DB::table('siswa')->where('id_siswa', $id_siswa)->update([
                        'status'   => 'lulus'
                    ]);
                    $countLulus++;
                } else {
                    // Jika tidak lulus (Tinggal kelas), status tetap 'aktif' dan id_kelas tetap
                        DB::table('siswa')->where('id_siswa', $id_siswa)->update([
                            'status'   => 'aktif'
                        ]);
                    $countTinggal++;
                }
            }

            DB::commit();
            return redirect()->route('mutasi.dashboard_akhir.index')
                ->with('success', "Proses Kelulusan Selesai: $countLulus siswa LULUS, $countTinggal siswa TINGGAL KELAS.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}