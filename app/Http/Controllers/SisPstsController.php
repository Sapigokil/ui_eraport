<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SisPstsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->id_siswa) {
            return redirect()->back()->with('error', 'Akun Anda belum ditautkan dengan data Induk Siswa.');
        }

        $id_siswa = $user->id_siswa;

        $sumatifPeriods = DB::table('sumatif')
            ->join('kelas', 'sumatif.id_kelas', '=', 'kelas.id_kelas')
            ->where('sumatif.id_siswa', $id_siswa)
            ->select('sumatif.tahun_ajaran', 'sumatif.semester', 'kelas.nama_kelas', 'kelas.id_kelas')
            ->distinct()
            ->get();

        $projectPeriods = DB::table('project')
            ->join('kelas', 'project.id_kelas', '=', 'kelas.id_kelas')
            ->where('project.id_siswa', $id_siswa)
            ->select('project.tahun_ajaran', 'project.semester', 'kelas.nama_kelas', 'kelas.id_kelas')
            ->distinct()
            ->get();

        // Gabungkan data periode
        $riwayat_psts = $sumatifPeriods->merge($projectPeriods)->unique(function ($item) {
            return $item->tahun_ajaran . '-' . $item->semester . '-' . $item->id_kelas;
        });

        // Urutkan: Tahun Ajaran (Kecil ke Besar) -> Semester (1 ke 2)
        $riwayat_psts = $riwayat_psts->sort(function ($a, $b) {
            if ($a->tahun_ajaran == $b->tahun_ajaran) {
                return $a->semester <=> $b->semester; 
            }
            return $a->tahun_ajaran <=> $b->tahun_ajaran; 
        })->values();

        // Kelompokkan berdasarkan Tahun Ajaran
        $grouped_psts = $riwayat_psts->groupBy('tahun_ajaran');

        return view('sismenu.psts.index', compact('grouped_psts'));
    }

    public function detail($tahun_ajaran, $semester, $id_kelas)
    {
        $user = Auth::user();
        $id_siswa = $user->id_siswa;
        
        // Kembalikan tanda strip (-) menjadi garis miring (/)
        $ta = str_replace('-', '/', $tahun_ajaran);

        $kelas = DB::table('kelas')->where('id_kelas', $id_kelas)->first();

        if (!$kelas) {
            abort(404, 'Data Kelas tidak ditemukan.');
        }

        // 1. Ambil data nilai dari tabel 'sumatif' (Join ke mata_pelajaran)
        $raw_sumatif = DB::table('sumatif')
            ->join('mata_pelajaran', 'sumatif.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('sumatif.id_siswa', $id_siswa)
            ->where('sumatif.tahun_ajaran', $ta)
            ->where('sumatif.semester', $semester)
            ->where('sumatif.id_kelas', $id_kelas)
            ->select(
                'mata_pelajaran.nama_mapel', 
                'mata_pelajaran.kategori', 
                'sumatif.sumatif as jenis_penilaian', 
                'sumatif.nilai'
            )
            ->get();

        // 2. Ambil data nilai dari tabel 'project'
        $raw_project = DB::table('project')
            ->join('mata_pelajaran', 'project.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('project.id_siswa', $id_siswa)
            ->where('project.tahun_ajaran', $ta)
            ->where('project.semester', $semester)
            ->where('project.id_kelas', $id_kelas)
            ->select(
                'mata_pelajaran.nama_mapel', 
                'mata_pelajaran.kategori',
                DB::raw("'Project' as jenis_penilaian"), 
                'project.nilai'
            )
            ->get();

        $raw_nilai = $raw_sumatif->merge($raw_project);

        $data_psts = [];
        $jenis_penilaian_unik = []; 

        // ✅ LABEL TRANSLASI KATEGORI
        $kategoriLabel = [
            1 => 'Mata Pelajaran Umum',
            2 => 'Mata Pelajaran Kejuruan',
            3 => 'Mata Pelajaran Pilihan',
            4 => 'Muatan Lokal',
        ];

        foreach ($raw_nilai as $n) {
            $id_kategori = $n->kategori;
            $kelompok = $kategoriLabel[$id_kategori] ?? 'Lainnya'; 
            
            $data_psts[$kelompok][$n->nama_mapel][$n->jenis_penilaian] = $n->nilai;
            
            if (!in_array($n->jenis_penilaian, $jenis_penilaian_unik)) {
                $jenis_penilaian_unik[] = $n->jenis_penilaian;
            }
        }

        // Urutkan jenis penilaian agar S1, S2... berurutan, Project di akhir
        usort($jenis_penilaian_unik, function($a, $b) {
            if ($a === 'Project') return 1;
            if ($b === 'Project') return -1;
            return strcmp($a, $b);
        });

        // Urutkan kelompok mapel
        uksort($data_psts, function($a, $b) use ($kategoriLabel) {
            $posA = array_search($a, $kategoriLabel);
            $posB = array_search($b, $kategoriLabel);
            
            $posA = $posA === false ? 99 : $posA;
            $posB = $posB === false ? 99 : $posB;
            
            return $posA <=> $posB;
        });

        // Mengurutkan nama mapel di dalam masing-masing kelompok secara alfabetis
        foreach ($data_psts as $kel => &$mapels) {
            ksort($mapels);
        }

        return view('sismenu.psts.detail', compact('data_psts', 'jenis_penilaian_unik', 'ta', 'semester', 'kelas'));
    }

    // =========================================================================
    // METHOD BARU: CETAK PDF PSTS
    // =========================================================================
    public function cetak($tahun_ajaran, $semester, $id_kelas, $jenis)
    {
        $user = Auth::user();
        $id_siswa = $user->id_siswa;
        
        $ta = str_replace('-', '/', $tahun_ajaran);

        // 1. DATA SISWA & KELAS
        $siswa = Siswa::with('kelas')->where('id_siswa', $id_siswa)->firstOrFail();
        $kelas = DB::table('kelas')->where('id_kelas', $id_kelas)->first();

        // Penentuan Fase (Tingkat 10 = E, Tingkat 11/12 = F)
        $fase = (isset($kelas->tingkat) && $kelas->tingkat == 10) ? 'E' : 'F';

        // 2. DATA INFO SEKOLAH
        $infoSekolah = DB::table('info_sekolah')->first();

        // 3. TARIK DATA NILAI KHUSUS JENIS YANG DIPILIH (BESERTA CAPAIAN)
        $kategoriLabel = [
            1 => 'MATA PELAJARAN UMUM',
            2 => 'MATA PELAJARAN KEJURUAN',
            3 => 'MATA PELAJARAN PILIHAN',
            4 => 'MUATAN LOKAL',
        ];

        $data_psts = [];

        if ($jenis === 'Project') {
            $raw_data = DB::table('project')
                ->join('mata_pelajaran', 'project.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('project.id_siswa', $id_siswa)
                ->where('project.tahun_ajaran', $ta)
                ->where('project.semester', $semester)
                ->where('project.id_kelas', $id_kelas)
                ->select(
                    'mata_pelajaran.nama_mapel', 
                    'mata_pelajaran.kategori', 
                    'project.nilai',
                    'project.tujuan_pembelajaran as capaian' // Langsung dari tabel project
                )
                ->get();
        } else {
            // Jika Sumatif (Angka 1, 2, dst)
            $raw_data = DB::table('sumatif')
                ->join('mata_pelajaran', 'sumatif.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('sumatif.id_siswa', $id_siswa)
                ->where('sumatif.tahun_ajaran', $ta)
                ->where('sumatif.semester', $semester)
                ->where('sumatif.id_kelas', $id_kelas)
                ->where('sumatif.sumatif', $jenis)
                ->select(
                    'mata_pelajaran.nama_mapel', 
                    'mata_pelajaran.kategori', 
                    'sumatif.nilai',
                    'sumatif.tujuan_pembelajaran as capaian' // Langsung dari tabel sumatif
                )
                ->get();
        }

        // Format ulang ke array untuk di-loop di view PDF
        foreach ($raw_data as $row) {
            $kelompok = $kategoriLabel[$row->kategori] ?? 'LAINNYA';
            
            $data_psts[$kelompok][$row->nama_mapel] = [
                'nilai' => $row->nilai,
                'capaian' => $row->capaian ?? '-'
            ];
        }

        // Urutkan kelompok mapel
        uksort($data_psts, function($a, $b) use ($kategoriLabel) {
            $posA = array_search($a, $kategoriLabel);
            $posB = array_search($b, $kategoriLabel);
            $posA = $posA === false ? 99 : $posA;
            $posB = $posB === false ? 99 : $posB;
            return $posA <=> $posB;
        });

        // Urutkan mapel secara alfabetis
        foreach ($data_psts as $kel => &$mapels) {
            ksort($mapels);
        }

        $semesterInt = $semester;

        // 4. LOAD PDF (Variabel guru wali tidak dikirim lagi karena dihapus)
        $pdf = Pdf::loadView('sismenu.psts.data', compact(
            'siswa', 'kelas', 'infoSekolah', 'tahun_ajaran', 'semester', 'semesterInt',
            'fase', 'jenis', 'data_psts'
        ))->setPaper('a4', 'portrait');

        $nama_file = "PSTS_" . str_replace('/', '-', $tahun_ajaran) . "_Smt{$semester}_" . strtoupper($siswa->nama_siswa) . "_" . ($jenis == 'Project' ? 'Project' : "Sumatif_{$jenis}") . ".pdf";

        return $pdf->stream($nama_file);
    }
}