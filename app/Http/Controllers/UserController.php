<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        
        // 1. Query Dasar
        $query = User::with('roles')->orderBy('id', 'DESC');

        // 2. LOGIKA STEALTH MODE (Filter List User)
        // Jika yang login BUKAN developer, sembunyikan semua user yang punya role 'developer'
        if (!$currentUser->hasRole('developer')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'developer');
            });
        }

        $users = $query->paginate(15); 

        // 3. Filter List Role (Untuk filter di view jika ada)
        if ($currentUser->hasRole('developer')) {
            $roles = Role::all();
        } else {
            $roles = Role::where('name', '!=', 'developer')->get();
        }
        
        return view('user.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        // LOGIKA STEALTH MODE (Dropdown Role)
        // User biasa tidak boleh melihat opsi 'developer' saat membuat user baru
        if (Auth::user()->hasRole('developer')) {
            $roles = Role::all();
        } else {
            $roles = Role::where('name', '!=', 'developer')->get();
        }

        return view('user.users.create', compact('roles'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', Password::min(8)], 
            'role_name' => 'required|exists:roles,name',
        ]);
        
        // PROTEKSI: Mencegah user non-developer memberikan role 'developer'
        if (!Auth::user()->hasRole('developer') && $request->role_name == 'developer') {
            return redirect()->back()->with('error', 'Anda tidak diizinkan membuat user dengan role Developer.');
        }

        // 1. Buat User Baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
            'is_active' => $request->has('is_active'),
        ]);
        
        // 2. Assign Role
        $user->assignRole($request->role_name);
        
        return redirect()->route('settings.system.users.index')
                         ->with('success', 'Akun pengguna baru berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $currentUser = Auth::user();

        // PROTEKSI AKSES: Non-developer tidak boleh edit user Developer
        if (!$currentUser->hasRole('developer') && $user->hasRole('developer')) {
            abort(403, 'Akses Ditolak: Restricted User');
        }

        // LOGIKA STEALTH MODE (Dropdown Role)
        if ($currentUser->hasRole('developer')) {
            $roles = Role::all();
        } else {
            $roles = Role::where('name', '!=', 'developer')->get();
        }

        return view('user.users.edit', compact('user', 'roles'));
    }
    
    public function update(Request $request, User $user)
    {
        // PROTEKSI AWAL: Cek hak akses terhadap target user
        if (!Auth::user()->hasRole('developer') && $user->hasRole('developer')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah data Developer.');
        }

        // Validasi, perhatikan password sekarang 'nullable'
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id, 
            'password' => ['nullable', 'string', Password::min(8)], // <-- Tambahan Validasi Password
            'role_name' => 'required|exists:roles,name',
        ]);

        // PROTEKSI INPUT ROLE: Jangan biarkan user diubah jadi developer oleh non-dev
        if (!Auth::user()->hasRole('developer') && $request->role_name == 'developer') {
            return redirect()->back()->with('error', 'Anda tidak diizinkan menetapkan role Developer.');
        }
        
        // 1. Update Detail User
        $user->name = $request->name;
        $user->email = $request->email; 
        $user->is_active = $request->has('is_active'); 
        
        // Cek apakah kolom password diisi, jika ya, maka update passwordnya
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        
        // 2. Update Role
        $user->syncRoles([]); 
        $user->assignRole($request->role_name);
        
        return redirect()->route('settings.system.users.index')
                         ->with('success', 'Data pengguna dan role berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // 1. Cek: Jangan biarkan user menghapus akunnya sendiri yang sedang login
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        // 2. PROTEKSI STEALTH: Non-developer tidak boleh menghapus user Developer
        if (!Auth::user()->hasRole('developer') && $user->hasRole('developer')) {
            return back()->with('error', 'Akses ditolak: Anda tidak dapat menghapus akun Developer.');
        }

        // 3. PROTEKSI USER DENGAN ROLE KRUSIAL (PERMINTAAN BARU)
        // User yang memiliki role Developer, Admin, atau Guru tidak boleh dihapus.
        if ($user->hasAnyRole(['developer', 'admin', 'guru'])) {
             // Ambil nama role pertama yang dimiliki user untuk pesan error
             $roleName = $user->roles->first()->name ?? 'Utama'; 
             return back()->with('error', 'Pengguna dengan role sistem (' . Str::title($roleName) . ') dilindungi dan tidak dapat dihapus.');
        }

        // 4. (Opsional) Cek ID 1 (Super Admin Hardcoded)
        if ($user->id == 1) { 
             return back()->with('error', 'Akun Super Admin utama tidak boleh dihapus.');
        }

        // 5. Hapus User
        $user->delete();

        return redirect()->route('settings.system.users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }
}