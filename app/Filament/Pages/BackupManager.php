<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class BackupManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    // Disatukan ke dalam grup Manajemen Sekolah
    protected static ?string $navigationGroup = 'Manajemen Sekolah';

    protected static ?string $navigationLabel = 'Backup & Restore DB';

    protected static ?string $title = 'Manajemen Keamanan Database';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.backup-manager';

    /**
     * 1. HANYA ADMIN / HEADMASTER / SUPER ADMIN YANG BISA TAMPIL & AKSES MENU INI
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();

        // Ambil role dari property user / teacher
        $userRole = $user->role ?? $user->role_type ?? $user->teacher?->role_type ?? '';

        return in_array($userRole, ['admin', 'headmaster', 'super_admin']) || ($user->is_admin ?? false);
    }

    protected function getHeaderActions(): array
    {
        return [
            // 2. EXPORT DATABASE (Tanpa Buka Tab Baru)
            Action::make('exportDatabase')
                ->label('Backup / Download Seluruh Database')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Unduh Backup Seluruh Database?')
                ->modalDescription('Proses ini akan mengekspor SELURUH TABEL & DATA sekolah ke dalam satu file dump (.sql).')
                ->action(function () {
                    $dbHost = config('database.connections.mysql.host');
                    $dbPort = config('database.connections.mysql.port', 3306);
                    $dbName = config('database.connections.mysql.database');
                    $dbUser = config('database.connections.mysql.username');
                    $dbPass = config('database.connections.mysql.password');

                    $fileName = 'backup-full-database-'.date('Y-m-d_H-i-s').'.sql';
                    $filePath = storage_path('app/'.$fileName);

                    if (! file_exists(storage_path('app'))) {
                        mkdir(storage_path('app'), 0755, true);
                    }

                    // Eksekusi mysqldump langsung di action Filament
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

                    if ($returnVar !== 0 || ! file_exists($filePath) || filesize($filePath) === 0) {
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }

                        Notification::make()
                            ->title('Gagal Export Database')
                            ->body(! empty($output) ? implode(' ', $output) : 'Perintah mysqldump gagal dieksekusi.')
                            ->danger()
                            ->send();

                        return;
                    }

                    // Langsung pemicu pengunduhan file tanpa route tambahan & tanpa tab baru
                    return response()->download($filePath)->deleteFileAfterSend(true);
                }),

            // 3. RESTORE / IMPORT DATABASE
            Action::make('importDatabase')
                ->label('Restore / Timpa Database')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('danger')
                ->form([
                    Forms\Components\FileUpload::make('backup_file')
                        ->label('Upload File Backup (.sql)')
                        ->acceptedFileTypes(['application/sql', 'text/plain', 'text/x-sql'])
                        ->directory('temp-backups')
                        ->required(),
                ])
                ->modalHeading('Restore Database Sekolah')
                ->modalDescription('⚠️ PERHATIAN: Memulihkan database akan MENIMPA SELURUH DATA saat ini.')
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $filePath = storage_path('app/public/'.$data['backup_file']);

                    if (! file_exists($filePath)) {
                        Notification::make()->title('File tidak ditemukan.')->danger()->send();

                        return;
                    }

                    $dbHost = config('database.connections.mysql.host');
                    $dbPort = config('database.connections.mysql.port', 3306);
                    $dbName = config('database.connections.mysql.database');
                    $dbUser = config('database.connections.mysql.username');
                    $dbPass = config('database.connections.mysql.password');

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
                    @unlink($filePath);

                    if ($returnVar === 0) {
                        Notification::make()->title('Database Berhasil Dipulihkan!')->success()->send();
                    } else {
                        Notification::make()
                            ->title('Gagal Restore Database')
                            ->body(! empty($output) ? implode(' ', $output) : 'Format SQL tidak cocok.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
