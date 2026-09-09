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
        // Jika belum login atau akun user tidak punya data student
        if (! Auth::check() || ! Auth::user()->student) {
            Auth::logout();
            return redirect()->route('student.login')->withErrors([
                'email' => 'Akses ditolak. Halaman ini hanya untuk siswa.'
            ]);
        }

        return $next($request);
    }
}