<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Uji;
use App\Models\UjiSoal;
use App\Models\UjiHasil;
use App\Models\UjiJawaban;
use App\Traits\AuthorizesSbaAccess;
use Illuminate\Support\Facades\DB;

class SbaHasilController extends Controller
{
    use AuthorizesSbaAccess;

    /**
     * Helper Private: Smart Normalized Exact Match untuk Jawaban Isian Singkat
     */
    private function isJawabanIsianCocok(string $jawabanSiswa, array $daftarKunci): bool
    {
        $siswaTrim = trim($jawabanSiswa);
        if ($siswaTrim === '') {
            return false;
        }

        $kunciVariants = [];
        foreach ($daftarKunci as $kItem) {
            foreach (explode('|', (string) $kItem) as $sub) {
                $clean = trim($sub);
                if ($clean !== '') {
                    $kunciVariants[] = $clean;
                }
            }
        }

        if (empty($kunciVariants)) {
            return false;
        }

        $normalizeNumber = static function (string $val): ?float {
            $cleaned = preg_replace('/\s+/u', '', trim($val));
            if ($cleaned === '') {
                return null;
            }
            if (substr_count($cleaned, ',') === 1 && substr_count($cleaned, '.') === 0) {
                $cleaned = str_replace(',', '.', $cleaned);
            }
            if (is_numeric($cleaned)) {
                return (float) $cleaned;
            }
            return null;
        };

        $normalizeText = static function (string $val): string {
            $lower = mb_strtolower(trim($val), 'UTF-8');
            $stripped = preg_replace('/^[\p{P}\p{S}\s]+|[\p{P}\p{S}\s]+$/u', '', $lower);
            return trim((string) preg_replace('/\s+/u', ' ', (string) $stripped));
        };

        $siswaNum = $normalizeNumber($siswaTrim);
        $siswaTextNorm = $normalizeText($siswaTrim);

        foreach ($kunciVariants as $kunci) {
            $kunciNum = $normalizeNumber($kunci);

            if ($kunciNum !== null) {
                if ($siswaNum !== null && abs($siswaNum - $kunciNum) < 1e-9) {
                    return true;
                }
                continue;
            }

            $kunciTextNorm = $normalizeText($kunci);
            if ($kunciTextNorm !== '' && $siswaTextNorm === $kunciTextNorm) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper Private: Pemetaan NISN, NIS (NIPD) & Nama Kelas untuk Koleksi UjiHasil
     */
    private function attachSiswaKelasInfo($hasilsCollection): void
    {
        $siswaIds = $hasilsCollection->map(function ($row) {
            return $row->asesi ? $row->asesi->id_siswa : null;
        })->filter()->unique()->values()->all();

        $siswaMap = !empty($siswaIds)
            ? DB::table('siswa')
                ->whereIn('id_siswa', $siswaIds)
                ->get(['id_siswa', 'nisn', 'nipd', 'id_kelas'])
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

            $row->nipd_display = ($siswaData && !empty($siswaData->nipd))
                ? (string) $siswaData->nipd
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
                ->where(function ($sub) use ($search) {
                    $sub->where('siswa.nisn', 'like', "%{$search}%")
                        ->orWhere('siswa.nipd', 'like', "%{$search}%");
                })
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

        $dataHasils->load('jawabans');
        $this->attachSiswaKelasInfo($dataHasils);

        // Urutkan eksplisit berdasarkan Kelas (abjad/natural A-Z), lalu NIS / nipd (smallest to largest)
        $dataHasils = $dataHasils->sort(function ($a, $b) {
            $kelasA = trim((string) ($a->nama_kelas_display ?? ''));
            $kelasB = trim((string) ($b->nama_kelas_display ?? ''));

            if ($kelasA === '' && $kelasB !== '') {
                return 1;
            }
            if ($kelasA !== '' && $kelasB === '') {
                return -1;
            }

            $cmpKelas = strnatcasecmp($kelasA, $kelasB);
            if ($cmpKelas !== 0) {
                return $cmpKelas;
            }

            $nisA = trim((string) ($a->nipd_display ?? ''));
            $nisB = trim((string) ($b->nipd_display ?? ''));

            if ($nisA === '' && $nisB !== '') {
                return 1;
            }
            if ($nisA !== '' && $nisB === '') {
                return -1;
            }

            if (ctype_digit($nisA) && ctype_digit($nisB)) {
                $cmpNis = (int) $nisA <=> (int) $nisB;
                if ($cmpNis !== 0) {
                    return $cmpNis;
                }
            } else {
                $cmpNis = strnatcasecmp($nisA, $nisB);
                if ($cmpNis !== 0) {
                    return $cmpNis;
                }
            }

            return ((int) $a->percobaan_ke) <=> ((int) $b->percobaan_ke);
        })->values();

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
            echo '<th>NIS</th>';
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

                $nisDisplay = !empty($row->nipd_display)
                    ? (string) $row->nipd_display
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
                echo '<td style="mso-number-format:\'\@\';">' . htmlspecialchars($nisDisplay) . '</td>';
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
     * Helper Private: Hitung Skor Satu Butir Soal Otomatis (PG, PG Kompleks, Benar/Salah, Isian Singkat/Esai Berkunci)
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

        $kunciFiltered = array_values(array_filter($kunciArray, fn($v) => trim((string) $v) !== ''));
        if (empty($kunciFiltered)) {
            return 0.0;
        }

        if ($soal->tipe === 'esai') {
            $jawabanText = is_array($jawaban->jawaban)
                ? (string) (reset($jawaban->jawaban) ?: '')
                : (string) $jawaban->jawaban;

            return $this->isJawabanIsianCocok($jawabanText, $kunciFiltered)
                ? $bobotSoalTarget
                : 0.0;
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

        // PG
        $jawabanTunggal = is_array($jawaban->jawaban)
            ? (string) (reset($jawaban->jawaban) ?: '')
            : trim((string) $jawaban->jawaban, "\" \t\n\r\0\x0B");

        return in_array($jawabanTunggal, array_map('strval', $kunciFiltered), true)
            ? $bobotSoalTarget
            : 0.0;
    }

    /**
     * Helper Private: Cek apakah suatu butir soal membutuhkan penilaian manual
     */
    private function isSoalManualGraded(UjiSoal $soal): bool
    {
        $kunciArray = is_array($soal->kunci_jawaban)
            ? array_values(array_filter($soal->kunci_jawaban, fn($v) => trim((string) $v) !== ''))
            : [];

        if ($soal->tipe === 'esai') {
            return empty($kunciArray);
        }

        if (in_array($soal->tipe, ['pg', 'pg_kompleks'], true)) {
            return empty($kunciArray);
        }

        return false;
    }

    /**
     * Helper Private: Proses Hitung Ulang & Simpan Skor untuk 1 Record UjiHasil
     */
    private function recalculateSingleHasil(UjiHasil $hasil, $soals): float
    {
        $mapJawaban = $hasil->jawabans->keyBy('uji_soal_id');
        $skorOtomatisBaru = 0.0;
        $skorManualTotal = 0.0;

        foreach ($soals as $soal) {
            $jawaban = $mapJawaban->get($soal->id);
            $isManualGraded = $this->isSoalManualGraded($soal);

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

        return $totalSkorBaru;
    }

    /**
     * Refresh / Hitung Ulang Nilai Satu Peserta Sesuai Bobot & Kunci Soal Terbaru
     */
    public function refreshNilaiHasil($hasil_id)
    {
        $hasil = UjiHasil::with(['uji.soals', 'jawabans'])->findOrFail($hasil_id);
        $this->authorizeUjiAccess($hasil->uji);

        $totalSkorBaru = DB::transaction(function () use ($hasil) {
            return $this->recalculateSingleHasil($hasil, $hasil->uji->soals);
        });

        return redirect()->back()->with(
            'success',
            "Nilai peserta berhasil diperbarui sesuai bobot & kunci soal terbaru (Total Skor Tersimpan: {$totalSkorBaru} Poin)."
        );
    }

    /**
     * Refresh / Hitung Ulang Nilai Massal (Peserta Terpilih atau Seluruh Peserta Ujian)
     */
    public function bulkRefreshNilai(Request $request, $uji_id)
    {
        $uji = Uji::with('soals')->findOrFail($uji_id);
        $this->authorizeUjiAccess($uji);

        $mode = $request->input('mode', 'selected');

        if ($mode === 'all') {
            $hasils = UjiHasil::with('jawabans')
                ->where('uji_id', $uji->id)
                ->get();
        } else {
            $request->validate([
                'ids'   => 'required|array|min:1',
                'ids.*' => 'integer|exists:uji_hasils,id',
            ], [
                'ids.required' => 'Pilih minimal 1 peserta untuk diperbarui nilainya.',
            ]);

            $hasils = UjiHasil::with('jawabans')
                ->where('uji_id', $uji->id)
                ->whereIn('id', $request->ids)
                ->get();
        }

        if ($hasils->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data hasil peserta yang dapat diperbarui.');
        }

        $soals = $uji->soals;

        DB::transaction(function () use ($hasils, $soals) {
            foreach ($hasils as $hasil) {
                $this->recalculateSingleHasil($hasil, $soals);
            }
        });

        $jumlahDiproses = $hasils->count();
        $keteranganScope = $mode === 'all' ? 'seluruh peserta pada asesmen ini' : 'peserta terpilih';

        return redirect()->back()->with(
            'success',
            "Berhasil menghitung ulang dan memperbarui nilai untuk {$jumlahDiproses} {$keteranganScope} sesuai bobot & kunci soal terbaru."
        );
    }

    /**
     * Simpan Penilaian Manual / Take Over Guru (Uraian Berkunci maupun Tanpa Kunci, & PG/PGK Tanpa Kunci) serta Finalisasi Skor
     */
    public function simpanPenilaianEsai(Request $request, $hasil_id)
    {
        $hasil = UjiHasil::with('uji.soals')->findOrFail($hasil_id);
        $this->authorizeUjiAccess($hasil->uji);

        $skorManualTotal = 0.0;
        $skorOtomatisTotal = 0.0;

        $skorInputs = $request->input('skor_manual', $request->input('skor_esai', []));
        if (!is_array($skorInputs)) {
            $skorInputs = [];
        }

        DB::transaction(function () use ($hasil, $skorInputs, &$skorManualTotal, &$skorOtomatisTotal) {
            foreach ($hasil->uji->soals as $soal) {
                $jawaban = UjiJawaban::where('uji_hasil_id', $hasil->id)
                    ->where('uji_soal_id', $soal->id)
                    ->first();

                $isManualGraded = $this->isSoalManualGraded($soal);
                $hasGuruOverrideInput = array_key_exists($soal->id, $skorInputs) && $skorInputs[$soal->id] !== null && $skorInputs[$soal->id] !== '';

                if ($isManualGraded || ($soal->tipe === 'esai' && $hasGuruOverrideInput)) {
                    $skorDidapat = $hasGuruOverrideInput
                        ? (float) $skorInputs[$soal->id]
                        : ($jawaban ? (float) $jawaban->skor_didapat : 0.0);

                    if ($skorDidapat < 0) {
                        $skorDidapat = 0.0;
                    }
                    if ($skorDidapat > (float) $soal->bobot_nilai) {
                        $skorDidapat = (float) $soal->bobot_nilai;
                    }

                    $skorDidapat = round($skorDidapat, 2);

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

                    if ($isManualGraded) {
                        $skorManualTotal += $skorDidapat;
                    } else {
                        $skorOtomatisTotal += $skorDidapat;
                    }
                } else {
                    if ($jawaban) {
                        $skorOtomatisTotal += (float) $jawaban->skor_didapat;
                    }
                }
            }

            $skorOtomatisTotal = round($skorOtomatisTotal, 2);
            $totalSkorAkhir = round($skorOtomatisTotal + $skorManualTotal, 2);

            $hasil->update([
                'skor_otomatis'     => $skorOtomatisTotal,
                'total_skor'        => $totalSkorAkhir,
                'status_pengerjaan' => 'final',
            ]);
        });

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

            if (!$this->isSoalManualGraded($soal)) {
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
}