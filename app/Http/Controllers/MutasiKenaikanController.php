<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RiwayatKenaikanKelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MutasiKenaikanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Semua Kelas & Ekstrak Tingkatannya (Angka di depan)
        $semuaKelas = Kelas::all()->map(function($k) {
            // Mengambil angka berapapun di awal string (contoh: "10 AKL 1" -> 10, "7A" -> 7)
            preg_match('/^\d+/', $k->nama_kelas, $matches);
            $k->tingkat = !empty($matches) ? (int)$matches[0] : 0;
            return $k;
        });

        // 2. Cari Tingkat Tertinggi (Misal 12 untuk SMA, 9 untuk SMP, 6 untuk SD)
        $maxTingkat = $semuaKelas->max('tingkat');

        // 3. Filter Kelas Asal (Hanya tampilkan kelas di bawah tingkat tertinggi)
        $kelasAsalList = $semuaKelas->where('tingkat', '<', $maxTingkat)->sortBy('nama_kelas');

        $id_kelas_asal = $request->id_kelas_asal;
        $dataSiswa = collect([]);
        $pilihanKelasTujuan = collect([]);
        $idKelasDefaultTujuan = null;
        $kelasAsalTerpilih = null;

        if ($id_kelas_asal) {
            $kelasAsalTerpilih = $semuaKelas->firstWhere('id_kelas', $id_kelas_asal);
            $tingkatAsal = $kelasAsalTerpilih->tingkat;
            $tingkatTujuan = $tingkatAsal + 1;

            // 4. Ambil Siswa Aktif Saja di kelas tersebut
            $dataSiswa = Siswa::where('id_kelas', $id_kelas_asal)
                ->where('status', 'aktif') // GATEKEEPER SISWA AKTIF
                ->orderBy('nama_siswa', 'asc')
                ->get();

            // 5. Siapkan Pilihan Dropdown Tujuan:
            // HANYA tampilkan 1 kelas asal (Tinggal Kelas) & Semua kelas di Tingkat + 1 (Naik Kelas)
            $pilihanKelasTujuan = $semuaKelas->filter(function($k) use ($id_kelas_asal, $tingkatTujuan) {
                // Return TRUE jika ID kelas sama persis dengan kelas asal, ATAU tingkatnya naik 1 tingkat
                return $k->id_kelas == $id_kelas_asal || $k->tingkat === $tingkatTujuan;
            })->sortBy([
                ['tingkat', 'asc'],
                ['nama_kelas', 'asc']
            ]);

            // 6. AUTO-MAPPING: Tebak kelas tujuan (Misal: "10 AKL 1" -> "11 AKL 1")
            $namaKelasAsal = $kelasAsalTerpilih->nama_kelas;
            $tebakanNamaTujuan = preg_replace('/^'.$tingkatAsal.'/', $tingkatTujuan, $namaKelasAsal, 1);
            
            $kelasTebakan = $pilihanKelasTujuan->firstWhere('nama_kelas', $tebakanNamaTujuan);
            if ($kelasTebakan) {
                $idKelasDefaultTujuan = $kelasTebakan->id_kelas;
            }
        }

        // Siapkan referensi Tahun Ajaran (Bisa disesuaikan dengan setting global Anda)
        $tahunSekarang = date('Y');
        $taLama = ($tahunSekarang - 1) . '/' . $tahunSekarang;
        $taBaru = $tahunSekarang . '/' . ($tahunSekarang + 1);

        return view('mutasi.kenaikan_index', compact(
            'kelasAsalList', 
            'id_kelas_asal', 
            'dataSiswa', 
            'pilihanKelasTujuan',
            'kelasAsalTerpilih',
            'idKelasDefaultTujuan',
            'taLama',
            'taBaru'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kelas_lama'     => 'required|exists:kelas,id_kelas',
            'tahun_ajaran_lama' => 'required',
            'tahun_ajaran_baru' => 'required',
            'tujuan'            => 'required|array', // Format array dari view: name="tujuan[id_siswa]" = id_kelas_baru
        ]);

        $id_kelas_lama = $request->id_kelas_lama;
        $dataTujuan = $request->tujuan; // isinya: [ id_siswa => id_kelas_baru ]
        
        $adminName = Auth::user()->name ?? 'Admin Sistem';

        DB::beginTransaction();
        try {
            $countProses = 0;

            foreach ($dataTujuan as $id_siswa => $id_kelas_baru) {
                // Tentukan status (Jika id kelas sama, berarti tinggal kelas)
                $status_kenaikan = ($id_kelas_lama == $id_kelas_baru) ? 'tinggal_kelas' : 'naik_kelas';

                // 1. Catat ke tabel Riwayat
                RiwayatKenaikanKelas::create([
                    'id_siswa'          => $id_siswa,
                    'id_kelas_lama'     => $id_kelas_lama,
                    'id_kelas_baru'     => $id_kelas_baru,
                    'tahun_ajaran_lama' => $request->tahun_ajaran_lama,
                    'tahun_ajaran_baru' => $request->tahun_ajaran_baru,
                    'status'            => $status_kenaikan,
                    'user_admin'        => $adminName
                ]);

                // 2. Update Data Master Siswa
                DB::table('siswa')->where('id_siswa', $id_siswa)->update([
                    'id_kelas'   => $id_kelas_baru
                ]);

                $countProses++;
            }

            DB::commit();
            return redirect()->route('mutasi.dashboard_akhir.index')
                ->with('success', "Berhasil memproses kenaikan kelas untuk $countProses siswa aktif.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }
}