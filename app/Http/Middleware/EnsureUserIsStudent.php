<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('student');

        // Belum login sebagai siswa
        if (! $guard->check()) {
            return redirect()
                ->route('student.login')
                ->withErrors([
                    'email' => 'Silakan login terlebih dahulu.',
                ]);
        }

        $user = $guard->user();

        // Pastikan user memiliki relasi student
        if (! $user || ! $user->student) {

            $guard->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('student.login')
                ->withErrors([
                    'email' => 'Akses ditolak. Halaman ini hanya untuk akun siswa.',
                ]);
        }

        return $next($request);
    }
}