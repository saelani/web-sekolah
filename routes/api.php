<?php

use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\StudentAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HomeController;

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