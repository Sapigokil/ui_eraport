<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas; 
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        
        $tab = $request->tab ?? 'admin'; 
        $search = $request->search;
        $id_kelas = $request->id_kelas; 
        $perPage = $request->input('per_page', 10);

        $query = User::with('roles')->orderBy('id', 'DESC');

        if (!$currentUser->hasRole('developer')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'developer');
            });
        }

        if ($tab == 'admin') {
            $query->whereHas('roles', function($q) {
                $q->where('name', 'like', '%admin%')
                  ->orWhere('name', 'developer');
            });
        } 
        elseif ($tab == 'guru') {
            $query->whereHas('roles', function($q) {
                $q->where('name', 'like', '%guru%');
            });
        } 
        elseif ($tab == 'siswa') {
            $query->whereHas('roles', function($q) {
                $q->where('name', 'siswa');
            });
            
            if ($id_kelas) {
                $query->whereHas('siswa', function($q) use ($id_kelas) {
                    $q->where('id_kelas', $id_kelas);
                });
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($perPage === 'all') {
            $totalCount = $query->count();
            $users = $query->paginate($totalCount > 0 ? $totalCount : 1)->withQueryString();
        } else {
            $users = $query->paginate((int) $perPage)->withQueryString();
        }
        
        $kelas_list = collect();
        if ($tab == 'siswa') {
            $kelas_list = Kelas::orderBy('tingkat', 'asc')->orderBy('nama_kelas', 'asc')->get();
        }

        if ($currentUser->hasRole('developer')) {
            $roles = Role::all();
        } else {
            $roles = Role::where('name', '!=', 'developer')->get();
        }
        
        return view('user.users.index', compact('users', 'roles', 'search', 'tab', 'id_kelas', 'kelas_list', 'perPage'));
    }

    public function create()
    {
        if (Auth::user()->hasRole('developer')) {
            $roles = Role::whereNotIn('name', ['guru_erapor', 'guru_ekskul'])->get();
        } else {
            $roles = Role::whereNotIn('name', ['developer', 'guru_erapor', 'guru_ekskul'])->get();
        }

        $gurus = Guru::whereNull('id_user')->orderBy('nama_guru', 'asc')->get();
        $siswas = Siswa::with('kelas')->whereNull('id_user')->where('status', 'aktif')->orderBy('nama_siswa', 'asc')->get();

        return view('user.users.create', compact('roles', 'gurus', 'siswas'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'jenis_akun' => 'required|in:admin,guru,siswa',
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => ['required', 'string', Password::min(8)], 
            'role_name'  => 'required|exists:roles,name',
            'id_guru'    => 'nullable|required_if:jenis_akun,guru|exists:guru,id_guru',
            'id_siswa'   => 'nullable|required_if:jenis_akun,siswa|exists:siswa,id_siswa',
        ]);
        
        if (!Auth::user()->hasRole('developer') && $request->role_name == 'developer') {
            return redirect()->back()->with('error', 'Anda tidak diizinkan membuat user dengan role Developer.');
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password), 
                'is_active' => $request->has('is_active') ? 1 : 0,
                'id_guru'   => $request->jenis_akun == 'guru' ? $request->id_guru : null,
                'id_siswa'  => $request->jenis_akun == 'siswa' ? $request->id_siswa : null,
            ]);
            
            $user->assignRole($request->role_name);

            if ($request->jenis_akun == 'guru' && $request->id_guru) {
                DB::table('guru')->where('id_guru', $request->id_guru)->update(['id_user' => $user->id]);
            } elseif ($request->jenis_akun == 'siswa' && $request->id_siswa) {
                DB::table('siswa')->where('id_siswa', $request->id_siswa)->update(['id_user' => $user->id]);
            }

            DB::commit();
            return redirect()->route('settings.system.users.index')
                             ->with('success', 'Akun pengguna baru berhasil dibuat dan ditautkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $currentUser = Auth::user();

        // 1. PROTEKSI AKSES
        if (!$currentUser->hasRole('developer') && $user->hasRole('developer')) {
            abort(403, 'Akses Ditolak: Restricted User');
        }

        // 2. LOGIKA ROLE
        if ($currentUser->hasRole('developer')) {
            $roles = Role::all();
        } else {
            $roles = Role::where('name', '!=', 'developer')->get();
        }

        $userRoleNames = $user->roles->pluck('name')->toArray();
        $hiddenRoles = ['guru_erapor', 'guru_ekskul'];
        
        if (empty(array_intersect($hiddenRoles, $userRoleNames))) {
            $roles = $roles->reject(function($role) use ($hiddenRoles) {
                return in_array($role->name, $hiddenRoles);
            });
        }

        // 3. TENTUKAN JENIS AKUN SAAT INI UNTUK INFORMASI READ-ONLY
        $jenis_akun = 'admin';
        if ($user->id_guru) $jenis_akun = 'guru';
        elseif ($user->id_siswa) $jenis_akun = 'siswa';

        // Tidak perlu lagi mengambil seluruh data $gurus dan $siswas karena form bersifat Read-Only
        return view('user.users.edit', compact('user', 'roles', 'jenis_akun'));
    }
    
    public function update(Request $request, User $user)
    {
        if (!Auth::user()->hasRole('developer') && $user->hasRole('developer')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah data Developer.');
        }

        // VALIDASI DISESUAIKAN: Hapus validasi penautan karena fitur tersebut kini Read-Only
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email,' . $user->id, 
            'password'   => ['nullable', 'string', Password::min(8)], 
            'role_name'  => 'required|exists:roles,name',
        ]);

        if (!Auth::user()->hasRole('developer') && $request->role_name == 'developer') {
            return redirect()->back()->with('error', 'Anda tidak diizinkan menetapkan role Developer.');
        }
        
        DB::beginTransaction();
        try {
            // 1. UPDATE DETAIL USER (Tanpa menyentuh id_guru / id_siswa)
            $user->name = $request->name;
            $user->email = $request->email; 
            $user->is_active = $request->has('is_active') ? 1 : 0; 
            
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();
            
            // 2. UPDATE ROLE
            $user->syncRoles([]); 
            $user->assignRole($request->role_name);

            DB::commit();
            return redirect()->route('settings.system.users.index')
                             ->with('success', 'Data pengguna berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        if (!Auth::user()->hasRole('developer') && $user->hasRole('developer')) {
            return back()->with('error', 'Akses ditolak: Anda tidak dapat menghapus akun Developer.');
        }

        if ($user->hasAnyRole(['developer', 'admin', 'guru'])) {
             $roleName = $user->roles->first()->name ?? 'Utama'; 
             return back()->with('error', 'Pengguna dengan role sistem (' . Str::title($roleName) . ') dilindungi dan tidak dapat dihapus.');
        }

        if ($user->id == 1) { 
             return back()->with('error', 'Akun Super Admin utama tidak boleh dihapus.');
        }

        $user->delete();

        return redirect()->route('settings.system.users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }
}