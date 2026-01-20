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
    public function dataPembelajaran(Request $request) // 🛑 Menerima Request $request
    {
        // 1. Inisialisasi Query (Base Query dengan Join untuk Pengurutan Mapel)
        // $query = Pembelajaran::select('pembelajaran.*')
        //     ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel');
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
        // Logic filter Guru: Hanya terapkan WHERE jika id_guru TIDAK kosong dan TIDAK nol (yang berarti 'Semua Guru' dipilih)
        if (!empty($idGuruFilter) && $idGuruFilter != 0) {
            $query->where('pembelajaran.id_guru', $idGuruFilter);
        }

        // 3. Pengurutan & Eksekusi
        $pembelajaran = $query
            ->orderBy('mata_pelajaran.urutan', 'asc')
            ->orderBy('mata_pelajaran.nama_mapel', 'asc') 
            ->with(['mapel', 'kelas', 'guru'])
            ->get(); // Eksekusi query dengan filter

        // 4. Ambil data untuk dropdown filter
        // $mapel_list = MataPelajaran::orderBy('urutan')->orderBy('nama_mapel')->get();
        $mapel_list = MataPelajaran::where('is_active', 1)
            ->orderBy('urutan')
            ->orderBy('nama_mapel')
            ->get();

        $kelas_list = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $guru_list = Guru::orderBy('nama_guru')->get(); 

        // 5. Kirim data ke view
        return view('pembelajaran.index', compact('pembelajaran', 'mapel_list', 'kelas_list', 'guru_list'));
    }

    // 🟫 Tampilkan form create
    public function create()
    {
        // $mapel = MataPelajaran::orderBy('urutan', 'asc')->orderBy('nama_mapel', 'asc')->get();
        $mapel = MataPelajaran::where('is_active', 1)
            ->orderBy('urutan', 'asc')
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
            // 'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
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
            $id_guru = $data['id_guru'] ?? self::DEFAULT_GURU_ID; 
            $is_active = isset($data['active']); 

            if ($is_active) {
                // Jika aktif, pastikan ID Guru valid (bukan 0 atau kosong)
                if (empty($id_guru) || $id_guru === "" || $id_guru == 0) {
                    $id_guru = self::DEFAULT_GURU_ID;
                }
                
                $existing = Pembelajaran::where('id_mapel', $id_mapel)
                                        ->where('id_kelas', $id_kelas)
                                        ->first();
                
                if (!$existing) {
                    // Create baru
                    Pembelajaran::create([
                        'id_mapel' => $id_mapel,
                        'id_kelas' => $id_kelas,
                        'id_guru'  => $id_guru, // Menggunakan ID valid atau Placeholder ID 1
                    ]);
                    $counter++;
                } else {
                    // Update guru yang ada
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
            $id_guru = $data['id_guru'] ?? self::DEFAULT_GURU_ID; 
            $is_active = isset($data['active']); 

            if ($is_active) {
                // Jika aktif, pastikan ID Guru valid (bukan 0 atau kosong)
                if (empty($id_guru) || $id_guru === "" || $id_guru == 0) {
                    $id_guru = self::DEFAULT_GURU_ID;
                }
                
                $existing = Pembelajaran::where('id_mapel', $id_mapel_edit)
                                        ->where('id_kelas', $id_kelas)
                                        ->first();
                
                if (!$existing) {
                    // Create baru
                    Pembelajaran::create([
                        'id_mapel' => $id_mapel_edit, 
                        'id_kelas' => $id_kelas,
                        'id_guru'  => $id_guru, // Menggunakan ID valid (misal 1)
                    ]);
                    $counter_created++;
                } else {
                    // Update guru yang ada jika berbeda
                    if ($existing->id_guru != $id_guru) {
                        $existing->update(['id_guru' => $id_guru]);
                        $counter_updated++;
                    }
                }
            } else {
                // Skema Delete: Jika Tidak Aktif, Hapus Record Pembelajaran
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