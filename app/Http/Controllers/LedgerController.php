<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\InfoSekolah;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LedgerTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
private function sortLedger($dataLedger)
{
    return collect($dataLedger)
        ->sort(function ($a, $b) {

            // 1️⃣ rata-rata DESC
            $cmp = $b->rata_rata <=> $a->rata_rata;
            if ($cmp !== 0) {
                return $cmp;
            }

            // 2️⃣ kalau sama → nama A-Z
            return strcmp($a->nama_siswa, $b->nama_siswa);
        })
        ->values()
        ->all();
}
  
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        // 🔥 TAMBAHKAN DI SINI
        $jurusanList = Kelas::select('jurusan')
        ->whereNotNull('jurusan')
        ->distinct()
        ->orderBy('jurusan')
        ->pluck('jurusan');

        $mode = $request->mode ?? 'kelas';
        $id_kelas = $request->id_kelas;
        $jurusan  = $request->jurusan;
        $tingkat  = $request->tingkat;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL') ? 1 : 2;

        $daftarMapel = [];
        $dataLedger = [];

        /* =========================
        MODE KELAS
        ========================= */
        if ($mode === 'kelas' && $id_kelas) {

            $daftarMapel = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $id_kelas)
                ->where('mata_pelajaran.is_active', 1) //hanya tampilkan mapel active=1 di db
                ->orderBy('mata_pelajaran.kategori')
                ->orderBy('mata_pelajaran.urutan')
                ->select(
                    'mata_pelajaran.id_mapel',
                    'mata_pelajaran.nama_mapel',
                    'mata_pelajaran.nama_singkat',
                    'mata_pelajaran.kategori',
                    DB::raw("
                    CASE 
                        WHEN mata_pelajaran.nama_mapel LIKE '%Agama%' 
                        THEN 'AGAMA' 
                        ELSE mata_pelajaran.id_mapel 
                    END AS mapel_key
                ") //merge mapel agama
                )
                ->get();

                // ===================
                //  (MERGE MAPEL AGAMA)
                // ===================
                $daftarMapel = $daftarMapel
                    ->groupBy('mapel_key')
                    ->map(function ($items) {
                        $first = $items->first();

                        // KHUSUS AGAMA
                        if ($first->mapel_key === 'AGAMA') {
                            return (object)[
                                'id_mapel'     => 'AGAMA',
                                'nama_mapel'   => 'Agama',
                                'nama_singkat'=> 'Agama',
                                'kategori'     => $first->kategori,
                            ];
                        }

                        // MAPEL NORMAL
                        return (object)[
                            'id_mapel'     => $first->id_mapel,
                            'nama_mapel'   => $first->nama_mapel,
                            'nama_singkat'=> $first->nama_singkat,
                            'kategori'     => $first->kategori,
                        ];
                    })
                    ->values();


            // 2. Ambil data siswa di kelas tersebut
            $siswaList = Siswa::where('id_kelas', $id_kelas)
                ->orderBy('nama_siswa')
                ->get();

            // 🔥 POINT 1 — ambil semua nilai SEKALIGUS
            $nilaiList = DB::table('nilai_akhir')
                ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
                ->whereIn('id_mapel', $daftarMapel->pluck('id_mapel'))
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', trim($tahun_ajaran))
                ->get()
                ->groupBy(fn ($n) => $n->id_siswa.'-'.$n->id_mapel);

            // 🔥 POINT 2 — ambil semua absensi SEKALIGUS
            $absensiList = DB::table('catatan')
                ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', trim($tahun_ajaran))
                ->get()
                ->keyBy('id_siswa');

            foreach ($siswaList as $siswa) {
    $nilaiPerMapel = [];
    $totalNilai = 0;
    $jumlahMapelTerisi = 0;

    foreach ($daftarMapel as $mapel) {
        $key = $siswa->id_siswa.'-'.$mapel->id_mapel;
        // $score = $nilaiList[$key][0]->nilai_akhir ?? 0;
        $score = (int) round($nilaiList[$key][0]->nilai_akhir ?? 0);


        $nilaiPerMapel[$mapel->id_mapel] = $score;

        if ($score > 0) {
            $totalNilai += $score;
            $jumlahMapelTerisi++;
        }
    }

    $absensi = $absensiList[$siswa->id_siswa] ?? null;

    $dataLedger[] = (object)[
        'nama_siswa' => $siswa->nama_siswa,
        'nipd'       => $siswa->nipd,
        'scores'     => $nilaiPerMapel,
        'total'      => (int) $totalNilai,
        'rata_rata'  => $jumlahMapelTerisi ? (int) round($totalNilai / $jumlahMapelTerisi) : 0,
        'absensi'    => (object)[
            'sakit' => $absensi->sakit ?? 0,
            'izin'  => $absensi->ijin ?? 0,
            'alpha' => $absensi->alpha ?? 0,
        ]
    ];
}

        }

        /* =========================
        MODE JURUSAN
        ========================= */
        if ($mode === 'jurusan' && $jurusan) {

            // 1️⃣ Ambil semua kelas dalam jurusan
            // $kelasIds = Kelas::where('jurusan', $jurusan)
            //     ->pluck('id_kelas');
            $kelasQuery = Kelas::where('jurusan', $jurusan);

            if (!empty($tingkat)) {
            $kelasQuery->where(function ($q) use ($tingkat) {
                $q->where('nama_kelas', 'LIKE', $tingkat . '%')
                ->orWhere('nama_kelas', 'LIKE', 'X' . $tingkat . '%');
            });
        }

            $kelasIds = $kelasQuery->pluck('id_kelas');


            // 2️⃣ Ambil daftar mapel gabungan
            $daftarMapel = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->whereIn('pembelajaran.id_kelas', $kelasIds)
                ->where('mata_pelajaran.is_active', 1) //hanya tampilkan mapel active=1 di db
                ->orderBy('mata_pelajaran.kategori', 'asc')
                ->orderBy('mata_pelajaran.urutan', 'asc')
                ->select(
                    'mata_pelajaran.id_mapel',
                    'mata_pelajaran.nama_mapel',
                    'mata_pelajaran.nama_singkat',
                    'mata_pelajaran.kategori',
                    'mata_pelajaran.urutan',
                    DB::raw("
                    CASE 
                        WHEN mata_pelajaran.nama_mapel LIKE '%Agama%' 
                        THEN 'AGAMA' 
                        ELSE mata_pelajaran.id_mapel 
                    END AS mapel_key
                ") //merge mapel agama
                )
                ->distinct()
                ->get();

            // 3️⃣ Ambil semua siswa di jurusan
            $siswaList = Siswa::whereIn('id_kelas', $kelasIds)
                ->orderBy('nama_siswa')
                ->get();

            $nilaiList = DB::table('nilai_akhir')
                ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
                ->whereIn('id_mapel', $daftarMapel->pluck('id_mapel'))
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', trim($tahun_ajaran))
                ->get()
                ->groupBy(fn ($n) => $n->id_siswa.'-'.$n->id_mapel);

            $absensiList = DB::table('catatan')
                ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', trim($tahun_ajaran))
                ->get()
                ->keyBy('id_siswa');
                        
                foreach ($siswaList as $siswa) {

                $nilaiPerMapel = [];
                $totalNilai = 0;
                $jumlahMapelTerisi = 0;

                foreach ($daftarMapel as $mapel) {

                    $key = $siswa->id_siswa . '-' . $mapel->id_mapel;
                    // $score = $nilaiList[$key][0]->nilai_akhir ?? 0;
                    $score = (int) round($nilaiList[$key][0]->nilai_akhir ?? 0);


                    $nilaiPerMapel[$mapel->id_mapel] = $score;

                    if ($score > 0) {
                        $totalNilai += $score;
                        $jumlahMapelTerisi++;
                    }
                }

                $absensi = $absensiList[$siswa->id_siswa] ?? null;

                $dataLedger[] = (object)[
                    'nama_siswa' => $siswa->nama_siswa,
                    'nipd'       => $siswa->nipd,
                    'scores'     => $nilaiPerMapel,
                    'total'      => (int) $totalNilai,
                    'rata_rata'  => $jumlahMapelTerisi
                        ? (int) round($totalNilai / $jumlahMapelTerisi)
                        : 0,
                    'absensi'    => (object)[
                        'sakit' => $absensi->sakit ?? 0,
                        'izin'  => $absensi->ijin ?? 0,
                        'alpha' => $absensi->alpha ?? 0,
                    ]
                ];
            }
        }
        // 🔥 TAMBAHKAN DI SINI (WAJIB)
        // $dataLedger = $this->sortLedger($dataLedger);

        // ================= SORTING SESUAI FILTER =================
        $urut = $request->urut ?? 'ranking';

        if ($urut === 'ranking') {
            // Ranking nilai (rata-rata DESC)
            $dataLedger = $this->sortLedger($dataLedger);
        } elseif ($urut === 'absen') {
            // Nomor absen = urut alfabet nama siswa
            $dataLedger = collect($dataLedger)
                ->sortBy(fn ($row) => strtolower($row->nama_siswa))
                ->values()
                ->all();
        }
        // =========================================================

        return view('rapor.ledger_index', compact(
            'kelas', 
            'jurusanList',
            'mode',
            'id_kelas', 
            'jurusan',
            'semesterRaw', 
            'tahun_ajaran', 
            'daftarMapel', 
            'dataLedger'
        ));
    }

    private function buildFilename(Request $request, string $ext): string
    {
        $kelas = Kelas::find($request->id_kelas);

        $namaKelas = $kelas
            ? preg_replace('/[^A-Za-z0-9\-]/', '_', $kelas->nama_kelas)
            : 'Tanpa_Kelas';

        $semester = $request->semester ?? 'Ganjil';
        $tahun = str_replace('/', '-', $request->tahun_ajaran ?? 'Tahun');

        return "Ledger_{$namaKelas}_{$semester}_{$tahun}.{$ext}";
    }


    public function exportExcel(Request $request)
    {
        $filename = $this->buildFilename($request, 'xlsx');
        return Excel::download(
            new LedgerTemplateExport($request),
            $filename
        );
    }
    public function exportPdf(Request $request)
    {
        $data = $this->buildLedgerData($request);
        $filename = $this->buildFilename($request, 'pdf');

        $pdf = Pdf::loadView('rapor.ledger_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream($filename);
    }

    public function buildLedgerData(Request $request): array
    {
        $mode = $request->mode ?? 'kelas';
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = strtoupper($semesterRaw) === 'GANJIL' ? 1 : 2;

        /*
        |--------------------------------------------------------------------------
        | TENTUKAN KELAS IDS (INI KUNCINYA 🔑)
        |--------------------------------------------------------------------------
        */
        if ($mode === 'kelas') {
            $id_kelas = $request->id_kelas;
            $kelasIds = collect([$id_kelas]);
            $kelas = Kelas::find($id_kelas);
        } else {
            // MODE JURUSAN
            $jurusan = $request->jurusan;
            $tingkat = $request->tingkat;

            $kelasQuery = Kelas::where('jurusan', $jurusan);

            if (!empty($tingkat)) {
                $kelasQuery->where(function ($q) use ($tingkat) {
                    $q->where('nama_kelas', 'LIKE', $tingkat.'%')
                    ->orWhere('nama_kelas', 'LIKE', 'X'.$tingkat.'%');
                });
            }

            $kelasIds = $kelasQuery->pluck('id_kelas');

            // dummy object biar PDF tidak error
            $kelas = (object)[
                'nama_kelas' => 'Jurusan ' . $jurusan
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | INFO SEKOLAH
        |--------------------------------------------------------------------------
        */
        $infoSekolah = InfoSekolah::first();

        $namaSekolah   = $infoSekolah->nama_sekolah ?? 'NAMA SEKOLAH';
        $alamatSekolah = implode(', ', array_filter([
            $infoSekolah->jalan ?? null,
            $infoSekolah->kelurahan ?? null,
            $infoSekolah->kecamatan ?? null,
            $infoSekolah->kota_kab ?? null,
            $infoSekolah->provinsi ?? null,
            $infoSekolah->kode_pos ?? null,
        ]));

        $nama_wali = $kelas->wali_kelas ?? '-';
        $nip_wali  = '-';

        /*
        |--------------------------------------------------------------------------
        | MAPEL & SISWA (PAKAI whereIn)
        |--------------------------------------------------------------------------
        */
        $daftarMapel = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->whereIn('pembelajaran.id_kelas', $kelasIds)
            ->where('mata_pelajaran.is_active', 1)
            ->orderBy('mata_pelajaran.kategori')
            ->orderBy('mata_pelajaran.nama_mapel')
            ->select('mata_pelajaran.*')
            ->distinct()
            ->get();

        $siswaList = Siswa::whereIn('id_kelas', $kelasIds)
            ->orderBy('nama_siswa')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | NILAI & ABSENSI
        |--------------------------------------------------------------------------
        */
        $nilaiList = DB::table('nilai_akhir')
            ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
            ->whereIn('id_mapel', $daftarMapel->pluck('id_mapel'))
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', trim($tahun_ajaran))
            ->get()
            ->groupBy(fn ($n) => $n->id_siswa.'-'.$n->id_mapel);

        $absensiList = DB::table('catatan')
            ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', trim($tahun_ajaran))
            ->get()
            ->keyBy('id_siswa');

        /*
        |--------------------------------------------------------------------------
        | BUILD LEDGER
        |--------------------------------------------------------------------------
        */
        $dataLedger = [];

        foreach ($siswaList as $siswa) {
            $total = 0;
            $count = 0;
            $scores = [];

            foreach ($daftarMapel as $mapel) {
                $key = $siswa->id_siswa.'-'.$mapel->id_mapel;
                $score = (int) round($nilaiList[$key][0]->nilai_akhir ?? 0);

                $scores[$mapel->id_mapel] = $score;

                if ($score > 0) {
                    $total += $score;
                    $count++;
                }
            }

            $absen = $absensiList[$siswa->id_siswa] ?? null;

            $dataLedger[] = (object)[
                'nama_siswa' => $siswa->nama_siswa,
                'nipd' => $siswa->nipd,
                'scores' => $scores,
                'total' => $total,
                'rata_rata' => $count ? (int) round($total / $count) : 0,
                'absensi' => (object)[
                    'sakit' => $absen->sakit ?? 0,
                    'izin'  => $absen->ijin ?? 0,
                    'alpha' => $absen->alpha ?? 0,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SORTING (RANKING / ABSEN)
        |--------------------------------------------------------------------------
        */
        $urut = $request->urut ?? 'ranking';

        if ($urut === 'ranking') {
            $dataLedger = $this->sortLedger($dataLedger);
        } else {
            $dataLedger = collect($dataLedger)
                ->sortBy(fn ($r) => strtolower($r->nama_siswa))
                ->values()
                ->all();
        }

        return compact(
            'namaSekolah',
            'alamatSekolah',
            'kelas',
            'daftarMapel',
            'dataLedger',
            'semesterRaw',
            'tahun_ajaran',
            'nama_wali',
            'nip_wali'
        );
    }



}