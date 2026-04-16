<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Pembelajaran;
use App\Models\InfoSekolah;
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
     */
    private function getInfoSekolahAman()
    {
        $info = InfoSekolah::first();
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
     * Halaman Dashboard Cetak Rapor
     */
    public function cetakIndex(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        
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
        $semesterInt = $this->getSemesterInt($semesterRaw);

        $finalSiswaList = collect([]);
        $kelasAktif = null;

        if ($id_kelas) {
            $kelasAktif = Kelas::find($id_kelas);

            $masterSiswa = Siswa::where('id_kelas', $id_kelas)
                ->where('status', 'aktif')
                ->select('id_siswa', 'nama_siswa', 'nisn', 'nipd')
                ->get()
                ->keyBy('id_siswa');

            $snapshotRapor = DB::table('nilai_akhir_rapor')
                ->where('id_kelas', $id_kelas)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', $semesterInt)
                ->select('id_siswa', 'status_data', 'updated_at', 'nama_siswa_snapshot', 'nisn_snapshot')
                ->get()
                ->keyBy('id_siswa');

            $allSiswaIDs = $masterSiswa->keys()->merge($snapshotRapor->keys())->unique();

            $finalSiswaList = $allSiswaIDs->map(function($id) use ($masterSiswa, $snapshotRapor) {
                $master = $masterSiswa->get($id);
                $snap   = $snapshotRapor->get($id);

                $nama = $snap->nama_siswa_snapshot ?? $master->nama_siswa ?? 'Data Siswa Terhapus';
                $nisn = $snap->nisn_snapshot ?? $master->nisn ?? '-';
                
                $statusRapor = 'belum_generate';
                $tanggalGenerate = null;

                if ($snap) {
                    $statusRapor = $snap->status_data;
                    $tanggalGenerate = $snap->updated_at;
                }

                $statusSiswa = 'aktif';
                if (!$master && $snap) {
                    $statusSiswa = 'history_moved'; 
                }

                return (object) [
                    'id_siswa'   => $id,
                    'nama_siswa' => $nama,
                    'nisn'       => $nisn,
                    'status_rapor' => $statusRapor,
                    'status_siswa' => $statusSiswa,
                    'last_update'  => $tanggalGenerate,
                    'is_ready_print' => in_array($statusRapor, ['final', 'cetak'])
                ];
            });

            $finalSiswaList = $finalSiswaList->sortBy('nama_siswa')->values();
        }

        return view('rapor.cetak_rapor', compact(
            'kelas', 'id_kelas', 'semesterRaw', 'tahun_ajaran', 
            'kelasAktif', 'finalSiswaList'
        ));
    }

    /**
     * ==============================================================
     * LOGIKA INTI GENERATE RAPOR (Digunakan oleh Satuan & Massal)
     * ==============================================================
     */
    private function prosesGenerateSingle($id_siswa, $id_kelas, $semesterRaw, $tahun_ajaran)
    {
        $semesterInt = $this->getSemesterInt($semesterRaw);
        
        $siswa = Siswa::with('kelas')->find($id_siswa);
        if (!$siswa) throw new \Exception("Data siswa dengan ID $id_siswa tidak ditemukan.");
        
        $kelas = $siswa->kelas ?? Kelas::find($id_kelas);

        $bobot = \App\Models\BobotNilai::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', strtoupper($semesterRaw))
            ->first();
        if (!$bobot) throw new \Exception("Bobot Nilai belum disetting Admin.");

        $sekolah = $this->getInfoSekolahAman();
        $kepsekName = $sekolah->nama_kepsek ?? '-';
        $kepsekNip  = $sekolah->nip_kepsek ?? '-';

        $guruWali = DB::table('guru')->where('id_guru', $kelas->id_guru ?? null)->first();
        if (!$guruWali && $kelas) $guruWali = DB::table('guru')->where('nama_guru', $kelas->wali_kelas)->first();
        $namaWaliSnapshot = $guruWali->nama_guru ?? $kelas->wali_kelas ?? '-';
        $nipWaliSnapshot  = $guruWali->nip ?? '-';

        $namaKelasSnapshot = $kelas->nama_kelas ?? '-';
        $tingkatSnapshot = (int) preg_replace('/[^0-9]/', '', $kelas->tingkat ?? '10'); 
        $faseSnapshot = ($tingkatSnapshot >= 11) ? 'F' : 'E';
        
        $detailSiswa = DB::table('detail_siswa')->where('id_siswa', $id_siswa)->first();
        $agamaSiswa = strtolower(trim($detailSiswa->agama ?? ''));

        // TAHAP A: UPDATE LEVEL MAPEL (nilai_akhir)
        $listPembelajaran = Pembelajaran::with(['mapel' => function($q){ 
            $q->where('is_active', 1); 
        }, 'guru'])->where('id_kelas', $id_kelas)->get();

        foreach ($listPembelajaran as $pemb) {
            if (!$pemb->mapel) continue;

            $syaratAgama = $pemb->mapel->agama_khusus; 
            if (!empty($syaratAgama)) {
                if (strtolower(trim($syaratAgama)) != $agamaSiswa) continue; 
            }

            $namaMapelSnapshot = $pemb->mapel->nama_mapel;
            $kodeMapelSnapshot = $pemb->mapel->nama_singkat ?? '-';
            $namaGuruSnapshot  = ($pemb->guru) ? $pemb->guru->nama_guru : 'Guru Belum Ditentukan';
            
            $kategoriLabel = 'Mata Pelajaran Umum';
            if (isset($pemb->mapel->kategori)) {
                $mapKategori = [1 => 'Mata Pelajaran Umum', 2 => 'Mata Pelajaran Kejuruan', 3 => 'Mata Pelajaran Pilihan', 4 => 'Muatan Lokal'];
                $kategoriLabel = $mapKategori[$pemb->mapel->kategori] ?? $pemb->mapel->kategori;
            }

            $sumatifData = DB::table('sumatif')->where([
                'id_siswa' => $id_siswa, 'id_mapel' => $pemb->id_mapel,
                'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
            ])->get();

            $projectRow = DB::table('project')->where([
                'id_siswa' => $id_siswa, 'id_mapel' => $pemb->id_mapel,
                'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
            ])->first();
            $nilaiProject = $projectRow ? $projectRow->nilai : 0;

            $hasil = \App\Helpers\NilaiCalculator::process($sumatifData, $nilaiProject, $bobot);
            $deskripsi = $this->generateDeskripsi($id_siswa, $pemb->id_mapel, $semesterInt, $tahun_ajaran);

            DB::table('nilai_akhir')->updateOrInsert(
                [
                    'id_siswa' => $id_siswa, 'id_mapel' => $pemb->id_mapel,
                    'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
                ],
                array_merge($hasil['s_vals'], [
                    'id_kelas' => $id_kelas,
                    'rata_sumatif'  => $hasil['rata_sumatif'],
                    'bobot_sumatif' => $hasil['bobot_sumatif'],
                    'nilai_project' => $hasil['nilai_project'],
                    'rata_project'  => $hasil['rata_project'],
                    'bobot_project' => $hasil['bobot_project'],
                    'nilai_akhir'   => $hasil['nilai_akhir'],
                    'capaian_akhir' => $deskripsi,
                    'nama_mapel_snapshot'     => $namaMapelSnapshot,
                    'kode_mapel_snapshot'     => $kodeMapelSnapshot,
                    'kategori_mapel_snapshot' => $kategoriLabel,
                    'nama_guru_snapshot'      => $namaGuruSnapshot,
                    'nama_kelas_snapshot'     => $namaKelasSnapshot,
                    'tingkat'                 => $tingkatSnapshot,
                    'fase'                    => $faseSnapshot,
                    'status_data' => 'draft',
                    'updated_at'  => now(),
                    'created_at'  => DB::raw('IFNULL(created_at, NOW())') 
                ])
            );
        }

        // TAHAP B: UPDATE LEVEL HEADER (nilai_akhir_rapor)
        $catatan = DB::table('catatan')->where([
            'id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
        ])->first();
        
        $activeEkskuls = DB::table('ekskul_siswa')
            ->join('ekskul', 'ekskul_siswa.id_ekskul', '=', 'ekskul.id_ekskul')
            ->where('ekskul_siswa.id_siswa', $id_siswa)
            ->select('ekskul.id_ekskul', 'ekskul.nama_ekskul')
            ->get();

        $nilaiEkskuls = DB::table('nilai_ekskul')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->get()
            ->keyBy('id_ekskul');

        $ekskulSnapshot = [];
        foreach($activeEkskuls as $ae) {
            $nilai = $nilaiEkskuls->get($ae->id_ekskul);
            $ekskulSnapshot[] = [
                'nama'       => $ae->nama_ekskul, 
                'predikat'   => $nilai->predikat ?? '-',  
                'keterangan' => $nilai->keterangan ?? '-' 
            ];
        }

        DB::table('nilai_akhir_rapor')->updateOrInsert(
            [
                'id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran
            ],
            [
                'id_kelas' => $id_kelas,
                'nama_siswa_snapshot' => $siswa->nama_siswa,
                'nisn_snapshot'       => $siswa->nisn ?? '-',
                'nipd_snapshot'       => $siswa->nipd ?? '-', 
                'nama_kelas_snapshot' => $namaKelasSnapshot,
                'tingkat'             => $tingkatSnapshot,
                'fase'                => $faseSnapshot, 
                'wali_kelas_snapshot' => $namaWaliSnapshot,
                'nip_wali_snapshot'   => $nipWaliSnapshot,
                'kepsek_snapshot'     => $kepsekName,
                'nip_kepsek_snapshot' => $kepsekNip,
                'sakit' => $catatan->sakit ?? 0, 
                'ijin'  => $catatan->ijin ?? 0, 
                'alpha' => $catatan->alpha ?? 0,
                'kokurikuler'        => $catatan->kokurikuler ?? '-',
                'catatan_wali_kelas' => $catatan->catatan_wali_kelas ?? '-',
                'status_kenaikan'    => $catatan->status_kenaikan ?? 'proses',
                'data_ekskul'   => json_encode($ekskulSnapshot),
                'tanggal_cetak' => now(),
                'status_data'   => 'draft',
                'updated_at'    => now(),
                'created_at'    => DB::raw('IFNULL(created_at, NOW())')
            ]
        );
    }

    /**
     * EKSEKUSI SATUAN (Bypass Admin)
     */
    public function generateRapor(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->prosesGenerateSingle($request->id_siswa, $request->id_kelas, $request->semester, $request->tahun_ajaran);
            DB::commit();
            return response()->json(['message' => 'Data nilai, absen, dan catatan berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function unlockRapor(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterInt = $this->getSemesterInt($request->semester);

        DB::beginTransaction();
        try {
            DB::table('nilai_akhir_rapor')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->update(['status_data' => 'draft', 'updated_at' => now()]);
            DB::table('nilai_akhir')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->update(['status_data' => 'draft']);
            DB::commit();
            return response()->json(['message' => 'Kunci rapor dibuka. Status kembali DRAFT.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal unlock: ' . $e->getMessage()], 500);
        }
    }

    public function finalisasiRapor(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterInt = $this->getSemesterInt($request->semester);

        DB::beginTransaction();
        try {
            DB::table('nilai_akhir_rapor')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->update(['status_data' => 'final', 'updated_at' => now()]);
            DB::table('nilai_akhir')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->update(['status_data' => 'final']);
            DB::commit();
            return response()->json(['message' => 'Rapor berhasil difinalisasi dan SIAP CETAK.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal finalisasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ==============================================================
     * EKSEKUSI MASSAL (Smart Bulk Action)
     * ==============================================================
     */
    public function generateRaporMassal(Request $request)
    {
        $id_siswa_array = $request->id_siswa_array ?? [];
        if (empty($id_siswa_array)) return response()->json(['message' => 'Tidak ada siswa yang dipilih.'], 400);

        $semesterInt = $this->getSemesterInt($request->semester);
        $berhasil = 0; $dilewati = 0; $gagal = 0;

        foreach ($id_siswa_array as $id_siswa) {
            $cekStatus = DB::table('nilai_akhir_rapor')->where(['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $request->tahun_ajaran])->first();
            if ($cekStatus && in_array($cekStatus->status_data, ['final', 'cetak'])) {
                $dilewati++;
                continue;
            }

            DB::beginTransaction();
            try {
                $this->prosesGenerateSingle($id_siswa, $request->id_kelas, $request->semester, $request->tahun_ajaran);
                DB::commit();
                $berhasil++;
            } catch (\Exception $e) {
                DB::rollBack();
                $gagal++;
            }
        }
        return response()->json(['message' => "Proses Selesai! Berhasil: $berhasil siswa. Dilewati: $dilewati siswa. Gagal: $gagal siswa."]);
    }

    public function finalisasiRaporMassal(Request $request)
    {
        $id_siswa_array = $request->id_siswa_array ?? [];
        if (empty($id_siswa_array)) return response()->json(['message' => 'Tidak ada siswa yang dipilih.'], 400);

        $semesterInt = $this->getSemesterInt($request->semester);
        $berhasil = 0; $dilewati = 0;

        DB::beginTransaction();
        try {
            foreach ($id_siswa_array as $id_siswa) {
                $cek = DB::table('nilai_akhir_rapor')->where(['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $request->tahun_ajaran])->first();
                if (!$cek || $cek->status_data !== 'draft') {
                    $dilewati++;
                    continue;
                }
                
                DB::table('nilai_akhir_rapor')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $request->tahun_ajaran)->update(['status_data' => 'final', 'updated_at' => now()]);
                DB::table('nilai_akhir')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $request->tahun_ajaran)->update(['status_data' => 'final']);
                $berhasil++;
            }
            DB::commit();
            return response()->json(['message' => "Finalisasi Selesai! $berhasil siswa berhasil dikunci. $dilewati siswa dilewati karena status belum draft."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses finalisasi massal: ' . $e->getMessage()], 500);
        }
    }

    public function unlockRaporMassal(Request $request)
    {
        $id_siswa_array = $request->id_siswa_array ?? [];
        if (empty($id_siswa_array)) return response()->json(['message' => 'Tidak ada siswa yang dipilih.'], 400);

        $semesterInt = $this->getSemesterInt($request->semester);
        $berhasil = 0; $dilewati = 0;

        DB::beginTransaction();
        try {
            foreach ($id_siswa_array as $id_siswa) {
                $cek = DB::table('nilai_akhir_rapor')->where(['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $request->tahun_ajaran])->first();
                if (!$cek || !in_array($cek->status_data, ['final', 'cetak'])) {
                    $dilewati++;
                    continue;
                }
                
                DB::table('nilai_akhir_rapor')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $request->tahun_ajaran)->update(['status_data' => 'draft', 'updated_at' => now()]);
                DB::table('nilai_akhir')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $request->tahun_ajaran)->update(['status_data' => 'draft']);
                $berhasil++;
            }
            DB::commit();
            return response()->json(['message' => "Unlock Selesai! Kunci $berhasil siswa telah dibuka. $dilewati siswa dilewati karena belum dikunci."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses unlock massal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ==============================================================
     * PROSES CETAK SATUAN & PERSIAPAN DATA PDF
     * ==============================================================
     */
    public function cetak_proses($id_siswa, Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterInt = $this->getSemesterInt($semesterRaw);
        
        // 👇 PERBAIKAN: Menangkap parameter tanggal cetak dari JS URL
        $tanggal_cetak = $request->tanggal_cetak ?? date('Y-m-d');

        $data = $this->persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran);

        if (!$data) return "<script>alert('Data Rapor belum dikunci/final. Silakan Generate terlebih dahulu.');window.close();</script>";

        // 👇 Inject ke array data PDF
        $data['tanggal_cetak_override'] = $tanggal_cetak;

        DB::table('nilai_akhir_rapor')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->update(['status_data' => 'cetak']);
        DB::table('nilai_akhir')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->update(['status_data' => 'cetak']);

        $pdf = Pdf::loadView('rapor.pdf1_template', $data)
                ->setPaper('a4', 'portrait')
                ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
        
        return $pdf->stream('Rapor_' . ($data['siswa']->nama_siswa ?? 'Siswa') . '.pdf');
    }

    private function persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran)
    {
        $semesterInt = $this->getSemesterInt($semesterRaw);
        
        $header = DB::table('nilai_akhir_rapor')->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->first();
        if (!$header || !in_array($header->status_data, ['final', 'cetak'])) return null; 

        $getSekolah = InfoSekolah::first();
        if (!$getSekolah) {
            $getSekolah = new \stdClass();
            $getSekolah->nama_sekolah = '-';
            $getSekolah->jalan = ''; $getSekolah->kelurahan = ''; $getSekolah->kecamatan = ''; 
            $getSekolah->kota_kab = ''; $getSekolah->kode_pos = ''; $getSekolah->nama_kepsek = '-'; $getSekolah->nip_kepsek = '-';
        }

        $nilaiSnapshot = DB::table('nilai_akhir')
            ->where('id_siswa', $id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)
            ->leftJoin('mata_pelajaran', 'nilai_akhir.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->select('nilai_akhir.*', 'mata_pelajaran.urutan', 'mata_pelajaran.kategori')
            ->orderBy('mata_pelajaran.kategori', 'asc')->orderBy('mata_pelajaran.urutan', 'asc')->get();

        $mapelFinal = [];
        foreach ($nilaiSnapshot as $row) {
            $kategoriKey = $row->kategori_mapel_snapshot ?? $row->kategori ?? 1;
            $mapelFinal[$kategoriKey][] = (object) [
                'id_mapel'    => $row->id_mapel,
                'nama_mapel'  => $row->nama_mapel_snapshot,
                'nilai_akhir' => (int) $row->nilai_akhir,
                'capaian'     => $row->capaian_akhir,
                'nama_guru'   => $row->nama_guru_snapshot
            ];
        }

        $dataEkskul = [];
        if (!empty($header->data_ekskul)) {
            $decoded = json_decode($header->data_ekskul);
            if (is_array($decoded)) $dataEkskul = $decoded;
        } elseif (!empty($header->data_ekskul_snapshot)) {
             $decoded = json_decode($header->data_ekskul_snapshot);
             if (is_array($decoded)) $dataEkskul = $decoded;
        }

        $siswaMock = (object) [
            'nama_siswa' => $header->nama_siswa_snapshot,
            'nisn' => $header->nisn_snapshot,
            'nipd' => $header->nipd_snapshot,
            'kelas' => (object) ['nama_kelas' => $header->nama_kelas_snapshot]
        ];

        return [
            'siswa'         => $siswaMock,
            'fase'          => $header->fase ?? $header->fase_snapshot ?? '-',
            'sekolah'       => $getSekolah->nama_sekolah,
            'alamat_sekolah'=> $getSekolah->jalan,
            'infoSekolah'   => $getSekolah, 
            'info_sekolah'  => $getSekolah,
            'mapelGroup'    => $mapelFinal,
            'dataEkskul'    => $dataEkskul,
            'catatan'       => (object) [
                'sakit' => $header->sakit,
                'izin' => $header->ijin ?? $header->izin ?? 0,
                'alpha' => $header->alpha,
                'catatan_wali_kelas' => $header->catatan_wali_kelas,
                'kokurikuler' => $header->kokurikuler ?? '-', 
                'status_kenaikan' => $header->status_kenaikan
            ],
            'semester'      => $semesterRaw,
            'semesterInt'   => $semesterInt,
            'tahun_ajaran'  => $tahun_ajaran,
            'nama_wali'     => $header->wali_kelas_snapshot,
            'nip_wali'      => $header->nip_wali_snapshot,
            'nama_kepsek'   => $header->kepsek_snapshot,
            'nip_kepsek'    => $header->nip_kepsek_snapshot,
            'tanggal_cetak' => $header->tanggal_cetak,
        ];
    }

    /**
     * ==============================================================
     * PDF MASSAL & PDF MERGER (Mendukung Checkbox / String IDs)
     * ==============================================================
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
        
        $pdf = Pdf::loadView('rapor.pdf2_massal_template', compact('allData'))->setPaper('a4', 'portrait')->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
        return $pdf->download('RAPOR_MASSAL_' . time() . '.pdf');
    }

    public function download_massal_merge(Request $request)
    {
        set_time_limit(1200);
        ini_set('memory_limit', '1024M');

        $id_kelas = $request->id_kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $semesterInt = $this->getSemesterInt($semesterRaw);
        
        // 👇 PERBAIKAN: Menangkap parameter tanggal cetak
        $tanggal_cetak = $request->tanggal_cetak ?? date('Y-m-d');

        // Filter Smart Massal berdasarkan Checkbox
        $querySiswa = Siswa::where('id_kelas', $id_kelas);
        if ($request->has('ids') && !empty($request->ids)) {
            $idArray = explode(',', $request->ids);
            $querySiswa->whereIn('id_siswa', $idArray);
        }
        $siswaList = $querySiswa->orderBy('nama_siswa', 'asc')->get();

        if ($siswaList->isEmpty()) return back()->with('error', 'Tidak ada siswa yang terpilih atau ditemukan.');

        $path = storage_path('app/public/temp_rapor');
        if (!File::isDirectory($path)) File::makeDirectory($path, 0777, true, true);

        $merger = PDFMerger::init();
        $generatedFiles = [];
        $siswaBerhasil = 0;

        foreach ($siswaList as $siswa) {
            $data = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
            if (!$data) continue; 
            
            // 👇 Inject ke array data PDF
            $data['tanggal_cetak_override'] = $tanggal_cetak;

            DB::table('nilai_akhir_rapor')->where('id_siswa', $siswa->id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->update(['status_data' => 'cetak']);
            DB::table('nilai_akhir')->where('id_siswa', $siswa->id_siswa)->where('semester', $semesterInt)->where('tahun_ajaran', $tahun_ajaran)->update(['status_data' => 'cetak']);

            $pdf = Pdf::loadView('rapor.pdf1_template', $data)
                    ->setPaper('a4', 'portrait')
                    ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
            
            $fileName = 'Temp_' . $siswa->id_siswa . '_' . rand(1000,9999) . '.pdf';
            $fullPath = $path . '/' . $fileName;
            
            $pdf->save($fullPath);
            $merger->addPDF($fullPath, 'all');
            $generatedFiles[] = $fullPath;
            $siswaBerhasil++;
        }

        if ($siswaBerhasil > 0) {
            $finalFileName = 'Rapor_Massal_Kelas_' . ($siswaList[0]->kelas->nama_kelas ?? 'All') . '.pdf';
            $finalPath = $path . '/' . $finalFileName;
            
            $merger->merge();
            $merger->save($finalPath);

            foreach ($generatedFiles as $file) {
                if (File::exists($file)) File::delete($file);
            }

            return response()->download($finalPath)->deleteFileAfterSend(true);
        } else {
            return back()->with('error', 'Gagal memproses data. Pastikan status rapor siswa yang dicentang sudah FINAL.');
        }
    }

    /**
     * Helper Deskripsi Otomatis (Digunakan oleh Proses Generate)
     */
    private function generateDeskripsi($id_siswa, $id_mapel, $semester, $tahun_ajaran)
    {
        $sumatif = DB::table('sumatif')
            ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $semester, 'tahun_ajaran' => $tahun_ajaran])
            ->whereNotNull('nilai')
            ->get()->map(function($item) { return ['nilai' => (float) $item->nilai, 'tp' => $item->tujuan_pembelajaran]; });

        $project = DB::table('project')
            ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $semester, 'tahun_ajaran' => $tahun_ajaran])
            ->get()->map(function($item) { return ['nilai' => (float) $item->nilai, 'tp' => $item->tujuan_pembelajaran]; });

        $semuaNilai = $sumatif->merge($project)->filter(function($item) { return !empty(trim((string)$item['tp'])); });

        if ($semuaNilai->isEmpty()) return "Capaian kompetensi belum tersedia.";

        $terendah = $semuaNilai->sortBy('nilai')->first();
        $tertinggi = $semuaNilai->sortByDesc('nilai')->first();

        if ($semuaNilai->count() === 1 || $terendah['nilai'] === $tertinggi['nilai']) {
            $narasi = ($terendah['nilai'] > 84) ? "Menunjukkan penguasaan yang baik dalam hal" : "Perlu penguatan dalam hal";
            return $narasi . " " . $terendah['tp'] . ".";
        }

        $kunciRendah = ($terendah['nilai'] < 81) ? "Perlu peningkatan dalam hal" : "Perlu penguatan dalam hal";
        $kunciTinggi = ($tertinggi['nilai'] > 89) ? "Mahir dalam hal" : "Baik dalam hal";

        return "{$kunciRendah} " . trim($terendah['tp']) . ", namun menunjukkan capaian {$kunciTinggi} " . trim($tertinggi['tp']) . ".";
    }
}