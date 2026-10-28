<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (! Auth::check()) {
            return view('auth.login');
        }

        $dashboardRoute = match (Auth::user()->role) {
            'admin' => 'admin.dashboard',
            'tutor' => 'tutor.dashboard',
            'student' => 'student.dashboard',
            'parent' => 'parent.dashboard',
            default => null,
        };

        if ($dashboardRoute) {
            return redirect()->route($dashboardRoute);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Role akun tidak valid. Silakan hubungi administrator.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'status' => 'active',
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'tutor') {
                return redirect()->route('tutor.dashboard');
            } elseif ($user->role === 'student') {
                return redirect()->route('student.dashboard');
            } elseif ($user->role === 'parent') {
                return redirect()->route('parent.dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Email atau password salah, atau akun tidak aktif.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
