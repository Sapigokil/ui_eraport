<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::id());

        return view('laravel-examples.user-profile', compact('user'));
    }

    public function update(Request $request)
    {
        if (config('app.is_demo') && in_array(Auth::id(), [1])) {
            return back()->with('error', "You are in a demo version. You are not allowed to change the email for default users.");
        }

        $request->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'password' => 'nullable|min:8|confirmed',
        ]);
        // [
        //     'name.required' => 'Name is required',
        //     'email.required' => 'Email is required',
        //     'password.required' => 'Password is required',
        // ]);

        $user = User::find(Auth::id());

        $data = [
        'name' => $request->name,
        'email' => $request->email,
        'location' => $request->location,
        'phone' => $request->phone,
        'about' => $request->about,
    ];

    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

        $user->update($data);
        Auth::logout();

        return redirect()->route('sign-in')
        ->with('success', 'Password berhasil diubah. Silakan login kembali.');
    }
}
