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
     * Helper: Mendapatkan List ID Kelas berdasarkan Request (Mode Kelas / Jurusan)
     */
    private function getKelasIdsFromRequest(Request $request)
    {
        $mode = $request->mode ?? 'kelas';

        if ($mode === 'kelas') {
            return $request->id_kelas ? [$request->id_kelas] : [];
        }

        // Mode Jurusan
        $jurusan = $request->jurusan;
        $tingkat = $request->tingkat;
        
        $kelasQuery = Kelas::where('jurusan', $jurusan);

        if (!empty($tingkat)) {
            $kelasQuery->where(function ($q) use ($tingkat) {
                $q->where('nama_kelas', 'LIKE', $tingkat . '%')
                    ->orWhere('nama_kelas', 'LIKE', 'X' . $tingkat . '%');
            });
        }
        
        return $kelasQuery->pluck('id_kelas')->toArray();
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

        // 3. Ambil Kelas IDs
        $kelasIds = $this->getKelasIdsFromRequest($request);

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

        // 4. Ambil Data Core
        $coreData = $this->buildDataCore($kelasIds, $semesterInt, $tahun_ajaran);
        
        $daftarMapel = $coreData['daftarMapel'];
        $dataLedger  = $coreData['dataLedger'];

        // 5. Sorting Tampilan
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
     * 🟢 PUBLIC API: Digunakan oleh LedgerTemplateExport (EXCEL)
     * Method ini menjembatani antara Request Excel dengan Core Logic
     */
    public function buildLedgerData(Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL') ? 1 : 2;

        // Ambil ID Kelas dari logic terpusat
        $kelasIds = $this->getKelasIdsFromRequest($request);

        // Panggil Logic Utama (Ambil Nilai, Mapel, Siswa)
        $data = $this->buildDataCore($kelasIds, $semesterInt, $tahun_ajaran);

        // ---------------------------------------------------------
        // PERBAIKAN: Tambahkan Data Info Sekolah di sini
        // ---------------------------------------------------------
        $infoSekolah = InfoSekolah::first();
        
        $data['namaSekolah'] = $infoSekolah->nama_sekolah ?? 'NAMA SEKOLAH BELUM DISET';
        
        $data['alamatSekolah'] = implode(', ', array_filter([
            $infoSekolah->jalan ?? null,
            $infoSekolah->kelurahan ?? null,
            $infoSekolah->kecamatan ?? null,
            $infoSekolah->kota_kab ?? null
        ]));

        // Tambahkan Metadata Lainnya
        $data['semester'] = $semesterRaw;
        $data['tahun_ajaran'] = $tahun_ajaran;
        
        // Ambil Nama Kelas (jika mode kelas)
        if (!empty($kelasIds)) {
            $kelasObj = Kelas::find($kelasIds[0]);
            $data['namaKelas'] = $kelasObj ? $kelasObj->nama_kelas : 'Semua Kelas';
            $data['waliKelas'] = $kelasObj ? ($kelasObj->wali_kelas ?? '-') : '-';
        } else {
            $data['namaKelas'] = 'Semua Kelas';
            $data['waliKelas'] = '-';
        }
        
        // Sorting default ranking untuk Excel
        $data['dataLedger'] = $this->sortLedger($data['dataLedger']);

        return $data;
    }

    /**
     * CORE LOGIC: Membangun Data Ledger (FIX ZOMBIE DATA & GLOBAL AGAMA)
     */
    private function buildDataCore($kelasIds, $semesterInt, $tahun_ajaran)
    {
        // -----------------------------------------------------------
        // A. Ambil Data Mapel (Header Tabel)
        // -----------------------------------------------------------
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
            ->distinct()
            ->orderBy('mata_pelajaran.kategori')
            ->orderBy('mata_pelajaran.urutan')
            ->get();

        // 🟢 GLOBAL AGAMA LOOKUP + ZOMBIE ID FIX
        $globalAgamaIds = DB::table('mata_pelajaran')
            ->where('nama_mapel', 'LIKE', '%Agama%')
            ->pluck('id_mapel')
            ->toArray();
        
        // ⚠️ PAKSA MASUKKAN ID 1 (Mapel Zombie/Terhapus)
        if (!in_array(1, $globalAgamaIds)) {
            $globalAgamaIds[] = 1; 
        }

        // Grouping Header Mapel
        $daftarMapel = $rawMapelData
            ->groupBy('mapel_key')
            ->map(function ($items) {
                $first = $items->first();
                if ($first->mapel_key === 'AGAMA') {
                    return (object)[
                        'id_mapel'     => 'AGAMA',
                        'nama_mapel'   => 'Pendidikan Agama',
                        'nama_singkat' => 'Agama',
                        'kategori'     => $first->kategori,
                        'urutan'       => $first->urutan
                    ];
                }
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

        // -----------------------------------------------------------
        // B. Ambil Siswa
        // -----------------------------------------------------------
        $siswaList = Siswa::whereIn('id_kelas', $kelasIds)
            ->orderBy('nama_siswa')
            ->get();

        // -----------------------------------------------------------
        // C. Ambil Nilai
        // -----------------------------------------------------------
        $nilaiList = DB::table('nilai_akhir')
            ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', trim($tahun_ajaran))
            ->get()
            ->groupBy(fn ($n) => $n->id_siswa . '-' . $n->id_mapel);

        // -----------------------------------------------------------
        // D. Ambil Absensi
        // -----------------------------------------------------------
        $absensiList = DB::table('catatan')
            ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', trim($tahun_ajaran))
            ->get()
            ->keyBy('id_siswa');

        // -----------------------------------------------------------
        // E. Build Data Ledger
        // -----------------------------------------------------------
        $dataLedger = [];

        foreach ($siswaList as $siswa) {
            $nilaiPerMapel = [];
            $totalNilai = 0;
            $jumlahMapelTerisi = 0;

            foreach ($daftarMapel as $mapel) {
                $score = 0;

                // LOGIKA KHUSUS AGAMA
                if ($mapel->id_mapel === 'AGAMA') {
                    foreach ($globalAgamaIds as $idAgamaAsli) {
                        $key = $siswa->id_siswa . '-' . $idAgamaAsli;
                        if (isset($nilaiList[$key])) {
                            $val = $nilaiList[$key][0]->nilai_akhir ?? 0;
                            $score = (int) round($val);
                            if ($score > 0) break; 
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

                $nilaiPerMapel[$mapel->id_mapel] = $score;

                if ($score > 0) {
                    $totalNilai += $score;
                    $jumlahMapelTerisi++;
                }
            }

            // Absen
            $absensi = $absensiList[$siswa->id_siswa] ?? null;

            $dataLedger[] = (object)[
                'nama_siswa' => $siswa->nama_siswa,
                'nipd'       => $siswa->nipd,
                'nisn'       => $siswa->nisn,
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
            'kelasObj'    => Kelas::find($kelasIds[0] ?? null)
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
     * Export PDF (Versi Cepat: Direct Print)
     */
    public function exportPdf(Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = strtoupper($semesterRaw) === 'GANJIL' ? 1 : 2;

        // Ambil ID Kelas
        $kelasIds = $this->getKelasIdsFromRequest($request);
        
        // Label Nama Kelas untuk PDF
        $mode = $request->mode ?? 'kelas';
        $namaKelasLabel = '-';
        $kelasObj = null;

        if (!empty($kelasIds)) {
            $kelasObj = Kelas::find($kelasIds[0]);
            if ($mode === 'kelas') {
                $namaKelasLabel = $kelasObj->nama_kelas;
            } else {
                $namaKelasLabel = 'Jurusan ' . $request->jurusan;
            }
        }

        // Panggil Core Logic
        $core = $this->buildDataCore($kelasIds, $semesterInt, $tahun_ajaran);
        
        // Sorting default ranking
        $dataLedger = $this->sortLedger($core['dataLedger']);

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

        // Buat Judul File untuk Browser
        $namaFile = $this->buildFilename($request, 'pdf'); // Hanya untuk title tab

        $dataView = [
            'namaSekolah'   => $namaSekolah,
            'alamatSekolah' => $alamatSekolah,
            'kelas'         => (object)['nama_kelas' => $namaKelasLabel],
            'daftarMapel'   => $core['daftarMapel'],
            'dataLedger'    => $dataLedger,
            'semesterRaw'   => $semesterRaw,
            'tahun_ajaran'  => $tahun_ajaran,
            'nama_wali'     => $nama_wali,
            'nip_wali'      => $nip_wali,
            'pageTitle'     => $namaFile // Kirim judul file ke view
        ];

        // PERUBAHAN DISINI:
        // Jangan pakai Pdf::loadView(), langsung return view biasa.
        return view('rapor.ledger_pdf', $dataView);
    }
}