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
        // Cek apakah user sudah login menggunakan guard default
        if (! Auth::check()) {
            return redirect()
                ->route('student.login')
                ->withErrors([
                    'email' => 'Silakan login terlebih dahulu.',
                ]);
        }

        $user = Auth::user();

        // Pastikan user memiliki relasi student atau rolenya student
        if (! $user->student && ! $user->hasRole('student')) {
            Auth::logout();

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
