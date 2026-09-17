<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Livewire Component Frontend Public
use App\Livewire\Public\HomeScreen;
use App\Livewire\Public\PostDetail;

// Livewire Component Auth (Sign In, Sign Up, Forgot Password)
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ForgotPassword;

// Livewire Component Dashboard Siswa
use App\Livewire\Student\Dashboard as StudentDashboard;

// Middleware Proteksi Siswa
use App\Http\Middleware\EnsureUserIsStudent;

// PDF Controllers
use App\Http\Controllers\StudentAttendancePdfController;
use App\Http\Controllers\StudentSavingPdfController;
use App\Http\Controllers\AcademicCalendarPdfController;
use App\Http\Controllers\SchedulePdfController;

/*
|--------------------------------------------------------------------------
| PDF Routes (Protected by Auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/pdf/schedules', SchedulePdfController::class)->name('pdf.schedules');
    Route::get('/academic-calendar/pdf', AcademicCalendarPdfController::class)->name('academic-calendar.pdf');
    Route::get('/student-saving/pdf', StudentSavingPdfController::class)->name('student-saving.pdf');
    Route::get('/student-attendance/pdf', StudentAttendancePdfController::class)->name('student-attendance.pdf');
});

/*
|--------------------------------------------------------------------------
| Web Routes - Public / Frontend
|--------------------------------------------------------------------------
*/
Route::get('/', HomeScreen::class)->name('home');
Route::get('/berita/{post:slug}', PostDetail::class)->name('berita.show');


/*
|--------------------------------------------------------------------------
| Web Routes - Autentikasi (Livewire Sign In, Sign Up, Forgot Password)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Mengganti controller lama dengan komponen Livewire
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    
    // Alias jika ingin mempertahankan URL /siswa/login mengarah ke Livewire login yang sama
    Route::get('/siswa/login', Login::class)->name('student.login');
});


/*
|--------------------------------------------------------------------------
| Web Routes - Portal Khusus Siswa (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', EnsureUserIsStudent::class])->prefix('siswa')->name('student.')->group(function () {
    
    // Dashboard Utama Siswa
    Route::get('/dashboard', StudentDashboard::class)->name('dashboard');

    // Logout khusus siswa (menggunakan guard default 'web' karena menggunakan sys_users)
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});