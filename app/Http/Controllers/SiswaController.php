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
        set_time_limit(0); 
        ini_set('memory_limit', '512M'); 
        
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        
        $file = $request->file('file');
        
        try {
            $rows = Excel::toArray(new \stdClass(), $file)[0]; 
        } catch (\Exception $e) {
            \Log::error("Gagal membaca file Excel Siswa: " . $e->getMessage());
            return back()->with('error', 'Gagal membaca file Excel Siswa. Pastikan format file benar. Error: ' . $e->getMessage());
        }

        if (count($rows) < 8) { 
            return back()->with('error', 'File Excel tidak valid atau baris data kurang dari 8!');
        }
        
        $headerRow1 = $rows[4] ?? []; 
        $headerRow2 = $rows[5] ?? []; 
        
        $maxColumns = count($rows[7]); 
        
        $rawHeader = array_pad(array_merge($headerRow1, $headerRow2), $maxColumns, null);
        $rawHeader = array_slice($rawHeader, 0, $maxColumns);

        $header = [];
        $headerKeys = [];
        foreach ($rawHeader as $h) {
            $h = strtolower(trim((string)$h)); 
            $h = str_replace(["\r", "\n"], '_', $h);
            $cleanH = str_replace([' ', '.', '-', '/', '\\', '(', ')', ' '], '_', $h); 
            
            if (empty($cleanH)) {
                 $cleanH = 'kolom_kosong_' . (count($header) + 1);
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
        
        $dataStart = 7; 
        $countInsert = 0;
        $countUpdate = 0;
        $currentRow = 0;
        $skippedRows = []; 

        DB::beginTransaction();
        
        try {
            for ($i = $dataStart; $i < count($rows); $i++) {
                $currentRow = $i + 1;
                $row = $rows[$i]; 
                
                if (empty(array_filter($row))) {
                    $skippedRows[] = ['row' => $currentRow, 'reason' => 'Baris dianggap kosong'];
                    continue;
                }
                
                if (count($row) != $headerCount) {
                    $row = array_pad($row, $headerCount, null);
                    if (count($row) > $headerCount) {
                        $row = array_slice($row, 0, $headerCount);
                    }
                }
                
                $mapped = array_combine($header, $row);
                if (!$mapped) {
                     $skippedRows[] = ['row' => $currentRow, 'reason' => 'Gagal memetakan'];
                     continue;
                }
                
                $namaSiswa = trim((string)($mapped['nama'] ?? '')); 
                $nisnSiswa = trim((string)($mapped['nisn'] ?? ''));

                if (empty($namaSiswa) || $namaSiswa === '0') {
                    $skippedRows[] = ['row' => $currentRow, 'reason' => "Nama Siswa kosong atau nilainya '0'"];
                    continue;
                }
                
                if (empty($nisnSiswa)) {
                    $nipdSiswa = trim((string)($mapped['nipd'] ?? ''));
                    if (!empty($nipdSiswa) && Siswa::where('nipd', $nipdSiswa)->exists()) {
                         $skippedRows[] = ['row' => $currentRow, 'reason' => "NIPD ($nipdSiswa) sudah ada, NISN kosong. Dianggap duplikat."];
                         continue;
                    }
                }

                $rombel = $mapped['rombel_saat_ini'] ?? null;
                $idKelas = null;
                $tingkat = null;

                if ($rombel) {
                    $parts = explode(' ', $rombel);
                    $tingkat = is_numeric($parts[0]) ? intval($parts[0]) : 10;

                    $kelas = Kelas::where('nama_kelas', $rombel)->first();

                    if (!$kelas) {
                        $kelas = Kelas::create([
                            'nama_kelas' => $rombel, 'tingkat' => $tingkat, 'jurusan' => $parts[1] ?? '', 'wali_kelas' => null, 'jumlah_siswa' => 0,
                        ]);
                    }
                    $idKelas = $kelas->id_kelas;
                }

                $parseDate = function($value) {
                    if (empty($value)) return null;
                    try {
                         if (is_numeric($value) && $value > 0) {
                            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->toDateString();
                         }
                         return Carbon::parse($value)->toDateString();
                    } catch (\Exception $e) {
                        return null;
                    }
                };

                $cleanNumber = fn($val) => preg_replace('/[^0-9]/', '', (string)($val ?? ''));
                
                $siswaData = [
                    'nipd' => trim((string)($mapped['nipd'] ?? null)), 
                    'nisn' => $nisnSiswa, 
                    'nama_siswa' => $namaSiswa,
                    'jenis_kelamin' => $mapped['jk'] ?? null, 
                    'tingkat' => $tingkat, 
                    'id_kelas' => $idKelas, 
                    'id_ekskul' => null, 
                ];

                $detailData = [
                    'id_kelas' => $idKelas, 
                    
                    // KOLOM BARU IMPORT
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
                    'jml_saudara_kandung' => $cleanNumber($mapped['jml_saudara_kandung'] ?? $row[64] ?? null),
                    'jarak_rumah' => $mapped['jarak_rumah'] ?? null,

                    'nama_ayah' => $row[24] ?? null, 
                    'tahun_lahir_ayah' => $row[25] ?? null, 
                    'jenjang_pendidikan_ayah' => $row[26] ?? null,
                    'pekerjaan_ayah' => $row[27] ?? null, 
                    'penghasilan_ayah' => $row[28] ?? null, 
                    'nik_ayah' => $row[29] ?? null,
                    'nama_ibu' => $row[30] ?? null, 
                    'tahun_lahir_ibu' => $row[31] ?? null, 
                    'jenjang_pendidikan_ibu' => $row[32] ?? null,
                    'pekerjaan_ibu' => $row[33] ?? null, 
                    'penghasilan_ibu' => $row[34] ?? null, 
                    'nik_ibu' => $row[35] ?? null,
                    'nama_wali' => $row[36] ?? null, 
                    'tahun_lahir_wali' => $row[37] ?? null, 
                    'jenjang_pendidikan_wali' => $row[38] ?? null,
                    'pekerjaan_wali' => $row[39] ?? null, 
                    'penghasilan_wali' => $row[40] ?? null, 
                    'nik_wali' => $row[41] ?? null,
                ];
                
                $siswa = null;
                $action = 'INSERT';

                if (!empty($nisnSiswa)) {
                    $searchKey = ['nisn' => $nisnSiswa]; 
                    
                    $siswa = Siswa::updateOrCreate($searchKey, $siswaData);

                    if ($siswa->wasRecentlyCreated) {
                        $countInsert++;
                    } else {
                        $countUpdate++;
                    }
                } else {
                    $siswa = Siswa::create($siswaData);
                    $countInsert++;
                }

                if (!$siswa) {
                    $skippedRows[] = ['row' => $currentRow, 'reason' => 'Gagal mendapatkan/membuat instance Siswa.'];
                    continue;
                }
                
                $siswa->detail()->updateOrCreate(
                    ['id_siswa' => $siswa->id_siswa],
                    $detailData
                );
                
                if ($idKelas) {
                    Kelas::where('id_kelas', $idKelas)->increment('jumlah_siswa');
                }
            }

            DB::commit(); 
            
            $totalProcessed = $countInsert + $countUpdate;
            if (count($skippedRows) > 0) {
                 \Log::warning("Import Siswa: Ditemukan " . count($skippedRows) . " baris yang dilewati. Detail: " . json_encode($skippedRows));
            }
            
            $message = "Import Excel Siswa selesai! Total diproses: $totalProcessed (Insert: $countInsert, Update: $countUpdate). Ditemukan " . count($skippedRows) . " baris dilewati. Detail di log.";
            return redirect()->route('master.siswa.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack(); 
            $errorMessage = "Import Excel Siswa gagal pada Baris ke-$currentRow. Pesan Error: " . $e->getMessage();
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