<?php
// File: app/Http/Controllers/PesertaEkskulController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ekskul;
use App\Models\EkskulSiswa;
use App\Models\Siswa;
use App\Models\Kelas; 
use Illuminate\Support\Facades\DB;

class PesertaEkskulController extends Controller
{
    // === 1. Tampilkan Daftar Peserta Ekskul (Index Agregat) ===
    public function index(Request $request)
    {
        $ekskuls = Ekskul::orderBy('nama_ekskul')->get();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        $filter_id_ekskul = $request->input('id_ekskul');
        $filter_id_kelas = $request->input('id_kelas'); 
        $filter_nama_siswa = $request->input('nama_siswa');
        
        // 1. Inisiasi Query Dasar: Ambil data EkskulSiswa khusus siswa aktif
        $peserta = EkskulSiswa::with(['siswa.kelas', 'ekskul'])
                               ->whereHas('siswa', function($query) {
                                   $query->where('status', 'aktif');
                               })
                               ->orderBy('id_ekskul', 'asc')
                               ->orderBy('id_siswa', 'asc'); // Ordering agar data tidak acak

        // 2. Terapkan Filter
        if ($filter_id_ekskul) {
            $peserta->where('id_ekskul', $filter_id_ekskul);
        }
        
        if ($filter_id_kelas) {
            $peserta->whereHas('siswa', function($query) use ($filter_id_kelas) {
                $query->where('status', 'aktif')
                      ->where('id_kelas', $filter_id_kelas);
            });
        }

        if ($filter_nama_siswa) {
            $peserta->whereHas('siswa', function($query) use ($filter_nama_siswa) {
                $query->where('status', 'aktif')
                      ->where('nama_siswa', 'like', '%' . $filter_nama_siswa . '%');
            });
        }
        
        // 3. Eksekusi Query
        $peserta = $peserta->paginate(50)->appends($request->query());

        // 4. Kirim data ke view (menggunakan $peserta, bukan $data_kelompok)
        return view('ekskul.siswa_index', compact(
            'ekskuls', 
            'kelas', 
            'peserta', 
            'filter_id_ekskul', 
            'filter_id_kelas',
            'filter_nama_siswa' 
        ));
    }

    // === 2. Tampilkan Form Tambah Peserta Ekskul ===
    public function create()
    {
        $ekskuls = Ekskul::orderBy('nama_ekskul')->get();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        $siswas = Siswa::select('id_siswa', 'nama_siswa', 'id_kelas')
                       ->where('status', 'aktif')
                       ->with('kelas:id_kelas,nama_kelas') 
                       ->orderBy('nama_siswa')
                       ->get();

        $pesertaEkskul = EkskulSiswa::select('id_ekskul', 'id_siswa')
        ->get()
        ->groupBy('id_ekskul');

        return view('ekskul.siswa_create', compact('ekskuls', 'kelas', 'siswas', 'pesertaEkskul'));
    }

    // === 3. Simpan Peserta Ekskul ===
    public function store(Request $request)
    {
        if ($request->has('siswa_ids')) {
            $filtered_ids = array_filter($request->input('siswa_ids'));
            if (!empty($filtered_ids)) {
                $request->merge(['siswa_ids' => $filtered_ids]);
            } else {
                $request->request->remove('siswa_ids');
            }
        }
        
        $validatedData = $request->validate([
            'id_ekskul' => 'required|exists:ekskul,id_ekskul', 
            'id_kelas_filter' => 'required|exists:kelas,id_kelas', 
            'siswa_ids' => 'required|array|min:1', 
            'siswa_ids.*' => 'exists:siswa,id_siswa', 
        ], [
            'siswa_ids.required' => 'Anda harus memilih minimal satu siswa peserta.',
            'siswa_ids.min' => 'Anda harus memilih minimal satu siswa peserta.',
        ]);

        $id_ekskul = $validatedData['id_ekskul'];
        $id_kelas_filter = $validatedData['id_kelas_filter'];
        $submitted_siswa_ids = $validatedData['siswa_ids'];
        
        $valid_siswa_ids = Siswa::whereIn('id_siswa', $submitted_siswa_ids)
                                ->where('id_kelas', $id_kelas_filter)
                                ->where('status', 'aktif')
                                ->pluck('id_siswa')
                                ->toArray();
        
        $existing_siswa_ids = EkskulSiswa::where('id_ekskul', $id_ekskul)
                                         ->whereIn('id_siswa', $valid_siswa_ids)
                                         ->pluck('id_siswa')
                                         ->toArray();

        $siswa_to_insert = array_diff($valid_siswa_ids, $existing_siswa_ids);

        if (empty($siswa_to_insert)) {
            $message = 'Tidak ada siswa baru yang dapat ditambahkan (semua siswa yang valid sudah terdaftar).';
            return redirect()->route('master.ekskul.siswa.create')->withInput()->with('error', $message);
        }

        $data = [];
        foreach ($siswa_to_insert as $siswa_id) {
            $data[] = [
                'id_ekskul' => $id_ekskul,
                'id_siswa' => $siswa_id,
            ];
        }
        
        EkskulSiswa::insert($data); 

        $count_inserted = count($data);
        $message = "Berhasil menambahkan {$count_inserted} peserta ke ekstrakurikuler.";
        
        return redirect()->route('master.ekskul.siswa.index')->with('success', $message);
    }
    
    // === 4. Tampilkan Form Edit Peserta Ekskul (Dipindahkan dari EkskulController lama) ===
    public function edit(Request $request, $id_ekskul) 
    {
        // 🛑 BARU: Ambil ID Kelas dari query parameter yang dikirim dari Index
        $preselected_id_kelas = $request->query('filter_kelas');
        
        // 1. Ambil data Ekskul yang diedit
        $ekskul_edit = Ekskul::with('guru')->findOrFail($id_ekskul);
        
        // 2. Ambil semua Kelas
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        // 3. Ambil semua Siswa Aktif
        $siswas = Siswa::select('id_siswa', 'nama_siswa', 'id_kelas')
                       ->where('status', 'aktif')
                       ->with('kelas:id_kelas,nama_kelas') 
                       ->orderBy('nama_siswa')
                       ->get();

        // 4. Ambil ID siswa yang sudah terdaftar di ekskul ini
        $terdaftar_ids = EkskulSiswa::where('id_ekskul', $id_ekskul)
                                    ->pluck('id_siswa')
                                    ->toArray();

        // Kita tidak lagi menggunakan $siswa_ekskul_edit
        return view('ekskul.siswa_edit', compact(
            'ekskul_edit', // Ekskul yang sedang diedit
            'kelas', 
            'siswas',
            'preselected_id_kelas', // ID Kelas yang dipilih sebelumnya
            'terdaftar_ids' // Daftar ID siswa yang sudah tercentang
        ));
    }

    // === 5. Update Peserta Ekskul (Dipindahkan dari EkskulController lama) ===
    public function update(Request $request, $id_ekskul) // Parameter harus $id_ekskul
    {
        // 1. Validasi Input Dasar
        $validatedData = $request->validate([
            'siswa_ids' => 'nullable|array', 
            'siswa_ids.*' => 'exists:siswa,id_siswa', 
        ], [
            // Opsional: Pesan error kustom
            'siswa_ids.array' => 'Daftar siswa tidak valid.',
        ]);
        
        // 2. Ambil Model Ekskul
        $ekskul = Ekskul::findOrFail($id_ekskul);
        
        // 3. Bersihkan array dari nilai kosong dan pastikan hanya siswa aktif
        $submitted_siswa_ids = array_filter($validatedData['siswa_ids'] ?? []);
        $valid_active_ids = Siswa::whereIn('id_siswa', $submitted_siswa_ids)
                                 ->where('status', 'aktif')
                                 ->pluck('id_siswa')
                                 ->toArray();

        // Pertahankan ID siswa non-aktif (lulus/keluar) yang sudah tersimpan sebelumnya agar riwayat tidak hilang
        $non_active_existing_ids = EkskulSiswa::where('ekskul_siswa.id_ekskul', $id_ekskul)
            ->join('siswa', 'ekskul_siswa.id_siswa', '=', 'siswa.id_siswa')
            ->where('siswa.status', '!=', 'aktif')
            ->pluck('ekskul_siswa.id_siswa')
            ->toArray();

        $final_sync_ids = array_unique(array_merge($valid_active_ids, $non_active_existing_ids));

        // 4. Sinkronisasi Data
        $result = $ekskul->peserta()->sync($final_sync_ids);

        // 5. Notifikasi dan Pengalihan
        $count_added = count($result['attached']);
        $count_removed = count($result['detached']);
        $total_changed = $count_added + $count_removed;
        
        if ($total_changed == 0) {
             return redirect()->route('master.ekskul.siswa.index')->with('warning', 'Tidak ada perubahan pada peserta ekstrakurikuler.');
        } else {
             $message = "Berhasil menyinkronkan peserta. Ditambahkan: {$count_added}, Dihapus: {$count_removed}.";
             return redirect()->route('master.ekskul.siswa.index')->with('success', $message);
        }
    }

    // === 6. Hapus Peserta Ekskul ===
    public function destroy($id_ekskul_siswa)
    {
        $data = EkskulSiswa::findOrFail($id_ekskul_siswa); 
        $data->delete();

        return redirect()->route('master.ekskul.siswa.index')->with('success', 'Peserta ekstrakurikuler berhasil dihapus.');
    }
}