<?php
namespace App\Filament\Resources\LearningObjectiveResource\Pages;

use App\Exports\LearningObjectivesExport;
use App\Filament\Resources\LearningObjectiveResource;
use App\Imports\LearningObjectivesImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListLearningObjectives extends ListRecords
{
    protected static string $resource = LearningObjectiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // --- TOMBOL IMPORT CUSTOM ---
            Actions\Action::make('importExcel')
                ->label('Import TP')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('subject_id')
                        ->label('Mata Pelajaran')
                        ->relationship('subject', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('phase')
                        ->label('Fase')
                        ->options([
                            'A' => 'Fase A (Kelas 1-2)',
                            'B' => 'Fase B (Kelas 3-4)',
                            'C' => 'Fase C (Kelas 5-6)',
                        ])
                        ->required(),

                    Forms\Components\Select::make('level')
                        ->label('Tingkat Kelas')
                        ->options([
                            1 => 'Kelas 1',
                            2 => 'Kelas 2',
                            3 => 'Kelas 3',
                            4 => 'Kelas 4',
                            5 => 'Kelas 5',
                            6 => 'Kelas 6',
                        ])
                        ->required(),

                    Forms\Components\Select::make('semester')
                        ->label('Semester')
                        ->options([
                            1 => 'Semester 1',
                            2 => 'Semester 2',
                        ])
                        ->required(),

                    Forms\Components\FileUpload::make('attachment')
                        ->label('File Excel / CSV')
                        ->helperText('Format kolom Excel cukup 1 header bernama "deskripsi". Kode TP akan digenerate otomatis.')
                        ->disk('local')
                        ->directory('imports')
                        ->maxSize(10240) // Maksimal 10MB
                        ->rules(['required', 'file', 'mimes:xlsx,xls,csv,txt'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Dapatkan absolute path file secara presisi dari disk local Laravel
                    $filePath = Storage::disk('local')->path($data['attachment']);

                    Excel::import(
                        new LearningObjectivesImport(
                            (int) $data['subject_id'],
                            $data['phase'],
                            (int) $data['level'],
                            (int) $data['semester']
                        ),
                        $filePath
                    );

                    // Bersihkan file sementara setelah berhasil di-import
                    Storage::disk('local')->delete($data['attachment']);

                    Notification::make()
                        ->title('Import Selesai')
                        ->body('Data Tujuan Pembelajaran berhasil disimpan dan Kode TP digenerate otomatis.')
                        ->success()
                        ->send();
                }),

            // --- TOMBOL EXPORT ---
            Actions\Action::make('exportExcel')
                ->label('Export TP')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->action(function () {
                    return Excel::download(new LearningObjectivesExport, 'Tujuan_Pembelajaran_' . date('Y-m-d') . '.xlsx');
                }),
        ];
    }
}