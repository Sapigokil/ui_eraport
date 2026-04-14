<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa; 
use App\Models\Guru; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function create()
    {
        session()->forget(['data_ditemukan', 'step_dua', 'is_duplicate']);
        return view('auth.signup');
    }

    /**
     * Step 1: Cek Validitas Data & Cek Status Akun
     */
    public function checkUser(Request $request)
    {
        $request->validate([
            'tipe_akun'     => 'required|in:siswa,guru',
            'nomor_induk'   => 'required|string', 
            'tanggal_lahir' => 'required|date',
        ]);

        $dataMaster = null;
        $isDuplicate = false; // Flag default

        // --- LOGIKA SISWA ---
        if ($request->tipe_akun == 'siswa') {
            $siswa = Siswa::join('detail_siswa', 'siswa.id_siswa', '=', 'detail_siswa.id_siswa') 
                        ->where(function($q) use ($request) {
                            $q->where('siswa.nisn', $request->nomor_induk)
                              ->orWhere('siswa.nipd', $request->nomor_induk);
                        })
                        ->where('detail_siswa.tanggal_lahir', $request->tanggal_lahir)
                        ->select('siswa.nama_siswa', 'siswa.id_siswa') 
                        ->first();
            
            if ($siswa) {
                // Cek apakah User ini sudah ada di tabel users?
                if (User::where('id_siswa', $siswa->id_siswa)->exists()) {
                    $isDuplicate = true; // Tandai sebagai duplikat
                }

                $dataMaster = [
                    'nama'   => $siswa->nama_siswa,
                    'id_ref' => $siswa->id_siswa,
                    'role'   => 'siswa'
                ];
            }

        // --- LOGIKA GURU ---
        } elseif ($request->tipe_akun == 'guru') {
            $guru = Guru::join('detail_guru', 'guru.id_guru', '=', 'detail_guru.id_guru')
                        ->where(function($q) use ($request) {
                            $q->where('guru.nip', $request->nomor_induk)
                              ->orWhere('guru.nuptk', $request->nomor_induk);
                        })
                        ->where('detail_guru.tanggal_lahir', $request->tanggal_lahir)
                        ->select('guru.nama_guru', 'guru.id_guru') 
                        ->first();

            if ($guru) {
                // Cek apakah User ini sudah ada di tabel users?
                if (User::where('id_guru', $guru->id_guru)->exists()) {
                    $isDuplicate = true; // Tandai sebagai duplikat
                }

                $dataMaster = [
                    'nama'   => $guru->nama_guru,
                    'id_ref' => $guru->id_guru,
                    'role'   => 'guru'
                ];
            }
        }

        // Jika Data Tidak Ditemukan
        if (!$dataMaster) {
            return back()->withInput()->withErrors([
                'msg' => 'Data tidak ditemukan. Pastikan Nomor Induk dan Tanggal Lahir sesuai.'
            ]);
        }

        // Kirim data ke View
        // Kita kirim flag 'is_duplicate' agar View tahu harus menampilkan tombol Reset atau Form Daftar biasa
        return view('auth.signup', [
            'step_dua'       => true,
            'data_ditemukan' => $dataMaster,
            'is_duplicate'   => $isDuplicate,
            'old_input'      => $request->all()
        ]);
    }

    /**
     * Step 2A: Simpan Akun Baru (Create)
     */
    public function store(Request $request)
    {
        $this->validateData($request); 

        $id_guru  = ($request->tipe_akun == 'guru') ? $request->id_ref : null;
        $id_siswa = ($request->tipe_akun == 'siswa') ? $request->id_ref : null;

        $user = User::create([
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => Str::lower($request->email),
            'password'  => Hash::make($request->password),
            'role'      => $request->tipe_akun, // Kolom manual (tetap huruf kecil tidak apa-apa)
            'id_guru'   => $id_guru,
            'id_siswa'  => $id_siswa,
        ]);

        // --- PERBAIKAN LOGIKA ROLE SPATIE ---
        
        // 1. Tentukan nama Role sesuai database (Case Sensitive)
        $roleName = null;

        if ($request->tipe_akun == 'guru') {
            $roleName = 'Guru'; // Sesuai Seeder Anda (Huruf Besar)
        } elseif ($request->tipe_akun == 'siswa') {
            $roleName = 'Siswa'; // Pastikan Role 'Siswa' ada di database
        }

        // 2. Assign Role jika definisinya ada
        if ($roleName) {
            // Kita gunakan try-catch agar jika role tidak ditemukan di DB, aplikasi tidak crash
            try {
                $user->assignRole($roleName);
            } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                // Opsional: Log error atau abaikan jika role belum dibuat di DB
                // return back()->withErrors(['msg' => 'Role database belum tersedia.']);
            }
        }

        return redirect()->route('sign-up')->with('success_register', 'Akun berhasil dibuat!');
    }

    /**
     * Step 2B: Update Akun (Reset Password/Email)
     */
    public function updateAccount(Request $request)
    {
        // 1. Cari User yang akan diupdate berdasarkan ID Guru/Siswa
        $query = User::query();
        
        if ($request->tipe_akun == 'guru') {
            $query->where('id_guru', $request->id_ref);
        } else {
            $query->where('id_siswa', $request->id_ref);
        }
        
        $user = $query->firstOrFail(); // Error jika tidak ketemu (seharusnya ketemu)

        // 2. Validasi (Ignore ID User saat ini untuk pengecekan Unique)
        $request->validate([
            'username' => ['required', 'string', 'min:8', Rule::unique('users')->ignore($user->id)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'required|string|min:8',
            'id_ref'   => 'required',
        ], [
            'username.min'    => 'Username minimal 8 karakter.',
            'username.unique' => 'Username ini sudah dipakai user lain.',
            'email.unique'    => 'Email ini sudah dipakai user lain.',
            'password.min'    => 'Password minimal 8 karakter.',
        ]);

        // 3. Update Data
        $user->update([
            'name'      => $request->name, // Update nama jika ada perubahan di master
            'username'  => $request->username,
            'email'     => Str::lower($request->email),
            'password'  => Hash::make($request->password),
        ]);

        return redirect()->route('sign-up')->with('success_register', 'Akun berhasil diperbarui! Silahkan login dengan password baru.');
    }

    // Helper Validasi untuk Create
    private function validateData($request) {
        $request->validate([
            'username' => 'required|string|min:8|unique:users,username',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'name'      => 'required',
            'tipe_akun' => 'required',
            'id_ref'    => 'required', 
        ], [
            'username.min'    => 'Username minimal 8 karakter.',
            'username.unique' => 'Username ini sudah dipakai.',
            'password.min'    => 'Password minimal 8 karakter.',
            'email.unique'    => 'Email sudah terdaftar.',
        ]);
    }
}