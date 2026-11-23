<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanBiodata;
use App\Models\Siswa;
use App\Models\DetailSiswa;

class AdminValidasiBioController extends Controller
{
    // Menampilkan Tabel Antrean (To-Do List)
    public function index(Request $request)
    {
        // 1. Ambil Parameter dari Request
        $search = $request->input('search');
        $statusFilter = $request->input('status', 'pending'); // Default menampilkan yang pending
        $perPage = $request->input('per_page', 10); // Default 10 baris

        // 2. Siapkan Query Dasar
        $query = PengajuanBiodata::with(['siswa.kelas']);

        // 3. Filter berdasarkan Status (Pending, Disetujui, Ditolak, atau Semua)
        if ($statusFilter !== 'semua') {
            $query->where('status', $statusFilter);
        }

        // 4. Filter Pencarian Text (Nama Siswa atau NISN)
        if (!empty($search)) {
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // 5. Eksekusi Query dengan Pagination Dinamis
        if ($perPage == 'all' || $perPage == -1) {
            $pengajuans = $query->orderBy('created_at', 'desc')->paginate(100000);
        } else {
            $pengajuans = $query->orderBy('created_at', 'desc')->paginate($perPage);
        }

        // Append parameter URL agar ketika pindah halaman (page=2), filter tidak hilang
        $pengajuans->appends($request->all());

        return view('siswa.validasi_bio_index', compact('pengajuans', 'statusFilter', 'perPage', 'search'));
    }

    public function show($id)
    {
        // Tambahkan relasi detail dan ekskul agar halaman full detail bisa dirender
        $pengajuan = PengajuanBiodata::with(['siswa.kelas', 'siswa.detail', 'siswa.ekskul'])->findOrFail($id);

        if ($pengajuan->status !== 'pending') {
            return redirect()->route('master.validasi_bio.index')->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $data_perubahan = $pengajuan->data_perubahan;

        return view('siswa.validasi_bio', compact('pengajuan', 'data_perubahan'));
    }

    // ==============================================================================
    // EKSEKUSI PENYIMPANAN DATA (TERIMA / TOLAK)
    // ==============================================================================
    public function proses(Request $request, $id)
    {
        // 1. Cari data pengajuan
        $pengajuan = PengajuanBiodata::findOrFail($id);

        if ($pengajuan->status !== 'pending') {
            return redirect()->route('master.validasi_bio.index')->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        // 2. Ambil array keputusan dari Radio Button form
        $keputusan = $request->input('keputusan', []);
        $data_perubahan = $pengajuan->data_perubahan;

        $keranjangUpdate = [];
        $jumlahDiterima = 0;
        $jumlahDitolak = 0;

        // 3. Looping data JSON dan bandingkan dengan keputusan Admin
        foreach ($data_perubahan as $kolom => $nilai) {
            $status_keputusan = $keputusan[$kolom] ?? 'tolak';

            if ($status_keputusan === 'terima') {
                $rawBaru = is_string($nilai['baru'] ?? null)
                    ? trim($nilai['baru'])
                    : ($nilai['baru'] ?? null);

                // Jika data barunya adalah '-' atau kosong, kembalikan menjadi null
                $nilai_final = ($rawBaru === '-' || $rawBaru === '' || $rawBaru === null)
                    ? null
                    : $rawBaru;

                $keranjangUpdate[$kolom] = $nilai_final;
                $jumlahDiterima++;
            } else {
                $jumlahDitolak++;
            }
        }

        // 4. Validasi batas panjang karakter & tipe kolom pada database aktif sebelum UPDATE
        if (count($keranjangUpdate) > 0) {
            $detailModel = new DetailSiswa();
            $connectionName = $detailModel->getConnectionName();
            $tableName = $detailModel->getTable();

            $columnsInfo = DB::connection($connectionName)->select("SHOW COLUMNS FROM `{$tableName}`");
            $columnLimits = [];
            $columnTypes = [];

            foreach ($columnsInfo as $colInfo) {
                $field = $colInfo->Field ?? '';
                $type = strtolower((string) ($colInfo->Type ?? ''));
                if ($field === '') {
                    continue;
                }

                $columnTypes[$field] = $type;

                if (preg_match('/^(?:var)?char\((\d+)\)/', $type, $matches)) {
                    $columnLimits[$field] = (int) $matches[1];
                } elseif (str_starts_with($type, 'year')) {
                    $columnLimits[$field] = 4;
                }
            }

            $daftarErrorKolom = [];

            foreach ($keranjangUpdate as $kolom => $val) {
                if ($val === null) {
                    continue;
                }

                $strVal = (string) $val;
                $labelKolom = ucwords(str_replace('_', ' ', $kolom));

                // Cek jika kolom bertipe YEAR atau tahun_lahir_* harus 4 digit angka
                if ((isset($columnTypes[$kolom]) && str_starts_with($columnTypes[$kolom], 'year')) || str_starts_with($kolom, 'tahun_lahir')) {
                    if (!preg_match('/^\d{4}$/', $strVal)) {
                        $daftarErrorKolom[] = "Kolom \"{$labelKolom}\" ({$kolom}) berisi \"{$strVal}\" — harus berupa 4 digit angka tahun (contoh: 1978). Silakan pilih TOLAK pada kolom ini.";
                        continue;
                    }
                }

                // Cek jika panjang karakter melebihi batas VARCHAR / CHAR database
                if (isset($columnLimits[$kolom])) {
                    $panjangAktual = mb_strlen($strVal, 'UTF-8');
                    $batasMaksimal = $columnLimits[$kolom];

                    if ($panjangAktual > $batasMaksimal) {
                        $daftarErrorKolom[] = "Kolom \"{$labelKolom}\" ({$kolom}) berisi \"{$strVal}\" ({$panjangAktual} karakter) — melebihi batas maksimal {$batasMaksimal} karakter. Silakan pilih TOLAK pada kolom ini.";
                    }
                }
            }

            if (!empty($daftarErrorKolom)) {
                $pesanError = "Gagal menyimpan perubahan karena ada data yang tidak sesuai batas kolom database:\n• " . implode("\n• ", $daftarErrorKolom);
                return redirect()->back()->with('error', $pesanError);
            }

            DetailSiswa::where('id_siswa', $pengajuan->id_siswa)->update($keranjangUpdate);
        }

        // 5. Tentukan Status Akhir Pengajuan
        $status_akhir = ($jumlahDiterima > 0) ? 'disetujui' : 'ditolak';

        // 6. Update Status dan Catatan di tabel 'pengajuan_biodata'
        $pengajuan->update([
            'status'           => $status_akhir,
            'keterangan_admin' => $request->input('keterangan_admin'),
        ]);

        // 7. Berikan pesan sukses yang detail ke Admin
        $pesan = "Validasi selesai! {$jumlahDiterima} data diperbarui, dan {$jumlahDitolak} data ditolak.";

        return redirect()->route('master.validasi_bio.index')->with('success', $pesan);
    }
}