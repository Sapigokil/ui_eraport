<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RiwayatKenaikanKelas;
use App\Models\PengumumanSiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MutasiKenaikanController extends Controller
{
    public function index(Request $request)
    {
        $id_kelas_asal = $request->id_kelas_asal;
        $taLama = $request->tahun_ajaran_lama ?? (date('n') >= 7 ? date('Y').'/'.(date('Y')+1) : (date('Y')-1).'/'.date('Y'));
        $taBaru = (substr($taLama, 0, 4) + 1) . '/' . (substr($taLama, 5, 4) + 1); // Menghitung TA Baru Otomatis

        $semuaKelas = Kelas::all()->map(function($k) {
            preg_match('/^\d+/', $k->nama_kelas, $matches);
            $k->tingkat = !empty($matches) ? (int)$matches[0] : 0;
            return $k;
        });

        $kelasAsalTerpilih = $semuaKelas->firstWhere('id_kelas', $id_kelas_asal);
        
        if (!$kelasAsalTerpilih) {
            return redirect()->route('mutasi.kenaikan_dashboard.index')->with('error', 'Pilih kelas terlebih dahulu.');
        }

        // 1. Data Siswa Aktif
        $dataSiswa = Siswa::where('id_kelas', $id_kelas_asal)
            ->where('status', 'aktif') 
            ->orderBy('nama_siswa', 'asc')
            ->get();

        // 2. Prediksi Kelas Tujuan
        $tingkatAsal = $kelasAsalTerpilih->tingkat;
        $tingkatTujuan = $tingkatAsal + 1;

        $pilihanKelasTujuan = $semuaKelas->filter(function($k) use ($id_kelas_asal, $tingkatTujuan) {
            return $k->id_kelas == $id_kelas_asal || $k->tingkat === $tingkatTujuan;
        })->sortBy([
            ['tingkat', 'asc'],
            ['nama_kelas', 'asc']
        ]);

        $namaKelasAsal = $kelasAsalTerpilih->nama_kelas;
        $tebakanNamaTujuan = preg_replace('/^'.$tingkatAsal.'/', $tingkatTujuan, $namaKelasAsal, 1);
        $kelasTebakan = $pilihanKelasTujuan->firstWhere('nama_kelas', $tebakanNamaTujuan);
        $idKelasDefaultTujuan = $kelasTebakan ? $kelasTebakan->id_kelas : null;

        // 3. Tarik Riwayat Kenaikan (Untuk Revisi) menggunakan DB Query Builder
        $riwayatExisting = DB::table('riwayat_kenaikan_kelas')
            ->where('id_kelas_lama', $id_kelas_asal)
            ->where('tahun_ajaran_lama', $taLama)
            ->get()
            ->keyBy('id_siswa');

        $stat = ['naik' => 0, 'tinggal' => 0, 'belum' => 0];

        foreach ($dataSiswa as $siswa) {
            $riwayat = $riwayatExisting->get($siswa->id_siswa);
            if ($riwayat) {
                $siswa->status_kenaikan = $riwayat->status; 
                $siswa->id_kelas_tujuan = $riwayat->id_kelas_baru;
                
                if ($riwayat->status == 'naik_kelas') $stat['naik']++;
                else $stat['tinggal']++;
            } else {
                $siswa->status_kenaikan = 'belum';
                $siswa->id_kelas_tujuan = '';
                $stat['belum']++;
            }
        }

        // 4. Replikasi Logika Warna Dashboard
        $maxTingkat = $semuaKelas->max('tingkat');
        $words = explode(' ', trim($kelasAsalTerpilih->nama_kelas));
        $jurusan = $words[1] ?? 'UMUM';
        $hue = crc32($jurusan) % 360;
        
        if ($kelasAsalTerpilih->tingkat == $maxTingkat - 1) {
            $l1 = 65; $l2 = 50; 
        } else {
            $l1 = 75; $l2 = 60; 
        }
        $bg_gradient = "linear-gradient(135deg, hsl({$hue}, 75%, {$l1}%), hsl(".($hue + 25).", 75%, {$l2}%))";

        return view('mutasi.kenaikan_index', compact(
            'id_kelas_asal', 'dataSiswa', 'pilihanKelasTujuan', 'kelasAsalTerpilih', 
            'idKelasDefaultTujuan', 'taLama', 'taBaru', 'bg_gradient', 'stat'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kelas_lama'     => 'required|exists:kelas,id_kelas',
            'tahun_ajaran_lama' => 'required',
            'tahun_ajaran_baru' => 'required',
            'tujuan'            => 'required|array', 
        ]);

        $id_kelas_lama = $request->id_kelas_lama;
        $taLama = $request->tahun_ajaran_lama;
        $taBaru = $request->tahun_ajaran_baru;
        $dataTujuan = $request->tujuan; 
        
        $adminName = Auth::user()->name ?? 'Admin Sistem';

        DB::beginTransaction();
        try {
            $countProses = 0;

            foreach ($dataTujuan as $id_siswa => $keputusan) {
                // Jika kosong (Belum diproses), hapus draf sebelumnya
                if (empty($keputusan)) {
                    DB::table('riwayat_kenaikan_kelas')->where('id_siswa', $id_siswa)->where('tahun_ajaran_lama', $taLama)->delete();
                    DB::table('pengumuman_siswa')->where('id_siswa', $id_siswa)->where('tahun_ajaran', $taLama)->where('jenis', 'kenaikan')->delete();
                    continue;
                }

                $isNaik = ($keputusan !== 'tinggal');
                $id_kelas_baru = $isNaik ? $keputusan : $id_kelas_lama;
                $status_kenaikan = $isNaik ? 'naik_kelas' : 'tinggal_kelas';

                // ==============================================================================
                // 👇 CARA "BRUTE FORCE" AGAR MENGHINDARI SILENT FAIL ELOQUENT 👇
                // ==============================================================================
                
                // 1. CEK & SIMPAN RIWAYAT KENAIKAN
                $riwayatAda = DB::table('riwayat_kenaikan_kelas')
                    ->where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran_lama', $taLama)
                    ->exists();

                if ($riwayatAda) {
                    DB::table('riwayat_kenaikan_kelas')
                        ->where('id_siswa', $id_siswa)
                        ->where('tahun_ajaran_lama', $taLama)
                        ->update([
                            'id_kelas_baru'     => $id_kelas_baru,
                            'tahun_ajaran_baru' => $taBaru,
                            'status'            => $status_kenaikan,
                            'user_admin'        => $adminName,
                            'status_eksekusi'   => 'draft', // Draf sebelum dieksekusi di akhir tahun
                            'updated_at'        => now()
                        ]);
                } else {
                    DB::table('riwayat_kenaikan_kelas')->insert([
                        'id_siswa'          => $id_siswa,
                        'tahun_ajaran_lama' => $taLama,
                        'id_kelas_lama'     => $id_kelas_lama,
                        'id_kelas_baru'     => $id_kelas_baru,
                        'tahun_ajaran_baru' => $taBaru,
                        'status'            => $status_kenaikan,
                        'user_admin'        => $adminName,
                        'status_eksekusi'   => 'draft',
                        'created_at'        => now(),
                        'updated_at'        => now()
                    ]);
                }

                // 2. CEK & SIMPAN PENGUMUMAN SISWA
                $pengumumanAda = DB::table('pengumuman_siswa')
                    ->where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran', $taLama)
                    ->where('jenis', 'kenaikan')
                    ->exists();

                if ($pengumumanAda) {
                    DB::table('pengumuman_siswa')
                        ->where('id_siswa', $id_siswa)
                        ->where('tahun_ajaran', $taLama)
                        ->where('jenis', 'kenaikan')
                        ->update([
                            'status_hasil'  => $isNaik ? 'naik' : 'tinggal',
                            'user_input'    => $adminName,
                            'updated_at'    => now()
                        ]);
                } else {
                    DB::table('pengumuman_siswa')->insert([
                        'id_siswa'      => $id_siswa,
                        'tahun_ajaran'  => $taLama,
                        'jenis'         => 'kenaikan',
                        'status_hasil'  => $isNaik ? 'naik' : 'tinggal',
                        'status'        => 'hold',
                        'has_seen'      => 0, 
                        'user_input'    => $adminName,
                        'created_at'    => now(),
                        'updated_at'    => now()
                    ]);
                }
                // ==============================================================================

                $countProses++;
            }

            DB::commit();
            return redirect()->route('mutasi.kenaikan_dashboard.index')
                ->with('success', "Berhasil memproses draf kenaikan kelas untuk $countProses siswa.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }
}