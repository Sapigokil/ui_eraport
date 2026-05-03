<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RiwayatKenaikanKelas;
use App\Models\PengumumanSiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MutasiKelulusanController extends Controller
{
    public function index(Request $request)
    {
        $id_kelas_asal = $request->id_kelas_asal;
        $taLama = $request->tahun_ajaran_lama ?? (date('n') >= 7 ? date('Y').'/'.(date('Y')+1) : (date('Y')-1).'/'.date('Y'));
        
        $kelasAsalTerpilih = Kelas::find($id_kelas_asal);
        if (!$kelasAsalTerpilih) {
            return redirect()->route('mutasi.kelulusan_dashboard.index')->with('error', 'Pilih kelas terlebih dahulu.');
        }

        // 1. Ambil Siswa Aktif
        $dataSiswa = Siswa::where('id_kelas', $id_kelas_asal)
            ->where('status', 'aktif')
            ->orderBy('nama_siswa', 'asc')
            ->get();

        // 2. Tarik data riwayat (Disesuaikan agar mengambil object utuh untuk file_skl)
        $riwayatExisting = DB::table('riwayat_kenaikan_kelas')
            ->where('id_kelas_lama', $id_kelas_asal)
            ->where('tahun_ajaran_lama', $taLama)
            ->get()
            ->keyBy('id_siswa');

        // 3. Gabungkan status & file_skl ke data siswa & Hitung Statistik untuk Banner
        $stat = ['lulus' => 0, 'tidak_lulus' => 0, 'belum' => 0]; 
        foreach ($dataSiswa as $siswa) {
            $riwayat = $riwayatExisting[$siswa->id_siswa] ?? null;
            $status = $riwayat ? $riwayat->status : ''; 
            $file_skl = $riwayat ? $riwayat->file_skl : null;

            $siswa->status_kelulusan = $status;
            $siswa->file_skl = $file_skl;
            
            if ($status == 'lulus') $stat['lulus']++;
            elseif ($status == 'tidak_lulus') $stat['tidak_lulus']++; 
            else $stat['belum']++;
        }

        // 4. Replikasi Logika Warna Dashboard agar Sinkron
        $words = explode(' ', trim($kelasAsalTerpilih->nama_kelas));
        $jurusan = $words[1] ?? 'UMUM';
        $hue = crc32($jurusan) % 360;
        $bg_gradient = "linear-gradient(135deg, hsl({$hue}, 75%, 55%), hsl(".($hue + 25).", 75%, 40%))";

        return view('mutasi.kelulusan_index', compact(
            'kelasAsalTerpilih', 'dataSiswa', 'id_kelas_asal', 'taLama', 'bg_gradient', 'stat'
        ));
    }

    public function store(Request $request)
    {
        $id_kelas_lama = $request->id_kelas_lama;
        $taLama = $request->tahun_ajaran_lama;
        $dataTujuan = $request->tujuan; // Array [id_siswa => status]
        $adminName = Auth::user()->name ?? 'Admin Sistem';

        DB::beginTransaction();
        try {
            foreach ($dataTujuan as $id_siswa => $keputusan) {
                
                // Cek Riwayat Lama
                $riwayatOld = DB::table('riwayat_kenaikan_kelas')
                    ->where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran_lama', $taLama)
                    ->first();

                // Jika dropdown dikosongkan (Batal Diproses)
                if (empty($keputusan)) {
                    // Jangan hapus Draf & File SKL jika sudah ada file yang diunggah
                    if ($riwayatOld && $riwayatOld->file_skl) {
                        DB::table('riwayat_kenaikan_kelas')
                            ->where('id_siswa', $id_siswa)
                            ->where('tahun_ajaran_lama', $taLama)
                            ->update(['status' => '', 'updated_at' => now()]);
                            
                        // Hanya hapus tabel pengumuman
                        DB::table('pengumuman_siswa')
                            ->where('id_siswa', $id_siswa)
                            ->where('tahun_ajaran', $taLama)
                            ->where('jenis', 'kelulusan')
                            ->delete();
                    } else {
                        // Jika tidak ada file SKL, aman untuk menghapus draf sepenuhnya
                        DB::table('riwayat_kenaikan_kelas')->where('id_siswa', $id_siswa)->where('tahun_ajaran_lama', $taLama)->delete();
                        DB::table('pengumuman_siswa')->where('id_siswa', $id_siswa)->where('tahun_ajaran', $taLama)->where('jenis', 'kelulusan')->delete();
                    }
                    continue;
                }

                $isLulus = ($keputusan == 'lulus');

                if ($riwayatOld) {
                    DB::table('riwayat_kenaikan_kelas')
                        ->where('id_siswa', $id_siswa)
                        ->where('tahun_ajaran_lama', $taLama)
                        ->update([
                            'id_kelas_baru' => $isLulus ? 0 : $id_kelas_lama,
                            'status' => $keputusan, 
                            'user_admin' => $adminName,
                            'status_eksekusi' => 'draft',
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table('riwayat_kenaikan_kelas')->insert([
                        'id_siswa' => $id_siswa,
                        'tahun_ajaran_lama' => $taLama,
                        'id_kelas_lama' => $id_kelas_lama,
                        'id_kelas_baru' => $isLulus ? 0 : $id_kelas_lama,
                        'tahun_ajaran_baru' => 'LULUS',
                        'status' => $keputusan,
                        'user_admin' => $adminName,
                        'status_eksekusi' => 'draft',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $pengumumanAda = DB::table('pengumuman_siswa')
                    ->where('id_siswa', $id_siswa)
                    ->where('tahun_ajaran', $taLama)
                    ->where('jenis', 'kelulusan')
                    ->exists();

                if ($pengumumanAda) {
                    DB::table('pengumuman_siswa')
                        ->where('id_siswa', $id_siswa)
                        ->where('tahun_ajaran', $taLama)
                        ->where('jenis', 'kelulusan')
                        ->update([
                            'status_hasil' => $keputusan,
                            'user_input' => $adminName,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table('pengumuman_siswa')->insert([
                        'id_siswa' => $id_siswa,
                        'tahun_ajaran' => $taLama,
                        'jenis' => 'kelulusan',
                        'status_hasil' => $keputusan,
                        'status' => 'hold',
                        'has_seen' => 0,
                        'user_input' => $adminName,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('mutasi.kelulusan_dashboard.index')
                ->with('success', "Data revisi kelulusan berhasil disimpan.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update database: ' . $e->getMessage());
        }
    }

    /**
     * FUNGSI Upload SKL via AJAX per baris
     */
    public function uploadSklAjax(Request $request, $id_siswa)
    {
        $request->validate([
            'file_skl' => 'required|mimes:pdf|max:2048',
            'tahun_ajaran_lama' => 'required'
        ]);

        $taLama = $request->tahun_ajaran_lama;
        $siswa = DB::table('siswa')->where('id_siswa', $id_siswa)->first();
        
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.']);
        }

        $file = $request->file('file_skl');
        $nisn = $siswa->nisn ?? 'NONISN';
        $newFilename = 'SKL_' . $id_siswa . '_' . $nisn . '.' . $file->getClientOriginalExtension();
        
        // Simpan langsung ke Private Storage
        $file->storeAs('skl', $newFilename, 'local');

        $riwayatAda = DB::table('riwayat_kenaikan_kelas')
            ->where('id_siswa', $id_siswa)
            ->where('tahun_ajaran_lama', $taLama)
            ->exists();

        // Jika riwayat/draft belum ada, buat draft kosong agar PDF memiliki rumah
        if ($riwayatAda) {
            DB::table('riwayat_kenaikan_kelas')
                ->where('id_siswa', $id_siswa)
                ->where('tahun_ajaran_lama', $taLama)
                ->update([
                    'file_skl' => $newFilename, 
                    'updated_at' => now()
                ]);
        } else {
            DB::table('riwayat_kenaikan_kelas')->insert([
                'id_siswa' => $id_siswa,
                'tahun_ajaran_lama' => $taLama,
                'id_kelas_lama' => $siswa->id_kelas,
                'id_kelas_baru' => null,
                'tahun_ajaran_baru' => 'LULUS',
                'status' => '', // Dibiarkan kosong karena Admin belum memilih kelulusan
                'user_admin' => Auth::user()->name ?? 'Admin Sistem',
                'status_eksekusi' => 'draft',
                'file_skl' => $newFilename,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Melihat SKL di Tab Baru (Khusus Admin/User yg berhak)
     */
    public function viewSkl($id_siswa)
    {
        $riwayat = DB::table('riwayat_kenaikan_kelas')
            ->where('id_siswa', $id_siswa)
            ->whereNotNull('file_skl')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$riwayat || !$riwayat->file_skl) {
            return abort(404, 'Data SKL tidak ditemukan di database.');
        }

        $path = storage_path('app/skl/' . $riwayat->file_skl);

        if (!file_exists($path)) {
            return abort(404, 'File PDF fisik tidak ditemukan di server.');
        }

        // Return file PDF untuk di render langsung di browser
        return response()->file($path);
    }

    /**
     * 👇 FUNGSI BARU: Hapus SKL via AJAX per baris 👇
     */
    public function deleteSklAjax(Request $request, $id_siswa)
    {
        $taLama = $request->tahun_ajaran_lama;

        $riwayat = DB::table('riwayat_kenaikan_kelas')
            ->where('id_siswa', $id_siswa)
            ->where('tahun_ajaran_lama', $taLama)
            ->whereNotNull('file_skl')
            ->first();

        if ($riwayat) {
            // Hapus file fisik di storage
            if (Storage::disk('local')->exists('skl/' . $riwayat->file_skl)) {
                Storage::disk('local')->delete('skl/' . $riwayat->file_skl);
            }
            
            // Kosongkan nama file di database
            DB::table('riwayat_kenaikan_kelas')
                ->where('id_siswa', $id_siswa)
                ->where('tahun_ajaran_lama', $taLama)
                ->update([
                    'file_skl' => null, 
                    'updated_at' => now()
                ]);
                
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Data SKL tidak ditemukan untuk dihapus.']);
    }
}