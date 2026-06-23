<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('app.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->with('error', 'Email atau password tidak valid.')->withInput();
        }

        $request->session()->put('auth_user', [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'branch_id' => $user->branch_id,
        ]);

        return match ($user->role) {
            'owner' => redirect()->route('dashboard.owner')->with('success', 'Selamat datang Owner!'),
            'super_admin' => redirect()->route('dashboard.super-admin')->with('success', 'Selamat datang Super Admin!'),
            'admin' => redirect()->route('dashboard.admin')->with('success', 'Selamat datang Admin!'),
            'kasir' => redirect()->route('dashboard.cashier')->with('success', 'Selamat datang Kasir!'),
            'barista' => redirect()->route('dashboard.barista')->with('success', 'Selamat datang Barista!'),
            default => redirect()->route('landing')->with('error', 'Akses dibatasi. Role Anda tidak memiliki izin dashboard.'),
        };
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('auth_user');

        return redirect()->route('landing');
    }
}
