<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\BobotNilai;
use App\Models\NilaiAkhir;
use App\Models\Season;
use App\Helpers\NilaiCalculator; // Helper untuk konsistensi
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

        // 3. CEK SEASON
        $seasonStatus = $this->checkSeason($tahun_ajaran, $semesterInt);
        $seasonOpen   = $seasonStatus['is_open'];
        $seasonMessage = $seasonStatus['message'];
        
        $seasonDetail = \App\Models\Season::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semesterInt)
            ->first();

        // 4. AMBIL MAPEL
        if ($id_kelas) {
            $mapelList = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $id_kelas)
                ->where('mata_pelajaran.is_active', 1) 
                ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel', 'mata_pelajaran.kategori', 'mata_pelajaran.urutan', 'mata_pelajaran.agama_khusus')
                ->orderBy('mata_pelajaran.kategori', 'asc')
                ->orderBy('mata_pelajaran.urutan', 'asc')
                ->get();
        }

        // 5. PROSES DATA
        if ($id_kelas && $id_mapel) {
            $bobot = \App\Models\BobotNilai::where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', strtoupper($semesterRaw))
                ->first();
            
            $bobotInfo = $bobot;

            // Ambil Detail Mapel terpilih untuk filter agama
            $mapelActive = DB::table('mata_pelajaran')->where('id_mapel', $id_mapel)->first();
            $syaratAgama = $mapelActive->agama_khusus;

            // Query Siswa
            $querySiswa = DB::table('siswa')
                ->join('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa')
                ->where('siswa.id_kelas', $id_kelas)
                ->select('siswa.id_siswa', 'siswa.nama_siswa', 'siswa.nisn', 'detail_siswa.agama')
                ->orderBy('siswa.nama_siswa', 'asc');

            // Filter Siswa berdasarkan agama jika mapel tersebut spesifik agama
            if (!empty($syaratAgama)) {
                $querySiswa->where(DB::raw('LOWER(detail_siswa.agama)'), strtolower(trim($syaratAgama)));
            }

            $siswa = $querySiswa->get();

            foreach ($siswa as $s) {
                // Ambil Data Mentah Sumatif
                $sumatifCollection = DB::table('sumatif')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->get();

                // Ambil Data Mentah Project
                $projectRow = DB::table('project')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();
                $nilaiP = $projectRow ? $projectRow->nilai : 0;

                // --- HITUNG MENGGUNAKAN HELPER (KONSISTENSI) ---
                $hasil = NilaiCalculator::process($sumatifCollection, $nilaiP, $bobot);

                // Cek data yang sudah tersimpan di database
                $saved = DB::table('nilai_akhir')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();

                // Gunakan nilai dari DB jika sudah ada, jika belum gunakan hitungan rumus
                $nilaiFinal = $hasil['nilai_akhir'];
                $deskripsi = $saved ? $saved->capaian_akhir : $this->generateDeskripsi($s->id_siswa, $id_mapel, $semesterInt, $tahun_ajaran);

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
        }

        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 3; $t <= $tahunSekarang + 1; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }
        $semesterList = ['Ganjil', 'Genap'];

        $isLocked = false;
        if ($id_kelas && $id_mapel) {
            $isLocked = DB::table('nilai_akhir')
                ->where('id_kelas', $id_kelas)
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->where('status_data', '!=', 'draft')
                ->exists();
        }

        return view('nilai.rekap_nilai.index', compact(
            'kelas', 'mapelList', 'dataSiswa', 'bobotInfo', 
            'id_kelas', 'id_mapel', 'semesterRaw', 'tahun_ajaran', 
            'semesterList', 'tahunAjaranList', 'seasonOpen', 'seasonMessage', 'seasonDetail',
            'isLocked'
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

        // =========================================================================
        // 2. NEW: Gatekeeper Status (Mencegah update jika data sudah Final/Cetak)
        // =========================================================================
        $isLocked = DB::table('nilai_akhir')
            ->where('id_kelas', $id_kelas)
            ->where('id_mapel', $id_mapel)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('status_data', '!=', 'draft') // Cek apakah ada yang selain draft
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
                // Ambil Data Mentah
                $sumatifCollection = DB::table('sumatif')->where([
                    'id_siswa' => $id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->get();

                $projectRow = DB::table('project')->where([
                    'id_siswa' => $id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();
                $nilaiP = $projectRow ? $projectRow->nilai : 0;

                // --- HITUNG MENGGUNAKAN HELPER ---
                $hasil = NilaiCalculator::process($sumatifCollection, $nilaiP, $bobot);

                // Override nilai jika ada input manual dari form
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

        if ($semuaNilai->isEmpty()) return "Capaian kompetensi belum tersedia.";

        $terendah = $semuaNilai->sortBy('nilai')->first();
        $tertinggi = $semuaNilai->sortByDesc('nilai')->first();

        if ($semuaNilai->count() === 1 || $terendah['nilai'] === $tertinggi['nilai']) {
            $narasi = ($terendah['nilai'] > 84) ? "Menunjukkan penguasaan yang baik dalam hal" : "Perlu penguatan dalam hal";
            return $narasi . " " . $terendah['tp'] . ".";
        }

        $kunciRendah = ($terendah['nilai'] < 81) ? "Perlu peningkatan dalam hal" : "Perlu penguatan dalam hal";
        $kunciTinggi = ($tertinggi['nilai'] > 89) ? "Mahir dalam hal" : "Baik dalam hal";

        return "{$kunciRendah} " . trim($terendah['tp']) . ", namun menunjukkan capaian {$kunciTinggi} " . trim($tertinggi['tp']) . ".";
    }
}