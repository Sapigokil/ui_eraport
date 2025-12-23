<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\RaporNilai; 
use App\Models\Ekskul;


class RaporNilaiController extends Controller
{
    // === 1. READ: Dashboard Progres (Route: master.rapornilai.index) ===
    public function index(Request $request) 
    {
        $kelasList = Kelas::all(); 
        $mapelList = MataPelajaran::all(); 
        
        $query = RaporNilai::select([
            'id_kelas', 
            'id_mapel', 
            'id_tahun_ajaran', 
            'semester',
            DB::raw('COUNT(id_rapor) as total_siswa_dinilai') 
        ]);
        
        if ($request->id_kelas) { $query->where('id_kelas', $request->id_kelas); } 
        if ($request->id_mapel) { $query->where('id_mapel', $request->id_mapel); }
        if ($request->id_tahun_ajaran) { $query->where('id_tahun_ajaran', $request->id_tahun_ajaran); } 
        if ($request->semester) { 
            $semesterDB = ($request->semester == 'Ganjil' || $request->semester == 'ganjil') ? 1 : 2; 
            $query->where('semester', $semesterDB); 
        } 
        
        $progressData = $query->groupBy('id_kelas', 'id_mapel', 'id_tahun_ajaran', 'semester')
                            ->get();

        $kelasMap = Kelas::pluck('nama_kelas', 'id_kelas');
        $mapelMap = MataPelajaran::pluck('nama_mapel', 'id_mapel');
        $siswaPerKelas = Siswa::select('id_kelas', DB::raw('COUNT(*) as total'))->groupBy('id_kelas')->get()->keyBy('id_kelas');

        $progress = $progressData->map(function ($item) use ($kelasMap, $mapelMap, $siswaPerKelas) {
            
            $totalSiswaDiKelas = $siswaPerKelas[$item->id_kelas]->total ?? 0;
            
            return [
                'nama_kelas' => $kelasMap[$item->id_kelas] ?? 'Kelas Dihapus',
                'nama_mapel' => $mapelMap[$item->id_mapel] ?? 'Mapel Dihapus',
                'tahun_ajaran' => $item->id_tahun_ajaran, 
                'semester' => $item->semester,
                'dinilai' => $item->total_siswa_dinilai,
                'total_siswa' => $totalSiswaDiKelas,
                'persen' => $totalSiswaDiKelas > 0 ? round(($item->total_siswa_dinilai / $totalSiswaDiKelas) * 100) : 0,
                'id_kelas' => $item->id_kelas, 
                'id_mapel' => $item->id_mapel,
            ];
        });
        
        $tahunAjaranList = ["2025/2026", "2026/2027", "2027/2028", "2028/2029", "2029/2030"];
        $semesterList = ["Ganjil", "Genap"];

        return view('rapor.nilai_index', compact(
            'progress', 
            'kelasList', 
            'mapelList',
            'request',
            'tahunAjaranList',
            'semesterList'
        ));
    }

    // === 2. CREATE: Menampilkan Form Input/Edit Massal (Route: master.rapornilai.create) ===
    public function create(Request $request) 
    {
        $kelas = Kelas::all();
        $mapel = MataPelajaran::all();
        
        $siswa = collect();
        $rapor = collect();

        $idKelas = $request->id_kelas;
        $idMapel = $request->id_mapel;
        $idTahunAjaranFilter = $request->id_tahun_ajaran; 
        $semesterFilter = $request->semester;

        $tahunAjaranList = ["2025/2026", "2026/2027", "2027/2028", "2028/2029", "2029/2030"];
        $semesterList = ["Ganjil", "Genap"];

        if ($idKelas && $idMapel && $idTahunAjaranFilter && $semesterFilter) {

            $semesterDB = ($semesterFilter == 'Ganjil' || $semesterFilter == 'ganjil') ? 1 : 2; 

            $siswa = Siswa::where('id_kelas', $idKelas)->get();

            $rapor = RaporNilai::where('id_kelas', $idKelas) 
                ->where('id_mapel', $idMapel)
                ->where('id_tahun_ajaran', $idTahunAjaranFilter) 
                ->where('semester', $semesterDB) 
                ->get()
                ->keyBy('id_siswa');
        }

        $viewName = 'rapor.nilai_create'; 
        if ($rapor->isNotEmpty() || ($request->has('mode') && $request->mode == 'edit')) {
             $viewName = 'rapor.nilai_edit';
        }

        return view($viewName, [
            'kelas'             => $kelas,
            'mapel'             => $mapel,
            'siswa'             => $siswa,
            'rapor'             => $rapor,
            'request'           => $request,
            'tahunAjaranList'   => $tahunAjaranList, 
            'semesterList'      => $semesterList,   
        ]);
    }
    
    // === 3. STORE/UPDATE: Memproses Simpan Data Massal (Route: master.rapornilai.store) ===
    public function store(Request $request)
    {
        $request->validate([
            'id_kelas'          => 'required',
            'id_mapel'          => 'required',
            'id_tahun_ajaran'   => 'required',
            'semester'          => 'required',
            'id_siswa'          => 'required|array',
        ]);
        
        $semesterDB = ($request->semester == 'Ganjil' || $request->semester == 'ganjil') ? 1 : 2;

        foreach ($request->id_siswa as $index => $id_siswa) {

            $nilai = $request->nilai[$index];
            $capaian = $request->capaian[$index];

            if ($nilai === null && (!$capaian || trim($capaian) === "")) {
                continue;
            }

            RaporNilai::updateOrCreate( 
                [
                    'id_siswa'          => $id_siswa,
                    'id_mapel'          => $request->id_mapel,
                    'id_tahun_ajaran'   => $request->id_tahun_ajaran,
                    'semester'          => $semesterDB,
                ],
                [
                    'id_kelas' => $request->id_kelas,
                    'nilai'    => $nilai,
                    'capaian'  => $capaian,
                ]
            );
        }

        // 🛑 KOREKSI: Menggunakan master.rapornilai.create
        return redirect()->route('master.rapornilai.create', [ 
            'id_kelas'        => $request->id_kelas,
            'id_mapel'        => $request->id_mapel,
            'id_tahun_ajaran' => $request->id_tahun_ajaran,
            'semester'        => $request->semester, 
            'mode'            => 'edit'
        ])->with('success', 'Data Nilai Rapor berhasil disimpan/diperbarui!');
    }

    // === 4. READ (Detail): Menampilkan detail satu data (Route: master.rapornilai.show) ===
    public function show($id_rapor) 
    {
        $rapor = RaporNilai::findOrFail($id_rapor);
        return view('rapor.nilai_show', compact('rapor'));
    }
    
    // === 5. EDIT: Menampilkan form edit satu data (Route: master.rapornilai.edit) ===
    public function edit($id_rapor) 
    {
        $rapor = RaporNilai::findOrFail($id_rapor);
        return view('rapor.nilai_edit_single', compact('rapor'));
    }
    
    // === 6. UPDATE: Memperbarui satu data (Route: master.rapornilai.update) ===
    public function update(Request $request, $id_rapor) 
    {
        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'capaian' => 'nullable|string|max:255',
        ]);

        $rapor = RaporNilai::findOrFail($id_rapor);
        $rapor->update($request->all());

        // 🛑 KOREKSI: Menggunakan master.rapornilai.show
        return redirect()->route('master.rapornilai.show', $rapor->id_rapor)->with('success', 'Nilai berhasil diperbarui.');
    }

    // === 7. DELETE: Menghapus Data (Route: master.rapornilai.destroy) ===
    public function destroy($id_rapor) 
    {
        $rapor = RaporNilai::find($id_rapor);
        if ($rapor) {
            $rapor->delete();
            return back()->with('success', 'Data nilai rapor berhasil dihapus.');
        }
        return back()->with('error', 'Data nilai rapor tidak ditemukan.');
    }

    public function detailProgress(Request $request)
    {
        try {
            $id_siswa = $request->id_siswa;
            $id_kelas = $request->id_kelas;
            $semester = $request->semester;
            $tahun_ajaran = $request->tahun_ajaran;

            // 1. Ambil Mapel WAJIB berdasarkan Ploting Kelas (Pembelajaran)
            $mapelWajib = \DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $id_kelas)
                ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel', 'mata_pelajaran.kategori')
                ->get();

            // 2. Ambil Mapel yang SUDAH ADA NILAINYA untuk siswa ini
            $nilaiSiswa = \DB::table('nilai_akhir')
                ->where('id_siswa', $id_siswa)
                ->where('semester', $semester)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->get()
                ->keyBy('id_mapel'); // Kita buat id_mapel sebagai key agar mudah dicocokkan

            // 3. Proses Pencocokan (Reconciliation)
            $dataResult = $mapelWajib->map(function($mapel) use ($nilaiSiswa) {
                // Mencocokkan: Apakah id_mapel di daftar wajib ada di daftar nilai?
                $dataNilai = $nilaiSiswa->get($mapel->id_mapel);
                $sudahAdaNilai = !is_null($dataNilai);

                return [
                    'nama_mapel' => $mapel->nama_mapel,
                    'kategori'   => $mapel->kategori ?? 'Umum',
                    'is_lengkap' => $sudahAdaNilai,
                    'nilai_akhir' => $sudahAdaNilai ? $dataNilai->nilai_akhir : '-'
                ];
            });

            // 4. Urutkan (Gunakan referensi Kurikulum Merdeka yang sudah disimpan)
            $sortedData = $dataResult->sortBy(function($item) {
                $priorityMap = [
                    'Pendidikan Agama dan Budi Pekerti' => 10,
                    'Pendidikan Pancasila' => 20,
                    'Bahasa Indonesia' => 30,
                    'Pendidikan Jasmani, Olah Raga, dan Kesehatan' => 40,
                    'Sejarah' => 50,
                    'Seni dan Budaya' => 60,
                    'Matematika' => 110,
                    'Bahasa Inggris' => 120,
                    'Informatika' => 130,
                    'Projek Ilmu Pengetahuan Alam dan Sosial' => 140,
                    'Projek Kreatif dan Kewirausahaan' => 150,
                ];
                return $priorityMap[$item['nama_mapel']] ?? 500;
            })->values();

            return response()->json(['data' => $sortedData]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mencocokkan data: ' . $e->getMessage()], 500);
        }
    }
}