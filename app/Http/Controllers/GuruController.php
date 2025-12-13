<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\DetailGuru;
use App\Models\Pembelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::query();

        // Terapkan logika pencarian jika parameter 'search' ada
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            
            // Mencari nama guru, NIP, atau NUPTK
            $query->where(function ($q) use ($search) {
                $q->where('nama_guru', 'like', '%' . $search . '%')
                  ->orWhere('nip', 'like', '%' . $search . '%')
                  ->orWhere('nuptk', 'like', '%' . $search . '%');
            });
        }
        
        // Terapkan Pagination dan ambil hasil
        $gurus = $query->paginate(20)->withQueryString(); 

        return view('guru.index', compact('gurus'));
    }

    public function create()
    {
        // Ambil data Kelas dan Mata Pelajaran dari database
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $mapelList = MataPelajaran::orderBy('nama_mapel')->get(); 

        // Kirim kedua variabel tersebut ke view
        return view('guru.create', compact('kelasList', 'mapelList'));
    }

    public function show($id)
    {
        // Memuat Model Guru berdasarkan ID, sekaligus mengambil data relasi (eager loading)
        // detailGuru, pembelajaran, pembelajaran.kelas, dan pembelajaran.mapel
        $guru = Guru::with([
            'detailGuru', 
            'pembelajaran.kelas', 
            'pembelajaran.mapel'
        ])->findOrFail($id);

        // Kirim data yang sudah dimuat ke view 'guru.show'
        return view('guru.show', compact('guru'));
    }

    /**
     * Simpan guru baru yang baru dibuat ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Data Gabungan
        $request->validate([
            // Field Model Guru
            'nama_guru' => 'required|string|max:255',
            'nip' => 'nullable|string|max:18|unique:guru,nip',
            'nuptk' => 'nullable|string|max:16|unique:guru,nuptk',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'jenis_ptk' => 'required|string|max:100',
            'role' => 'required|string|max:100', 
            'status' => 'required|in:aktif,non-aktif',
            
            // Field Model DetailGuru (Contoh beberapa field penting)
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable|string|max:50',
            'alamat' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:15',
            'email' => 'nullable|email|unique:detail_guru,email', 
            'nik' => 'nullable|string|max:20|unique:detail_guru,nik',
            
            // Field Model Pembelajaran (Jika di-input sebagai array)
            'pembelajaran.*.id_kelas' => 'nullable|integer|exists:kelas,id_kelas',
            'pembelajaran.*.id_mapel' => 'nullable|integer|exists:mata_pelajaran,id_mapel',
        ]);

        // 2. Database Transaction
        DB::beginTransaction();

            try {
                // A. Create Model Guru - Ganti getFillable() dengan array field
            $guru = Guru::create($request->only([
                'nama_guru', 'nip', 'nuptk', 'jenis_kelamin', 'jenis_ptk', 'role', 'status'
            ]));

            // B. Create Model DetailGuru - Ganti getFillable() dengan array field
            $detailData = $request->only([
                'tempat_lahir', 'tanggal_lahir', 'status_kepegawaian', 'agama', 'alamat', 'rt', 'rw', 'dusun', 'kelurahan', 'kecamatan', 'kode_pos', 'no_telp', 'no_hp', 'email', 'tugas_tambahan', 'sk_cpns', 'tgl_cpns', 'sk_pengangkatan', 'tmt_pengangkatan', 'lembaga_pengangkatan', 'pangkat_gol', 'sumber_gaji', 'nama_ibu_kandung', 'status_perkawinan', 'nama_suami_istri', 'nip_suami_istri', 'pekerjaan_suami_istri', 'tmt_pns', 'lisensi_kepsek', 'diklat_kepengawasan', 'keahlian_braille', 'keahlian_isyarat', 'npwp', 'nama_wajib_pajak', 'kewarganegaraan', 'bank', 'norek_bank', 'nama_rek', 'nik', 'no_kk', 'karpeg', 'karis_karsu', 'lintang', 'bujur', 'nuks'
            ]);
            
            // ... (Logika create DetailGuru dan Pembelajaran)
            $guru->detailGuru()->create($detailData);

            // ... (Logika Pembelajaran)
            
            DB::commit();
            return redirect()->route('guru.index')->with('success', 'Data Guru berhasil ditambahkan!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error saat menyimpan data Guru: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data guru: ' . $e->getMessage());
        }
    }


    // --- METHOD EDIT ---
    /**
     * Tampilkan form untuk mengedit guru tertentu.
     */
    public function edit($id)
    {
        // Eager loading relasi DetailGuru dan Pembelajaran
        $guru = Guru::with('detailGuru', 'pembelajaran')
                    ->findOrFail($id);
        
        // Asumsi: Anda juga butuh daftar Kelas dan Mata Pelajaran untuk form Pembelajaran
        $kelasList = Kelas::all(); // Ganti Kelas dengan nama model Anda
        $mapelList = MataPelajaran::all(); // Ganti MataPelajaran dengan nama model Anda

        return view('guru.edit', compact('guru', 'kelasList', 'mapelList'));
    }

    /**
     * Perbarui data guru tertentu di database.
     */
    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        // 1. Validasi Data Gabungan
        $request->validate([
            // ... (Validasi Guru dan DetailGuru yang sama)
            'nip' => ['nullable', 'string', 'max:18', Rule::unique('guru', 'nip')->ignore($guru->id_guru, 'id_guru')],
            // ...
            
            // Field Model Pembelajaran (Dibuat OPSI)
            'pembelajaran.*.id_kelas' => 'nullable|integer|exists:kelas,id_kelas',
            'pembelajaran.*.id_mapel' => 'nullable|integer|exists:mata_pelajaran,id_mapel',
        ]);

        DB::beginTransaction();

            try {
                // A. Update Model Guru - Ganti getFillable() dengan array field
            $guru->update($request->only([
                'nama_guru', 'nip', 'nuptk', 'jenis_kelamin', 'jenis_ptk', 'role', 'status'
            ]));

            // B. Update Model DetailGuru - Ganti getFillable() dengan array field
            $detailData = $request->only([
                'tempat_lahir', 'tanggal_lahir', 'status_kepegawaian', 'agama', 'alamat', 'rt', 'rw', 'dusun', 'kelurahan', 'kecamatan', 'kode_pos', 'no_telp', 'no_hp', 'email', 'tugas_tambahan', 'sk_cpns', 'tgl_cpns', 'sk_pengangkatan', 'tmt_pengangkatan', 'lembaga_pengangkatan', 'pangkat_gol', 'sumber_gaji', 'nama_ibu_kandung', 'status_perkawinan', 'nama_suami_istri', 'nip_suami_istri', 'pekerjaan_suami_istri', 'tmt_pns', 'lisensi_kepsek', 'diklat_kepengawasan', 'keahlian_braille', 'keahlian_isyarat', 'npwp', 'nama_wajib_pajak', 'kewarganegaraan', 'bank', 'norek_bank', 'nama_rek', 'nik', 'no_kk', 'karpeg', 'karis_karsu', 'lintang', 'bujur', 'nuks'
            ]);
            
            // ... (Logika update DetailGuru dan Pembelajaran)
            $guru->detailGuru()->updateOrCreate(
                ['id_guru' => $guru->id_guru],
                $detailData
            );
            
            // ... (Logika Pembelajaran)

            DB::commit();
            return redirect()->route('guru.index')->with('success', 'Data Guru dan detailnya berhasil diperbarui!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error saat memperbarui data Guru: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data guru: ' . $e->getMessage());
        }
    }


    // --- METHOD DELETE (DESTROY) ---
    /**
     * Hapus guru tertentu dari database.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $guru = Guru::findOrFail($id);
            
            // Hapus data DetailGuru terkait 
            if ($guru->detailGuru) {
                $guru->detailGuru->delete();
            }

            // Hapus data Pembelajaran terkait 
            $guru->pembelajaran()->delete(); 

            // Hapus Model Guru utama
            $guru->delete();

            DB::commit();
            return redirect()->route('guru.index')->with('success', 'Data Guru dan semua relasinya berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data guru: ' . $e->getMessage());
        }
    }
    
    
        // PDF CSV
    public function exportPdf()
    {
        $guru = Guru::all();

        $pdf = Pdf::loadView('exports.data_guru_pdf', [
            'guru' => $guru
        ]);

        return $pdf->download('data-guru.pdf');
    }

    public function exportCsv()
    {
        $guru = Guru::all();

        return response()->streamDownload(function() use ($guru) {

            $file = fopen('php://output', 'w');

            // HEADER CSV
            fputcsv($file, ['No','Nama Guru','NIP','NUPTK','Jenis Kelamin','Jenis PTK','Role','Status']);

            $no = 1;

            foreach ($guru as $g) {
                fputcsv($file, [
                    $no++,
                    $g->nama_guru,
                    $g->nip,
                    $g->nuptk,
                    $g->jenis_kelamin,
                    $g->jenis_ptk,
                    $g->role,
                    $g->status,
                ]);
            }

            fclose($file);

        }, 'data-guru.csv', [
            'Content-Type' => 'text/csv'
        ]);
    }


    public function importCsv(Request $request)
    {
        // Pastikan validasi file ada
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $csv = array_map('str_getcsv', file($request->file('file')));

        $startImport = false;
        $processedCount = 0;
        $skippedCount = 0;
        $headerDitemukan = false;

        // Helper untuk tanggal
        $fixDate = function ($date) {
            if (empty(trim($date))) return null;
            $d = date_create($date);
            return $d ? date_format($d, 'Y-m-d') : null;
        };
        $fixString = function ($value) { 
            $value = trim($value);
            return empty($value) || $value === '-' ? null : $value;
        };

        DB::beginTransaction();

        try {

            foreach ($csv as $row) {

                // Skip row kosong dan header
                if (empty(trim($row[1] ?? ''))) continue;
                if (strtolower(trim($row[1] ?? '')) === 'nama') continue;

                // Deteksi baris data asli (Jika logika project lama Anda membutuhkan ini)
                if (!$startImport) {
                    if (strlen(trim($row[1] ?? '')) > 2) {
                        $startImport = true;
                    } else {
                        continue;
                    }
                }
                
                // --- LOGIKA JENIS KELAMIN FIX ---
                $jk = strtoupper(trim($row[3] ?? ''));
                
                // Tentukan nilai yang akan di-insert: Laki-laki, Perempuan, atau NULL (jika tidak valid)
                $jenisKelaminValue = null;
                if ($jk === 'L') {
                    $jenisKelaminValue = 'Laki-laki';
                } elseif ($jk === 'P') {
                    $jenisKelaminValue = 'Perempuan';
                }
                // --------------------------------

                // FIX KRUSIAL: Jika Jenis Kelamin tidak valid (NULL), SKIP baris ini
                if ($jenisKelaminValue === null) {
                    $skippedCount++;
                    continue; // Hentikan pemrosesan baris ini dan lanjut ke baris berikutnya
                }

                // =====================
                // Insert ke tabel guru
                // =====================
                $guru = Guru::create([
                    'id_pembelajaran' => null, // sesuaikan kolom CSV
                    'nama_guru'       => $row[1] ?? null,
                    'nip'             => $row[6] ?? null,
                    'nuptk'           => $row[2] ?? null,
                    'jenis_kelamin'   => $jenisKelaminValue, // Nilai sudah dijamin Laki-laki atau Perempuan
                    'jenis_ptk'       => $row[8] ?? null,
                    'role'            => 'guru_mapel',
                    'status'          => 'aktif',
                ]);

                // =========================
                // Insert ke detail_guru
                // ... (lanjutan kolom detail guru) ...
                // =========================
                DetailGuru::create([
                    'id_guru'                 => $guru->id_guru,
                    'tempat_lahir'            => $row[4] ?? null,
                    'tanggal_lahir'           => $fixDate($row[5] ?? null),
                    'status_kepegawaian'      => $row[7] ?? null,
                    'agama'                   => $row[9] ?? null,
                    'alamat'                  => $row[10] ?? null,
                    // Kolom detail lainnya
                    'rt'                      => $row[11] ?? null,
                    'rw'                      => $row[12] ?? null,
                    'dusun'                   => $row[13] ?? null,
                    'kelurahan'               => $row[14] ?? null,
                    'kecamatan'               => $row[15] ?? null,
                    'kode_pos'                => $row[16] ?? null,
                    'no_telp'                 => $row[17] ?? null,
                    'no_hp'                   => $row[18] ?? null,
                    'email'                   => $row[19] ?? null,
                    'tugas_tambahan'          => $row[20] ?? null,
                    'sk_cpns'                 => $row[21] ?? null,
                    'tgl_cpns'                => $fixDate($row[22] ?? null),
                    'sk_pengangkatan'         => $row[23] ?? null,
                    'tmt_pengangkatan'        => $fixDate($row[24] ?? null),
                    'lembaga_pengangkatan'    => $row[25] ?? null,
                    'pangkat_gol'             => $row[26] ?? null,
                    'sumber_gaji'             => $row[27] ?? null,
                    'nama_ibu_kandung'        => $row[28] ?? null,
                    'status_perkawinan'       => $row[29] ?? null,
                    'nama_suami_istri'        => $row[30] ?? null,
                    'nip_suami_istri'         => $row[31] ?? null,
                    'pekerjaan_suami_istri'   => $row[32] ?? null,
                    'tmt_pns'                 => $fixDate($row[33] ?? null),
                    'lisensi_kepsek'          => $row[34] ?? null,
                    'diklat_kepengawasan'     => $row[35] ?? null,
                    'keahlian_braille'        => $row[36] ?? null,
                    'keahlian_isyarat'        => $row[37] ?? null,
                        // ... (lanjutan kolom detail guru) ...
                    'nuks'                    => $row[50] ?? null,
                ]);


                $processedCount++;
            }

            DB::commit();
            
            $message = "Data CSV berhasil diimport! Total diproses: $processedCount guru. Dilewati: $skippedCount baris.";

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            // Pastikan Anda melihat log error di storage/logs/laravel.log untuk detailnya
            \Log::error("Gagal saat mengimpor CSV Guru: " . $e->getMessage());
            return back()->with('error', 'Gagal mengimpor data. Pesan error: ' . $e->getMessage());
        }
    }



}