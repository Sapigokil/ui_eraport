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
     * AJAX: Get Detail Progress Per Siswa (Untuk Modal di Halaman Cetak)
     */
    public function getDetailProgress(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $id_kelas = $request->id_kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        
        // 1. Konversi semester ke format database (Enum 1 atau 2)
        $semesterRaw = $request->semester ?? 'Ganjil';
        $semesterEnum = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        $agamaSiswa = DB::table('detail_siswa')
            ->where('id_siswa', $id_siswa)
            ->value('agama');

        $agamaSiswa = strtolower(trim($agamaSiswa));


        // 2. Ambil daftar mata pelajaran di kelas tersebut
        $pembelajaran = DB::table('pembelajaran')
        ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
        ->where('pembelajaran.id_kelas', $id_kelas)
        ->where('mata_pelajaran.is_active', 1) //menampilkan mapel active
        ->where(function ($q) use ($agamaSiswa) {
            $q->whereNull('mata_pelajaran.agama_khusus')
            ->orWhereRaw(
                'LOWER(TRIM(mata_pelajaran.agama_khusus)) = ?',
                [$agamaSiswa]
            );
        })
        ->select(
            'mata_pelajaran.id_mapel',
            'mata_pelajaran.nama_mapel',
            'mata_pelajaran.kategori'
        )
        ->orderBy('mata_pelajaran.kategori', 'asc')
        ->orderBy('mata_pelajaran.urutan', 'asc')
        ->get();


        // 3. Loop dan ambil nilai_akhir secara langsung per mapel
        $data = $pembelajaran->map(function($mp) use ($id_siswa, $semesterEnum, $tahun_ajaran) {
            
            $nilai = DB::table('nilai_akhir')
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $mp->id_mapel)
                ->where('semester', (string)$semesterEnum) 
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

        // Menggunakan persiapkanDataRapor (Versi 1)
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
        
        $allData = []; 
        
        foreach ($daftarSiswa as $siswa) {
            // Menggunakan persiapkanDataRapor2 (Versi 2 untuk massal sesuai kode asli)
            $allData[] = $this->persiapkanDataRapor2($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
        }

        $pdf = Pdf::loadView('rapor.pdf2_massal_template', compact('allData'))
                ->setPaper('a4', 'portrait')
                ->setOption([
                    'isPhpEnabled' => true, 
                    'isRemoteEnabled' => true
                ]);

        return $pdf->stream('Rapor_Massal_Kelas_'.$id_kelas.'.pdf');
    }

    /**
     * Helper Private: Logika Inti Auto-Sync & Pengambilan Data (Versi Massal)
     */
    private function persiapkanDataRapor2($id_siswa, $semesterRaw, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;
        $siswa = Siswa::with('kelas')->findOrFail($id_siswa);
        $agamaSiswa = DB::table('detail_siswa')
            ->where('id_siswa', $id_siswa)
            ->value('agama');

        $agamaSiswa = ucfirst(strtolower(trim($agamaSiswa)));

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

        // --- 2. MAPEL GROUPING (Dengan Sorting kolom urutan) ---
        $mapelFinal = [];
        $daftarUrutan = [1 => 'MATA PELAJARAN UMUM', 2 => 'MATA PELAJARAN KEJURUAN', 3 => 'MATA PELAJARAN PILIHAN', 4 => 'MUATAN LOKAL'];
        foreach ($daftarUrutan as $key => $headerLabel) {
            $kelompok = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $siswa->id_kelas)
                ->where('mata_pelajaran.kategori', $key)
                ->where('mata_pelajaran.is_active', 1) //menampilkan mapel active
                ->where(function ($q) use ($agamaSiswa) {
                    $q->whereNull('mata_pelajaran.agama_khusus')
                    ->orWhereRaw('LOWER(TRIM(mata_pelajaran.agama_khusus)) = ?', [$agamaSiswa]);
                })
            ->select(
                'mata_pelajaran.id_mapel',
                'mata_pelajaran.nama_mapel'
            )
            ->orderBy('mata_pelajaran.urutan', 'asc')
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
            'nama_kepsek'   => $getSekolah->nama_kepsek ?? 'NAMA KEPALA SEKOLAH',
            'nip_kepsek'    => $getSekolah->nip_kepsek ?? '-',
        ];
    }

    /**
     * Helper Private: Logika Inti untuk Cetak Satuan
     */
    private function persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;
        $siswa = Siswa::with('kelas')->findOrFail($id_siswa);
        $agamaSiswa = DB::table('detail_siswa')
            ->where('id_siswa', $id_siswa)
            ->value('agama');

        $agamaSiswa = ucfirst(strtolower(trim($agamaSiswa)));
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
                
                // 3. Update atau Insert
                DB::table('nilai_akhir')->updateOrInsert(
                    [
                        'id_siswa' => $id_siswa, 
                        'id_mapel' => $pb->id_mapel, 
                        'semester' => $semesterInt, 
                        'tahun_ajaran' => $tahun_ajaran
                    ],
                    [
                        'id_kelas' => $siswa->id_kelas, 
                        'nilai_akhir' => $nilaiFinal, 
                        'updated_at' => now()
                    ]
                );
            }
        }

        // --- MAPEL GROUPING (1-4) (Dengan Sorting kolom urutan) ---
        $mapelFinal = [];
        $daftarUrutan = [1 => 'MATA PELAJARAN UMUM', 2 => 'MATA PELAJARAN KEJURUAN', 3 => 'MATA PELAJARAN PILIHAN', 4 => 'MUATAN LOKAL'];
        foreach ($daftarUrutan as $key => $headerLabel) {
            $kelompok = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $siswa->id_kelas)
                ->where('mata_pelajaran.kategori', $key)
                ->where('mata_pelajaran.is_active', 1) //menampilkan mapel active
                ->where(function ($q) use ($agamaSiswa) {
                    $q->whereNull('mata_pelajaran.agama_khusus')
                    ->orWhereRaw('LOWER(TRIM(mata_pelajaran.agama_khusus)) = ?', [$agamaSiswa]);
                })
            ->select(
                'mata_pelajaran.id_mapel',
                'mata_pelajaran.nama_mapel'
            )
            ->orderBy('mata_pelajaran.urutan', 'asc')
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
            'nama_kepsek'   => $getSekolah->nama_kepsek ?? 'NAMA KEPALA SEKOLAH',
            'nip_kepsek'    => $getSekolah->nip_kepsek ?? '-',
        ];
    }

    /**
     * Mesin Sinkronisasi Progres Rapor (Status Siap Cetak)
     * PERBAIKAN: Menghapus variable $countSumatif/$sumatifCount yang error
     * MEMBERSIHKAN ERROR Undefined variable $sumatifCount
     */
    public function perbaruiStatusRapor($id_siswa, $semester, $tahun_ajaran)
    {
        // 1. Normalisasi Semester (Ganjil -> 1, Genap -> 2)
        $semesterInt = (strtoupper($semester) == 'GANJIL' || $semester == '1') ? 1 : 2;

        // 2. Ambil data siswa dan daftar mapel di kelasnya
        $siswa = Siswa::findOrFail($id_siswa);
        $agamaSiswa = DB::table('detail_siswa')
            ->where('id_siswa', $id_siswa)
            ->value('agama');

        $agamaSiswa = strtolower(trim($agamaSiswa));

        $daftarMapel = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('pembelajaran.id_kelas', $siswa->id_kelas)
            ->where('mata_pelajaran.is_active', 1) //menampilkan mapel active
            ->where(function ($q) use ($agamaSiswa) {
                $q->whereNull('mata_pelajaran.agama_khusus')
                ->orWhereRaw(
                    'LOWER(TRIM(mata_pelajaran.agama_khusus)) = ?',
                    [$agamaSiswa]
                );
            })
            ->pluck('mata_pelajaran.id_mapel');


        $totalMapelSeharusnya = $daftarMapel->count();
        $mapelTuntas = 0;

        // 3. Cek Kelengkapan Nilai Akhir per Mapel
        // Kita cek ke tabel nilai_akhir karena fungsi sinkronkanKelas sudah mengisi tabel tersebut
        foreach ($daftarMapel as $id_mapel) {
            $adaNilai = DB::table('nilai_akhir')
                ->where([
                    'id_siswa' => $id_siswa,
                    'id_mapel' => $id_mapel,
                    'semester' => $semesterInt,
                    'tahun_ajaran' => (string)$tahun_ajaran
                ])
                ->where('nilai_akhir', '>', 0)
                ->exists();

            if ($adaNilai) {
                $mapelTuntas++;
            }
        }

        // 4. Cek apakah Wali Kelas sudah isi Catatan (tidak boleh kosong/null)
        $isCatatanReady = DB::table('catatan')
            ->where([
                'id_siswa' => $id_siswa,
                'semester' => $semesterInt,
                'tahun_ajaran' => (string)$tahun_ajaran
            ])
            ->whereNotNull('catatan_wali_kelas')
            ->whereRaw("TRIM(catatan_wali_kelas) != ''")
            ->exists();

        // 5. Tentukan Status Akhir
        $statusAkhir = ($mapelTuntas >= $totalMapelSeharusnya && $isCatatanReady) 
                        ? 'Siap Cetak' 
                        : 'Belum Lengkap';

        // 6. Simpan/Update ke tabel status_rapor
        return StatusRapor::updateOrCreate(
            [
                'id_siswa' => $id_siswa,
                'semester' => $semesterInt,
                'tahun_ajaran' => (string)$tahun_ajaran,
            ],
            [
                'id_kelas' => $siswa->id_kelas,
                'total_mapel_seharusnya' => $totalMapelSeharusnya,
                'mapel_tuntas_input' => $mapelTuntas,
                'is_catatan_wali_ready' => $isCatatanReady ? 1 : 0,
                'status_akhir' => $statusAkhir,
            ]
        );
    }

    /**
     * Fungsi Sinkronisasi Utama yang dipanggil tombol di View
     */
    public function sinkronkanKelas(Request $request)
    {
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        if (!$id_kelas) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 400);
        }

        $siswaList = Siswa::where('id_kelas', $id_kelas)->get();
        $daftarMapel = DB::table('pembelajaran')            
        // ->where('id_kelas', $id_kelas)->get();
        ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
        ->where('mata_pelajaran.is_active', 1)
        ->where('pembelajaran.id_kelas', $id_kelas)
        ->get();


        foreach ($siswaList as $siswa) {
            // A. Update Nilai Akhir dari Rata-rata Sumatif
            foreach ($daftarMapel as $mapel) {
                $avgSumatif = DB::table('sumatif')
                    ->where([
                        'id_siswa' => $siswa->id_siswa, 
                        'id_mapel' => $mapel->id_mapel, 
                        'semester' => $semesterInt, 
                        'tahun_ajaran' => $tahun_ajaran
                    ])
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
                            'nilai_akhir' => (int)round($avgSumatif),
                            'updated_at' => now()
                        ]
                    );
                }
            }
            
            // B. Perbarui Status Monitoring (Lengkap/Belum)
            $this->perbaruiStatusRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
        }

        return response()->json(['message' => 'Sinkronisasi Nilai (Sumatif & Project) serta Status Rapor berhasil.']);
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
        return $pdf->download($filename);
    }

    /**
     * Download Rapor Massal (Menggunakan PDF2)
     */
    public function download_massal(Request $request)
    {
        set_time_limit(0); 
        
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
                // Menggunakan persiapkanDataRapor (Versi Satuan)
                $data = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
                
                $pdf = \Pdf::loadView('rapor.pdf1_template', $data)
                        ->setPaper('a4', 'portrait')
                        ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
                
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
     */
    public function download_massal_pdf(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';

        if (!$id_kelas) {
            return redirect()->back()->with('error', 'Silakan pilih kelas terlebih dahulu.');
        }

        $daftarSiswa = Siswa::where('id_kelas', $id_kelas)
            ->orderBy('nama_siswa', 'asc')
            ->get();

        if ($daftarSiswa->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data siswa di kelas ini.');
        }

        $allData = [];

        foreach ($daftarSiswa as $siswa) {
            // Menggunakan persiapkanDataRapor (Versi Satuan) agar konsisten
            $allData[] = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
        }

        $dataKelas = Kelas::find($id_kelas);
        $namaKelasFile = str_replace(' ', '_', $dataKelas->nama_kelas ?? $id_kelas);

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

        // Ambil 2 TP saja (terendah & tertinggi)
        $tpRendah = $nilaiTp->first();
        $tpTinggi = $nilaiTp->last();

        $narasiRendah = ($tpRendah->nilai < 78)
            ? 'Perlu peningkatan dalam hal'
            : 'Perlu penguatan dalam hal';

        $narasiTinggi = ($tpTinggi->nilai >= 78)
            ? 'Baik dalam hal'
            : 'Cukup dalam hal';

        if ($tpRendah->tujuan_pembelajaran === $tpTinggi->tujuan_pembelajaran) {
            return "{$narasiRendah} {$tpRendah->tujuan_pembelajaran}.";
        }

        return "{$narasiRendah} {$tpRendah->tujuan_pembelajaran}, namun menunjukkan capaian {$narasiTinggi} {$tpTinggi->tujuan_pembelajaran}.";
    }
}