<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Pembelajaran;
use App\Models\InfoSekolah; // Pastikan Model ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Illuminate\Support\Str;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger; 
use Illuminate\Support\Facades\Storage;
use File;

class RaporController extends Controller
{
    /**
     * Helper Internal: Konversi Semester
     */
    private function getSemesterInt($semesterRaw)
    {
        if (empty($semesterRaw)) return 1;
        return (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;
    }

    /**
     * Helper Internal: Get Fase
     */
    private function getFase($tingkat) {
        $t = strtoupper(preg_replace("/[^a-zA-Z0-9]/", "", $tingkat));
        if (in_array($t, ['10', 'X'])) return 'E';
        if (in_array($t, ['11', 'XI', '12', 'XII'])) return 'F';
        return '-';
    }

    /**
     * Helper Internal: Get Data Sekolah (Safety Mode)
     * Mencegah Error jika tabel info_sekolah kosong
     */
    private function getInfoSekolahAman()
    {
        $info = InfoSekolah::first();
        
        // Jika data kosong, buat object dummy agar tidak error view undefined
        if (!$info) {
            $info = new \stdClass();
            $info->nama_sekolah = 'NAMA SEKOLAH BELUM DISETTING';
            $info->jalan = 'Alamat belum disetting';
            $info->kota_kab = 'Kota/Kab';
            $info->provinsi = 'Provinsi';
            $info->nama_kepsek = 'Kepala Sekolah Belum Diset';
            $info->nip_kepsek = '-';
        }
        
        return $info;
    }

    /**
     * Halaman Dashboard Cetak Rapor (REVISI DETAIL DATA)
     */
    /**
     * Halaman Dashboard Cetak Rapor (REVISI: DETAIL TEXT, EKSKUL, SORTING)
     */
    public function cetakIndex(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        
        // --- LOGIKA PERIODE ---
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        if ($bulanSekarang < 7) {
            $defaultTA = ($tahunSekarang - 1) . '/' . $tahunSekarang;
            $defaultSemester = 'Genap';
        } else {
            $defaultTA = $tahunSekarang . '/' . ($tahunSekarang + 1);
            $defaultSemester = 'Ganjil';
        }
        $semesterRaw = $request->semester ?? $defaultSemester;
        $tahun_ajaran = $request->tahun_ajaran ?? $defaultTA;
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        $siswaList = [];
        $kelasAktif = null;
        $stats = ['raw_count' => 0, 'final_count' => 0, 'total_siswa' => 0, 'persen_raw' => 0, 'persen_final' => 0];

        if ($id_kelas) {
            $kelasAktif = Kelas::find($id_kelas);

            // A. REFERENSI MAPEL
            $pembelajaranAll = Pembelajaran::join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->leftJoin('guru', 'pembelajaran.id_guru', '=', 'guru.id_guru')
                ->where('pembelajaran.id_kelas', $id_kelas)
                ->select('pembelajaran.id_mapel', 'mata_pelajaran.nama_mapel', 'mata_pelajaran.kategori', 'mata_pelajaran.agama_khusus', 'mata_pelajaran.is_active', 'guru.nama_guru')
                ->orderBy('mata_pelajaran.kategori', 'asc')
                ->orderBy('mata_pelajaran.urutan', 'asc')
                ->get();

            // B. AMBIL SISWA
            $siswaList = Siswa::leftJoin('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa')
                ->where('siswa.id_kelas', $id_kelas)
                ->select('siswa.*', 'detail_siswa.agama')
                ->orderBy('siswa.nama_siswa', 'asc')
                ->get();

            $stats['total_siswa'] = $siswaList->count();

            // C. EAGER LOAD DATA
            $allNilai = DB::table('nilai_akhir')->where('id_kelas', $id_kelas)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->get();
            
            // Data Raw (Untuk Cek Status Centang/Silang Saja)
            $allSumatif = DB::table('sumatif')->whereIn('id_siswa', $siswaList->pluck('id_siswa'))->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->whereNotNull('nilai')->select('id_siswa', 'id_mapel')->distinct()->get()->groupBy('id_siswa');
            $allProject = DB::table('project')->whereIn('id_siswa', $siswaList->pluck('id_siswa'))->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->whereNotNull('nilai')->select('id_siswa', 'id_mapel')->distinct()->get()->groupBy('id_siswa');
            $allCatatanRaw = DB::table('catatan')->whereIn('id_siswa', $siswaList->pluck('id_siswa'))->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->get()->keyBy('id_siswa');
            
            // Data Snapshot (SUMBER UTAMA TAMPILAN)
            $allSnapshotHeader = DB::table('nilai_akhir_rapor')->where('id_kelas', $id_kelas)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->get()->keyBy('id_siswa');

            // D. ANALISA PER SISWA
            foreach ($siswaList as $s) {
                $detailMapel = [];
                $rawLengkapCount = 0;
                $targetMapelCount = 0;
                $agamaSiswa = strtolower(trim($s->agama ?? ''));

                $mySumatif = $allSumatif->get($s->id_siswa);
                $myProject = $allProject->get($s->id_siswa);

                // --- 1. Detail Mapel ---
                foreach ($pembelajaranAll as $p) {
                    if (!$p->is_active) continue;

                    // Filter Agama
                    $syaratAgama = strtolower(trim($p->agama_khusus ?? ''));
                    if (!empty($syaratAgama)) {
                        $isMatch = false;
                        if (str_contains($syaratAgama, 'islam') && $agamaSiswa == 'islam') $isMatch = true;
                        elseif (str_contains($syaratAgama, 'kristen') && in_array($agamaSiswa, ['kristen', 'protestan'])) $isMatch = true;
                        elseif (str_contains($syaratAgama, 'katolik') && in_array($agamaSiswa, ['katolik', 'katholik'])) $isMatch = true;
                        elseif (str_contains($syaratAgama, 'hindu') && $agamaSiswa == 'hindu') $isMatch = true;
                        elseif (str_contains($syaratAgama, 'buddha') && in_array($agamaSiswa, ['buddha', 'budha'])) $isMatch = true;
                        elseif (str_contains($syaratAgama, 'khong') && str_contains($agamaSiswa, 'khong')) $isMatch = true;
                        if (!$isMatch && $syaratAgama !== $agamaSiswa) continue; 
                    }

                    $targetMapelCount++;
                    $nilaiItem = $allNilai->where('id_siswa', $s->id_siswa)->where('id_mapel', $p->id_mapel)->first();
                    
                    $existSumatif = $mySumatif ? $mySumatif->where('id_mapel', $p->id_mapel)->isNotEmpty() : false;
                    $existProject = $myProject ? $myProject->where('id_mapel', $p->id_mapel)->isNotEmpty() : false;
                    
                    $hasRawData = ($existSumatif || $existProject);
                    $hasSnap    = $nilaiItem && $nilaiItem->status_data === 'final';

                    if ($hasRawData) $rawLengkapCount++;

                    $detailMapel[] = [
                        'mapel'       => $p->nama_mapel,
                        'nilai_score' => ($nilaiItem && !is_null($nilaiItem->nilai_akhir)) ? (int)$nilaiItem->nilai_akhir : '-', 
                        'ada_nilai'   => $hasRawData,
                        'ada_rekap'   => $hasSnap, 
                    ];
                }

                // --- 2. Detail Non-Akademik (REVISI: STRICTLY FROM SNAPSHOT) ---
                $catRaw = $allCatatanRaw->get($s->id_siswa); // Hanya utk cek "Ada Raw"
                $snapHeader = $allSnapshotHeader->get($s->id_siswa); // Sumber Utama

                $hasRawNonAkademik = ($catRaw !== null);
                $statusSnapshot = $snapHeader ? $snapHeader->status_data : 'kosong';
                $hasSnapHeader = ($snapHeader !== null); // Ada row di tabel rapor (entah draft/final)

                // AMBIL DATA TEXT (HANYA DARI SNAPSHOT)
                $txtKokurikuler = $snapHeader->kokurikuler ?? '-';
                $txtCatatanWali = $snapHeader->catatan_wali_kelas ?? '-';
                $valSakit       = $snapHeader->sakit ?? '-';
                $valIjin        = $snapHeader->ijin ?? $snapHeader->izin ?? '-';
                $valAlpha       = $snapHeader->alpha ?? '-';

                // AMBIL DATA EKSKUL (HANYA DARI SNAPSHOT JSON)
                $ekskulDisplay = [];
                if ($snapHeader && !empty($snapHeader->data_ekskul)) {
                    $jsonEks = json_decode($snapHeader->data_ekskul);
                    if (is_array($jsonEks)) { 
                        foreach ($jsonEks as $e) { 
                            $ekskulDisplay[] = ($e->nama ?? '-') . " (" . ($e->predikat ?? '-') . ")"; 
                        } 
                    }
                }
                if (empty($ekskulDisplay)) $ekskulDisplay[] = "-";

                $isRawLengkap = ($rawLengkapCount >= $targetMapelCount) && $hasRawNonAkademik;
                if ($isRawLengkap) $stats['raw_count']++;
                if (in_array($statusSnapshot, ['final', 'cetak'])) $stats['final_count']++;

                $s->detail_mapel = $detailMapel;
                $s->raw_status = $isRawLengkap ? 'lengkap' : 'belum';
                $s->snapshot_status = $statusSnapshot;
                $s->tanggal_cetak = $snapHeader->tanggal_cetak ?? null;
                $s->last_update = $snapHeader->updated_at ?? null;

                $s->detail_non_akademik = [
                    'raw'   => $hasRawNonAkademik, // Badge Status Raw
                    'snap'  => $hasSnapHeader,     // Badge Status Rekap
                    
                    // Value Display (Strictly from Snapshot)
                    'sakit' => $valSakit,
                    'izin'  => $valIjin,
                    'alpha' => $valAlpha,
                    'kokurikuler_short' => Str::limit($txtKokurikuler, 30),
                    'kokurikuler_full'  => $txtKokurikuler,
                    'catatan_short'     => Str::limit($txtCatatanWali, 30),
                    'catatan_full'      => $txtCatatanWali,
                    'ekskul_list'       => $ekskulDisplay
                ];

                $s->can_print_unlock = in_array($statusSnapshot, ['final', 'cetak']);
                $s->can_generate = ($isRawLengkap && (!$snapHeader || $statusSnapshot == 'draft'));
                $s->is_draft = ($statusSnapshot == 'draft');
            }

            if ($stats['total_siswa'] > 0) {
                $stats['persen_raw'] = round(($stats['raw_count'] / $stats['total_siswa']) * 100);
                $stats['persen_final'] = round(($stats['final_count'] / $stats['total_siswa']) * 100);
            }
        }

        return view('rapor.cetak_rapor', compact('kelas', 'siswaList', 'id_kelas', 'semesterRaw', 'tahun_ajaran', 'kelasAktif', 'stats'));
    }

    /**
     * 2. GENERATE RAPOR (ADMIN OVERRIDE)
     */
    public function generateRapor(Request $request)
    {
        if (!$request->has('id_kelas')) {
             $siswa = Siswa::find($request->id_siswa);
             if($siswa) $request->merge(['id_kelas' => $siswa->id_kelas]);
        }
        return app('App\Http\Controllers\MonitoringWaliController')->generateRaporWalikelas($request);
    }

    /**
     * 3. UNLOCK RAPOR (KEMBALI KE DRAFT)
     */
    public function unlockRapor(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterRaw = $request->semester;
        $semesterInt = $this->getSemesterInt($semesterRaw);

        DB::beginTransaction();
        try {
            DB::table('nilai_akhir_rapor')
                ->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)
                ->update(['status_data' => 'draft', 'updated_at' => now()]);

            DB::table('nilai_akhir')
                ->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)
                ->update(['status_data' => 'draft']);

            DB::commit();
            return response()->json(['message' => 'Kunci rapor dibuka. Status kembali menjadi DRAFT.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal unlock: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 4. CETAK PROSES (SATUAN)
     */
    public function cetak_proses($id_siswa, Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterInt = $this->getSemesterInt($semesterRaw);
        
        $data = $this->persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran);

        if (!$data) {
            return "<script>alert('Data Rapor belum dikunci/final. Silakan Generate terlebih dahulu.');window.close();</script>";
        }

        // Update status 'cetak'
        DB::table('nilai_akhir_rapor')
            ->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)
            ->update(['status_data' => 'cetak']); 

        $pdf = Pdf::loadView('rapor.pdf1_template', $data)
                ->setPaper('a4', 'portrait')
                ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
        
        return $pdf->stream('Rapor_' . ($data['siswa']->nama_siswa ?? 'Siswa') . '.pdf');
    }

    /**
     * ==========================================================
     * 🔥 CORE LOGIC: PERSIAPAN DATA CETAK RAPOR (SNAPSHOT VERSION)
     * ==========================================================
     * Mengambil data MURNI dari tabel arsip (Snapshot) untuk konsistensi data jangka panjang.
     */
    private function persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;
        
        // 1. AMBIL HEADER RAPOR (SNAPSHOT)
        $header = DB::table('nilai_akhir_rapor')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->first();

        // Validasi: Data harus sudah difinalisasi
        if (!$header || !in_array($header->status_data, ['final', 'cetak'])) {
            return null; 
        }

        // 2. AMBIL INFO SEKOLAH (Tetap Live Data agar kop surat selalu update)
        $getSekolah = InfoSekolah::first();
        if (!$getSekolah) {
            $getSekolah = new \stdClass();
            $getSekolah->nama_sekolah = '-';
            $getSekolah->jalan = ''; $getSekolah->kelurahan = ''; $getSekolah->kecamatan = ''; 
            $getSekolah->kota_kab = ''; $getSekolah->kode_pos = ''; $getSekolah->nama_kepsek = '-'; $getSekolah->nip_kepsek = '-';
        }

        // dd($getSekolah->jalan);

        // 3. AMBIL DETAIL NILAI MAPEL (SNAPSHOT)
        // Dikelompokkan berdasarkan Kategori & Urutan
        $nilaiSnapshot = DB::table('nilai_akhir')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            // Join ke Mata Pelajaran HANYA untuk mengambil urutan sorting (jika perlu)
            // Data nama & nilai tetap dari snapshot nilai_akhir
            ->leftJoin('mata_pelajaran', 'nilai_akhir.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->select(
                'nilai_akhir.*', 
                'mata_pelajaran.urutan', 
                'mata_pelajaran.kategori' // Fallback kategori jika snapshot kosong
            )
            ->orderBy('mata_pelajaran.kategori', 'asc')
            ->orderBy('mata_pelajaran.urutan', 'asc')
            ->get();

        $mapelFinal = [];
        $daftarLabel = [1 => 'MATA PELAJARAN UMUM', 2 => 'MATA PELAJARAN KEJURUAN', 3 => 'MATA PELAJARAN PILIHAN', 4 => 'MUATAN LOKAL'];

        foreach ($nilaiSnapshot as $row) {
            // Prioritas ambil kategori dari snapshot, fallback ke master mapel
            $kategoriKey = $row->kategori_mapel_snapshot ?? $row->kategori ?? 1;
            
            $item = (object) [
                'id_mapel'    => $row->id_mapel,
                'nama_mapel'  => $row->nama_mapel_snapshot, // Nama dari Snapshot
                'nilai_akhir' => (int) $row->nilai_akhir,     // Nilai Bulat
                'capaian'     => $row->capaian_akhir,         // Deskripsi
                'nama_guru'   => $row->nama_guru_snapshot     // Guru saat itu
            ];

            $mapelFinal[$kategoriKey][] = $item;
        }

        // 4. DECODE DATA EKSKUL (JSON SNAPSHOT)
        // Format JSON di DB: [{"nama":"Pramuka","predikat":"B","keterangan":"..."}]
        $dataEkskul = [];
        if (!empty($header->data_ekskul)) { // Cek kolom 'data_ekskul' (sesuaikan jika namanya data_ekskul_snapshot)
            $decoded = json_decode($header->data_ekskul);
            if (is_array($decoded)) {
                $dataEkskul = $decoded; // Langsung pakai karena formatnya sudah object
            }
        } elseif (!empty($header->data_ekskul_snapshot)) { // Backup cek kolom lain
             $decoded = json_decode($header->data_ekskul_snapshot);
             if (is_array($decoded)) $dataEkskul = $decoded;
        }

        // 5. MOCK OBJECT SISWA (Dari Snapshot Header)
        // Agar View tidak error saat panggil $data['siswa']->nama_siswa
        $siswaMock = (object) [
            'nama_siswa' => $header->nama_siswa_snapshot,
            'nisn' => $header->nisn_snapshot,
            'nipd' => $header->nipd_snapshot,
            'kelas' => (object) [
                'nama_kelas' => $header->nama_kelas_snapshot
            ]
        ];

        // 6. RETURN DATA LENGKAP KE VIEW
        return [
            'siswa'         => $siswaMock,
            'fase'          => $header->fase ?? $header->fase_snapshot ?? '-',
            
            // Info Sekolah (Live Data)
            'sekolah'       => $getSekolah->nama_sekolah,
            'alamat_sekolah'=> $getSekolah->jalan,
            'infoSekolah'   => $getSekolah, 
            'info_sekolah'  => $getSekolah, // Redundansi untuk kompatibilitas view

            // Data Inti
            'mapelGroup'    => $mapelFinal,
            'dataEkskul'    => $dataEkskul, // Array of Objects
            
            'catatan'       => (object) [
                'sakit' => $header->sakit,
                'izin' => $header->ijin ?? $header->izin ?? 0, // Handle typo kolom
                'alpha' => $header->alpha,
                'catatan_wali_kelas' => $header->catatan_wali_kelas,
                'kokurikuler' => $header->kokurikuler ?? '-', 
                'status_kenaikan' => $header->status_kenaikan
            ],
            
            // Metadata Rapor
            'semester'      => $semesterRaw,
            'semesterInt'   => $semesterInt,
            'tahun_ajaran'  => $tahun_ajaran,
            
            // Tanda Tangan (Snapshot)
            'nama_wali'     => $header->wali_kelas_snapshot,
            'nip_wali'      => $header->nip_wali_snapshot,
            'nama_kepsek'   => $header->kepsek_snapshot,
            'nip_kepsek'    => $header->nip_kepsek_snapshot,
            'tanggal_cetak' => $header->tanggal_cetak,
        ];
    }

    /**
     * 6. DOWNLOAD MASSAL
     */
    public function download_massal_pdf(Request $request)
    {
        set_time_limit(0); ini_set('memory_limit', '512M');
        $id_kelas = $request->id_kelas; $semesterRaw = $request->semester ?? 'Ganjil'; $tahun_ajaran = $request->tahun_ajaran;
        if (!$id_kelas) return redirect()->back()->with('error', 'Pilih kelas.');
        $daftarSiswa = Siswa::where('id_kelas', $id_kelas)->orderBy('nama_siswa', 'asc')->get();
        $allData = [];
        foreach ($daftarSiswa as $siswa) {
            $data = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
            if ($data) $allData[] = $data;
        }
        if (empty($allData)) return redirect()->back()->with('error', 'Belum ada data rapor FINAL untuk kelas ini.');
        
        $pdf = Pdf::loadView('rapor.pdf2_massal_template', compact('allData'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
            
        $filename = 'RAPOR_MASSAL_' . time() . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * 7. DOWNLOAD MASSAL MERGE (PDF MERGER)
     */
    public function download_massal_merge(Request $request)
    {
        // 1. KONFIGURASI PERFORMA (Cetak massal butuh resource besar)
        set_time_limit(1200); // 20 Menit maks
        ini_set('memory_limit', '1024M'); // 1GB Ram

        $id_kelas = $request->id_kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $semesterInt = $this->getSemesterInt($semesterRaw); // Pastikan function ini ada/aksesibel

        // 2. AMBIL DATA SISWA
        $siswaList = Siswa::where('id_kelas', $id_kelas)
            ->orderBy('nama_siswa', 'asc')
            ->get();

        if ($siswaList->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa di kelas ini.');
        }

        // 3. SIAPKAN FOLDER TEMP
        $path = storage_path('app/public/temp_rapor');
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        // Inisialisasi Merger
        $merger = PDFMerger::init();
        $generatedFiles = [];
        $siswaBerhasil = 0;

        // 4. LOOPING GENERATE PDF PER SISWA
        foreach ($siswaList as $siswa) {
            
            // A. Ambil Data (Gunakan logic yang sama dengan cetak satuan)
            $data = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);

            // Validasi: Jika data rapor belum final, skip siswa ini (atau bisa dipaksa cetak)
            if (!$data) continue; 

            // B. Update Status ke 'cetak' (PENTING: Update status per siswa)
            DB::table('nilai_akhir_rapor')
                ->where('id_siswa', $siswa->id_siswa)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->update(['status_data' => 'cetak']);

            // C. Generate PDF Menggunakan Template Satuan (pdf1_template)
            // Kita menggunakan view yang SAMA PERSIS dengan cetak satuan
            $pdf = Pdf::loadView('rapor.pdf1_template', $data)
                    ->setPaper('a4', 'portrait')
                    ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
            
            // D. Simpan ke Folder Temp
            // Nama file unik agar tidak bentrok
            $fileName = 'Temp_' . $siswa->id_siswa . '_' . rand(1000,9999) . '.pdf';
            $fullPath = $path . '/' . $fileName;
            
            $pdf->save($fullPath);

            // E. Masukkan ke Antrian Merger
            $merger->addPDF($fullPath, 'all');
            $generatedFiles[] = $fullPath;
            $siswaBerhasil++;
        }

        // 5. PROSES MERGE & DOWNLOAD
        if ($siswaBerhasil > 0) {
            $finalFileName = 'Rapor_Massal_Kelas_' . ($siswaList[0]->kelas->nama_kelas ?? 'All') . '.pdf';
            $finalPath = $path . '/' . $finalFileName;
            
            // Gabungkan semua file
            $merger->merge();
            $merger->save($finalPath);

            // 6. BERSIHKAN FILE TEMP (Penting agar storage tidak penuh)
            foreach ($generatedFiles as $file) {
                if (File::exists($file)) File::delete($file);
            }

            // Download file final, lalu hapus file final tersebut setelah terkirim
            return response()->download($finalPath)->deleteFileAfterSend(true);
        } else {
            return back()->with('error', 'Gagal memproses data. Pastikan status rapor siswa sudah FINAL/Generate.');
        }
    }
}