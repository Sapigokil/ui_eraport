<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ekskul;
use App\Models\EkskulSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Season;
use App\Models\NilaiEkskul;
use Illuminate\Support\Facades\DB;

class EkskulNilaiController extends Controller
{
    // ========================================================================
    // FITUR 1: KELOLA PESERTA EKSKUL (Menautkan Siswa ke Ekskul)
    // ========================================================================

    public function indexPeserta(Request $request)
    {
        $ekskuls = Ekskul::with('guru')
            ->withCount('peserta')
            ->orderBy('nama_ekskul', 'asc')
            ->paginate(10);

        return view('ekskul.peserta_index', compact('ekskuls'));
    }

    public function editPeserta(Request $request, $id_ekskul)
    {
        $ekskul = Ekskul::with('guru')->findOrFail($id_ekskul);
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        // Hitung peserta per kelas (untuk badge di sidebar)
        $peserta_per_kelas = EkskulSiswa::where('ekskul_siswa.id_ekskul', $id_ekskul)
            ->join('siswa', 'ekskul_siswa.id_siswa', '=', 'siswa.id_siswa')
            ->select('siswa.id_kelas', DB::raw('count(*) as total'))
            ->groupBy('siswa.id_kelas')
            ->pluck('total', 'id_kelas')
            ->toArray();

        $filter_id_kelas = $request->input('id_kelas');
        $siswa_list = [];
        $registered_ids = [];

        if ($filter_id_kelas) {
            $siswa_list = Siswa::where('id_kelas', $filter_id_kelas)
                ->orderBy('nama_siswa')
                ->get();

            $registered_ids = EkskulSiswa::where('id_ekskul', $id_ekskul)
                ->whereIn('id_siswa', $siswa_list->pluck('id_siswa'))
                ->pluck('id_siswa')
                ->toArray();
        }

        $total_peserta = EkskulSiswa::where('id_ekskul', $id_ekskul)->count();

        return view('ekskul.peserta_edit', compact(
            'ekskul', 
            'kelas', 
            'filter_id_kelas', 
            'siswa_list', 
            'registered_ids',
            'total_peserta',
            'peserta_per_kelas'
        ));
    }

    public function updatePeserta(Request $request, $id_ekskul)
    {
        $request->validate([
            'id_kelas_filter' => 'required',
        ]);

        $id_kelas = $request->id_kelas_filter;
        $submitted_ids = $request->input('siswa_ids', []);

        DB::beginTransaction();
        try {
            // 1. Ambil semua siswa di kelas yang sedang diedit
            $all_siswa_in_class = Siswa::where('id_kelas', $id_kelas)->pluck('id_siswa')->toArray();

            // 2. Detach (Hapus) siswa di kelas ini dari ekskul tsb
            EkskulSiswa::where('id_ekskul', $id_ekskul)
                ->whereIn('id_siswa', $all_siswa_in_class)
                ->delete();

            // 3. Attach (Pasang) kembali siswa yang dicentang
            $data_insert = [];
            foreach ($submitted_ids as $id_siswa) {
                $data_insert[] = [
                    'id_ekskul' => $id_ekskul,
                    'id_siswa' => $id_siswa
                ];
            }
            
            if(!empty($data_insert)) {
                EkskulSiswa::insert($data_insert);
            }
            
            DB::commit();
            return redirect()->route('ekskul.peserta.edit', ['id' => $id_ekskul, 'id_kelas' => $id_kelas])
                ->with('success', 'Data peserta kelas ini berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update peserta: ' . $e->getMessage());
        }
    }


    // ========================================================================
    // FITUR 2: INPUT NILAI EKSKUL
    // ========================================================================

    private function mapSemesterToInt(string $semester): ?int
    {
        $map = ['GANJIL' => 1, 'GENAP' => 2];
        return $map[strtoupper($semester)] ?? null;
    }

    public function indexNilai(Request $request)
    {
        $activeSeason = Season::where('is_active', 1)->first();
        
        $defaultSemester = ($activeSeason && $activeSeason->semester == 1) ? 'Ganjil' : 'Genap';
        $defaultTahun = $activeSeason ? $activeSeason->tahun_ajaran : date('Y').'/'.(date('Y')+1);

        $semesterRaw = $request->input('semester', $defaultSemester);
        $tahunAjaran = $request->input('tahun_ajaran', $defaultTahun);
        
        $semesterInt = $this->mapSemesterToInt($semesterRaw);

        // Logic Validasi Akses
        $isLocked = true; 
        $accessStatus = 'denied'; 
        $lockMessage = "";

        if ($activeSeason) {
            $isSamePeriod = ($activeSeason->semester == $semesterInt && $activeSeason->tahun_ajaran == $tahunAjaran);
            
            if (!$isSamePeriod) {
                $accessStatus = 'denied';
                $lockMessage = "SEMESTER TIDAK SESUAI: Sistem aktif untuk <b>" . ($activeSeason->semester == 1 ? 'Ganjil' : 'Genap') . " " . $activeSeason->tahun_ajaran . "</b>.";
            } else {
                $today = date('Y-m-d');
                $isInDateRange = ($today >= $activeSeason->start_date && $today <= $activeSeason->end_date);

                if ($isInDateRange && $activeSeason->is_open) {
                    $accessStatus = 'open';
                    $isLocked = false;
                } else {
                    $accessStatus = 'read_only';
                    $lockMessage = "Mode Terkunci / Lihat Saja. Diluar jadwal input nilai.";
                }
            }
        } else {
            $lockMessage = "Tidak ada Season aktif saat ini.";
        }

        $ekskuls = Ekskul::with('guru')
            ->withCount([
                'peserta', 
                'nilai as sudah_dinilai' => function ($query) use ($semesterInt, $tahunAjaran) {
                    $query->where('semester', $semesterInt)
                          ->where('tahun_ajaran', $tahunAjaran);
                }
            ])
            ->orderBy('nama_ekskul')
            ->get();

        return view('ekskul.nilai_index', compact(
            'ekskuls', 'semesterRaw', 'tahunAjaran', 'isLocked', 'accessStatus', 'lockMessage', 'activeSeason'
        ));
    }

    public function inputNilai(Request $request, $id_ekskul)
    {
        $ekskul = Ekskul::with('guru')->findOrFail($id_ekskul);
        
        $semesterRaw = $request->input('semester');
        $tahunAjaran = $request->input('tahun_ajaran');
        $semesterInt = $this->mapSemesterToInt($semesterRaw);

        // Ambil Siswa Peserta
        $siswa = EkskulSiswa::with(['siswa.kelas'])
            ->where('ekskul_siswa.id_ekskul', $id_ekskul)
            ->join('siswa', 'ekskul_siswa.id_siswa', '=', 'siswa.id_siswa')
            ->leftJoin('kelas', 'siswa.id_kelas', '=', 'kelas.id_kelas')
            ->select('ekskul_siswa.*')
            ->orderBy('kelas.nama_kelas', 'asc')
            ->orderBy('siswa.nama_siswa', 'asc')
            ->get();

        // List Kelas Unik Filter
        $listKelas = $siswa->map(function($item) {
            return $item->siswa->kelas;
        })->unique('id_kelas')->values();

        // Nilai Existing
        $existing_nilai = DB::table('nilai_ekskul')
            ->where('id_ekskul', $id_ekskul)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get()
            ->keyBy('id_siswa');

        // Statistik Header
        $totalPeserta = $siswa->count();
        $sudahDinilai = $existing_nilai->count();
        $persenSelesai = $totalPeserta > 0 ? round(($sudahDinilai / $totalPeserta) * 100) : 0;

        $headerData = (object) [
            'nama_ekskul' => $ekskul->nama_ekskul,
            'pembimbing' => $ekskul->guru->nama_guru ?? 'Belum Ditentukan',
            'semester' => $semesterRaw,
            'tahun_ajaran' => $tahunAjaran,
            'total' => $totalPeserta,
            'dinilai' => $sudahDinilai,
            'persen' => $persenSelesai
        ];

        return view('ekskul.nilai_input', compact(
            'ekskul', 'siswa', 'existing_nilai', 'headerData', 'listKelas', 'semesterRaw', 'tahunAjaran'
        ));
    }

    /**
     * SIMPAN NILAI (STORE) - FIXED & CLEAN
     */
    public function storeNilai(Request $request)
    {
        // Validasi dasar
        $request->validate([
            'id_ekskul' => 'required',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
            // Kita validasi bulk_json_data, bukan selected_ids lagi
            'bulk_json_data' => 'required', 
        ]);

        $semesterInt = $this->mapSemesterToInt($request->semester);
        
        // Decode JSON dari view
        $dataPeserta = json_decode($request->bulk_json_data, true);

        if (empty($dataPeserta)) {
            return back()->with('error', 'Tidak ada data siswa yang dikirim.');
        }

        DB::beginTransaction();
        try {
            // Loop data dari hasil decode JSON
            foreach ($dataPeserta as $item) {
                
                $idSiswa = $item['id_siswa'];
                $predikat = $item['predikat'] ?? null;
                $keterangan = $item['keterangan'] ?? null;
                $idKelasSnapshot = $item['id_kelas'] ?? null;

                NilaiEkskul::updateOrCreate(
                    [
                        'id_ekskul' => $request->id_ekskul,
                        'id_siswa' => $idSiswa,
                        'semester' => $semesterInt,
                        'tahun_ajaran' => $request->tahun_ajaran,
                    ],
                    [
                        'id_kelas' => $idKelasSnapshot,
                        'predikat' => $predikat,
                        'keterangan' => $keterangan,
                        'updated_at' => now(),
                    ]
                );
            }

            DB::commit();
            return back()->with('success', 'Data nilai (' . count($dataPeserta) . ' siswa) berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function checkPrerequisite(Request $request)
    {
        $season = Season::where('is_active', 1)->first();
        
        if (!$season || $season->is_open == 0) {
            return response()->json([
                'status' => 'locked_season',
                'message' => 'Input nilai dikunci. Tidak ada Season yang aktif atau sedang ditutup oleh Admin.',
                'season' => null
            ]);
        }

        $seasonData = [
            'semester' => $season->semester == 1 ? 'Ganjil' : 'Genap',
            'tahun' => $season->tahun_ajaran,
            'status' => 'Terbuka',
            'start' => date('d/m/Y', strtotime($season->start_date)),
            'end' => date('d/m/Y', strtotime($season->end_date))
        ];

        $inputSemesterInt = $this->mapSemesterToInt($request->semester);

        if ($inputSemesterInt != $season->semester || $request->tahun_ajaran != $season->tahun_ajaran) {
            return response()->json([
                'status' => 'locked_season',
                'message' => 'Anda sedang melihat data di luar Season aktif. Input hanya diperbolehkan pada <b>Semester ' . $seasonData['semester'] . ' Tahun Ajaran ' . $seasonData['tahun'] . '</b>.',
                'season' => $seasonData
            ]);
        }

        $today = date('Y-m-d');
        if ($today < $season->start_date || $today > $season->end_date) {
            return response()->json([
                'status' => 'locked_season',
                'message' => 'Akses ditutup karena di luar jadwal input nilai yang telah ditentukan.',
                'season' => $seasonData
            ]);
        }

        return response()->json([
            'status' => 'safe',
            'season' => $seasonData
        ]);
    }
}