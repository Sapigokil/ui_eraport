<?php

namespace App\Http\Controllers;

use App\Models\PklGuruSiswa;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class PklGuruSiswaController extends Controller
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
        
        $mode = $request->mode ?? 'guru'; 
        $id_kelas = $request->id_kelas;

        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 3; $t <= $tahunSekarang + 3; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }
        rsort($tahunAjaranList);

        $dataKelompok = collect();
        $dataSiswa = collect();
        $kelas_list = collect();
        $guru_list = collect(); 

        // ==========================================
        // LOGIKA MODE 1: VIEW BY GURU
        // ==========================================
        if ($mode == 'guru') {
            // REVISI: Mengambil data Penempatan dari tabel pkl_penempatan
            $rawData = DB::table('pkl_gurusiswa')
                ->leftJoin('pkl_penempatan', function($join) use ($tahun_ajaran, $semester) {
                    $join->on('pkl_gurusiswa.id_siswa', '=', 'pkl_penempatan.id_siswa')
                         ->where('pkl_penempatan.tahun_ajaran', '=', $tahun_ajaran)
                         ->where('pkl_penempatan.semester', '=', $semester);
                })
                ->leftJoin('pkl_tempat', 'pkl_penempatan.id_pkltempat', '=', 'pkl_tempat.id')
                ->where('pkl_gurusiswa.tahun_ajaran', $tahun_ajaran)
                ->where('pkl_gurusiswa.semester', $semester)
                ->select(
                    'pkl_gurusiswa.*', 
                    'pkl_tempat.nama_perusahaan as tempat_pkl' 
                )
                ->get();

            $dataKelompok = $rawData->groupBy('id_guru')->map(function ($items) {
                $first = $items->first();
                return (object) [
                    'id_guru'      => $first->id_guru,
                    'nama_guru'    => $first->nama_guru,
                    'jurusan'      => $items->pluck('jurusan')->unique()->values(),
                    'tingkat'      => $items->pluck('tingkat')->unique()->values(),
                    'jumlah_siswa' => $items->count(),
                    'daftar_siswa' => $items, 
                ];
            })->values();
        } 
        // ==========================================
        // LOGIKA MODE 2: VIEW BY KELAS
        // ==========================================
        elseif ($mode == 'kelas') {
            $kelas_list = Kelas::orderBy('tingkat', 'asc')->orderBy('nama_kelas', 'asc')->get();
            $guru_list = Guru::orderBy('nama_guru', 'asc')->get(); 

            if ($id_kelas) {
                $kelasInfo = Kelas::find($id_kelas);

                // REVISI: Mengambil relasi guru dari pkl_gurusiswa, dan lokasi dari pkl_penempatan
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
                        'pkl_gurusiswa.id_guru', 
                        'pkl_gurusiswa.nama_guru',
                        'pkl_tempat.nama_perusahaan as tempat_pkl' 
                    )
                    ->orderBy('siswa.nama_siswa', 'asc')
                    ->get();

                $dataSiswa->map(function($item) use ($kelasInfo) {
                    $item->nama_kelas = $kelasInfo->nama_kelas ?? '-';
                    $item->tingkat = $kelasInfo->tingkat ?? '-'; 
                    $item->jurusan = $kelasInfo->jurusan ?? '-'; 
                    return $item;
                });
            }
        }

        return view('pkl.gurusiswa.index', compact(
            'mode',
            'id_kelas',
            'dataKelompok', 
            'dataSiswa',
            'kelas_list',
            'guru_list', 
            'tahun_ajaran', 
            'semester', 
            'tahunAjaranList'
        ));
    }

    public function setup(Request $request)
    {
        $tahun_ajaran = $request->tahun_ajaran ?? date('Y') . '/' . (date('Y') + 1);
        $semester = $request->semester ?? 1;
        $id_guru = $request->id_guru;

        $guru_list = Guru::orderBy('nama_guru', 'asc')->get();
        $kelas_list = Kelas::orderBy('tingkat', 'asc')->orderBy('nama_kelas', 'asc')->get();

        $siswa_terpilih = collect();
        if ($id_guru) {
            $siswa_terpilih = PklGuruSiswa::where('id_guru', $id_guru)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', $semester)
                ->get();
        }

        return view('pkl.gurusiswa.setup', compact(
            'tahun_ajaran', 'semester', 'id_guru', 'guru_list', 'kelas_list', 'siswa_terpilih'
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
        
        $siswa_sudah_pkl = DB::table('pkl_gurusiswa')
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester)
            ->pluck('id_guru', 'id_siswa')
            ->toArray();

        $data = $siswa->map(function($s) use ($siswa_sudah_pkl) {
            $is_used = array_key_exists($s->id_siswa, $siswa_sudah_pkl);
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
            'id_guru' => 'required',
            'tahun_ajaran' => 'required',
            'semester' => 'required',
        ]);

        $id_guru = $request->id_guru;
        $tahun_ajaran = $request->tahun_ajaran;
        $semester = $request->semester;
        $siswa_ids = $request->id_siswa ?? []; 

        $guru = Guru::find($id_guru);

        PklGuruSiswa::where('id_guru', $id_guru)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester)
            ->delete();

        if (count($siswa_ids) > 0) {
            $siswa_data = DB::table('siswa')
                ->join('kelas', 'siswa.id_kelas', '=', 'kelas.id_kelas')
                ->whereIn('siswa.id_siswa', $siswa_ids)
                ->select('siswa.id_siswa', 'siswa.nama_siswa', 'siswa.id_kelas', 'kelas.nama_kelas', 'kelas.tingkat', 'kelas.jurusan')
                ->get();
            
            foreach ($siswa_data as $s) {
                PklGuruSiswa::where('id_siswa', $s->id_siswa)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->where('semester', $semester)
                    ->delete();

                PklGuruSiswa::create([
                    'id_guru' => $guru->id_guru,
                    'id_siswa' => $s->id_siswa,
                    'id_kelas' => $s->id_kelas,
                    'tahun_ajaran' => $tahun_ajaran,
                    'semester' => $semester,
                    'nama_guru' => $guru->nama_guru,
                    'nama_siswa' => $s->nama_siswa,
                    'nama_kelas' => $s->nama_kelas,
                    'tingkat' => $s->tingkat,
                    'jurusan' => $s->jurusan,
                    'status' => 0
                ]);
            }
        }

        return redirect()->route('pkl.gurusiswa.index', ['tahun_ajaran' => $tahun_ajaran, 'semester' => $semester])
                         ->with('success', 'Kelompok bimbingan berhasil disimpan!');
    }

    public function storeMassal(Request $request)
    {
        $tahun_ajaran = $request->tahun_ajaran;
        $semester = $request->semester;
        $id_kelas_filter = $request->id_kelas; 
        
        $pilihan_guru = $request->id_guru_pilihan ?? []; 

        $guru_ids = array_unique(array_filter(array_values($pilihan_guru)));
        $guru_data = Guru::whereIn('id_guru', $guru_ids)->pluck('nama_guru', 'id_guru');

        foreach ($pilihan_guru as $id_siswa => $id_guru) {
            
            if (empty($id_guru)) {
                PklGuruSiswa::where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->where('semester', $semester)
                    ->delete();
                continue; 
            }

            $siswa_info = DB::table('siswa')
                ->join('kelas', 'siswa.id_kelas', '=', 'kelas.id_kelas')
                ->where('siswa.id_siswa', $id_siswa)
                ->select('siswa.id_siswa', 'siswa.nama_siswa', 'siswa.id_kelas', 'kelas.nama_kelas', 'kelas.tingkat', 'kelas.jurusan')
                ->first();

            if ($siswa_info) {
                PklGuruSiswa::updateOrInsert(
                    [
                        'id_siswa' => $id_siswa,
                        'tahun_ajaran' => $tahun_ajaran,
                        'semester' => $semester,
                    ],
                    [
                        'id_guru' => $id_guru,
                        'nama_guru' => $guru_data[$id_guru] ?? 'Guru Tidak Ditemukan',
                        'id_kelas' => $siswa_info->id_kelas,
                        'nama_siswa' => $siswa_info->nama_siswa,
                        'nama_kelas' => $siswa_info->nama_kelas,
                        'tingkat' => $siswa_info->tingkat,
                        'jurusan' => $siswa_info->jurusan,
                    ]
                );
            }
        }

        return redirect()->route('pkl.gurusiswa.index', [
            'mode' => 'kelas',
            'id_kelas' => $id_kelas_filter,
            'tahun_ajaran' => $tahun_ajaran,
            'semester' => $semester
        ])->with('success', 'Data Pembimbing berhasil diperbarui secara massal!');
    }
}