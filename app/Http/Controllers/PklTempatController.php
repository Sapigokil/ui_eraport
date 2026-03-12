<?php

namespace App\Http\Controllers;

use App\Models\PklTempat;
use App\Models\Guru;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PklTempatExport;

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
    // FUNGSI IMPORT, EXPORT, & TEMPLATE (Menggunakan Maatwebsite Excel)
    // =====================================================================

    /**
     * Mengunduh Template Kosong (.xlsx)
     */
    public function downloadTemplate()
    {
        // Parameter 'true' untuk mode template (isi dummy data)
        return Excel::download(new PklTempatExport(true), 'Template_Import_Tempat_PKL.xlsx');
    }

    /**
     * Export Data ke Excel (.xlsx)
     */
    public function exportExcel()
    {
        $fileName = 'Data_Tempat_PKL_' . date('Ymd_His') . '.xlsx';
        // Parameter 'false' untuk mode export data murni
        return Excel::download(new PklTempatExport(false), $fileName);
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