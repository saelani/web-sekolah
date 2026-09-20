<?php

namespace App\Traits;

use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Throwable;

trait HasCustomNotifications
{
    /**
     * Jalankan aksi dengan penanganan exception global tanpa membuka tab baru/crash.
     */
    protected function handleSafetyExecution(callable $callback, string $successMessage = 'Data berhasil diproses.')
    {
        try {
            $result = $callback();

            Notification::make()
                ->title('Berhasil')
                ->body($successMessage)
                ->success()
                ->send();

            return $result;
        } catch (QueryException $e) {
            report($e);

            Notification::make()
                ->title('Gagal Memproses Data')
                ->body('Terjadi kesalahan pada database (Relasi data terkunci atau format tidak sesuai).')
                ->danger()
                ->persistent()
                ->send();

            return null;
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title('Kesalahan Sistem')
                ->body($e->getMessage() ?: 'Terjadi kesalahan internal pada sistem.')
                ->danger()
                ->persistent()
                ->send();

            return null;
        }
    }
}
