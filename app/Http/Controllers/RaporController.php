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
     * Halaman Monitoring Progres
     */
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL') ? 1 : 2;

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
     * Halaman Daftar Siswa
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
     * Mesin Cetak PDF: Pemisahan Berdasarkan Kategori (Integer 1-4)
     */
    public function cetak_proses($id_siswa, Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        $siswa = Siswa::with('kelas')->findOrFail($id_siswa);
        $getSekolah = InfoSekolah::first();
        
        $sekolah = $getSekolah->nama_sekolah ?? 'SMKN 1 SALATIGA';
        $infoSekolahVar = $getSekolah->jalan ?? 'Alamat Sekolah';

        // Logika Fase
        $tktRaw = trim($siswa->kelas->tingkat ?? '');
        $tkt = strtoupper(preg_replace("/[^a-zA-Z0-9]/", "", $tktRaw));
        $fase = match (true) {
            ($tkt === 'X' || $tkt === '10') => 'E',
            ($tkt === 'XI' || $tkt === '11' || $tkt === 'XII' || $tkt === '12') => 'F',
            default => '-'
        };

        // --- TAHAP 1-4: PECAH PER KATEGORI INTEGER ---
        $mapelFinal = [];
        $daftarKategori = [
            1 => 'Mata Pelajaran Umum',
            2 => 'Mata Pelajaran Kejuruan',
            3 => 'Mata Pelajaran Pilihan',
            4 => 'Muatan Lokal'
        ];

        foreach ($daftarKategori as $key => $label) {
            // Ambil mapel kelas tersebut yang masuk kategori integer terkait
            $kelompok = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $siswa->id_kelas)
                ->where('mata_pelajaran.kategori', $key) // Pencarian berdasarkan integer 1-4
                ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
                ->get();

            if ($kelompok->isNotEmpty()) {
                foreach ($kelompok as $mp) {
                    $mp->nilai_akhir = $this->hitungNilai($id_siswa, $mp->id_mapel, $semesterInt, $tahun_ajaran);
                    $mp->capaian = $mp->nilai_akhir >= 75 
                        ? "Menunjukkan penguasaan yang baik dalam " . $mp->nama_mapel 
                        : "Perlu bimbingan dalam " . $mp->nama_mapel;
                }
                // Simpan ke array dengan label kategori agar bisa di-loop di View
                $mapelFinal[$label] = $kelompok;
            }
        }

        $catatan = DB::table('catatan')->where(['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->first();
        $namaWali = $siswa->kelas->wali_kelas ?? 'Wali Kelas';
        $dataGuru = DB::table('guru')->where('nama_guru', 'LIKE', '%' . $namaWali . '%')->first();

        $data = [
            'siswa'         => $siswa,
            'fase'          => $fase,
            'sekolah'       => $sekolah,
            'infoSekolah'   => $infoSekolahVar,
            'mapelGroup'    => $mapelFinal,
            'catatan'       => $catatan,
            'semester'      => $semesterRaw,
            'tahun_ajaran'  => $tahun_ajaran,
            'semesterInt'   => $semesterInt,
            'namaWali'      => $namaWali,
            'nip_wali'      => $dataGuru->nip ?? '-',
        ];

        $pdf = Pdf::loadView('rapor.pdf1_template', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('Rapor_'.$siswa->nama_siswa.'.pdf');
    }

    /**
     * Private Helper: Hitung Nilai Akhir
     */
    private function hitungNilai($id_siswa, $id_mapel, $semester, $tahun)
    {
        $sumatif = DB::table('sumatif')
            ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $semester, 'tahun_ajaran' => $tahun])
            ->avg('nilai') ?: 0;

        $project = DB::table('project')
            ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $semester, 'tahun_ajaran' => $tahun])
            ->avg('nilai') ?: 0;

        return round(($sumatif + $project) / 2);
    }
}