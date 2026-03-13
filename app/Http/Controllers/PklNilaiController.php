<?php

namespace App\Http\Controllers;

use App\Models\PklTp;
use App\Models\PklTpIndikator;
use App\Models\PklTpRubrik;
use App\Models\PklCatatanSiswa;
use App\Models\PklNilaiSiswa;
use App\Models\PklSeason;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\PklTempat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PklNilaiController extends Controller
{
    // ==============================================================
    // HALAMAN 1: INDEX (DASBOR MONITORING & FILTER)
    // ==============================================================
    public function index(Request $request)
    {
        $season = PklSeason::currentOpen();
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');

        if ($bulanSekarang < 7) {
            $defaultTAFallback = ($tahunSekarang - 1) . '/' . $tahunSekarang;
            $defaultSemesterFallback = 2; 
        } else {
            $defaultTAFallback = $tahunSekarang . '/' . ($tahunSekarang + 1);
            $defaultSemesterFallback = 1; 
        }

        $tahun_ajaran = $request->tahun_ajaran ?? ($season ? $season->tahun_ajaran : $defaultTAFallback);
        $semester = $request->semester ?? ($season ? $season->semester : $defaultSemesterFallback);

        $id_kelas = $request->id_kelas;
        $id_tempat = $request->id_tempat;
        $status_penilaian = $request->status_penilaian; // Tangkap request status

        // ==========================================
        // LOGIKA FILTER ROLE (DATA ISOLATION)
        // ==========================================
        $user = Auth::user();
        if ($user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor'])) {
            $id_guru = $request->id_guru;
        } else {
            $id_guru = $user->id_guru;
        }

        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $guruList = Guru::orderBy('nama_guru')->get();
        $tempatList = PklTempat::orderBy('nama_perusahaan')->get();
        
        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 3; $t <= $tahunSekarang + 3; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }
        rsort($tahunAjaranList);

        $query = DB::table('pkl_penempatan')
            ->join('pkl_gurusiswa', function($join) use ($tahun_ajaran, $semester) {
                $join->on('pkl_penempatan.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                     ->where('pkl_gurusiswa.tahun_ajaran', '=', $tahun_ajaran)
                     ->where('pkl_gurusiswa.semester', '=', $semester);
            })
            ->leftJoin('pkl_tempat', 'pkl_penempatan.id_pkltempat', '=', 'pkl_tempat.id')
            ->leftJoin('pkl_catatansiswa', 'pkl_penempatan.id', '=', 'pkl_catatansiswa.id_penempatan');

        // EKSEKUSI FILTER
        if ($id_kelas) $query->where('pkl_gurusiswa.id_kelas', $id_kelas);
        
        if ($user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor'])) {
            if ($id_guru) $query->where('pkl_gurusiswa.id_guru', $id_guru);
        } else {
            // PENGAMANAN ABSOLUT
            $query->where('pkl_gurusiswa.id_guru', $id_guru ?: 0);
        }

        if ($id_tempat) $query->where('pkl_penempatan.id_pkltempat', $id_tempat);

        // FILTER STATUS PENILAIAN
        if ($status_penilaian !== null && $status_penilaian !== '') {
            if ($status_penilaian === 'belum') {
                $query->whereNull('pkl_catatansiswa.status_penilaian');
            } elseif ($status_penilaian === '0') {
                $query->where('pkl_catatansiswa.status_penilaian', 0);
            } elseif ($status_penilaian === '1') {
                $query->where('pkl_catatansiswa.status_penilaian', 1);
            }
        }

        $dataSiswa = $query->select(
                'pkl_penempatan.id as id_penempatan',
                'pkl_gurusiswa.id_guru',
                'pkl_gurusiswa.nama_siswa',
                'pkl_gurusiswa.nama_kelas',
                'pkl_gurusiswa.nama_guru',
                'pkl_tempat.nama_perusahaan as tempat_pkl',
                'pkl_catatansiswa.status_penilaian'
            )
            ->orderBy('pkl_gurusiswa.nama_kelas')
            ->orderBy('pkl_gurusiswa.nama_siswa')
            ->get();

        $totalSiswa = $dataSiswa->count();
        $rawCount = $dataSiswa->whereNotNull('status_penilaian')->count(); 
        $finalCount = $dataSiswa->where('status_penilaian', 1)->count(); 
        $persenRaw = $totalSiswa > 0 ? round(($rawCount / $totalSiswa) * 100) : 0;
        $persenFinal = $totalSiswa > 0 ? round(($finalCount / $totalSiswa) * 100) : 0;

        return view('pkl.nilai.index', compact(
            'tahun_ajaran', 'semester', 'tahunAjaranList',
            'kelasList', 'guruList', 'tempatList',
            'id_kelas', 'id_guru', 'id_tempat', 'status_penilaian',
            'dataSiswa', 'totalSiswa', 'rawCount', 'finalCount', 'persenRaw', 'persenFinal'
        ));
    }

    // ==============================================================
    // HALAMAN 2: FORM INPUT (SPLIT SCREEN)
    // ==============================================================
    public function input(Request $request)
    {
        $user = Auth::user();
        
        // PENGAMANAN LAPIS DUA UNTUK HALAMAN INPUT
        if ($user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor'])) {
            $id_guru = $request->id_guru ?? $user->id_guru;
        } else {
            $id_guru = $user->id_guru; // Kunci paksa
        }
        
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');

        if ($bulanSekarang < 7) {
            $defaultTAFallback = ($tahunSekarang - 1) . '/' . $tahunSekarang;
            $defaultSemesterFallback = 2; 
        } else {
            $defaultTAFallback = $tahunSekarang . '/' . ($tahunSekarang + 1);
            $defaultSemesterFallback = 1; 
        }

        $tahun_ajaran = $request->tahun_ajaran ?? $defaultTAFallback;
        $semester = $request->semester ?? $defaultSemesterFallback;

        if (!$id_guru) {
            return redirect()->route('pkl.nilai.index')->with('error', 'Silakan pilih guru pembimbing melalui halaman index terlebih dahulu.');
        }

        $tpData = PklTp::where('is_active', 1)->orderBy('no_urut', 'asc')->get();
        $indikatorData = PklTpIndikator::orderBy('no_urut', 'asc')->get()->groupBy('id_pkl_tp');
        $rubrikData = PklTpRubrik::all()->groupBy('id_pkl_tp_indikator');

        $dataSiswa = DB::table('pkl_penempatan')
            ->join('pkl_gurusiswa', function($join) use ($tahun_ajaran, $semester) {
                $join->on('pkl_penempatan.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                     ->where('pkl_gurusiswa.tahun_ajaran', '=', $tahun_ajaran)
                     ->where('pkl_gurusiswa.semester', '=', $semester);
            })
            ->leftJoin('pkl_tempat', 'pkl_penempatan.id_pkltempat', '=', 'pkl_tempat.id')
            ->leftJoin('pkl_catatansiswa', 'pkl_penempatan.id', '=', 'pkl_catatansiswa.id_penempatan')
            ->where('pkl_gurusiswa.id_guru', $id_guru)
            ->select(
                'pkl_penempatan.id as id_penempatan',
                'pkl_gurusiswa.nama_siswa',
                'pkl_gurusiswa.nama_kelas',
                'pkl_gurusiswa.nama_guru',
                'pkl_tempat.nama_perusahaan as tempat_pkl',
                'pkl_catatansiswa.status_penilaian' 
            )
            ->orderBy('pkl_gurusiswa.nama_kelas')
            ->orderBy('pkl_gurusiswa.nama_siswa')
            ->get();

        $pembimbingInfo = (object) [
            'nama_kelompok' => 'Kelompok Bimbingan PKL',
            'nama_guru' => $dataSiswa->first()->nama_guru ?? 'Guru Pembimbing'
        ];

        $totalSiswa = $dataSiswa->count();
        $rawCount = $dataSiswa->whereNotNull('status_penilaian')->count(); 
        $finalCount = $dataSiswa->where('status_penilaian', 1)->count(); 
        $persenRaw = $totalSiswa > 0 ? round(($rawCount / $totalSiswa) * 100) : 0;
        $persenFinal = $totalSiswa > 0 ? round(($finalCount / $totalSiswa) * 100) : 0;

        return view('pkl.nilai.input', compact(
            'tahun_ajaran', 'semester', 'id_guru', 
            'tpData', 'indikatorData', 'rubrikData', 
            'dataSiswa', 'pembimbingInfo',
            'totalSiswa', 'rawCount', 'finalCount', 'persenRaw', 'persenFinal'
        ));
    }

    // ==============================================================
    // AJAX GET SISWA DATA
    // ==============================================================
    public function getSiswaData($id_penempatan)
    {
        $catatan = PklCatatanSiswa::where('id_penempatan', $id_penempatan)->first();
        $nilai = PklNilaiSiswa::where('id_penempatan', $id_penempatan)->get()->keyBy('id_pkl_tp');

        return response()->json(['status' => 'success', 'catatan' => $catatan, 'nilai' => $nilai]);
    }

    // ==============================================================
    // PROSES SIMPAN NILAI 
    // ==============================================================
    public function store(Request $request)
    {
        $request->validate([
            'id_penempatan' => 'required',
            'status_penilaian' => 'required|in:0,1',
            'nilai' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $id_penempatan = $request->id_penempatan;

            // 1. Simpan Catatan, Data Sertifikat, & Absensi
            PklCatatanSiswa::updateOrCreate(
                ['id_penempatan' => $id_penempatan],
                [
                    'id_guru' => $request->id_guru,
                    'program_keahlian' => $request->program_keahlian,
                    'konsentrasi_keahlian' => $request->konsentrasi_keahlian,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'nama_instruktur' => $request->nama_instruktur,
                    'sakit' => $request->sakit ?? 0,
                    'izin' => $request->izin ?? 0,
                    'alpa' => $request->alpa ?? 0,
                    'catatan_pembimbing' => $request->catatan_pembimbing,
                    'status_penilaian' => $request->status_penilaian,
                    'created_by' => auth()->user()->id ?? null
                ]
            );

            // 2. Simpan Nilai per TP
            if ($request->has('nilai')) {
                $semuaRubrik = PklTpRubrik::all()->groupBy('id_pkl_tp_indikator');

                foreach ($request->nilai as $id_tp => $dataIndikatorInput) {
                    $nilaiArray = [];
                    $validInputs = []; 
                    $totalNilai = 0;

                    foreach ($dataIndikatorInput as $id_ind => $val_angka) {
                        if ($val_angka === null || $val_angka === '') continue;

                        $val_angka = (int)$val_angka;
                        $totalNilai += $val_angka;

                        $rubrikInd = $semuaRubrik->get($id_ind, collect());
                        $deskripsiDapat = '';

                        foreach ($rubrikInd as $r) {
                            if ($val_angka >= $r->min_nilai && $val_angka <= $r->max_nilai) {
                                $deskripsiDapat = $r->deskripsi_rubrik;
                                break;
                            }
                        }

                        $nilaiArray[$id_ind] = ['nilai' => $val_angka, 'deskripsi' => $deskripsiDapat];
                        $validInputs[] = ['nilai' => $val_angka, 'deskripsi' => $deskripsiDapat];
                    }

                    $countNilai = count($validInputs);

                    if ($countNilai == 0) {
                        PklNilaiSiswa::where('id_penempatan', $id_penempatan)->where('id_pkl_tp', $id_tp)->delete();
                        continue; 
                    }

                    $rataRata = $totalNilai / $countNilai;
                    $maxVal = -1; $minVal = 101;
                    $descMax = ''; $descMin = '';

                    foreach ($validInputs as $item) {
                        if ($item['nilai'] > $maxVal) {
                            $maxVal = $item['nilai'];
                            $descMax = $item['deskripsi'];
                        }
                        if ($item['nilai'] < $minVal) {
                            $minVal = $item['nilai'];
                            $descMin = $item['deskripsi'];
                        }
                    }

                    $gabungan = "";
                    $dMax = lcfirst(trim($descMax));
                    $dMin = lcfirst(trim($descMin));

                    if ($maxVal == $minVal) {
                        if ($countNilai >= 2) {
                            $keys = array_rand($validInputs, 2);
                            $d1 = lcfirst(trim($validInputs[$keys[0]]['deskripsi']));
                            $d2 = lcfirst(trim($validInputs[$keys[1]]['deskripsi']));
                            $gabungan = "Ananda $d1 dan $d2.";
                        } else {
                            $d1 = lcfirst(trim($validInputs[0]['deskripsi']));
                            $gabungan = "Ananda $d1.";
                        }
                    } else {
                        if ($maxVal >= 80 && $minVal >= 80) {
                            $gabungan = "Ananda $dMax dan $dMin.";
                        } elseif ($maxVal >= 80 && $minVal < 80) {
                            $gabungan = "Ananda $dMax namun $dMin.";
                        } else { 
                            $gabungan = "Ananda $dMax dan $dMin.";
                        }
                    }

                    PklNilaiSiswa::updateOrCreate(
                        ['id_penempatan' => $id_penempatan, 'id_pkl_tp' => $id_tp],
                        [
                            'data_indikator' => $nilaiArray,
                            'nilai_rata_rata' => $rataRata,
                            'deskripsi_gabungan' => $gabungan,
                            'created_by' => auth()->user()->id ?? null
                        ]
                    );
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Nilai berhasil disimpan!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}