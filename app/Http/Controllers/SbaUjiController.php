<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Uji;
use App\Models\UjiHasil;
use App\Models\UjiJawaban;
use Carbon\Carbon;

class SbaUjiController extends Controller
{
    // ======================================================================
    // 1. PORTAL & VALIDASI MASUK
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

        // Cek Validasi Waktu Mulai & Selesai
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
            $asesi = User::where('nisn', $request->nisn)->first();
            if (!$asesi) {
                return back()->with('error', 'NISN tidak terdaftar dalam sistem!');
            }
            $asesiId = $asesi->id;
            $namaPublik = $asesi->name;
            $mode = 'siswa';
        }

        // Cek Riwayat Percobaan & Pelanggaran
        $riwayat = UjiHasil::where('uji_id', $uji->id)
            ->where(function($q) use ($asesiId, $namaPublik) {
                if ($asesiId) {
                    $q->where('asesi_id', $asesiId);
                } else {
                    $q->where('nama_peserta_publik', $namaPublik);
                }
            })->get();

        // Apakah ada sesi yang masih 'berlangsung'?
        $sesiAktif = $riwayat->where('status_pengerjaan', 'berlangsung')->first();
        
        if (!$sesiAktif) {
            $jumlahPercobaan = $riwayat->count();
            
            if ($jumlahPercobaan >= $uji->maksimal_percobaan) {
                return back()->with('error', 'Anda telah mencapai batas maksimal percobaan ujian ini ('. $uji->maksimal_percobaan .' kali).');
            }

            $pernahGagal = $riwayat->where('status_pengerjaan', 'gagal_pelanggaran')->first();
            if ($pernahGagal && !$uji->ulangi_jika_gagal) {
                return back()->with('error', 'Anda pernah didiskualifikasi karena pelanggaran dan tidak diizinkan mengulang ujian ini.');
            }
        }

        // Set Sesi Terisolasi
        session([
            'sba_mode' => $mode,
            'sba_peserta_id' => $asesiId,
            'sba_peserta_nama' => $namaPublik,
            'sba_kode_uji' => $kodeUji
        ]);

        // LANGSUNG DIALIHKAN KE HALAMAN UJIAN (1 VIEW)
        return redirect()->route('sba.uji.halaman', ['kode_uji' => $kodeUji]);
    }

    // ======================================================================
    // 2. PELAKSANAAN UJIAN (SINGLE PAGE ENGINE)
    // ======================================================================

    public function halamanUji($kode_uji)
    {
        $kodeUji = strtoupper(trim($kode_uji));
        $uji = Uji::with('soals')->where('kode_uji', $kodeUji)->firstOrFail();

        // Cek apakah peserta belum masuk portal ATAU kode ujian di sesi berbeda dengan URL yang diakses
        if ((!session()->has('sba_peserta_id') && !session()->has('sba_peserta_nama')) || session('sba_kode_uji') !== $kodeUji) {
            return redirect()->route('sba.portal', ['kode' => $kodeUji])
                ->with('warning', 'Silakan masukkan data diri untuk memulai ujian.');
        }

        $asesiId = session('sba_peserta_id');
        $namaPeserta = session('sba_peserta_nama');

        // Cari atau Buat Sesi Pengerjaan Database (UjiHasil)
        $hasil = UjiHasil::where('uji_id', $uji->id)
            ->where('status_pengerjaan', 'berlangsung')
            ->where(function($q) use ($asesiId, $namaPeserta) {
                if ($asesiId) $q->where('asesi_id', $asesiId);
                else $q->where('nama_peserta_publik', $namaPeserta);
            })->first();

        if (!$hasil) {
            $percobaanSebelumnya = UjiHasil::where('uji_id', $uji->id)
                ->where(function($q) use ($asesiId, $namaPeserta) {
                    if ($asesiId) $q->where('asesi_id', $asesiId);
                    else $q->where('nama_peserta_publik', $namaPeserta);
                })->count();

            $hasil = UjiHasil::create([
                'uji_id' => $uji->id,
                'asesi_id' => $asesiId,
                'nama_peserta_publik' => $asesiId ? null : $namaPeserta,
                'percobaan_ke' => $percobaanSebelumnya + 1,
                'waktu_mulai' => now(),
                'status_pengerjaan' => 'berlangsung',
                'jumlah_pelanggaran' => 0
            ]);
        }

        // Simpan ID Hasil ke sesi
        session(['sba_hasil_id' => $hasil->id]);

        // Acak Soal jika diaktifkan
        $soals = $uji->acak_soal ? $uji->soals->shuffle() : $uji->soals;

        return view('sba-uji.uji', compact('uji', 'namaPeserta', 'hasil', 'soals'));
    }

    // ======================================================================
    // 3. AJAX: SIMPAN JAWABAN PER BUTIR SOAL
    // ======================================================================
    public function simpanJawaban(Request $request, $kode_uji)
    {
        $hasilId = session('sba_hasil_id');
        if (!$hasilId) {
            return response()->json(['status' => 'error', 'message' => 'Sesi tidak ditemukan.'], 401);
        }

        $request->validate([
            'uji_soal_id' => 'required|exists:uji_soals,id',
            'jawaban' => 'nullable',
            'ragu_ragu' => 'nullable|boolean',
        ]);

        $jawabanInput = $request->jawaban;
        if (is_array($jawabanInput)) {
            $jawabanInput = json_encode($jawabanInput);
        }

        UjiJawaban::updateOrCreate(
            [
                'uji_hasil_id' => $hasilId,
                'uji_soal_id' => $request->uji_soal_id,
            ],
            [
                'jawaban' => $jawabanInput,
                'ragu_ragu' => $request->has('ragu_ragu') ? $request->ragu_ragu : false,
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Jawaban tersimpan']);
    }

    // ======================================================================
    // 4. AJAX: CATAT PELANGGARAN
    // ======================================================================
    public function catatPelanggaran(Request $request, $kode_uji)
    {
        $hasilId = session('sba_hasil_id');
        $uji = Uji::where('kode_uji', strtoupper($kode_uji))->firstOrFail();

        // Jika mode Open Book aktif, abaikan seluruh pencatatan pelanggaran
        if ($uji->open_book) {
            return response()->json([
                'status' => 'ignored',
                'message' => 'Mode Open Book aktif, perpindahan tab diizinkan.'
            ]);
        }

        $hasil = UjiHasil::find($hasilId);

        if (!$hasil || $hasil->status_pengerjaan !== 'berlangsung') {
            return response()->json(['status' => 'error']);
        }

        $hasil->increment('jumlah_pelanggaran');
        $pelanggaranSekarang = $hasil->jumlah_pelanggaran;

        $catatan = $hasil->catatan_pelanggaran . " | Pindah tab/keluar fullscreen ke-" . $pelanggaranSekarang . " pada " . now();
        $hasil->update(['catatan_pelanggaran' => $catatan]);

        if ($pelanggaranSekarang >= $uji->toleransi_pelanggaran) {
            $hasil->update([
                'status_pengerjaan' => 'gagal_pelanggaran',
                'waktu_selesai' => now()
            ]);
            
            session()->forget(['sba_hasil_id', 'sba_kode_uji', 'sba_mode', 'sba_peserta_id', 'sba_peserta_nama']);

            return response()->json([
                'status' => 'gagal',
                'message' => 'Anda telah melewati batas toleransi pelanggaran. Ujian dinyatakan GAGAL.'
            ]);
        }

        return response()->json([
            'status' => 'warning',
            'sisa_toleransi' => $uji->toleransi_pelanggaran - $pelanggaranSekarang,
            'message' => 'Peringatan! Anda terdeteksi keluar dari area ujian.'
        ]);
    }

    // ======================================================================
    // 5. FINALISASI & PENILAIAN OTOMATIS
    // ======================================================================
    public function selesaiUjian(Request $request, $kode_uji)
    {
        $hasilId = session('sba_hasil_id');
        $uji = Uji::with('soals')->where('kode_uji', strtoupper($kode_uji))->firstOrFail();
        $hasil = UjiHasil::with('jawabans')->where('id', $hasilId)->firstOrFail();

        if ($hasil->status_pengerjaan !== 'berlangsung') {
            return redirect()->route('sba.portal')->with('error', 'Ujian sudah diselesaikan sebelumnya.');
        }

        $totalSkorOtomatis = 0;
        $mapSoal = $uji->soals->keyBy('id');

        foreach ($hasil->jawabans as $jawabanSiswa) {
            $soal = $mapSoal->get($jawabanSiswa->uji_soal_id);
            if (!$soal) continue;

            if (in_array($soal->tipe, ['pg', 'pg_kompleks'])) {
                $kunciArray = $soal->kunci_jawaban ?? [];
                $jawabanSiswaRaw = $jawabanSiswa->jawaban;

                if ($soal->tipe === 'pg_kompleks') {
                    $jawabanSiswaArray = json_decode($jawabanSiswaRaw, true) ?? [];
                    sort($kunciArray);
                    sort($jawabanSiswaArray);
                    
                    if ($kunciArray == $jawabanSiswaArray) {
                        $skorItem = $soal->bobot_nilai;
                        $totalSkorOtomatis += $skorItem;
                        $jawabanSiswa->update(['skor_didapat' => $skorItem]);
                    }
                } else {
                    if (in_array($jawabanSiswaRaw, $kunciArray)) {
                        $skorItem = $soal->bobot_nilai;
                        $totalSkorOtomatis += $skorItem;
                        $jawabanSiswa->update(['skor_didapat' => $skorItem]);
                    }
                }
            }
        }

        $hasil->update([
            'waktu_selesai' => now(),
            'skor_otomatis' => $totalSkorOtomatis,
            'total_skor' => $totalSkorOtomatis,
            'status_pengerjaan' => 'selesai'
        ]);

        session()->forget(['sba_hasil_id', 'sba_kode_uji', 'sba_mode', 'sba_peserta_id', 'sba_peserta_nama']);

        return redirect()->route('sba.uji.selesai.tampil', ['kode_uji' => $kode_uji, 'hasil_id' => $hasil->id]);
    }

    public function tampilkanHasil($kode_uji, $hasil_id)
    {
        $uji = Uji::where('kode_uji', strtoupper($kode_uji))->firstOrFail();
        $hasil = UjiHasil::where('id', $hasil_id)->firstOrFail();

        return view('sba-uji.selesai', compact('uji', 'hasil'));
    }
}