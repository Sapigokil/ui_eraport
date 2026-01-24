<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\BobotNilai;
use App\Models\NilaiAkhir;
use App\Models\Season; // Tambahkan Model Season
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Tambahkan Carbon untuk tanggal

class RekapNilaiController extends Controller
{
    /**
     * HALAMAN UTAMA: Tampilkan Tabel Rekap
     */
    public function index(Request $request)
    {
        // 1. SETTING DEFAULT (OTOMATIS BERDASARKAN BULAN)
        $bulanSekarang = date('n'); // 1-12
        $tahunSekarang = date('Y');

        // Logika: Juli - Desember = Ganjil, Januari - Juni = Genap
        if ($bulanSekarang >= 7) {
            $semDefault = 'Ganjil';
            $taDefault  = $tahunSekarang . '/' . ($tahunSekarang + 1);
        } else {
            $semDefault = 'Genap';
            $taDefault  = ($tahunSekarang - 1) . '/' . $tahunSekarang;
        }

        // Ambil dari Request jika ada, jika tidak gunakan Default di atas
        $id_kelas     = $request->id_kelas;
        $id_mapel     = $request->id_mapel;
        $semesterRaw  = $request->semester ?? $semDefault;
        $tahun_ajaran = $request->tahun_ajaran ?? $taDefault;
        
        // Konversi Semester ke Int (1/2)
        $semesterInt  = (strtoupper($semesterRaw) == 'GENAP' || $semesterRaw == '2') ? 2 : 1;

        // 2. DATA MASTER & FILTER
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $mapelList = [];
        $dataSiswa = [];
        $bobotInfo = null;
        $seasonDetail = null;

        // 3. CEK SEASON (JADWAL INPUT)
        // Helper checkSeason tetap dipanggil untuk status
        $seasonStatus = $this->checkSeason($tahun_ajaran, $semesterInt);
        $seasonOpen   = $seasonStatus['is_open'];
        $seasonMessage = $seasonStatus['message'];
        
        // Ambil Detail Season untuk Tampilan View
        $seasonDetail = Season::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semesterInt)
            ->first();

        // 4. AMBIL LIST MAPEL (Jika Kelas Dipilih)
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

        // 5. PROSES DATA SISWA (Dilakukan meskipun Season Terkunci)
        if ($id_kelas && $id_mapel) {
            // Ambil Bobot
            $bobot = BobotNilai::where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', strtoupper($semesterRaw))
                ->first();
            
            $bobotInfo = $bobot;

            // Set Default Bobot jika kosong (agar tidak error pembagian)
            $pSumatif  = $bobot->bobot_sumatif ?? 50;
            $pProject  = $bobot->bobot_project ?? 50;
            $targetMin = $bobot->jumlah_sumatif ?? 0;

            $siswa = Siswa::where('id_kelas', $id_kelas)->orderBy('nama_siswa')->get();

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
                $bobotSNominal = round($rataS * ($pSumatif / 100), 2);

                // B. PROJECT
                $projectRow = DB::table('project')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();
                
                $nilaiP = $projectRow ? $projectRow->nilai : 0;
                $bobotPNominal = round($nilaiP * ($pProject / 100), 2);

                // C. HITUNG NILAI AKHIR (Rumus)
                $naRumus = 0;
                if ($rataS > 0 || $nilaiP > 0) {
                    $naRumus = (int) round($bobotSNominal + $bobotPNominal);
                }

                // D. CEK SNAPSHOT (Data yang sudah disimpan)
                $saved = DB::table('nilai_akhir')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();

                $nilaiFinal = $saved ? $saved->nilai_akhir : $naRumus;
                $deskripsi  = $saved ? $saved->capaian_akhir : $this->generateDeskripsi($s->id_siswa, $id_mapel, $semesterInt, $tahun_ajaran);

                $dataSiswa[] = (object)[
                    'id_siswa'   => $s->id_siswa,
                    'nama_siswa' => $s->nama_siswa,
                    'nisn'       => $s->nisn,
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

        // Generator Dropdown Tahun Ajaran
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
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required',
            'id_mapel' => 'required',
        ]);

        $id_kelas = $request->id_kelas;
        $id_mapel = $request->id_mapel;
        $semesterRaw = $request->semester;
        $semesterInt = (strtoupper($semesterRaw) == 'GENAP' || $semesterRaw == '2') ? 2 : 1;
        $tahun_ajaran = $request->tahun_ajaran;

        // --- GATEKEEPER: CEK SEASON SEBELUM SIMPAN ---
        $seasonCheck = $this->checkSeason($tahun_ajaran, $semesterInt);
        if (!$seasonCheck['is_open']) {
            return redirect()->back()->with('error', 'Gagal Simpan: ' . $seasonCheck['message']);
        }

        // SIAPKAN DATA SNAPSHOT
        $kelas = Kelas::find($id_kelas);
        $namaKelasSnapshot = $kelas->nama_kelas;
        $tingkatSnapshot = (int) preg_replace('/[^0-9]/', '', $kelas->tingkat ?? '10');
        $faseSnapshot = ($tingkatSnapshot >= 11) ? 'F' : 'E';

        $mapel = MataPelajaran::find($id_mapel);
        $namaMapelSnapshot = $mapel->nama_mapel;
        $kodeMapelSnapshot = $mapel->kode_mapel ?? null;

        $pembelajaran = DB::table('pembelajaran')
            ->leftJoin('guru', 'pembelajaran.id_guru', '=', 'guru.id_guru')
            ->where('id_kelas', $id_kelas)
            ->where('id_mapel', $id_mapel)
            ->first();
        $namaGuruSnapshot = $pembelajaran->nama_guru ?? Auth::user()->name ?? 'Guru Mapel';

        DB::beginTransaction();
        try {
            $dataInput = $request->input('data', []);

            foreach ($dataInput as $id_siswa => $val) {
                $nilaiFix = (int) ($val['nilai_akhir'] ?? 0);
                $deskripsiFix = $val['deskripsi'] ?? '-';

                DB::table('nilai_akhir')->updateOrInsert(
                    [
                        'id_siswa' => $id_siswa,
                        'id_mapel' => $id_mapel,
                        'semester' => $semesterInt,
                        'tahun_ajaran' => $tahun_ajaran
                    ],
                    [
                        'id_kelas' => $id_kelas,
                        'nilai_akhir' => $nilaiFix,
                        'capaian_akhir' => $deskripsiFix,
                        'tingkat' => $tingkatSnapshot,
                        'fase' => $faseSnapshot,
                        'nama_kelas_snapshot' => $namaKelasSnapshot,
                        'nama_mapel_snapshot' => $namaMapelSnapshot,
                        'kode_mapel_snapshot' => $kodeMapelSnapshot,
                        'nama_guru_snapshot' => $namaGuruSnapshot,
                        'status_data' => 'aktif', // Default status
                        'updated_at' => now()
                    ]
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data Nilai Akhir berhasil dikunci dan disimpan.');

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
        
        // Cari Season berdasarkan Tahun & Semester yang dipilih
        $season = Season::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semesterInt)
            ->first();

        if (!$season) {
            return [
                'is_open' => false,
                'message' => 'Jadwal Season belum diatur untuk Tahun Ajaran/Semester ini.'
            ];
        }

        // Cek Flag is_open (Manual Override)
        if (!$season->is_open) {
            return [
                'is_open' => false,
                'message' => 'Akses input nilai untuk season ini ditutup oleh admin.'
            ];
        }

        // Cek Rentang Tanggal
        // Asumsi: start_date dan end_date di database adalah tipe DATE
        if ($today->lt($season->start_date)) {
            return [
                'is_open' => false,
                'message' => 'Masa input nilai belum dimulai. (Mulai: ' . Carbon::parse($season->start_date)->format('d-m-Y') . ')'
            ];
        }

        if ($today->gt($season->end_date)) {
            return [
                'is_open' => false,
                'message' => 'Masa input nilai telah berakhir pada ' . Carbon::parse($season->end_date)->format('d-m-Y')
            ];
        }

        return ['is_open' => true, 'message' => 'Aman'];
    }

    /**
     * Helper: Generator Deskripsi
     */
    private function generateDeskripsi($id_siswa, $id_mapel, $smt, $ta)
    {
        $nilaiTp = DB::table('sumatif')
            ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $smt, 'tahun_ajaran' => $ta])
            ->whereNotNull('nilai')
            ->orderBy('nilai', 'asc')
            ->get();

        if ($nilaiTp->isEmpty()) return 'Capaian kompetensi belum tersedia.';

        $tpRendah = $nilaiTp->first();
        $tpTinggi = $nilaiTp->last();

        $narasiRendah = ($tpRendah->nilai < 78) ? 'Perlu peningkatan dalam' : 'Perlu penguatan dalam';
        $narasiTinggi = ($tpTinggi->nilai >= 78) ? 'Baik dalam' : 'Cukup dalam';

        if ($tpRendah->tujuan_pembelajaran === $tpTinggi->tujuan_pembelajaran) {
            return "{$narasiRendah} {$tpRendah->tujuan_pembelajaran}.";
        }
        return "{$narasiRendah} {$tpRendah->tujuan_pembelajaran}, namun menunjukkan capaian {$narasiTinggi} {$tpTinggi->tujuan_pembelajaran}.";
    }
}