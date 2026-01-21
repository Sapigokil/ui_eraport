<?php

// File: app/Http/Controllers/PembelajaranController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelajaran;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\Guru;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;


class PembelajaranController extends Controller
{
    // 🛑 KOREKSI: Mengubah aksesibilitas menjadi public agar dapat diakses di view
    public const DEFAULT_GURU_ID = 1; 

    // 🟩 Halaman utama (Menambahkan Filter)
    public function dataPembelajaran(Request $request) 
    {
        // 1. Inisialisasi Query
        $query = Pembelajaran::select('pembelajaran.*')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('mata_pelajaran.is_active', 1);

        // 2. Terapkan Filter
        if ($request->id_mapel) {
            $query->where('pembelajaran.id_mapel', $request->id_mapel);
        }
        if ($request->id_kelas) {
            $query->where('pembelajaran.id_kelas', $request->id_kelas);
        }
        
        $idGuruFilter = $request->id_guru;
        if (!empty($idGuruFilter) && $idGuruFilter != 0) {
            $query->where('pembelajaran.id_guru', $idGuruFilter);
        }

        // 3. Pengurutan & Eksekusi (REVISI DISINI)
        // Logika: Kategori (asc) -> Urutan (asc) -> Nama (asc)
        $pembelajaran = $query
            ->orderBy('mata_pelajaran.kategori', 'asc') // 1. Prioritas Utama: Kategori
            ->orderBy('mata_pelajaran.urutan', 'asc')   // 2. Prioritas Kedua: Urutan
            ->with(['mapel', 'kelas', 'guru'])
            ->get();

        // 4. Ambil data untuk dropdown filter (Disamakan urutannya agar konsisten)
        $mapel_list = MataPelajaran::where('is_active', 1)
            ->orderBy('kategori', 'asc') // Tambahkan ini
            ->orderBy('urutan', 'asc')
            ->get();

        $kelas_list = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $guru_list = Guru::orderBy('nama_guru')->get(); 

        return view('pembelajaran.index', compact('pembelajaran', 'mapel_list', 'kelas_list', 'guru_list'));
    }

    // 🟩 API: Ambil data pembelajaran berdasarkan Mapel (untuk AJAX)
    public function getByMapel($id_mapel)
    {
        $data = Pembelajaran::where('id_mapel', $id_mapel)
            ->select('id_kelas', 'id_guru')
            ->get();

        return response()->json($data);
    }

    // 🟫 Tampilkan form create
    public function create()
    {
        // $mapel = MataPelajaran::orderBy('urutan', 'asc')->orderBy('nama_mapel', 'asc')->get();
        $mapel = MataPelajaran::where('is_active', 1)
            ->orderBy('kategori', 'asc') // Tambahkan ini
            ->orderBy('nama_mapel', 'asc')
            ->get();

        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $guru = Guru::orderBy('nama_guru')->get();
        
        return view('pembelajaran.create', compact('mapel', 'kelas', 'guru'));
    }
    
    // 🟦 Simpan data pembelajaran (Mass Store)
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar (ID Mapel)
        $request->validate([
            'id_mapel' => [
                'required',
                Rule::exists('mata_pelajaran', 'id_mapel')->where('is_active', 1),
            ],
            'kelas_guru' => 'required|array', 
        ]);
        
        $id_mapel = $request->id_mapel;
        $data_pembelajaran = $request->kelas_guru;
        $counter = 0;

        // 2. Loop dan Proses Data Jamak
        foreach ($data_pembelajaran as $data) {
            
            $id_kelas = $data['id_kelas'];
            
            // Ambil input guru, default null jika tidak ada key-nya
            $raw_guru = $data['id_guru'] ?? null;
            
            // LOGIKA BARU: Jika 0 atau string kosong, ubah jadi NULL (Jangan pakai DEFAULT_GURU_ID)
            if (empty($raw_guru) || $raw_guru === "0" || $raw_guru === 0) {
                $id_guru = null;
            } else {
                $id_guru = $raw_guru;
            }

            $is_active = isset($data['active']); 

            if ($is_active) {
                // Cek apakah data sudah ada
                $existing = Pembelajaran::where('id_mapel', $id_mapel)
                                        ->where('id_kelas', $id_kelas)
                                        ->first();
                
                if (!$existing) {
                    // Create baru
                    Pembelajaran::create([
                        'id_mapel' => $id_mapel,
                        'id_kelas' => $id_kelas,
                        'id_guru'  => $id_guru, // Simpan NULL jika belum ditentukan
                    ]);
                    $counter++;
                } else {
                    // Update guru yang ada (bisa jadi mengubah dari Ada Guru -> NULL)
                    $existing->update(['id_guru' => $id_guru]);
                }
            } else {
                // Skema Delete: Jika Tidak Aktif, Hapus Record Pembelajaran
                Pembelajaran::where('id_mapel', $id_mapel)
                            ->where('id_kelas', $id_kelas)
                            ->delete();
            }
        }

        // 3. Tangani Hasil
        if ($counter > 0) {
            return redirect()->route('master.pembelajaran.index')
                ->with('success', "Berhasil menambahkan {$counter} tautan pembelajaran baru dan memperbarui data lainnya.");
        } else {
            return redirect()->route('master.pembelajaran.index')
                ->with('success', 'Perubahan pada tautan pembelajaran berhasil disimpan.');
        }
    }

    // 🟪 Tampilkan form edit (Mass Edit berdasarkan ID Mapel)
    public function edit($id_pembelajaran)
    {
        $pembelajaran_awal = Pembelajaran::findOrFail($id_pembelajaran);
        $id_mapel_edit = $pembelajaran_awal->id_mapel;

        $mapel_edit = MataPelajaran::findOrFail($id_mapel_edit);

        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $guru = Guru::orderBy('nama_guru')->get();

        $existing_pembelajaran = Pembelajaran::where('id_mapel', $id_mapel_edit)
                                            ->get()
                                            ->keyBy('id_kelas'); 
        
        return view('pembelajaran.edit', compact(
            'mapel_edit', 
            'kelas', 
            'guru', 
            'existing_pembelajaran',
            'pembelajaran_awal' 
        ));
    }

    // 🟨 Proses Update data pembelajaran (Mass Update)
    public function update(Request $request, $id_pembelajaran) 
    {
        // Ambil data awal untuk mengetahui konteks Mapel apa yang sedang diedit
        $pembelajaran_awal = Pembelajaran::findOrFail($id_pembelajaran);
        $id_mapel_edit = $pembelajaran_awal->id_mapel;

        $request->validate([
            'kelas_guru' => 'required|array', 
        ]);

        $data_pembelajaran = $request->kelas_guru;
        $counter_created = 0;
        $counter_deleted = 0;
        $counter_updated = 0;

        foreach ($data_pembelajaran as $data) {
            
            $id_kelas = $data['id_kelas'];
            
            // Ambil input guru
            $raw_guru = $data['id_guru'] ?? null;

            // LOGIKA BARU: Jika 0 atau kosong, set NULL.
            if (empty($raw_guru) || $raw_guru === "0" || $raw_guru === 0) {
                $id_guru = null;
            } else {
                $id_guru = $raw_guru;
            }

            $is_active = isset($data['active']); 

            if ($is_active) {
                
                // Cek data existing
                $existing = Pembelajaran::where('id_mapel', $id_mapel_edit)
                                        ->where('id_kelas', $id_kelas)
                                        ->first();
                
                if (!$existing) {
                    // Create baru jika belum ada
                    Pembelajaran::create([
                        'id_mapel' => $id_mapel_edit, 
                        'id_kelas' => $id_kelas,
                        'id_guru'  => $id_guru, 
                    ]);
                    $counter_created++;
                } else {
                    // Update jika id_guru berbeda (termasuk jika berubah jadi NULL)
                    if ($existing->id_guru != $id_guru) {
                        $existing->update(['id_guru' => $id_guru]);
                        $counter_updated++;
                    }
                }
            } else {
                // Skema Delete: Jika Tidak Aktif (Uncheck), Hapus Data
                $deleted = Pembelajaran::where('id_mapel', $id_mapel_edit) 
                                       ->where('id_kelas', $id_kelas)
                                       ->delete();
                if($deleted) $counter_deleted++;
            }
        }

        $message = "Berhasil memperbarui tautan pembelajaran (Dibuat: $counter_created, Diperbarui: $counter_updated, Dihapus: $counter_deleted).";
        return redirect()->route('master.pembelajaran.index')->with('success', $message);
    }

    // 🟥 Hapus data pembelajaran
    public function destroy($id)
    {
        $pembelajaran = Pembelajaran::findOrFail($id);
        $pembelajaran->delete();

        return redirect()->route('master.pembelajaran.index')
            ->with('success', 'Data pembelajaran berhasil dihapus.');
    }

    // Export PDF (Menerima parameter filter)
    public function exportPdf(Request $request)
    {
        $query = Pembelajaran::with(['mapel', 'kelas', 'guru'])
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel');
            
        // Terapkan Filter dari Request
        if ($request->id_mapel) { $query->where('pembelajaran.id_mapel', $request->id_mapel); }
        if ($request->id_kelas) { $query->where('pembelajaran.id_kelas', $request->id_kelas); }
        $idGuruFilter = $request->id_guru;
        if (!empty($idGuruFilter) && $idGuruFilter != 0) { $query->where('pembelajaran.id_guru', $idGuruFilter); }


        $pembelajaran = $query
            ->orderBy('mata_pelajaran.urutan', 'asc')
            ->orderBy('pembelajaran.id_pembelajaran', 'asc')
            ->get();
            
        $pdf = Pdf::loadView('exports.data_pembelajaran_pdf', compact('pembelajaran'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('data_pembelajaran_filtered.pdf');
    }


    // Export CSV (Menerima parameter filter)
    public function exportCsv(Request $request)
    {
        $query = Pembelajaran::with(['mapel', 'kelas', 'guru'])
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel');

        // Terapkan Filter dari Request
        if ($request->id_mapel) { $query->where('pembelajaran.id_mapel', $request->id_mapel); }
        if ($request->id_kelas) { $query->where('pembelajaran.id_kelas', $request->id_kelas); }
        $idGuruFilter = $request->id_guru;
        if (!empty($idGuruFilter) && $idGuruFilter != 0) { $query->where('pembelajaran.id_guru', $idGuruFilter); }

        $pembelajaran = $query
            ->orderBy('mata_pelajaran.urutan', 'asc')
            ->orderBy('pembelajaran.id_pembelajaran', 'asc')
            ->get();

        $filename = 'data_pembelajaran_filtered.csv';
        $handle = fopen($filename, 'w+');

        fputcsv($handle, ['No', 'Mata Pelajaran', 'Tingkat', 'Kelas', 'Jurusan', 'Guru Mapel']);

        foreach ($pembelajaran as $i => $p) {
            fputcsv($handle, [
                $i + 1,
                $p->mapel->nama_mapel ?? '-',
                $p->kelas->tingkat ?? '-',
                $p->kelas->nama_kelas ?? '-',
                $p->kelas->jurusan ?? '-',
                $p->guru->nama_guru ?? '-',
            ]);
        }

        fclose($handle);

        return Response::download($filename)->deleteFileAfterSend(true);
    }
}