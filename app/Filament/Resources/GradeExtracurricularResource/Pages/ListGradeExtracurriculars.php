<?php

namespace App\Filament\Resources\GradeExtracurricularResource\Pages;

use App\Exports\GradeExtracurricularsExport;
use App\Filament\Resources\GradeExtracurricularResource;
use App\Imports\GradeExtracurricularsImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListGradeExtracurriculars extends ListRecords
{
    protected static string $resource = GradeExtracurricularResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // --- TOMBOL IMPORT NILAI EKSKUL ---
            Actions\Action::make('importExcel')
                ->label('Import Nilai')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('attachment')
                        ->label('File Excel / CSV')
                        ->helperText('Kolom header excel: enrollment_id, extracurricular_id, grade, description.')
                        ->disk('local')
                        ->directory('imports')
                        ->maxSize(10240)
                        ->rules(['required', 'file', 'mimes:xlsx,xls,csv,txt'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $filePath = Storage::disk('local')->path($data['attachment']);

                    Excel::import(new GradeExtracurricularsImport, $filePath);

                    Storage::disk('local')->delete($data['attachment']);

                    Notification::make()
                        ->title('Import Selesai')
                        ->body('Data nilai ekstrakurikuler berhasil diimpor.')
                        ->success()
                        ->send();
                }),

            // --- TOMBOL EXPORT NILAI EKSKUL ---
            Actions\Action::make('exportExcel')
                ->label('Export Nilai')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->action(function () {
                    return Excel::download(new GradeExtracurricularsExport, 'Nilai_Ekstrakurikuler_' . date('Y-m-d') . '.xlsx');
                }),
        ];
    }
}