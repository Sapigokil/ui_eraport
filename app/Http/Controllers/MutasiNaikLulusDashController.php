<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RiwayatKenaikanKelas;
use App\Models\PengumumanSetting;
use App\Models\PengumumanSiswa;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MutasiNaikLulusDashController extends Controller
{
    /**
     * Menampilkan Halaman Dashboard Eksekutif Akhir Tahun
     */
    public function index(Request $request)
    {
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        $taAktif = ($bulanSekarang >= 7) ? $tahunSekarang . '/' . ($tahunSekarang + 1) : ($tahunSekarang - 1) . '/' . $tahunSekarang;
        $ta = $request->input('tahun_ajaran', $taAktif);

        $kelasMaster = Kelas::all()->map(function($k) {
            preg_match('/^\d+/', $k->nama_kelas, $matches);
            $k->tingkat = !empty($matches) ? (int)$matches[0] : 0;
            return $k;
        });
        $maxTingkat = $kelasMaster->max('tingkat');

        // --- A. KELULUSAN ---
        $idsKelulusan = $kelasMaster->where('tingkat', $maxTingkat)->pluck('id_kelas')->toArray();
        $totalSiswaLulusan = Siswa::whereIn('id_kelas', $idsKelulusan)->where('status', 'aktif')->count();
        
        $statLulus = [
            'lulus' => DB::table('riwayat_kenaikan_kelas')->whereIn('id_kelas_lama', $idsKelulusan)->where('tahun_ajaran_lama', $ta)->where('status', 'lulus')->count(),
            'gagal' => DB::table('riwayat_kenaikan_kelas')->whereIn('id_kelas_lama', $idsKelulusan)->where('tahun_ajaran_lama', $ta)->where('status', 'tidak_lulus')->count(),
        ];
        $statLulus['belum'] = max(0, $totalSiswaLulusan - ($statLulus['lulus'] + $statLulus['gagal']));

        // Statistik Baca Pengumuman Lulus
        $bacaLulus = [
            'sudah' => DB::table('pengumuman_siswa')->where('jenis', 'kelulusan')->where('tahun_ajaran', $ta)->where('has_seen', 1)->count(),
            'belum' => DB::table('pengumuman_siswa')->where('jenis', 'kelulusan')->where('tahun_ajaran', $ta)->where('has_seen', 0)->count(),
        ];

        // --- B. KENAIKAN ---
        $idsKenaikan = $kelasMaster->where('tingkat', '<', $maxTingkat)->pluck('id_kelas')->toArray();
        $totalSiswaKenaikan = Siswa::whereIn('id_kelas', $idsKenaikan)->where('status', 'aktif')->count();
        
        $statNaik = [
            'naik' => DB::table('riwayat_kenaikan_kelas')->whereIn('id_kelas_lama', $idsKenaikan)->where('tahun_ajaran_lama', $ta)->where('status', 'naik_kelas')->count(),
            'tinggal' => DB::table('riwayat_kenaikan_kelas')->whereIn('id_kelas_lama', $idsKenaikan)->where('tahun_ajaran_lama', $ta)->where('status', 'tinggal_kelas')->count(),
        ];
        $statNaik['belum'] = max(0, $totalSiswaKenaikan - ($statNaik['naik'] + $statNaik['tinggal']));

        // Statistik Baca Pengumuman Naik
        $bacaNaik = [
            'sudah' => DB::table('pengumuman_siswa')->where('jenis', 'kenaikan')->where('tahun_ajaran', $ta)->where('has_seen', 1)->count(),
            'belum' => DB::table('pengumuman_siswa')->where('jenis', 'kenaikan')->where('tahun_ajaran', $ta)->where('has_seen', 0)->count(),
        ];

        $jadwalLulus = PengumumanSetting::where('jenis', 'kelulusan')->where('tahun_ajaran', $ta)->first();
        $jadwalNaik = PengumumanSetting::where('jenis', 'kenaikan')->where('tahun_ajaran', $ta)->first();

        $listTA = RiwayatKenaikanKelas::select('tahun_ajaran_lama')->distinct()->pluck('tahun_ajaran_lama')->toArray();
        if(!in_array($taAktif, $listTA)) array_unshift($listTA, $taAktif);

        return view('mutasi.pengumuman.naiklulus_dashboard', compact(
            'ta', 'listTA', 'maxTingkat',
            'totalSiswaLulusan', 'statLulus', 'jadwalLulus', 'bacaLulus',
            'totalSiswaKenaikan', 'statNaik', 'jadwalNaik', 'bacaNaik'
        ));
    }

    public function updateJadwal(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:kelulusan,kenaikan',
            'tahun_ajaran' => 'required',
            'waktu_buka' => 'required|date',
            'waktu_tutup' => 'required|date|after:waktu_buka',
        ]);

        PengumumanSetting::updateOrCreate(
            ['jenis' => $request->jenis, 'tahun_ajaran' => $request->tahun_ajaran],
            [
                'waktu_buka' => $request->waktu_buka,
                'waktu_tutup' => $request->waktu_tutup,
                'is_aktif' => true
            ]
        );

        PengumumanSiswa::where('jenis', $request->jenis)
            ->where('tahun_ajaran', $request->tahun_ajaran)
            ->update(['status' => 'published']);

        return back()->with('success', 'Jadwal pengumuman berhasil disimpan dan data siswa otomatis berstatus Published!');
    }

    public function deleteJadwal(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:kelulusan,kenaikan',
            'tahun_ajaran' => 'required',
        ]);

        PengumumanSetting::where('jenis', $request->jenis)
            ->where('tahun_ajaran', $request->tahun_ajaran)
            ->delete();

        PengumumanSiswa::where('jenis', $request->jenis)
            ->where('tahun_ajaran', $request->tahun_ajaran)
            ->update(['status' => 'hold']);

        return back()->with('success', 'Jadwal pengumuman dihapus. Status siswa ditarik kembali (Hold).');
    }

    /**
     * LANGKAH 1: Menampilkan Halaman Wizard Eksekusi (Pratinjau Data Detail)
     */
    public function halamanEksekusi(Request $request)
    {
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        $taAktif = ($bulanSekarang >= 7) ? $tahunSekarang . '/' . ($tahunSekarang + 1) : ($tahunSekarang - 1) . '/' . $tahunSekarang;
        
        $ta = $request->input('tahun_ajaran', $taAktif);

        $kelasMaster = Kelas::all()->keyBy('id_kelas');

        $riwayatLulus = RiwayatKenaikanKelas::where('tahun_ajaran_lama', $ta)
            ->where('status_eksekusi', 'draft')
            ->whereIn('status', ['lulus', 'tidak_lulus'])
            ->get();
            
        $riwayatNaik = RiwayatKenaikanKelas::where('tahun_ajaran_lama', $ta)
            ->where('status_eksekusi', 'draft')
            ->whereIn('status', ['naik_kelas', 'tinggal_kelas'])
            ->get();

        $semuaIdSiswa = $riwayatLulus->pluck('id_siswa')->merge($riwayatNaik->pluck('id_siswa'))->unique();
        $siswaMaster = Siswa::whereIn('id_siswa', $semuaIdSiswa)->pluck('nama_siswa', 'id_siswa');

        $rekap = [
            'total_lulus' => $riwayatLulus->where('status', 'lulus')->count(),
            'total_tidak_lulus' => $riwayatLulus->where('status', 'tidak_lulus')->count(),
            'total_naik' => $riwayatNaik->where('status', 'naik_kelas')->count(),
            'total_tinggal' => $riwayatNaik->where('status', 'tinggal_kelas')->count(),
            'total_eksekusi' => $riwayatLulus->count() + $riwayatNaik->count()
        ];

        // --- RINCIAN KELULUSAN PER KELAS (Urut Abjad) ---
        $detailKelulusan = $riwayatLulus->groupBy('id_kelas_lama')->map(function($items, $id_kelas) use ($kelasMaster, $siswaMaster) {
            $tidakLulusItems = $items->where('status', 'tidak_lulus');
            $namaTidakLulus = $tidakLulusItems->map(function($item) use ($siswaMaster) {
                return $siswaMaster[$item->id_siswa] ?? 'Siswa Tidak Diketahui';
            })->values()->toArray();

            $kMaster = $kelasMaster->get($id_kelas);
            $nama_kelas = $kMaster ? $kMaster->nama_kelas : 'Kelas Tidak Diketahui';

            return [
                'nama_kelas' => $nama_kelas,
                'total' => $items->count(),
                'lulus' => $items->where('status', 'lulus')->count(),
                'tidak_lulus' => $tidakLulusItems->count(),
                'list_tidak_lulus' => $namaTidakLulus 
            ];
        })->sortBy('nama_kelas')->values(); 

        // --- RINCIAN KENAIKAN PER KELAS (Urut Tingkat -> Abjad) ---
        $detailKenaikan = $riwayatNaik->groupBy('id_kelas_lama')->map(function($items, $id_kelas) use ($kelasMaster, $siswaMaster) {
            $tinggalItems = $items->where('status', 'tinggal_kelas');
            $namaTinggal = $tinggalItems->map(function($item) use ($siswaMaster) {
                return $siswaMaster[$item->id_siswa] ?? 'Siswa Tidak Diketahui';
            })->values()->toArray();
            
            $tinggal = $tinggalItems->count();
            
            $naikItems = $items->where('status', 'naik_kelas')->groupBy('id_kelas_baru');
            $naikDetail = $naikItems->map(function($nItems, $id_baru) use ($kelasMaster) {
                $kBaru = $kelasMaster->get($id_baru);
                return [
                    'nama_kelas_baru' => $kBaru ? $kBaru->nama_kelas : 'Kelas Tidak Diketahui',
                    'jumlah' => $nItems->count()
                ];
            })->values();

            $kAsal = $kelasMaster->get($id_kelas);
            $nama_kelas = $kAsal ? $kAsal->nama_kelas : 'Kelas Tidak Diketahui';
            
            preg_match('/^\d+/', $nama_kelas, $matches);
            $tingkat = !empty($matches) ? (int)$matches[0] : 0;

            return [
                'tingkat' => $tingkat,
                'nama_kelas_asal' => $nama_kelas,
                'total' => $items->count(),
                'tinggal' => $tinggal,
                'list_tinggal' => $namaTinggal,
                'naik_detail' => $naikDetail,
            ];
        })->sort(function ($a, $b) {
            if ($a['tingkat'] === $b['tingkat']) {
                return strcmp($a['nama_kelas_asal'], $b['nama_kelas_asal']);
            }
            return $b['tingkat'] <=> $a['tingkat'];
        })->values();

        return view('mutasi.pengumuman.eksekusi_wizard', compact('ta', 'rekap', 'detailKelulusan', 'detailKenaikan'));
    }

    /**
     * LANGKAH 2 & 3: Mengeksekusi Data ke Master Siswa secara Permanen
     */
    public function prosesEksekusi(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required',
            'konfirmasi' => 'required|in:PROSES' 
        ]);

        $ta = $request->tahun_ajaran;
        
        $riwayat = DB::table('riwayat_kenaikan_kelas')
                    ->where('tahun_ajaran_lama', $ta)
                    ->where('status_eksekusi', 'draft')
                    ->get();

        if ($riwayat->isEmpty()) {
            return back()->with('error', 'Sistem tidak menemukan data draf Mutasi/Kelulusan untuk Tahun Ajaran tersebut.');
        }

        DB::beginTransaction();
        try {
            $countLulus = 0;
            $countPindah = 0;

            foreach ($riwayat as $r) {
                $siswa = DB::table('siswa')->where('id_siswa', $r->id_siswa)->first();
                
                if ($siswa) {
                    if ($r->status == 'lulus') {
                        DB::table('siswa')->where('id_siswa', $r->id_siswa)->update(['status' => 'lulus']);
                        $countLulus++;
                    } elseif ($r->status == 'tidak_lulus') {
                        DB::table('siswa')->where('id_siswa', $r->id_siswa)->update([
                            'status' => 'aktif',
                            'id_kelas' => $r->id_kelas_lama
                        ]);
                    } elseif ($r->status == 'naik_kelas' || $r->status == 'tinggal_kelas') {
                        DB::table('siswa')->where('id_siswa', $r->id_siswa)->update(['status' => 'aktif']);
                        
                        if ($siswa->id_kelas != $r->id_kelas_baru) {
                            DB::table('siswa')->where('id_siswa', $r->id_siswa)->update([
                                'id_kelas' => $r->id_kelas_baru
                            ]);
                            $countPindah++;
                        }
                    }
                }
            }

            DB::table('riwayat_kenaikan_kelas')
                ->where('tahun_ajaran_lama', $ta)
                ->where('status_eksekusi', 'draft')
                ->update(['status_eksekusi' => 'final']);

            DB::commit();
            
            return redirect()->route('mutasi.eksekusi.index', ['tahun_ajaran' => $ta])
                ->with('sukses_eksekusi', true)
                ->with('hasil_lulus', $countLulus)
                ->with('hasil_pindah', $countPindah);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengeksekusi database: ' . $e->getMessage());
        }
    }
}