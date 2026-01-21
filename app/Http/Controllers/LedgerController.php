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
    /**
     * Helper untuk sorting data ledger (Ranking)
     */
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

    /**
     * Helper untuk membuat nama file export
     */
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

    /**
     * Halaman Utama Ledger (Web View)
     */
    public function index(Request $request)
    {
        // 1. Data Pendukung Filter
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $jurusanList = Kelas::select('jurusan')
            ->whereNotNull('jurusan')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan');

        // 2. Ambil Parameter Request
        $mode = $request->mode ?? 'kelas';
        $id_kelas = $request->id_kelas;
        $jurusan  = $request->jurusan;
        $tingkat  = $request->tingkat;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL') ? 1 : 2;

        // Inisialisasi Variabel View
        $daftarMapel = collect();
        $dataLedger = [];

        // ============================================================
        // TAHAP 1: TENTUKAN LINGKUP KELAS (SCOPE)
        // ============================================================
        $kelasIds = [];

        if ($mode === 'kelas' && $id_kelas) {
            $kelasIds = [$id_kelas];
        } elseif ($mode === 'jurusan' && $jurusan) {
            $kelasQuery = Kelas::where('jurusan', $jurusan);

            if (!empty($tingkat)) {
                $kelasQuery->where(function ($q) use ($tingkat) {
                    $q->where('nama_kelas', 'LIKE', $tingkat . '%')
                        ->orWhere('nama_kelas', 'LIKE', 'X' . $tingkat . '%');
                });
            }
            $kelasIds = $kelasQuery->pluck('id_kelas')->toArray();
        }

        // Jika tidak ada kelas terpilih/valid, return view kosong
        if (empty($kelasIds)) {
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

        // ============================================================
        // TAHAP 2: AMBIL DATA (Logic Terpusat)
        // ============================================================
        // Kita gunakan method buildDataCore agar logic sama dengan PDF/Excel
        $coreData = $this->buildDataCore($kelasIds, $semesterInt, $tahun_ajaran);
        
        $daftarMapel = $coreData['daftarMapel']; // Header Mapel (Grouped)
        $dataLedger  = $coreData['dataLedger'];  // Data Nilai Siswa

        // ============================================================
        // TAHAP 3: SORTING TAMPILAN
        // ============================================================
        $urut = $request->urut ?? 'ranking';

        if ($urut === 'ranking') {
            $dataLedger = $this->sortLedger($dataLedger);
        } else {
            // Sort by Nama (Absen)
            $dataLedger = collect($dataLedger)
                ->sortBy(fn ($r) => strtolower($r->nama_siswa))
                ->values()
                ->all();
        }

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

    /**
     * CORE LOGIC: Membangun Data Ledger (Dipakai Index, PDF, & Excel)
     * Agar logic 'AGAMA' konsisten di semua tempat.
     */
    private function buildDataCore($kelasIds, $semesterInt, $tahun_ajaran)
    {
        // A. Ambil Data Mapel Mentah (Raw) - PENTING untuk ID Asli
        $rawMapelData = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->whereIn('pembelajaran.id_kelas', $kelasIds)
            ->where('mata_pelajaran.is_active', 1)
            ->select(
                'mata_pelajaran.id_mapel',
                'mata_pelajaran.nama_mapel',
                'mata_pelajaran.nama_singkat',
                'mata_pelajaran.kategori',
                'mata_pelajaran.urutan',
                DB::raw("
                    CASE 
                        WHEN mata_pelajaran.nama_mapel LIKE '%Agama%' THEN 'AGAMA' 
                        ELSE mata_pelajaran.id_mapel 
                    END AS mapel_key
                ")
            )
            ->distinct() // Hindari duplikat jika mode jurusan (banyak kelas punya mapel sama)
            ->orderBy('mata_pelajaran.kategori')
            ->orderBy('mata_pelajaran.urutan')
            ->get();

        // B. Simpan Array ID Asli untuk Query Nilai
        $allMapelIds = $rawMapelData->pluck('id_mapel')->toArray();

        // C. Simpan Array ID Khusus Agama (Misal: 1, 2, 3...)
        $agamaMapelIds = $rawMapelData
            ->where('mapel_key', 'AGAMA')
            ->pluck('id_mapel')
            ->toArray();

        // D. Buat Daftar Mapel untuk Header Tabel (Grouping)
        $daftarMapel = $rawMapelData
            ->groupBy('mapel_key')
            ->map(function ($items) {
                $first = $items->first();
                // Jika Group Agama, paksa properti jadi 'Agama'
                if ($first->mapel_key === 'AGAMA') {
                    return (object)[
                        'id_mapel'     => 'AGAMA',
                        'nama_mapel'   => 'Pendidikan Agama',
                        'nama_singkat' => 'Agama',
                        'kategori'     => $first->kategori,
                        'urutan'       => $first->urutan
                    ];
                }
                // Mapel Biasa
                return (object)[
                    'id_mapel'     => $first->id_mapel,
                    'nama_mapel'   => $first->nama_mapel,
                    'nama_singkat' => $first->nama_singkat,
                    'kategori'     => $first->kategori,
                    'urutan'       => $first->urutan
                ];
            })
            ->sortBy(fn($m) => $m->kategori . '-' . $m->urutan)
            ->values();

        // E. Ambil Siswa
        $siswaList = Siswa::whereIn('id_kelas', $kelasIds)
            ->orderBy('nama_siswa')
            ->get();

        // F. Ambil Nilai (Gunakan ID ASLI $allMapelIds)
        $nilaiList = DB::table('nilai_akhir')
            ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
            ->whereIn('id_mapel', $allMapelIds) // <-- PAKE ID ASLI
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', trim($tahun_ajaran))
            ->get()
            ->groupBy(fn ($n) => $n->id_siswa . '-' . $n->id_mapel); // Key: "100-1"

        // G. Ambil Absensi
        $absensiList = DB::table('catatan')
            ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', trim($tahun_ajaran))
            ->get()
            ->keyBy('id_siswa');

        // H. Build Data Siswa
        $dataLedger = [];

        foreach ($siswaList as $siswa) {
            $nilaiPerMapel = [];
            $totalNilai = 0;
            $jumlahMapelTerisi = 0;

            foreach ($daftarMapel as $mapel) {
                $score = 0;

                // LOGIKA KHUSUS AGAMA (Cari salah satu ID Agama yang cocok)
                if ($mapel->id_mapel === 'AGAMA') {
                    foreach ($agamaMapelIds as $idAgamaAsli) {
                        $key = $siswa->id_siswa . '-' . $idAgamaAsli;
                        if (isset($nilaiList[$key])) {
                            // Ambil nilai pertama yang ketemu (karena siswa biasanya cuma punya 1 agama)
                            $val = $nilaiList[$key][0]->nilai_akhir ?? 0;
                            $score = (int) round($val);

                            if ($score > 0) break; // Keluar loop agama jika nilai ketemu
                        }
                    }
                }
                // LOGIKA MAPEL BIASA
                else {
                    $key = $siswa->id_siswa . '-' . $mapel->id_mapel;
                    if (isset($nilaiList[$key])) {
                        $val = $nilaiList[$key][0]->nilai_akhir ?? 0;
                        $score = (int) round($val);
                    }
                }

                // Masukkan ke array skor
                $nilaiPerMapel[$mapel->id_mapel] = $score;

                if ($score > 0) {
                    $totalNilai += $score;
                    $jumlahMapelTerisi++;
                }
            }

            // Ambil Data Absen
            $absensi = $absensiList[$siswa->id_siswa] ?? null;

            // Push ke Data Ledger Utama
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

        return [
            'daftarMapel' => $daftarMapel,
            'dataLedger'  => $dataLedger,
            'kelasObj'    => Kelas::find($kelasIds[0] ?? null) // Untuk info wali kelas (ambil sampel kelas pertama)
        ];
    }

    /**
     * Export Excel
     */
    public function exportExcel(Request $request)
    {
        $filename = $this->buildFilename($request, 'xlsx');
        return Excel::download(
            new LedgerTemplateExport($request),
            $filename
        );
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        // Gunakan fungsi buildDataCore agar data PDF sama persis dengan Web
        $mode = $request->mode ?? 'kelas';
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = strtoupper($semesterRaw) === 'GANJIL' ? 1 : 2;

        // Re-logic penentuan Kelas IDs (Duplikasi logic dari index sedikit)
        $kelasIds = [];
        $namaKelasLabel = '';

        if ($mode === 'kelas') {
            $id_kelas = $request->id_kelas;
            $kelasIds = [$id_kelas];
            $kelasObj = Kelas::find($id_kelas);
            $namaKelasLabel = $kelasObj ? $kelasObj->nama_kelas : '-';
        } else {
            $jurusan = $request->jurusan;
            $tingkat = $request->tingkat;
            $kelasQuery = Kelas::where('jurusan', $jurusan);
            if (!empty($tingkat)) {
                $kelasQuery->where(function ($q) use ($tingkat) {
                    $q->where('nama_kelas', 'LIKE', $tingkat . '%')
                        ->orWhere('nama_kelas', 'LIKE', 'X' . $tingkat . '%');
                });
            }
            $kelasIds = $kelasQuery->pluck('id_kelas')->toArray();
            $namaKelasLabel = 'Jurusan ' . $jurusan;
            $kelasObj = Kelas::find($kelasIds[0] ?? 0);
        }

        // Panggil Core Logic
        $core = $this->buildDataCore($kelasIds, $semesterInt, $tahun_ajaran);
        $dataLedger = $this->sortLedger($core['dataLedger']); // Default PDF Ranking

        // Info Sekolah
        $infoSekolah = InfoSekolah::first();
        $namaSekolah = $infoSekolah->nama_sekolah ?? 'NAMA SEKOLAH';
        $alamatSekolah = implode(', ', array_filter([
            $infoSekolah->jalan ?? null,
            $infoSekolah->kelurahan ?? null,
            $infoSekolah->kecamatan ?? null,
            $infoSekolah->kota_kab ?? null
        ]));

        $nama_wali = $kelasObj->wali_kelas ?? '-';
        $nip_wali  = '-';

        $dataView = [
            'namaSekolah'   => $namaSekolah,
            'alamatSekolah' => $alamatSekolah,
            'kelas'         => (object)['nama_kelas' => $namaKelasLabel],
            'daftarMapel'   => $core['daftarMapel'],
            'dataLedger'    => $dataLedger,
            'semesterRaw'   => $semesterRaw,
            'tahun_ajaran'  => $tahun_ajaran,
            'nama_wali'     => $nama_wali,
            'nip_wali'      => $nip_wali
        ];

        $filename = $this->buildFilename($request, 'pdf');

        $pdf = Pdf::loadView('rapor.ledger_pdf', $dataView)
            ->setPaper('a4', 'landscape');

        return $pdf->stream($filename);
    }
}