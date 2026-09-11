<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('student.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ], [
            'email.required' => 'NISN / Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // 1. Find user manually to prevent standard Auth::attempt event dispatching
        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            
            // 2. Check if user is associated with student data
            if (! $user->student) {
                return back()->withErrors([
                    'email' => 'Akun ini bukan akun siswa. Silakan login via Admin Panel.',
                ]);
            }

            // 3. Log in without firing Filament's global auth listener
            Auth::guard('student')->login($user);
            $request->session()->regenerate();

            return redirect()->route('student.dashboard');
        }

        return back()->withErrors([
            'email' => 'NISN / Email atau Password yang Anda masukkan salah.',
        ]);
    }
}