<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\PklRaporSiswa;
use App\Models\PklRaporNilai;
use App\Models\PklSeason;
use App\Models\InfoSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

class PklRaporController extends Controller
{
    /**
     * HALAMAN INDEX CETAK RAPOR PKL
     */
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        
        $season = PklSeason::currentOpen();
        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');

        if ($bulanSekarang < 7) {
            $defaultTA = ($tahunSekarang - 1) . '/' . $tahunSekarang;
            $defaultSemester = 2;
        } else {
            $defaultTA = $tahunSekarang . '/' . ($tahunSekarang + 1);
            $defaultSemester = 1;
        }
        
        $semesterRaw = $request->semester ?? ($season->semester ?? $defaultSemester);
        $tahun_ajaran = $request->tahun_ajaran ?? ($season->tahun_ajaran ?? $defaultTA);

        $finalSiswaList = collect([]);
        $kelasAktif = null;

        if ($id_kelas) {
            $kelasAktif = Kelas::find($id_kelas);

            // REVISI: Join ke pkl_gurusiswa untuk mengambil nama_guru
            $masterSiswa = DB::table('siswa')
                ->where('siswa.id_kelas', $id_kelas)
                ->where('siswa.status', 'aktif')
                ->leftJoin('pkl_penempatan', function($join) use ($tahun_ajaran, $semesterRaw) {
                    $join->on('siswa.id_siswa', '=', 'pkl_penempatan.id_siswa')
                         ->where('pkl_penempatan.tahun_ajaran', '=', $tahun_ajaran)
                         ->where('pkl_penempatan.semester', '=', $semesterRaw);
                })
                ->leftJoin('pkl_gurusiswa', function($join) use ($tahun_ajaran, $semesterRaw) {
                    $join->on('siswa.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                         ->where('pkl_gurusiswa.tahun_ajaran', '=', $tahun_ajaran)
                         ->where('pkl_gurusiswa.semester', '=', $semesterRaw);
                })
                ->leftJoin('pkl_catatansiswa', 'pkl_penempatan.id', '=', 'pkl_catatansiswa.id_penempatan')
                ->select(
                    'siswa.id_siswa', 'siswa.nama_siswa', 'siswa.nisn',
                    'pkl_catatansiswa.status_penilaian',
                    'pkl_gurusiswa.nama_guru' 
                )
                ->get()
                ->keyBy('id_siswa');

            // REVISI: Ambil nama_guru_snapshot dari rapor
            $snapshotRapor = DB::table('pkl_raporsiswa')
                ->where('id_kelas', $id_kelas)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', $semesterRaw)
                ->select('id_siswa', 'status_data', 'last_update', 'nama_siswa_snapshot', 'nisn_snapshot', 'nama_guru_snapshot')
                ->get()
                ->keyBy('id_siswa');

            $allSiswaIDs = $masterSiswa->keys()->merge($snapshotRapor->keys())->unique();

            $finalSiswaList = $allSiswaIDs->map(function($id) use ($masterSiswa, $snapshotRapor) {
                $master = $masterSiswa->get($id);
                $snap   = $snapshotRapor->get($id);

                $nama = $snap->nama_siswa_snapshot ?? $master->nama_siswa ?? 'Data Siswa Terhapus';
                $nisn = $snap->nisn_snapshot ?? $master->nisn ?? '-';
                $namaGuru = $snap->nama_guru_snapshot ?? $master->nama_guru ?? 'Belum Ditentukan';
                
                $statusRapor = 'belum_generate';
                $tanggalGenerate = null;

                if ($snap) {
                    $statusRapor = $snap->status_data;
                    $tanggalGenerate = $snap->last_update;
                }

                $statusSiswa = 'aktif';
                if (!$master && $snap) {
                    $statusSiswa = 'history_moved';
                }

                $statusGuruPenilaian = null;
                if ($statusRapor == 'belum_generate' && $master) {
                    if ($master->status_penilaian === 1 || $master->status_penilaian === 3) {
                        $statusGuruPenilaian = 'siap'; 
                    } elseif ($master->status_penilaian === 0) {
                        $statusGuruPenilaian = 'belum_siap'; 
                    }
                }

                return (object) [
                    'id_siswa'       => $id,
                    'nama_siswa'     => $nama,
                    'nisn'           => $nisn,
                    'nama_guru'      => $namaGuru, // Dikirim ke view
                    'status_rapor'   => $statusRapor,
                    'status_siswa'   => $statusSiswa,
                    'status_guru'    => $statusGuruPenilaian, 
                    'last_update'    => $tanggalGenerate,
                    'is_ready_print' => in_array($statusRapor, ['final', 'cetak'])
                ];
            });

            $finalSiswaList = $finalSiswaList->sortBy('nama_siswa')->values();
        }

        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 3; $t <= $tahunSekarang + 3; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }
        rsort($tahunAjaranList);
        
        $semesterList = [1, 2]; 

        return view('pkl.rapor.index', compact(
            'kelas', 'id_kelas', 'semesterRaw', 'tahun_ajaran', 
            'kelasAktif', 'finalSiswaList', 'tahunAjaranList', 'semesterList'
        ));
    }

    /**
     * ==============================================================
     * LOGIKA INTI GENERATE RAPOR PKL (Digunakan oleh Satuan & Massal)
     * ==============================================================
     */
    private function prosesGenerateSinglePkl($id_siswa, $id_kelas, $semester, $tahun_ajaran)
    {
        $sumberData = DB::table('pkl_penempatan')
            ->join('pkl_gurusiswa', function($join) use ($tahun_ajaran, $semester) {
                $join->on('pkl_penempatan.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                     ->where('pkl_gurusiswa.tahun_ajaran', '=', $tahun_ajaran)
                     ->where('pkl_gurusiswa.semester', '=', $semester);
            })
            ->join('siswa', 'pkl_penempatan.id_siswa', '=', 'siswa.id_siswa')
            ->join('kelas', 'pkl_gurusiswa.id_kelas', '=', 'kelas.id_kelas')
            ->leftJoin('pkl_tempat', 'pkl_penempatan.id_pkltempat', '=', 'pkl_tempat.id')
            ->join('pkl_catatansiswa', 'pkl_penempatan.id', '=', 'pkl_catatansiswa.id_penempatan')
            ->where('pkl_penempatan.id_siswa', $id_siswa)
            ->select(
                'pkl_penempatan.id as id_penempatan', 'siswa.nama_siswa', 'siswa.nisn',
                'kelas.nama_kelas', 'kelas.wali_kelas',
                'pkl_gurusiswa.nama_guru', 'pkl_tempat.nama_perusahaan',
                'pkl_catatansiswa.*'
            )
            ->first();

        if (!$sumberData) {
            throw new \Exception("Siswa belum memiliki data penempatan/nilai dari Guru Pembimbing.");
        }
        if ($sumberData->status_penilaian !== 1 && $sumberData->status_penilaian !== 3) {
            throw new \Exception("Data nilai belum di-Finalisasi oleh Guru Pembimbing.");
        }

        $raporSiswa = PklRaporSiswa::updateOrCreate(
            [
                'id_siswa' => $id_siswa,
                'tahun_ajaran' => $tahun_ajaran,
                'semester' => $semester
            ],
            [
                'id_kelas' => $id_kelas,
                'nama_siswa_snapshot' => $sumberData->nama_siswa,
                'nisn_snapshot' => $sumberData->nisn,
                'kelas_snapshot' => $sumberData->nama_kelas,
                'wali_kelas_snapshot' => $sumberData->wali_kelas,
                'nama_guru_snapshot' => $sumberData->nama_guru,
                'nama_instruktur_snapshot' => $sumberData->nama_instruktur,
                'tempat_pkl_snapshot' => $sumberData->nama_perusahaan,
                'tanggal_mulai_snapshot' => $sumberData->tanggal_mulai,
                'tanggal_selesai_snapshot' => $sumberData->tanggal_selesai,
                'program_keahlian_snapshot' => $sumberData->program_keahlian,
                'konsentrasi_keahlian_snapshot' => $sumberData->konsentrasi_keahlian,
                'sakit' => $sumberData->sakit,
                'izin' => $sumberData->izin,
                'alpa' => $sumberData->alpa,
                'catatan_pembimbing' => $sumberData->catatan_pembimbing,
                'status_data' => 'draft', 
                'last_update' => now()
            ]
        );

        PklRaporNilai::where('id_pkl_raporsiswa', $raporSiswa->id)->delete();

        $sumberNilai = DB::table('pkl_nilaisiswa')
            ->join('pkl_tp', 'pkl_nilaisiswa.id_pkl_tp', '=', 'pkl_tp.id')
            ->where('pkl_nilaisiswa.id_penempatan', $sumberData->id_penempatan)
            ->select('pkl_nilaisiswa.*', 'pkl_tp.label_tp') 
            ->get();

        foreach ($sumberNilai as $n) {
            PklRaporNilai::create([
                'id_pkl_raporsiswa' => $raporSiswa->id,
                'id_pkl_tp' => $n->id_pkl_tp,
                'nama_tp_snapshot' => $n->label_tp,
                'nilai_rata_rata' => $n->nilai_rata_rata,
                'deskripsi_gabungan' => $n->deskripsi_gabungan
            ]);
        }
    }

    /**
     * EKSEKUSI SATUAN
     */
    public function generate(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->prosesGenerateSinglePkl($request->id_siswa, $request->id_kelas, $request->semester, $request->tahun_ajaran);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data rapor berhasil ditarik dari Guru Pembimbing!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function finalisasi(Request $request)
    {
        DB::beginTransaction();
        try {
            PklRaporSiswa::where('id_siswa', $request->id_siswa)
                ->where('tahun_ajaran', $request->tahun_ajaran)
                ->where('semester', $request->semester)
                ->update(['status_data' => 'final', 'last_update' => now()]);

            $penempatan = DB::table('pkl_penempatan')
                ->join('pkl_gurusiswa', function($join) use ($request) {
                    $join->on('pkl_penempatan.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                         ->where('pkl_gurusiswa.tahun_ajaran', '=', $request->tahun_ajaran)
                         ->where('pkl_gurusiswa.semester', '=', $request->semester);
                })
                ->where('pkl_penempatan.id_siswa', $request->id_siswa)
                ->select('pkl_penempatan.id as id_penempatan')->first();

            if ($penempatan) {
                DB::table('pkl_catatansiswa')->where('id_penempatan', $penempatan->id_penempatan)->update(['status_penilaian' => 3]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Rapor difinalisasi dan SIAP CETAK.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal memproses: ' . $e->getMessage()], 500);
        }
    }

    public function unlock(Request $request)
    {
        DB::beginTransaction();
        try {
            PklRaporSiswa::where('id_siswa', $request->id_siswa)
                ->where('tahun_ajaran', $request->tahun_ajaran)
                ->where('semester', $request->semester)
                ->update(['status_data' => 'draft', 'last_update' => now()]);

            $penempatan = DB::table('pkl_penempatan')
                ->join('pkl_gurusiswa', function($join) use ($request) {
                    $join->on('pkl_penempatan.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                         ->where('pkl_gurusiswa.tahun_ajaran', '=', $request->tahun_ajaran)
                         ->where('pkl_gurusiswa.semester', '=', $request->semester);
                })
                ->where('pkl_penempatan.id_siswa', $request->id_siswa)
                ->select('pkl_penempatan.id as id_penempatan')->first();

            if ($penempatan) {
                DB::table('pkl_catatansiswa')->where('id_penempatan', $penempatan->id_penempatan)->update(['status_penilaian' => 1]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Kunci rapor dibuka, status kembali ke DRAFT.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal membuka kunci: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ==============================================================
     * EKSEKUSI MASSAL (Smart Bulk Action - DARI SUMATIF)
     * ==============================================================
     */
    public function generateMassal(Request $request)
    {
        $id_siswa_array = $request->id_siswa_array ?? [];
        if (empty($id_siswa_array)) return response()->json(['message' => 'Tidak ada siswa yang dipilih.'], 400);

        $berhasil = 0; $dilewati = 0; $gagal = 0;

        foreach ($id_siswa_array as $id_siswa) {
            // FILTER: Lewati jika data sudah final/cetak
            $cekStatus = PklRaporSiswa::where(['id_siswa' => $id_siswa, 'semester' => $request->semester, 'tahun_ajaran' => $request->tahun_ajaran])->first();
            if ($cekStatus && in_array($cekStatus->status_data, ['final', 'cetak'])) {
                $dilewati++;
                continue;
            }

            DB::beginTransaction();
            try {
                $this->prosesGenerateSinglePkl($id_siswa, $request->id_kelas, $request->semester, $request->tahun_ajaran);
                DB::commit();
                $berhasil++;
            } catch (\Exception $e) {
                DB::rollBack();
                $gagal++;
            }
        }
        return response()->json(['status' => 'success', 'message' => "Proses Selesai! Berhasil: $berhasil siswa. Dilewati: $dilewati siswa. Gagal: $gagal siswa."]);
    }

    public function finalisasiMassal(Request $request)
    {
        $id_siswa_array = $request->id_siswa_array ?? [];
        if (empty($id_siswa_array)) return response()->json(['message' => 'Tidak ada siswa yang dipilih.'], 400);

        $berhasil = 0; $dilewati = 0;

        DB::beginTransaction();
        try {
            foreach ($id_siswa_array as $id_siswa) {
                // FILTER: Hanya memfinalisasi yang berstatus DRAFT
                $cek = PklRaporSiswa::where(['id_siswa' => $id_siswa, 'semester' => $request->semester, 'tahun_ajaran' => $request->tahun_ajaran])->first();
                if (!$cek || $cek->status_data !== 'draft') {
                    $dilewati++;
                    continue;
                }
                
                $cek->update(['status_data' => 'final', 'last_update' => now()]);
                
                $penempatan = DB::table('pkl_penempatan')
                    ->join('pkl_gurusiswa', function($join) use ($request) {
                        $join->on('pkl_penempatan.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                             ->where('pkl_gurusiswa.tahun_ajaran', '=', $request->tahun_ajaran)
                             ->where('pkl_gurusiswa.semester', '=', $request->semester);
                    })
                    ->where('pkl_penempatan.id_siswa', $id_siswa)
                    ->select('pkl_penempatan.id as id_penempatan')->first();

                if ($penempatan) {
                    DB::table('pkl_catatansiswa')->where('id_penempatan', $penempatan->id_penempatan)->update(['status_penilaian' => 3]);
                }
                $berhasil++;
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => "Finalisasi Selesai! $berhasil siswa berhasil dikunci. $dilewati siswa dilewati."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal memproses finalisasi massal: ' . $e->getMessage()], 500);
        }
    }

    public function unlockMassal(Request $request)
    {
        $id_siswa_array = $request->id_siswa_array ?? [];
        if (empty($id_siswa_array)) return response()->json(['message' => 'Tidak ada siswa yang dipilih.'], 400);

        $berhasil = 0; $dilewati = 0;

        DB::beginTransaction();
        try {
            foreach ($id_siswa_array as $id_siswa) {
                // FILTER: Hanya me-unlock yang sudah final atau cetak
                $cek = PklRaporSiswa::where(['id_siswa' => $id_siswa, 'semester' => $request->semester, 'tahun_ajaran' => $request->tahun_ajaran])->first();
                if (!$cek || !in_array($cek->status_data, ['final', 'cetak'])) {
                    $dilewati++;
                    continue;
                }
                
                $cek->update(['status_data' => 'draft', 'last_update' => now()]);
                
                $penempatan = DB::table('pkl_penempatan')
                    ->join('pkl_gurusiswa', function($join) use ($request) {
                        $join->on('pkl_penempatan.id_siswa', '=', 'pkl_gurusiswa.id_siswa')
                             ->where('pkl_gurusiswa.tahun_ajaran', '=', $request->tahun_ajaran)
                             ->where('pkl_gurusiswa.semester', '=', $request->semester);
                    })
                    ->where('pkl_penempatan.id_siswa', $id_siswa)
                    ->select('pkl_penempatan.id as id_penempatan')->first();

                if ($penempatan) {
                    DB::table('pkl_catatansiswa')->where('id_penempatan', $penempatan->id_penempatan)->update(['status_penilaian' => 1]);
                }
                $berhasil++;
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => "Unlock Selesai! Kunci $berhasil siswa telah dibuka. $dilewati siswa dilewati."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal memproses unlock massal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PDF LOGIC: HELPER PERSIAPAN DATA (DENGAN TGL CETAK)
     */
    private function persiapkanDataRaporPkl($id_siswa, $semester, $tahun_ajaran, $tgl_cetak = null)
    {
        $raporSiswa = PklRaporSiswa::where('id_siswa', $id_siswa)
            ->where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', $semester)
            ->first();

        if (!$raporSiswa || !in_array($raporSiswa->status_data, ['final', 'cetak'])) {
            return null;
        }

        $raporNilai = PklRaporNilai::where('id_pkl_raporsiswa', $raporSiswa->id)->get();

        $infoSekolah = InfoSekolah::first();
        if (!$infoSekolah) {
            $infoSekolah = new \stdClass();
            $infoSekolah->nama_sekolah = 'SMKN 1 SALATIGA';
            $infoSekolah->kota_kab = 'Salatiga';
        }

        $tanggalCetakRapor = $tgl_cetak ? \Carbon\Carbon::parse($tgl_cetak) : \Carbon\Carbon::now();

        return compact('infoSekolah', 'raporSiswa', 'raporNilai', 'tanggalCetakRapor');
    }

    /**
     * CETAK SATUAN
     */
    public function cetak_proses($id_siswa, Request $request)
    {
        $semester = $request->semester;
        $tahun_ajaran = $request->tahun_ajaran;
        $tgl_cetak = $request->tgl_cetak; 
        
        $data = $this->persiapkanDataRaporPkl($id_siswa, $semester, $tahun_ajaran, $tgl_cetak);

        if (!$data) {
            return "<script>alert('Data Rapor belum dikunci/final. Silakan Finalisasi terlebih dahulu.');window.close();</script>";
        }

        PklRaporSiswa::where('id', $data['raporSiswa']->id)->update(['status_data' => 'cetak']);

        $pdf = Pdf::loadView('pkl.rapor.pdf_pkl_template', $data)
                ->setPaper('a4', 'portrait')
                ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
        
        return $pdf->stream('Rapor_PKL_' . $data['raporSiswa']->nama_siswa_snapshot . '.pdf');
    }

    /**
     * CETAK MASSAL MERGE PDF
     */
    public function download_massal_merge(Request $request)
    {
        set_time_limit(1200); 
        ini_set('memory_limit', '1024M');

        $id_kelas = $request->id_kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        $semester = $request->semester;
        $tgl_cetak = $request->tgl_cetak;

        // Filter Smart Massal berdasarkan Checkbox
        $querySiswa = Siswa::where('id_kelas', $id_kelas);
        if ($request->has('ids') && !empty($request->ids)) {
            $idArray = explode(',', $request->ids);
            $querySiswa->whereIn('id_siswa', $idArray);
        }
        $siswaList = $querySiswa->orderBy('nama_siswa', 'asc')->get();

        if ($siswaList->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang terpilih atau ditemukan di kelas ini.');
        }

        $path = storage_path('app/public/temp_rapor_pkl');
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        $merger = PDFMerger::init();
        $generatedFiles = [];
        $siswaBerhasil = 0;

        foreach ($siswaList as $siswa) {
            $data = $this->persiapkanDataRaporPkl($siswa->id_siswa, $semester, $tahun_ajaran, $tgl_cetak);
            if (!$data) continue; 

            PklRaporSiswa::where('id', $data['raporSiswa']->id)->update(['status_data' => 'cetak']);

            $pdf = Pdf::loadView('pkl.rapor.pdf_pkl_template', $data)
                    ->setPaper('a4', 'portrait')
                    ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
            
            $fileName = 'Temp_PKL_' . $siswa->id_siswa . '_' . rand(1000,9999) . '.pdf';
            $fullPath = $path . '/' . $fileName;
            $pdf->save($fullPath);

            $merger->addPDF($fullPath, 'all');
            $generatedFiles[] = $fullPath;
            $siswaBerhasil++;
        }

        if ($siswaBerhasil > 0) {
            $namaKelas = $siswaList[0]->kelas->nama_kelas ?? 'Kelas';
            $finalFileName = 'Rapor_PKL_Massal_' . $namaKelas . '.pdf';
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
}