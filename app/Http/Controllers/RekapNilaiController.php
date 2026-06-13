<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\BobotNilai;
use App\Models\NilaiAkhir;
use App\Models\Season;
use App\Models\Pembelajaran; 
use App\Models\Guru;         
use App\Helpers\NilaiCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RekapNilaiController extends Controller
{
    // === METHOD HELPER BARU: KAMUS ALIAS AGAMA ===
    private function getAgamaAliases(?string $agama_khusus): array
    {
        if (!$agama_khusus) return [];

        $agama = trim(strtolower($agama_khusus));

        $aliases = [
            'katholik' => ['katholik', 'katolik'],
            'katolik'  => ['katholik', 'katolik'],
            'kristen'  => ['kristen', 'protestan', 'kristen protestan'],
            'konghucu' => ['konghucu', 'kong hucu', 'khonghucu'],
        ];

        return $aliases[$agama] ?? [$agama];
    }

    /**
     * HALAMAN UTAMA: Tampilkan Tabel Rekap
     */
    public function index(Request $request)
    {
        // 1. SETTING DEFAULT
        $bulanSekarang = date('n'); 
        $tahunSekarang = date('Y');

        if ($bulanSekarang >= 7) {
            $semDefault = 'Ganjil';
            $taDefault  = $tahunSekarang . '/' . ($tahunSekarang + 1);
        } else {
            $semDefault = 'Genap';
            $taDefault  = ($tahunSekarang - 1) . '/' . $tahunSekarang;
        }

        $id_kelas     = $request->id_kelas;
        $id_mapel     = $request->id_mapel;
        $semesterRaw  = $request->semester ?? $semDefault;
        $tahun_ajaran = $request->tahun_ajaran ?? $taDefault;
        $semesterInt  = (strtoupper($semesterRaw) == 'GENAP' || $semesterRaw == '2') ? 2 : 1;

        // ==================================================
        // 2. IDENTIFIKASI ROLE & GURU FILTER (RBAC)
        // ==================================================
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']); 
        
        if ($isGuru) {
            $id_guru_filter = $user->id_guru;
        } else {
            $id_guru_filter = $request->id_guru; 
        }

        $guruList = Guru::orderBy('nama_guru')->get();

        // 3. CEK SEASON
        $seasonStatus = $this->checkSeason($tahun_ajaran, $semesterInt);
        $seasonOpen   = $seasonStatus['is_open'];
        $seasonMessage = $seasonStatus['message'];
        
        $seasonDetail = \App\Models\Season::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semesterInt)
            ->first();

        // ==================================================
        // 4. LOGIKA CASCADING DROPDOWN BERDASARKAN ROLE
        // ==================================================
        $kelas = collect();
        $mapelList = collect();

        if ($isGuru) {
            // ---> ALUR GURU: MAPEL DULU BARU KELAS <---
            $mapelList = Pembelajaran::where('id_guru', $id_guru_filter)
                ->whereHas('mapel', function ($q) { $q->where('is_active', 1); })
                ->with(['mapel' => function ($q) { $q->where('is_active', 1); }])
                ->get()
                ->map(fn($p) => $p->mapel)
                ->filter()
                ->unique('id_mapel')
                ->sortBy([['kategori', 'asc'], ['urutan', 'asc']])
                ->values();

            if ($id_mapel) {
                $kelas = Pembelajaran::where('id_guru', $id_guru_filter)
                    ->where('id_mapel', $id_mapel)
                    ->with('kelas')
                    ->get()
                    ->map(fn($p) => $p->kelas)
                    ->filter()
                    ->unique('id_kelas')
                    ->sortBy('nama_kelas')
                    ->values();
            }
        } else {
            // ---> ALUR ADMIN: KELAS DULU BARU MAPEL <---
            $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

            if ($id_kelas) {
                $queryMapel = Pembelajaran::where('id_kelas', $id_kelas);
                
                if ($id_guru_filter) {
                    $queryMapel->where('id_guru', $id_guru_filter);
                }

                $mapelList = $queryMapel->whereHas('mapel', function ($q) { $q->where('is_active', 1); })
                    ->with(['mapel' => function ($q) { $q->where('is_active', 1); }])
                    ->get()
                    ->map(fn($p) => $p->mapel)
                    ->filter()
                    ->unique('id_mapel')
                    ->sortBy([['kategori', 'asc'], ['urutan', 'asc']])
                    ->values();
            }
        }

        // ==================================================
        // 5. PROSES DATA SISWA & KALKULASI NILAI
        // ==================================================
        $dataSiswa = [];
        $bobotInfo = null;

        // --- VARIABEL UNTUK GATEKEEPER ---
        $isLocked = false;
        $canSave  = false;
        $totalSiswaMemenuhiTarget = 0;
        $batasMinimalSumatif = 3; // Default
        
        if ($id_kelas && $id_mapel) {
            $bobot = \App\Models\BobotNilai::where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', strtoupper($semesterRaw))
                ->first();
            
            $bobotInfo = $bobot;
            $batasMinimalSumatif = $bobotInfo ? (int) $bobotInfo->jumlah_sumatif : 3;

            $mapelActive = DB::table('mata_pelajaran')->where('id_mapel', $id_mapel)->first();
            $syaratAgama = $mapelActive->agama_khusus;

            $querySiswa = DB::table('siswa')
                ->join('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa')
                ->where('siswa.id_kelas', $id_kelas)
                ->select('siswa.id_siswa', 'siswa.nama_siswa', 'siswa.nisn', 'detail_siswa.agama')
                ->orderBy('siswa.nama_siswa', 'asc');

            if (!empty($syaratAgama)) {
                $agamaList = $this->getAgamaAliases($syaratAgama);
                $querySiswa->whereIn(DB::raw('LOWER(TRIM(detail_siswa.agama))'), $agamaList);
            }

            $siswa = $querySiswa->get();

            foreach ($siswa as $s) {
                $sumatifCollection = DB::table('sumatif')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->get();

                $projectRow = DB::table('project')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();
                $nilaiP = $projectRow ? $projectRow->nilai : 0;

                $hasil = NilaiCalculator::process($sumatifCollection, $nilaiP, $bobot);

                $saved = DB::table('nilai_akhir')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();

                $nilaiFinal = $hasil['nilai_akhir'];
                $deskripsi = $saved ? $saved->capaian_akhir : $this->generateDeskripsi($s->id_siswa, $id_mapel, $semesterInt, $tahun_ajaran);

                // --- HITUNG JUMLAH SUMATIF YANG ADA NILAINYA UNTUK SISWA INI ---
                $jumlahSumatifSiswa = $sumatifCollection->filter(function ($item) {
                    return !is_null($item->nilai); // Hanya hitung jika kolom nilai sudah diisi
                })->count();

                if ($jumlahSumatifSiswa >= $batasMinimalSumatif) {
                    $totalSiswaMemenuhiTarget++;
                }

                $dataSiswa[] = (object)[
                    'id_siswa'   => $s->id_siswa,
                    'nama_siswa' => $s->nama_siswa,
                    'nisn'       => $s->nisn,
                    'agama'      => $s->agama,
                    's1' => $hasil['s_vals']['nilai_s1'] ?? '-', 
                    's2' => $hasil['s_vals']['nilai_s2'] ?? '-', 
                    's3' => $hasil['s_vals']['nilai_s3'] ?? '-', 
                    's4' => $hasil['s_vals']['nilai_s4'] ?? '-', 
                    's5' => $hasil['s_vals']['nilai_s5'] ?? '-',
                    'rata_s'     => $hasil['rata_sumatif'],
                    'bobot_s_v'  => $hasil['bobot_sumatif'],
                    'nilai_p'    => $hasil['nilai_project'], 
                    'bobot_p_v'  => $hasil['bobot_project'],
                    'nilai_akhir'=> $nilaiFinal,
                    'deskripsi'  => $deskripsi,
                    'is_saved'   => $saved ? true : false,
                    'na_rumus'   => $hasil['nilai_akhir']
                ];
            }

            // --- CEK STATUS LOCKED (Sudah dicetak/final dari sisi Admin/Wali) ---
            $isLocked = DB::table('nilai_akhir')
                ->where('id_kelas', $id_kelas)
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->where('status_data', '!=', 'draft')
                ->exists();

            // --- TENTUKAN CAN_SAVE ---
            // Hanya bisa save jika belum dikunci DAN seluruh siswa (count($dataSiswa)) sudah memenuhi target
            if (!$isLocked && count($dataSiswa) > 0 && $totalSiswaMemenuhiTarget === count($dataSiswa)) {
                $canSave = true;
            }
        }

        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 3; $t <= $tahunSekarang + 1; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }
        $semesterList = ['Ganjil', 'Genap'];

        return view('nilai.rekap_nilai.index', compact(
            'kelas', 'mapelList', 'dataSiswa', 'bobotInfo', 
            'id_kelas', 'id_mapel', 'semesterRaw', 'tahun_ajaran', 
            'semesterList', 'tahunAjaranList', 'seasonOpen', 'seasonMessage', 'seasonDetail',
            'isLocked', 'canSave', 'totalSiswaMemenuhiTarget', 'batasMinimalSumatif',
            'isGuru', 'id_guru_filter', 'guruList'
        ));
    }

    /**
     * AKSI: SIMPAN FINALISASI (SNAPSHOT)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_kelas'     => 'required',
            'id_mapel'     => 'required',
            'semester'     => 'required',
            'tahun_ajaran' => 'required',
        ]);

        $id_kelas     = $request->id_kelas;
        $id_mapel     = $request->id_mapel;
        $semesterRaw  = $request->semester;
        $semesterInt  = (strtoupper($semesterRaw) == 'GENAP' || $semesterRaw == '2') ? 2 : 1;
        $tahun_ajaran = $request->tahun_ajaran;

        // Gatekeeper Season
        $seasonCheck = $this->checkSeason($tahun_ajaran, $semesterInt);
        if (!$seasonCheck['is_open']) {
            return redirect()->back()->with('error', 'Gagal Simpan: ' . $seasonCheck['message']);
        }

        // Gatekeeper Status
        $isLocked = DB::table('nilai_akhir')
            ->where('id_kelas', $id_kelas)
            ->where('id_mapel', $id_mapel)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('status_data', '!=', 'draft') 
            ->exists();

        if ($isLocked) {
            return redirect()->back()->with('error', 'Gagal Simpan: Data nilai sudah dikunci (Status Final/Cetak). Silakan hubungi Wali Kelas/Admin jika ingin melakukan perubahan data.');
        }

        // Data Snapshot Identitas
        $kelas = Kelas::find($id_kelas);
        $namaKelasSnapshot = $kelas->nama_kelas;
        $tingkatSnapshot   = (int) preg_replace('/[^0-9]/', '', $kelas->tingkat ?? '10');
        $faseSnapshot      = ($tingkatSnapshot >= 11) ? 'F' : 'E';

        $mapel = MataPelajaran::find($id_mapel);
        $namaMapelSnapshot = $mapel->nama_mapel;
        $kodeMapelSnapshot = $mapel->nama_singkat ?? '-';
        
        $kategoriLabel = 'Mata Pelajaran Umum';
        if (is_numeric($mapel->kategori)) {
            $mapKategori = [1 => 'Mata Pelajaran Umum', 2 => 'Mata Pelajaran Kejuruan', 3 => 'Mata Pelajaran Pilihan', 4 => 'Muatan Lokal'];
            $kategoriLabel = $mapKategori[$mapel->kategori] ?? 'Mata Pelajaran Umum';
        }

        $pembelajaran = DB::table('pembelajaran')
            ->leftJoin('guru', 'pembelajaran.id_guru', '=', 'guru.id_guru')
            ->where('id_kelas', $id_kelas)->where('id_mapel', $id_mapel)->first();
        $namaGuruSnapshot = $pembelajaran->nama_guru ?? Auth::user()->name ?? 'Guru Mapel';

        $bobot = \App\Models\BobotNilai::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', strtoupper($semesterRaw))
            ->first();
        
        if (!$bobot) return back()->with('error', "Gagal: Bobot Nilai belum disetting Admin.");

        DB::beginTransaction();
        try {
            $dataInput = $request->input('data', []);

            foreach ($dataInput as $id_siswa => $val) {
                $sumatifCollection = DB::table('sumatif')->where([
                    'id_siswa' => $id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->get();

                $projectRow = DB::table('project')->where([
                    'id_siswa' => $id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();
                $nilaiP = $projectRow ? $projectRow->nilai : 0;

                $hasil = NilaiCalculator::process($sumatifCollection, $nilaiP, $bobot);

                $nilaiFinal = (isset($val['nilai_akhir']) && is_numeric($val['nilai_akhir'])) 
                    ? (int) $val['nilai_akhir'] 
                    : $hasil['nilai_akhir'];

                $deskripsiFix = $this->generateDeskripsi($id_siswa, $id_mapel, $semesterInt, $tahun_ajaran);

                DB::table('nilai_akhir')->updateOrInsert(
                    [
                        'id_siswa' => $id_siswa, 'id_mapel' => $id_mapel,
                        'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                    ],
                    array_merge($hasil['s_vals'], [
                        'id_kelas' => $id_kelas,
                        'rata_sumatif'  => $hasil['rata_sumatif'],
                        'bobot_sumatif' => $hasil['bobot_sumatif'],
                        'nilai_project' => $hasil['nilai_project'],
                        'rata_project'  => $hasil['rata_project'],
                        'bobot_project' => $hasil['bobot_project'],
                        'nilai_akhir'   => $nilaiFinal,
                        'capaian_akhir' => $deskripsiFix,
                        'nama_mapel_snapshot'     => $namaMapelSnapshot,
                        'kode_mapel_snapshot'     => $kodeMapelSnapshot,
                        'kategori_mapel_snapshot' => $kategoriLabel,
                        'nama_guru_snapshot'      => $namaGuruSnapshot,
                        'nama_kelas_snapshot'     => $namaKelasSnapshot,
                        'tingkat'                 => $tingkatSnapshot,
                        'fase'                    => $faseSnapshot,
                        'status_data' => 'draft',
                        'updated_at'  => now(),
                        'created_at'  => DB::raw('IFNULL(created_at, NOW())') 
                    ])
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data Nilai Akhir berhasil difinalisasi dan disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function checkSeason($tahun_ajaran, $semesterInt)
    {
        $today = Carbon::today();
        $season = Season::where('tahun_ajaran', $tahun_ajaran)->where('semester', $semesterInt)->first();

        if (!$season) return ['is_open' => false, 'message' => 'Jadwal Season belum diatur.'];
        if (!$season->is_open) return ['is_open' => false, 'message' => 'Akses ditutup admin.'];
        if ($today->lt($season->start_date)) return ['is_open' => false, 'message' => 'Masa input belum mulai.'];
        if ($today->gt($season->end_date)) return ['is_open' => false, 'message' => 'Masa input berakhir.'];

        return ['is_open' => true, 'message' => 'Aman'];
    }

    private function generateDeskripsi($id_siswa, $id_mapel, $semester, $tahun_ajaran)
    {
        $sumatif = DB::table('sumatif')
            ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $semester, 'tahun_ajaran' => $tahun_ajaran])
            ->whereNotNull('nilai')
            ->get()->map(function($item) { return ['nilai' => (float) $item->nilai, 'tp' => $item->tujuan_pembelajaran]; });

        $project = DB::table('project')
            ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $semester, 'tahun_ajaran' => $tahun_ajaran])
            ->get()->map(function($item) { return ['nilai' => (float) $item->nilai, 'tp' => $item->tujuan_pembelajaran]; });

        $semuaNilai = $sumatif->merge($project)->filter(function($item) { return !empty(trim((string)$item['tp'])); });

        // 1. Jika data kosong (belum ada nilai sama sekali yang disubmit)
        if ($semuaNilai->isEmpty()) return "-";

        $terendah = $semuaNilai->sortBy('nilai')->first();
        $tertinggi = $semuaNilai->sortByDesc('nilai')->first();

        // 2. Jika nilai tertinggi adalah 0 (berarti semua ujian diisi 0 karena tidak ikut)
        if ($tertinggi['nilai'] <= 0) return "-";

        if ($semuaNilai->count() === 1 || $terendah['nilai'] === $tertinggi['nilai']) {
            $narasi = ($terendah['nilai'] > 84) ? "Menunjukkan penguasaan yang baik dalam hal" : "Perlu penguatan dalam hal";
            return $narasi . " " . $terendah['tp'] . ".";
        }

        $kunciRendah = ($terendah['nilai'] < 81) ? "Perlu peningkatan dalam hal" : "Perlu penguatan dalam hal";
        $kunciTinggi = ($tertinggi['nilai'] > 89) ? "Mahir dalam hal" : "Baik dalam hal";

        return "{$kunciRendah} " . trim($terendah['tp']) . ", namun menunjukkan capaian {$kunciTinggi} " . trim($tertinggi['tp']) . ".";
    }
}