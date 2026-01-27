<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\BobotNilai;
use App\Models\NilaiAkhir;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RekapNilaiController extends Controller
{
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

        // 2. DATA MASTER
        $kelas = \App\Models\Kelas::orderBy('nama_kelas', 'asc')->get();
        $mapelList = [];
        $dataSiswa = [];
        $bobotInfo = null;
        $seasonDetail = null;

        // 3. CEK SEASON
        if (method_exists($this, 'checkSeason')) {
            $seasonStatus = $this->checkSeason($tahun_ajaran, $semesterInt);
            $seasonOpen   = $seasonStatus['is_open'];
            $seasonMessage = $seasonStatus['message'];
        } else {
            $seasonOpen = true; 
            $seasonMessage = '';
        }
        
        $seasonDetail = \App\Models\Season::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semesterInt)
            ->first();

        // 4. AMBIL MAPEL
        if ($id_kelas) {
            $mapelList = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $id_kelas)
                ->where('mata_pelajaran.is_active', 1) 
                ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel', 'mata_pelajaran.kategori', 'mata_pelajaran.urutan')
                ->orderBy('mata_pelajaran.kategori', 'asc')
                ->orderBy('mata_pelajaran.urutan', 'asc')
                ->orderBy('mata_pelajaran.nama_mapel', 'asc')
                ->get();
        }

        // 5. PROSES DATA (LOGIC SAMA DENGAN STORE)
        if ($id_kelas && $id_mapel) {
            $bobot = \App\Models\BobotNilai::where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', strtoupper($semesterRaw))
                ->first();
            
            $bobotInfo = $bobot;

            // Parameter Bobot
            $pSumatif  = $bobot->bobot_sumatif ?? 50;
            $pProject  = $bobot->bobot_project ?? 50;
            $targetMin = $bobot->jumlah_sumatif ?? 0;

            // Filter Agama
            $mapelActive = DB::table('mata_pelajaran')->where('id_mapel', $id_mapel)->first();
            $namaMapel   = $mapelActive->nama_mapel ?? '';
            $filterAgama = null; 

            if (stripos($namaMapel, 'Islam') !== false) $filterAgama = ['islam'];
            elseif (stripos($namaMapel, 'Kristen') !== false || stripos($namaMapel, 'Protestan') !== false) $filterAgama = ['kristen', 'protestan'];
            elseif (stripos($namaMapel, 'Katholik') !== false || stripos($namaMapel, 'Katolik') !== false) $filterAgama = ['katholik', 'katolik'];
            elseif (stripos($namaMapel, 'Hindu') !== false) $filterAgama = ['hindu'];
            elseif (stripos($namaMapel, 'Buddha') !== false || stripos($namaMapel, 'Budha') !== false) $filterAgama = ['buddha', 'budha'];
            elseif (stripos($namaMapel, 'Konghucu') !== false || stripos($namaMapel, 'Khonghucu') !== false) $filterAgama = ['konghucu', 'khonghucu', 'khong hu cu'];

            // Query Siswa
            $querySiswa = DB::table('siswa')
                ->join('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa')
                ->where('siswa.id_kelas', $id_kelas)
                ->select('siswa.id_siswa', 'siswa.nama_siswa', 'siswa.nisn', 'detail_siswa.agama')
                ->orderBy('siswa.nama_siswa', 'asc');

            if ($filterAgama !== null) {
                $querySiswa->whereIn(DB::raw('LOWER(detail_siswa.agama)'), $filterAgama);
            }

            $siswa = $querySiswa->get();

            foreach ($siswa as $s) {
                // A. SUMATIF
                $sumatifCollection = DB::table('sumatif')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->get();

                $s1 = $sumatifCollection->firstWhere('sumatif', 1)->nilai ?? null;
                $s2 = $sumatifCollection->firstWhere('sumatif', 2)->nilai ?? null;
                $s3 = $sumatifCollection->firstWhere('sumatif', 3)->nilai ?? null;
                $s4 = $sumatifCollection->firstWhere('sumatif', 4)->nilai ?? null;
                $s5 = $sumatifCollection->firstWhere('sumatif', 5)->nilai ?? null;

                $nilaiSumatifValid = collect([$s1, $s2, $s3, $s4, $s5])->filter(fn($val) => $val !== null);
                $jmlTerisi = $nilaiSumatifValid->count();
                $pembagi = max($jmlTerisi, $targetMin);
                $rataS = ($pembagi > 0) ? round($nilaiSumatifValid->sum() / $pembagi, 2) : 0;
                
                $bobotSNominal = (int) round($rataS * ($pSumatif / 100));

                // B. PROJECT
                $projectRow = DB::table('project')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();
                
                $nilaiP = $projectRow ? $projectRow->nilai : 0;
                $bobotPNominal = (int) round($nilaiP * ($pProject / 100));

                // C. FINAL
                $naRumus = 0;
                if ($rataS > 0 || $nilaiP > 0) {
                    $naRumus = $bobotSNominal + $bobotPNominal;
                }

                // D. SNAPSHOT
                $saved = DB::table('nilai_akhir')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();

                $nilaiFinal = $saved ? (int) $saved->nilai_akhir : $naRumus;
                $deskripsi = $saved ? $saved->capaian_akhir : $this->generateDeskripsi($s->id_siswa, $id_mapel, $semesterInt, $tahun_ajaran);

                $dataSiswa[] = (object)[
                    'id_siswa'   => $s->id_siswa,
                    'nama_siswa' => $s->nama_siswa,
                    'nisn'       => $s->nisn,
                    'agama'      => $s->agama,
                    's1' => $s1 ?? '-', 's2' => $s2 ?? '-', 's3' => $s3 ?? '-', 's4' => $s4 ?? '-', 's5' => $s5 ?? '-',
                    'rata_s'     => $rataS,
                    'bobot_s_v'  => $bobotSNominal,
                    'nilai_p'    => $nilaiP, 
                    'bobot_p_v'  => $bobotPNominal,
                    'nilai_akhir'=> $nilaiFinal,
                    'deskripsi'  => $deskripsi,
                    'is_saved'   => $saved ? true : false,
                    'na_rumus'   => $naRumus
                ];
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
            'semesterList', 'tahunAjaranList', 'seasonOpen', 'seasonMessage', 'seasonDetail'
        ));
    }

    /**
     * AKSI: SIMPAN FINALISASI (SNAPSHOT)
     * [PERBAIKAN] Sekarang menyimpan semua komponen nilai (S1-S5, Rata-rata, Bobot, dll)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required',
            'id_mapel' => 'required',
            'semester'     => 'required',
            'tahun_ajaran' => 'required',
        ]);

        $id_kelas = $request->id_kelas;
        $id_mapel = $request->id_mapel;
        $semesterRaw = $request->semester;
        $semesterInt = (strtoupper($semesterRaw) == 'GENAP' || $semesterRaw == '2') ? 2 : 1;
        $tahun_ajaran = $request->tahun_ajaran;

        // --- GATEKEEPER: CEK SEASON ---
        $seasonCheck = $this->checkSeason($tahun_ajaran, $semesterInt);
        if (!$seasonCheck['is_open']) {
            return redirect()->back()->with('error', 'Gagal Simpan: ' . $seasonCheck['message']);
        }

        // 1. SIAPKAN DATA SNAPSHOT KELAS & MAPEL
        $kelas = Kelas::find($id_kelas);
        $namaKelasSnapshot = $kelas->nama_kelas;
        $tingkatSnapshot = (int) preg_replace('/[^0-9]/', '', $kelas->tingkat ?? '10');
        $faseSnapshot = ($tingkatSnapshot >= 11) ? 'F' : 'E';

        $mapel = MataPelajaran::find($id_mapel);
        $namaMapelSnapshot = $mapel->nama_mapel;
        $kodeMapelSnapshot = $mapel->nama_singkat ?? null;
        
        $kategoriLabel = $mapel->kategori; 
        if (is_numeric($mapel->kategori)) {
            $mapKategori = [
                1 => 'Mata Pelajaran Umum', 2 => 'Mata Pelajaran Kejuruan',
                3 => 'Mata Pelajaran Pilihan', 4 => 'Muatan Lokal',
            ];
            $kategoriLabel = $mapKategori[$mapel->kategori] ?? 'Mata Pelajaran Umum';
        }

        $pembelajaran = DB::table('pembelajaran')
            ->leftJoin('guru', 'pembelajaran.id_guru', '=', 'guru.id_guru')
            ->where('id_kelas', $id_kelas)
            ->where('id_mapel', $id_mapel)
            ->first();
        $namaGuruSnapshot = $pembelajaran->nama_guru ?? Auth::user()->name ?? 'Guru Mapel';

        // 2. AMBIL CONFIG BOBOT
        $bobot = \App\Models\BobotNilai::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', strtoupper($semesterRaw))
            ->first();
        
        $pSumatif  = $bobot->bobot_sumatif ?? 50;
        $pProject  = $bobot->bobot_project ?? 50;
        $targetMin = $bobot->jumlah_sumatif ?? 0;

        DB::beginTransaction();
        try {
            // Ambil input dari form (terutama ID siswa dan Deskripsi manual jika ada)
            $dataInput = $request->input('data', []);

            foreach ($dataInput as $id_siswa => $val) {
                // ==========================================================
                // [BARU] HITUNG ULANG KOMPONEN NILAI UNTUK DISIMPAN
                // ==========================================================
                
                // A. Query Sumatif
                $sumatifCollection = DB::table('sumatif')->where([
                    'id_siswa' => $id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->get();

                $s1 = $sumatifCollection->firstWhere('sumatif', 1)->nilai ?? null;
                $s2 = $sumatifCollection->firstWhere('sumatif', 2)->nilai ?? null;
                $s3 = $sumatifCollection->firstWhere('sumatif', 3)->nilai ?? null;
                $s4 = $sumatifCollection->firstWhere('sumatif', 4)->nilai ?? null;
                $s5 = $sumatifCollection->firstWhere('sumatif', 5)->nilai ?? null;

                $nilaiSumatifValid = collect([$s1, $s2, $s3, $s4, $s5])->filter(fn($val) => $val !== null);
                $pembagi = max($nilaiSumatifValid->count(), $targetMin);
                $rataSumatif = ($pembagi > 0) ? round($nilaiSumatifValid->sum() / $pembagi, 2) : 0;
                $bobotSumatifVal = (int) round($rataSumatif * ($pSumatif / 100));

                // B. Query Project
                $projectRow = DB::table('project')->where([
                    'id_siswa' => $id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();
                $nilaiProject = $projectRow ? $projectRow->nilai : 0;
                // Asumsi: Rata Project = Nilai Project (karena biasanya 1 nilai/semester), jika ada banyak bisa disesuaikan
                $rataProject = $nilaiProject; 
                $bobotProjectVal = (int) round($nilaiProject * ($pProject / 100));

                // C. Nilai Akhir & Deskripsi (Dari Input Form atau Hitung Ulang)
                // Kita prioritaskan nilai_akhir dari form input karena mungkin user melakukan override/pembulatan manual di view
                $nilaiFix = (int) ($val['nilai_akhir'] ?? 0);
                $deskripsiFix = $val['deskripsi'] ?? '-';

                // ==========================================================
                // SIMPAN KE DATABASE (LENGKAP)
                // ==========================================================
                DB::table('nilai_akhir')->updateOrInsert(
                    [
                        'id_siswa' => $id_siswa,
                        'id_mapel' => $id_mapel,
                        'semester' => $semesterInt,
                        'tahun_ajaran' => $tahun_ajaran
                    ],
                    [
                        'id_kelas' => $id_kelas,
                        
                        // Detail Nilai (Yang sebelumnya hilang)
                        'nilai_s1' => $s1,
                        'nilai_s2' => $s2,
                        'nilai_s3' => $s3,
                        'nilai_s4' => $s4,
                        'nilai_s5' => $s5,
                        'rata_sumatif'  => $rataSumatif,
                        'bobot_sumatif' => $bobotSumatifVal,
                        
                        'nilai_project' => $nilaiProject,
                        'rata_project'  => $rataProject, // Kolom rata_project
                        'bobot_project' => $bobotProjectVal,

                        // Nilai Final
                        'nilai_akhir'   => $nilaiFix,
                        'capaian_akhir' => $deskripsiFix,

                        // Snapshot Data
                        'tingkat' => $tingkatSnapshot,
                        'fase' => $faseSnapshot,
                        'nama_kelas_snapshot' => $namaKelasSnapshot,
                        'nama_mapel_snapshot' => $namaMapelSnapshot,
                        'kode_mapel_snapshot' => $kodeMapelSnapshot,
                        'kategori_mapel_snapshot' => $kategoriLabel,
                        'nama_guru_snapshot' => $namaGuruSnapshot,
                        
                        'status_data' => 'final', // [FIXED] Ubah jadi Final
                        'updated_at' => now(),
                        
                        // [FIXED] Created At hanya diisi jika data baru insert
                        'created_at' => DB::raw('IFNULL(created_at, NOW())') 
                    ]
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data Nilai Akhir berhasil difinalisasi dan disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Cek Status Season
     */
    private function checkSeason($tahun_ajaran, $semesterInt)
    {
        $today = Carbon::today();
        
        $season = Season::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semesterInt)
            ->first();

        if (!$season) {
            return ['is_open' => false, 'message' => 'Jadwal Season belum diatur.'];
        }
        if (!$season->is_open) {
            return ['is_open' => false, 'message' => 'Akses ditutup admin.'];
        }
        if ($today->lt($season->start_date)) {
            return ['is_open' => false, 'message' => 'Masa input belum mulai.'];
        }
        if ($today->gt($season->end_date)) {
            return ['is_open' => false, 'message' => 'Masa input berakhir.'];
        }

        return ['is_open' => true, 'message' => 'Aman'];
    }

    /**
     * Helper Generate Deskripsi (Sama seperti sebelumnya)
     */
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

        if ($semuaNilai->isEmpty()) return "Capaian kompetensi belum tersedia.";

        if ($semuaNilai->count() > 2) {
            $terendahTmp = $semuaNilai->sortBy('nilai')->first();
            $tertinggiTmp = $semuaNilai->sortByDesc('nilai')->first();
            $semuaNilai = ($terendahTmp['tp'] === $tertinggiTmp['tp']) ? collect([$terendahTmp]) : collect([$terendahTmp, $tertinggiTmp]);
        }

        $terendah = $semuaNilai->sortBy('nilai')->first();
        $tertinggi = $semuaNilai->sortByDesc('nilai')->first();

        if ($semuaNilai->count() === 1 || $terendah['nilai'] === $tertinggi['nilai']) {
            $narasi = ($terendah['nilai'] > 84) ? "Menunjukkan penguasaan yang baik dalam hal" : "Perlu penguatan dalam hal";
            return $narasi . " " . $semuaNilai->pluck('tp')->unique()->implode(', ') . ".";
        }

        $kunciRendah = ($terendah['nilai'] < 81) ? "Perlu peningkatan dalam hal" : "Perlu penguatan dalam hal";
        $kunciTinggi = ($tertinggi['nilai'] > 89) ? "Mahir dalam hal" : "Baik dalam hal";

        return "{$kunciRendah} " . trim($terendah['tp']) . ", namun menunjukkan capaian {$kunciTinggi} " . trim($tertinggi['tp']) . ".";
    }
}