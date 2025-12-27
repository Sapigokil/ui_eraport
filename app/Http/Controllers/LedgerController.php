<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL') ? 1 : 2;

        $daftarMapel = [];
        $dataLedger = [];

        if ($id_kelas) {
            // 1. Ambil daftar mata pelajaran yang ada di kelas tersebut (Header Kolom)
            $daftarMapel = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $id_kelas)
                // Urutan: Umum(1) -> Kejuruan(2) -> Pilihan(3) -> Mulok(4)
                ->orderBy('mata_pelajaran.kategori', 'asc') 
                // Sub-urutan: Berdasarkan Nama Mapel
                ->orderBy('mata_pelajaran.nama_mapel', 'asc') 
                ->select(
                    'mata_pelajaran.id_mapel', 
                    'mata_pelajaran.nama_mapel', 
                    'mata_pelajaran.nama_singkat', // <-- Tambahan
                    'mata_pelajaran.kategori'
                )
                ->get();

            // 2. Ambil data siswa di kelas tersebut
            $siswaList = Siswa::where('id_kelas', $id_kelas)
                ->orderBy('nama_siswa', 'asc')
                ->get();

            foreach ($siswaList as $siswa) {
                $nilaiPerMapel = [];
                $totalNilai = 0;
                $jumlahMapelTerisi = 0;

                // 3. Ambil Nilai Akhir per Mata Pelajaran untuk tiap siswa
                foreach ($daftarMapel as $mapel) {
                    $nilai = DB::table('nilai_akhir')
                        ->where([
                            'id_siswa' => $siswa->id_siswa,
                            'id_mapel' => $mapel->id_mapel,
                            'semester' => $semesterInt,
                            'tahun_ajaran' => trim($tahun_ajaran)
                        ])->first();

                    $score = $nilai ? $nilai->nilai_akhir : 0;
                    $nilaiPerMapel[$mapel->id_mapel] = $score;

                    if ($score > 0) {
                        $totalNilai += $score;
                        $jumlahMapelTerisi++;
                    }
                }

                // 4. Ambil Data Absensi dari tabel catatan
                $absensi = DB::table('catatan')
                    ->where([
                        'id_siswa' => $siswa->id_siswa,
                        'semester' => $semesterInt,
                        'tahun_ajaran' => trim($tahun_ajaran)
                    ])->first();

                // 5. Susun Baris Data Siswa
                $dataLedger[] = (object)[
                    'nama_siswa' => $siswa->nama_siswa,
                    'nipd'       => $siswa->nipd,
                    'scores'     => $nilaiPerMapel,
                    'total'      => $totalNilai,
                    'rata_rata'  => $jumlahMapelTerisi > 0 ? round($totalNilai / $jumlahMapelTerisi, 2) : 0,
                    'absensi'    => (object)[
                        'sakit' => $absensi->sakit ?? 0,
                        'izin'  => $absensi->ijin ?? 0,
                        'alpha' => $absensi->alpha ?? 0,
                    ]
                ];
            }
        }

        return view('rapor.ledger_index', compact(
            'kelas', 
            'id_kelas', 
            'semesterRaw', 
            'tahun_ajaran', 
            'daftarMapel', 
            'dataLedger'
        ));
    }
}