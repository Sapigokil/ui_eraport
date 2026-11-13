<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Uji;
use App\Models\UjiSoal;
use App\Models\UjiHasil;
use App\Models\UjiJawaban;
use Illuminate\Support\Facades\Storage;

class SbaController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil daftar ID user yang memiliki asesmen dari tabel ujis secara langsung
        $authorIds = Uji::select('user_id')->distinct()->pluck('user_id');
        $authors = \App\Models\User::whereIn('id', $authorIds)->get(['id', 'name']);

        // 2. Query Utama Uji
        $query = Uji::with('author')->withCount('hasils');

        // Filter Search (Kode Uji, Judul, Deskripsi)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_uji', 'like', "%{$search}%")
                ->orWhere('judul', 'like', "%{$search}%")
                ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter Author
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Sorting Kolom (Default: id DESC / Latest)
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['id', 'kode_uji', 'judul', 'user_id', 'waktu_mulai', 'status'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest('id');
        }

        // Per Page Pagination (Default: 10)
        $perPage = (int) $request->get('per_page', 10);
        $ujis = $query->paginate($perPage)->withQueryString();

        return view('sba.index', compact('ujis', 'authors'));
    }

    public function create()
    {
        return view('sba.form');
    }

    public function edit(Uji $uji)
    {
        $uji->load('soals');
        return view('sba.form', compact('uji'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'                 => 'required|string|max:255',
            'durasi_menit'          => 'required|integer|min:0',
            'toleransi_pelanggaran' => 'required|integer|min:1',
            'waktu_mulai'           => 'nullable|date',
            'waktu_selesai'         => 'nullable|date|after_or_equal:waktu_mulai',
            'maksimal_percobaan'    => 'nullable|integer|min:1',
            'soals'                 => 'required|array|min:1',
            'soals.*.pertanyaan'    => 'required|string',
            'soals.*.tipe'          => 'required|in:pg,pg_kompleks,esai',
            'soals.*.media'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // 1. Buat Ujian Utama
        $uji = Uji::create([
            'user_id'               => auth()->id(),
            'kode_uji'              => strtoupper(\Illuminate\Support\Str::random(6)),
            'judul'                 => $request->judul,
            'deskripsi'             => $request->deskripsi,
            'waktu_mulai'           => $request->waktu_mulai,
            'waktu_selesai'         => $request->waktu_selesai,
            'durasi_menit'          => $request->durasi_menit,
            'acak_soal'             => $request->boolean('acak_soal'),
            'open_book'             => $request->boolean('open_book'),
            'tampilkan_nilai'       => $request->boolean('tampilkan_nilai'),
            'buka_untuk_umum'       => $request->boolean('buka_untuk_umum'),
            'toleransi_pelanggaran' => $request->toleransi_pelanggaran,
            'maksimal_percobaan'    => $request->maksimal_percobaan ?? 1,
            'ulangi_jika_gagal'     => $request->boolean('ulangi_jika_gagal'),
            'status'                => $request->status ?? 'draft',
        ]);

        // 2. Simpan Setiap Soal & File Media (Jika Ada)
        foreach ($request->soals as $index => $soalData) {
            $pathMedia = null;

            if ($request->hasFile("soals.{$index}.media")) {
                $pathMedia = $request->file("soals.{$index}.media")->store('soal-images', 'public');
            }

            UjiSoal::create([
                'uji_id'        => $uji->id,
                'tipe'          => $soalData['tipe'],
                'pertanyaan'    => $soalData['pertanyaan'],
                'media'         => $pathMedia,
                'opsi_jawaban'  => isset($soalData['opsi_jawaban']) ? array_values($soalData['opsi_jawaban']) : null,
                'kunci_jawaban' => isset($soalData['kunci_jawaban']) ? (array) $soalData['kunci_jawaban'] : null,
                'bobot_nilai'   => $soalData['bobot_nilai'] ?? 10,
            ]);
        }

        return redirect()->route('sba.index')->with('success', 'Ujian dan soal berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $uji = Uji::with('soals')->findOrFail($id);

        $request->validate([
            'judul'                 => 'required|string|max:255',
            'durasi_menit'          => 'required|integer|min:0',
            'toleransi_pelanggaran' => 'required|integer|min:1',
            'waktu_mulai'           => 'nullable|date',
            'waktu_selesai'         => 'nullable|date|after_or_equal:waktu_mulai',
            'maksimal_percobaan'    => 'nullable|integer|min:1',
            'soals'                 => 'required|array|min:1',
            'soals.*.pertanyaan'    => 'required|string',
            'soals.*.media'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // 1. Update Data Utama Ujian
        $uji->update([
            'judul'                 => $request->judul,
            'deskripsi'             => $request->deskripsi,
            'waktu_mulai'           => $request->waktu_mulai,
            'waktu_selesai'         => $request->waktu_selesai,
            'durasi_menit'          => $request->durasi_menit,
            'acak_soal'             => $request->boolean('acak_soal'),
            'open_book'             => $request->boolean('open_book'),
            'tampilkan_nilai'       => $request->boolean('tampilkan_nilai'),
            'buka_untuk_umum'       => $request->boolean('buka_untuk_umum'),
            'toleransi_pelanggaran' => $request->toleransi_pelanggaran,
            'maksimal_percobaan'    => $request->maksimal_percobaan ?? 1,
            'ulangi_jika_gagal'     => $request->boolean('ulangi_jika_gagal'),
            'status'                => $request->status,
        ]);

        // 2. Hapus Soal di Database yang Dihapus oleh User di Form
        $inputSoalIds = array_filter(array_column($request->soals, 'id'));
        $uji->soals()->whereNotIn('id', $inputSoalIds)->delete();

        // 3. Simpan / Update Soals
        foreach ($request->soals as $index => $soalData) {
            $soalId = $soalData['id'] ?? null;
            $soalEksis = $soalId ? UjiSoal::find($soalId) : new UjiSoal();

            $pathMedia = $soalEksis->media;

            // Cek jika ada request hapus gambar eksisting
            if (isset($soalData['remove_media']) && $soalData['remove_media'] == '1') {
                $pathMedia = null;
            }

            // Cek jika ada file gambar baru yang diunggah
            if ($request->hasFile("soals.{$index}.media")) {
                $pathMedia = $request->file("soals.{$index}.media")->store('soal-images', 'public');
            }

            $soalEksis->fill([
                'uji_id'        => $uji->id,
                'tipe'          => $soalData['tipe'],
                'pertanyaan'    => $soalData['pertanyaan'],
                'media'         => $pathMedia,
                'opsi_jawaban'  => isset($soalData['opsi_jawaban']) ? array_values($soalData['opsi_jawaban']) : null,
                'kunci_jawaban' => isset($soalData['kunci_jawaban']) ? (array) $soalData['kunci_jawaban'] : null,
                'bobot_nilai'   => $soalData['bobot_nilai'] ?? 10,
            ]);

            $soalEksis->save();
        }

        return redirect()->route('sba.index')->with('success', 'Perubahan ujian dan gambar soal berhasil disimpan!');
    }

    /**
     * Hapus 1 Butir Soal Spesifik (Otomatis hapus file media via Model Event UjiSoal)
     */
    public function destroySoal($soal_id)
    {
        $soal = \App\Models\UjiSoal::findOrFail($soal_id);
        
        // Memanggil delete() akan memicu static::deleting pada Model UjiSoal untuk menghapus file fisik di storage
        $soal->delete();

        return response()->json([
            'status' => 'success', 
            'message' => 'Soal dan file media berhasil dihapus dari server.'
        ]);
    }

    /**
     * Hapus Seluruh Ujian (Otomatis hapus seluruh file media soal-soalnya)
     */
    public function destroy($id)
    {
        $uji = Uji::findOrFail($id);
        
        // Memanggil delete() pada Uji akan memicu static::deleting di Model Uji yang menghapus seluruh UjiSoal beserta gambar fisiknya
        $uji->delete();

        return redirect()->route('sba.index')->with('success', 'Ujian beserta seluruh gambar soal berhasil dihapus!');
    }

    /**
     * Halaman Index Daftar Hasil Peserta / Asesi per Ujian
     */
    public function hasilIndex(Request $request, $uji_id)
    {
        $uji = Uji::findOrFail($uji_id);

        // Pemanggilan Query Utama
        $hasils = $this->getFilteredHasilQuery($request, $uji->id)->paginate((int) $request->get('per_page', 10))->withQueryString();

        return view('sba.hasil', compact('uji', 'hasils'));
    }

    /**
     * Helper Private Query Filter Hasil Ujian
     */
    private function getFilteredHasilQuery(Request $request, $uji_id)
    {
        $query = UjiHasil::where('uji_id', $uji_id)->with('asesi');

        // Filter Search (Asesi ID, Nama Peserta Publik, Nama User)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asesi_id', 'like', "%{$search}%")
                ->orWhere('nama_peserta_publik', 'like', "%{$search}%")
                ->orWhereHas('asesi', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter Status Pengerjaan
        if ($request->filled('status_pengerjaan')) {
            $query->where('status_pengerjaan', $request->status_pengerjaan);
        }

        // Checkbox: Sembunyikan Identitas Ganda (Ambil Percobaan Terbaru)
        if ($request->has('hide_duplicates') && $request->hide_duplicates == '1') {
            // Subquery untuk mengambil ID hasil terbaru per asesi/nama publik
            $subLatestIds = UjiHasil::where('uji_id', $uji_id)
                ->selectRaw('MAX(id) as max_id')
                ->groupByRaw("COALESCE(CAST(asesi_id AS CHAR), nama_peserta_publik)")
                ->pluck('max_id');

            $query->whereIn('id', $subLatestIds);
        }

        // Sorting Kolom (Default: total_skor DESC)
        $sortBy = $request->get('sort_by', 'total_skor');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['id', 'asesi_id', 'total_skor', 'waktu_mulai', 'status_pengerjaan'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('total_skor', 'desc');
        }

        return $query;
    }

    /**
     * Ekspor Rekap Hasil (Excel .xls / PDF)
     */
    public function exportHasil(Request $request, $uji_id)
    {
        $uji = Uji::findOrFail($uji_id);
        $format = $request->get('format', 'excel'); // 'excel' atau 'pdf'
        
        // Ambil data sesuai filter aktif tanpa pagination
        $dataHasils = $this->getFilteredHasilQuery($request, $uji->id)->get();

        if ($format === 'pdf') {
            // Menggunakan DomPDF / Laravel PDF View
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sba.exports.hasil_pdf', compact('uji', 'dataHasils'))
                ->setPaper('a4', 'landscape');
                
            return $pdf->download("Rekap_Hasil_{$uji->kode_uji}.pdf");
        }

        // Export format Microsoft Excel (.xls) via HTML Spreadsheet Stream
        $fileName = "Rekap_Hasil_{$uji->kode_uji}.xls";
        
        $headers = [
            "Content-Type"        => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Cache-Control"       => "max-age=0, no-cache, must-revalidate",
            "Pragma"              => "no-cache",
        ];

        $callback = function() use ($uji, $dataHasils) {
            // Output format HTML Spreadsheet yang dibaca sempurna oleh Microsoft Excel (.xls)
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Hasil Ujian</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
            echo '<body>';
            echo '<table border="1">';
            echo '<tr style="background-color: #5e72e4; color: #ffffff; font-weight: bold;">';
            echo '<th>No</th>';
            echo '<th>ID Asesi</th>';
            echo '<th>Nama Peserta</th>';
            echo '<th>Percobaan Ke</th>';
            echo '<th>Waktu Mulai</th>';
            echo '<th>Waktu Selesai</th>';
            echo '<th>Durasi</th>';
            echo '<th>Status</th>';
            echo '<th>Skor Akhir</th>';
            echo '</tr>';

            foreach ($dataHasils as $idx => $row) {
                $nama = $row->asesi ? $row->asesi->name : ($row->nama_peserta_publik ?? 'Guest');
                
                // Kalkulasi Durasi Eksplisit
                $durasiText = '-';
                if ($row->waktu_mulai && $row->waktu_selesai) {
                    $totalDetik = $row->waktu_mulai->diffInSeconds($row->waktu_selesai);
                    $jam = floor($totalDetik / 3600);
                    $menit = floor(($totalDetik % 3600) / 60);
                    $detik = $totalDetik % 60;
                    $durasiText = sprintf('%02d:%02d:%02d', $jam, $menit, $detik);
                } elseif ($row->status_pengerjaan === 'berlangsung') {
                    $durasiText = 'Sedang Berjalan';
                }

                echo '<tr>';
                echo '<td>' . ($idx + 1) . '</td>';
                echo '<td>' . ($row->asesi_id ?? 'Publik') . '</td>';
                echo '<td>' . htmlspecialchars($nama) . '</td>';
                echo '<td>' . $row->percobaan_ke . '</td>';
                echo '<td>' . ($row->waktu_mulai ? $row->waktu_mulai->format('d/m/Y H:i:s') : '-') . '</td>';
                echo '<td>' . ($row->waktu_selesai ? $row->waktu_selesai->format('d/m/Y H:i:s') : '-') . '</td>';
                echo '<td>' . $durasiText . '</td>';
                echo '<td>' . strtoupper($row->status_pengerjaan) . '</td>';
                echo '<td>' . $row->total_skor . '</td>';
                echo '</tr>';
            }

            echo '</table>';
            echo '</html>';
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Hapus Data Hasil Ujian Peserta
     */
    public function hasilDestroy($hasil_id)
    {
        $hasil = UjiHasil::findOrFail($hasil_id);
        $ujiId = $hasil->uji_id;
        
        // Jawaban peserta otomatis terhapus via CASCADE / Manual Delete
        $hasil->jawabans()->delete();
        $hasil->delete();

        return redirect()->route('sba.hasil.index', $ujiId)->with('success', 'Data hasil ujian peserta berhasil dihapus.');
    }


    /**
     * Halaman Detail & Penilaian Jawaban Asesi
     */
    public function hasilDetail($hasil_id)
    {
        $hasil = UjiHasil::with(['uji.soals', 'jawabans', 'asesi'])->findOrFail($hasil_id);
        
        // Mapping jawaban siswa berdasarkan uji_soal_id agar mudah dipanggil di Blade
        $mapJawaban = $hasil->jawabans->keyBy('uji_soal_id');

        return view('sba.hasil_detail', compact('hasil', 'mapJawaban'));
    }

    /**
     * Simpan Penilaian Esai & Finalisasi Skor
     */
    public function simpanPenilaianEsai(Request $request, $hasil_id)
    {
        $hasil = UjiHasil::with('uji.soals')->findOrFail($hasil_id);

        $skorEsaiTotal = 0;
        $skorOtomatisPg = 0;

        // Ambil input skor dari form per uji_soal_id
        $skorInputs = $request->input('skor_esai', []);

        foreach ($hasil->uji->soals as $soal) {
            $jawaban = UjiJawaban::where('uji_hasil_id', $hasil->id)
                ->where('uji_soal_id', $soal->id)
                ->first();

            if ($soal->tipe === 'esai') {
                $skorDidapat = isset($skorInputs[$soal->id]) ? (float) $skorInputs[$soal->id] : 0;
                
                // Batasi agar skor tidak melebihi bobot maksimal soal
                if ($skorDidapat > $soal->bobot_nilai) {
                    $skorDidapat = $soal->bobot_nilai;
                }

                if ($jawaban) {
                    $jawaban->update(['skor_didapat' => $skorDidapat]);
                } else {
                    UjiJawaban::create([
                        'uji_hasil_id' => $hasil->id,
                        'uji_soal_id' => $soal->id,
                        'jawaban' => null,
                        'skor_didapat' => $skorDidapat
                    ]);
                }
                $skorEsaiTotal += $skorDidapat;
            } else {
                // Hitung kembali skor PG/PG Kompleks yang sudah ada
                if ($jawaban) {
                    $skorOtomatisPg += (float) $jawaban->skor_didapat;
                }
            }
        }

        // Update Total Skor Akhir di UjiHasil
        $totalSkorAkhir = $skorOtomatisPg + $skorEsaiTotal;

        $hasil->update([
            'skor_otomatis' => $skorOtomatisPg,
            'total_skor' => $totalSkorAkhir
        ]);

        return redirect()->back()->with('success', 'Penilaian esai dan finalisasi skor berhasil disimpan!');
    }

    /**
     * Export Detail Jawaban Peserta ke PDF
     */
    public function exportHasilDetailPdf($hasil_id)
    {
        $hasil = UjiHasil::with(['uji.soals', 'jawabans', 'asesi'])->findOrFail($hasil_id);
        $mapJawaban = $hasil->jawabans->keyBy('uji_soal_id');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sba.exports.hasil_detail_pdf', compact('hasil', 'mapJawaban'))
            ->setPaper('a4', 'portrait');

        $namaPeserta = $hasil->asesi ? $hasil->asesi->name : ($hasil->nama_peserta_publik ?? 'Peserta');
        $fileName = 'Detail_Hasil_' . \Illuminate\Support\Str::slug($namaPeserta) . '_' . $hasil->uji->kode_uji . '.pdf';

        return $pdf->download($fileName);
    }

    public function bulkDelete(Request $request, $uji_id)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:uji_hasils,id', // Sesuaikan nama tabel hasil uji kamu jika berbeda (misal: uji_hasil)
        ]);

        try {
            // Hapus data hasil ujian yang tercentang
            \App\Models\UjiHasil::where('uji_id', $uji_id)
                ->whereIn('id', $request->ids)
                ->delete();

            return redirect()->back()->with('success', count($request->ids) . ' data hasil ujian berhasil dihapus secara massal.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data hasil ujian: ' . $e->getMessage());
        }
    }
}