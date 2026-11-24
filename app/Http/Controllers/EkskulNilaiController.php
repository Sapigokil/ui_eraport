<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ekskul;
use App\Models\EkskulSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Season;
use App\Models\NilaiEkskul;
use App\Models\Guru; // Pastikan model Guru dipanggil
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Pastikan Auth dipanggil

class EkskulNilaiController extends Controller
{
    // ========================================================================
    // FITUR 1: KELOLA PESERTA EKSKUL (Menautkan Siswa ke Ekskul)
    // ========================================================================

    public function indexPeserta(Request $request)
    {
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']); 
        
        if ($isGuru) {
            $id_guru_filter = $user->id_guru;
        } else {
            $id_guru_filter = $request->id_guru; 
        }

        $guruList = Guru::orderBy('nama_guru')->get();

        $query = Ekskul::with('guru')->withCount([
            'peserta' => function ($q) {
                $q->whereHas('siswa', function ($sq) {
                    $sq->where('status', 'aktif');
                });
            }
        ]);

        if ($id_guru_filter) {
            $query->where('id_guru', $id_guru_filter);
        }

        $ekskuls = $query->orderBy('nama_ekskul', 'asc')->paginate(10)->withQueryString();

        return view('ekskul.peserta_index', compact('ekskuls', 'isGuru', 'id_guru_filter', 'guruList'));
    }

    public function editPeserta(Request $request, $id_ekskul)
    {
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']);

        $ekskul = Ekskul::with('guru')->findOrFail($id_ekskul);

        if ($isGuru && $ekskul->id_guru != $user->id_guru) {
            return redirect()->route('ekskul.peserta.index')->with('error', 'Akses Ditolak! Anda bukan pembimbing ekstrakurikuler ini.');
        }

        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        $peserta_per_kelas = EkskulSiswa::where('ekskul_siswa.id_ekskul', $id_ekskul)
            ->join('siswa', 'ekskul_siswa.id_siswa', '=', 'siswa.id_siswa')
            ->where('siswa.status', 'aktif')
            ->select('siswa.id_kelas', DB::raw('count(*) as total'))
            ->groupBy('siswa.id_kelas')
            ->pluck('total', 'id_kelas')
            ->toArray();

        $filter_id_kelas = $request->input('id_kelas');
        $siswa_list = [];
        $registered_ids = [];

        if ($filter_id_kelas) {
            $siswa_list = Siswa::where('id_kelas', $filter_id_kelas)
                ->where('status', 'aktif')
                ->orderBy('nama_siswa')
                ->get();

            $registered_ids = EkskulSiswa::where('id_ekskul', $id_ekskul)
                ->whereIn('id_siswa', $siswa_list->pluck('id_siswa'))
                ->pluck('id_siswa')
                ->toArray();
        }

        $total_peserta = EkskulSiswa::where('id_ekskul', $id_ekskul)
            ->whereHas('siswa', function ($q) {
                $q->where('status', 'aktif');
            })
            ->count();

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
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']);

        $ekskul = Ekskul::findOrFail($id_ekskul);

        if ($isGuru && $ekskul->id_guru != $user->id_guru) {
            return redirect()->route('ekskul.peserta.index')->with('error', 'Akses Ditolak! Anda tidak memiliki izin memperbarui ekskul ini.');
        }

        $request->validate([
            'id_kelas_filter' => 'required',
        ]);

        $id_kelas = $request->id_kelas_filter;
        $submitted_ids = $request->input('siswa_ids', []);

        DB::beginTransaction();
        try {
            $all_siswa_in_class = Siswa::where('id_kelas', $id_kelas)
                ->where('status', 'aktif')
                ->pluck('id_siswa')
                ->toArray();

            EkskulSiswa::where('id_ekskul', $id_ekskul)
                ->whereIn('id_siswa', $all_siswa_in_class)
                ->delete();

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
        // 1. IDENTIFIKASI ROLE & GURU FILTER
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']); 
        
        if ($isGuru) {
            $id_guru_filter = $user->id_guru;
        } else {
            $id_guru_filter = $request->id_guru; 
        }

        $guruList = Guru::orderBy('nama_guru')->get();

        // 2. SEASON LOGIC
        $activeSeason = Season::where('is_active', 1)->first();
        
        $defaultSemester = ($activeSeason && $activeSeason->semester == 1) ? 'Ganjil' : 'Genap';
        $defaultTahun = $activeSeason ? $activeSeason->tahun_ajaran : date('Y').'/'.(date('Y')+1);

        $semesterRaw = $request->input('semester', $defaultSemester);
        $tahunAjaran = $request->input('tahun_ajaran', $defaultTahun);
        
        $semesterInt = $this->mapSemesterToInt($semesterRaw);

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

        // 3. QUERY EKSKUL BERSERTA FILTER GURU
        $query = Ekskul::with('guru')
            ->withCount([
                'peserta' => function ($q) {
                    $q->whereHas('siswa', function ($sq) {
                        $sq->where('status', 'aktif');
                    });
                },
                'nilai as sudah_dinilai' => function ($q) use ($semesterInt, $tahunAjaran) {
                    $q->where('semester', $semesterInt)
                      ->where('tahun_ajaran', $tahunAjaran);
                }
            ]);

        if ($id_guru_filter) {
            $query->where('id_guru', $id_guru_filter);
        }

        $ekskuls = $query->orderBy('nama_ekskul')->get();

        return view('ekskul.nilai_index', compact(
            'ekskuls', 'semesterRaw', 'tahunAjaran', 'isLocked', 'accessStatus', 'lockMessage', 'activeSeason',
            'isGuru', 'id_guru_filter', 'guruList'
        ));
    }

    public function inputNilai(Request $request, $id_ekskul)
    {
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']);

        $ekskul = Ekskul::with('guru')->findOrFail($id_ekskul);
        
        // SECURITY LOCK: Mencegah guru akses URL manual untuk input nilai ekskul lain
        if ($isGuru && $ekskul->id_guru != $user->id_guru) {
            return redirect()->route('ekskul.nilai.index')->with('error', 'Akses Ditolak! Anda bukan pembimbing ekstrakurikuler ini.');
        }

        $semesterRaw = $request->input('semester');
        $tahunAjaran = $request->input('tahun_ajaran');
        $semesterInt = $this->mapSemesterToInt($semesterRaw);

        $siswa = EkskulSiswa::with(['siswa.kelas'])
            ->where('ekskul_siswa.id_ekskul', $id_ekskul)
            ->join('siswa', 'ekskul_siswa.id_siswa', '=', 'siswa.id_siswa')
            ->where('siswa.status', 'aktif')
            ->leftJoin('kelas', 'siswa.id_kelas', '=', 'kelas.id_kelas')
            ->select('ekskul_siswa.*')
            ->orderBy('kelas.nama_kelas', 'asc')
            ->orderBy('siswa.nama_siswa', 'asc')
            ->get();

        $listKelas = $siswa->map(function($item) {
            return $item->siswa->kelas;
        })->unique('id_kelas')->values();

        $existing_nilai = DB::table('nilai_ekskul')
            ->where('id_ekskul', $id_ekskul)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get()
            ->keyBy('id_siswa');

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

    public function storeNilai(Request $request)
    {
        $user = Auth::user();
        $isGuru = !$user->hasAnyRole(['developer', 'admin_erapor', 'guru_erapor']);

        $ekskul = Ekskul::findOrFail($request->id_ekskul);

        // SECURITY LOCK
        if ($isGuru && $ekskul->id_guru != $user->id_guru) {
            return back()->with('error', 'Akses Ditolak! Anda tidak memiliki izin menyimpan nilai di ekskul ini.');
        }

        $request->validate([
            'id_ekskul' => 'required',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
            'bulk_json_data' => 'required', 
        ]);

        $semesterInt = $this->mapSemesterToInt($request->semester);
        
        $dataPeserta = json_decode($request->bulk_json_data, true);

        if (empty($dataPeserta)) {
            return back()->with('error', 'Tidak ada data siswa yang dikirim.');
        }

        DB::beginTransaction();
        try {
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