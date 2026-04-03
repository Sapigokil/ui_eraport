<?php

namespace App\Http\Controllers;

use App\Models\PklSeason;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PklRaporMonitoringController extends Controller
{
    public function index(Request $request)
    {
        // 1. Setup Periode (Filter)
        $season = PklSeason::currentOpen();
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');

        if ($bulanSekarang < 7) {
            $defaultTA = ($tahunSekarang - 1) . '/' . $tahunSekarang;
            $defaultSemester = 2;
        } else {
            $defaultTA = $tahunSekarang . '/' . ($tahunSekarang + 1);
            $defaultSemester = 1;
        }

        $tahun_ajaran = $request->tahun_ajaran ?? ($season->tahun_ajaran ?? $defaultTA);
        $semester = $request->semester ?? ($season->semester ?? $defaultSemester);

        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 3; $t <= $tahunSekarang + 3; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }
        rsort($tahunAjaranList);

        // 2. Ambil Semua Data Siswa PKL beserta status penilaiannya
        $siswaPkl = DB::table('pkl_penempatan')
            ->join('pkl_gurusiswa', function($join) use ($tahun_ajaran, $semester) {
                $join->on('pkl_penempatan.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                     ->where('pkl_gurusiswa.tahun_ajaran', '=', $tahun_ajaran)
                     ->where('pkl_gurusiswa.semester', '=', $semester);
            })
            ->join('siswa', 'pkl_penempatan.id_siswa', '=', 'siswa.id_siswa')
            ->leftJoin('pkl_tempat', 'pkl_penempatan.id_pkltempat', '=', 'pkl_tempat.id')
            ->leftJoin('pkl_catatansiswa', 'pkl_penempatan.id', '=', 'pkl_catatansiswa.id_penempatan')
            ->join('kelas', 'pkl_gurusiswa.id_kelas', '=', 'kelas.id_kelas')
            ->select(
                'kelas.id_kelas', 'kelas.nama_kelas', 'kelas.wali_kelas',
                'pkl_penempatan.id as id_penempatan',
                'pkl_gurusiswa.id_siswa', 'pkl_gurusiswa.nama_siswa', 
                'siswa.nisn', 
                'pkl_gurusiswa.id_guru', 'pkl_gurusiswa.nama_guru',
                'pkl_tempat.nama_perusahaan as tempat_pkl',
                'pkl_catatansiswa.status_penilaian',
                'pkl_catatansiswa.sakit', 'pkl_catatansiswa.izin', 'pkl_catatansiswa.alpa', 'pkl_catatansiswa.catatan_pembimbing'
            )
            ->get();

        // 3. Kelompokkan berdasarkan Kelas
        $groupedByKelas = $siswaPkl->groupBy('id_kelas');

        $monitoringData = [];
        $global_siswa_total = $siswaPkl->count();
        $global_siswa_final = 0;

        foreach ($groupedByKelas as $id_kelas => $students) {
            $jml_siswa = $students->count();
            
            // ✅ PERBAIKAN PROGRESS BAR: Hitung siswa yang sudah Final Guru (1) atau Final Admin (3)
            $siswa_selesai = $students->filter(function($s) {
                if (is_null($s->status_penilaian)) return false;
                $val = (int) $s->status_penilaian;
                return $val === 1 || $val === 3;
            })->count();

            $global_siswa_final += $siswa_selesai;

            $persen = $jml_siswa > 0 ? round(($siswa_selesai / $jml_siswa) * 100) : 0;
            $kelasInfo = $students->first();

            // ✅ PERBAIKAN MAPPING DATA (Mengatasi String vs Integer)
            $detail_siswa = $students->map(function($s) {
                
                $statusData = 'kosong';
                if (!is_null($s->status_penilaian)) {
                    $val = (int) $s->status_penilaian;
                    if ($val === 1 || $val === 3) {
                        $statusData = 'lengkap';
                    } elseif ($val === 0) {
                        $statusData = 'proses';
                    }
                }

                return [
                    'nama_siswa' => $s->nama_siswa,
                    'nisn' => $s->nisn,
                    'guru' => $s->nama_guru,
                    'tempat' => $s->tempat_pkl ?? 'Belum Diatur',
                    'status' => $statusData,
                    'id_guru' => $s->id_guru,
                    'id_penempatan' => $s->id_penempatan,
                    'sakit' => $s->sakit ?? 0,
                    'ijin' => $s->izin ?? 0,
                    'alpha' => $s->alpa ?? 0,
                    'catatan_short' => Str::limit($s->catatan_pembimbing ?? '-', 45),
                    'catatan_full' => $s->catatan_pembimbing ?? '-'
                ];
            });

            // Format object per kelas
            $monitoringData[] = (object)[
                'kelas' => (object)['id_kelas' => $id_kelas, 'nama_kelas' => $kelasInfo->nama_kelas],
                'wali_kelas' => $kelasInfo->wali_kelas ?? 'Belum diset',
                'jml_siswa' => $jml_siswa,
                'siswa_selesai' => $siswa_selesai,
                'persen' => $persen,
                'detail_siswa' => $detail_siswa
            ];
        }

        // Urutkan berdasarkan Nama Kelas A-Z
        usort($monitoringData, function($a, $b) {
            return strcmp($a->kelas->nama_kelas, $b->kelas->nama_kelas);
        });

        // 4. Hitung Statistik Global
        $stats = [
            'total_rombel' => count($monitoringData),
            'persen_global' => $global_siswa_total > 0 ? round(($global_siswa_final / $global_siswa_total) * 100) : 0,
            'siswa_final' => $global_siswa_final,
            'siswa_total' => $global_siswa_total
        ];

        return view('pkl.rapor.monitor', compact('tahun_ajaran', 'semester', 'tahunAjaranList', 'monitoringData', 'stats'));
    }
}