<?php

use App\Http\Middleware\EnsureUserIsStudent;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registrasi alias middleware untuk proteksi portal siswa
        $middleware->alias([
            'ensure.student' => EnsureUserIsStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 1. Penanganan JSON bawaan untuk API
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // 2. Global Exception Handler untuk Filament & Livewire (Notifikasi Toast)
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->hasHeader('X-Livewire') || $request->is('admin*')) {
                // Ambil pesan error (tampilkan pesan mendetail hanya saat debug mode aktif)
                $errorMessage = config('app.debug')
                    ? $e->getMessage()
                    : 'Terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi atau hubungi Administrator.';

                Notification::make()
                    ->title('Terjadi Kesalahan Sistem')
                    ->body($errorMessage)
                    ->danger()
                    ->persistent()
                    ->send();

                // Hentikan agar browser tidak me-reload halaman atau membuka tab baru
                return response()->noContent();
            }
        });
    })->create();
