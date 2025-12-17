<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\InfoSekolah;
use App\Models\Catatan;
use App\Models\StatusRapor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RaporController extends Controller
{
    /**
     * Halaman Monitoring Progres Per Mata Pelajaran
     */
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL') ? 1 : 2;

        // Ambil data sekolah agar tidak undefined di view monitoring
        $infoSekolah = InfoSekolah::first();
        $namasekolah = $infoSekolah->nama_sekolah ?? 'E-Rapor SMK';
        $alamatsekolah = $infoSekolah->jalan ?? 'Alamat belum diatur';

        $monitoring = [];

        if ($id_kelas) {
            $pembelajaran = DB::table('pembelajaran')
                ->leftJoin('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel') 
                ->where('pembelajaran.id_kelas', $id_kelas)
                ->select('pembelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
                ->get();

            if ($pembelajaran->isNotEmpty()) {
                $totalSiswaKelas = DB::table('siswa')->where('id_kelas', $id_kelas)->count();

                foreach ($pembelajaran as $mp) {
                    $namaMapel = $mp->nama_mapel ?? "Mapel ID: " . $mp->id_mapel;

                    $siswaTuntasIds = DB::table(function ($query) use ($mp, $semesterInt, $tahun_ajaran) {
                        $query->select('id_siswa')
                            ->from('sumatif')
                            ->where('id_mapel', $mp->id_mapel)
                            ->where('semester', $semesterInt)
                            ->where('tahun_ajaran', $tahun_ajaran)
                            ->where('nilai', '>', 0)
                            ->unionAll(
                                DB::table('project')
                                    ->select('id_siswa')
                                    ->where('id_mapel', $mp->id_mapel)
                                    ->where('semester', $semesterInt)
                                    ->where('tahun_ajaran', $tahun_ajaran)
                                    ->where('nilai', '>', 0)
                            );
                    }, 'combined_grades')
                    ->select('id_siswa', DB::raw('count(*) as total'))
                    ->groupBy('id_siswa')
                    ->having('total', '>=', 1)
                    ->pluck('id_siswa');

                    $monitoring[] = (object)[
                        'id_mapel' => $mp->id_mapel,
                        'nama_mapel' => $namaMapel,
                        'tuntas' => $siswaTuntasIds->count(),
                        'belum' => $totalSiswaKelas - $siswaTuntasIds->count(),
                        'total_siswa' => $totalSiswaKelas
                    ];
                }
            }
        }

        return view('rapor.index_rapor', compact('kelas', 'monitoring', 'id_kelas', 'semesterRaw', 'tahun_ajaran', 'namasekolah', 'alamatsekolah'));
    }

    /**
     * AJAX: Mendapatkan daftar nama siswa untuk Modal Detail di Monitoring
     */
    public function getDetailSiswa(Request $request)
    {
        $id_mapel = $request->id_mapel;
        $id_kelas = $request->id_kelas;
        $tipe = $request->tipe; 
        $semester = (strtoupper($request->semester) == 'GANJIL') ? 1 : 2;
        $tahun_ajaran = $request->tahun_ajaran;

        $semuaSiswa = DB::table('siswa')
            ->where('id_kelas', $id_kelas)
            ->select('id_siswa', 'nama_siswa', 'nis')
            ->get();

        $tuntasIds = DB::table(function ($query) use ($id_mapel, $semester, $tahun_ajaran) {
            $query->select('id_siswa')
                ->from('sumatif')
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semester)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->where('nilai', '>', 0)
                ->unionAll(
                    DB::table('project')
                        ->select('id_siswa')
                        ->where('id_mapel', $id_mapel)
                        ->where('semester', $semester)
                        ->where('tahun_ajaran', $tahun_ajaran)
                        ->where('nilai', '>', 0)
                );
        }, 'combined_grades')
        ->select('id_siswa', DB::raw('count(*) as total'))
        ->groupBy('id_siswa')
        ->having('total', '>=', 1)
        ->pluck('id_siswa')
        ->toArray();

        if ($tipe == 'tuntas') {
            $result = $semuaSiswa->whereIn('id_siswa', $tuntasIds);
        } else {
            $result = $semuaSiswa->whereNotIn('id_siswa', $tuntasIds);
        }

        return response()->json($result->values());
    }

    /**
     * Mesin Sinkronisasi
     */
    public function perbaruiStatusRapor($id_siswa, $semester, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semester) == 'GANJIL' || $semester == '1') ? 1 : 2;
        $siswa = Siswa::findOrFail($id_siswa);

        $daftarMapel = DB::table('pembelajaran')->where('id_kelas', $siswa->id_kelas)->pluck('id_mapel');
        $totalMapel = $daftarMapel->count();
        $mapelTuntas = 0;

        foreach ($daftarMapel as $id_mapel) {
            $sumatifCount = DB::table('sumatif')
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', (string)$tahun_ajaran)
                ->where('nilai', '>', 0)
                ->count();

            $projectCount = DB::table('project')
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', (string)$tahun_ajaran)
                ->where('nilai', '>', 0)
                ->count();

            if (($sumatifCount + $projectCount) >= 1) { 
                $mapelTuntas++; 
            }
        }

        $isCatatanReady = DB::table('catatan')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', (string)$tahun_ajaran)
            ->whereNotNull('catatan_wali_kelas') 
            ->whereRaw("TRIM(catatan_wali_kelas) != ''") 
            ->exists();

        return StatusRapor::updateOrCreate(
            ['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => (string)$tahun_ajaran],
            [
                'id_kelas' => $siswa->id_kelas,
                'total_mapel_seharusnya' => $totalMapel,
                'mapel_tuntas_input' => $mapelTuntas,
                'is_catatan_wali_ready' => $isCatatanReady ? 1 : 0,
                'status_akhir' => ($mapelTuntas >= $totalMapel && $isCatatanReady) ? 'Siap Cetak' : 'Belum Lengkap'
            ]
        );
    }

    public function sinkronkanKelas(Request $request)
    {
        try {
            $id_kelas = $request->id_kelas;
            $semester = $request->semester;
            $tahun_ajaran = $request->tahun_ajaran;
            if (!$id_kelas) return response()->json(['success' => false, 'message' => 'ID Kelas tidak ditemukan'], 400);

            $daftarSiswa = Siswa::where('id_kelas', $id_kelas)->get();
            foreach ($daftarSiswa as $s) {
                $this->perbaruiStatusRapor($s->id_siswa, $semester, $tahun_ajaran);
            }
            return response()->json(['success' => true, 'message' => 'Sinkronisasi berhasil']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Cetak Rapor Per Siswa
     */
    public function cetakIndex(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        $siswaList = [];

        if ($id_kelas) {
            $siswaList = Siswa::with('kelas')
                ->where('id_kelas', $id_kelas)
                ->orderBy('nama_siswa', 'asc')
                ->get();

            foreach ($siswaList as $s) {
                $s->status_monitoring = DB::table('status_rapor')
                    ->where('id_siswa', $s->id_siswa)
                    ->where('semester', (int)$semesterInt)
                    ->where('tahun_ajaran', trim((string)$tahun_ajaran))
                    ->first();

                $s->data_catatan = DB::table('catatan')
                    ->where('id_siswa', $s->id_siswa)
                    ->where('semester', (int)$semesterInt)
                    ->where('tahun_ajaran', trim((string)$tahun_ajaran))
                    ->first();
            }
        }

        return view('rapor.cetak_rapor', compact('kelas', 'siswaList', 'id_kelas', 'semesterRaw', 'tahun_ajaran'));
    }

    public function getMapelByKelas($id_kelas)
    {
        $mapel = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('pembelajaran.id_kelas', $id_kelas)
            ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
            ->get();
        return response()->json($mapel);
    }

    public function cetak_proses($id_siswa, Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        // 1. Ambil data siswa beserta relasi kelas
        $siswa = Siswa::with('kelas')->findOrFail($id_siswa);
        
        // 2. Ambil data sekolah
        $getSekolah = InfoSekolah::first();
        $nama_sekolah = $getSekolah->nama_sekolah ?? 'SMKN 1 SALATIGA';
        $alamat_sekolah = $getSekolah->jalan ?? 'Jl. Jend. Sudirman No. 1 Salatiga';

        // 3. Logika Generate FASE (Tingkat X -> E, XI/XII -> F)
        // Kita gunakan trim() untuk membersihkan spasi yang mungkin ada di database
        $tingkat = trim($siswa->kelas->tingkat ?? '');
        $fase = match (strtoupper($tingkat)) {
            'X', '10' => 'E',
            'XI', '11', 'XII', '12' => 'F',
            default => '-'
        };

        // 4. Data Wali Kelas & Guru
        $namaWali = $siswa->kelas->wali_kelas;
        $dataGuru = DB::table('guru')
            ->where('nama_guru', 'LIKE', '%' . $namaWali . '%')
            ->first();

        // 5. Olah Mata Pelajaran & Nilai
$mapelRaw = DB::table('pembelajaran')
    ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
    ->where('pembelajaran.id_kelas', $siswa->id_kelas)
    ->select('mata_pelajaran.*')
    ->get();

// DEFINISIKAN URUTAN KATEGORI (Harus sama persis dengan tulisan di database)
    $urutanKustom = [
        'Mata Pelajaran Umum'    => 1,
        'Mata Pelajaran Kejuruan' => 2,
        'Mata Pelajaran Pilihan'  => 3,
        'Muatan Lokal'           => 4
    ];

    // Kelompokkan dan Urutkan berdasarkan array di atas
    $mapelGroup = $mapelRaw->groupBy('kategori')->sortBy(function ($items, $key) use ($urutanKustom) {
        return $urutanKustom[$key] ?? 99; // Jika ada kategori lain, taruh di paling bawah
    });

    foreach ($mapelGroup as $kategori => $daftarMapel) {
        foreach ($daftarMapel as $mp) {
            // ... (Logika hitung nilai sumatif & project tetap sama) ...
            $nilaiSumatif = DB::table('sumatif')
                ->where(['id_siswa' => $id_siswa, 'id_mapel' => $mp->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])
                ->avg('nilai') ?: 0;

            $nilaiProject = DB::table('project')
                ->where(['id_siswa' => $id_siswa, 'id_mapel' => $mp->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])
                ->avg('nilai') ?: 0;

            $mp->nilai_akhir = round(($nilaiSumatif + $nilaiProject) / 2);
            
            // Logika Capaian Kompetensi
            $mp->capaian = $mp->nilai_akhir >= 75 
                ? "Menunjukkan penguasaan yang baik dalam " . $mp->nama_mapel
                : "Perlu bimbingan lebih lanjut dalam " . $mp->nama_mapel;
        }
    }

        // 6. Catatan & Ekskul
        $catatan = DB::table('catatan')
            ->where(['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])
            ->first();

        $dataEkskul = [];
        if ($catatan && !empty($catatan->ekskul)) {
            $ids = explode(',', $catatan->ekskul);
            $predikats = explode(',', $catatan->predikat);
            $keterangans = explode('|', $catatan->keterangan);

            foreach ($ids as $index => $id) {
                $namaEkskul = DB::table('ekskul')->where('id_ekskul', trim($id))->value('nama_ekskul');
                if ($namaEkskul) {
                    $dataEkskul[] = (object)[
                        'nama' => $namaEkskul,
                        'predikat' => $predikats[$index] ?? '-',
                        'keterangan' => $keterangans[$index] ?? '-'
                    ];
                }
            }
        }    

        // --- BARIS dd($fase) DIHAPUS AGAR PROSES LANJUT KE GENERATE PDF ---

        // 7. Penyelarasan Variabel agar Blade tidak Error
        $data = [
            'siswa'         => $siswa,
            'sekolah'       => $nama_sekolah,    
            'infoSekolah'   => $alamat_sekolah,  
            'fase'          => $fase,            // Key ini yang akan dipanggil di view sebagai $fase
            'nipd'          => $siswa->nis,
            'nisn'          => $siswa->nisn,
            'mapelGroup'    => $mapelGroup,
            'catatan'       => $catatan,
            'dataEkskul'    => $dataEkskul,
            'semester'      => $semesterRaw,
            'tahun_ajaran'  => $tahun_ajaran,
            'semesterInt'   => $semesterInt,
            'namaWali'      => $namaWali,
            'nip_wali'      => $dataGuru->nip ?? '-',
        ];

        $pdf = Pdf::loadView('rapor.pdf1_template', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('Rapor_'.$siswa->nama_siswa.'.pdf');
    }
}