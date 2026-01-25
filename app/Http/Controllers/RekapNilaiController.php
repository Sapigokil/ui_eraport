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
        // Pastikan method checkSeason ada di Controller ini atau Traits
        if (method_exists($this, 'checkSeason')) {
            $seasonStatus = $this->checkSeason($tahun_ajaran, $semesterInt);
            $seasonOpen   = $seasonStatus['is_open'];
            $seasonMessage = $seasonStatus['message'];
        } else {
            // Fallback jika method tidak ada
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

        // 5. PROSES DATA
        if ($id_kelas && $id_mapel) {
            $bobot = \App\Models\BobotNilai::where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', strtoupper($semesterRaw))
                ->first();
            
            $bobotInfo = $bobot;

            $pSumatif  = $bobot->bobot_sumatif ?? 50;
            $pProject  = $bobot->bobot_project ?? 50;
            $targetMin = $bobot->jumlah_sumatif ?? 0;

            // --- MODIFIKASI: FILTER AGAMA ---
            
            // A. Cek Nama Mapel
            $mapelActive = DB::table('mata_pelajaran')->where('id_mapel', $id_mapel)->first();
            $namaMapel   = $mapelActive->nama_mapel ?? '';

            // B. Tentukan Filter Berdasarkan Nama Mapel
            $filterAgama = null; 

            if (stripos($namaMapel, 'Islam') !== false) {
                $filterAgama = ['islam'];
            } elseif (stripos($namaMapel, 'Kristen') !== false || stripos($namaMapel, 'Protestan') !== false) {
                $filterAgama = ['kristen', 'protestan'];
            } elseif (stripos($namaMapel, 'Katholik') !== false || stripos($namaMapel, 'Katolik') !== false) {
                $filterAgama = ['katholik', 'katolik'];
            } elseif (stripos($namaMapel, 'Hindu') !== false) {
                $filterAgama = ['hindu'];
            } elseif (stripos($namaMapel, 'Buddha') !== false || stripos($namaMapel, 'Budha') !== false) {
                $filterAgama = ['buddha', 'budha'];
            } elseif (stripos($namaMapel, 'Konghucu') !== false || stripos($namaMapel, 'Khonghucu') !== false) {
                $filterAgama = ['konghucu', 'khonghucu', 'khong hu cu'];
            }

            // C. Query Siswa dengan Join ke Detail Siswa
            $querySiswa = DB::table('siswa')
                ->join('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa')
                ->where('siswa.id_kelas', $id_kelas)
                ->select(
                    'siswa.id_siswa', 
                    'siswa.nama_siswa', 
                    'siswa.nisn',
                    'detail_siswa.agama'
                )
                ->orderBy('siswa.nama_siswa', 'asc');

            // D. Terapkan Filter Jika Ada
            if ($filterAgama !== null) {
                $querySiswa->whereIn(DB::raw('LOWER(detail_siswa.agama)'), $filterAgama);
            }

            $siswa = $querySiswa->get();
            // ---------------------------------

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
                
                // Pembulatan Bobot Sumatif
                $bobotSNominal = (int) round($rataS * ($pSumatif / 100));

                // B. PROJECT
                $projectRow = DB::table('project')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();
                
                $nilaiP = $projectRow ? $projectRow->nilai : 0;
                
                // Pembulatan Bobot Project
                $bobotPNominal = (int) round($nilaiP * ($pProject / 100));

                // C. FINAL (Penjumlahan Integer)
                $naRumus = 0;
                if ($rataS > 0 || $nilaiP > 0) {
                    $naRumus = $bobotSNominal + $bobotPNominal;
                }

                // D. SNAPSHOT
                $saved = DB::table('nilai_akhir')->where([
                    'id_siswa' => $s->id_siswa, 'id_mapel' => $id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ])->first();

                // FIX: Pastikan Nilai Akhir selalu Integer (Bulat)
                $nilaiFinal = $saved ? (int) $saved->nilai_akhir : $naRumus;
                
                // Pastikan method generateDeskripsi ada
                $deskripsi = $saved ? $saved->capaian_akhir : $this->generateDeskripsi($s->id_siswa, $id_mapel, $semesterInt, $tahun_ajaran);

                $dataSiswa[] = (object)[
                    'id_siswa'   => $s->id_siswa,
                    'nama_siswa' => $s->nama_siswa,
                    'nisn'       => $s->nisn,
                    'agama'      => $s->agama, // Opsional: untuk debug
                    's1' => $s1 ?? '-', 's2' => $s2 ?? '-', 's3' => $s3 ?? '-', 's4' => $s4 ?? '-', 's5' => $s5 ?? '-',
                    'rata_s'     => $rataS,
                    'bobot_s_v'  => $bobotSNominal, // Int
                    'nilai_p'    => $nilaiP, 
                    'bobot_p_v'  => $bobotPNominal, // Int
                    'nilai_akhir'=> $nilaiFinal,    // Int
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
        $kodeMapelSnapshot = $mapel->nama_singkat ?? null;
        // --- LOGIKA MAPPING KATEGORI (Sesuai MapelController) ---
        $kategoriLabel = $mapel->kategori; // Default (jaga-jaga jika di DB sudah string)

        if (is_numeric($mapel->kategori)) {
            $mapKategori = [
                1 => 'Mata Pelajaran Umum',
                2 => 'Mata Pelajaran Kejuruan',
                3 => 'Mata Pelajaran Pilihan',
                4 => 'Muatan Lokal',
            ];
            // Ambil dari array, jika tidak ada (null), default ke 'Mata Pelajaran Umum'
            $kategoriLabel = $mapKategori[$mapel->kategori] ?? 'Mata Pelajaran Umum';
        }
        // ---------------------------------------------------------

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
                        'kategori_mapel_snapshot' => $kategoriLabel,
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
     * Fungsi Helper untuk Generate Deskripsi Capaian Otomatis
     * Menggabungkan Sumatif & Project, serta menggunakan logika Range Nilai.
     */
    private function generateDeskripsi($id_siswa, $id_mapel, $semester, $tahun_ajaran)
    {
        // 1. Ambil Nilai & TP Sumatif
        $sumatif = DB::table('sumatif')
            ->where([
                'id_siswa' => $id_siswa,
                'id_mapel' => $id_mapel,
                'semester' => $semester,
                'tahun_ajaran' => $tahun_ajaran
            ])
            ->whereNotNull('nilai')
            ->get()
            ->map(function($item) {
                return [
                    'nilai' => (float) $item->nilai,
                    'tp'    => $item->tujuan_pembelajaran,
                    'sumber'=> 'Sumatif'
                ];
            });

        // 2. Ambil Nilai & TP Project
        $project = DB::table('project')
            ->where([
                'id_siswa' => $id_siswa,
                'id_mapel' => $id_mapel,
                'semester' => $semester,
                'tahun_ajaran' => $tahun_ajaran
            ])
            ->get()
            ->map(function($item) {
                return [
                    'nilai' => (float) $item->nilai,
                    'tp'    => $item->tujuan_pembelajaran,
                    'sumber'=> 'Project'
                ];
            });

        // 3. Gabungkan Data & Filter TP Kosong
        $semuaNilai = $sumatif->merge($project)->filter(function($item) {
            return !empty(trim((string)$item['tp'])); 
        });

        if ($semuaNilai->isEmpty()) {
            return "Capaian kompetensi belum tersedia.";
        }

        // --- LOGIKA PEMBENTUKAN NARASI ---
        
        // Batasi Maksimal 2 TP (Terendah & Tertinggi) untuk narasi agar tidak kepanjangan
        if ($semuaNilai->count() > 2) {
            $terendahTmp  = $semuaNilai->sortBy('nilai')->first();
            $tertinggiTmp = $semuaNilai->sortByDesc('nilai')->first();

            // Jika TP terendah & tertinggi kebetulan sama, ambil satu saja
            if ($terendahTmp['tp'] === $tertinggiTmp['tp']) {
                $semuaNilai = collect([$terendahTmp]);
            } else {
                $semuaNilai = collect([$terendahTmp, $tertinggiTmp]);
            }
        }

        $terendah  = $semuaNilai->sortBy('nilai')->first();
        $tertinggi = $semuaNilai->sortByDesc('nilai')->first();

        // KASUS 1: Nilai Tunggal atau Nilai Terendah = Tertinggi (Kemampuan Merata)
        if ($semuaNilai->count() === 1 || $terendah['nilai'] === $tertinggi['nilai']) {
            $nilaiKomparasi = $terendah['nilai'];
            
            // Logic threshold (Bisa disesuaikan dengan KKM sekolah)
            if ($nilaiKomparasi > 84) {
                $narasi = "Menunjukkan penguasaan yang baik dalam hal";
            } else {
                $narasi = "Perlu penguatan dalam hal";
            }
            
            $allTujuan = $semuaNilai->pluck('tp')->unique()->implode(', ');
            return $narasi . " " . $allTujuan . ".";
        }

        // KASUS 2: Nilai Bervariasi (Ada yang kurang, ada yang bagus)
        // A. Bagian Terendah
        $nilaiRendah = $terendah['nilai'];
        $tpRendah    = trim($terendah['tp']);
        
        if ($nilaiRendah < 81) {
            $kunciRendah = "Perlu peningkatan dalam hal";
        } else {
            $kunciRendah = "Perlu penguatan dalam hal";
        }

        // B. Bagian Tertinggi
        $nilaiTinggi = $tertinggi['nilai'];
        $tpTinggi    = trim($tertinggi['tp']);
        
        if ($nilaiTinggi > 89) {
            $kunciTinggi = "Mahir dalam hal";
        } else {
            $kunciTinggi = "Baik dalam hal";
        }

        // Gabungkan kalimat
        return "{$kunciRendah} {$tpRendah}, namun menunjukkan capaian {$kunciTinggi} {$tpTinggi}.";
    }

}