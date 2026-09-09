<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Livewire Component Frontend Public
use App\Livewire\Public\HomeScreen;
use App\Livewire\Public\PostDetail;

// Livewire Component Portal Siswa
use App\Livewire\Student\Auth\Login as StudentLogin;
use App\Livewire\Student\Dashboard as StudentDashboard;

// Middleware Proteksi Siswa
use App\Http\Middleware\EnsureUserIsStudent;

/*
|--------------------------------------------------------------------------
| Web Routes - Public / Frontend
|--------------------------------------------------------------------------
*/

// Halaman Utama / Home
Route::get('/', HomeScreen::class)->name('home');

// Detail Berita (diakses publik)
Route::get('/berita/{post:slug}', PostDetail::class)->name('berita.show');


/*
|--------------------------------------------------------------------------
| Web Routes - Portal Khusus Siswa
|--------------------------------------------------------------------------
*/

// Guest Route (Hanya bisa diakses jika belum login)
Route::middleware('guest')->group(function () {
    Route::get('/siswa/login', StudentLogin::class)->name('student.login');
});

// Protected Routes (Hanya bisa diakses setelah login & dipastikan akun Siswa)
Route::middleware(['auth', EnsureUserIsStudent::class])->prefix('siswa')->name('student.')->group(function () {
    
    // Dashboard Utama Siswa
    Route::get('/dashboard', StudentDashboard::class)->name('dashboard');

    // Menu-menu Fitur Siswa (Aktifkan saat komponennya sudah dibuat)
    // Route::get('/profil', \App\Livewire\Student\Profile::class)->name('profile');
    // Route::get('/tabungan', \App\Livewire\Student\Savings::class)->name('savings');
    // Route::get('/kehadiran', \App\Livewire\Student\Attendance::class)->name('attendance');
    // Route::get('/nilai', \App\Livewire\Student\Grades::class)->name('grades');
    // Route::get('/cbt', \App\Livewire\Student\Cbt::class)->name('cbt');

    // Logout khusus siswa
    Route::get('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Gunakan redirect()->to() atau redirect()->route() standar Laravel
        return redirect()->route('student.login');
    })->name('logout');
});