<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\DetailSiswa;
use App\Models\Kelas;
use App\Models\Ekskul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SiswaExport; 

class SiswaController extends Controller
{
    protected $siswaFillable = ['nipd', 'nisn', 'nama_siswa', 'jenis_kelamin', 'tingkat', 'id_kelas', 'id_ekskul'];

    private function applyFilters($query, Request $request)
    {
        $statusFilter = $request->get('status', 'aktif');
        if ($statusFilter !== 'semua') {
            $query->where('status', $statusFilter);
        }

        if ($request->has('id_kelas')) {
            $filterKelas = $request->id_kelas;
            if ($filterKelas == 'no_class') {
                $query->whereNull('id_kelas');
            } elseif ($filterKelas != 'all' && $filterKelas != '') {
                $query->where('id_kelas', $filterKelas);
            }
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%')
                  ->orWhere('nipd', 'like', '%' . $search . '%');
            });
        }
        return $query;
    }

    public function index(Request $request)
    {
        $listKelas = Kelas::orderBy('nama_kelas')->get();
        $query = Siswa::with('kelas', 'ekskul');
        $query = $this->applyFilters($query, $request);
        
        $query->orderBy('nama_siswa', 'asc');
        $siswas = $query->paginate(20)->withQueryString();

        return view('siswa.index', compact('siswas', 'listKelas'));
    }

    public function create()
    {
        $siswa = new Siswa();
        $detail = new DetailSiswa(); 
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $ekskulList = Ekskul::orderBy('nama_ekskul')->get();
        
        return view('siswa.create', compact('siswa', 'detail', 'kelasList', 'ekskulList'));
    }

    public function store(Request $request)
    {
        $jkMap = [
            'laki-laki' => 'L',
            'laki laki' => 'L',
            'l'         => 'L',
            'perempuan' => 'P',
            'p'         => 'P',
        ];

        $jkInput = strtolower(trim($request->jenis_kelamin));

        $request->merge([
            'jenis_kelamin' => $jkMap[$jkInput] ?? null
        ]);
        
        $request->validate([
            'nipd' => 'required|string|max:20|unique:siswa,nipd',
            'nisn' => 'required|string|max:10|unique:siswa,nisn',
            'nama_siswa' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'id_kelas' => 'required|integer|exists:kelas,id_kelas',
            'id_ekskul' => 'nullable|integer|exists:ekskul,id_ekskul',
            'nik' => 'nullable|string|max:20|unique:detail_siswa,nik',
            'email' => 'nullable|email|unique:detail_siswa,email',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
        ]);

        $kelas = Kelas::findOrFail($request->id_kelas);

        $request->merge([
            'tingkat' => $kelas->tingkat
        ]);

        DB::beginTransaction();
        try {
            $siswa = Siswa::create($request->only($this->siswaFillable));

            $detailFields = (new DetailSiswa())->getFillable();
            $detailData = $request->only(array_diff($detailFields, ['id_siswa', 'id_kelas']));
            $detailData['id_kelas'] = $request->id_kelas;

            $siswa->detail()->create($detailData);

            DB::commit();
            return redirect()->route('master.siswa.index')->with('success', 'Data Siswa berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error saat menyimpan data Siswa: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data siswa: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $siswa = Siswa::with('detail', 'kelas', 'ekskul')->findOrFail($id);
        return view('siswa.show', compact('siswa'));
    }
    
    public function edit($id)
    {
        $siswa = Siswa::with('detail', 'kelas', 'ekskul')->findOrFail($id);
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $ekskulList = Ekskul::orderBy('nama_ekskul')->get();

        return view('siswa.edit', compact('siswa', 'kelasList', 'ekskulList'));
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $jkMap = [
            'laki-laki' => 'L',
            'laki laki' => 'L',
            'l'         => 'L',
            'perempuan' => 'P',
            'p'         => 'P',
        ];

        $jkInput = strtolower(trim($request->jenis_kelamin));

        $request->merge([
            'jenis_kelamin' => $jkMap[$jkInput] ?? null
        ]);
        
        $request->validate([
            'nipd' => ['required', 'string', 'max:20', Rule::unique('siswa', 'nipd')->ignore($siswa->id_siswa, 'id_siswa')],
            'nisn' => ['required', 'string', 'max:10', Rule::unique('siswa', 'nisn')->ignore($siswa->id_siswa, 'id_siswa')],
            'nama_siswa' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'id_kelas' => 'required|integer|exists:kelas,id_kelas',
            'nik' => ['nullable', 'string', 'max:20', Rule::unique('detail_siswa', 'nik')->ignore($siswa->id_siswa, 'id_siswa')],
            'email' => ['nullable', 'email', Rule::unique('detail_siswa', 'email')->ignore($siswa->id_siswa, 'id_siswa')],
        ]);
        
        DB::beginTransaction();
        try {
            $siswa->update($request->only($this->siswaFillable));

            $detailFields = (new DetailSiswa())->getFillable();
            $detailData = $request->only(array_diff($detailFields, ['id_siswa']));
            $detailData['id_kelas'] = $request->id_kelas;

            $siswa->detail()->updateOrCreate(
                ['id_siswa' => $siswa->id_siswa],
                $detailData
            );

            DB::commit();
            return redirect()->route('master.siswa.index')->with('success', 'Data Siswa berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error saat memperbarui data Siswa: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data siswa: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $siswa = Siswa::findOrFail($id);
            $siswa->detail()->delete(); 
            $siswa->delete();

            DB::commit();
            return redirect()->route('master.siswa.index')->with('success', 'Data Siswa dan detailnya berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Gagal menghapus Siswa $id: " . $e->getMessage()); 
            return redirect()->back()->with('error', 'Gagal menghapus data siswa: ' . $e->getMessage());
        }
    }

    /**
     * Pengaturan Massal: Update Kelas Awal & Tanggal Masuk
     */
    public function massUpdate(Request $request)
    {
        $request->validate([
            'target_kelas' => 'required|integer|exists:kelas,id_kelas',
            'kelas_awal'   => 'required|string|max:50',
            'tgl_masuk'    => 'required|date',
        ]);

        try {
            // Ambil semua id_siswa yang ada di kelas target dan berstatus aktif
            $idSiswas = Siswa::where('id_kelas', $request->target_kelas)
                             ->where('status', 'aktif')
                             ->pluck('id_siswa');

            if ($idSiswas->isEmpty()) {
                return back()->with('error', 'Tidak ada siswa aktif di kelas yang dipilih.');
            }

            // Update langsung ke tabel detail_siswa
            DetailSiswa::whereIn('id_siswa', $idSiswas)->update([
                'kelas_awal' => $request->kelas_awal,
                'tgl_masuk'  => $request->tgl_masuk,
            ]);

            return back()->with('success', 'Data massal (Kelas Awal & Tgl Masuk) berhasil diupdate untuk ' . $idSiswas->count() . ' siswa.');
            
        } catch (\Exception $e) {
            \Log::error("Mass Update Siswa Error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengupdate data massal.');
        }
    }

    // =========================================================================
    // IMPORT CSV METHOD
    // =========================================================================

    public function importCsv(Request $request)
    {
        set_time_limit(0); 
        ini_set('memory_limit', '512M'); 
        
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $rawLines = file($file->getPathname());

        $rows = array_map(function ($line) {
            $line = str_replace(['`', '“', '”'], '"', $line);
            return str_getcsv($line, ',', '"', '\\');
        }, $rawLines);
        
        if (count($rows) < 10) {
            return back()->with('error', 'File CSV tidak valid!');
        }

        $headerRow1 = str_getcsv($rows[4][0]); 
        $headerRow2 = str_getcsv($rows[5][0]); 
        $rawHeader = array_merge($headerRow1, $headerRow2);

        $header = array_map(function ($h) {
            $h = strtolower(trim($h));
            $h = str_replace(["\r", "\n"], '_', $h);
            return str_replace([' ', '.', '-', '/', '\\', '(', ')'], '_', $h);
        }, $rawHeader);

        $dataStart = 9;
        $count = 0;

        for ($i = $dataStart; $i < count($rows); $i++) {

            if (!isset($rows[$i][0]) || trim($rows[$i][0]) == '') {
                continue;
            }

            $row = str_getcsv($rows[$i][0] ?? '', ',', '"', '\\');

            if (count($row) <= 1) {
                $row = str_getcsv($rawLines[$i], ',', '"', '\\');
            }

            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), null);
            }
            if (count($row) > count($header)) {
                $row = array_slice($row, 0, count($header));
            }

            $mapped = array_combine($header, $row);
            if (!$mapped) continue;

            $rombel = $mapped['rombel_saat_ini'] ?? null;  

            $idKelas = null;
            $tingkat = null;

            if ($rombel) {
                $parts = explode(' ', $rombel);
                $tingkat = is_numeric($parts[0]) ? intval($parts[0]) : 10;

                $kelas = Kelas::where('nama_kelas', $rombel)->first();

                if (!$kelas) {
                    $kelas = Kelas::create([
                        'nama_kelas' => $rombel,      
                        'tingkat'    => $tingkat,      
                        'jurusan'    => $parts[1] ?? '', 
                        'wali_kelas' => null,         
                        'jumlah_siswa' => 0,
                    ]);
                }

                $idKelas = $kelas->id_kelas;
            }

            // Data Ayah
            $mapped['data_ayah_nama']               = $row[24] ?? null;
            $mapped['data_ayah_tahun_lahir']        = $row[25] ?? null;
            $mapped['data_ayah_jenjang_pendidikan'] = $row[26] ?? null;
            $mapped['data_ayah_pekerjaan']          = $row[27] ?? null;
            $mapped['data_ayah_penghasilan']        = $row[28] ?? null;
            $mapped['data_ayah_nik']                = $row[29] ?? null;

            // Data Ibu
            $mapped['data_ibu_nama']                = $row[30] ?? null;
            $mapped['data_ibu_tahun_lahir']         = $row[31] ?? null;
            $mapped['data_ibu_jenjang_pendidikan']  = $row[32] ?? null;
            $mapped['data_ibu_pekerjaan']           = $row[33] ?? null;
            $mapped['data_ibu_penghasilan']         = $row[34] ?? null;
            $mapped['data_ibu_nik']                 = $row[35] ?? null;

            // Data Wali
            $mapped['data_wali_nama']               = $row[36] ?? null;
            $mapped['data_wali_tahun_lahir']        = $row[37] ?? null;
            $mapped['data_wali_jenjang_pendidikan'] = $row[38] ?? null;
            $mapped['data_wali_pekerjaan']          = $row[39] ?? null;
            $mapped['data_wali_penghasilan']        = $row[40] ?? null;
            $mapped['data_wali_nik']                = $row[41] ?? null;

            $mapped['no_kps'] = $mapped['no__kps'] ?? null;
            $mapped['jml_saudara_kandung'] = preg_replace('/[^0-9]/', '', ($row[64] ?? ''));
            $mapped['jarak_rumah'] = $row[65] ?? null;

            $rombel = $mapped['rombel_saat_ini'] ?? null;

            if ($rombel) {
                preg_match('/^\d+/', $rombel, $match);
                $mapped['tingkat'] = $match[0] ?? null;
                $mapped['kelas'] = $rombel;
            } else {
                $mapped['tingkat'] = null;
                $mapped['kelas'] = null;
            }

            $siswa = Siswa::create([
                'nipd'          => $mapped['nipd'] ?? null,
                'nisn'          => $mapped['nisn'] ?? null,
                'nama_siswa'    => $mapped['nama'] ?? null,
                'jenis_kelamin' => $mapped['jk'] ?? null,
                'tingkat'       => $tingkat,
                'id_kelas'      => $idKelas, 
                'id_ekskul'     => null,
            ]);

            if ($idKelas) {
                Kelas::where('id_kelas', $idKelas)->increment('jumlah_siswa');
            }

            // Memastikan penamaan kunci $mapped untuk kolom baru sesuai penulisan nama header di Excel
            DetailSiswa::create([
                'id_siswa' => $siswa->id_siswa,
                'id_kelas' => $idKelas, 
                
                // KOLOM BARU IMPORT
                'kelas_awal' => $mapped['kelas_awal'] ?? null,
                'tgl_masuk'  => $mapped['tanggal_masuk'] ?? $mapped['tgl_masuk'] ?? null,
                'telp_wali'  => $mapped['telp_wali'] ?? $mapped['telepon_wali'] ?? null,

                'tempat_lahir' => $mapped['tempat_lahir'] ?? null,
                'tanggal_lahir' => $mapped['tanggal_lahir'] ?? null,
                'agama' => $mapped['agama'] ?? null,
                'alamat' => $mapped['alamat'] ?? null,
                'kelurahan' => $mapped['kelurahan'] ?? null,
                'kecamatan' => $mapped['kecamatan'] ?? null,
                'kode_pos' => $mapped['kode_pos'] ?? null,
                'telepon' => $mapped['telepon'] ?? null,
                'no_hp' => $mapped['hp'] ?? null,
                'email' => $mapped['e_mail'] ?? null,
                'nik' => $mapped['nik'] ?? null,
                'rt' => $mapped['rt'] ?? null,
                'rw' => $mapped['rw'] ?? null,
                'dusun' => $mapped['dusun'] ?? null,
                'jenis_tinggal' => $mapped['jenis_tinggal'] ?? null,
                'alat_transportasi' => $mapped['alat_transportasi'] ?? null,
                'skhun' => $mapped['skhun'] ?? null,
                'penerima_kps' => $mapped['penerima_kps'] ?? null,
                'no_kps' => $mapped['no_kps'] ?? null,
                'rombel' => $mapped['kelas'] ?? null,
                'no_peserta_ujian_nasional' => $mapped['no_peserta_ujian_nasional'] ?? null,
                'no_seri_ijazah' => $mapped['no_seri_ijazah'] ?? null,
                'penerima_kip' => $mapped['penerima_kip'] ?? null,
                'no_kip' => $mapped['nomor_kip'] ?? null,
                'nama_kip' => $mapped['nama_di_kip'] ?? null,
                'no_kks' => $mapped['nomor_kks'] ?? null,
                'no_regis_akta_lahir' => $mapped['no_registrasi_akta_lahir'] ?? null,
                'bank' => $mapped['bank'] ?? null,
                'no_rek_bank' => $mapped['nomor_rekening_bank'] ?? null,
                'rek_atas_nama' => $mapped['rekening_atas_nama'] ?? null,
                'layak_pip_usulan' => $mapped['layak_pip__usulan_dari_sekolah_'] ?? null,
                'alasan_layak_pip' => $mapped['alasan_layak_pip'] ?? null,
                'kebutuhan_khusus' => $mapped['kebutuhan_khusus'] ?? null,
                'sekolah_asal' => $mapped['sekolah_asal'] ?? null,
                'anak_ke_berapa' => $mapped['anak_ke_berapa'] ?? null,
                'lintang' => $mapped['lintang'] ?? null,
                'bujur' => $mapped['bujur'] ?? null,
                'no_kk' => $mapped['no_kk'] ?? null,
                'bb' => $mapped['berat_badan'] ?? null,
                'tb' => $mapped['tinggi_badan'] ?? null,
                'lingkar_kepala' => $mapped['lingkar_kepala'] ?? null,
                'jml_saudara_kandung' => $mapped['jml_saudara_kandung'],
                'jarak_rumah' => $mapped['jarak_rumah'] ?? null,

                'nama_ayah' => $mapped['data_ayah_nama'] ?? null,
                'tahun_lahir_ayah' => $mapped['data_ayah_tahun_lahir'] ?? null,
                'jenjang_pendidikan_ayah' => $mapped['data_ayah_jenjang_pendidikan'] ?? null,
                'pekerjaan_ayah' => $mapped['data_ayah_pekerjaan'] ?? null,
                'penghasilan_ayah' => $mapped['data_ayah_penghasilan'] ?? null,
                'nik_ayah' => $mapped['data_ayah_nik'] ?? null,

                'nama_ibu' => $mapped['data_ibu_nama'] ?? null,
                'tahun_lahir_ibu' => $mapped['data_ibu_tahun_lahir'] ?? null,
                'jenjang_pendidikan_ibu' => $mapped['data_ibu_jenjang_pendidikan'] ?? null,
                'pekerjaan_ibu' => $mapped['data_ibu_pekerjaan'] ?? null,
                'penghasilan_ibu' => $mapped['data_ibu_penghasilan'] ?? null,
                'nik_ibu' => $mapped['data_ibu_nik'] ?? null,

                'nama_wali' => $mapped['data_wali_nama'] ?? null,
                'tahun_lahir_wali' => $mapped['data_wali_tahun_lahir'] ?? null,
                'jenjang_pendidikan_wali' => $mapped['data_wali_jenjang_pendidikan'] ?? null,
                'pekerjaan_wali' => $mapped['data_wali_pekerjaan'] ?? null,
                'penghasilan_wali' => $mapped['data_wali_penghasilan'] ?? null,
                'nik_wali' => $mapped['data_wali_nik'] ?? null,
            ]);

            $count++;
        }

        return back()->with('success', "Import selesai! Total baris masuk: $count");
    }

    public function importXlsx(Request $request)
    {
        set_time_limit(300); 
        ini_set('memory_limit', '512M'); 
        
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        
        $file = $request->file('file');
        
        try {
            $rows = Excel::toArray(new \stdClass(), $file)[0]; 
        } catch (\Exception $e) {
            \Log::error("Gagal membaca file Excel Siswa: " . $e->getMessage());
            return back()->with('error', 'Gagal membaca file Excel Siswa. Error: ' . $e->getMessage());
        }

        if (count($rows) < 2) { 
            return back()->with('error', 'File Excel kosong atau tidak memiliki data!');
        }
        
        // 1. OLAH HEADER (Header hanya di Baris 1 / Index 0)
        $rawHeader = $rows[0] ?? []; 

        $header = [];
        $headerKeys = [];
        foreach ($rawHeader as $idx => $h) {
            $h = strtolower(trim((string)$h)); 
            $h = str_replace(["\r", "\n"], '_', $h);
            $cleanH = str_replace([' ', '.', '-', '/', '\\', '(', ')'], '_', $h); 
            
            if (empty($cleanH)) {
                $cleanH = 'kolom_' . ($idx + 1);
            }
            
            $originalCleanH = $cleanH;
            $counter = 1;
            while (in_array($cleanH, $headerKeys)) {
                $cleanH = $originalCleanH . '_' . $counter++;
            }

            $headerKeys[] = $cleanH;
            $header[] = $cleanH;
        }

        $headerCount = count($header);
        $dataStart = 1; // Data mulai dari Baris 2 Excel (Index 1)
        $countInsert = 0;
        $countUpdate = 0;
        $currentRow = 0;
        $skippedRows = []; 

        // 2. CACHE DATA KELAS & SISWA KE MEMORI (SUPER FAST)
        $kelasCache = Kelas::pluck('id_kelas', 'nama_kelas')->toArray();
        $existingNipd = Siswa::whereNotNull('nipd')->pluck('nipd', 'nipd')->toArray();
        $existingNisnMap = Siswa::whereNotNull('nisn')->pluck('id_siswa', 'nisn')->toArray();

        $parseDate = function($value) {
            if (empty($value)) return null;
            try {
                if (is_numeric($value) && $value > 0) {
                    return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->toDateString();
                }
                return \Carbon\Carbon::parse($value)->toDateString();
            } catch (\Exception $e) {
                return null;
            }
        };

        $cleanNumber = fn($val) => preg_replace('/[^0-9]/', '', (string)($val ?? ''));

        \DB::beginTransaction();
        
        try {
            $consecutiveEmptyRows = 0;
            $affectedKelasIds = [];

            for ($i = $dataStart; $i < count($rows); $i++) {
                $currentRow = $i + 1; // Nomor baris nyata di Excel
                $row = $rows[$i]; 

                // HENTIKAN OTOMATIS JIKA MENEMUKAN BARIS KOSONG BERTURUT-TURUT
                $nonEmptyValues = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
                if (empty($nonEmptyValues)) {
                    $consecutiveEmptyRows++;
                    if ($consecutiveEmptyRows >= 3) {
                        break; // Langsung stop loop jika 3 baris berturut-turut kosong
                    }
                    continue;
                }
                $consecutiveEmptyRows = 0; 
                
                if (count($row) < $headerCount) {
                    $row = array_pad($row, $headerCount, null);
                }

                $mapped = array_combine($header, array_slice($row, 0, $headerCount));
                if (!$mapped) {
                    $skippedRows[] = ['row' => $currentRow, 'reason' => 'Gagal memetakan kolom.'];
                    continue;
                }
                
                // SESUAIKAN DENGAN NAMA KOLOM DARI EXCEL DAPODIK
                $namaSiswa = trim((string)($mapped['nama'] ?? $mapped['nama_siswa'] ?? $mapped['nama_peserta_didik'] ?? '')); 
                $nisnSiswa = trim((string)($mapped['nisn'] ?? ''));
                $nipdSiswa = trim((string)($mapped['nipd'] ?? ''));

                if (empty($namaSiswa) || $namaSiswa === '0') {
                    $skippedRows[] = ['row' => $currentRow, 'reason' => "Nama Siswa kosong atau nilainya '0'"];
                    continue;
                }
                
                // Check NIPD Duplikat via Cache
                if (empty($nisnSiswa) && !empty($nipdSiswa)) {
                    if (isset($existingNipd[$nipdSiswa])) {
                        $skippedRows[] = ['row' => $currentRow, 'reason' => "NIPD ($nipdSiswa) sudah ada & NISN kosong."];
                        continue;
                    }
                }

                // OLAH DATA KELAS
                $rombel = trim((string)($mapped['rombel_saat_ini'] ?? $mapped['rombel'] ?? $mapped['kelas'] ?? ''));
                $idKelas = null;
                $tingkat = null;

                if (!empty($rombel)) {
                    $parts = explode(' ', $rombel);
                    $tingkat = is_numeric($parts[0]) ? intval($parts[0]) : 10;

                    if (!isset($kelasCache[$rombel])) {
                        $newKelas = Kelas::create([
                            'nama_kelas' => $rombel, 
                            'tingkat' => $tingkat, 
                            'jurusan' => $parts[1] ?? '', 
                            'wali_kelas' => null, 
                            'jumlah_siswa' => 0,
                        ]);
                        $kelasCache[$rombel] = $newKelas->id_kelas;
                    }
                    $idKelas = $kelasCache[$rombel];
                    $affectedKelasIds[$idKelas] = true;
                }

                // DATA UTAMA SISWA
                $siswaData = [
                    'nipd' => !empty($nipdSiswa) ? $nipdSiswa : null, 
                    'nisn' => !empty($nisnSiswa) ? $nisnSiswa : null, 
                    'nama_siswa' => $namaSiswa,
                    'jenis_kelamin' => $mapped['jk'] ?? $mapped['jenis_kelamin'] ?? null, 
                    'tingkat' => $tingkat, 
                    'id_kelas' => $idKelas, 
                    'id_ekskul' => null, 
                ];

                // DETAIL SISWA
                $detailData = [
                    'id_kelas' => $idKelas, 
                    'kelas_awal' => $mapped['kelas_awal'] ?? null,
                    'tgl_masuk'  => $parseDate($mapped['tanggal_masuk'] ?? $mapped['tgl_masuk'] ?? null),
                    'telp_wali'  => $mapped['telp_wali'] ?? $mapped['telepon_wali'] ?? null,
                    'tempat_lahir' => $mapped['tempat_lahir'] ?? null, 
                    'tanggal_lahir' => $parseDate($mapped['tanggal_lahir'] ?? null), 
                    'agama' => $mapped['agama'] ?? null,
                    'alamat' => $mapped['alamat'] ?? null, 
                    'kelurahan' => $mapped['kelurahan'] ?? null, 
                    'kecamatan' => $mapped['kecamatan'] ?? null,
                    'kode_pos' => $mapped['kode_pos'] ?? null, 
                    'telepon' => $mapped['telepon'] ?? null, 
                    'no_hp' => $mapped['hp'] ?? null,
                    'email' => $mapped['e_mail'] ?? $mapped['email'] ?? null, 
                    'nik' => $mapped['nik'] ?? null, 
                    'rt' => $mapped['rt'] ?? null, 
                    'rw' => $mapped['rw'] ?? null,
                    'dusun' => $mapped['dusun'] ?? null, 
                    'jenis_tinggal' => $mapped['jenis_tinggal'] ?? null, 
                    'alat_transportasi' => $mapped['alat_transportasi'] ?? null,
                    'skhun' => $mapped['skhun'] ?? null, 
                    'penerima_kps' => $mapped['penerima_kps'] ?? null, 
                    'no_kps' => $mapped['no_kps'] ?? null,
                    'rombel' => $rombel, 
                    'no_peserta_ujian_nasional' => $mapped['no_peserta_ujian_nasional'] ?? null,
                    'no_seri_ijazah' => $mapped['no_seri_ijazah'] ?? null, 
                    'penerima_kip' => $mapped['penerima_kip'] ?? null, 
                    'no_kip' => $mapped['nomor_kip'] ?? null,
                    'nama_kip' => $mapped['nama_di_kip'] ?? null, 
                    'no_kks' => $mapped['nomor_kks'] ?? null, 
                    'no_regis_akta_lahir' => $mapped['no_registrasi_akta_lahir'] ?? null,
                    'bank' => $mapped['bank'] ?? null, 
                    'no_rek_bank' => $mapped['nomor_rekening_bank'] ?? null, 
                    'rek_atas_nama' => $mapped['rekening_atas_nama'] ?? null,
                    'layak_pip_usulan' => $mapped['layak_pip__usulan_dari_sekolah_'] ?? null, 
                    'alasan_layak_pip' => $mapped['alasan_layak_pip'] ?? null,
                    'kebutuhan_khusus' => $mapped['kebutuhan_khusus'] ?? null, 
                    'sekolah_asal' => $mapped['sekolah_asal'] ?? null,
                    'anak_ke_berapa' => $mapped['anak_ke_berapa'] ?? null, 
                    'lintang' => $mapped['lintang'] ?? null, 
                    'bujur' => $mapped['bujur'] ?? null,
                    'no_kk' => $mapped['no_kk'] ?? null, 
                    'bb' => $mapped['berat_badan'] ?? null, 
                    'tb' => $mapped['tinggi_badan'] ?? null,
                    'lingkar_kepala' => $mapped['lingkar_kepala'] ?? null, 
                    'jml_saudara_kandung' => $cleanNumber($mapped['jml_saudara_kandung'] ?? null),
                    'jarak_rumah' => $mapped['jarak_rumah'] ?? null,

                    // DATA ORANG TUA / WALI
                    'nama_ayah' => $mapped['nama_ayah'] ?? null, 
                    'tahun_lahir_ayah' => $mapped['tahun_lahir_ayah'] ?? null, 
                    'jenjang_pendidikan_ayah' => $mapped['pendidikan_ayah'] ?? null,
                    'pekerjaan_ayah' => $mapped['pekerjaan_ayah'] ?? null, 
                    'penghasilan_ayah' => $mapped['penghasilan_ayah'] ?? null, 
                    'nik_ayah' => $mapped['nik_ayah'] ?? null,
                    'nama_ibu' => $mapped['nama_ibu'] ?? null, 
                    'tahun_lahir_ibu' => $mapped['tahun_lahir_ibu'] ?? null, 
                    'jenjang_pendidikan_ibu' => $mapped['pendidikan_ibu'] ?? null,
                    'pekerjaan_ibu' => $mapped['pekerjaan_ibu'] ?? null, 
                    'penghasilan_ibu' => $mapped['penghasilan_ibu'] ?? null, 
                    'nik_ibu' => $mapped['nik_ibu'] ?? null,
                    'nama_wali' => $mapped['nama_wali'] ?? null, 
                    'tahun_lahir_wali' => $mapped['tahun_lahir_wali'] ?? null, 
                    'jenjang_pendidikan_wali' => $mapped['pendidikan_wali'] ?? null,
                    'pekerjaan_wali' => $mapped['pekerjaan_wali'] ?? null, 
                    'penghasilan_wali' => $mapped['penghasilan_wali'] ?? null, 
                    'nik_wali' => $mapped['nik_wali'] ?? null,
                ];
                
                // SIMPAN / UPDATE SISWA
                if (!empty($nisnSiswa) && isset($existingNisnMap[$nisnSiswa])) {
                    $siswa = Siswa::find($existingNisnMap[$nisnSiswa]);
                    if ($siswa) {
                        $siswa->update($siswaData);
                        $countUpdate++;
                    }
                } else {
                    $siswa = Siswa::create($siswaData);
                    $countInsert++;
                    if (!empty($nisnSiswa)) {
                        $existingNisnMap[$nisnSiswa] = $siswa->id_siswa;
                    }
                }

                if ($siswa) {
                    if (!empty($nipdSiswa)) {
                        $existingNipd[$nipdSiswa] = $nipdSiswa;
                    }

                    $siswa->detail()->updateOrCreate(
                        ['id_siswa' => $siswa->id_siswa],
                        $detailData
                    );
                }
            }

            // UPDATE JUMLAH SISWA KELAS SEKALI SAJA DI AKHIR
            foreach (array_keys($affectedKelasIds) as $kId) {
                $totalInKelas = Siswa::where('id_kelas', $kId)->count();
                Kelas::where('id_kelas', $kId)->update(['jumlah_siswa' => $totalInKelas]);
            }

            \DB::commit(); 
            
            $totalProcessed = $countInsert + $countUpdate;
            if (count($skippedRows) > 0) {
                \Log::warning("Import Siswa: Ditemukan " . count($skippedRows) . " baris yang dilewati. Detail: " . json_encode($skippedRows));
            }
            
            $message = "Import Excel Siswa selesai! Berhasil diproses: $totalProcessed (Siswa Baru: $countInsert, Update: $countUpdate).";
            return redirect()->route('master.siswa.index')->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollBack(); 
            $errorMessage = "Import Excel Siswa gagal pada Baris ke-$currentRow. Error: " . $e->getMessage();
            \Log::error($errorMessage);
            return redirect()->back()->with('error', $errorMessage);
        }
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new SiswaExport($request), 'data_siswa_lengkap.xlsx');
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');

        $query = Siswa::with(['kelas', 'detail']);
        $query = $this->applyFilters($query, $request);

        $siswas = $query->orderBy('nama_siswa', 'asc')->get();
        
        $namaSekolah = \App\Models\InfoSekolah::value('nama_sekolah') ?? 'E-RAPOR';

        $pdf = Pdf::loadView('siswa.exports.data_siswa_pdf', compact('siswas', 'namaSekolah'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('Data_Siswa_' . date('Ymd') . '.pdf');
    }
}