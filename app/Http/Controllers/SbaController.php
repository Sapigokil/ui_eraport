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
    /**
     * Validasi hak akses terhadap suatu asesmen berdasarkan permission sba.view_all
     */
    private function authorizeUjiAccess(Uji $uji): void
    {
        if (!auth()->user()->can('sba.view_all') && (int) $uji->user_id !== (int) auth()->id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses asesmen milik pengguna lain.');
        }
    }

    public function index(Request $request)
    {
        $canViewAll = auth()->user()->can('sba.view_all');
        $currentUserId = (string) auth()->id();

        // 1. Daftar Author (hanya dimuat jika memiliki permission sba.view_all)
        if ($canViewAll) {
            $authorIds = Uji::select('user_id')->distinct()->pluck('user_id')->toArray();
            if (!in_array((int) $currentUserId, array_map('intval', $authorIds), true)) {
                $authorIds[] = (int) $currentUserId;
            }
            $authors = \App\Models\User::whereIn('id', $authorIds)
                ->orderBy('name')
                ->get(['id', 'name']);

            // Default pertama kali buka halaman adalah milik sendiri; jika memilih "Semua Author" (?user_id=), nilai menjadi ''
            $selectedAuthorId = $request->has('user_id')
                ? (string) $request->input('user_id', '')
                : $currentUserId;
        } else {
            $authors = collect();
            $selectedAuthorId = $currentUserId;
        }

        // 2. Query Utama Uji (memuat soals & count hasil berstatus 'selesai' untuk deteksi antrean penilaian)
        $query = Uji::with(['author', 'soals'])
            ->withCount(['hasils', 'hasilsSelesai']);

        // Filter Search (Kode Uji, Judul, Deskripsi)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_uji', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter Author (jika punya sba.view_all, filter mengikuti $selectedAuthorId; jika tidak, dikunci ke auth()->id())
        if ($canViewAll) {
            if ($selectedAuthorId !== '') {
                $query->where('user_id', $selectedAuthorId);
            }
        } else {
            $query->where('user_id', $currentUserId);
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

        return view('sba.index', compact('ujis', 'authors', 'canViewAll', 'selectedAuthorId'));
    }

    public function create()
    {
        return view('sba.form');
    }

    public function edit(Uji $uji)
    {
        $this->authorizeUjiAccess($uji);

        $uji->load('soals');
        return view('sba.form', compact('uji'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'kode_uji' => strtoupper(trim((string) $request->kode_uji)),
        ]);

        $request->validate([
            'judul'                 => 'required|string|max:255',
            'kode_uji'              => ['required', 'string', 'max:12', 'regex:/^[A-Z0-9]+$/', 'unique:ujis,kode_uji'],
            'durasi_menit'          => 'required|integer|min:0',
            'toleransi_pelanggaran' => 'required|integer|min:1',
            'waktu_mulai'           => 'nullable|date',
            'waktu_selesai'         => 'nullable|date|after_or_equal:waktu_mulai',
            'maksimal_percobaan'    => 'nullable|integer|min:1',
            'soals'                 => 'required|array|min:1',
            'soals.*.pertanyaan'    => 'required|string',
            'soals.*.tipe'          => 'required|in:pg,pg_kompleks,esai',
            'soals.*.media'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'kode_uji.required' => 'Kode Asesmen wajib diisi.',
            'kode_uji.max'      => 'Kode Asesmen maksimal 12 karakter.',
            'kode_uji.regex'    => 'Kode Asesmen hanya boleh berisi huruf (A-Z) dan angka (0-9) tanpa spasi atau simbol.',
            'kode_uji.unique'   => 'Kode Asesmen sudah digunakan oleh ujian lain. Silakan gunakan kode lain.',
        ]);

        // 1. Buat Ujian Utama
        $uji = Uji::create([
            'user_id'               => auth()->id(),
            'kode_uji'              => $request->kode_uji,
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

        return redirect()->route('sba.index')->with('success', 'Ujian dan soal berhasil dibuat dengan kode: ' . $uji->kode_uji);
    }

    public function update(Request $request, $id)
    {
        $uji = Uji::with('soals')->findOrFail($id);
        $this->authorizeUjiAccess($uji);

        $request->merge([
            'kode_uji' => strtoupper(trim((string) $request->kode_uji)),
        ]);

        $request->validate([
            'judul'                 => 'required|string|max:255',
            'kode_uji'              => ['required', 'string', 'max:12', 'regex:/^[A-Z0-9]+$/', 'unique:ujis,kode_uji,' . $uji->id],
            'durasi_menit'          => 'required|integer|min:0',
            'toleransi_pelanggaran' => 'required|integer|min:1',
            'waktu_mulai'           => 'nullable|date',
            'waktu_selesai'         => 'nullable|date|after_or_equal:waktu_mulai',
            'maksimal_percobaan'    => 'nullable|integer|min:1',
            'soals'                 => 'required|array|min:1',
            'soals.*.pertanyaan'    => 'required|string',
            'soals.*.media'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'kode_uji.required' => 'Kode Asesmen wajib diisi.',
            'kode_uji.max'      => 'Kode Asesmen maksimal 12 karakter.',
            'kode_uji.regex'    => 'Kode Asesmen hanya boleh berisi huruf (A-Z) dan angka (0-9) tanpa spasi atau simbol.',
            'kode_uji.unique'   => 'Kode Asesmen sudah digunakan oleh ujian lain. Silakan gunakan kode lain.',
        ]);

        // 1. Update Data Utama Ujian
        $uji->update([
            'kode_uji'              => $request->kode_uji,
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
        $soal = \App\Models\UjiSoal::with('uji')->findOrFail($soal_id);
        if ($soal->uji) {
            $this->authorizeUjiAccess($soal->uji);
        }

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
        $this->authorizeUjiAccess($uji);

        // Memanggil delete() pada Uji akan memicu static::deleting di Model Uji yang menghapus seluruh UjiSoal beserta gambar fisiknya
        $uji->delete();

        return redirect()->route('sba.index')->with('success', 'Ujian beserta seluruh gambar soal berhasil dihapus!');
    }

    /**
     * Halaman Index Daftar Hasil Peserta / Asesi per Ujian
     */
    public function hasilIndex(Request $request, $uji_id)
    {
        $uji = Uji::with('soals')->findOrFail($uji_id);
        $this->authorizeUjiAccess($uji);

        $butuhPenilaianManual = $uji->butuhPenilaianManual();
        $jumlahPerluDinilai = $butuhPenilaianManual
            ? UjiHasil::where('uji_id', $uji->id)->where('status_pengerjaan', 'selesai')->count()
            : 0;

        // Pemanggilan Query Utama
        $hasils = $this->getFilteredHasilQuery($request, $uji->id)
            ->paginate((int) $request->get('per_page', 10))
            ->withQueryString();

        return view('sba.hasil', compact('uji', 'hasils', 'butuhPenilaianManual', 'jumlahPerluDinilai'));
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
            $query->where(function ($q) use ($search) {
                $q->where('asesi_id', 'like', "%{$search}%")
                    ->orWhere('nama_peserta_publik', 'like', "%{$search}%")
                    ->orWhereHas('asesi', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter Status Pengerjaan
        if ($request->filled('status_pengerjaan')) {
            $query->where('status_pengerjaan', $request->status_pengerjaan);
        }

        // Filter Rentang Tanggal Pengumpulan (waktu_selesai / waktu_mulai)
        if ($request->filled('tgl_mulai')) {
            $query->whereDate('waktu_selesai', '>=', $request->tgl_mulai);
        }
        if ($request->filled('tgl_selesai')) {
            $query->whereDate('waktu_selesai', '<=', $request->tgl_selesai);
        }

        // Checkbox: Sembunyikan Identitas Ganda (Ambil Percobaan Terbaru)
        if ($request->has('hide_duplicates') && $request->hide_duplicates == '1') {
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
        $this->authorizeUjiAccess($uji);

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

        $callback = function () use ($uji, $dataHasils) {
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
        $hasil = UjiHasil::with('uji')->findOrFail($hasil_id);
        $this->authorizeUjiAccess($hasil->uji);

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
        $this->authorizeUjiAccess($hasil->uji);

        // Mapping jawaban siswa berdasarkan uji_soal_id agar mudah dipanggil di Blade
        $mapJawaban = $hasil->jawabans->keyBy('uji_soal_id');

        return view('sba.hasil_detail', compact('hasil', 'mapJawaban'));
    }

    /**
     * Refresh / Hitung Ulang Nilai Peserta Sesuai Bobot Soal Terbaru
     */
    public function refreshNilaiHasil($hasil_id)
    {
        $hasil = UjiHasil::with(['uji.soals', 'jawabans'])->findOrFail($hasil_id);
        $this->authorizeUjiAccess($hasil->uji);

        $mapJawaban = $hasil->jawabans->keyBy('uji_soal_id');
        $skorOtomatisBaru = 0;
        $skorManualTotal = 0;

        foreach ($hasil->uji->soals as $soal) {
            $jawaban = $mapJawaban->get($soal->id);
            $kunciArray = is_array($soal->kunci_jawaban)
                ? array_values(array_filter($soal->kunci_jawaban, fn($v) => $v !== null && $v !== ''))
                : [];
            $isManualGraded = ($soal->tipe === 'esai') || (in_array($soal->tipe, ['pg', 'pg_kompleks']) && empty($kunciArray));

            if ($isManualGraded) {
                // Pertahankan skor manual yang ada, namun pangkas jika melebihi bobot soal terbaru
                if ($jawaban) {
                    $skorManualItem = (float) $jawaban->skor_didapat;
                    if ($skorManualItem > (float) $soal->bobot_nilai) {
                        $skorManualItem = (float) $soal->bobot_nilai;
                        $jawaban->update(['skor_didapat' => $skorManualItem]);
                    }
                    $skorManualTotal += $skorManualItem;
                }
            } else {
                // Evaluasi ulang soal otomatis (PG / PG Kompleks dengan kunci) sesuai bobot terbaru
                $skorItemBaru = 0;

                if ($jawaban && $jawaban->jawaban !== null && $jawaban->jawaban !== '') {
                    $isBenar = false;

                    if ($soal->tipe === 'pg_kompleks') {
                        $jawabanSiswaArray = array_map('strval', json_decode($jawaban->jawaban, true) ?? []);
                        $kunciStrArray = array_map('strval', $kunciArray);
                        sort($kunciStrArray);
                        sort($jawabanSiswaArray);
                        $isBenar = ($kunciStrArray === $jawabanSiswaArray && !empty($kunciStrArray));
                    } else {
                        $isBenar = in_array((string) $jawaban->jawaban, array_map('strval', $kunciArray), true);
                    }

                    if ($isBenar) {
                        $skorItemBaru = (float) $soal->bobot_nilai;
                    }

                    $jawaban->update(['skor_didapat' => $skorItemBaru]);
                } elseif ($jawaban) {
                    $jawaban->update(['skor_didapat' => 0]);
                }

                $skorOtomatisBaru += $skorItemBaru;
            }
        }

        $totalSkorBaru = $skorOtomatisBaru + $skorManualTotal;

        $hasil->update([
            'skor_otomatis' => $skorOtomatisBaru,
            'total_skor'    => $totalSkorBaru,
        ]);

        return redirect()->back()->with('success', "Nilai peserta berhasil diperbarui sesuai bobot soal terbaru (Total Skor: {$totalSkorBaru} Poin).");
    }

    /**
     * Simpan Penilaian Manual (Esai & PG/PGK Tanpa Kunci) serta Finalisasi Skor (Status menjadi 'final')
     */
    public function simpanPenilaianEsai(Request $request, $hasil_id)
    {
        $hasil = UjiHasil::with('uji.soals')->findOrFail($hasil_id);
        $this->authorizeUjiAccess($hasil->uji);

        $skorManualTotal = 0;
        $skorOtomatisPg = 0;

        // Ambil input skor manual dari form per uji_soal_id (mendukung 'skor_manual' maupun 'skor_esai')
        $skorInputs = $request->input('skor_manual', $request->input('skor_esai', []));

        foreach ($hasil->uji->soals as $soal) {
            $jawaban = UjiJawaban::where('uji_hasil_id', $hasil->id)
                ->where('uji_soal_id', $soal->id)
                ->first();

            $kunciArray = is_array($soal->kunci_jawaban) ? array_filter($soal->kunci_jawaban, fn($v) => $v !== null && $v !== '') : [];
            $isManualGraded = ($soal->tipe === 'esai') || (in_array($soal->tipe, ['pg', 'pg_kompleks']) && empty($kunciArray));

            if ($isManualGraded) {
                $skorDidapat = isset($skorInputs[$soal->id]) ? (float) $skorInputs[$soal->id] : ($jawaban ? (float) $jawaban->skor_didapat : 0);

                // Batasi agar skor tidak kurang dari 0 atau melebihi bobot maksimal soal
                if ($skorDidapat < 0) {
                    $skorDidapat = 0;
                }
                if ($skorDidapat > $soal->bobot_nilai) {
                    $skorDidapat = $soal->bobot_nilai;
                }

                if ($jawaban) {
                    $jawaban->update(['skor_didapat' => $skorDidapat]);
                } else {
                    UjiJawaban::create([
                        'uji_hasil_id' => $hasil->id,
                        'uji_soal_id'  => $soal->id,
                        'jawaban'      => null,
                        'skor_didapat' => $skorDidapat,
                    ]);
                }
                $skorManualTotal += $skorDidapat;
            } else {
                // Hitung kembali skor PG/PG Kompleks otomatis yang memiliki kunci
                if ($jawaban) {
                    $skorOtomatisPg += (float) $jawaban->skor_didapat;
                }
            }
        }

        // Update Total Skor Akhir di UjiHasil & set status menjadi 'final'
        $totalSkorAkhir = $skorOtomatisPg + $skorManualTotal;

        $hasil->update([
            'skor_otomatis'     => $skorOtomatisPg,
            'total_skor'        => $totalSkorAkhir,
            'status_pengerjaan' => 'final',
        ]);

        return redirect()->back()->with('success', 'Penilaian akhir berhasil disimpan dan status diubah menjadi FINAL!');
    }

    /**
     * Buka Kunci (Unlock) Sesi Peserta yang Diskualifikasi / Gagal Pelanggaran
     */
    public function unlockSesi($hasil_id)
    {
        $hasil = UjiHasil::with('uji')->findOrFail($hasil_id);
        $this->authorizeUjiAccess($hasil->uji);

        if ($hasil->status_pengerjaan !== 'gagal_pelanggaran') {
            return redirect()->back()->with('error', 'Hanya peserta dengan status diskualifikasi yang dapat di-unlock.');
        }

        $namaAdmin = auth()->user()->name ?? 'Admin';
        $catatanBaru = ($hasil->catatan_pelanggaran ?? '') . " | Di-unlock oleh {$namaAdmin} pada " . now()->format('d/m/Y H:i:s');

        $hasil->update([
            'status_pengerjaan'   => 'berlangsung',
            'waktu_selesai'       => null,
            'jumlah_pelanggaran'  => 0,
            'catatan_pelanggaran' => $catatanBaru,
        ]);

        return redirect()->back()->with('success', 'Sesi peserta berhasil dibuka kembali (Unlock). Peserta dapat login ulang untuk melanjutkan ujian.');
    }

    /**
     * Finalisasi Paksa oleh Administrator untuk Peserta yang Stuck 'berlangsung'
     */
    public function finalisasiPaksa($hasil_id)
    {
        $hasil = UjiHasil::with(['uji.soals', 'jawabans'])->findOrFail($hasil_id);
        $this->authorizeUjiAccess($hasil->uji);

        if ($hasil->status_pengerjaan !== 'berlangsung') {
            return redirect()->back()->with('error', 'Hanya sesi dengan status berlangsung yang dapat difinalisasi.');
        }

        $totalSkorOtomatis = 0;
        $mapSoal = $hasil->uji->soals->keyBy('id');

        foreach ($hasil->jawabans as $jawabanSiswa) {
            $soal = $mapSoal->get($jawabanSiswa->uji_soal_id);
            if (!$soal) {
                continue;
            }

            if (in_array($soal->tipe, ['pg', 'pg_kompleks'])) {
                $kunciArray = $soal->kunci_jawaban ?? [];
                $jawabanSiswaRaw = $jawabanSiswa->jawaban;
                $skorItem = 0;

                if ($soal->tipe === 'pg_kompleks') {
                    $jawabanSiswaArray = json_decode($jawabanSiswaRaw, true) ?? [];
                    sort($kunciArray);
                    sort($jawabanSiswaArray);
                    if ($kunciArray == $jawabanSiswaArray) {
                        $skorItem = $soal->bobot_nilai;
                    }
                } else {
                    if (in_array($jawabanSiswaRaw, $kunciArray)) {
                        $skorItem = $soal->bobot_nilai;
                    }
                }

                $totalSkorOtomatis += $skorItem;
                $jawabanSiswa->update(['skor_didapat' => $skorItem]);
            }
        }

        $namaAdmin = auth()->user()->name ?? 'Admin';
        $catatanBaru = ($hasil->catatan_pelanggaran ?? '') . " | Difinalisasi paksa oleh {$namaAdmin} pada " . now()->format('d/m/Y H:i:s');

        $hasil->update([
            'waktu_selesai'       => now(),
            'skor_otomatis'       => $totalSkorOtomatis,
            'total_skor'          => $totalSkorOtomatis,
            'status_pengerjaan'   => 'selesai',
            'catatan_pelanggaran' => $catatanBaru,
        ]);

        return redirect()->back()->with('success', "Sesi peserta berhasil diselesaikan dengan skor sementara {$totalSkorOtomatis} poin.");
    }

    /**
     * Export Detail Jawaban Peserta ke PDF
     */
    public function exportHasilDetailPdf($hasil_id)
    {
        $hasil = UjiHasil::with(['uji.soals', 'jawabans', 'asesi'])->findOrFail($hasil_id);
        $this->authorizeUjiAccess($hasil->uji);

        $mapJawaban = $hasil->jawabans->keyBy('uji_soal_id');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sba.exports.hasil_detail_pdf', compact('hasil', 'mapJawaban'))
            ->setPaper('a4', 'portrait');

        $namaPeserta = $hasil->asesi ? $hasil->asesi->name : ($hasil->nama_peserta_publik ?? 'Peserta');
        $fileName = 'Detail_Hasil_' . \Illuminate\Support\Str::slug($namaPeserta) . '_' . $hasil->uji->kode_uji . '.pdf';

        return $pdf->download($fileName);
    }

    public function bulkDelete(Request $request, $uji_id)
    {
        $uji = Uji::findOrFail($uji_id);
        $this->authorizeUjiAccess($uji);

        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:uji_hasils,id',
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

    /**
     * Duplikat Ujian beserta Seluruh Butir Soal & Kunci Jawaban (Tanpa Hasil Peserta)
     */
    public function duplicate($id)
    {
        $ujiAsli = Uji::with('soals')->findOrFail($id);
        $this->authorizeUjiAccess($ujiAsli);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($ujiAsli) {
            // 1. Generate Kode Uji 6 karakter yang benar-benar baru dan unik
            do {
                $kodeBaru = strtoupper(\Illuminate\Support\Str::random(6));
            } while (Uji::where('kode_uji', $kodeBaru)->exists());

            // 2. Generate Judul Baru dengan suffix _copy (otomatis increment jika sudah ada)
            $baseJudul = $ujiAsli->judul . '_copy';
            $judulBaru = $baseJudul;
            $counter = 2;
            while (Uji::where('judul', $judulBaru)->exists()) {
                $judulBaru = $baseJudul . '_' . $counter;
                $counter++;
            }

            // 3. Replikasi Data Ujian Utama
            $ujiBaru = $ujiAsli->replicate([
                'kode_uji',
                'judul',
                'user_id',
                'status',
                'created_at',
                'updated_at',
            ]);

            $ujiBaru->kode_uji = $kodeBaru;
            $ujiBaru->judul    = $judulBaru;
            $ujiBaru->user_id  = auth()->id() ?? $ujiAsli->user_id;
            $ujiBaru->status   = 'draft'; // Diset draft agar dapat ditinjau dulu sebelum dipublikasikan
            $ujiBaru->save();

            // 4. Duplikat Seluruh Soal & Salin File Media Gambar (Agar Independen saat Soal Lama Dihapus)
            foreach ($ujiAsli->soals as $soalAsli) {
                $pathMediaBaru = null;

                if ($soalAsli->media && Storage::disk('public')->exists($soalAsli->media)) {
                    $ext = pathinfo($soalAsli->media, PATHINFO_EXTENSION);
                    $namaFileBaru = 'soal-images/' . \Illuminate\Support\Str::random(40) . ($ext ? '.' . $ext : '');
                    Storage::disk('public')->copy($soalAsli->media, $namaFileBaru);
                    $pathMediaBaru = $namaFileBaru;
                }

                UjiSoal::create([
                    'uji_id'        => $ujiBaru->id,
                    'tipe'          => $soalAsli->tipe,
                    'pertanyaan'    => $soalAsli->pertanyaan,
                    'media'         => $pathMediaBaru,
                    'opsi_jawaban'  => $soalAsli->opsi_jawaban,
                    'kunci_jawaban' => $soalAsli->kunci_jawaban,
                    'bobot_nilai'   => $soalAsli->bobot_nilai,
                ]);
            }

            return redirect()->route('sba.index')->with(
                'success',
                "Ujian berhasil diduplikat menjadi \"{$ujiBaru->judul}\" dengan Kode Uji baru: {$ujiBaru->kode_uji}."
            );
        });
    }

    /**
     * Unduh Template Excel (.xlsx) untuk Import Soal SBA
     */
    public function downloadTemplateSoal()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Soal SBA');

        // 1. Header Kolom Wajib (Urutan Baku)
        $headers = [
            'no',
            'tipe_soal',
            'pertanyaan',
            'opsi_a',
            'opsi_b',
            'opsi_c',
            'opsi_d',
            'opsi_e',
            'kunci_jawaban',
            'bobot_nilai',
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Styling Header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '5E72E4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // 2. Contoh Pengisian 3 Tipe Soal (PG, PG Kompleks, Esai)
        $contohData = [
            [1, 'pg', 'Ibu kota Provinsi Jawa Tengah adalah...', 'Bandung', 'Surabaya', 'Semarang', 'Surakarta', 'Yogyakarta', 'C', 10],
            [2, 'pg_kompleks', 'Manakah di bawah ini yang termasuk bilangan prima?', '2', '4', '5', '9', '11', 'A,C,E', 20],
            [3, 'esai', 'Jelaskan secara singkat proses terjadinya siklus air!', '', '', '', '', '', '', 30],
        ];
        $sheet->fromArray($contohData, null, 'A2');

        // Data Validation Dropdown untuk kolom tipe_soal (B2:B200)
        for ($row = 2; $row <= 200; $row++) {
            $validation = $sheet->getCell("B{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"pg,pg_kompleks,esai"');
        }

        // Auto-size kolom
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Import_Soal_SBA.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * AJAX Endpoint: Validasi Header & Parse File Excel Menjadi Array JSON Soal (Temporary to View)
     */
    public function importSoalPreview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'excel_file.required' => 'Silakan pilih file Excel terlebih dahulu.',
            'excel_file.mimes'    => 'Format file harus berekstensi .xlsx, .xls, atau .csv.',
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);

            if (empty($rows) || count($rows) < 2) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'File Excel kosong atau tidak memiliki baris data soal.',
                ], 422);
            }

            // 1. Validasi Urutan & Nama Header Kolom (Baris 1)
            $expectedHeaders = [
                'no',
                'tipe_soal',
                'pertanyaan',
                'opsi_a',
                'opsi_b',
                'opsi_c',
                'opsi_d',
                'opsi_e',
                'kunci_jawaban',
                'bobot_nilai',
            ];

            $actualHeaders = array_map(function ($val) {
                return strtolower(trim((string) $val));
            }, array_slice($rows[0], 0, 10));

            if ($actualHeaders !== $expectedHeaders) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data tidak sesuai template! Urutan dan nama header kolom wajib: ' . implode(', ', $expectedHeaders) . '.',
                ], 422);
            }

            // 2. Parsing & Validasi Tiap Baris Soal (Mulai Baris 2)
            $parsedSoals = [];
            $errors = [];
            $hurufToIndex = ['A' => '0', 'B' => '1', 'C' => '2', 'D' => '3', 'E' => '4'];

            foreach (array_slice($rows, 1) as $idx => $row) {
                $excelRowNum = $idx + 2;

                // Lewati baris yang benar-benar kosong seluruh kolomnya
                $joined = trim(implode('', array_map('strval', array_slice($row, 0, 10))));
                if ($joined === '') {
                    continue;
                }

                $tipe       = strtolower(trim((string) ($row[1] ?? '')));
                $pertanyaan = trim((string) ($row[2] ?? ''));
                $opsiRaw    = [
                    trim((string) ($row[3] ?? '')), // A
                    trim((string) ($row[4] ?? '')), // B
                    trim((string) ($row[5] ?? '')), // C
                    trim((string) ($row[6] ?? '')), // D
                    trim((string) ($row[7] ?? '')), // E
                ];
                $kunciRaw   = strtoupper(trim((string) ($row[8] ?? '')));
                $bobot      = is_numeric($row[9] ?? null) && (int) $row[9] > 0 ? (int) $row[9] : 10;

                if (!in_array($tipe, ['pg', 'pg_kompleks', 'esai'])) {
                    $errors[] = "Baris {$excelRowNum}: Tipe soal '{$tipe}' tidak valid (gunakan pg, pg_kompleks, atau esai).";
                    continue;
                }

                if ($pertanyaan === '') {
                    $errors[] = "Baris {$excelRowNum}: Kolom pertanyaan tidak boleh kosong.";
                    continue;
                }

                $opsiJawaban = null;
                $kunciJawaban = [];

                if (in_array($tipe, ['pg', 'pg_kompleks'])) {
                    if ($opsiRaw[0] === '' || $opsiRaw[1] === '') {
                        $errors[] = "Baris {$excelRowNum}: Soal {$tipe} minimal wajib mengisi Opsi A dan Opsi B.";
                        continue;
                    }

                    if ($kunciRaw === '') {
                        $errors[] = "Baris {$excelRowNum}: Kunci jawaban wajib diisi untuk tipe {$tipe}.";
                        continue;
                    }

                    $opsiJawaban = $opsiRaw;

                    if ($tipe === 'pg') {
                        if (!isset($hurufToIndex[$kunciRaw])) {
                            $errors[] = "Baris {$excelRowNum}: Kunci jawaban PG harus berupa satu huruf (A, B, C, D, atau E).";
                            continue;
                        }
                        $idxKunci = (int) $hurufToIndex[$kunciRaw];
                        if ($opsiRaw[$idxKunci] === '') {
                            $errors[] = "Baris {$excelRowNum}: Kunci jawaban '{$kunciRaw}' menunjuk pada opsi yang kosong.";
                            continue;
                        }
                        $kunciJawaban = [(string) $idxKunci];
                    } else {
                        // pg_kompleks (dipisah koma, misal A,C,E)
                        $parts = array_unique(array_filter(array_map('trim', explode(',', $kunciRaw))));
                        $validKunci = true;
                        foreach ($parts as $huruf) {
                            if (!isset($hurufToIndex[$huruf])) {
                                $errors[] = "Baris {$excelRowNum}: Kunci PG Kompleks '{$huruf}' tidak valid (gunakan huruf A-E dipisah koma).";
                                $validKunci = false;
                                break;
                            }
                            $idxKunci = (int) $hurufToIndex[$huruf];
                            if ($opsiRaw[$idxKunci] === '') {
                                $errors[] = "Baris {$excelRowNum}: Kunci jawaban '{$huruf}' menunjuk pada opsi yang kosong.";
                                $validKunci = false;
                                break;
                            }
                            $kunciJawaban[] = (string) $idxKunci;
                        }
                        if (!$validKunci) {
                            continue;
                        }
                    }
                }

                $parsedSoals[] = [
                    'tipe'          => $tipe,
                    'pertanyaan'    => $pertanyaan,
                    'media'         => null,
                    'opsi_jawaban'  => $opsiJawaban,
                    'kunci_jawaban' => $kunciJawaban,
                    'bobot_nilai'   => $bobot,
                ];
            }

            if (!empty($errors)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Terdapat kesalahan pada data Excel:',
                    'errors'  => $errors,
                ], 422);
            }

            if (empty($parsedSoals)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak ada baris soal yang valid ditemukan di dalam file Excel.',
                ], 422);
            }

            return response()->json([
                'status'  => 'success',
                'message' => count($parsedSoals) . ' soal berhasil dimuat ke form. Silakan periksa kembali sebelum menyimpan asesmen.',
                'soals'   => $parsedSoals,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }
}