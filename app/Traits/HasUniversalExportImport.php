<?php

namespace App\Traits;

use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

trait HasUniversalExportImport
{
    /**
     * Tombol Impor Data Excel/CSV
     */
    public static function getImportAction(string $importerClass): Action
    {
        return Action::make('importData')
            ->label('Impor Data')
            ->icon('heroicon-m-arrow-up-tray')
            ->color('info')
            ->form([
                FileUpload::make('file')
                    ->label('Pilih File Excel/CSV')
                    ->required()
                    ->disk('public') // Memastikan storage disk sesuai
                    ->directory('imports') // Menyimpan sementara di folder storage/app/public/imports
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                        'text/x-csv',
                        'application/csv',
                        'application/x-csv',
                        'text/comma-separated-values',
                        'text/x-comma-separated-values',
                    ]),
            ])
            ->action(function (array $data) use ($importerClass) {
                try {
                    Excel::import(new $importerClass, storage_path('app/public/' . $data['file']));

                    Notification::make()
                        ->title('Impor Berhasil')
                        ->body('Data berhasil diimpor ke dalam sistem.')
                        ->success()
                        ->send();
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Gagal Mengimpor Data')
                        ->body('Terjadi kesalahan: ' . $e->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();
                }
            });
    }

    /**
     * Tombol Ekspor Data Excel
     */
    public static function getExportAction(string $exporterClass, string $fileName = 'export.xlsx'): Action
    {
        return Action::make('exportData')
            ->label('Ekspor Data')
            ->icon('heroicon-m-arrow-down-tray')
            ->color('success')
            ->action(function () use ($exporterClass, $fileName) {
                try {
                    return Excel::download(new $exporterClass, $fileName);
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Gagal Mengekspor Data')
                        ->body('Terjadi kesalahan: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}