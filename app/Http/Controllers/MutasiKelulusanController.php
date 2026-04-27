<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RiwayatKenaikanKelas;
use App\Models\PengumumanSiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MutasiKelulusanController extends Controller
{
    public function index(Request $request)
    {
        $id_kelas_asal = $request->id_kelas_asal;
        $taLama = $request->tahun_ajaran_lama ?? (date('n') >= 7 ? date('Y').'/'.(date('Y')+1) : (date('Y')-1).'/'.date('Y'));
        
        $kelasAsalTerpilih = Kelas::find($id_kelas_asal);
        if (!$kelasAsalTerpilih) {
            return redirect()->route('mutasi.kelulusan_dashboard.index')->with('error', 'Pilih kelas terlebih dahulu.');
        }

        // 1. Ambil Siswa Aktif
        $dataSiswa = Siswa::where('id_kelas', $id_kelas_asal)
            ->where('status', 'aktif')
            ->orderBy('nama_siswa', 'asc')
            ->get();

        // 2. Tarik data riwayat (untuk penanganan REVISI)
        $riwayatExisting = DB::table('riwayat_kenaikan_kelas')
            ->where('id_kelas_lama', $id_kelas_asal)
            ->where('tahun_ajaran_lama', $taLama)
            ->pluck('status', 'id_siswa')
            ->toArray();

        // 3. Gabungkan status ke data siswa & Hitung Statistik untuk Banner
        $stat = ['lulus' => 0, 'tidak_lulus' => 0, 'belum' => 0]; 
        foreach ($dataSiswa as $siswa) {
            $status = $riwayatExisting[$siswa->id_siswa] ?? ''; 
            $siswa->status_kelulusan = $status;
            
            if ($status == 'lulus') $stat['lulus']++;
            elseif ($status == 'tidak_lulus') $stat['tidak_lulus']++; 
            else $stat['belum']++;
        }

        // 4. Replikasi Logika Warna Dashboard agar Sinkron
        $words = explode(' ', trim($kelasAsalTerpilih->nama_kelas));
        $jurusan = $words[1] ?? 'UMUM';
        $hue = crc32($jurusan) % 360;
        $bg_gradient = "linear-gradient(135deg, hsl({$hue}, 75%, 55%), hsl(".($hue + 25).", 75%, 40%))";

        return view('mutasi.kelulusan_index', compact(
            'kelasAsalTerpilih', 'dataSiswa', 'id_kelas_asal', 'taLama', 'bg_gradient', 'stat'
        ));
    }

    public function store(Request $request)
    {
        $id_kelas_lama = $request->id_kelas_lama;
        $taLama = $request->tahun_ajaran_lama;
        $dataTujuan = $request->tujuan; // Array [id_siswa => status]
        $adminName = Auth::user()->name ?? 'Admin Sistem';

        DB::beginTransaction();
        try {
            foreach ($dataTujuan as $id_siswa => $keputusan) {
                
                // Jika dropdown dikosongkan (Batal Diproses)
                if (empty($keputusan)) {
                    DB::table('riwayat_kenaikan_kelas')->where('id_siswa', $id_siswa)->where('tahun_ajaran_lama', $taLama)->delete();
                    DB::table('pengumuman_siswa')->where('id_siswa', $id_siswa)->where('tahun_ajaran', $taLama)->where('jenis', 'kelulusan')->delete();
                    continue;
                }

                $isLulus = ($keputusan == 'lulus');

                // ==============================================================================
                // 👇 CARA "BRUTE FORCE" QUERY BUILDER (Mengatasi Silent Fail pada Model) 👇
                // ==============================================================================
                
                // CEK RIWAYAT
                $riwayatAda = DB::table('riwayat_kenaikan_kelas')
                    ->where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran_lama', $taLama)
                    ->exists();

                if ($riwayatAda) {
                    // Update Paksa Langsung ke Database
                    DB::table('riwayat_kenaikan_kelas')
                        ->where('id_siswa', $id_siswa)
                        ->where('tahun_ajaran_lama', $taLama)
                        ->update([
                            'id_kelas_baru' => $isLulus ? 0 : $id_kelas_lama,
                            'status' => $keputusan, 
                            'user_admin' => $adminName,
                            'status_eksekusi' => 'draft',
                            'updated_at' => now()
                        ]);
                } else {
                    // Insert Baru
                    DB::table('riwayat_kenaikan_kelas')->insert([
                        'id_siswa' => $id_siswa,
                        'tahun_ajaran_lama' => $taLama,
                        'id_kelas_lama' => $id_kelas_lama,
                        'id_kelas_baru' => $isLulus ? 0 : $id_kelas_lama,
                        'tahun_ajaran_baru' => 'LULUS',
                        'status' => $keputusan,
                        'user_admin' => $adminName,
                        'status_eksekusi' => 'draft',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // CEK PENGUMUMAN
                $pengumumanAda = DB::table('pengumuman_siswa')
                    ->where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran', $taLama)
                    ->where('jenis', 'kelulusan')
                    ->exists();

                if ($pengumumanAda) {
                    DB::table('pengumuman_siswa')
                        ->where('id_siswa', $id_siswa)
                        ->where('tahun_ajaran', $taLama)
                        ->where('jenis', 'kelulusan')
                        ->update([
                            'status_hasil' => $keputusan,
                            'user_input' => $adminName,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table('pengumuman_siswa')->insert([
                        'id_siswa' => $id_siswa,
                        'tahun_ajaran' => $taLama,
                        'jenis' => 'kelulusan',
                        'status_hasil' => $keputusan,
                        'status' => 'hold',
                        'has_seen' => 0,
                        'user_input' => $adminName,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                // ==============================================================================
            }

            DB::commit();
            return redirect()->route('mutasi.kelulusan_dashboard.index')
                ->with('success', "Data revisi kelulusan berhasil disuntikkan ke database.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update database: ' . $e->getMessage());
        }
    }
}