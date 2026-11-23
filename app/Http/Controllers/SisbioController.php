<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use App\Models\DetailSiswa;
use App\Models\PengajuanBiodata;
use App\Models\BioSeason;
use Carbon\Carbon;

class SisbioController extends Controller
{
    // Helper Method (Private) untuk mengecek status BioSeason
    private function checkBiodataSeason()
    {
        $season = BioSeason::first();
        $isOpen = false;

        if ($season && $season->is_active) {
            $now = Carbon::now();
            $start = Carbon::parse($season->tanggal_mulai);
            $end = Carbon::parse($season->tanggal_akhir);

            $isOpen = $now->between($start, $end);
        }

        return [
            'season' => $season,
            'isOpen' => $isOpen,
        ];
    }

    public function index()
    {
        $id_siswa = Auth::user()->id_siswa;

        if (!$id_siswa) {
            abort(403, 'Akun Anda tidak tertaut dengan data Siswa.');
        }

        $siswa = Siswa::with(['detail', 'kelas'])->findOrFail($id_siswa);

        $pengajuanPending = PengajuanBiodata::where('id_siswa', $id_siswa)
            ->where('status', 'pending')
            ->first();

        $riwayatTerakhir = PengajuanBiodata::where('id_siswa', $id_siswa)
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->orderBy('updated_at', 'desc')
            ->first();

        // Cek Season
        $seasonCheck = $this->checkBiodataSeason();
        $bioSeason = $seasonCheck['season'];
        $isBiodataOpen = $seasonCheck['isOpen'];

        return view('sismenu.bio_index', compact('siswa', 'pengajuanPending', 'riwayatTerakhir', 'bioSeason', 'isBiodataOpen'));
    }

    // Method untuk menampilkan Halaman Form Lengkap
    public function edit()
    {
        // 1. Proteksi Halaman Form
        $seasonCheck = $this->checkBiodataSeason();
        if (!$seasonCheck['isOpen']) {
            return redirect()->route('sis.biodata')->with('error', 'Akses ditolak: Periode pembaruan biodata saat ini sedang ditutup.');
        }

        $id_siswa = Auth::user()->id_siswa;
        if (!$id_siswa) {
            abort(403, 'Akun Anda tidak tertaut dengan data Siswa.');
        }

        $siswa = Siswa::with(['detail', 'kelas'])->findOrFail($id_siswa);

        return view('sismenu.bio_form', compact('siswa'));
    }

    public function ajukanPerubahan(Request $request)
    {
        // 1. Proteksi Jendela Waktu
        $seasonCheck = $this->checkBiodataSeason();
        if (!$seasonCheck['isOpen']) {
            return redirect()->route('sis.biodata')->with('error', 'Aksi ditolak: Periode pembaruan biodata saat ini sedang ditutup. Data Anda tidak disimpan.');
        }

        // 2. Proteksi Dobel Pengajuan
        $id_siswa = Auth::user()->id_siswa;
        if (!$id_siswa) {
            abort(403, 'Akun Anda tidak tertaut dengan data Siswa.');
        }

        $cekPending = PengajuanBiodata::where('id_siswa', $id_siswa)->where('status', 'pending')->first();
        if ($cekPending) {
            return redirect()->route('sis.biodata')->with('error', 'Anda masih memiliki pengajuan perubahan yang sedang menunggu persetujuan Admin.');
        }

        // Bersihkan spasi berlebih pada seluruh input string sebelum validasi
        $trimmedInput = [];
        foreach ($request->all() as $key => $val) {
            $trimmedInput[$key] = is_string($val) ? trim($val) : $val;
        }
        $request->merge($trimmedInput);

        // Ambil batas panjang kolom aktual dari database aktif untuk tabel detail_siswa
        $detailModel = new DetailSiswa();
        $connectionName = $detailModel->getConnectionName();
        $tableName = $detailModel->getTable();
        $columnsInfo = DB::connection($connectionName)->select("SHOW COLUMNS FROM `{$tableName}`");

        $dbMax = [];
        foreach ($columnsInfo as $colInfo) {
            $field = $colInfo->Field ?? '';
            $type = strtolower((string) ($colInfo->Type ?? ''));
            if ($field !== '' && preg_match('/^(?:var)?char\((\d+)\)/', $type, $matches)) {
                $dbMax[$field] = (int) $matches[1];
            }
        }

        $maxLen = function (string $col, int $default) use ($dbMax): int {
            return isset($dbMax[$col]) ? min($dbMax[$col], $default) : $default;
        };

        $tahunMaksimal = (int) date('Y');

        // 3. VALIDASI BACKEND LENGKAP (Format & Batas Karakter Database)
        $rules = [
            'tempat_lahir'        => 'required|string|max:' . $maxLen('tempat_lahir', 100),
            'tanggal_lahir'       => 'required|date',
            'agama'               => 'required|string|max:' . $maxLen('agama', 30),
            'sekolah_asal'        => 'required|string|max:' . $maxLen('sekolah_asal', 150),
            'nik'                 => 'required|digits:16',
            'no_kk'               => 'required|digits:16',
            'no_regis_akta_lahir' => 'required|string|max:' . $maxLen('no_regis_akta_lahir', 100),
            'bb'                  => 'required|numeric|min:10|max:300',
            'tb'                  => 'required|numeric|min:50|max:250',
            'lingkar_kepala'      => 'required|numeric|min:20|max:100',
            'anak_ke_berapa'      => 'required|integer|min:1|max:30',
            'jml_saudara_kandung' => 'required|integer|min:0|max:30',
            'no_hp'               => 'required|regex:/^[0-9]{9,15}$/|max:' . $maxLen('no_hp', 15),
            'email'               => 'required|email|max:' . $maxLen('email', 100),
            'alamat'              => 'required|string|max:' . $maxLen('alamat', 255),
            'rt'                  => 'required|regex:/^[0-9]{1,3}$/|max:' . $maxLen('rt', 3),
            'rw'                  => 'required|regex:/^[0-9]{1,3}$/|max:' . $maxLen('rw', 3),
            'dusun'               => 'required|string|max:' . $maxLen('dusun', 100),
            'kelurahan'           => 'required|string|max:' . $maxLen('kelurahan', 100),
            'kecamatan'           => 'required|string|max:' . $maxLen('kecamatan', 100),
            'kode_pos'            => 'required|string|max:' . $maxLen('kode_pos', 50),
            'jenis_tinggal'       => 'required|string|max:' . $maxLen('jenis_tinggal', 50),
            'alat_transportasi'   => 'required|string|max:' . $maxLen('alat_transportasi', 100),

            'telp_ortu'           => 'nullable|regex:/^[0-9]{9,15}$/|max:' . $maxLen('telp_ortu', 15),
            'alamat_ortu'         => 'nullable|string|max:' . $maxLen('alamat_ortu', 255),
            'telp_wali'           => 'nullable|regex:/^[0-9]{9,15}$/|max:' . $maxLen('telp_wali', 15),
            'alamat_wali'         => 'nullable|string|max:' . $maxLen('alamat_wali', 255),

            'nama_ayah'               => 'required|string|max:' . $maxLen('nama_ayah', 150),
            'nik_ayah'                => 'required|digits:16',
            'tahun_lahir_ayah'        => "required|integer|digits:4|min:1900|max:{$tahunMaksimal}",
            'jenjang_pendidikan_ayah' => 'required|string|max:' . $maxLen('jenjang_pendidikan_ayah', 50),
            'pekerjaan_ayah'          => 'required|string|max:' . $maxLen('pekerjaan_ayah', 100),
            'penghasilan_ayah'        => 'required|string|max:' . $maxLen('penghasilan_ayah', 100),

            'nama_ibu'                => 'required|string|max:' . $maxLen('nama_ibu', 150),
            'nik_ibu'                 => 'required|digits:16',
            'tahun_lahir_ibu'         => "required|integer|digits:4|min:1900|max:{$tahunMaksimal}",
            'jenjang_pendidikan_ibu'  => 'required|string|max:' . $maxLen('jenjang_pendidikan_ibu', 50),
            'pekerjaan_ibu'           => 'required|string|max:' . $maxLen('pekerjaan_ibu', 100),
            'penghasilan_ibu'         => 'required|string|max:' . $maxLen('penghasilan_ibu', 100),

            'nama_wali'               => 'nullable|string|max:' . $maxLen('nama_wali', 150),
            'nik_wali'                => 'nullable|digits:16',
            'tahun_lahir_wali'        => "nullable|integer|digits:4|min:1900|max:{$tahunMaksimal}",
            'jenjang_pendidikan_wali' => 'nullable|string|max:' . $maxLen('jenjang_pendidikan_wali', 50),
            'pekerjaan_wali'          => 'nullable|string|max:' . $maxLen('pekerjaan_wali', 100),
            'penghasilan_wali'        => 'nullable|string|max:' . $maxLen('penghasilan_wali', 100),
        ];

        $messages = [
            'required' => 'Kolom :attribute wajib diisi.',
            'numeric'  => 'Kolom :attribute harus berupa angka.',
            'integer'  => 'Kolom :attribute harus berupa bilangan bulat.',
            'digits'   => 'Kolom :attribute harus terdiri dari tepat :digits digit angka.',
            'min'      => 'Nilai pada kolom :attribute minimal :min.',
            'max'      => 'Panjang/nilai pada kolom :attribute maksimal :max.',
            'email'    => 'Format alamat email tidak valid.',
            'date'     => 'Format tanggal pada kolom :attribute tidak valid.',
            'regex'    => 'Format isian pada kolom :attribute tidak sesuai (gunakan angka tanpa spasi/tanda baca).',
        ];

        $attributes = [
            'tempat_lahir'            => 'Tempat Lahir',
            'tanggal_lahir'           => 'Tanggal Lahir',
            'agama'                   => 'Agama',
            'sekolah_asal'            => 'Sekolah Asal',
            'nik'                     => 'NIK Siswa',
            'no_kk'                   => 'No. KK',
            'no_regis_akta_lahir'     => 'No. Registrasi Akta Lahir',
            'bb'                      => 'Berat Badan',
            'tb'                      => 'Tinggi Badan',
            'lingkar_kepala'          => 'Lingkar Kepala',
            'anak_ke_berapa'          => 'Anak Ke-',
            'jml_saudara_kandung'     => 'Jumlah Saudara Kandung',
            'no_hp'                   => 'No. Handphone / WA Siswa',
            'email'                   => 'Alamat Email',
            'alamat'                  => 'Alamat Jalan / Gg / Blok',
            'rt'                      => 'RT',
            'rw'                      => 'RW',
            'dusun'                   => 'Dusun / Kampung',
            'kelurahan'               => 'Kelurahan / Desa',
            'kecamatan'               => 'Kecamatan',
            'kode_pos'                => 'Kode Pos (Wilayah)',
            'jenis_tinggal'           => 'Jenis Tinggal',
            'alat_transportasi'       => 'Alat Transportasi',
            'telp_ortu'               => 'No. Handphone Orang Tua',
            'alamat_ortu'             => 'Alamat Orang Tua',
            'telp_wali'               => 'No. Handphone Wali',
            'alamat_wali'             => 'Alamat Wali',
            'nama_ayah'               => 'Nama Ayah',
            'nik_ayah'                => 'NIK Ayah',
            'tahun_lahir_ayah'        => 'Tahun Lahir Ayah',
            'jenjang_pendidikan_ayah' => 'Pendidikan Terakhir Ayah',
            'pekerjaan_ayah'          => 'Pekerjaan Ayah',
            'penghasilan_ayah'        => 'Penghasilan Ayah',
            'nama_ibu'                => 'Nama Ibu',
            'nik_ibu'                 => 'NIK Ibu',
            'tahun_lahir_ibu'         => 'Tahun Lahir Ibu',
            'jenjang_pendidikan_ibu'  => 'Pendidikan Terakhir Ibu',
            'pekerjaan_ibu'           => 'Pekerjaan Ibu',
            'penghasilan_ibu'         => 'Penghasilan Ibu',
            'nama_wali'               => 'Nama Wali',
            'nik_wali'                => 'NIK Wali',
            'tahun_lahir_wali'        => 'Tahun Lahir Wali',
            'jenjang_pendidikan_wali' => 'Pendidikan Terakhir Wali',
            'pekerjaan_wali'          => 'Pekerjaan Wali',
            'penghasilan_wali'        => 'Penghasilan Wali',
        ];

        $request->validate($rules, $messages, $attributes);

        $detailSiswa = DetailSiswa::where('id_siswa', $id_siswa)->first();
        if (!$detailSiswa) {
            return redirect()->route('sis.biodata')->with('error', 'Data detail biodata Anda belum tersedia di sistem. Silakan hubungi Admin.');
        }

        $inputs = $request->except(['_token', '_method']);
        $perubahan = [];

        foreach ($inputs as $kolom => $nilai_baru) {
            if (array_key_exists($kolom, $detailSiswa->getAttributes())) {
                $nilai_lama = $detailSiswa->$kolom;

                // Normalisasi string kosong menjadi null agar setara dengan database
                $input_bersih = ($nilai_baru === '' || $nilai_baru === null) ? null : trim((string) $nilai_baru);

                if ((string) $input_bersih !== (string) $nilai_lama) {
                    $perubahan[$kolom] = [
                        'lama' => $nilai_lama ?? '-',
                        'baru' => $input_bersih ?? '-',
                    ];
                }
            }
        }

        if (count($perubahan) > 0) {
            PengajuanBiodata::create([
                'id_siswa'       => $id_siswa,
                'data_perubahan' => $perubahan,
                'status'         => 'pending',
            ]);

            return redirect()->route('sis.biodata')->with('success', 'Pengajuan perubahan biodata berhasil dikirim! Silakan tunggu persetujuan Admin.');
        }

        return redirect()->route('sis.biodata.edit')->with('info', 'Tidak ada data yang Anda ubah.');
    }

    // Script AJAX Penanda Sudah Dibaca
    public function markAsRead($id)
    {
        try {
            $id_siswa = Auth::user()->id_siswa;

            $pengajuan = PengajuanBiodata::where('id_pengajuan', $id)
                ->where('id_siswa', $id_siswa)
                ->first();

            if ($pengajuan) {
                $pengajuan->is_read = 1;
                $pengajuan->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil ditandai sudah dibaca',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Data pengajuan tidak ditemukan atau bukan milik siswa ini.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error Server: ' . $e->getMessage(),
            ]);
        }
    }
}