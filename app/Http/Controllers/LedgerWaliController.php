<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Pembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LedgerWaliExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LedgerWaliController extends Controller
{
    private function mapSemesterToInt(?string $semester): int
    {
        return (strtoupper($semester) == 'GENAP') ? 2 : 1;
    }

    /**
     * Fungsi sentral untuk menarik data Ledger
     */
    private function getLedgerData(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['developer', 'admin_erapor']);
        
        // 1. Filter Daftar Kelas Berdasarkan Role
        if ($isAdmin) {
            $kelasList = Kelas::orderBy('nama_kelas', 'asc')->get();
        } else {
            $kelasList = Kelas::where('id_guru', $user->id_guru)->orderBy('nama_kelas', 'asc')->get();
        }

        $tahunSekarang = date('Y');
        $bulanSekarang = date('n');
        
        $defaultSemester = ($bulanSekarang < 7) ? 'Genap' : 'Ganjil';
        $defaultTahunAjaran = ($bulanSekarang < 7) ? ($tahunSekarang - 1) . '/' . $tahunSekarang : $tahunSekarang . '/' . ($tahunSekarang + 1);

        $semesterRaw  = $request->semester ?? $defaultSemester;
        $tahun_ajaran = $request->tahun_ajaran ?? $defaultTahunAjaran;
        $semesterInt  = $this->mapSemesterToInt($semesterRaw);
        $showRanking  = $request->show_ranking ?? '0';
        $sortBy       = $request->sort_by ?? 'absen';

        // =========================================================================
        // PROTEKSI KEAMANAN (Mencegah bypass URL oleh user iseng)
        // =========================================================================
        $id_kelas = $request->id_kelas;

        if (!$isAdmin && $id_kelas) {
            // Pastikan id_kelas yang diminta benar-benar ada di daftar kelas binaannya
            $validAkses = $kelasList->contains('id_kelas', $id_kelas);
            if (!$validAkses) {
                // Jika mencoba akses kelas lain, batalkan dan kosongkan
                $id_kelas = null; 
            }
        }

        // Set Default Kelas (Akan mengambil kelas pertama miliknya)
        if (!$id_kelas) {
            $id_kelas = $kelasList->first()->id_kelas ?? null;
        }
        // =========================================================================

        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 3; $t <= $tahunSekarang + 3; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }
        rsort($tahunAjaranList);
        $semesterList = ['Ganjil', 'Genap'];

        $daftarMapel = collect();
        $dataLedger  = collect();
        $kelasTerpilih = null;

        if ($id_kelas) {
            $kelasTerpilih = Kelas::find($id_kelas);

            $rawMapel = Pembelajaran::where('id_kelas', $id_kelas)
                ->whereHas('mapel', function ($q) { $q->where('is_active', 1); })
                ->with('mapel')
                ->get()
                ->map(function($p) {
                    $m = $p->mapel;
                    $isAgama = (stripos($m->nama_mapel, 'Agama') !== false);
                    return (object)[
                        'id_mapel'     => $isAgama ? 'AGAMA' : (string)$m->id_mapel,
                        'id_mapel_asli'=> (string)$m->id_mapel,
                        'nama_mapel'   => $isAgama ? 'Pendidikan Agama' : $m->nama_mapel,
                        'nama_singkat' => $isAgama ? 'Agama' : ($m->nama_singkat ?? $m->nama_mapel),
                        'kategori'     => $m->kategori ?? 1,
                        'urutan'       => $m->urutan ?? 99,
                        'is_agama'     => $isAgama
                    ];
                });

            $daftarMapel = $rawMapel->groupBy('id_mapel')->map(function($items) {
                return $items->first();
            })->sort(function ($a, $b) {
                if ($a->is_agama && !$b->is_agama) return -1;
                if (!$a->is_agama && $b->is_agama) return 1;
                if ($a->kategori != $b->kategori) return $a->kategori <=> $b->kategori;
                return $a->urutan <=> $b->urutan;
            })->values();

            $globalAgamaIds = $rawMapel->where('is_agama', true)->pluck('id_mapel_asli')->toArray();

            $siswaList = Siswa::where('id_kelas', $id_kelas)->where('status', 'aktif')->orderBy('nama_siswa')->get();
            $siswaIds = $siswaList->pluck('id_siswa')->toArray();

            $rawNilai = DB::table('nilai_akhir')
                ->whereIn('id_siswa', $siswaIds)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->get();

            $mapNilai = [];
            foreach ($rawNilai as $rn) {
                $mapNilai[$rn->id_siswa][(string)$rn->id_mapel] = $rn->nilai_akhir;
            }

            $rawAbsen = DB::table('nilai_akhir_rapor')
                ->whereIn('id_siswa', $siswaIds)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->get();

            $mapAbsen = [];
            foreach ($rawAbsen as $ra) {
                $mapAbsen[$ra->id_siswa] = $ra;
            }

            $tempLedger = [];
            $jumlahKolomMapel = count($daftarMapel);

            foreach ($siswaList as $siswa) {
                $nilaiPerMapel = [];
                $totalNilai = 0;

                foreach ($daftarMapel as $mapel) {
                    $score = null;
                    if ($mapel->is_agama) {
                        foreach ($globalAgamaIds as $idAgamaAsli) {
                            if (isset($mapNilai[$siswa->id_siswa][$idAgamaAsli]) && is_numeric($mapNilai[$siswa->id_siswa][$idAgamaAsli])) {
                                $score = $mapNilai[$siswa->id_siswa][$idAgamaAsli];
                                break;
                            }
                        }
                    } else {
                        $mId = (string)$mapel->id_mapel;
                        if (isset($mapNilai[$siswa->id_siswa][$mId]) && is_numeric($mapNilai[$siswa->id_siswa][$mId])) {
                            $score = $mapNilai[$siswa->id_siswa][$mId];
                        }
                    }

                    $nilaiPerMapel[$mapel->id_mapel] = $score;
                    if (is_numeric($score)) {
                        $totalNilai += $score;
                    }
                }

                $absenSiswa = $mapAbsen[$siswa->id_siswa] ?? null;

                $tempLedger[] = (object)[
                    'id_siswa'   => $siswa->id_siswa,
                    'nama_siswa' => $siswa->nama_siswa,
                    'nipd'       => $siswa->nipd ?? '-',
                    'nisn'       => $siswa->nisn ?? '-',
                    'scores'     => $nilaiPerMapel,
                    'total'      => $totalNilai,
                    'rata_rata'  => $jumlahKolomMapel ? round($totalNilai / $jumlahKolomMapel, 2) : 0,
                    'absensi'    => (object)[
                        'sakit' => $absenSiswa && is_numeric($absenSiswa->sakit) ? $absenSiswa->sakit : '-',
                        'izin'  => $absenSiswa && is_numeric($absenSiswa->ijin ?? $absenSiswa->izin) ? ($absenSiswa->ijin ?? $absenSiswa->izin) : '-',
                        'alpha' => $absenSiswa && is_numeric($absenSiswa->alpha) ? $absenSiswa->alpha : '-'
                    ], 
                    'status_kenaikan' => $absenSiswa->status_kenaikan ?? null,
                    'ranking_no' => 0
                ];
            }

            $collection = collect($tempLedger);
            $rankedData = $collection->sort(function ($a, $b) {
                if ($b->rata_rata != $a->rata_rata) return $b->rata_rata <=> $a->rata_rata;
                return strcmp($a->nama_siswa, $b->nama_siswa);
            })->values();

            foreach ($rankedData as $index => $item) {
                $item->ranking_no = $index + 1;
            }

            $dataLedger = ($sortBy === 'absen') 
                ? $rankedData->sortBy('nama_siswa')->values() 
                : $rankedData->sortBy('ranking_no')->values();
        }

        return compact(
            'kelasList', 'id_kelas', 'semesterRaw', 'tahun_ajaran', 
            'daftarMapel', 'dataLedger', 'tahunAjaranList', 'semesterList', 
            'showRanking', 'sortBy', 'isAdmin', 'kelasTerpilih'
        );
    }

    public function index(Request $request)
    {
        $data = $this->getLedgerData($request);
        
        // Cek pesan error dari redirect (misal dari manipulasi URL Ekspor)
        if ($request->session()->has('error')) {
            return view('nilai.ledger_wali', $data)->with('error', $request->session()->get('error'));
        }
        
        return view('nilai.ledger_wali', $data);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getLedgerData($request);

        if (!$data['kelasTerpilih']) {
            return redirect()->route('walikelas.ledger.index')->with('error', 'Silakan pilih kelas terlebih dahulu sebelum melakukan export, atau Anda tidak memiliki akses ke kelas ini.');
        }

        $namaKelas = str_replace(' ', '_', $data['kelasTerpilih']->nama_kelas);
        $fileName = "Ledger_{$namaKelas}_{$data['semesterRaw']}_{$data['tahun_ajaran']}.xlsx";
        $fileName = str_replace('/', '_', $fileName);

        return Excel::download(new LedgerWaliExport($data), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getLedgerData($request);

        if (!$data['kelasTerpilih']) {
            return redirect()->route('walikelas.ledger.index')->with('error', 'Silakan pilih kelas terlebih dahulu sebelum melakukan export, atau Anda tidak memiliki akses ke kelas ini.');
        }

        $pdf = Pdf::loadView('nilai.ledger_wali_export', $data)->setPaper('A4', 'landscape');

        $namaKelas = str_replace(' ', '_', $data['kelasTerpilih']->nama_kelas);
        $fileName = "Ledger_{$namaKelas}_{$data['semesterRaw']}_{$data['tahun_ajaran']}.pdf";
        $fileName = str_replace('/', '_', $fileName);

        return $pdf->download($fileName);
    }
}