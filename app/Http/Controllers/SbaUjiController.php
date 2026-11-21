<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Uji;
use App\Models\UjiHasil;
use App\Models\UjiJawaban;
use Carbon\Carbon;

class SbaUjiController extends Controller
{
    // ======================================================================
    // 1. PORTAL & VALIDASI MASUK (Validasi kode_uji hanya di sini)
    // ======================================================================
    
    public function portal()
    {
        return view('sba-uji.portal');
    }

    public function processPortal(Request $request)
    {
        $request->validate([
            'kode_uji' => 'required|string',
        ]);

        $kodeUji = strtoupper(trim($request->kode_uji));
        $uji = Uji::where('kode_uji', $kodeUji)->whereIn('status', ['published', 'closed'])->first();

        if (!$uji) {
            return back()->with('error', 'Kode Asesmen tidak ditemukan atau belum diterbitkan!');
        }

        if ($uji->status === 'closed') {
            return back()->with('error', 'Asesmen ini sudah ditutup oleh admin.');
        }

        // Cek Validasi Waktu Mulai & Selesai (Patokan Utama)
        $now = now();
        if ($uji->waktu_mulai && $now->lt($uji->waktu_mulai)) {
            return back()->with('error', 'Ujian belum dimulai. Jadwal: ' . $uji->waktu_mulai->format('d/m/Y H:i'));
        }
        if ($uji->waktu_selesai && $now->gt($uji->waktu_selesai)) {
            return back()->with('error', 'Ujian telah berakhir pada: ' . $uji->waktu_selesai->format('d/m/Y H:i'));
        }

        // Inisialisasi variabel peserta
        $asesiId = null;
        $namaPublik = null;

        // --- JALUR PUBLIK ---
        if ($request->has('is_public') && $request->is_public == '1') {
            $request->validate(['nama_lengkap' => 'required|string|max:100']);
            if (!$uji->buka_untuk_umum) {
                return back()->with('error', 'Asesmen ini hanya untuk peserta terdaftar!');
            }
            $namaPublik = $request->nama_lengkap;
            $mode = 'publik';
        } 
        // --- JALUR SISWA / ASESI ---
        else {
            $request->validate(['nisn' => 'required|string']);
            $nisnInput = trim($request->nisn);

            // 1. Cari data siswa berdasarkan kolom nisn pada tabel siswa
            $siswa = Siswa::where('nisn', $nisnInput)->first();
            if (!$siswa) {
                return back()->with('error', 'NISN tidak terdaftar dalam sistem!');
            }

            // 2. Cek apakah siswa sudah terhubung ke akun tabel users (via id_siswa atau username)
            $asesi = User::where('id_siswa', $siswa->id_siswa)
                ->orWhere('username', $siswa->nisn)
                ->first();

            if ($asesi) {
                $asesiId = $asesi->id;
                $namaPublik = $asesi->name ?: $siswa->nama_siswa;
            } else {
                // Fallback jika siswa belum membuat akun di tabel users
                $asesiId = null;
                $namaPublik = $siswa->nama_siswa . ' (' . $siswa->nisn . ')';
            }

            $mode = 'siswa';
        }

        // Cek Riwayat Percobaan & Pelanggaran berdasarkan uji_id
        $riwayat = UjiHasil::where('uji_id', $uji->id)
            ->where(function($q) use ($asesiId, $namaPublik) {
                if ($asesiId) {
                    $q->where('asesi_id', $asesiId);
                } else {
                    $q->where('nama_peserta_publik', $namaPublik);
                }
            })->get();

        // Apakah ada sesi yang masih 'berlangsung'? (Termasuk sesi yang baru di-unlock admin)
        $sesiAktif = $riwayat->where('status_pengerjaan', 'berlangsung')->first();
        
        if (!$sesiAktif) {
            $jumlahPercobaan = $riwayat->count();
            
            if ($jumlahPercobaan >= $uji->maksimal_percobaan) {
                return back()->with('error', 'Anda telah mencapai batas maksimal percobaan ujian ini ('. $uji->maksimal_percobaan .' kali).');
            }

            $pernahGagal = $riwayat->where('status_pengerjaan', 'gagal_pelanggaran')->first();
            if ($pernahGagal && !$uji->ulangi_jika_gagal) {
                return back()->with('error', 'Anda pernah didiskualifikasi karena pelanggaran dan silahkan menghubungi pengawas uji.');
            }
        }

        // Set Sesi Terisolasi berbasis uji_id
        session()->forget('sba_kode_uji');
        session([
            'sba_mode'         => $mode,
            'sba_peserta_id'   => $asesiId,
            'sba_peserta_nama' => $namaPublik,
            'sba_uji_id'       => $uji->id,
        ]);

        // Dialihkan ke URL /sba/asesmen/{kode_uji}
        return redirect()->route('sba.uji.halaman', ['kode_uji' => $uji->kode_uji]);
    }

    // ======================================================================
    // 2. PELAKSANAAN UJIAN & QUICKLINK (/sba/asesmen/{kode_uji})
    // ======================================================================

    public function halamanUji($kode_uji)
    {
        $kodeUji = strtoupper(trim($kode_uji));
        $sessionUjiId = session('sba_uji_id');
        $hasPesertaSession = session()->has('sba_peserta_id') || session()->has('sba_peserta_nama');

        // Jika peserta sudah tervalidasi lewat portal, utamakan pencarian lewat sba_uji_id
        // agar tetap aman meski guru mengubah kode_uji di tengah sesi atau peserta me-refresh halaman
        if ($hasPesertaSession && $sessionUjiId) {
            $uji = Uji::with('soals')->find($sessionUjiId);

            // Jika ternyata kode pada URL cocok dengan ujian lain (misal peserta membuka quicklink ujian berbeda)
            $ujiDariUrl = Uji::where('kode_uji', $kodeUji)->first();
            if ($ujiDariUrl && (int) $ujiDariUrl->id !== (int) $sessionUjiId) {
                return redirect()->route('sba.portal', ['kode' => $ujiDariUrl->kode_uji])
                    ->with('warning', 'Silakan masukkan data diri untuk memulai ujian ini.');
            }

            if (!$uji) {
                session()->forget(['sba_hasil_id', 'sba_uji_id', 'sba_kode_uji', 'sba_mode', 'sba_peserta_id', 'sba_peserta_nama']);
                return redirect()->route('sba.portal')
                    ->with('error', 'Data asesmen tidak ditemukan.');
            }
        } else {
            // Akses langsung via Quicklink (misal: /sba/asesmen/COBA123) tanpa sesi portal
            $uji = Uji::where('kode_uji', $kodeUji)->first();

            if (!$uji) {
                return redirect()->route('sba.portal')
                    ->with('error', 'Kode Asesmen tidak ditemukan atau sudah tidak berlaku.');
            }

            return redirect()->route('sba.portal', ['kode' => $uji->kode_uji])
                ->with('warning', 'Silakan masukkan data diri untuk memulai ujian.');
        }

        $now = now();
        if ($uji->waktu_mulai && $now->lt($uji->waktu_mulai)) {
            return redirect()->route('sba.portal', ['kode' => $uji->kode_uji])
                ->with('error', 'Ujian belum dimulai. Jadwal: ' . $uji->waktu_mulai->format('d/m/Y H:i'));
        }

        $asesiId = session('sba_peserta_id');
        $namaPeserta = session('sba_peserta_nama');

        // Cari Sesi Pengerjaan Database (UjiHasil) berdasarkan uji_id
        $hasil = UjiHasil::where('uji_id', $uji->id)
            ->where('status_pengerjaan', 'berlangsung')
            ->where(function($q) use ($asesiId, $namaPeserta) {
                if ($asesiId) $q->where('asesi_id', $asesiId);
                else $q->where('nama_peserta_publik', $namaPeserta);
            })->first();

        // Jika jadwal selesai sudah terlewati atau status closed
        if ($uji->status === 'closed' || ($uji->waktu_selesai && $now->gte($uji->waktu_selesai))) {
            if ($hasil) {
                $totalSkorOtomatis = $this->hitungSkorOtomatis($uji, $hasil);
                $hasil->update([
                    'waktu_selesai'     => $now,
                    'skor_otomatis'     => $totalSkorOtomatis,
                    'total_skor'        => $totalSkorOtomatis,
                    'status_pengerjaan' => 'selesai',
                ]);
                session()->forget(['sba_hasil_id', 'sba_uji_id', 'sba_kode_uji', 'sba_mode', 'sba_peserta_id', 'sba_peserta_nama']);
                return redirect()->route('sba.uji.selesai.tampil', ['uji_id' => $uji->id, 'hasil_id' => $hasil->id]);
            }

            session()->forget(['sba_hasil_id', 'sba_uji_id', 'sba_kode_uji', 'sba_mode', 'sba_peserta_id', 'sba_peserta_nama']);
            return redirect()->route('sba.portal')->with('error', 'Jadwal ujian telah berakhir.');
        }

        if (!$hasil) {
            $percobaanSebelumnya = UjiHasil::where('uji_id', $uji->id)
                ->where(function($q) use ($asesiId, $namaPeserta) {
                    if ($asesiId) $q->where('asesi_id', $asesiId);
                    else $q->where('nama_peserta_publik', $namaPeserta);
                })->count();

            $hasil = UjiHasil::create([
                'uji_id'              => $uji->id,
                'asesi_id'            => $asesiId,
                'nama_peserta_publik' => $asesiId ? null : $namaPeserta,
                'percobaan_ke'        => $percobaanSebelumnya + 1,
                'waktu_mulai'         => $now,
                'status_pengerjaan'   => 'berlangsung',
                'jumlah_pelanggaran'  => 0,
            ]);
        }

        // Hitung batas waktu efektif (yang paling cepat tercapai antara durasi ujian vs jadwal waktu_selesai)
        $batasDurasi = $uji->durasi_menit > 0
            ? $hasil->waktu_mulai->copy()->addMinutes($uji->durasi_menit)
            : null;
        $batasJadwal = $uji->waktu_selesai
            ? $uji->waktu_selesai->copy()
            : null;

        $batasAkhir = null;
        if ($batasDurasi && $batasJadwal) {
            $batasAkhir = $batasDurasi->lt($batasJadwal) ? $batasDurasi : $batasJadwal;
        } else {
            $batasAkhir = $batasDurasi ?? $batasJadwal;
        }

        $sisaDetikAwal = null;
        if ($batasAkhir) {
            $sisaDetikAwal = (int) $now->diffInSeconds($batasAkhir, false);
            if ($sisaDetikAwal <= 0) {
                $totalSkorOtomatis = $this->hitungSkorOtomatis($uji, $hasil);
                $hasil->update([
                    'waktu_selesai'     => $now,
                    'skor_otomatis'     => $totalSkorOtomatis,
                    'total_skor'        => $totalSkorOtomatis,
                    'status_pengerjaan' => 'selesai',
                ]);
                session()->forget(['sba_hasil_id', 'sba_uji_id', 'sba_kode_uji', 'sba_mode', 'sba_peserta_id', 'sba_peserta_nama']);
                return redirect()->route('sba.uji.selesai.tampil', ['uji_id' => $uji->id, 'hasil_id' => $hasil->id]);
            }
        }

        // Simpan ID Hasil ke sesi
        session(['sba_hasil_id' => $hasil->id]);

        // Ambil jawaban yang sudah tersimpan sebelumnya (untuk sesi lanjutan / refresh)
        $jawabanTersimpan = UjiJawaban::where('uji_hasil_id', $hasil->id)
            ->get()
            ->keyBy('uji_soal_id');

        // Acak Soal jika diaktifkan
        $soals = $uji->acak_soal ? $uji->soals->shuffle() : $uji->soals;

        return view('sba-uji.uji', compact('uji', 'namaPeserta', 'hasil', 'soals', 'jawabanTersimpan', 'sisaDetikAwal', 'batasAkhir'));
    }

    // ======================================================================
    // 3. AJAX: SIMPAN JAWABAN PER BUTIR SOAL
    // ======================================================================
    public function simpanJawaban(Request $request, $uji_id)
    {
        $hasilId = session('sba_hasil_id');
        if (!$hasilId) {
            return response()->json(['status' => 'error', 'message' => 'Sesi tidak ditemukan.'], 401);
        }

        $uji = Uji::findOrFail($uji_id);
        $hasil = UjiHasil::where('id', $hasilId)->where('uji_id', $uji->id)->first();

        if (!$hasil || $hasil->status_pengerjaan !== 'berlangsung') {
            return response()->json(['status' => 'error', 'message' => 'Sesi ujian sudah berakhir.'], 403);
        }

        // Jika jadwal selesai sudah terlewati, beri sinyal expired agar frontend otomatis finalisasi
        if ($uji->waktu_selesai && now()->gt($uji->waktu_selesai)) {
            return response()->json(['status' => 'expired', 'message' => 'Jadwal ujian telah berakhir.'], 403);
        }

        $request->validate([
            'uji_soal_id' => 'required|exists:uji_soals,id',
            'jawaban'     => 'nullable',
            'ragu_ragu'   => 'nullable|boolean',
        ]);

        $jawabanInput = $request->jawaban;
        if (is_array($jawabanInput)) {
            $jawabanInput = json_encode($jawabanInput);
        }

        UjiJawaban::updateOrCreate(
            [
                'uji_hasil_id' => $hasilId,
                'uji_soal_id'  => $request->uji_soal_id,
            ],
            [
                'jawaban'   => $jawabanInput,
                'ragu_ragu' => $request->has('ragu_ragu') ? $request->ragu_ragu : false,
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Jawaban tersimpan']);
    }

    /**
     * Helper Private: Hitung Skor Otomatis PG, PG Kompleks, & Pernyataan Benar Salah
     */
    private function hitungSkorOtomatis(Uji $uji, UjiHasil $hasil)
    {
        $hasil->loadMissing('jawabans');
        $totalSkorOtomatis = 0;
        $mapSoal = $uji->soals->keyBy('id');

        foreach ($hasil->jawabans as $jawabanSiswa) {
            $soal = $mapSoal->get($jawabanSiswa->uji_soal_id);
            if (!$soal) continue;

            if ($soal->tipe === 'benar_salah') {
                $pernyataanList = is_array($soal->opsi_jawaban) ? $soal->opsi_jawaban : [];
                $kunciArray = is_array($soal->kunci_jawaban) ? $soal->kunci_jawaban : [];
                $jawabanSiswaArray = is_array($jawabanSiswa->jawaban)
                    ? $jawabanSiswa->jawaban
                    : (json_decode((string) $jawabanSiswa->jawaban, true) ?? []);

                $skorItem = 0;
                foreach ($pernyataanList as $idx => $item) {
                    $kunciItem = strtolower(trim((string) ($kunciArray[$idx] ?? '')));
                    $jawabItem = strtolower(trim((string) ($jawabanSiswaArray[$idx] ?? '')));
                    $bobotItem = (float) ($item['bobot'] ?? 0);

                    if ($kunciItem !== '' && $jawabItem === $kunciItem) {
                        $skorItem += $bobotItem;
                    }
                }

                $totalSkorOtomatis += $skorItem;
                $jawabanSiswa->update(['skor_didapat' => $skorItem]);
            } elseif (in_array($soal->tipe, ['pg', 'pg_kompleks'])) {
                $kunciArray = $soal->kunci_jawaban ?? [];
                $jawabanSiswaRaw = $jawabanSiswa->jawaban;
                $skorItem = 0;

                if ($soal->tipe === 'pg_kompleks') {
                    $jawabanSiswaArray = json_decode($jawabanSiswaRaw, true) ?? [];
                    sort($kunciArray);
                    sort($jawabanSiswaArray);

                    if ($kunciArray == $jawabanSiswaArray && !empty($kunciArray)) {
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

        return $totalSkorOtomatis;
    }

    // ======================================================================
    // 4. AJAX: CATAT PELANGGARAN (Tetap Hitung Skor yang Sudah Dikerjakan)
    // ======================================================================
    public function catatPelanggaran(Request $request, $uji_id)
    {
        $hasilId = session('sba_hasil_id');
        $uji = Uji::with('soals')->findOrFail($uji_id);

        // Jika mode Open Book aktif, abaikan seluruh pencatatan pelanggaran
        if ($uji->open_book) {
            return response()->json([
                'status'  => 'ignored',
                'message' => 'Mode Open Book aktif, perpindahan tab diizinkan.'
            ]);
        }

        $hasil = UjiHasil::where('id', $hasilId)->where('uji_id', $uji->id)->first();

        if (!$hasil || $hasil->status_pengerjaan !== 'berlangsung') {
            return response()->json(['status' => 'error']);
        }

        $hasil->increment('jumlah_pelanggaran');
        $pelanggaranSekarang = $hasil->jumlah_pelanggaran;

        $tipeMap = [
            'pindah_tab'                => 'Pindah tab / membuka aplikasi lain',
            'loss_focus'                => 'Kehilangan fokus jendela ujian',
            'keluar_fullscreen'         => 'Keluar dari mode layar penuh',
            'ganti_soal_non_fullscreen' => 'Berpindah soal di luar mode layar penuh',
        ];
        $tipeInput = $request->input('tipe', '');
        $keteranganTipe = $tipeMap[$tipeInput] ?? 'Pindah tab/keluar area ujian';

        $catatan = $hasil->catatan_pelanggaran . " | " . $keteranganTipe . " ke-" . $pelanggaranSekarang . " pada " . now();
        $hasil->update(['catatan_pelanggaran' => $catatan]);

        if ($pelanggaranSekarang >= $uji->toleransi_pelanggaran) {
            // Tetap hitung skor dari jawaban yang sudah masuk sebelum terkunci
            $skorTerkumpul = $this->hitungSkorOtomatis($uji, $hasil);

            $hasil->update([
                'status_pengerjaan' => 'gagal_pelanggaran',
                'waktu_selesai'     => now(),
                'skor_otomatis'     => $skorTerkumpul,
                'total_skor'        => $skorTerkumpul,
            ]);
            
            session()->forget(['sba_hasil_id', 'sba_uji_id', 'sba_kode_uji', 'sba_mode', 'sba_peserta_id', 'sba_peserta_nama']);

            return response()->json([
                'status'  => 'gagal',
                'message' => 'Anda telah melewati batas toleransi pelanggaran. Ujian dihentikan otomatis.'
            ]);
        }

        return response()->json([
            'status'         => 'warning',
            'sisa_toleransi' => $uji->toleransi_pelanggaran - $pelanggaranSekarang,
            'message'        => 'Peringatan! Anda terdeteksi keluar dari area ujian.'
        ]);
    }

    // ======================================================================
    // 5. FINALISASI & PENILAIAN OTOMATIS
    // ======================================================================
    public function selesaiUjian(Request $request, $uji_id)
    {
        $uji = Uji::with('soals')->findOrFail($uji_id);
        $hasilId = session('sba_hasil_id') ?? $request->input('hasil_id');

        $hasil = UjiHasil::with('jawabans')
            ->where('id', $hasilId)
            ->where('uji_id', $uji->id)
            ->first();

        if (!$hasil) {
            return redirect()->route('sba.portal')->with('error', 'Data sesi ujian tidak ditemukan.');
        }

        // Jika sudah selesai (akibat double-tap / auto-submit bersamaan), langsung tampilkan hasil
        if (in_array($hasil->status_pengerjaan, ['selesai', 'final'])) {
            session()->forget(['sba_hasil_id', 'sba_uji_id', 'sba_kode_uji', 'sba_mode', 'sba_peserta_id', 'sba_peserta_nama']);
            return redirect()->route('sba.uji.selesai.tampil', ['uji_id' => $uji->id, 'hasil_id' => $hasil->id]);
        }

        if ($hasil->status_pengerjaan !== 'berlangsung') {
            return redirect()->route('sba.portal')->with('error', 'Sesi ujian tidak valid atau dihentikan.');
        }

        $totalSkorOtomatis = $this->hitungSkorOtomatis($uji, $hasil);

        $hasil->update([
            'waktu_selesai'     => now(),
            'skor_otomatis'     => $totalSkorOtomatis,
            'total_skor'        => $totalSkorOtomatis,
            'status_pengerjaan' => 'selesai'
        ]);

        session()->forget(['sba_hasil_id', 'sba_uji_id', 'sba_kode_uji', 'sba_mode', 'sba_peserta_id', 'sba_peserta_nama']);

        return redirect()->route('sba.uji.selesai.tampil', ['uji_id' => $uji->id, 'hasil_id' => $hasil->id]);
    }

    public function tampilkanHasil($uji_id, $hasil_id)
    {
        $uji = Uji::with('soals')->findOrFail($uji_id);
        $hasil = UjiHasil::where('id', $hasil_id)->where('uji_id', $uji->id)->firstOrFail();

        return view('sba-uji.selesai', compact('uji', 'hasil'));
    }
}