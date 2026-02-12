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
            ->withCount('siswas')
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
            ->withCount('siswas')
            ->get();

        return view('kelas.index', compact('kelas'));
    }

    public function show($id_kelas)
    {
        $kelas = Kelas::with('guru')->withCount('siswas')->findOrFail($id_kelas);
        // Menggunakan relationship siswas jika sudah didefinisikan di model Kelas
        // $anggota = $kelas->siswas; 
        // Atau cara manual seperti kodemu:
        $anggota = Siswa::select('siswa.*')
            ->join('detail_siswa', 'detail_siswa.id_siswa', '=', 'siswa.id_siswa')
            ->where('detail_siswa.id_kelas', $id_kelas)
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
            'nama_kelas' => 'required|string|max:100',
            'tingkat'    => 'required|integer',
            'jurusan'    => 'required|string',
            // 'wali_kelas' string tidak wajib jika sudah pakai id_guru (tergantung struktur DB)
            // Pastikan input di form name="id_guru"
            'id_guru'    => 'required|exists:guru,id_guru', 
        ]);

        // Opsional: Ambil nama guru berdasarkan ID untuk mengisi kolom wali_kelas (jika kolom itu string manual)
        $guru = Guru::find($request->id_guru);
        $namaWaliKelas = $guru ? $guru->nama_guru : '-';

        Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'tingkat'    => $request->tingkat,
            'jurusan'    => $request->jurusan,
            'id_guru'    => $request->id_guru, // ✅ PERBAIKAN: Simpan ID Guru
            'wali_kelas' => $namaWaliKelas,    // Isi otomatis nama berdasarkan ID (opsional)
        ]);

        return redirect()->route('master.kelas.index')->with('success', 'Kelas berhasil ditambahkan!');
    }

    // Update data kelas
    public function update(Request $request, $id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);
        
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:100',
            'tingkat'    => 'required|string', // Pastikan tipe data sesuai (string/integer)
            'jurusan'    => 'required|string',
            'id_guru'    => 'required|exists:guru,id_guru', // ✅ PERBAIKAN: Validasi ID Guru
            // 'jumlah_siswa' => 'nullable|integer|min:0', // Biasanya jumlah siswa otomatis, tidak diinput manual
        ]);

        // Opsional: Update nama wali kelas string jika ID berubah
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

        // Perbaikan Query Anggota (Join yang benar)
        $anggota = Siswa::select(
                'siswa.nama_siswa',
                'siswa.nisn',
                'detail_siswa.id_kelas'
            )
            ->join('detail_siswa', 'detail_siswa.id_siswa', '=', 'siswa.id_siswa')
            ->where('detail_siswa.id_kelas', $id_kelas)
            ->get();

        return view('kelas.index', compact('kelas', 'anggota'));
    }

    // Tambah anggota ke kelas
    public function tambahAnggota(Request $request, $id_kelas)
    {
        // Validasi ID Siswa (bukan nama/nisn manual, biasanya pilih dari dropdown siswa yg belum punya kelas)
        $request->validate([
            'id_siswa' => 'required|exists:siswa,id_siswa'
        ]);

        // Update detail siswa
        DetailSiswa::updateOrCreate(
            ['id_siswa' => $request->id_siswa],
            ['id_kelas' => $id_kelas]
        );

        // Update jumlah siswa di tabel kelas (jika kolom ini ada dan manual)
        // Sebaiknya gunakan withCount di query daripada simpan statis
        $jumlah = DetailSiswa::where('id_kelas', $id_kelas)->count();
        Kelas::where('id_kelas', $id_kelas)->update(['jumlah_siswa' => $jumlah]);

        return redirect()->back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    // Hapus anggota tertentu
    public function hapusAnggota($id_siswa)
    {
        $detail = DetailSiswa::where('id_siswa', $id_siswa)->first();
        
        if($detail) {
            $id_kelas_lama = $detail->id_kelas;
            
            $detail->update(['id_kelas' => null]);

            // Update counter kelas lama
            if($id_kelas_lama) {
                $jumlah = DetailSiswa::where('id_kelas', $id_kelas_lama)->count();
                Kelas::where('id_kelas', $id_kelas_lama)->update(['jumlah_siswa' => $jumlah]);
            }
        }

        return back()->with('success', 'Anggota dihapus dari kelas.');
    }

    /**
     * 🔹 Export semua data kelas ke PDF
     */
    public function exportPdf()
    {
        $kelas = Kelas::orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->withCount('siswas')
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
        $kelas = Kelas::with('guru')->orderBy('tingkat')->orderBy('nama_kelas')->get();

        $filename = 'data_kelas.csv';
        $handle = fopen($filename, 'w+');

        // Header
        fputcsv($handle, ['No', 'Nama Kelas', 'Tingkat', 'Jurusan', 'Wali Kelas', 'Jumlah Siswa']);

        foreach ($kelas as $i => $k) {
            // Ambil nama wali kelas dari relasi atau kolom string
            $wali = $k->guru ? $k->guru->nama_guru : $k->wali_kelas;
            
            fputcsv($handle, [
                $i + 1,
                $k->nama_kelas,
                $k->tingkat,
                $k->jurusan,
                $wali,
                $k->jumlah_siswa // Atau $k->siswas_count jika pakai withCount
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
        $kelas = Kelas::with(['siswas', 'guru'])->findOrFail($id);

        $pdf = Pdf::loadView('kelas.exports.kelas_single_pdf', compact('kelas'))
            ->setPaper('a4', 'portrait');

        $filename = 'data_kelas_' . str_replace(' ', '_', strtolower($kelas->nama_kelas)) . '.pdf';

        return $pdf->download($filename);
    }
}