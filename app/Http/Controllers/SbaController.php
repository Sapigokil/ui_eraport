<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Uji;
use App\Models\UjiSoal;
use App\Models\UjiHasil;
use App\Models\UjiJawaban;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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

    /**
     * Helper Private: Ambil daftar Mata Pelajaran aktif (is_active = 1)
     */
    private function getActiveMapels()
    {
        return MataPelajaran::where('is_active', 1)
            ->orderBy('urutan')
            ->orderBy('nama_mapel')
            ->get(['id_mapel', 'nama_mapel', 'nama_singkat', 'id_guru']);
    }

    /**
     * AJAX Endpoint: Cek ketersediaan Kode Asesmen secara real-time
     */
    public function checkKode(Request $request)
    {
        $kode = strtoupper(trim((string) $request->query('kode', '')));
        $ignoreId = $request->query('ignore_id');

        if ($kode === '') {
            return response()->json([
                'available' => false,
                'message'   => 'Kode Asesmen wajib diisi.',
            ]);
        }

        if (!preg_match('/^[A-Z0-9]{1,12}$/', $kode)) {
            return response()->json([
                'available' => false,
                'message'   => 'Kode hanya boleh huruf A-Z dan angka 0-9 (maksimal 12 karakter).',
            ]);
        }

        $query = Uji::where('kode_uji', $kode);
        if (!empty($ignoreId)) {
            $query->where('id', '!=', $ignoreId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message'   => $exists
                ? "Kode \"{$kode}\" sudah digunakan oleh ujian lain."
                : "Kode \"{$kode}\" tersedia dan dapat digunakan.",
        ]);
    }

    /**
     * Helper Private: Normalisasi & Validasi Payload Soal (termasuk tipe benar_salah & opsi gambar)
     */
    private function normalizeSoalPayload(
        Request $request,
        array $soalData,
        int $index,
        ?UjiSoal $existingSoal = null
    ): array {
        $nomorSoal = $index + 1;
        $tipe = $soalData['tipe'];

        if ($tipe === 'benar_salah') {
            $rawPernyataan = $soalData['pernyataan_bs'] ?? [];
            if (!is_array($rawPernyataan) || count($rawPernyataan) === 0) {
                throw ValidationException::withMessages([
                    "soals.{$index}.pernyataan_bs" => ["Soal #{$nomorSoal}: Minimal harus terdapat 1 pernyataan Benar/Salah."],
                ]);
            }

            $opsiNormalized = [];
            $kunciNormalized = [];
            $totalBobot = 0;

            foreach (array_values($rawPernyataan) as $pIdx => $item) {
                $noPernyataan = $pIdx + 1;
                $teks = trim((string) ($item['teks'] ?? ''));
                $kunci = strtolower(trim((string) ($item['kunci'] ?? '')));
                $bobot = isset($item['bobot']) && is_numeric($item['bobot']) ? (int) $item['bobot'] : 0;

                if ($teks === '') {
                    throw ValidationException::withMessages([
                        "soals.{$index}.pernyataan_bs" => ["Soal #{$nomorSoal}: Teks pernyataan ke-{$noPernyataan} wajib diisi."],
                    ]);
                }

                if (!in_array($kunci, ['benar', 'salah'], true)) {
                    throw ValidationException::withMessages([
                        "soals.{$index}.pernyataan_bs" => ["Soal #{$nomorSoal}: Kunci jawaban pernyataan ke-{$noPernyataan} wajib dipilih (Benar atau Salah)."],
                    ]);
                }

                if ($bobot < 0) {
                    $bobot = 0;
                }

                $opsiNormalized[] = [
                    'teks'  => $teks,
                    'bobot' => $bobot,
                ];
                $kunciNormalized[] = $kunci;
                $totalBobot += $bobot;
            }

            return [
                'opsi_jawaban'  => $opsiNormalized,
                'kunci_jawaban' => $kunciNormalized,
                'bobot_nilai'   => $totalBobot,
            ];
        }

        if (in_array($tipe, ['pg', 'pg_kompleks'], true)) {
            $rawOpsi = $soalData['opsi_jawaban'] ?? [];
            $existingOpsiMedia = ($existingSoal && is_array($existingSoal->opsi_media))
                ? $existingSoal->opsi_media
                : [];
            $removeOpsiMedia = $soalData['remove_opsi_media'] ?? [];

            $opsiNormalized = [];
            $opsiTerisiCount = 0;

            for ($optIdx = 0; $optIdx < 5; $optIdx++) {
                $teksOpsi = isset($rawOpsi[$optIdx]) ? trim((string) $rawOpsi[$optIdx]) : '';
                $opsiNormalized[$optIdx] = $teksOpsi;

                $isRemoved = isset($removeOpsiMedia[$optIdx]) && (string) $removeOpsiMedia[$optIdx] === '1';
                $hasExistingImg = !$isRemoved && !empty($existingOpsiMedia[$optIdx]);
                $hasNewImg = $request->hasFile("soals.{$index}.opsi_media.{$optIdx}");

                if ($teksOpsi !== '' || $hasExistingImg || $hasNewImg) {
                    $opsiTerisiCount++;
                }
            }

            if ($opsiTerisiCount < 2) {
                throw ValidationException::withMessages([
                    "soals.{$index}.opsi_jawaban" => ["Soal #{$nomorSoal}: Minimal harus mengisi 2 pilihan jawaban (berupa teks atau gambar)."],
                ]);
            }

            return [
                'opsi_jawaban'  => $opsiNormalized,
                'kunci_jawaban' => isset($soalData['kunci_jawaban']) ? array_values((array) $soalData['kunci_jawaban']) : null,
                'bobot_nilai'   => isset($soalData['bobot_nilai']) && is_numeric($soalData['bobot_nilai']) ? (int) $soalData['bobot_nilai'] : 10,
            ];
        }

        // Esai
        return [
            'opsi_jawaban'  => null,
            'kunci_jawaban' => null,
            'bobot_nilai'   => isset($soalData['bobot_nilai']) && is_numeric($soalData['bobot_nilai']) ? (int) $soalData['bobot_nilai'] : 10,
        ];
    }

    /**
     * Helper Private: Proses Simpan / Ganti / Hapus File Gambar per Opsi Jawaban (A-E)
     */
    private function processOpsiMediaFiles(
        Request $request,
        array $soalData,
        int $index,
        ?UjiSoal $existingSoal = null
    ): ?array {
        $tipe = $soalData['tipe'];
        $existingOpsiMedia = ($existingSoal && is_array($existingSoal->opsi_media))
            ? $existingSoal->opsi_media
            : [];

        // Jika bukan PG / PG Kompleks, bersihkan file gambar opsi lama bila ada
        if (!in_array($tipe, ['pg', 'pg_kompleks'], true)) {
            foreach ($existingOpsiMedia as $oldPath) {
                if (!empty($oldPath) && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            return null;
        }

        $removeOpsiMedia = $soalData['remove_opsi_media'] ?? [];
        $resultPaths = [];
        $hasAnyImage = false;

        for ($optIdx = 0; $optIdx < 5; $optIdx++) {
            $currentPath = $existingOpsiMedia[$optIdx] ?? null;
            $isRemoved = isset($removeOpsiMedia[$optIdx]) && (string) $removeOpsiMedia[$optIdx] === '1';

            if ($isRemoved) {
                if (!empty($currentPath) && Storage::disk('public')->exists($currentPath)) {
                    Storage::disk('public')->delete($currentPath);
                }
                $currentPath = null;
            }

            if ($request->hasFile("soals.{$index}.opsi_media.{$optIdx}")) {
                if (!empty($currentPath) && Storage::disk('public')->exists($currentPath)) {
                    Storage::disk('public')->delete($currentPath);
                }
                $currentPath = $request->file("soals.{$index}.opsi_media.{$optIdx}")
                    ->store('soal-images/opsi', 'public');
            }

            $resultPaths[$optIdx] = $currentPath;
            if (!empty($currentPath)) {
                $hasAnyImage = true;
            }
        }

        return $hasAnyImage ? $resultPaths : null;
    }

    public function index(Request $request)
    {
        $canViewAll = auth()->user()->can('sba.view_all');
        $currentUserId = (string) auth()->id();

        if ($canViewAll) {
            $authorIds = Uji::select('user_id')->distinct()->pluck('user_id')->toArray();
            if (!in_array((int) $currentUserId, array_map('intval', $authorIds), true)) {
                $authorIds[] = (int) $currentUserId;
            }
            $authors = \App\Models\User::whereIn('id', $authorIds)
                ->orderBy('name')
                ->get(['id', 'name']);

            $selectedAuthorId = $request->has('user_id')
                ? (string) $request->input('user_id', '')
                : $currentUserId;
        } else {
            $authors = collect();
            $selectedAuthorId = $currentUserId;
        }

        $mapels = $this->getActiveMapels();

        $query = Uji::with(['author', 'mapel', 'soals'])
            ->withCount(['hasils', 'hasilsSelesai']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_uji', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_uji')) {
            $query->where('kategori_uji', $request->kategori_uji);
        }

        if ($request->filled('id_mapel')) {
            if ($request->id_mapel === 'none') {
                $query->whereNull('id_mapel');
            } else {
                $query->where('id_mapel', $request->id_mapel);
            }
        }

        if ($canViewAll) {
            if ($selectedAuthorId !== '') {
                $query->where('user_id', $selectedAuthorId);
            }
        } else {
            $query->where('user_id', $currentUserId);
        }

        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['id', 'kode_uji', 'judul', 'kategori_uji', 'id_mapel', 'user_id', 'waktu_mulai', 'status'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest('id');
        }

        $perPage = (int) $request->get('per_page', 10);
        $ujis = $query->paginate($perPage)->withQueryString();

        return view('sba.index', compact('ujis', 'authors', 'mapels', 'canViewAll', 'selectedAuthorId'));
    }

    public function create()
    {
        $mapels = $this->getActiveMapels();
        $defaultMapelId = null;

        $idGuru = auth()->user()->id_guru ?? null;
        if (!empty($idGuru)) {
            $mapelGuru = $mapels->firstWhere('id_guru', (int) $idGuru);
            if ($mapelGuru) {
                $defaultMapelId = $mapelGuru->id_mapel;
            }
        }

        return view('sba.form', compact('mapels', 'defaultMapelId'));
    }

    public function edit(Uji $uji)
    {
        $this->authorizeUjiAccess($uji);

        $uji->load('soals');
        $mapels = $this->getActiveMapels();
        $defaultMapelId = $uji->id_mapel;

        return view('sba.form', compact('uji', 'mapels', 'defaultMapelId'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'kode_uji' => strtoupper(trim((string) $request->kode_uji)),
            'id_mapel' => $request->filled('id_mapel') ? $request->id_mapel : null,
        ]);

        $request->validate([
            'judul'                 => 'required|string|max:255',
            'kategori_uji'          => 'required|in:PSTS,PSAS,Sumatif,Umum',
            'id_mapel'              => 'nullable|integer|exists:mata_pelajaran,id_mapel',
            'kode_uji'              => ['required', 'string', 'max:12', 'regex:/^[A-Z0-9]+$/', 'unique:ujis,kode_uji'],
            'durasi_menit'          => 'required|integer|min:0',
            'toleransi_pelanggaran' => 'required|integer|min:0',
            'waktu_mulai'           => 'nullable|date',
            'waktu_selesai'         => 'nullable|date|after_or_equal:waktu_mulai',
            'maksimal_percobaan'    => 'nullable|integer|min:1',
            'soals'                 => 'required|array|min:1',
            'soals.*.pertanyaan'    => 'required|string',
            'soals.*.tipe'          => 'required|in:pg,pg_kompleks,benar_salah,esai',
            'soals.*.media'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'soals.*.opsi_media.*'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'judul.required'                 => 'Judul Asesmen wajib diisi.',
            'kategori_uji.required'          => 'Kategori Uji wajib dipilih.',
            'kategori_uji.in'                => 'Kategori Uji harus salah satu dari PSTS, PSAS, Sumatif, atau Umum.',
            'id_mapel.exists'                => 'Mata Pelajaran yang dipilih tidak valid.',
            'kode_uji.required'              => 'Kode Asesmen wajib diisi.',
            'kode_uji.max'                   => 'Kode Asesmen maksimal 12 karakter.',
            'kode_uji.regex'                 => 'Kode Asesmen hanya boleh berisi huruf (A-Z) dan angka (0-9) tanpa spasi atau simbol.',
            'kode_uji.unique'                => 'Kode Asesmen sudah digunakan oleh ujian lain. Silakan gunakan kode lain.',
            'durasi_menit.required'          => 'Durasi pengerjaan wajib diisi (isi 0 jika tanpa batas).',
            'waktu_selesai.after_or_equal'   => 'Waktu Selesai tidak boleh lebih awal dari Waktu Mulai.',
            'soals.required'                 => 'Minimal harus terdapat 1 butir soal.',
            'soals.*.pertanyaan.required'    => 'Teks pertanyaan pada butir soal wajib diisi.',
            'soals.*.media.image'            => 'Lampiran soal harus berupa file gambar.',
            'soals.*.media.mimes'            => 'Format gambar soal harus JPEG, PNG, JPG, GIF, atau WEBP.',
            'soals.*.media.max'              => 'Ukuran file gambar soal maksimal 2MB.',
            'soals.*.opsi_media.*.image'     => 'Lampiran pilihan jawaban harus berupa file gambar.',
            'soals.*.opsi_media.*.mimes'     => 'Format gambar pilihan jawaban harus JPEG, PNG, JPG, GIF, atau WEBP.',
            'soals.*.opsi_media.*.max'       => 'Ukuran file gambar pilihan jawaban maksimal 2MB.',
        ]);

        // Validasi & Normalisasi payload tiap soal sebelum masuk transaksi DB
        $normalizedSoals = [];
        foreach ($request->soals as $index => $soalData) {
            $normalizedSoals[$index] = $this->normalizeSoalPayload($request, $soalData, (int) $index);
        }

        $uji = DB::transaction(function () use ($request, $normalizedSoals) {
            $uji = Uji::create([
                'user_id'               => auth()->id(),
                'kode_uji'              => $request->kode_uji,
                'judul'                 => $request->judul,
                'kategori_uji'          => $request->kategori_uji ?? 'Umum',
                'id_mapel'              => $request->id_mapel,
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

            foreach ($request->soals as $index => $soalData) {
                $pathMedia = null;
                if ($request->hasFile("soals.{$index}.media")) {
                    $pathMedia = $request->file("soals.{$index}.media")->store('soal-images', 'public');
                }

                $opsiMediaPaths = $this->processOpsiMediaFiles($request, $soalData, (int) $index, null);
                $norm = $normalizedSoals[$index];

                UjiSoal::create([
                    'uji_id'        => $uji->id,
                    'tipe'          => $soalData['tipe'],
                    'pertanyaan'    => $soalData['pertanyaan'],
                    'media'         => $pathMedia,
                    'opsi_jawaban'  => $norm['opsi_jawaban'],
                    'opsi_media'    => $opsiMediaPaths,
                    'kunci_jawaban' => $norm['kunci_jawaban'],
                    'bobot_nilai'   => $norm['bobot_nilai'],
                ]);
            }

            return $uji;
        });

        $successMessage = 'Ujian dan soal berhasil dibuat dengan kode: ' . $uji->kode_uji;

        if ($request->expectsJson()) {
            session()->flash('success', $successMessage);
            return response()->json([
                'status'   => 'success',
                'message'  => $successMessage,
                'redirect' => route('sba.index'),
            ]);
        }

        return redirect()->route('sba.index')->with('success', $successMessage);
    }

    public function update(Request $request, $id)
    {
        $uji = Uji::with('soals')->findOrFail($id);
        $this->authorizeUjiAccess($uji);

        $request->merge([
            'kode_uji' => strtoupper(trim((string) $request->kode_uji)),
            'id_mapel' => $request->filled('id_mapel') ? $request->id_mapel : null,
        ]);

        $request->validate([
            'judul'                 => 'required|string|max:255',
            'kategori_uji'          => 'required|in:PSTS,PSAS,Sumatif,Umum',
            'id_mapel'              => 'nullable|integer|exists:mata_pelajaran,id_mapel',
            'kode_uji'              => ['required', 'string', 'max:12', 'regex:/^[A-Z0-9]+$/', 'unique:ujis,kode_uji,' . $uji->id],
            'durasi_menit'          => 'required|integer|min:0',
            'toleransi_pelanggaran' => 'required|integer|min:0',
            'waktu_mulai'           => 'nullable|date',
            'waktu_selesai'         => 'nullable|date|after_or_equal:waktu_mulai',
            'maksimal_percobaan'    => 'nullable|integer|min:1',
            'soals'                 => 'required|array|min:1',
            'soals.*.pertanyaan'    => 'required|string',
            'soals.*.tipe'          => 'required|in:pg,pg_kompleks,benar_salah,esai',
            'soals.*.media'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'soals.*.opsi_media.*'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'judul.required'                 => 'Judul Asesmen wajib diisi.',
            'kategori_uji.required'          => 'Kategori Uji wajib dipilih.',
            'kategori_uji.in'                => 'Kategori Uji harus salah satu dari PSTS, PSAS, Sumatif, atau Umum.',
            'id_mapel.exists'                => 'Mata Pelajaran yang dipilih tidak valid.',
            'kode_uji.required'              => 'Kode Asesmen wajib diisi.',
            'kode_uji.max'                   => 'Kode Asesmen maksimal 12 karakter.',
            'kode_uji.regex'                 => 'Kode Asesmen hanya boleh berisi huruf (A-Z) dan angka (0-9) tanpa spasi atau simbol.',
            'kode_uji.unique'                => 'Kode Asesmen sudah digunakan oleh ujian lain. Silakan gunakan kode lain.',
            'durasi_menit.required'          => 'Durasi pengerjaan wajib diisi (isi 0 jika tanpa batas).',
            'waktu_selesai.after_or_equal'   => 'Waktu Selesai tidak boleh lebih awal dari Waktu Mulai.',
            'soals.required'                 => 'Minimal harus terdapat 1 butir soal.',
            'soals.*.pertanyaan.required'    => 'Teks pertanyaan pada butir soal wajib diisi.',
            'soals.*.media.image'            => 'Lampiran soal harus berupa file gambar.',
            'soals.*.media.mimes'            => 'Format gambar soal harus JPEG, PNG, JPG, GIF, atau WEBP.',
            'soals.*.media.max'              => 'Ukuran file gambar soal maksimal 2MB.',
            'soals.*.opsi_media.*.image'     => 'Lampiran pilihan jawaban harus berupa file gambar.',
            'soals.*.opsi_media.*.mimes'     => 'Format gambar pilihan jawaban harus JPEG, PNG, JPG, GIF, atau WEBP.',
            'soals.*.opsi_media.*.max'       => 'Ukuran file gambar pilihan jawaban maksimal 2MB.',
        ]);

        $existingSoalsMap = $uji->soals->keyBy('id');
        $normalizedSoals = [];
        foreach ($request->soals as $index => $soalData) {
            $soalId = $soalData['id'] ?? null;
            $soalEksis = $soalId ? $existingSoalsMap->get((int) $soalId) : null;
            $normalizedSoals[$index] = $this->normalizeSoalPayload($request, $soalData, (int) $index, $soalEksis);
        }

        DB::transaction(function () use ($request, $uji, $normalizedSoals) {
            $uji->update([
                'kode_uji'              => $request->kode_uji,
                'judul'                 => $request->judul,
                'kategori_uji'          => $request->kategori_uji ?? 'Umum',
                'id_mapel'              => $request->id_mapel,
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

            $inputSoalIds = array_filter(array_column($request->soals, 'id'));
            $soalsToDelete = $uji->soals()->whereNotIn('id', $inputSoalIds)->get();
            foreach ($soalsToDelete as $delSoal) {
                if (is_array($delSoal->opsi_media)) {
                    foreach ($delSoal->opsi_media as $oldOptPath) {
                        if (!empty($oldOptPath) && Storage::disk('public')->exists($oldOptPath)) {
                            Storage::disk('public')->delete($oldOptPath);
                        }
                    }
                }
                $delSoal->delete();
            }

            foreach ($request->soals as $index => $soalData) {
                $soalId = $soalData['id'] ?? null;
                $soalEksis = $soalId ? UjiSoal::where('uji_id', $uji->id)->find($soalId) : new UjiSoal();
                if (!$soalEksis) {
                    $soalEksis = new UjiSoal();
                }

                $pathMedia = $soalEksis->media;

                if (isset($soalData['remove_media']) && $soalData['remove_media'] == '1') {
                    if (!empty($pathMedia) && Storage::disk('public')->exists($pathMedia)) {
                        Storage::disk('public')->delete($pathMedia);
                    }
                    $pathMedia = null;
                }

                if ($request->hasFile("soals.{$index}.media")) {
                    if (!empty($pathMedia) && Storage::disk('public')->exists($pathMedia)) {
                        Storage::disk('public')->delete($pathMedia);
                    }
                    $pathMedia = $request->file("soals.{$index}.media")->store('soal-images', 'public');
                }

                $opsiMediaPaths = $this->processOpsiMediaFiles(
                    $request,
                    $soalData,
                    (int) $index,
                    $soalEksis->exists ? $soalEksis : null
                );

                $norm = $normalizedSoals[$index];

                $soalEksis->fill([
                    'uji_id'        => $uji->id,
                    'tipe'          => $soalData['tipe'],
                    'pertanyaan'    => $soalData['pertanyaan'],
                    'media'         => $pathMedia,
                    'opsi_jawaban'  => $norm['opsi_jawaban'],
                    'opsi_media'    => $opsiMediaPaths,
                    'kunci_jawaban' => $norm['kunci_jawaban'],
                    'bobot_nilai'   => $norm['bobot_nilai'],
                ]);

                $soalEksis->save();
            }
        });

        $successMessage = 'Perubahan ujian dan gambar soal berhasil disimpan!';

        if ($request->expectsJson()) {
            session()->flash('success', $successMessage);
            return response()->json([
                'status'   => 'success',
                'message'  => $successMessage,
                'redirect' => route('sba.index'),
            ]);
        }

        return redirect()->route('sba.index')->with('success', $successMessage);
    }

    /**
     * Hapus 1 Butir Soal Spesifik (Otomatis hapus file media soal & gambar opsi jawaban)
     */
    public function destroySoal($soal_id)
    {
        $soal = \App\Models\UjiSoal::with('uji')->findOrFail($soal_id);
        if ($soal->uji) {
            $this->authorizeUjiAccess($soal->uji);
        }

        if (is_array($soal->opsi_media)) {
            foreach ($soal->opsi_media as $optPath) {
                if (!empty($optPath) && Storage::disk('public')->exists($optPath)) {
                    Storage::disk('public')->delete($optPath);
                }
            }
        }

        $soal->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Soal dan file media berhasil dihapus dari server.'
        ]);
    }

    /**
     * Hapus Seluruh Ujian (Otomatis hapus seluruh file media soal & gambar opsi jawaban)
     */
    public function destroy($id)
    {
        $uji = Uji::with('soals')->findOrFail($id);
        $this->authorizeUjiAccess($uji);

        foreach ($uji->soals as $soal) {
            if (is_array($soal->opsi_media)) {
                foreach ($soal->opsi_media as $optPath) {
                    if (!empty($optPath) && Storage::disk('public')->exists($optPath)) {
                        Storage::disk('public')->delete($optPath);
                    }
                }
            }
        }

        $uji->delete();

        return redirect()->route('sba.index')->with('success', 'Ujian beserta seluruh gambar soal berhasil dihapus!');
    }

    /**
     * Helper Private: Pemetaan NISN & Nama Kelas untuk Koleksi UjiHasil
     */
    private function attachSiswaKelasInfo($hasilsCollection): void
    {
        $siswaIds = $hasilsCollection->map(function ($row) {
            return $row->asesi ? $row->asesi->id_siswa : null;
        })->filter()->unique()->values()->all();

        $siswaMap = !empty($siswaIds)
            ? DB::table('siswa')
                ->whereIn('id_siswa', $siswaIds)
                ->get(['id_siswa', 'nisn', 'id_kelas'])
                ->keyBy('id_siswa')
            : collect();

        $kelasIds = $siswaMap->pluck('id_kelas')->filter()->unique()->values()->all();

        $kelasMap = !empty($kelasIds)
            ? DB::table('kelas')
                ->whereIn('id_kelas', $kelasIds)
                ->get(['id_kelas', 'nama_kelas'])
                ->keyBy('id_kelas')
            : collect();

        foreach ($hasilsCollection as $row) {
            $siswaData = ($row->asesi && !empty($row->asesi->id_siswa))
                ? $siswaMap->get($row->asesi->id_siswa)
                : null;

            $kelasData = ($siswaData && !empty($siswaData->id_kelas))
                ? $kelasMap->get($siswaData->id_kelas)
                : null;

            $row->nisn_display = ($siswaData && !empty($siswaData->nisn))
                ? (string) $siswaData->nisn
                : null;

            $row->nama_kelas_display = ($kelasData && !empty($kelasData->nama_kelas))
                ? (string) $kelasData->nama_kelas
                : null;
        }
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

        // Ambil daftar kelas unik dari peserta ujian ini untuk dropdown filter kelas
        $kelasIdsPeserta = DB::table('uji_hasils')
            ->join('users', 'uji_hasils.asesi_id', '=', 'users.id')
            ->join('siswa', 'users.id_siswa', '=', 'siswa.id_siswa')
            ->where('uji_hasils.uji_id', $uji->id)
            ->whereNotNull('siswa.id_kelas')
            ->distinct()
            ->pluck('siswa.id_kelas')
            ->toArray();

        if ($request->filled('id_kelas') && !in_array($request->id_kelas, $kelasIdsPeserta)) {
            $kelasIdsPeserta[] = $request->id_kelas;
        }

        $kelasList = !empty($kelasIdsPeserta)
            ? DB::table('kelas')
                ->whereIn('id_kelas', $kelasIdsPeserta)
                ->orderBy('nama_kelas')
                ->get(['id_kelas', 'nama_kelas'])
            : collect();

        $hasils = $this->getFilteredHasilQuery($request, $uji->id)
            ->paginate((int) $request->get('per_page', 10))
            ->withQueryString();

        $this->attachSiswaKelasInfo($hasils->getCollection());

        return view('sba.hasil', compact('uji', 'hasils', 'butuhPenilaianManual', 'jumlahPerluDinilai', 'kelasList'));
    }

    /**
     * Helper Private Query Filter Hasil Ujian
     */
    private function getFilteredHasilQuery(Request $request, $uji_id)
    {
        $query = UjiHasil::where('uji_id', $uji_id)->with('asesi');

        if ($request->filled('search')) {
            $search = $request->search;
            $userIdsByNisn = DB::table('users')
                ->join('siswa', 'users.id_siswa', '=', 'siswa.id_siswa')
                ->where('siswa.nisn', 'like', "%{$search}%")
                ->pluck('users.id');

            $query->where(function ($q) use ($search, $userIdsByNisn) {
                $q->where('asesi_id', 'like', "%{$search}%")
                    ->orWhere('nama_peserta_publik', 'like', "%{$search}%")
                    ->orWhereHas('asesi', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });

                if ($userIdsByNisn->isNotEmpty()) {
                    $q->orWhereIn('asesi_id', $userIdsByNisn);
                }
            });
        }

        if ($request->filled('id_kelas')) {
            $idKelas = $request->id_kelas;
            $userIdsInKelas = DB::table('users')
                ->join('siswa', 'users.id_siswa', '=', 'siswa.id_siswa')
                ->where('siswa.id_kelas', $idKelas)
                ->pluck('users.id');

            $query->whereIn('asesi_id', $userIdsInKelas);
        }

        if ($request->filled('status_pengerjaan')) {
            $query->where('status_pengerjaan', $request->status_pengerjaan);
        }

        if ($request->filled('tgl_mulai')) {
            $query->whereDate('waktu_selesai', '>=', $request->tgl_mulai);
        }
        if ($request->filled('tgl_selesai')) {
            $query->whereDate('waktu_selesai', '<=', $request->tgl_selesai);
        }

        if ($request->has('hide_duplicates') && $request->hide_duplicates == '1') {
            $subLatestIds = UjiHasil::where('uji_id', $uji_id)
                ->selectRaw('MAX(id) as max_id')
                ->groupByRaw("COALESCE(CAST(asesi_id AS CHAR), nama_peserta_publik)")
                ->pluck('max_id');

            $query->whereIn('id', $subLatestIds);
        }

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
        $uji = Uji::with('soals')->findOrFail($uji_id);
        $this->authorizeUjiAccess($uji);

        $format = $request->get('format', 'excel');
        $dataHasils = $this->getFilteredHasilQuery($request, $uji->id)->get();

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sba.exports.hasil_pdf', compact('uji', 'dataHasils'))
                ->setPaper('a4', 'landscape');

            return $pdf->download("Rekap_Hasil_{$uji->kode_uji}.pdf");
        }

        // Muat jawaban peserta secara batch untuk rincian skor per soal
        $dataHasils->load('jawabans');
        $this->attachSiswaKelasInfo($dataHasils);

        $daftarSoals = $uji->soals;
        $fileName = "Rekap_Hasil_{$uji->kode_uji}.xls";

        $headers = [
            "Content-Type"        => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Cache-Control"       => "max-age=0, no-cache, must-revalidate",
            "Pragma"              => "no-cache",
        ];

        $callback = function () use ($dataHasils, $daftarSoals) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Hasil Ujian</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
            echo '<body>';
            echo '<table border="1">';
            echo '<tr style="background-color: #5e72e4; color: #ffffff; font-weight: bold;">';
            echo '<th>No</th>';
            echo '<th>ID Asesi</th>';
            echo '<th>Nama Peserta</th>';
            echo '<th>Kelas</th>';
            echo '<th>Percobaan Ke</th>';
            echo '<th>Waktu Mulai</th>';
            echo '<th>Waktu Selesai</th>';
            echo '<th>Durasi</th>';
            echo '<th>Status</th>';
            echo '<th>Skor Akhir</th>';
            foreach ($daftarSoals as $soalIdx => $soalItem) {
                echo '<th>Soal ' . ($soalIdx + 1) . '</th>';
            }
            echo '</tr>';

            foreach ($dataHasils as $idx => $row) {
                $nama = $row->asesi ? $row->asesi->name : ($row->nama_peserta_publik ?? 'Guest');

                $idAsesiDisplay = !empty($row->nisn_display)
                    ? (string) $row->nisn_display
                    : (string) ($row->asesi_id ?? 'Publik');

                $namaKelasDisplay = !empty($row->nama_kelas_display)
                    ? (string) $row->nama_kelas_display
                    : '-';

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

                $mapJawabanSoal = $row->jawabans->keyBy('uji_soal_id');

                echo '<tr>';
                echo '<td>' . ($idx + 1) . '</td>';
                echo '<td style="mso-number-format:\'\@\';">' . htmlspecialchars($idAsesiDisplay) . '</td>';
                echo '<td>' . htmlspecialchars($nama) . '</td>';
                echo '<td>' . htmlspecialchars($namaKelasDisplay) . '</td>';
                echo '<td>' . $row->percobaan_ke . '</td>';
                echo '<td>' . ($row->waktu_mulai ? $row->waktu_mulai->format('d/m/Y H:i:s') : '-') . '</td>';
                echo '<td>' . ($row->waktu_selesai ? $row->waktu_selesai->format('d/m/Y H:i:s') : '-') . '</td>';
                echo '<td>' . $durasiText . '</td>';
                echo '<td>' . strtoupper($row->status_pengerjaan) . '</td>';
                echo '<td>' . $row->total_skor . '</td>';
                foreach ($daftarSoals as $soalItem) {
                    $jawabanItem = $mapJawabanSoal->get($soalItem->id);
                    $skorSoal = $jawabanItem ? $jawabanItem->skor_didapat : 0;
                    echo '<td>' . $skorSoal . '</td>';
                }
                echo '</tr>';
            }

            echo '</table>';
            echo '</body>';
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

        $mapJawaban = $hasil->jawabans->keyBy('uji_soal_id');

        return view('sba.hasil_detail', compact('hasil', 'mapJawaban'));
    }

    /**
     * Helper Private: Hitung Skor Satu Butir Soal Otomatis (PG, PG Kompleks, Benar/Salah)
     */
    private function evaluasiSkorSoalOtomatis(UjiSoal $soal, ?UjiJawaban $jawaban): float
    {
        if (!$jawaban || $jawaban->jawaban === null || $jawaban->jawaban === '') {
            return 0.0;
        }

        $kunciArray = is_array($soal->kunci_jawaban) ? $soal->kunci_jawaban : [];
        $bobotSoalTarget = (float) $soal->bobot_nilai;

        if ($soal->tipe === 'benar_salah') {
            $pernyataanList = is_array($soal->opsi_jawaban) ? $soal->opsi_jawaban : [];
            $jawabanSiswaArr = is_array($jawaban->jawaban)
                ? $jawaban->jawaban
                : (json_decode((string) $jawaban->jawaban, true) ?? []);

            // Hitung total bobot item di dalam JSON untuk menyesuaikan jika bobot_nilai soal diubah
            $totalBobotItemJson = 0.0;
            foreach ($pernyataanList as $item) {
                $totalBobotItemJson += (float) ($item['bobot'] ?? 0);
            }

            $faktorSkala = ($totalBobotItemJson > 0 && $bobotSoalTarget > 0)
                ? ($bobotSoalTarget / $totalBobotItemJson)
                : 1.0;

            $skorSoal = 0.0;
            $jumlahPernyataan = count($pernyataanList);

            foreach ($pernyataanList as $idx => $item) {
                $kunciItem = strtolower(trim((string) ($kunciArray[$idx] ?? ($item['kunci'] ?? ''))));
                $jawabItem = strtolower(trim((string) ($jawabanSiswaArr[$idx] ?? '')));

                $bobotItem = $totalBobotItemJson > 0
                    ? ((float) ($item['bobot'] ?? 0) * $faktorSkala)
                    : ($jumlahPernyataan > 0 ? ($bobotSoalTarget / $jumlahPernyataan) : 0.0);

                if ($kunciItem !== '' && $jawabItem === $kunciItem) {
                    $skorSoal += $bobotItem;
                }
            }

            return round($skorSoal, 2);
        }

        $kunciFiltered = array_values(array_filter($kunciArray, fn($v) => $v !== null && $v !== ''));
        if (empty($kunciFiltered)) {
            return 0.0;
        }

        if ($soal->tipe === 'pg_kompleks') {
            $rawJawaban = is_array($jawaban->jawaban)
                ? $jawaban->jawaban
                : (json_decode((string) $jawaban->jawaban, true) ?? []);

            $jawabanSiswaArray = array_values(array_unique(array_map('strval', $rawJawaban)));
            $kunciStrArray = array_values(array_unique(array_map('strval', $kunciFiltered)));
            sort($kunciStrArray);
            sort($jawabanSiswaArray);

            return ($kunciStrArray === $jawabanSiswaArray) ? $bobotSoalTarget : 0.0;
        }

        // PG (tangani jika jawaban tersimpan sebagai string tunggal maupun array 1 elemen)
        $jawabanTunggal = is_array($jawaban->jawaban)
            ? (string) (reset($jawaban->jawaban) ?: '')
            : trim((string) $jawaban->jawaban, "\" \t\n\r\0\x0B");

        return in_array($jawabanTunggal, array_map('strval', $kunciFiltered), true)
            ? $bobotSoalTarget
            : 0.0;
    }

    /**
     * Refresh / Hitung Ulang Nilai Peserta Sesuai Bobot Soal Terbaru
     */
    public function refreshNilaiHasil($hasil_id)
    {
        $hasil = UjiHasil::with(['uji.soals', 'jawabans'])->findOrFail($hasil_id);
        $this->authorizeUjiAccess($hasil->uji);

        $mapJawaban = $hasil->jawabans->keyBy('uji_soal_id');
        $skorOtomatisBaru = 0.0;
        $skorManualTotal = 0.0;

        DB::transaction(function () use ($hasil, $mapJawaban, &$skorOtomatisBaru, &$skorManualTotal) {
            foreach ($hasil->uji->soals as $soal) {
                $jawaban = $mapJawaban->get($soal->id);
                $kunciArray = is_array($soal->kunci_jawaban)
                    ? array_values(array_filter($soal->kunci_jawaban, fn($v) => $v !== null && $v !== ''))
                    : [];
                $isManualGraded = ($soal->tipe === 'esai') || (in_array($soal->tipe, ['pg', 'pg_kompleks']) && empty($kunciArray));

                if ($isManualGraded) {
                    if ($jawaban) {
                        $skorManualItem = (float) $jawaban->skor_didapat;
                        if ($skorManualItem > (float) $soal->bobot_nilai) {
                            $skorManualItem = (float) $soal->bobot_nilai;
                            $jawaban->forceFill(['skor_didapat' => $skorManualItem])->save();
                        }
                        $skorManualTotal += $skorManualItem;
                    }
                } else {
                    $skorItemBaru = $this->evaluasiSkorSoalOtomatis($soal, $jawaban);
                    if ($jawaban) {
                        $jawaban->forceFill(['skor_didapat' => $skorItemBaru])->save();
                    }
                    $skorOtomatisBaru += $skorItemBaru;
                }
            }

            $totalSkorBaru = round($skorOtomatisBaru + $skorManualTotal, 2);

            $hasil->forceFill([
                'skor_otomatis' => round($skorOtomatisBaru, 2),
                'total_skor'    => $totalSkorBaru,
            ])->save();
        });

        $hasil->refresh();

        return redirect()->back()->with(
            'success',
            "Nilai peserta berhasil diperbarui sesuai bobot soal terbaru (Total Skor Tersimpan: {$hasil->total_skor} Poin)."
        );
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

        $skorInputs = $request->input('skor_manual', $request->input('skor_esai', []));

        foreach ($hasil->uji->soals as $soal) {
            $jawaban = UjiJawaban::where('uji_hasil_id', $hasil->id)
                ->where('uji_soal_id', $soal->id)
                ->first();

            $kunciArray = is_array($soal->kunci_jawaban) ? array_filter($soal->kunci_jawaban, fn($v) => $v !== null && $v !== '') : [];
            $isManualGraded = ($soal->tipe === 'esai') || (in_array($soal->tipe, ['pg', 'pg_kompleks']) && empty($kunciArray));

            if ($isManualGraded) {
                $skorDidapat = isset($skorInputs[$soal->id]) ? (float) $skorInputs[$soal->id] : ($jawaban ? (float) $jawaban->skor_didapat : 0);

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
                if ($jawaban) {
                    $skorOtomatisPg += (float) $jawaban->skor_didapat;
                }
            }
        }

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

            if (in_array($soal->tipe, ['pg', 'pg_kompleks', 'benar_salah'])) {
                $skorItem = $this->evaluasiSkorSoalOtomatis($soal, $jawabanSiswa);
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
            \App\Models\UjiHasil::where('uji_id', $uji_id)
                ->whereIn('id', $request->ids)
                ->delete();

            return redirect()->back()->with('success', count($request->ids) . ' data hasil ujian berhasil dihapus secara massal.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data hasil ujian: ' . $e->getMessage());
        }
    }

    /**
     * Duplikat Ujian beserta Seluruh Butir Soal, Gambar Soal & Gambar Opsi (Tanpa Hasil Peserta)
     */
    public function duplicate($id)
    {
        $ujiAsli = Uji::with('soals')->findOrFail($id);
        $this->authorizeUjiAccess($ujiAsli);

        return DB::transaction(function () use ($ujiAsli) {
            do {
                $kodeBaru = strtoupper(\Illuminate\Support\Str::random(6));
            } while (Uji::where('kode_uji', $kodeBaru)->exists());

            $baseJudul = $ujiAsli->judul . '_copy';
            $judulBaru = $baseJudul;
            $counter = 2;
            while (Uji::where('judul', $judulBaru)->exists()) {
                $judulBaru = $baseJudul . '_' . $counter;
                $counter++;
            }

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
            $ujiBaru->status   = 'draft';
            $ujiBaru->save();

            foreach ($ujiAsli->soals as $soalAsli) {
                $pathMediaBaru = null;

                if ($soalAsli->media && Storage::disk('public')->exists($soalAsli->media)) {
                    $ext = pathinfo($soalAsli->media, PATHINFO_EXTENSION);
                    $namaFileBaru = 'soal-images/' . \Illuminate\Support\Str::random(40) . ($ext ? '.' . $ext : '');
                    Storage::disk('public')->copy($soalAsli->media, $namaFileBaru);
                    $pathMediaBaru = $namaFileBaru;
                }

                $opsiMediaBaru = null;
                if (is_array($soalAsli->opsi_media)) {
                    $copiedOpsi = [];
                    $hasCopied = false;
                    foreach ($soalAsli->opsi_media as $optIdx => $optPath) {
                        if (!empty($optPath) && Storage::disk('public')->exists($optPath)) {
                            $extOpt = pathinfo($optPath, PATHINFO_EXTENSION);
                            $namaOptBaru = 'soal-images/opsi/' . \Illuminate\Support\Str::random(40) . ($extOpt ? '.' . $extOpt : '');
                            Storage::disk('public')->copy($optPath, $namaOptBaru);
                            $copiedOpsi[$optIdx] = $namaOptBaru;
                            $hasCopied = true;
                        } else {
                            $copiedOpsi[$optIdx] = null;
                        }
                    }
                    if ($hasCopied) {
                        $opsiMediaBaru = $copiedOpsi;
                    }
                }

                UjiSoal::create([
                    'uji_id'        => $ujiBaru->id,
                    'tipe'          => $soalAsli->tipe,
                    'pertanyaan'    => $soalAsli->pertanyaan,
                    'media'         => $pathMediaBaru,
                    'opsi_jawaban'  => $soalAsli->opsi_jawaban,
                    'opsi_media'    => $opsiMediaBaru,
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
     * Unduh Template Excel (.xlsx) untuk Import Soal SBA (Mendukung Kolom Dinamis opsi_a, opsi_b, ... opsi_f, dst.)
     */
    public function downloadTemplateSoal()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Soal SBA');

        // 1. Header Kolom Standar (Kolom opsi_* dapat ditambah seperti opsi_f, opsi_g, dst. untuk benar_salah)
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
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '5E72E4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // 2. Contoh Pengisian 4 Tipe Soal (PG, PG Kompleks, Benar Salah, Esai)
        $contohData = [
            [1, 'pg', 'Ibu kota Provinsi Jawa Tengah adalah...', 'Bandung', 'Surabaya', 'Semarang', 'Surakarta', 'Yogyakarta', 'C', 10],
            [2, 'pg_kompleks', 'Manakah di bawah ini yang termasuk bilangan prima?', '2', '4', '5', '9', '11', 'A,C,E', 20],
            [3, 'benar_salah', 'Perhatikan informasi berikut mengenai tata surya. Tentukan Benar (B) atau Salah (S) untuk setiap pernyataan di bawah ini!', 'Matahari adalah pusat tata surya', 'Bumi memiliki dua satelit alami', 'Planet terbesar di tata surya adalah Jupiter', '', '', 'B,S,B', '10,10,20'],
            [4, 'esai', 'Jelaskan secara singkat proses terjadinya siklus air!', '', '', '', '', '', '', 30],
        ];
        $sheet->fromArray($contohData, null, 'A2');

        // Data Validation Dropdown untuk kolom tipe_soal (B2:B200)
        for ($row = 2; $row <= 200; $row++) {
            $validation = $sheet->getCell("B{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"pg,pg_kompleks,benar_salah,esai"');
        }

        // Auto-size kolom
        for ($colIdx = 1; $colIdx <= count($headers); $colIdx++) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Import_Soal_SBA.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export Daftar Soal dari Form ke Excel (.xlsx) dengan Kolom Dinamis Full Huruf (opsi_a..opsi_e, opsi_f, opsi_g, dst.)
     * Soal/Opsi yang memiliki gambar otomatis ditandai dengan teks "(isi gambar)"
     */
    public function exportSoalExcel(Request $request)
    {
        $rawSoals = $request->input('soals', []);
        if (!is_array($rawSoals) || count($rawSoals) === 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak ada butir soal untuk di-export.',
            ], 422);
        }

        // Hitung jumlah kolom opsi terbanyak (minimal 5 untuk opsi_a..opsi_e, bertambah dinamis jika pernyataan > 5)
        $maxOpsiCount = 5;
        foreach ($rawSoals as $soal) {
            if (strtolower(trim((string) ($soal['tipe'] ?? ''))) === 'benar_salah') {
                $jumlahPernyataan = is_array($soal['pernyataan_bs'] ?? null)
                    ? count($soal['pernyataan_bs'])
                    : 0;
                if ($jumlahPernyataan > $maxOpsiCount) {
                    $maxOpsiCount = $jumlahPernyataan;
                }
            }
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Soal SBA');

        // 1. Susun Header Dinamis Full Huruf (no, tipe_soal, pertanyaan, opsi_a..opsi_e, opsi_f, opsi_g.., kunci_jawaban, bobot_nilai)
        $headers = [
            'no',
            'tipe_soal',
            'pertanyaan',
        ];

        for ($i = 0; $i < $maxOpsiCount; $i++) {
            $suffixHuruf = strtolower(Coordinate::stringFromColumnIndex($i + 1)); // a..e, f, g, ..., z, aa, ab
            $headers[] = 'opsi_' . $suffixHuruf;
        }

        $headers[] = 'kunci_jawaban';
        $headers[] = 'bobot_nilai';

        $sheet->fromArray($headers, null, 'A1');

        $totalCols = count($headers);
        $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '5E72E4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $indexToHuruf = [0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E'];
        $rowsData = [];

        foreach (array_values($rawSoals) as $idx => $soal) {
            $no = $idx + 1;
            $tipe = strtolower(trim((string) ($soal['tipe'] ?? 'pg')));
            $pertanyaan = trim((string) ($soal['pertanyaan'] ?? ''));
            $hasMedia = !empty($soal['has_media']) && (string) $soal['has_media'] === '1';

            if ($hasMedia) {
                $pertanyaan = $pertanyaan !== ''
                    ? $pertanyaan . ' (isi gambar)'
                    : '(isi gambar)';
            }

            $opsiCols = array_fill(0, $maxOpsiCount, '');
            $kunciCol = '';
            $bobotCol = isset($soal['bobot_nilai']) && is_numeric($soal['bobot_nilai'])
                ? (int) $soal['bobot_nilai']
                : 10;

            if ($tipe === 'benar_salah') {
                $pernyataanList = is_array($soal['pernyataan_bs'] ?? null)
                    ? array_values($soal['pernyataan_bs'])
                    : [];
                $kunciParts = [];
                $bobotParts = [];

                foreach ($pernyataanList as $pIdx => $item) {
                    $opsiCols[$pIdx] = trim((string) ($item['teks'] ?? ''));
                    $kunciVal = strtolower(trim((string) ($item['kunci'] ?? '')));
                    if ($kunciVal === 'benar') {
                        $kunciParts[] = 'B';
                    } elseif ($kunciVal === 'salah') {
                        $kunciParts[] = 'S';
                    }
                    $bobotParts[] = isset($item['bobot']) && is_numeric($item['bobot'])
                        ? (int) $item['bobot']
                        : 10;
                }

                $kunciCol = implode(',', $kunciParts);
                $bobotCol = !empty($bobotParts) ? implode(',', $bobotParts) : $bobotCol;
            } elseif (in_array($tipe, ['pg', 'pg_kompleks'], true)) {
                $rawOpsi = is_array($soal['opsi_jawaban'] ?? null) ? $soal['opsi_jawaban'] : [];
                $rawOpsiMedia = is_array($soal['has_opsi_media'] ?? null) ? $soal['has_opsi_media'] : [];

                for ($optIdx = 0; $optIdx < 5; $optIdx++) {
                    $teksOpsi = trim((string) ($rawOpsi[$optIdx] ?? ''));
                    $hasOptImg = !empty($rawOpsiMedia[$optIdx]) && (string) $rawOpsiMedia[$optIdx] === '1';

                    if ($hasOptImg) {
                        $teksOpsi = $teksOpsi !== ''
                            ? $teksOpsi . ' (isi gambar)'
                            : '(isi gambar)';
                    }

                    $opsiCols[$optIdx] = $teksOpsi;
                }

                $rawKunci = is_array($soal['kunci_jawaban'] ?? null) ? $soal['kunci_jawaban'] : [];
                $hurufList = [];
                foreach ($rawKunci as $kVal) {
                    $kInt = (int) $kVal;
                    if (isset($indexToHuruf[$kInt]) && !in_array($indexToHuruf[$kInt], $hurufList, true)) {
                        $hurufList[] = $indexToHuruf[$kInt];
                    }
                }
                sort($hurufList);
                $kunciCol = implode(',', $hurufList);
            } else {
                // Esai
                $kunciCol = '';
            }

            $rowsData[] = array_merge(
                [$no, $tipe, $pertanyaan],
                $opsiCols,
                [$kunciCol, $bobotCol]
            );
        }

        $sheet->fromArray($rowsData, null, 'A2');

        // Data Validation Dropdown untuk kolom tipe_soal (B2:B200)
        $maxRowValidation = max(200, count($rowsData) + 20);
        for ($row = 2; $row <= $maxRowValidation; $row++) {
            $validation = $sheet->getCell("B{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"pg,pg_kompleks,benar_salah,esai"');
        }

        for ($colIdx = 1; $colIdx <= $totalCols; $colIdx++) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $kodeUji = preg_replace('/[^A-Za-z0-9_-]/', '', strtoupper(trim((string) $request->input('kode_uji', ''))));
        $fileName = $kodeUji !== ''
            ? "Export_Soal_SBA_{$kodeUji}.xlsx"
            : 'Export_Soal_SBA_' . date('Ymd_His') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * AJAX Endpoint: Smart Header Parser untuk File Excel Soal (Mendukung Banyak Kolom opsi_*)
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

            // 1. Smart Header Detection: Petakan indeks kolom berdasarkan nama header pada baris pertama
            $headerRow = $rows[0];
            $colMap = [];
            $opsiColIndices = [];
            $relevantColIndices = [];

            foreach ($headerRow as $colIdx => $rawHeader) {
                $hName = strtolower(trim((string) $rawHeader));
                if ($hName === '') {
                    continue;
                }

                if (in_array($hName, ['no', 'tipe_soal', 'pertanyaan', 'kunci_jawaban', 'bobot_nilai'], true)) {
                    $colMap[$hName] = $colIdx;
                    $relevantColIndices[] = $colIdx;
                } elseif (str_starts_with($hName, 'opsi_')) {
                    $opsiColIndices[] = $colIdx;
                    $relevantColIndices[] = $colIdx;
                }
            }

            $requiredHeaders = ['tipe_soal', 'pertanyaan', 'kunci_jawaban', 'bobot_nilai'];
            $missingHeaders = array_values(array_diff($requiredHeaders, array_keys($colMap)));

            if (!empty($missingHeaders) || count($opsiColIndices) < 2) {
                $detailMissing = [];
                if (!empty($missingHeaders)) {
                    $detailMissing[] = 'kolom wajib tidak ditemukan: ' . implode(', ', $missingHeaders);
                }
                if (count($opsiColIndices) < 2) {
                    $detailMissing[] = 'minimal harus terdapat 2 kolom dengan awalan opsi_ (contoh: opsi_a, opsi_b, ..., opsi_f, opsi_g, dst.)';
                }

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Header Excel tidak sesuai! (' . implode('; ', $detailMissing) . ').',
                ], 422);
            }

            $colIdxTipe       = $colMap['tipe_soal'];
            $colIdxPertanyaan = $colMap['pertanyaan'];
            $colIdxKunci      = $colMap['kunci_jawaban'];
            $colIdxBobot      = $colMap['bobot_nilai'];

            // 2. Parsing & Validasi Tiap Baris Soal (Mulai Baris 2)
            $parsedSoals = [];
            $errors = [];
            $hurufToIndex = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4];

            foreach (array_slice($rows, 1) as $idx => $row) {
                $excelRowNum = $idx + 2;

                // Lewati baris yang benar-benar kosong pada seluruh kolom relevan
                $joined = '';
                foreach ($relevantColIndices as $rColIdx) {
                    $joined .= trim((string) ($row[$rColIdx] ?? ''));
                }
                if ($joined === '') {
                    continue;
                }

                $tipe       = strtolower(trim((string) ($row[$colIdxTipe] ?? '')));
                $pertanyaan = trim((string) ($row[$colIdxPertanyaan] ?? ''));

                // Ambil seluruh nilai dari kolom berawalan opsi_* secara berurutan (opsi_a, opsi_b, ..., opsi_f, dst.)
                $allOpsiValues = [];
                foreach ($opsiColIndices as $oColIdx) {
                    $allOpsiValues[] = trim((string) ($row[$oColIdx] ?? ''));
                }

                $kunciRawClean = preg_replace('/[\s\x{00A0}\x{200B}-\x{200D}\x{FEFF}]+/u', '', (string) ($row[$colIdxKunci] ?? ''));
                $kunciRaw = strtoupper((string) $kunciRawClean);

                $bobotRawClean = preg_replace('/[\s\x{00A0}\x{200B}-\x{200D}\x{FEFF}]+/u', '', (string) ($row[$colIdxBobot] ?? ''));
                $bobotRaw = (string) $bobotRawClean;
                $bobot    = is_numeric($bobotRaw) && (int) $bobotRaw > 0 ? (int) $bobotRaw : 10;

                if (!in_array($tipe, ['pg', 'pg_kompleks', 'benar_salah', 'esai'], true)) {
                    $errors[] = "Baris {$excelRowNum}: Tipe soal '{$tipe}' tidak valid (gunakan pg, pg_kompleks, benar_salah, atau esai).";
                    continue;
                }

                if ($pertanyaan === '') {
                    $errors[] = "Baris {$excelRowNum}: Kolom pertanyaan tidak boleh kosong.";
                    continue;
                }

                $opsiJawaban = null;
                $kunciJawaban = [];

                if ($tipe === 'benar_salah') {
                    // Ambil seluruh pernyataan yang terisi dari seluruh kolom opsi_* (tidak dibatasi 5)
                    $pernyataanTerisi = array_values(array_filter($allOpsiValues, fn($v) => $v !== ''));
                    $jumlahPernyataan = count($pernyataanTerisi);

                    if ($jumlahPernyataan === 0) {
                        $errors[] = "Baris {$excelRowNum}: Soal benar_salah minimal wajib mengisi 1 pernyataan pada kolom opsi_*.";
                        continue;
                    }

                    if ($kunciRaw === '') {
                        $errors[] = "Baris {$excelRowNum}: Kunci jawaban wajib diisi untuk tipe benar_salah (contoh: B,S,B).";
                        continue;
                    }

                    $kunciParts = array_values(array_filter(array_map('trim', explode(',', $kunciRaw)), fn($v) => $v !== ''));
                    if (count($kunciParts) !== $jumlahPernyataan) {
                        $errors[] = "Baris {$excelRowNum}: Jumlah kunci jawaban (" . count($kunciParts) . ") harus sama dengan jumlah pernyataan yang terisi ({$jumlahPernyataan}).";
                        continue;
                    }

                    $validKunciBs = true;
                    foreach ($kunciParts as $kPart) {
                        if (in_array($kPart, ['B', 'BENAR', 'TRUE', '1'], true)) {
                            $kunciJawaban[] = 'benar';
                        } elseif (in_array($kPart, ['S', 'SALAH', 'FALSE', '0'], true)) {
                            $kunciJawaban[] = 'salah';
                        } else {
                            $errors[] = "Baris {$excelRowNum}: Kunci '{$kPart}' pada benar_salah tidak valid (gunakan B/BENAR atau S/SALAH dipisah koma).";
                            $validKunciBs = false;
                            break;
                        }
                    }
                    if (!$validKunciBs) {
                        continue;
                    }

                    // Parsing bobot: dipisah koma per pernyataan (misal "10,15,5") atau 1 angka total (dibagi rata)
                    $bobotPerItem = [];
                    if ($bobotRaw !== '' && str_contains($bobotRaw, ',')) {
                        $bobotParts = array_map('trim', explode(',', $bobotRaw));
                        if (count($bobotParts) !== $jumlahPernyataan) {
                            $errors[] = "Baris {$excelRowNum}: Jika bobot dipisah koma, jumlahnya (" . count($bobotParts) . ") harus sama dengan jumlah pernyataan ({$jumlahPernyataan}).";
                            continue;
                        }
                        foreach ($bobotParts as $bp) {
                            $bobotPerItem[] = is_numeric($bp) && (int) $bp >= 0 ? (int) $bp : 0;
                        }
                    } else {
                        $totalTarget = is_numeric($bobotRaw) && (int) $bobotRaw > 0 ? (int) $bobotRaw : ($jumlahPernyataan * 10);
                        $base = intdiv($totalTarget, $jumlahPernyataan);
                        $rem = $totalTarget % $jumlahPernyataan;
                        for ($i = 0; $i < $jumlahPernyataan; $i++) {
                            $bobotPerItem[] = $base + ($i === $jumlahPernyataan - 1 ? $rem : 0);
                        }
                    }

                    $opsiJawaban = [];
                    foreach ($pernyataanTerisi as $i => $teksPernyataan) {
                        $opsiJawaban[] = [
                            'teks'  => $teksPernyataan,
                            'bobot' => $bobotPerItem[$i],
                        ];
                    }
                    $bobot = array_sum($bobotPerItem);
                } elseif (in_array($tipe, ['pg', 'pg_kompleks'], true)) {
                    // Untuk PG & PG Kompleks, gunakan 5 kolom opsi pertama (opsi_a..opsi_e)
                    $opsiPg = [
                        $allOpsiValues[0] ?? '',
                        $allOpsiValues[1] ?? '',
                        $allOpsiValues[2] ?? '',
                        $allOpsiValues[3] ?? '',
                        $allOpsiValues[4] ?? '',
                    ];

                    if ($opsiPg[0] === '' || $opsiPg[1] === '') {
                        $errors[] = "Baris {$excelRowNum}: Soal {$tipe} minimal wajib mengisi 2 pilihan jawaban pertama (Opsi A dan Opsi B).";
                        continue;
                    }

                    if ($kunciRaw === '') {
                        $errors[] = "Baris {$excelRowNum}: Kunci jawaban wajib diisi untuk tipe {$tipe}.";
                        continue;
                    }

                    $opsiJawaban = $opsiPg;

                    if ($tipe === 'pg') {
                        if (!isset($hurufToIndex[$kunciRaw])) {
                            $errors[] = "Baris {$excelRowNum}: Kunci jawaban PG harus berupa satu huruf (A, B, C, D, atau E).";
                            continue;
                        }
                        $opsiIdxKunci = (int) $hurufToIndex[$kunciRaw];
                        if ($opsiPg[$opsiIdxKunci] === '') {
                            $errors[] = "Baris {$excelRowNum}: Kunci jawaban '{$kunciRaw}' menunjuk pada opsi yang kosong.";
                            continue;
                        }
                        $kunciJawaban = [(string) $opsiIdxKunci];
                    } else {
                        $parts = array_unique(array_filter(array_map('trim', explode(',', $kunciRaw))));
                        $validKunci = true;
                        foreach ($parts as $huruf) {
                            if (!isset($hurufToIndex[$huruf])) {
                                $errors[] = "Baris {$excelRowNum}: Kunci PG Kompleks '{$huruf}' tidak valid (gunakan huruf A-E dipisah koma).";
                                $validKunci = false;
                                break;
                            }
                            $opsiIdxKunci = (int) $hurufToIndex[$huruf];
                            if ($opsiPg[$opsiIdxKunci] === '') {
                                $errors[] = "Baris {$excelRowNum}: Kunci jawaban '{$huruf}' menunjuk pada opsi yang kosong.";
                                $validKunci = false;
                                break;
                            }
                            $kunciJawaban[] = (string) $opsiIdxKunci;
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
                    'opsi_media'    => null,
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