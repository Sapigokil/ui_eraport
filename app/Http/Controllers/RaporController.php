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
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class RaporController extends Controller
{
    /**
     * Halaman Monitoring Progres Per Mata Pelajaran
     */
    // public function index(Request $request)
    // {
    //     $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
    //     $id_kelas = $request->id_kelas;
    //     $semesterRaw = $request->semester ?? 'Ganjil';
    //     $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
    //     $semesterInt = (strtoupper($semesterRaw) == 'GANJIL') ? 1 : 2;

    //     $infoSekolah = InfoSekolah::first();
    //     $namasekolah = $infoSekolah->nama_sekolah ?? 'E-Rapor SMK';
    //     $alamatsekolah = $infoSekolah->jalan ?? 'Alamat belum diatur';

    //     $monitoring = [];

    //     if ($id_kelas) {
    //         $pembelajaran = DB::table('pembelajaran')
    //             ->leftJoin('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel') 
    //             ->where('pembelajaran.id_kelas', $id_kelas)
    //             ->select('pembelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
    //             ->get();

    //         if ($pembelajaran->isNotEmpty()) {
    //             $totalSiswaKelas = DB::table('siswa')->where('id_kelas', $id_kelas)->count();

    //             foreach ($pembelajaran as $mp) {
    //                 $namaMapel = $mp->nama_mapel ?? "Mapel ID: " . $mp->id_mapel;

    //                 $siswaTuntasIds = DB::table(function ($query) use ($mp, $semesterInt, $tahun_ajaran) {
    //                     $query->select('id_siswa')
    //                         ->from('sumatif')
    //                         ->where('id_mapel', $mp->id_mapel)
    //                         ->where('semester', $semesterInt)
    //                         ->where('tahun_ajaran', $tahun_ajaran)
    //                         ->where('nilai', '>', 0)
    //                         ->unionAll(
    //                             DB::table('project')
    //                                 ->select('id_siswa')
    //                                 ->where('id_mapel', $mp->id_mapel)
    //                                 ->where('semester', $semesterInt)
    //                                 ->where('tahun_ajaran', $tahun_ajaran)
    //                                 ->where('nilai', '>', 0)
    //                         );
    //                 }, 'combined_grades')
    //                 ->select('id_siswa', DB::raw('count(*) as total'))
    //                 ->groupBy('id_siswa')
    //                 ->having('total', '>=', 1)
    //                 ->pluck('id_siswa');

    //                 $monitoring[] = (object)[
    //                     'id_mapel' => $mp->id_mapel,
    //                     'nama_mapel' => $namaMapel,
    //                     'tuntas' => $siswaTuntasIds->count(),
    //                     'belum' => $totalSiswaKelas - $siswaTuntasIds->count(),
    //                     'total_siswa' => $totalSiswaKelas
    //                 ];
    //             }
    //         }
    //     }

    //     return view('rapor.index_rapor', compact('kelas', 'monitoring', 'id_kelas', 'semesterRaw', 'tahun_ajaran', 'namasekolah', 'alamatsekolah'));
    // }

    // /**
    //  * AJAX: Mendapatkan daftar nama siswa untuk Modal Detail di Monitoring
    //  */
    // public function getDetailSiswa(Request $request)
    // {
    //     $id_mapel = $request->id_mapel;
    //     $id_kelas = $request->id_kelas;
    //     $tipe = $request->tipe;
    //     $semester = (strtoupper($request->semester) == 'GANJIL') ? 1 : 2;
    //     $tahun_ajaran = $request->tahun_ajaran;

    //     $semuaSiswa = DB::table('siswa')
    //         ->where('id_kelas', $id_kelas)
    //         ->select('id_siswa', 'nama_siswa', 'nis')
    //         ->get();

    //     $tuntasIds = DB::table(function ($query) use ($id_mapel, $semester, $tahun_ajaran) {
    //         $query->select('id_siswa')
    //             ->from('sumatif')
    //             ->where('id_mapel', $id_mapel)
    //             ->where('semester', $semester)
    //             ->where('tahun_ajaran', $tahun_ajaran)
    //             ->where('nilai', '>', 0)
    //             ->unionAll(
    //                 DB::table('project')
    //                     ->select('id_siswa')
    //                     ->where('id_mapel', $id_mapel)
    //                     ->where('semester', $semester)
    //                     ->where('tahun_ajaran', $tahun_ajaran)
    //                     ->where('nilai', '>', 0)
    //             );
    //     }, 'combined_grades')
    //     ->select('id_siswa', DB::raw('count(*) as total'))
    //     ->groupBy('id_siswa')
    //     ->having('total', '>=', 1)
    //     ->pluck('id_siswa')
    //     ->toArray();

    //     if ($tipe == 'tuntas') {
    //         $result = $semuaSiswa->whereIn('id_siswa', $tuntasIds);
    //     } else {
    //         $result = $semuaSiswa->whereNotIn('id_siswa', $tuntasIds);
    //     }

    //     return response()->json($result->values());
    // }

    /**
     * AJAX: Get Detail Progress Per Siswa (Untuk Modal di Halaman Cetak)
     */
    public function getDetailProgress(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $id_kelas = $request->id_kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        
        // 1. Konversi semester ke format database (Enum 1 atau 2)
        // Jika input 'Ganjil' simpan 1, jika 'Genap' simpan 2
        $semesterRaw = $request->semester ?? 'Ganjil';
        $semesterEnum = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        // 2. Ambil daftar mata pelajaran di kelas tersebut
        $pembelajaran = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('pembelajaran.id_kelas', $id_kelas)
            ->select(
                'mata_pelajaran.id_mapel', 
                'mata_pelajaran.nama_mapel', 
                'mata_pelajaran.kategori'
            )
            ->get();

        // 3. Loop dan ambil nilai_akhir secara langsung per mapel
        $data = $pembelajaran->map(function($mp) use ($id_siswa, $semesterEnum, $tahun_ajaran) {
            
            $nilai = DB::table('nilai_akhir')
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $mp->id_mapel)
                ->where('semester', (string)$semesterEnum) // Paksa ke string jika Enum di DB terbaca string
                ->where('tahun_ajaran', trim($tahun_ajaran))
                ->first();

            // Logika penentuan status
            $hasNilai = ($nilai && !is_null($nilai->nilai_akhir) && $nilai->nilai_akhir > 0);

            return [
                'nama_mapel' => $mp->nama_mapel,
                'kategori' => match((int)$mp->kategori) {
                    1 => 'Umum',
                    2 => 'Kejuruan',
                    3 => 'Pilihan',
                    4 => 'Muatan Lokal',
                    default => 'Lainnya'
                },
                'is_lengkap' => $hasNilai,
                'nilai_akhir' => $hasNilai ? (int)$nilai->nilai_akhir : '-'
            ];
            dd($data);
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Halaman Cetak Rapor (List Siswa per Kelas)
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

    /**
     * Proses Cetak PDF Rapor Satuan
     */
    public function cetak_proses($id_siswa, Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';

        $data = $this->persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran);

        $pdf = Pdf::loadView('rapor.pdf1_template', $data)
                ->setPaper('a4', 'portrait')
                ->setOption([
                    'isPhpEnabled' => true,
                    'isRemoteEnabled' => true
                ]);

        return $pdf->stream('Rapor_'.$data['siswa']->nama_siswa.'.pdf');
    }

    /**
     * Proses Cetak Rapor Massal per Kelas
     */
    public function cetak_massal(Request $request)
    {
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';

        if (!$id_kelas) {
            return redirect()->back()->with('error', 'Silakan pilih kelas terlebih dahulu.');
        }

        $daftarSiswa = Siswa::where('id_kelas', $id_kelas)->orderBy('nama_siswa', 'asc')->get();
        
        // DEFINISIKAN VARIABEL INI
        $allData = []; 
        
        foreach ($daftarSiswa as $siswa) {
            // Gunakan helper yang sudah kita buat sebelumnya untuk Always Auto-Sync
            $allData[] = $this->persiapkanDataRapor2($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
        }

        // Kirim variabel ke PDF2 (pdf_massal_template)
        $pdf = Pdf::loadView('rapor.pdf2_massal_template', compact('allData'))
                ->setPaper('a4', 'portrait')
                ->setOption([
                    'isPhpEnabled' => true, 
                    'isRemoteEnabled' => true
                ]);

        return $pdf->stream('Rapor_Massal_Kelas_'.$id_kelas.'.pdf');
    }

    /**
     * Helper Private: Logika Inti Auto-Sync & Pengambilan Data
     */
    private function persiapkanDataRapor2($id_siswa, $semesterRaw, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;
        $siswa = Siswa::with('kelas')->findOrFail($id_siswa);
        $getSekolah = InfoSekolah::first();

        // --- 1. ALWAYS AUTO-SYNC NILAI ---
        $pembelajaranSiswa = DB::table('pembelajaran')->where('id_kelas', $siswa->id_kelas)->get();
        foreach ($pembelajaranSiswa as $pb) {
            $avgSumatif = DB::table('sumatif')->where(['id_siswa' => $id_siswa, 'id_mapel' => $pb->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->avg('nilai') ?? 0;
            $avgProject = DB::table('project')->where(['id_siswa' => $id_siswa, 'id_mapel' => $pb->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->avg('nilai') ?? 0;
            
            $totalNilai = 0; $pembagi = 0;
            if ($avgSumatif > 0) { $totalNilai += $avgSumatif; $pembagi++; }
            if ($avgProject > 0) { $totalNilai += $avgProject; $pembagi++; }
            $nilaiFinal = ($pembagi > 0) ? (int) round($totalNilai / $pembagi, 0) : 0;

            if ($nilaiFinal > 0) {
                $existing = DB::table('nilai_akhir')->where(['id_siswa' => $id_siswa, 'id_mapel' => $pb->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->first();
                DB::table('nilai_akhir')->updateOrInsert(
                    ['id_siswa' => $id_siswa, 'id_mapel' => $pb->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran],
                    [
                        'id_kelas' => $siswa->id_kelas, 
                        'nilai_akhir' => $nilaiFinal, 
                        'capaian_akhir' => $existing->capaian_akhir ?? 'Menunjukkan pemahaman yang baik dalam materi ini.', 
                        'updated_at' => now()
                    ]
                );
            }
        }

        // --- 2. MAPEL GROUPING (Sama Persis dengan Cetak Satuan) ---
        $mapelFinal = [];
        $daftarUrutan = [1 => 'MATA PELAJARAN UMUM', 2 => 'MATA PELAJARAN KEJURUAN', 3 => 'MATA PELAJARAN PILIHAN', 4 => 'MUATAN LOKAL'];
        foreach ($daftarUrutan as $key => $headerLabel) {
            $kelompok = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $siswa->id_kelas)
                ->where('mata_pelajaran.kategori', $key)
                ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
                ->get();

            if ($kelompok->isNotEmpty()) {
                foreach ($kelompok as $mp) {
                    $nf = DB::table('nilai_akhir')->where(['id_siswa' => $id_siswa, 'id_mapel' => $mp->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->first();
                    $mp->nilai_akhir = $nf->nilai_akhir ?? 0;
                    $mp->capaian = $nf->capaian_akhir ?? '-';
                }
                $mapelFinal[$key] = $kelompok;
            }
        }

        // --- 3. PARSING EKSKUL ---
        $catatan = DB::table('catatan')->where(['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->first();
        $dataEkskul = [];
        if ($catatan && !empty($catatan->ekskul)) {
            $ids = array_map('trim', explode(',', $catatan->ekskul));
            $grades = !empty($catatan->predikat) ? array_map('trim', explode(',', $catatan->predikat)) : [];
            $descs = !empty($catatan->keterangan) ? array_map('trim', explode('|', $catatan->keterangan)) : [];
            foreach ($ids as $index => $idEkstra) {
                if ($idEkstra != "") {
                    $namaEkskulReal = DB::table('ekskul')->where('id_ekskul', $idEkstra)->value('nama_ekskul');
                    $dataEkskul[] = (object)[
                        'nama' => $namaEkskulReal ?? 'Ekstra ID: ' . $idEkstra,
                        'predikat' => $grades[$index] ?? '-',
                        'keterangan' => $descs[$index] ?? '-'
                    ];
                }
            }
        }

        // --- 4. DATA LAINNYA ---
        $tktRaw = strtoupper(preg_replace("/[^a-zA-Z0-9]/", "", trim($siswa->kelas->tingkat ?? '')));
        $fase = match (true) {
            ($tktRaw === 'X' || $tktRaw === '10') => 'E',
            ($tktRaw === 'XI' || $tktRaw === '11' || $tktRaw === 'XII' || $tktRaw === '12') => 'F',
            default => '-'
        };

        $namaWali = $siswa->kelas->wali_kelas ?? 'Wali Kelas';
        $dataGuru = DB::table('guru')->where('nama_guru', 'LIKE', '%' . $namaWali . '%')->first();

        return [
            'siswa'         => $siswa,
            'fase'          => $fase,
            'sekolah'       => $getSekolah->nama_sekolah ?? 'SMKN 1 SALATIGA',
            'infoSekolah'   => $getSekolah->jalan ?? 'Alamat Sekolah',
            'info_sekolah'  => $getSekolah,
            'mapelGroup'    => $mapelFinal,
            'dataEkskul'    => $dataEkskul,
            'catatan'       => $catatan,
            'semester'      => $semesterRaw,
            'tahun_ajaran'  => $tahun_ajaran,
            'semesterInt'   => $semesterInt,
            'nama_wali'     => $namaWali,
            'nip_wali'      => $dataGuru->nip ?? '-',
        ];
    }

    private function persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;
        $siswa = Siswa::with('kelas')->findOrFail($id_siswa);
        $getSekolah = InfoSekolah::first();

        // --- LOGIKA ALWAYS AUTO-SYNC (NILAI & CAPAIAN) ---
        $pembelajaranSiswa = DB::table('pembelajaran')->where('id_kelas', $siswa->id_kelas)->get();
        
        foreach ($pembelajaranSiswa as $pb) {
            // 1. Kalkulasi Nilai Akhir
            $avgSumatif = DB::table('sumatif')->where(['id_siswa' => $id_siswa, 'id_mapel' => $pb->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->avg('nilai') ?? 0;
            $avgProject = DB::table('project')->where(['id_siswa' => $id_siswa, 'id_mapel' => $pb->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->avg('nilai') ?? 0;
            
            $totalNilai = 0; $pembagi = 0;
            if ($avgSumatif > 0) { $totalNilai += $avgSumatif; $pembagi++; }
            if ($avgProject > 0) { $totalNilai += $avgProject; $pembagi++; }
            $nilaiFinal = ($pembagi > 0) ? (int) round($totalNilai / $pembagi, 0) : 0;

            if ($nilaiFinal > 0) {
                // 2. GENERATE CAPAIAN OTOMATIS (Jika di DB masih kosong)
                $existing = DB::table('nilai_akhir')->where(['id_siswa' => $id_siswa, 'id_mapel' => $pb->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->first();
                
                $teksCapaian = $existing->capaian_akhir 
                    ?? 'Menunjukkan pemahaman yang baik terhadap kompetensi yang dipelajari.';


                // // Jika capaian belum ada, kita bantu buatkan otomatis dari data TP
                // if (empty($teksCapaian)) {
                //     $nilaiTp = DB::table('nilai_tp')
                //         ->join('tujuan_pembelajaran', 'nilai_tp.id_tp', '=', 'tujuan_pembelajaran.id_tp')
                //         ->where(['id_siswa' => $id_siswa, 'id_mapel' => $pb->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])
                //         ->orderBy('nilai', 'desc')
                //         ->get();

                //     if ($nilaiTp->isNotEmpty()) {
                //         $tpMax = $nilaiTp->first();
                //         $tpMin = $nilaiTp->last();
                        
                //         $teksCapaian = "Menunjukkan penguasaan yang sangat baik dalam " . $tpMax->deskripsi;
                //         if ($tpMax->id_tp != $tpMin->id_tp) {
                //             $teksCapaian .= ", namun perlu bimbingan dalam " . $tpMin->deskripsi;
                //         }
                //     } else {
                //         $teksCapaian = 'Menunjukkan pemahaman yang baik dalam materi ini.';
                //     }
                // }

                // 3. Update atau Insert
                DB::table('nilai_akhir')->updateOrInsert(
                    [
                        
                    'id_siswa' => $id_siswa, 
                    'id_mapel' => $pb->id_mapel, 
                    'semester' => $semesterInt, 
                    'tahun_ajaran' => $tahun_ajaran],
                    [
                        'id_kelas' => $siswa->id_kelas, 
                        'nilai_akhir' => $nilaiFinal, 
                        // 'capaian_akhir' => $teksCapaian, 
                        'updated_at' => now()
                    ]
                );
            }
        }

        // --- MAPEL GROUPING (1-4) ---
        $mapelFinal = [];
        $daftarUrutan = [1 => 'MATA PELAJARAN UMUM', 2 => 'MATA PELAJARAN KEJURUAN', 3 => 'MATA PELAJARAN PILIHAN', 4 => 'MUATAN LOKAL'];
        foreach ($daftarUrutan as $key => $headerLabel) {
            $kelompok = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $siswa->id_kelas)
                ->where('mata_pelajaran.kategori', $key)
                ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
                ->get();

            if ($kelompok->isNotEmpty()) {
                foreach ($kelompok as $mp) {
                    $nf = DB::table('nilai_akhir')->where(['id_siswa' => $id_siswa, 'id_mapel' => $mp->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->first();
                    $mp->nilai_akhir = $nf->nilai_akhir ?? 0;
                    $mp->capaian = $nf->capaian_akhir ?? '-';
                }
                $mapelFinal[$key] = $kelompok;
            }
        }

        // --- PARSING EKSKUL ---
        $catatan = DB::table('catatan')->where(['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->first();
        $dataEkskul = [];
        if ($catatan && !empty($catatan->ekskul)) {
            $ids = array_map('trim', explode(',', $catatan->ekskul));
            $grades = !empty($catatan->predikat) ? array_map('trim', explode(',', $catatan->predikat)) : [];
            $descs = !empty($catatan->keterangan) ? array_map('trim', explode('|', $catatan->keterangan)) : [];

            foreach ($ids as $index => $idEkstra) {
                if ($idEkstra != "") {
                    $namaEkskulReal = DB::table('ekskul')->where('id_ekskul', $idEkstra)->value('nama_ekskul');
                    $dataEkskul[] = (object)[
                        'nama' => $namaEkskulReal ?? 'Ekstra ID: ' . $idEkstra,
                        'predikat' => $grades[$index] ?? '-',
                        'keterangan' => $descs[$index] ?? '-'
                    ];
                }
            }
        }

        // --- DATA FASE ---
        $tktRaw = strtoupper(preg_replace("/[^a-zA-Z0-9]/", "", trim($siswa->kelas->tingkat ?? '')));
        $fase = match (true) {
            ($tktRaw === 'X' || $tktRaw === '10') => 'E',
            ($tktRaw === 'XI' || $tktRaw === '11' || $tktRaw === 'XII' || $tktRaw === '12') => 'F',
            default => '-'
        };

        $namaWali = $siswa->kelas->wali_kelas ?? 'Wali Kelas';
        $dataGuru = DB::table('guru')->where('nama_guru', 'LIKE', '%' . $namaWali . '%')->first();

        return [
            'siswa'         => $siswa,
            'fase'          => $fase,
            'sekolah'       => $getSekolah->nama_sekolah ?? 'SMKN 1 SALATIGA',
            'infoSekolah'   => $getSekolah->jalan ?? 'Alamat Sekolah',
            'info_sekolah'  => $getSekolah,
            'mapelGroup'    => $mapelFinal,
            'dataEkskul'    => $dataEkskul,
            'catatan'       => $catatan,
            'semester'      => $semesterRaw,
            'tahun_ajaran'  => $tahun_ajaran,
            'semesterInt'   => $semesterInt,
            'nama_wali'     => $namaWali,
            'nip_wali'      => $dataGuru->nip ?? '-',
        ];
    }

    /**
     * Mesin Sinkronisasi Progres Rapor (Status Siap Cetak)
     */
    public function perbaruiStatusRapor($id_siswa, $semester, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semester) == 'GANJIL' || $semester == '1') ? 1 : 2;
        $siswa = Siswa::findOrFail($id_siswa);

        $daftarMapel = DB::table('pembelajaran')->where('id_kelas', $siswa->id_kelas)->pluck('id_mapel');
        $totalMapel = $daftarMapel->count();
        $mapelTuntas = 0;

        foreach ($daftarMapel as $id_mapel) {
            $nilaiAkhir = DB::table('nilai_akhir')
                ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])
                ->where('nilai_akhir', '>', 0)
                ->exists();

           if (($sumatifCount + $projectCount) > 0) {

            // =========================
            // 1. AMBIL DATA SUMATIF
            // =========================
            $tpSumatif = DB::table('sumatif')
                ->where([
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $id_mapel,
                    'semester' => $semesterInt,
                    'tahun_ajaran' => $tahun_ajaran,
                ])
                ->where('nilai', '>', 0)
                ->get()
                ->map(fn ($s) => [
                    'nilai' => (float) $s->nilai,
                    'tp' => $s->tujuan_pembelajaran,
                ]);

            // =========================
            // 2. AMBIL DATA PROJECT
            // =========================
            $project = DB::table('project')
                ->where([
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $id_mapel,
                    'semester' => $semesterInt,
                    'tahun_ajaran' => $tahun_ajaran,
                ])
                ->where('nilai', '>', 0)
                ->first();

            $tpProject = $project
                ? collect([[
                    'nilai' => (float) $project->nilai,
                    'tp' => $project->tujuan_pembelajaran,
                ]])
                : collect();

            // =========================
            // 3. GABUNGKAN & GENERATE
            // =========================
            $semuaNilai = $tpSumatif->merge($tpProject);

            // 🔥 INI KUNCI UTAMANYA
            $capaian = app(\App\Http\Controllers\NilaiAkhirController::class)
                ->generateCapaianAkhir(null, $semuaNilai);

            // =========================
            // 4. SIMPAN KE NILAI AKHIR
            // =========================
            DB::table('nilai_akhir')->updateOrInsert(
                [
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $id_mapel,
                    'semester' => $semesterInt,
                    'tahun_ajaran' => $tahun_ajaran
                ],
                [
                    'id_kelas' => $siswa->id_kelas,
                    'capaian_akhir' => $capaian,
                    'updated_at' => now()
                ]
            );
        }

            if ($sumatifCount >= 1) {
                $mapelTuntas++;}

            if (($sumatifCount + $projectCount) >= 1) { 
                $mapelTuntas++; }
        }

        $isCatatanReady = DB::table('catatan')
            ->where(['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])
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
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        $siswaList = Siswa::where('id_kelas', $id_kelas)->get();
        $daftarMapel = DB::table('pembelajaran')->where('id_kelas', $id_kelas)->get();

        foreach ($siswaList as $siswa) {
            // --- PROSES 1: HITUNG & UPDATE NILAI AKHIR (TRIGGER) ---
            foreach ($daftarMapel as $mapel) {
                // Hitung rata-rata sumatif & format ke Integer
                $avgSumatif = DB::table('sumatif')
                    ->where(['id_siswa' => $siswa->id_siswa, 'id_mapel' => $mapel->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])
                    ->avg('nilai') ?? 0;

                if ($avgSumatif > 0) {
                    DB::table('nilai_akhir')->updateOrInsert(
                        [
                            'id_siswa' => $siswa->id_siswa, 
                            'id_mapel' => $mapel->id_mapel, 
                            'semester' => $semesterInt, 
                            'tahun_ajaran' => $tahun_ajaran
                        ],
                        [
                            'id_kelas' => $id_kelas,
                            'nilai_akhir' => (int)round($avgSumatif), // Simpan sebagai INT
                            'updated_at' => now()
                        ]
                    );
                }
            }
            // PROSES 2: Perbarui Status Rapor (Siap Cetak atau Tidak)
            $this->perbaruiStatusRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);

            // --- PROSES 2: CEK STATUS MONITORING (LOGIKA LAMA ANDA) ---
            // (Di sini tetap jalankan pengecekan apakah nilai_akhir & catatan sudah lengkap)
            // Jika lengkap, set status = 'Siap Cetak'
        }

        return response()->json(['message' => 'Data nilai berhasil diperbaharui dan disinkronkan.']);
    }
    /**
 * Download Rapor Satuan (Menggunakan PDF1)
 */
    public function download_satuan($id_siswa, Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';

        $data = $this->persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran);
        
        $pdf = Pdf::loadView('rapor.pdf1_template', $data)
                ->setPaper('a4', 'portrait')
                ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);

        $filename = 'Rapor_' . str_replace(' ', '_', $data['siswa']->nama_siswa) . '.pdf';
        return $pdf->download($filename); // Perintah Download
    }

    /**
     * Download Rapor Massal (Menggunakan PDF2)
     */
    public function download_massal(Request $request)
    {
        set_time_limit(0); // Mencegah timeout untuk proses banyak siswa
        
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';

        if (!$id_kelas) return redirect()->back()->with('error', 'Pilih kelas.');

        $daftarSiswa = Siswa::where('id_kelas', $id_kelas)->orderBy('nama_siswa', 'asc')->get();
        
        $zip = new ZipArchive;
        $zipFileName = 'Rapor_Kelas_' . $id_kelas . '_' . time() . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($daftarSiswa as $siswa) {
                // Ambil data menggunakan helper yang sudah ada (identik dengan PDF1)
                $data = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
                
                // Render view PDF1 (Satuan) agar layout tetap rapi & konsisten
                $pdf = \Pdf::loadView('rapor.pdf1_template', $data)
                        ->setPaper('a4', 'portrait')
                        ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
                
                // Masukkan file ke dalam ZIP
                $safeName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $siswa->nama_siswa);
                $zip->addFromString($safeName . '.pdf', $pdf->output());
            }
            $zip->close();

            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
    }

    /**
     * Download Rapor Massal dalam SATU FILE PDF (Single PDF file)
     * Menggunakan template massal dengan header/footer fixed di setiap halaman
     */
    public function download_massal_pdf(Request $request)
    {
        // Mencegah timeout jika jumlah siswa banyak
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';

        if (!$id_kelas) {
            return redirect()->back()->with('error', 'Silakan pilih kelas terlebih dahulu.');
        }

        // Ambil daftar siswa berdasarkan kelas
        $daftarSiswa = Siswa::where('id_kelas', $id_kelas)
            ->orderBy('nama_siswa', 'asc')
            ->get();

        if ($daftarSiswa->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data siswa di kelas ini.');
        }

        $allData = [];

        foreach ($daftarSiswa as $siswa) {
            // Gunakan helper persiapkanDataRapor (Eksisting) untuk mengambil data tiap siswa
            // Ini memastikan logika sinkronisasi & grouping mapel 100% sama dengan cetak satuan
            $allData[] = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
        }

        // Ambil info kelas untuk penamaan file
        $dataKelas = Kelas::find($id_kelas);
        $namaKelasFile = str_replace(' ', '_', $dataKelas->nama_kelas ?? $id_kelas);

        // Load View Massal (pdf2_massal_template)
        // Pastikan di dalam view pdf2_massal_template menggunakan @foreach($allData as $data)
        $pdf = Pdf::loadView('rapor.pdf2_massal_template', compact('allData'))
                ->setPaper('a4', 'portrait')
                ->setOption([
                    'isPhpEnabled' => true, 
                    'isRemoteEnabled' => true,
                    'margin_top' => 0,
                    'margin_bottom' => 0,
                ]);

        $filename = 'RAPOR_MASSAL_' . $namaKelasFile . '_' . time() . '.pdf';

        return $pdf->download($filename);
    }
    private function generateCapaianDariSumatif($id_siswa, $id_mapel, $semester, $tahun_ajaran)
    {
        $nilaiTp = DB::table('sumatif')
            ->where([
                'id_siswa' => $id_siswa,
                'id_mapel' => $id_mapel,
                'semester' => $semester,
                'tahun_ajaran' => $tahun_ajaran
            ])
            ->whereNotNull('nilai')
            ->select('nilai', 'tujuan_pembelajaran')
            ->orderBy('nilai', 'asc')
            ->get();

        if ($nilaiTp->isEmpty()) {
            return 'Perlu penguatan dalam hal Belum ditentukan.';
        }

        // 🔥 Ambil 2 TP saja (terendah & tertinggi)
        $tpRendah = $nilaiTp->first();
        $tpTinggi = $nilaiTp->last();

        // Kualifikasi rendah
        $narasiRendah = ($tpRendah->nilai < 78)
            ? 'Perlu peningkatan dalam hal'
            : 'Perlu penguatan dalam hal';

        // Kualifikasi tinggi
        $narasiTinggi = ($tpTinggi->nilai >= 78)
            ? 'Baik dalam hal'
            : 'Cukup dalam hal';

        // Jika cuma satu TP
        if ($tpRendah->tujuan_pembelajaran === $tpTinggi->tujuan_pembelajaran) {
            return "{$narasiRendah} {$tpRendah->tujuan_pembelajaran}.";
        }

        return "{$narasiRendah} {$tpRendah->tujuan_pembelajaran}, namun menunjukkan capaian {$narasiTinggi} {$tpTinggi->tujuan_pembelajaran}.";
    }




}