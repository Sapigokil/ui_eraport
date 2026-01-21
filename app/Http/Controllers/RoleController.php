<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Menampilkan daftar Role.
     */
    public function index()
    {
        $user = Auth::user();

        // LOGIKA STEALTH MODE
        // Developer melihat semua. User lain tidak melihat role 'developer'.
        if ($user->hasRole('developer')) {
            $roles = Role::orderBy('id', 'DESC')->get();
        } else {
            $roles = Role::where('name', '!=', 'developer')
                         ->orderBy('id', 'DESC')
                         ->get();
        }

        $permissions = Permission::all();

        return view('user.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Menampilkan form Create.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('user.roles.create', compact('permissions'));
    }

    /**
     * Menyimpan Role baru.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);

        // PROTEKSI: Mencegah user non-developer membuat role bernama 'developer'
        if (!Auth::user()->hasRole('developer') && strtolower($request->name) == 'developer') {
             return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membuat role Developer.');
        }

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));

        return redirect()->route('settings.system.roles.index')
                        ->with('success', 'Role berhasil dibuat');
    }

    /**
     * Menampilkan form Edit.
     */
    public function edit(Role $role)
    {
        // PROTEKSI: Non-developer tidak boleh akses halaman edit milik role Developer
        if (!Auth::user()->hasRole('developer') && $role->name == 'developer') {
            abort(403, 'Akses Ditolak: Restricted Role');
        }

        $permissions = Permission::all();
    
        return view('user.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Memperbarui Role.
     */
    public function update(Request $request, Role $role)
    {
        // PROTEKSI: Non-developer tidak boleh mengupdate role Developer
        if (!Auth::user()->hasRole('developer') && $role->name == 'developer') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah role Developer.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // PROTEKSI NAMA: Jangan biarkan nama role diubah jadi 'developer' oleh non-dev
        if (!Auth::user()->hasRole('developer') && strtolower($request->name) == 'developer') {
             return redirect()->back()->with('error', 'Anda tidak boleh menamai role ini sebagai Developer.');
        }

        $role->update(['name' => $request->name]);

        $newPermissions = $request->input('permissions', []);
        $role->syncPermissions($newPermissions); 
        
        return redirect()->route('settings.system.roles.index')
                         ->with('success', 'Role "' . $role->name . '" dan Izin berhasil diperbarui.');
    }

    /**
     * Menghapus Role.
     */
    public function destroy(Role $role)
    {
        $currentUser = Auth::user();

        // 1. PROTEKSI STEALTH (Developer asli tidak boleh disentuh)
        if (!$currentUser->hasRole('developer') && $role->name == 'developer') {
             return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // 2. PROTEKSI SYSTEM ROLES (REVISI: Tambah 'guru' dan 'siswa')
        // Role-role ini haram dihapus karena digunakan oleh sistem core / default register
        $systemRoles = ['developer', 'admin_erapor', 'guru_erapor', 'guru', 'siswa'];

        if (in_array(strtolower($role->name), $systemRoles)) {
            return redirect()->route('settings.system.roles.index') 
                             ->with('error', 'Role Sistem/Default (' . Str::title($role->name) . ') dilindungi dan tidak boleh dihapus.');
        }

        // 3. Cek User Terhubung
        if ($role->users()->count() > 0) {
            return redirect()->route('settings.system.roles.index')
                             ->with('error', 'Role ' . Str::title($role->name) . ' tidak dapat dihapus karena masih digunakan oleh user.');
        }

        $role->delete();

        return redirect()->route('settings.system.roles.index')
                        ->with('success', 'Role berhasil dihapus.');
    }
}