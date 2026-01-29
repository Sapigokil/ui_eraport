<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Pembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Illuminate\Support\Str;

class RaporController extends Controller
{
    /**
     * =========================================================================
     * 1. HALAMAN UTAMA (DASHBOARD CETAK)
     * =========================================================================
     * Menampilkan status kelengkapan data (Raw vs Snapshot) secara detail.
     */
    public function cetakIndex(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        
        // --- 1. LOGIKA PERIODE (Fixed) ---
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');

        if ($bulanSekarang < 7) {
            // Jan - Juni = Semester Genap, Tahun Ajaran Mundur 1 (Misal 2026 -> 2025/2026)
            $defaultTA = ($tahunSekarang - 1) . '/' . $tahunSekarang;
            $defaultSemester = 'Genap';
        } else {
            // Juli - Des = Semester Ganjil, Tahun Ajaran Berjalan (Misal 2026 -> 2026/2027)
            $defaultTA = $tahunSekarang . '/' . ($tahunSekarang + 1);
            $defaultSemester = 'Ganjil';
        }

        $semesterRaw = $request->semester ?? $defaultSemester;
        $tahun_ajaran = $request->tahun_ajaran ?? $defaultTA;
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        $siswaList = [];
        $kelasAktif = null;
        $stats = [
            'raw_count' => 0,
            'final_count' => 0,
            'total_siswa' => 0,
            'persen_raw' => 0,
            'persen_final' => 0
        ];

        if ($id_kelas) {
            $kelasAktif = Kelas::find($id_kelas);

            // A. Ambil Referensi Mapel & Guru
            $pembelajaranAll = Pembelajaran::with(['mapel', 'guru'])
                ->where('id_kelas', $id_kelas)
                ->get();

            // B. Ambil Siswa
            $siswaList = Siswa::leftJoin('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa')
                ->where('siswa.id_kelas', $id_kelas)
                ->select('siswa.*', 'detail_siswa.agama')
                ->orderBy('siswa.nama_siswa', 'asc')
                ->get();

            $stats['total_siswa'] = $siswaList->count();

            // C. Eager Load Data Nilai (Optimasi Query)
            $allNilai = DB::table('nilai_akhir')
                ->where('id_kelas', $id_kelas)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->get();

            $allCatatanRaw = DB::table('catatan')
                ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->get()
                ->keyBy('id_siswa');

            $allSnapshotHeader = DB::table('nilai_akhir_rapor')
                ->where('id_kelas', $id_kelas)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->get()
                ->keyBy('id_siswa');

            // D. Analisa Per Siswa
            foreach ($siswaList as $s) {
                $detailMapel = [];
                $rawLengkapCount = 0;
                $targetMapelCount = 0;
                $agamaSiswa = strtolower(trim($s->agama ?? ''));

                // --- Analisa Mapel (Filter Agama) ---
                foreach ($pembelajaranAll as $p) {
                    if (!$p->mapel || !$p->mapel->is_active) continue;

                    // Logic Filter Agama Dinamis
                    $syaratAgama = strtolower(trim($p->mapel->agama_khusus ?? ''));
                    if (!empty($syaratAgama)) {
                        $isMatch = false;
                        if (str_contains($syaratAgama, 'islam') && $agamaSiswa == 'islam') $isMatch = true;
                        elseif (str_contains($syaratAgama, 'kristen') && in_array($agamaSiswa, ['kristen', 'protestan'])) $isMatch = true;
                        elseif (str_contains($syaratAgama, 'katolik') && in_array($agamaSiswa, ['katolik', 'katholik'])) $isMatch = true;
                        elseif (str_contains($syaratAgama, 'hindu') && $agamaSiswa == 'hindu') $isMatch = true;
                        elseif (str_contains($syaratAgama, 'buddha') && in_array($agamaSiswa, ['buddha', 'budha'])) $isMatch = true;
                        elseif (str_contains($syaratAgama, 'khong') && str_contains($agamaSiswa, 'khong')) $isMatch = true;
                        
                        if (!$isMatch && $syaratAgama !== $agamaSiswa) continue; // Skip mapel ini jika beda agama
                    }

                    $targetMapelCount++;

                    // Cek Status Nilai Mapel Ini
                    $nilaiItem = $allNilai->where('id_siswa', $s->id_siswa)->where('id_mapel', $p->id_mapel)->first();
                    
                    $hasRaw = $nilaiItem && !is_null($nilaiItem->nilai_akhir); // Guru sudah input
                    $hasSnap = $nilaiItem && $nilaiItem->status_data === 'final'; // Sudah dikunci wali kelas

                    if ($hasRaw) $rawLengkapCount++;

                    $detailMapel[] = [
                        'mapel' => $p->mapel->nama_mapel,
                        'guru'  => $p->guru->nama_guru ?? '-',
                        'raw'   => $hasRaw,
                        'snap'  => $hasSnap
                    ];
                }

                // --- Analisa Non-Akademik ---
                $catRaw = $allCatatanRaw->get($s->id_siswa);
                $snapHeader = $allSnapshotHeader->get($s->id_siswa);

                $hasRawNonAkademik = ($catRaw !== null);
                $statusSnapshot = $snapHeader ? $snapHeader->status_data : 'kosong'; // kosong, draft, final, cetak
                $hasSnapHeader = in_array($statusSnapshot, ['final', 'cetak']);

                // --- Kesimpulan Status ---
                $isRawLengkap = ($rawLengkapCount >= $targetMapelCount) && $hasRawNonAkademik;

                // Update Statistik Global
                if ($isRawLengkap) $stats['raw_count']++;
                if ($hasSnapHeader) $stats['final_count']++;

                // Assign Data ke Object Siswa
                $s->detail_mapel = $detailMapel;
                $s->raw_status = $isRawLengkap ? 'lengkap' : 'belum';
                $s->snapshot_status = $statusSnapshot;
                $s->tanggal_cetak = $snapHeader->tanggal_cetak ?? null;
                $s->last_update = $snapHeader->updated_at ?? null;

                $s->detail_non_akademik = [
                    'raw'   => $hasRawNonAkademik,
                    'snap'  => $hasSnapHeader,
                    'sakit' => $hasSnapHeader ? $snapHeader->sakit : ($catRaw->sakit ?? '-'),
                    'izin'  => $hasSnapHeader ? $snapHeader->ijin : ($catRaw->ijin ?? '-'),
                    'alpha' => $hasSnapHeader ? $snapHeader->alpha : ($catRaw->alpha ?? '-'),
                ];

                // Logic Tombol Aksi
                $s->can_print_unlock = in_array($statusSnapshot, ['final', 'cetak']);
                $s->can_generate = ($isRawLengkap && (!$snapHeader || $statusSnapshot == 'draft'));
                $s->is_draft = ($statusSnapshot == 'draft');
            }

            // Hitung Persentase Statistik
            if ($stats['total_siswa'] > 0) {
                $stats['persen_raw'] = round(($stats['raw_count'] / $stats['total_siswa']) * 100);
                $stats['persen_final'] = round(($stats['final_count'] / $stats['total_siswa']) * 100);
            }
        }

        return view('rapor.cetak_rapor', compact('kelas', 'siswaList', 'id_kelas', 'semesterRaw', 'tahun_ajaran', 'kelasAktif', 'stats'));
    }

    /**
     * =========================================================================
     * 2. FITUR GENERATE & LOCK (ADMIN OVERRIDE)
     * =========================================================================
     */
    public function generateRapor(Request $request)
    {
        // Pastikan id_kelas ada (dikirim via AJAX)
        if (!$request->has('id_kelas')) {
             $siswa = Siswa::find($request->id_siswa);
             if($siswa) $request->merge(['id_kelas' => $siswa->id_kelas]);
        }
        
        // Meminjam logika MonitoringWaliController agar konsisten dan tidak duplikasi kode
        // Pastikan MonitoringWaliController sudah ada dan benar logikanya
        return app('App\Http\Controllers\MonitoringWaliController')->generateRaporWalikelas($request);
    }

    /**
     * =========================================================================
     * 3. FITUR UNLOCK RAPOR (KEMBALI KE DRAFT)
     * =========================================================================
     */
    public function unlockRapor(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterRaw = $request->semester;
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        DB::beginTransaction();
        try {
            // 1. Ubah Header Rapor jadi Draft
            DB::table('nilai_akhir_rapor')
                ->where('id_siswa', $id_siswa)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->update([
                    'status_data' => 'draft',
                    'updated_at' => now()
                ]);

            // 2. Ubah Nilai Mapel jadi Draft (Agar bisa direvisi/generate ulang)
            DB::table('nilai_akhir')
                ->where('id_siswa', $id_siswa)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->update([
                    'status_data' => 'draft'
                ]);

            DB::commit();
            return response()->json(['message' => 'Kunci rapor dibuka. Status kembali menjadi DRAFT.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal unlock: ' . $e->getMessage()], 500);
        }
    }

    /**
     * =========================================================================
     * 4. PROSES CETAK (SATUAN)
     * =========================================================================
     */
    public function cetak_proses($id_siswa, Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;
        
        // 1. Ambil Data Snapshot
        $data = $this->persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran);

        if (!$data) {
            return "<script>alert('Data Rapor belum dikunci/final. Silakan Generate terlebih dahulu.');window.close();</script>";
        }

        // 2. Update Status menjadi 'cetak' (untuk tracking)
        DB::table('nilai_akhir_rapor')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->update(['status_data' => 'cetak']); 

        // 3. Render PDF
        $pdf = Pdf::loadView('rapor.pdf1_template', $data)
                ->setPaper('a4', 'portrait')
                ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
        
        return $pdf->stream('Rapor_' . ($data['siswa']->nama_siswa ?? 'Siswa') . '.pdf');
    }

    /**
     * =========================================================================
     * 5. HELPER: BACA DATA SNAPSHOT
     * =========================================================================
     * Membaca murni dari tabel 'nilai_akhir' dan 'nilai_akhir_rapor'
     */
    private function persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;
        
        // 1. Baca Header
        $header = DB::table('nilai_akhir_rapor')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->first();

        // Validasi Status
        if (!$header || !in_array($header->status_data, ['final', 'cetak'])) {
            return null; 
        }

        // 2. Baca Detail Nilai
        $nilai = DB::table('nilai_akhir')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->orderBy('id_mapel', 'asc') // Bisa disesuaikan dengan urutan mapel snapshot
            ->get();
            
        // Grouping Mapel
        $mapelFinal = [];
        foreach ($nilai as $row) {
            $groupKey = $row->kategori_mapel_snapshot ?? '1';
            
            $item = (object) [
                'nama_mapel'  => $row->nama_mapel_snapshot,
                'nilai_akhir' => (int) $row->nilai_akhir,
                'capaian'     => $row->capaian_akhir,
                'nama_guru'   => $row->nama_guru_snapshot
            ];
            $mapelFinal[$groupKey][] = $item;
        }

        // 3. Decode Ekskul JSON
        $dataEkskul = [];
        // Support nama kolom lama/baru sesuai migrasi Anda
        $jsonKolom = $header->data_ekskul ?? $header->data_ekskul_snapshot ?? null;
        if (!empty($jsonKolom)) {
            $dataEkskul = json_decode($jsonKolom);
        }

        // 4. Mock Object Siswa (dari Snapshot)
        $siswaMock = (object) [
            'nama_siswa' => $header->nama_siswa_snapshot,
            'nisn' => $header->nisn_snapshot,
            'nipd' => $header->nipd_snapshot,
            'kelas' => (object) [
                'nama_kelas' => $header->nama_kelas_snapshot
            ]
        ];

        return [
            'siswa'         => $siswaMock,
            'fase'          => $header->fase ?? '-', // Pastikan kolom 'fase' atau 'fase_snapshot' ada
            'sekolah'       => 'SMKN 1 SALATIGA',
            'mapelGroup'    => $mapelFinal,
            'dataEkskul'    => $dataEkskul,
            
            'catatan'       => (object) [
                'sakit' => $header->sakit,
                'izin' => $header->ijin, // Gunakan 'ijin' sesuai migrasi
                'alpha' => $header->alpha,
                'catatan_wali_kelas' => $header->catatan_wali_kelas,
                'kokurikuler' => $header->kokurikuler ?? '-', 
                'status_kenaikan' => $header->status_kenaikan
            ],
            
            'semester'      => $semesterRaw,
            'tahun_ajaran'  => $tahun_ajaran,
            'nama_wali'     => $header->wali_kelas_snapshot,
            'nip_wali'      => $header->nip_wali_snapshot,
            'nama_kepsek'   => $header->kepsek_snapshot,
            'nip_kepsek'    => $header->nip_kepsek_snapshot,
            'tanggal_cetak' => $header->tanggal_cetak,
        ];
    }

    /**
     * =========================================================================
     * 6. DOWNLOAD MASSAL (PDF GABUNGAN)
     * =========================================================================
     */
    public function download_massal_pdf(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran;

        if (!$id_kelas) return redirect()->back()->with('error', 'Pilih kelas.');

        $daftarSiswa = Siswa::where('id_kelas', $id_kelas)->orderBy('nama_siswa', 'asc')->get();
        $allData = [];

        foreach ($daftarSiswa as $siswa) {
            $data = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
            if ($data) {
                $allData[] = $data;
            }
        }

        if (empty($allData)) return redirect()->back()->with('error', 'Belum ada data rapor FINAL untuk kelas ini.');

        $pdf = Pdf::loadView('rapor.pdf2_massal_template', compact('allData'))
                ->setPaper('a4', 'portrait')
                ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true, 'margin_top' => 0, 'margin_bottom' => 0]);

        $filename = 'RAPOR_MASSAL_' . time() . '.pdf';
        return $pdf->download($filename);
    }
}