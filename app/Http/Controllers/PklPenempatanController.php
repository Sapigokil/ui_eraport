<?php

namespace App\Http\Controllers;

use App\Models\PklPenempatan;
use App\Models\PklTempat; // Pastikan model ini sesuai dengan model tempat PKL Anda
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PklPenempatanController extends Controller
{
    public function index(Request $request)
    {
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        
        if ($bulanSekarang < 7) {
            $defaultTA = ($tahunSekarang - 1) . '/' . $tahunSekarang;
            $defaultSemester = 2; 
        } else {
            $defaultTA = $tahunSekarang . '/' . ($tahunSekarang + 1);
            $defaultSemester = 1; 
        }

        $tahun_ajaran = $request->tahun_ajaran ?? $defaultTA;
        $semester = $request->semester ?? $defaultSemester;
        
        $mode = $request->mode ?? 'kelas'; // Default mode
        $id_kelas = $request->id_kelas;
        $bidang_usaha = $request->bidang_usaha;

        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 3; $t <= $tahunSekarang + 3; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }
        rsort($tahunAjaranList);

        $dataSiswa = collect();
        $dataIndustri = collect();
        $kelas_list = Kelas::orderBy('tingkat', 'asc')->orderBy('nama_kelas', 'asc')->get();
        $tempat_list = DB::table('pkl_tempat')->orderBy('nama_perusahaan', 'asc')->get();
        $bidang_usaha_list = DB::table('pkl_tempat')->whereNotNull('bidang_usaha')->distinct()->pluck('bidang_usaha');

        // ==========================================
        // LOGIKA MODE 1: VIEW BY KELAS (Edit Massal)
        // ==========================================
        if ($mode == 'kelas') {
            if ($id_kelas) {
                $kelasInfo = Kelas::find($id_kelas);

                $dataSiswa = DB::table('siswa')
                    ->where('siswa.id_kelas', $id_kelas)
                    ->leftJoin('pkl_gurusiswa', function($join) use ($tahun_ajaran, $semester) {
                        $join->on('siswa.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                             ->where('pkl_gurusiswa.tahun_ajaran', '=', $tahun_ajaran)
                             ->where('pkl_gurusiswa.semester', '=', $semester);
                    })
                    ->leftJoin('pkl_penempatan', function($join) use ($tahun_ajaran, $semester) {
                        $join->on('siswa.id_siswa', '=', 'pkl_penempatan.id_siswa')
                             ->where('pkl_penempatan.tahun_ajaran', '=', $tahun_ajaran)
                             ->where('pkl_penempatan.semester', '=', $semester);
                    })
                    ->leftJoin('pkl_tempat', 'pkl_penempatan.id_pkltempat', '=', 'pkl_tempat.id') 
                    ->select(
                        'siswa.id_siswa',
                        'siswa.nama_siswa',
                        'pkl_gurusiswa.nama_guru',
                        'pkl_penempatan.id_pkltempat',
                        'pkl_tempat.nama_perusahaan as tempat_pkl' 
                    )
                    ->orderBy('siswa.nama_siswa', 'asc')
                    ->get();

                $dataSiswa->map(function($item) use ($kelasInfo) {
                    $item->nama_kelas = $kelasInfo->nama_kelas ?? '-';
                    return $item;
                });
            }
        } 
        // ==========================================
        // LOGIKA MODE 2: VIEW BY INDUSTRI (Rowspan)
        // ==========================================
        elseif ($mode == 'industri') {
            $query = DB::table('pkl_penempatan')
                ->join('pkl_tempat', 'pkl_penempatan.id_pkltempat', '=', 'pkl_tempat.id')
                ->join('siswa', 'pkl_penempatan.id_siswa', '=', 'siswa.id_siswa')
                ->join('kelas', 'siswa.id_kelas', '=', 'kelas.id_kelas')
                ->leftJoin('pkl_gurusiswa', function($join) use ($tahun_ajaran, $semester) {
                    $join->on('siswa.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                         ->where('pkl_gurusiswa.tahun_ajaran', '=', $tahun_ajaran)
                         ->where('pkl_gurusiswa.semester', '=', $semester);
                })
                ->where('pkl_penempatan.tahun_ajaran', $tahun_ajaran)
                ->where('pkl_penempatan.semester', $semester);

            if ($bidang_usaha) {
                $query->where('pkl_tempat.bidang_usaha', $bidang_usaha);
            }

            $rawData = $query->select(
                'pkl_tempat.id as id_tempat',
                'pkl_tempat.nama_perusahaan',
                'pkl_tempat.bidang_usaha',
                'siswa.nama_siswa',
                'kelas.nama_kelas',
                'kelas.tingkat',
                'kelas.jurusan',
                'pkl_gurusiswa.nama_guru'
            )->orderBy('pkl_tempat.nama_perusahaan', 'asc')
             ->orderBy('kelas.tingkat', 'asc')
             ->orderBy('kelas.jurusan', 'asc')
             ->orderBy('siswa.nama_siswa', 'asc')
             ->get();

            $dataIndustri = $rawData->groupBy('id_tempat')->map(function ($items) {
                $first = $items->first();
                return (object) [
                    'id_tempat'       => $first->id_tempat,
                    'nama_perusahaan' => $first->nama_perusahaan,
                    'bidang_usaha'    => $first->bidang_usaha,
                    'jumlah_siswa'    => $items->count(),
                    'daftar_siswa'    => $items, 
                ];
            })->values();
        }

        return view('pkl.penempatan.index', compact(
            'mode', 'id_kelas', 'bidang_usaha',
            'dataSiswa', 'dataIndustri',
            'kelas_list', 'tempat_list', 'bidang_usaha_list',
            'tahun_ajaran', 'semester', 'tahunAjaranList'
        ));
    }

    public function storeMassal(Request $request)
    {
        $tahun_ajaran = $request->tahun_ajaran;
        $semester = $request->semester;
        $id_kelas_filter = $request->id_kelas; 
        
        $pilihan_tempat = $request->id_pkltempat_pilihan ?? []; 

        foreach ($pilihan_tempat as $id_siswa => $id_pkltempat) {
            if (empty($id_pkltempat)) {
                PklPenempatan::where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->where('semester', $semester)
                    ->delete();
                continue;
            }

            // Ambil data relasi guru dari pkl_gurusiswa (jika ada) untuk snapshot di penempatan
            $relasiGuru = DB::table('pkl_gurusiswa')
                ->where('id_siswa', $id_siswa)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', $semester)
                ->first();

            PklPenempatan::updateOrInsert(
                [
                    'id_siswa' => $id_siswa,
                    'tahun_ajaran' => $tahun_ajaran,
                    'semester' => $semester,
                ],
                [
                    'id_pkltempat' => $id_pkltempat,
                    'id_guru' => $relasiGuru->id_guru ?? null,
                    'id_gurusiswa' => $relasiGuru->id ?? null,
                    'status' => 1 // Status otomatis aktif
                ]
            );
        }

        return redirect()->route('pkl.penempatan.index', [
            'mode' => 'kelas',
            'id_kelas' => $id_kelas_filter,
            'tahun_ajaran' => $tahun_ajaran,
            'semester' => $semester
        ])->with('success', 'Data Penempatan Industri berhasil diperbarui secara massal!');
    }

    public function setup(Request $request)
    {
        $tahun_ajaran = $request->tahun_ajaran ?? date('Y') . '/' . (date('Y') + 1);
        $semester = $request->semester ?? 1;
        $id_pkltempat = $request->id_pkltempat;

        $tempat_list = DB::table('pkl_tempat')->orderBy('nama_perusahaan', 'asc')->get();
        $kelas_list = Kelas::orderBy('tingkat', 'asc')->orderBy('nama_kelas', 'asc')->get();

        $siswa_terpilih = collect();
        if ($id_pkltempat) {
            $siswa_terpilih = DB::table('pkl_penempatan')
                ->join('siswa', 'pkl_penempatan.id_siswa', '=', 'siswa.id_siswa')
                ->join('kelas', 'siswa.id_kelas', '=', 'kelas.id_kelas')
                ->where('pkl_penempatan.id_pkltempat', $id_pkltempat)
                ->where('pkl_penempatan.tahun_ajaran', $tahun_ajaran)
                ->where('pkl_penempatan.semester', $semester)
                ->select('siswa.id_siswa', 'siswa.nama_siswa', 'kelas.nama_kelas', 'kelas.tingkat', 'kelas.jurusan')
                ->get();
        }

        return view('pkl.penempatan.setup', compact(
            'tahun_ajaran', 'semester', 'id_pkltempat', 'tempat_list', 'kelas_list', 'siswa_terpilih'
        ));
    }

    public function getSiswa(Request $request)
    {
        $id_kelas = $request->id_kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        $semester = $request->semester;

        $siswa = DB::table('siswa')
            ->join('kelas', 'siswa.id_kelas', '=', 'kelas.id_kelas')
            ->where('siswa.id_kelas', $id_kelas)
            ->select('siswa.id_siswa', 'siswa.nama_siswa', 'siswa.nisn', 'kelas.nama_kelas', 'kelas.tingkat', 'kelas.jurusan')
            ->orderBy('siswa.nama_siswa', 'asc')
            ->get();
        
        $siswa_sudah_penempatan = DB::table('pkl_penempatan')
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester)
            ->pluck('id_pkltempat', 'id_siswa')
            ->toArray();

        $data = $siswa->map(function($s) use ($siswa_sudah_penempatan) {
            $is_used = array_key_exists($s->id_siswa, $siswa_sudah_penempatan);
            return [
                'id_siswa' => $s->id_siswa,
                'nama_siswa' => $s->nama_siswa,
                'nisn' => $s->nisn ?? '-',
                'nama_kelas' => $s->nama_kelas,
                'tingkat' => $s->tingkat,
                'jurusan' => $s->jurusan,
                'is_used' => $is_used,
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pkltempat' => 'required',
            'tahun_ajaran' => 'required',
            'semester' => 'required',
        ]);

        $id_pkltempat = $request->id_pkltempat;
        $tahun_ajaran = $request->tahun_ajaran;
        $semester = $request->semester;
        $siswa_ids = $request->id_siswa ?? []; 

        PklPenempatan::where('id_pkltempat', $id_pkltempat)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester)
            ->delete();

        if (count($siswa_ids) > 0) {
            foreach ($siswa_ids as $id_siswa) {
                // Hapus jika siswa ini sudah ada di tempat lain
                PklPenempatan::where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->where('semester', $semester)
                    ->delete();

                $relasiGuru = DB::table('pkl_gurusiswa')
                    ->where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->where('semester', $semester)
                    ->first();

                PklPenempatan::create([
                    'id_pkltempat' => $id_pkltempat,
                    'id_siswa' => $id_siswa,
                    'id_guru' => $relasiGuru->id_guru ?? null,
                    'id_gurusiswa' => $relasiGuru->id ?? null,
                    'tahun_ajaran' => $tahun_ajaran,
                    'semester' => $semester,
                    'status' => 1
                ]);
            }
        }

        return redirect()->route('pkl.penempatan.index', ['mode' => 'industri', 'tahun_ajaran' => $tahun_ajaran, 'semester' => $semester])
                         ->with('success', 'Data penempatan industri berhasil disimpan!');
    }
}