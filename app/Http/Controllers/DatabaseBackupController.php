<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Filament\Notifications\Notification;

class DatabaseBackupController extends Controller
{
    /**
     * Export & Unduh SELURUH Database (.sql)
     */
    public function export()
    {
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', 3306);
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $fileName = "backup-full-database-" . date('Y-m-d_H-i-s') . ".sql";
        $filePath = storage_path("app/" . $fileName);

        // Memastikan direktori storage/app ada
        if (!file_exists(storage_path('app'))) {
            mkdir(storage_path('app'), 0755, true);
        }

        // Perintah mysqldump native Linux (menyertakan 2>&1 untuk menangkap stdErr jika gagal)
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s 2>&1',
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($filePath) || filesize($filePath) === 0) {
            $errorMsg = !empty($output) ? implode(' ', $output) : 'Gagal mengekspor database.';
            
            // Hapus file 0 byte jika terbuat
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            Notification::make()
                ->title('Gagal Export Database')
                ->body($errorMsg)
                ->danger()
                ->send();

            return back();
        }

        // Unduh file lalu hapus dari server setelah selesai dikirim
        return Response::download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Import / Restore SELURUH Database dari file .sql
     */
    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt|max:50000',
        ]);

        $file = $request->file('backup_file');
        $filePath = $file->getRealPath();

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', 3306);
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Perintah mysql restore native Linux
        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s --port=%s %s < %s 2>&1',
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $errorMsg = !empty($output) ? implode(' ', $output) : 'Format file SQL tidak valid.';

            Notification::make()
                ->title('Gagal Memulihkan Database')
                ->body($errorMsg)
                ->danger()
                ->send();

            return back();
        }

        Notification::make()
            ->title('Berhasil!')
            ->body('Seluruh database berhasil dipulihkan secara penuh.')
            ->success()
            ->send();

        return back();
    }
}