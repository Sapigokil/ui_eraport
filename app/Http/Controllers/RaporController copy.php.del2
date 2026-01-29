<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\InfoSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

class RaporController extends Controller
{
    /**
     * Halaman Dashboard Cetak Rapor
     */
    public function cetakIndex(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? date('Y') . '/' . (date('Y') + 1);
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        $siswaList = [];

        if ($id_kelas) {
            $siswaList = Siswa::with('kelas')
                ->where('id_kelas', $id_kelas)
                ->orderBy('nama_siswa', 'asc')
                ->get();

            // Cek Status Snapshot di tabel nilai_akhir_rapor
            foreach ($siswaList as $s) {
                $headerRapor = DB::table('nilai_akhir_rapor')
                    ->where('id_siswa', $s->id_siswa)
                    ->where('semester', $semesterInt)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->select('status_data', 'updated_at')
                    ->first();

                // Status untuk UI: Apakah sudah Final/Terkunci?
                $s->is_locked = ($headerRapor && $headerRapor->status_data === 'final');
                $s->last_update = $headerRapor->updated_at ?? null;
            }
        }

        return view('rapor.cetak_rapor', compact('kelas', 'siswaList', 'id_kelas', 'semesterRaw', 'tahun_ajaran'));
    }

    /**
     * AJAX: Detail Progress Mapel (Untuk Pop-up Cek Kelengkapan)
     */
    public function getDetailProgress(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $id_kelas = $request->id_kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        // 1. Ambil Agama Siswa (Untuk filter mapel agama)
        $agamaSiswaRaw = DB::table('detail_siswa')->where('id_siswa', $id_siswa)->value('agama');
        $agamaSiswa = strtolower(trim($agamaSiswaRaw));

        // 2. Ambil List Mapel Wajib Kelas Ini (Live Data)
        $pembelajaran = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('pembelajaran.id_kelas', $id_kelas)
            ->where('mata_pelajaran.is_active', 1)
            ->where(function ($q) use ($agamaSiswa) {
                $q->whereNull('mata_pelajaran.agama_khusus')
                  ->orWhereRaw('LOWER(TRIM(mata_pelajaran.agama_khusus)) = ?', [$agamaSiswa]);
            })
            ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel', 'mata_pelajaran.kategori', 'mata_pelajaran.kode_mapel')
            ->orderBy('mata_pelajaran.urutan', 'asc')
            ->get();

        // 3. Cek apakah sudah ada di tabel nilai_akhir
        $nilaiData = DB::table('nilai_akhir')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->get()
            ->keyBy('id_mapel');

        $result = $pembelajaran->map(function($mp) use ($nilaiData) {
            $data = $nilaiData->get($mp->id_mapel);
            // Dianggap Lengkap jika row ada dan nilai_akhir tidak null
            $isLengkap = $data && $data->nilai_akhir !== null; 

            return [
                'nama_mapel' => $mp->nama_mapel,
                'kode'       => $mp->kode_mapel,
                'kategori'   => match((int)$mp->kategori) {
                    1 => 'Umum', 2 => 'Kejuruan', 3 => 'Pilihan', 4 => 'Mulok', default => '-'
                },
                'is_lengkap' => $isLengkap,
                'nilai'      => $isLengkap ? (int)$data->nilai_akhir : '-',
                'status_text'=> $isLengkap ? 'Sudah Masuk' : 'Belum Input'
            ];
        });

        return response()->json(['data' => $result]);
    }

    /**
     * GENERATE & KUNCI RAPOR (FULL SNAPSHOT VERSION)
     * Menyimpan SEMUA data teks ke dalam tabel agar mandiri 100%.
     */
    public function generateRapor(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterRaw = $request->semester;
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        DB::beginTransaction();
        try {
            // 1. SIAPKAN DATA MENTAH (LIVE DATA SAAT INI)
            $siswa = Siswa::with('kelas')->findOrFail($id_siswa);
            $kelas = Kelas::find($siswa->id_kelas);
            $sekolah = InfoSekolah::first();
            $wali = DB::table('guru')->where('nama_guru', $kelas->wali_kelas)->first(); 

            // Ambil Catatan & Absensi (Inputan Wali Kelas)
            $catatanData = DB::table('catatan')
                ->where('id_siswa', $id_siswa)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->first();

            // 2. SIAPKAN JSON EKSKUL (SNAPSHOT NAMA & NILAI)
            // Kita olah sekarang agar nanti tinggal baca JSON saja tanpa query master ekskul
            $ekskulSnapshot = [];
            if ($catatanData && !empty($catatanData->ekskul)) {
                $ids = explode(',', $catatanData->ekskul);
                $predikats = explode(',', $catatanData->predikat ?? '');
                $descs = explode('|', $catatanData->keterangan ?? '');

                foreach($ids as $idx => $idEkskul) {
                    if(trim($idEkskul) == "") continue;
                    
                    // Ambil Nama Ekskul dari Master SAAT INI
                    $namaEkskul = DB::table('ekskul')->where('id_ekskul', trim($idEkskul))->value('nama_ekskul');
                    
                    $ekskulSnapshot[] = [
                        'nama' => $namaEkskul ?? 'Ekstrakurikuler Terhapus',
                        'predikat' => $predikats[$idx] ?? '-',
                        'keterangan' => $descs[$idx] ?? '-'
                    ];
                }
            }
            $jsonEkskul = json_encode($ekskulSnapshot); 

            // 3. SIMPAN HEADER SNAPSHOT (nilai_akhir_rapor)
            DB::table('nilai_akhir_rapor')->updateOrInsert(
                [
                    'id_siswa' => $id_siswa,
                    'semester' => $semesterInt,
                    'tahun_ajaran' => $tahun_ajaran
                ],
                [
                    'id_kelas' => $siswa->id_kelas,
                    
                    // --- SNAPSHOT IDENTITAS SISWA (MANDIRI) ---
                    'nama_siswa_snapshot' => $siswa->nama_siswa, 
                    'nisn_snapshot' => $siswa->nisn,
                    'nipd_snapshot' => $siswa->nipd,
                    
                    // --- SNAPSHOT KELAS & SEKOLAH ---
                    'nama_kelas_snapshot' => $kelas->nama_kelas,
                    'tingkat' => $kelas->tingkat,
                    'fase_snapshot' => $this->getFase($kelas->tingkat), // Simpan huruf E/F
                    
                    'wali_kelas_snapshot' => $kelas->wali_kelas,
                    'nip_wali_snapshot' => $wali->nip ?? '-',
                    
                    'kepsek_snapshot' => $sekolah->nama_kepsek ?? '-',
                    'nip_kepsek_snapshot' => $sekolah->nip_kepsek ?? '-',
                    
                    'tanggal_cetak' => now(), 
                    
                    // --- SNAPSHOT NILAI NON-MAPEL ---
                    'sakit' => $catatanData->sakit ?? 0,
                    'izin' => $catatanData->izin ?? 0,
                    'alpha' => $catatanData->alpha ?? 0,
                    'dispensasi' => $catatanData->dispensasi ?? 0,
                    
                    'catatan_akademik' => $catatanData->catatan_akademik ?? null,
                    'catatan_wali_kelas' => $catatanData->catatan_wali_kelas ?? '-',
                    'catatan_p5' => $catatanData->catatan_p5 ?? null,
                    
                    'data_ekskul_snapshot' => $jsonEkskul, // SIMPAN JSON LENGKAP
                    
                    'status_kenaikan' => $catatanData->status_kenaikan ?? 'naik', 
                    
                    // --- KUNCI STATUS ---
                    'status_data' => 'final',
                    'updated_at' => now(),
                    'created_at' => now() // Perlu dihandle jika insert manual, tapi updateOrInsert biasanya aman
                ]
            );

            // 4. SIMPAN DETAIL SNAPSHOT (nilai_akhir)
            // Mengisi kolom snapshot mapel/guru
            $nilaiAkhirItems = DB::table('nilai_akhir')
                ->where('id_siswa', $id_siswa)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->get();

            foreach ($nilaiAkhirItems as $item) {
                // Ambil data master saat ini
                $mapelMaster = DB::table('mata_pelajaran')->where('id_mapel', $item->id_mapel)->first();
                
                $pembelajaran = DB::table('pembelajaran')
                    ->where('id_kelas', $siswa->id_kelas)
                    ->where('id_mapel', $item->id_mapel)
                    ->first();
                
                $guruPengampu = $pembelajaran ? DB::table('guru')->where('id_guru', $pembelajaran->id_guru)->first() : null;

                // Kunci data mapel & guru ke dalam row nilai_akhir
                DB::table('nilai_akhir')
                    ->where('id_nilai_akhir', $item->id_nilai_akhir)
                    ->update([
                        // Identitas Kelas saat nilai dibuat
                        'nama_kelas_snapshot' => $kelas->nama_kelas,
                        'tingkat' => $kelas->tingkat,
                        'fase' => $this->getFase($kelas->tingkat),
                        
                        // Identitas Mapel (TEXT MATI)
                        'nama_mapel_snapshot' => $mapelMaster->nama_mapel ?? $item->nama_mapel_snapshot ?? 'Mapel Terhapus',
                        'kategori_mapel_snapshot' => $mapelMaster->kategori ?? '1',
                        'kode_mapel_snapshot' => $mapelMaster->kode_mapel ?? '-',
                        
                        // Identitas Guru (TEXT MATI)
                        'nama_guru_snapshot' => $guruPengampu->nama_guru ?? 'Belum diset',
                        
                        // Kunci Status
                        'status_data' => 'final'
                    ]);
            }

            DB::commit();
            return response()->json(['message' => 'Rapor berhasil dikunci. Data aman untuk arsip jangka panjang.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GENERATE RAPOR WALI KELAS (BATCH / MASSAL)
     * Fungsi: Mengunci data rapor (Header, Absen, Ekskul, Catatan) untuk SELURUH SISWA dalam satu kelas.
     * Dipanggil dari: Halaman Monitoring Wali Kelas.
     */
    public function generateRaporWalikelas(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'id_kelas' => 'required',
            'semester' => 'required',
            'tahun_ajaran' => 'required',
        ]);

        $id_kelas = $request->id_kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        
        // Konversi Semester
        $semesterRaw = $request->semester;
        $semesterInt = (strtoupper($semesterRaw) == 'GENAP' || $semesterRaw == '2') ? 2 : 1;

        // 2. Ambil Data Kelas & Guru (Untuk Snapshot Header)
        $kelas = Kelas::find($id_kelas);
        if (!$kelas) return back()->with('error', 'Kelas tidak ditemukan');

        // Logic Snapshot Wali Kelas (Cari via ID Guru dulu, fallback ke String Nama)
        $guruWali = DB::table('guru')->where('id_guru', $kelas->id_guru)->first();
        if (!$guruWali) {
             $guruWali = DB::table('guru')->where('nama_guru', $kelas->wali_kelas)->first();
        }
        $namaWaliSnapshot = $guruWali->nama_guru ?? $kelas->wali_kelas ?? '-';
        $nipWaliSnapshot  = $guruWali->nip ?? '-';

        // Logic Snapshot Kepsek (Ambil dari Info Sekolah)
        $sekolah = DB::table('info_sekolah')->first(); // Asumsi nama tabel info_sekolah
        $kepsekName = $sekolah->nama_kepsek ?? '-';
        $kepsekNip  = $sekolah->nip_kepsek ?? '-';

        // 3. Ambil Seluruh Siswa di Kelas Tersebut
        $siswaList = Siswa::where('id_kelas', $id_kelas)->get();

        if ($siswaList->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa di kelas ini.');
        }

        DB::beginTransaction();
        try {
            $countUpdated = 0;

            foreach ($siswaList as $siswa) {
                
                // A. Ambil Data Mentah dari Inputan Wali Kelas (Tabel 'catatan')
                $catatanData = DB::table('catatan')
                    ->where('id_siswa', $siswa->id_siswa)
                    ->where('semester', $semesterInt)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->first();

                // B. PROSES SNAPSHOT EKSKUL (Convert ID -> JSON Nama & Nilai)
                $ekskulSnapshot = [];
                if ($catatanData && !empty($catatanData->ekskul)) {
                    $ids = explode(',', $catatanData->ekskul);
                    $predikats = explode(',', $catatanData->predikat ?? '');
                    $descs = explode('|', $catatanData->keterangan ?? ''); // Separator pipe (|)

                    foreach($ids as $idx => $idEkskul) {
                        if(trim($idEkskul) == "") continue;
                        
                        // Lookup Nama Ekskul
                        $namaEkskul = DB::table('ekskul')->where('id_ekskul', trim($idEkskul))->value('nama_ekskul');
                        
                        $ekskulSnapshot[] = [
                            'nama' => $namaEkskul ?? 'Ekskul Tidak Ditemukan',
                            'predikat' => $predikats[$idx] ?? '-',
                            'keterangan' => $descs[$idx] ?? '-'
                        ];
                    }
                }
                $jsonEkskul = json_encode($ekskulSnapshot);

                // Default Value jika catatan kosong
                $sakit = $catatanData->sakit ?? 0;
                $izin  = $catatanData->ijin ?? 0; 
                $alpha = $catatanData->alpha ?? 0;
                $catatan_text = $catatanData->catatan_wali_kelas ?? '-';
                $status_naik  = $catatanData->status_kenaikan ?? null;

                // C. SIMPAN KE TABEL 'nilai_akhir_rapor' (HEADER FINAL)
                DB::table('nilai_akhir_rapor')->updateOrInsert(
                    [
                        'id_siswa' => $siswa->id_siswa,
                        'id_kelas' => $id_kelas,
                        'semester' => $semesterInt,
                        'tahun_ajaran' => $tahun_ajaran
                    ],
                    [
                        // Snapshot Identitas
                        'nama_siswa_snapshot' => $siswa->nama_siswa,
                        'nisn_snapshot' => $siswa->nisn,
                        'nipd_snapshot' => $siswa->nipd ?? '-',
                        
                        'nama_kelas_snapshot' => $kelas->nama_kelas,
                        'tingkat' => $kelas->tingkat,
                        'fase_snapshot' => $kelas->fase ?? 'E', // Asumsi ada accessor fase di Model Kelas
                        
                        'nama_wali' => $namaWaliSnapshot,
                        'nip_wali' => $nipWaliSnapshot,
                        
                        'kepsek_snapshot' => $kepsekName,
                        'nip_kepsek_snapshot' => $kepsekNip,
                        
                        // Snapshot Nilai Non-Mapel
                        'sakit' => $sakit,
                        'izin'  => $izin,
                        'alpha' => $alpha,
                        'catatan_wali' => $catatan_text,
                        'ekskul_data'  => $jsonEkskul, // JSON Final
                        'status_naik'  => $status_naik,
                        
                        // Metadata
                        'tanggal_rapor' => now(),
                        'status_data'   => 'final',
                        'updated_at'    => now()
                    ]
                );

                // D. (Opsional) Kunci juga tabel nilai_akhir (Mapel) menjadi 'final'
                DB::table('nilai_akhir')
                    ->where('id_siswa', $siswa->id_siswa)
                    ->where('semester', $semesterInt)
                    ->where('tahun_ajaran', $tahun_ajaran)
                    ->update(['status_data' => 'final']);
                
                $countUpdated++;
            }

            DB::commit();
            return back()->with('success', "Berhasil memfinalisasi rapor untuk $countUpdated siswa. Data siap dicetak oleh Admin.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal generate rapor massal: ' . $e->getMessage());
        }
    }

    /**
     * UNLOCK RAPOR (REVISI)
     * Mengembalikan status menjadi 'draft'
     */
    public function unlockRapor(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterRaw = $request->semester;
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        DB::beginTransaction();
        try {
            DB::table('nilai_akhir_rapor')
                ->where('id_siswa', $id_siswa)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->update(['status_data' => 'draft', 'updated_at' => now()]);

            DB::table('nilai_akhir')
                ->where('id_siswa', $id_siswa)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->update(['status_data' => 'draft']);

            DB::commit();
            return response()->json(['message' => 'Kunci rapor dibuka. Silakan revisi data.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PROSES CETAK PDF (MURNI MEMBACA SNAPSHOT)
     * Tidak ada Join ke tabel Master Siswa, Guru, Mapel, atau Ekskul.
     */
    private function persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;
        
        // 1. BACA HEADER SNAPSHOT
        $header = DB::table('nilai_akhir_rapor')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->first();

        // Validasi Final
        if (!$header || $header->status_data !== 'final') {
            return null; 
        }

        // 2. BACA DETAIL SNAPSHOT (Nilai Mapel)
        $nilai = DB::table('nilai_akhir')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->orderBy('id_mapel', 'asc') // Atau kolom urutan jika disnapshot
            ->get();
            
        // Grouping Mapel
        $mapelFinal = [];
        foreach ($nilai as $row) {
            $kategoriId = $row->kategori_mapel_snapshot ?? 1;
            
            $item = (object) [
                'nama_mapel'  => $row->nama_mapel_snapshot, // Nama dari Arsip
                'nilai_akhir' => (int) $row->nilai_akhir,   // Integer
                'capaian'     => $row->capaian_akhir,
                'nama_guru'   => $row->nama_guru_snapshot   // Nama dari Arsip
            ];
            $mapelFinal[$kategoriId][] = $item;
        }

        // 3. DECODE EKSKUL (DARI JSON SNAPSHOT)
        $dataEkskul = [];
        if (!empty($header->data_ekskul_snapshot)) {
            $dataEkskul = json_decode($header->data_ekskul_snapshot);
        }

        // 4. SUSUN DATA RETURN (MOCK OBJECT)
        // Kita buat object palsu agar View PDF (rapor/pdf1_template) tidak error
        $siswaMock = (object) [
            'nama_siswa' => $header->nama_siswa_snapshot,
            'nisn' => $header->nisn_snapshot,
            'nipd' => $header->nipd_snapshot,
            'kelas' => (object) [
                'nama_kelas' => $header->nama_kelas_snapshot
            ]
        ];

        return [
            'siswa'         => $siswaMock, // Menggunakan data arsip
            'fase'          => $header->fase_snapshot,
            'sekolah'       => 'SMKN 1 SALATIGA', // Bisa disnapshot juga jika perlu
            'mapelGroup'    => $mapelFinal,
            'dataEkskul'    => $dataEkskul,
            
            'catatan'       => (object) [
                'sakit' => $header->sakit,
                'izin' => $header->izin,
                'alpha' => $header->alpha,
                'catatan_wali_kelas' => $header->catatan_wali_kelas,
                'catatan_akademik' => $header->catatan_akademik,
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
     * PROSES CETAK SATUAN
     */
    public function cetak_proses($id_siswa, Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran;
        
        $data = $this->persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran);

        if (!$data) return "<script>alert('Data Rapor belum dikunci. Silakan Generate terlebih dahulu.');window.close();</script>";

        $pdf = Pdf::loadView('rapor.pdf1_template', $data)
                ->setPaper('a4', 'portrait')
                ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
        return $pdf->stream('Rapor_'.$data['siswa']->nama_siswa.'.pdf');
    }

    /**
     * PROSES CETAK MASSAL (ZIP)
     */
    public function cetak_massal(Request $request)
    {
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran;

        if (!$id_kelas) return redirect()->back()->with('error', 'Pilih kelas.');

        $daftarSiswa = Siswa::where('id_kelas', $id_kelas)->orderBy('nama_siswa', 'asc')->get();
        
        $zip = new ZipArchive;
        $zipFileName = 'Rapor_Kelas_' . $id_kelas . '_' . time() . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $count = 0;
            foreach ($daftarSiswa as $siswa) {
                $data = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
                if (!$data) continue; 

                $pdf = \Pdf::loadView('rapor.pdf1_template', $data)
                        ->setPaper('a4', 'portrait')
                        ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
                
                $safeName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $siswa->nama_siswa);
                $zip->addFromString($safeName . '.pdf', $pdf->output());
                $count++;
            }
            $zip->close();

            if ($count === 0) return redirect()->back()->with('error', 'Tidak ada data rapor FINAL di kelas ini.');

            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
    }

    /**
     * PROSES DOWNLOAD SATU PDF (GABUNGAN)
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

    // Helper
    private function getFase($tingkat) {
        $t = strtoupper(preg_replace("/[^a-zA-Z0-9]/", "", $tingkat));
        if (in_array($t, ['10', 'X'])) return 'E';
        if (in_array($t, ['11', 'XI', '12', 'XII'])) return 'F';
        return '-';
    }
}