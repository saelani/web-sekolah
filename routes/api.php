<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\StudentAuthController;
use App\Http\Controllers\API\StudentDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index']);
    Route::post('/student/exam/{examId}/start', [StudentDashboardController::class, 'startExam']);
    Route::post('/student/exam/{examId}/submit', [StudentDashboardController::class, 'submitExam']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::get('/home', [HomeController::class, 'index']);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rute untuk mengecek profil user yang sedang login (membutuhkan Token Sanctum)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rute Publik untuk Konsumsi Aplikasi Android (Tanpa Perlunya Login)
Route::prefix('v1')->group(function () {
    Route::get('/school-profile', [PublicApiController::class, 'profile']);
    Route::get('/posts', [PublicApiController::class, 'posts']);
    Route::get('/posts/{slug}', [PublicApiController::class, 'postDetail']);
    Route::get('/facilities', [PublicApiController::class, 'facilities']);
    Route::get('/achievements', [PublicApiController::class, 'achievements']);
});

// --- RUTE KHUSUS APLIKASI SISWA ---
Route::post('/student/login', [StudentAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student/me', [StudentAuthController::class, 'me']);
});
