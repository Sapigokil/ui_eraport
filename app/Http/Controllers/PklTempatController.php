<?php

namespace App\Http\Controllers;

use App\Models\PklTempat;
use App\Models\Guru;
use Illuminate\Http\Request;

class PklTempatController extends Controller
{
    /**
     * Menampilkan daftar Tempat PKL beserta Filter dan Pagination
     */
    public function index(Request $request)
    {
        $query = PklTempat::query();

        // 1. Filter by Nama Perusahaan
        if ($request->filled('search_nama')) {
            $query->where('nama_perusahaan', 'like', '%' . $request->search_nama . '%');
        }

        // 2. Filter by Bidang Usaha
        if ($request->filled('search_bidang')) {
            $query->where('bidang_usaha', $request->search_bidang);
        }

        // 3. Filter by Status (Default '1' / Aktif)
        $status = $request->input('search_status', '1');
        if ($status !== 'all') {
            $query->where('is_active', $status);
        }

        $query->latest();

        // 4. Pagination (10, 25, 50, 100, all)
        $perPage = $request->input('per_page', 10);
        
        if ($perPage === 'all') {
            $tempatPkl = $query->get();
        } else {
            $tempatPkl = $query->paginate($perPage);
        }

        // Mendapatkan daftar bidang usaha yang ada di database untuk dropdown filter
        $listBidangUsaha = PklTempat::whereNotNull('bidang_usaha')
                                    ->where('bidang_usaha', '!=', '')
                                    ->distinct()
                                    ->pluck('bidang_usaha');

        return view('pkl.tempat.index', compact('tempatPkl', 'listBidangUsaha', 'perPage', 'status'));
    }

    public function create()
    {
        $gurus = Guru::orderBy('nama_guru', 'asc')->get();
        $bidangUsahas = PklTempat::whereNotNull('bidang_usaha')->where('bidang_usaha', '!=', '')->distinct()->pluck('bidang_usaha');
        
        return view('pkl.tempat.show', compact('gurus', 'bidangUsahas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'guru_id' => 'nullable|integer',
            'nama_perusahaan' => 'required|string|max:255',
            'bidang_usaha' => 'nullable|string|max:255',
            'nama_pimpinan' => 'nullable|string|max:255',
            'alamat_perusahaan' => 'required|string',
            'kota' => 'nullable|string|max:255',
            'no_telp_perusahaan' => 'nullable|string|max:50',
            'email_perusahaan' => 'nullable|email|max:255',
            'no_surat_mou' => 'nullable|string|max:255',
            'tanggal_mou' => 'nullable|date',
            'nama_instruktur' => 'required|string|max:255',
            'no_telp_instruktur' => 'nullable|string|max:50',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;

        PklTempat::create($data);

        return redirect()->route('pkl.tempat.index')->with('success', 'Data Tempat PKL berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $tempat = PklTempat::findOrFail($id);
        $gurus = Guru::orderBy('nama_guru', 'asc')->get();
        $bidangUsahas = PklTempat::whereNotNull('bidang_usaha')->where('bidang_usaha', '!=', '')->distinct()->pluck('bidang_usaha');
        
        return view('pkl.tempat.show', compact('tempat', 'gurus', 'bidangUsahas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'guru_id' => 'nullable|integer',
            'nama_perusahaan' => 'required|string|max:255',
            'bidang_usaha' => 'nullable|string|max:255',
            'nama_pimpinan' => 'nullable|string|max:255',
            'alamat_perusahaan' => 'required|string',
            'kota' => 'nullable|string|max:255',
            'no_telp_perusahaan' => 'nullable|string|max:50',
            'email_perusahaan' => 'nullable|email|max:255',
            'no_surat_mou' => 'nullable|string|max:255',
            'tanggal_mou' => 'nullable|date',
            'nama_instruktur' => 'required|string|max:255',
            'no_telp_instruktur' => 'nullable|string|max:50',
        ]);

        $tempat = PklTempat::findOrFail($id);
        
        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;
        
        $tempat->update($data);

        return redirect()->route('pkl.tempat.index')->with('success', 'Data Tempat PKL berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tempat = PklTempat::findOrFail($id);
        $tempat->delete();

        return redirect()->route('pkl.tempat.index')->with('success', 'Data Tempat PKL berhasil dihapus.');
    }

    // =====================================================================
    // FUNGSI IMPORT, EXPORT, & TEMPLATE
    // =====================================================================

    /**
     * Kolom baku untuk Template dan Export
     */
    private function getExcelColumns()
    {
        return [
            'Nama Perusahaan', 'Bidang Usaha', 'Nama Pimpinan', 'Alamat Lengkap', 
            'Kota', 'No Telp Perusahaan', 'Email', 'No Surat MOU', 'Tanggal MOU (YYYY-MM-DD)', 
            'Nama Instruktur', 'No Telp Instruktur', 'Status Aktif (1/0)'
        ];
    }

    /**
     * Mengunduh Template Kosong
     */
    public function downloadTemplate()
    {
        $fileName = 'Template_Import_Tempat_PKL.csv';
        $columns = $this->getExcelColumns();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            // Tambahkan BOM agar Excel mengenali UTF-8
            fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
            fputcsv($file, $columns, ';');
            // Tambahkan 1 baris contoh
            fputcsv($file, ['PT. Contoh Abadi', 'Teknologi', 'Budi Santoso', 'Jl. Merdeka No 1', 'Salatiga', '0298-123456', 'info@contoh.com', '001/MOU/2024', '2024-01-01', 'Agus', '08123456789', '1'], ';');
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Data ke Excel (Format CSV UTF-8 yang terbaca native di Excel)
     */
    public function exportExcel()
    {
        $fileName = 'Data_Tempat_PKL_' . date('Ymd_His') . '.csv';
        $tempatPkls = PklTempat::all();
        $columns = $this->getExcelColumns();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($tempatPkls, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
            fputcsv($file, $columns, ';');

            foreach ($tempatPkls as $row) {
                fputcsv($file, [
                    $row->nama_perusahaan,
                    $row->bidang_usaha,
                    $row->nama_pimpinan,
                    $row->alamat_perusahaan,
                    $row->kota,
                    $row->no_telp_perusahaan,
                    $row->email_perusahaan,
                    $row->no_surat_mou,
                    $row->tanggal_mou ? $row->tanggal_mou->format('Y-m-d') : '',
                    $row->nama_instruktur,
                    $row->no_telp_instruktur,
                    $row->is_active ? '1' : '0'
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Excel
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_import' => 'required|mimes:xlsx,xls,csv'
        ]);

        // Cek ketersediaan package Maatwebsite Excel
        if (!class_exists('\Maatwebsite\Excel\Facades\Excel')) {
            return back()->with('error', 'Sistem mendeteksi bahwa Package Maatwebsite/Laravel-Excel belum diinstal. Fitur pemrosesan file Excel (.xlsx) murni membutuhkan package ini.');
        }

        // Jika package sudah ada, proses import dijalankan di sini.
        // Excel::import(new PklTempatImport, $request->file('file_import'));
        
        return back()->with('success', 'Fungsi Import Excel sedang dalam tahap simulasi.');
    }
}