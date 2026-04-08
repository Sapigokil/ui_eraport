<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.signin');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $loginType = $request->input('login_type', 'guru'); // Ambil tipe login (default: guru)
        $login = $request->input('login');

        // ==========================================
        // JALUR 1: LOGIN SISWA (Tanpa Password)
        // ==========================================
        if ($loginType === 'siswa') {
            
            // 1. Cari siswa berdasarkan NISN atau NIPD (Di UI disebut NIS)
            $siswa = Siswa::where('nisn', $login)
                          ->orWhere('nipd', $login) 
                          ->first();

            // Jika tidak ada di tabel siswa
            if (!$siswa) {
                return back()->withErrors([
                    'message' => 'NIS/NISN tidak ditemukan di pangkalan data sekolah.',
                ])->withInput($request->only('login'));
            }

            // 2. Auto-Provisioning: Cari User, Jika tidak ada maka buatkan secara diam-diam!
            $userSiswa = User::firstOrCreate(
                ['username' => $siswa->nisn], 
                [
                    'name'     => $siswa->nama_siswa,
                    'email'    => $siswa->nisn . '@siswa.local', 
                    'password' => Hash::make(Str::random(24)),   
                    
                    // ✅ PERBAIKAN 1: Isi kolom role manual
                    'role'     => 'siswa_erapor', 
                    
                    // ✅ PERBAIKAN 2: Isi foreign key ke tabel siswa
                    'id_siswa' => $siswa->id_siswa, 
                ]
            );

            // 3. Tautkan Role Spatie (Jika User ini baru dibuat / belum punya role)
            if (!$userSiswa->hasRole('siswa_erapor')) {
                $userSiswa->assignRole('siswa_erapor');
            }

            // ✅ PERBAIKAN 3: Update kolom id_user di tabel siswa (Relasi Dua Arah)
            // Lakukan pengecekan agar tidak melakukan proses save berulang-ulang setiap kali login
            if (is_null($siswa->id_user)) {
                $siswa->id_user = $userSiswa->id;
                $siswa->save();
            }

            // 4. Paksa Login & Redirect
            Auth::login($userSiswa);
            $request->session()->regenerate();
            
            return redirect()->intended('/dashboard');

        } 
        
        // ==========================================
        // JALUR 2: LOGIN GURU / ADMIN (Normal)
        // ==========================================
        else {
            // Cek apakah input tersebut adalah format email yang valid
            // Jika format email, maka kolom db yang dicek adalah 'email', jika bukan maka 'username'
            $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            // Susun credentials
            $credentials = [
                $fieldType => $login,
                'password' => $request->input('password')
            ];

            $rememberMe = $request->rememberMe ? true : false;

            // Lakukan Attempt Login
            if (Auth::attempt($credentials, $rememberMe)) {
                $request->session()->regenerate();
                return redirect()->intended('/dashboard');
            }

            // Jika gagal
            return back()->withErrors([
                'message' => 'Username/Email atau Password salah.',
            ])->withInput($request->only('login')); // Kembalikan input 'login'
        }
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/sign-in');
    }
}