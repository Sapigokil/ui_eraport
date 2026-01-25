<?php
// File: app/Http/Controllers/NilaiAkhirController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiAkhir;
use App\Models\Pembelajaran;
use App\Models\Sumatif; 
use App\Models\Project; 
use App\Models\BobotNilai;
use App\Http\Controllers\RaporController;
use App\Services\NilaiAkhirService;
use App\Services\CapaianAkhirService;

class NilaiAkhirController extends Controller
{
    private function mapSemesterToInt(string $semester): ?int
    {
        $map = [
            'GANJIL' => 1,
            'GENAP' => 2,
        ];
        return $map[strtoupper(trim($semester))] ?? null;
    }

    public function generateCapaianAkhir($siswa, $semuaNilai): ?string
    {
        if ($semuaNilai->count() === 0) {
            return "Data nilai intrakurikuler dan project belum tersedia.";
        }

        // ===============================
        // 🔥 BATASI MAKSIMAL 2 TP SAJA
        // ===============================
        if ($semuaNilai->count() > 2) {
            $terendahTmp  = $semuaNilai->sortBy('nilai')->first();
            $tertinggiTmp = $semuaNilai->sortByDesc('nilai')->first();

            // Kalau TP terendah & tertinggi sama (edge case)
            if ($terendahTmp['tp'] === $tertinggiTmp['tp']) {
                $semuaNilai = collect([$terendahTmp]);
            } else {
                $semuaNilai = collect([$terendahTmp, $tertinggiTmp]);
            }
        }

        $nilaiValid = $semuaNilai
            ->filter(fn ($n) => !empty(trim((string) $n['tp'])));

        if ($nilaiValid->isEmpty()) {
            return null;
        }

        // === BARU LANJUT KE LOGIKA LAMA ===
        $terendah  = $nilaiValid->sortBy('nilai')->first();
        $tertinggi = $nilaiValid->sortByDesc('nilai')->first();

        // Kasus 1: Nilai Tunggal atau Sama Semua
        if ($nilaiValid->count() === 1 || $terendah['nilai'] === $tertinggi['nilai']) {
            $nilaiKomparasi = $terendah['nilai']; 
            
            if ($nilaiKomparasi > 84) {
                $narasi = "Menunjukkan penguasaan yang baik dalam hal";
            } else {
                $narasi = "Perlu penguatan dalam hal";
            }
            
            $allTujuan = $nilaiValid
            ->pluck('tp')
            ->filter(fn ($tp) => !empty(trim((string) $tp)))
            ->unique()
            ->implode(', ');
            
            if ($allTujuan === '') {
                return null;
            }

            return $narasi . " " . $allTujuan . ".";
        } 
        
        // Kasus 2: Nilai Bervariasi
        $nilaiRendah = $terendah['nilai'];
        $tpRendah = trim((string) ($terendah['tp'] ?? ''));

        if ($nilaiRendah < 81) {
            $kunciRendah = "Perlu peningkatan dalam hal";
        } else {
            $kunciRendah = "Perlu penguatan dalam hal";
        }
        
        $nilaiTinggi = $tertinggi['nilai'];
        $tpTinggi = trim((string) ($tertinggi['tp'] ?? ''));

        if ($nilaiTinggi > 89) {
            $kunciTinggi = "Mahir dalam hal";
        } else {
            $kunciTinggi = "Baik dalam hal";
        }

        $narasi = "{$kunciRendah} {$tpRendah}, namun menunjukkan capaian {$kunciTinggi} {$tpTinggi}.";
        
        return $narasi;
    }

    public function index(Request $request)
    {
        // 1. SETUP DEFAULT
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
        $semesterDB   = $this->mapSemesterToInt($semesterRaw);

        // 2. DATA MASTER (DROPDOWN)
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $mapel = collect();
        
        // Ambil List Mapel (Sesuai Logika Dropdown Monitoring)
        if ($id_kelas) {
            $mapel = Pembelajaran::with(['mapel' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->where('id_kelas', $id_kelas)
            ->get()
            ->pluck('mapel')
            ->filter()
            ->sortBy([
                ['kategori', 'asc'], 
                ['urutan', 'asc'],   
            ])
            ->values();
        }

        $siswa = collect();
        $rekap = [];
        $error = null;
        $bobotInfo = null;

        // 3. PROSES DATA UTAMA
        if ($request->filled(['id_kelas', 'id_mapel', 'tahun_ajaran', 'semester'])) {
            
            // Validasi Input
            if (is_null($semesterDB)) {
                $error = 'Nilai semester tidak valid.';
                goto render_view;
            }

            $selectedMapel = MataPelajaran::where('id_mapel', $id_mapel)
                ->where('is_active', 1)
                ->first();

            if (!$selectedMapel) {
                $error = 'Mata pelajaran sudah tidak aktif.';
                goto render_view;
            }

            // --- A. FILTER AGAMA (LOGIKA BARU) ---
            $namaMapel   = $selectedMapel->nama_mapel;
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

            // --- B. AMBIL SISWA SESUAI FILTER ---
            $querySiswa = Siswa::with('detail')->where('id_kelas', $id_kelas);

            if ($filterAgama !== null) {
                $querySiswa->whereHas('detail', function ($q) use ($filterAgama) {
                    // Gunakan RAW untuk case-insensitive matching yang aman
                    $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(agama)'), $filterAgama);
                });
            }

            $siswa = $querySiswa->orderBy('nama_siswa')->get();

            if ($siswa->isEmpty()) {
                $error = 'Tidak ada siswa ditemukan (Cek filter agama atau data siswa).';
                goto render_view;
            }

            // --- C. AMBIL INFO BOBOT (Hanya untuk Info di View) ---
            $bobotInfo = BobotNilai::where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', strtoupper($semesterRaw))
                ->first();

            // --- D. AMBIL NILAI AKHIR (DARI TABEL NILAI_AKHIR) ---
            // Kita ambil sekaligus pakai whereIn id_siswa agar efisien
            $siswaIds = $siswa->pluck('id_siswa')->toArray();

            $nilaiAkhirData = NilaiAkhir::where('id_kelas', $id_kelas)
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semesterDB)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->whereIn('id_siswa', $siswaIds)
                ->get()
                ->keyBy('id_siswa');

            // --- E. MAPPING DATA KE VIEW ---
            foreach ($siswa as $s) {
                $data = $nilaiAkhirData->get($s->id_siswa);

                // Helper kecil untuk format angka: Jika null tampilkan '-', jika ada ubah ke Int
                $formatInt = fn($val) => ($val !== null && $val !== '') ? (int) $val : '-';
                
                // Khusus Rata-rata biarkan ada koma (opsional, jika ingin bulat juga ganti jadi formatInt)
                $rataSumatif = ($data && $data->rata_sumatif !== null) ? $data->rata_sumatif : '-';

                // Jika data belum ada, set default '-'
                $rekap[$s->id_siswa] = [
                    // Sumatif Detail
                    's1' => $data->nilai_s1 ?? '-',
                    's2' => $data->nilai_s2 ?? '-',
                    's3' => $data->nilai_s3 ?? '-',
                    's4' => $data->nilai_s4 ?? '-',
                    's5' => $data->nilai_s5 ?? '-',
                    
                    // Rata & Bobot Sumatif
                    'rata_sumatif'  => $data->rata_sumatif ?? '-',
                    'bobot_sumatif' => $formatInt($data->bobot_sumatif ?? '-'),
                    
                    // Project
                    'nilai_project' => $formatInt($data->nilai_project ?? '-'),
                    'rata_project'  => $data->rata_project ?? '-', // Jika kolom ini ada
                    'bobot_project' => $formatInt($data->bobot_project ?? '-'),
                    
                    // Final
                    'nilai_akhir'   => $formatInt($data->nilai_akhir ?? '-'),
                    'capaian_akhir' => $data->capaian_akhir ?? 'Belum dilakukan finalisasi nilai.',
                ];
            }
        }

        render_view:
        return view('nilai.nilaiakhir', compact(
            'kelas',
            'mapel',
            'siswa',
            'rekap',
            'error',
            'bobotInfo'
        ));
    }
}