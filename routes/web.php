<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Livewire Component Frontend Public
use App\Livewire\Public\HomeScreen;
use App\Livewire\Public\PostDetail;

// Livewire Component Dashboard Siswa
use App\Livewire\Student\Dashboard as StudentDashboard;

// Controller Auth Siswa Standar
use App\Http\Controllers\Student\AuthController;

// Middleware Proteksi Siswa
use App\Http\Middleware\EnsureUserIsStudent;

use App\Http\Controllers\StudentAttendancePdfController;
use App\Http\Controllers\StudentSavingPdfController;
use App\Http\Controllers\AcademicCalendarPdfController;

Route::middleware(['auth'])->group(function () {
    Route::get('/academic-calendar/pdf', AcademicCalendarPdfController::class)
        ->name('academic-calendar.pdf');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/student-saving/pdf', StudentSavingPdfController::class)
        ->name('student-saving.pdf');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/student-attendance/pdf', StudentAttendancePdfController::class)
        ->name('student-attendance.pdf');
});

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
| Web Routes - Auth Siswa (Standard Controller)
|--------------------------------------------------------------------------
*/

Route::get('/siswa/login', [AuthController::class, 'showLoginForm'])->name('student.login');
Route::post('/siswa/login', [AuthController::class, 'login'])->name('student.login.post');


/*
|--------------------------------------------------------------------------
| Web Routes - Portal Khusus Siswa (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware([EnsureUserIsStudent::class])->prefix('siswa')->name('student.')->group(function () {
    
    // Dashboard Utama Siswa
    Route::get('/dashboard', StudentDashboard::class)->name('dashboard');

    // Logout khusus siswa
    Route::post('/logout', function (Request $request) {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    })->name('logout');
});