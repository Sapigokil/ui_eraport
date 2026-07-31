<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\AnggotaKelas;
use App\Models\Guru;
use App\Models\DetailSiswa;
use App\Models\Siswa;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    // Halaman Data Kelas
    public function index()
    {
        $kelas = Kelas::orderBy('tingkat')
            ->orderBy('nama_kelas')
            // Tambahkan filter status aktif dengan LOWER agar kebal Case-Sensitive
            ->withCount(['siswas' => function ($query) {
                $query->whereRaw('LOWER(status) = ?', ['aktif']); 
            }])
            ->with('guru') // Load relasi guru agar nama wali kelas tampil benar
            ->get();

        return view('kelas.index', compact('kelas'));
    }

    /**
     * Halaman Index Data Kelas (versi rapi)
     */
    public function list()
    {
        $kelas = Kelas::orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->withCount(['siswas' => function ($query) {
                $query->whereRaw('LOWER(status) = ?', ['aktif']);
            }])
            ->get();

        return view('kelas.index', compact('kelas'));
    }

    public function show($id_kelas)
    {
        $kelas = Kelas::with('guru')
            ->withCount(['siswas' => function ($query) {
                $query->whereRaw('LOWER(status) = ?', ['aktif']);
            }])
            ->findOrFail($id_kelas);
        
        // 🔥 PERBAIKAN: Hapus JOIN, ambil langsung dari siswa.id_kelas
        $anggota = Siswa::where('id_kelas', $id_kelas)
            ->whereRaw('LOWER(status) = ?', ['aktif']) 
            ->orderBy('nama_siswa', 'asc') // Diurutkan berdasarkan abjad
            ->get();

        return view('kelas.show', compact('kelas', 'anggota'));
    }

    public function edit($id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);
        $guru = Guru::orderBy('nama_guru')->get();
        return view('kelas.edit', compact('kelas', 'guru'));
    }

    public function create()
    {
        $guru = Guru::orderBy('nama_guru')->get();
        return view('kelas.create', compact('guru'));
    }

    // Simpan data kelas baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas'    => 'required|string|max:100',
            'tingkat'       => 'required|integer',
            'jurusan'       => 'required|string',
            'prog_keahlian' => 'nullable|string', 
            'kons_keahlian' => 'nullable|string', 
            'id_guru'       => 'required|exists:guru,id_guru', 
        ]);

        $guru = Guru::find($request->id_guru);
        $namaWaliKelas = $guru ? $guru->nama_guru : '-';

        Kelas::create([
            'nama_kelas'    => $request->nama_kelas,
            'tingkat'       => $request->tingkat,
            'jurusan'       => $request->jurusan,
            'prog_keahlian' => $request->prog_keahlian, 
            'kons_keahlian' => $request->kons_keahlian, 
            'id_guru'       => $request->id_guru, 
            'wali_kelas'    => $namaWaliKelas,    
        ]);

        return redirect()->route('master.kelas.index')->with('success', 'Kelas berhasil ditambahkan!');
    }

    // Update data kelas
    public function update(Request $request, $id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);
        
        $validated = $request->validate([
            'nama_kelas'    => 'required|string|max:100',
            'tingkat'       => 'required|string', 
            'jurusan'       => 'required|string',
            'prog_keahlian' => 'nullable|string', 
            'kons_keahlian' => 'nullable|string', 
            'id_guru'       => 'required|exists:guru,id_guru', 
        ]);

        $guru = Guru::find($request->id_guru);
        $validated['wali_kelas'] = $guru ? $guru->nama_guru : $kelas->wali_kelas;

        $kelas->update($validated);
        
        return redirect()->back()->with('success', 'Data kelas berhasil diperbarui.');
    }

    // Hapus data kelas
    public function destroy($id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);
        $kelas->delete();

        return redirect()->back()->with('success', 'Data kelas berhasil dihapus.');
    }

    public function anggota($id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);

        // 🔥 PERBAIKAN: Hapus JOIN, ambil langsung dari siswa.id_kelas
        $anggota = Siswa::where('id_kelas', $id_kelas)
            ->whereRaw('LOWER(status) = ?', ['aktif']) 
            ->orderBy('nama_siswa', 'asc')
            ->get();

        return view('kelas.index', compact('kelas', 'anggota'));
    }

    // Tambah anggota ke kelas
    public function tambahAnggota(Request $request, $id_kelas)
    {
        $request->validate([
            'id_siswa' => 'required|exists:siswa,id_siswa'
        ]);

        // 1. Update id_kelas di tabel siswa (sebagai sumber kebenaran)
        Siswa::where('id_siswa', $request->id_siswa)
             ->update(['id_kelas' => $id_kelas]);

        // 2. Jika Anda masih menggunakan detail_siswa untuk keperluan lain, kita tetap update juga
        DetailSiswa::updateOrCreate(
            ['id_siswa' => $request->id_siswa],
            ['id_kelas' => $id_kelas]
        );

        // 3. Update jumlah siswa di tabel kelas 
        $jumlah = Siswa::where('id_kelas', $id_kelas)
            ->whereRaw('LOWER(status) = ?', ['aktif'])
            ->count();
            
        Kelas::where('id_kelas', $id_kelas)->update(['jumlah_siswa' => $jumlah]);

        return redirect()->back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    // Hapus anggota tertentu dari kelas
    public function hapusAnggota($id_siswa)
    {
        $siswa = Siswa::where('id_siswa', $id_siswa)->first();
        
        if($siswa) {
            $id_kelas_lama = $siswa->id_kelas;
            
            // 1. Kosongkan id_kelas di tabel siswa
            $siswa->update(['id_kelas' => null]);
            
            // 2. Kosongkan juga di detail_siswa
            DetailSiswa::where('id_siswa', $id_siswa)->update(['id_kelas' => null]);

            // 3. Hitung ulang jumlah siswa pada kelas yang ditinggalkan
            if($id_kelas_lama) {
                $jumlah = Siswa::where('id_kelas', $id_kelas_lama)
                    ->whereRaw('LOWER(status) = ?', ['aktif'])
                    ->count();
                    
                Kelas::where('id_kelas', $id_kelas_lama)->update(['jumlah_siswa' => $jumlah]);
            }
        }

        return back()->with('success', 'Anggota berhasil dikeluarkan dari kelas.');
    }

    /**
     * 🔹 Export semua data kelas ke PDF
     */
    public function exportPdf()
    {
        $kelas = Kelas::orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->withCount(['siswas' => function ($query) {
                $query->whereRaw('LOWER(status) = ?', ['aktif']);
            }])
            ->with('guru') // Load guru
            ->get();

        $pdf = Pdf::loadView(
            'kelas.exports.data_kelas_pdf',
            compact('kelas')
        )->setPaper('a4', 'landscape');

        return $pdf->download('data_kelas.pdf');
    }

    /**
     * 🔹 Export semua data kelas ke CSV
     */
    public function exportCsv()
    {
        $kelas = Kelas::with('guru')
            ->withCount(['siswas' => function ($query) {
                $query->whereRaw('LOWER(status) = ?', ['aktif']); // Hitung dinamis untuk CSV
            }])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $filename = 'data_kelas.csv';
        $handle = fopen($filename, 'w+');

        // Header
        fputcsv($handle, ['No', 'Nama Kelas', 'Tingkat', 'Jurusan', 'Program Keahlian', 'Konsentrasi Keahlian', 'Wali Kelas', 'Jumlah Siswa']);

        foreach ($kelas as $i => $k) {
            $wali = $k->guru ? $k->guru->nama_guru : $k->wali_kelas;
            
            fputcsv($handle, [
                $i + 1,
                $k->nama_kelas,
                $k->tingkat,
                $k->jurusan,
                $k->prog_keahlian, 
                $k->kons_keahlian, 
                $wali,
                $k->siswas_count // Gunakan property siswas_count hasil perhitungan dinamis
            ]);
        }

        fclose($handle);

        return Response::download($filename)->deleteFileAfterSend(true);
    }

    /**
     * 🔹 Export data satu kelas (dengan anggota) ke PDF
     */
    public function exportKelas($id)
    {
        $kelas = Kelas::with(['siswas' => function ($query) {
            $query->whereRaw('LOWER(status) = ?', ['aktif']); // Filter data siswa yang diload agar hanya yang aktif
        }, 'guru'])->findOrFail($id);

        $pdf = Pdf::loadView('kelas.exports.kelas_single_pdf', compact('kelas'))
            ->setPaper('a4', 'portrait');

        $filename = 'data_kelas_' . str_replace(' ', '_', strtolower($kelas->nama_kelas)) . '.pdf';

        return $pdf->download($filename);
    }
}