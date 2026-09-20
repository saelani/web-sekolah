<?php

namespace App\Filament\Resources\LearningObjectiveResource\Pages;

use App\Exports\LearningObjectivesExport;
use App\Filament\Resources\LearningObjectiveResource;
use App\Imports\LearningObjectivesImport;
use App\Models\ClassRoom;
use App\Models\SumativeScope;
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
                    // 1. Pilih Mata Pelajaran Terlebih Dahulu
                    Forms\Components\Select::make('subject_id')
                        ->label('Mata Pelajaran')
                        ->relationship('subject', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('sumative_scope_id', null)) // Reset bab jika mapel berubah
                        ->required(),

                    // 2. Pilih Bab / Lingkup Materi (Difilter berdasarkan Mata Pelajaran)
                    Forms\Components\Select::make('sumative_scope_id')
                        ->label('Bab / Lingkup Materi')
                        ->options(fn (Forms\Get $get) => SumativeScope::query()
                            ->when($get('subject_id'), fn ($q, $subjectId) => $q->where('subject_id', $subjectId))
                            ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            if ($state) {
                                $scope = SumativeScope::find($state);
                                if ($scope) {
                                    $set('phase', $scope->phase);
                                    $set('semester', $scope->semester);
                                }
                            }
                        })
                        ->required(),

                    // 3. Tingkat Kelas diambil dari model ClassRoom (acad_classes)
                    Forms\Components\Select::make('level')
                        ->label('Tingkat Kelas')
                        ->options(
                            ClassRoom::query()
                                ->select('level')
                                ->distinct()
                                ->orderBy('level')
                                ->pluck('level', 'level')
                                ->map(fn ($level) => "Kelas {$level}")
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            if ($state) {
                                if (in_array($state, [1, 2])) {
                                    $set('phase', 'A');
                                } elseif (in_array($state, [3, 4])) {
                                    $set('phase', 'B');
                                } elseif (in_array($state, [5, 6])) {
                                    $set('phase', 'C');
                                }
                            }
                        })
                        ->required(),

                    // 4. Fase (Otomatis / Bisa diubah manual)
                    Forms\Components\Select::make('phase')
                        ->label('Fase')
                        ->options([
                            'A' => 'Fase A (Kelas 1-2)',
                            'B' => 'Fase B (Kelas 3-4)',
                            'C' => 'Fase C (Kelas 5-6)',
                        ])
                        ->required(),

                    // 5. Semester
                    Forms\Components\Select::make('semester')
                        ->label('Semester')
                        ->options([
                            1 => 'Semester 1',
                            2 => 'Semester 2',
                        ])
                        ->required(),

                    // 6. Upload File Excel/CSV
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
                    $filePath = Storage::disk('local')->path($data['attachment']);

                    Excel::import(
                        new LearningObjectivesImport(
                            (int) $data['subject_id'],
                            (int) $data['sumative_scope_id'],
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
                        ->body('Data Tujuan Pembelajaran berhasil di-import dan Kode TP digenerate otomatis.')
                        ->success()
                        ->send();
                }),

            // --- TOMBOL EXPORT ---
            Actions\Action::make('exportExcel')
                ->label('Export TP')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->action(function () {
                    return Excel::download(new LearningObjectivesExport, 'Tujuan_Pembelajaran_'.date('Y-m-d').'.xlsx');
                }),
        ];
    }
}
