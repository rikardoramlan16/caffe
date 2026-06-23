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

        // Check if user has registered their face
        if (!empty($user->face_descriptor)) {
            $request->session()->put('pending_face_auth_user_id', $user->id);
            return redirect()->route('login.verify-face');
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

    public function showVerifyFace(Request $request)
    {
        $userId = $request->session()->get('pending_face_auth_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Silakan masukkan email dan password terlebih dahulu.');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'Karyawan tidak ditemukan.');
        }

        return view('app.verify-face', compact('user'));
    }

    public function verifyFace(Request $request)
    {
        $userId = $request->session()->get('pending_face_auth_user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Sesi kedaluwarsa. Silakan masukkan email dan password kembali.'], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan.'], 404);
        }

        $request->validate([
            'descriptor' => ['required', 'string'],
        ]);

        $storedArray = json_decode($user->face_descriptor, true);
        $passedArray = json_decode($request->descriptor, true);

        if (!is_array($storedArray) || !is_array($passedArray) || count($storedArray) !== 128 || count($passedArray) !== 128) {
            return response()->json(['success' => false, 'message' => 'Format deskriptor wajah tidak valid.'], 400);
        }

        // Calculate Euclidean Distance
        $sum = 0;
        for ($i = 0; $i < 128; $i++) {
            $diff = $storedArray[$i] - $passedArray[$i];
            $sum += $diff * $diff;
        }
        $distance = sqrt($sum);

        // Threshold of 0.6 is standard for face-api.js verification
        if ($distance > 0.6) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah tidak cocok (Skor kecocokan rendah). Silakan coba lagi.'
            ], 422);
        }

        // Complete the authentication
        $request->session()->forget('pending_face_auth_user_id');
        $request->session()->put('auth_user', [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'branch_id' => $user->branch_id,
        ]);

        $redirectUrl = match ($user->role) {
            'owner' => route('dashboard.owner'),
            'super_admin' => route('dashboard.super-admin'),
            'admin' => route('dashboard.admin'),
            'kasir' => route('dashboard.cashier'),
            'barista' => route('dashboard.barista'),
            default => route('landing'),
        };

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi wajah berhasil! Selamat bekerja.',
            'redirect' => $redirectUrl
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('auth_user');

        return redirect()->route('landing');
    }
}
