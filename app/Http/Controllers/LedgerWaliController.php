<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Pembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LedgerWaliController extends Controller
{
    private function mapSemesterToInt(?string $semester): int
    {
        return (strtoupper($semester) == 'GENAP') ? 2 : 1;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['developer', 'admin_erapor']);
        
        // 1. Ambil Kelas berdasarkan Role
        if ($isAdmin) {
            $kelasList = Kelas::orderBy('nama_kelas', 'asc')->get();
        } else {
            $kelasList = Kelas::where('id_guru', $user->id_guru)->orderBy('nama_kelas', 'asc')->get();
        }

        // Default Filter
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        
        $defaultSemester = ($bulanSekarang < 7) ? 'Genap' : 'Ganjil';
        $defaultTahunAjaran = ($bulanSekarang < 7) ? ($tahunSekarang - 1) . '/' . $tahunSekarang : $tahunSekarang . '/' . ($tahunSekarang + 1);

        $id_kelas     = $request->id_kelas ?? ($kelasList->first()->id_kelas ?? null);
        $semesterRaw  = $request->semester ?? $defaultSemester;
        $tahun_ajaran = $request->tahun_ajaran ?? $defaultTahunAjaran;
        $semesterInt  = $this->mapSemesterToInt($semesterRaw);
        $showRanking  = $request->show_ranking ?? '0';
        $sortBy       = $request->sort_by ?? 'absen';

        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 3; $t <= $tahunSekarang + 3; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }
        rsort($tahunAjaranList);
        $semesterList = ['Ganjil', 'Genap'];

        $daftarMapel = collect();
        $dataLedger  = collect();

        if ($id_kelas) {
            // 2. Ambil Header Mapel
            $rawMapel = Pembelajaran::where('id_kelas', $id_kelas)
                ->whereHas('mapel', function ($q) { $q->where('is_active', 1); })
                ->with('mapel')
                ->get()
                ->map(function($p) {
                    $m = $p->mapel;
                    $isAgama = (stripos($m->nama_mapel, 'Agama') !== false);
                    return (object)[
                        'id_mapel'     => $isAgama ? 'AGAMA' : (string)$m->id_mapel,
                        'id_mapel_asli'=> (string)$m->id_mapel,
                        'nama_mapel'   => $isAgama ? 'Pendidikan Agama' : $m->nama_mapel,
                        'nama_singkat' => $isAgama ? 'Agama' : ($m->nama_singkat ?? $m->nama_mapel),
                        'kategori'     => $m->kategori ?? 1,
                        'urutan'       => $m->urutan ?? 99,
                        'is_agama'     => $isAgama
                    ];
                });

            $daftarMapel = $rawMapel->groupBy('id_mapel')->map(function($items) {
                return $items->first();
            })->sort(function ($a, $b) {
                if ($a->is_agama && !$b->is_agama) return -1;
                if (!$a->is_agama && $b->is_agama) return 1;
                if ($a->kategori != $b->kategori) return $a->kategori <=> $b->kategori;
                return $a->urutan <=> $b->urutan;
            })->values();

            $globalAgamaIds = $rawMapel->where('is_agama', true)->pluck('id_mapel_asli')->toArray();

            // 3. Ambil Data Siswa
            $siswaList = Siswa::where('id_kelas', $id_kelas)->where('status', 'aktif')->orderBy('nama_siswa')->get();
            $siswaIds = $siswaList->pluck('id_siswa')->toArray();

            // 4. Ambil Nilai Akhir (Live Data)
            $rawNilai = DB::table('nilai_akhir')
                ->whereIn('id_siswa', $siswaIds)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->get();

            $mapNilai = [];
            foreach ($rawNilai as $rn) {
                $mapNilai[$rn->id_siswa][(string)$rn->id_mapel] = $rn->nilai_akhir;
            }

            // 5. AMBIL DATA ABSENSI & KENAIKAN DARI HEADER RAPOR (nilai_akhir_rapor)
            $rawAbsen = DB::table('nilai_akhir_rapor')
                ->whereIn('id_siswa', $siswaIds)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->get();

            $mapAbsen = [];
            foreach ($rawAbsen as $ra) {
                $mapAbsen[$ra->id_siswa] = $ra;
            }

            // 6. Susun Data Ledger
            $tempLedger = [];
            foreach ($siswaList as $siswa) {
                $nilaiPerMapel = [];
                $totalNilai = 0;
                $jumlahMapelTerisi = 0;

                foreach ($daftarMapel as $mapel) {
                    $score = null;
                    if ($mapel->is_agama) {
                        foreach ($globalAgamaIds as $idAgamaAsli) {
                            // PASTIKAN menggunakan is_numeric agar nilai 0 tetap terbaca sebagai ada isinya
                            if (isset($mapNilai[$siswa->id_siswa][$idAgamaAsli]) && is_numeric($mapNilai[$siswa->id_siswa][$idAgamaAsli])) {
                                $score = $mapNilai[$siswa->id_siswa][$idAgamaAsli];
                                break;
                            }
                        }
                    } else {
                        $mId = (string)$mapel->id_mapel;
                        if (isset($mapNilai[$siswa->id_siswa][$mId]) && is_numeric($mapNilai[$siswa->id_siswa][$mId])) {
                            $score = $mapNilai[$siswa->id_siswa][$mId];
                        }
                    }

                    $nilaiPerMapel[$mapel->id_mapel] = $score;
                    if (is_numeric($score)) {
                        $totalNilai += $score;
                        $jumlahMapelTerisi++;
                    }
                }

                // Ambil Absensi & Kenaikan
                $absenSiswa = $mapAbsen[$siswa->id_siswa] ?? null;

                $tempLedger[] = (object)[
                    'id_siswa'   => $siswa->id_siswa,
                    'nama_siswa' => $siswa->nama_siswa,
                    'nipd'       => $siswa->nipd ?? '-',
                    'nisn'       => $siswa->nisn ?? '-',
                    'scores'     => $nilaiPerMapel,
                    'total'      => $totalNilai,
                    'rata_rata'  => $jumlahMapelTerisi ? round($totalNilai / $jumlahMapelTerisi, 2) : 0,
                    'absensi'    => (object)[
                        'sakit' => $absenSiswa && is_numeric($absenSiswa->sakit) ? $absenSiswa->sakit : '-',
                        'izin'  => $absenSiswa && is_numeric($absenSiswa->ijin ?? $absenSiswa->izin) ? ($absenSiswa->ijin ?? $absenSiswa->izin) : '-',
                        'alpha' => $absenSiswa && is_numeric($absenSiswa->alpha) ? $absenSiswa->alpha : '-'
                    ], 
                    'status_kenaikan' => $absenSiswa->status_kenaikan ?? null,
                    'ranking_no' => 0
                ];
            }

            // 7. Kalkulasi Ranking
            $collection = collect($tempLedger);
            $rankedData = $collection->sort(function ($a, $b) {
                if ($b->rata_rata != $a->rata_rata) return $b->rata_rata <=> $a->rata_rata;
                return strcmp($a->nama_siswa, $b->nama_siswa);
            })->values();

            foreach ($rankedData as $index => $item) {
                $item->ranking_no = $index + 1;
            }

            $dataLedger = ($sortBy === 'absen') 
                ? $rankedData->sortBy('nama_siswa')->values() 
                : $rankedData->sortBy('ranking_no')->values();
        }

        return view('nilai.ledger_wali', compact(
            'kelasList', 'id_kelas', 'semesterRaw', 'tahun_ajaran', 
            'daftarMapel', 'dataLedger', 'tahunAjaranList', 'semesterList', 
            'showRanking', 'sortBy', 'isAdmin'
        ));
    }
}