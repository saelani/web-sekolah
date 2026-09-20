<?php

namespace App\Filament\Resources\TeacherSubjectClassesResource\Pages;

use App\Filament\Resources\TeacherSubjectClassesResource;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTeacherSubjectClasses extends ListRecords
{
    protected static string $resource = TeacherSubjectClassesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pengajar'),

            Actions\Action::make('bulkAssign')
                ->label('Plotting Guru ke Banyak Mapel & Kelas')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('academic_year_id')
                        ->label('Tahun Ajaran')
                        ->relationship('academicYear', 'year')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('teacher_id')
                        ->label('Guru Pengajar')
                        ->relationship('teacher', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    // Menggunakan options() murni agar bisa memilih banyak mata pelajaran
                    Forms\Components\Select::make('subject_ids')
                        ->label('Pilih Mata Pelajaran (Bisa Lebih dari 1)')
                        ->options(Subject::query()->pluck('name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required(),

                    // Menggunakan options() murni agar bisa memilih banyak kelas
                    Forms\Components\Select::make('class_ids')
                        ->label('Pilih Kelas (Bisa Lebih dari 1)')
                        ->options(ClassRoom::query()->pluck('name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $inserted = 0;
                    $subjectIds = $data['subject_ids'] ?? [];
                    $classIds = $data['class_ids'] ?? [];

                    // Loop silang: Setiap Mata Pelajaran x Setiap Kelas
                    foreach ($subjectIds as $subjectId) {
                        foreach ($classIds as $classId) {
                            TeacherSubjectClass::firstOrCreate([
                                'academic_year_id' => $data['academic_year_id'],
                                'teacher_id' => $data['teacher_id'],
                                'subject_id' => $subjectId,
                                'class_id' => $classId,
                            ]);
                            $inserted++;
                        }
                    }

                    Notification::make()
                        ->title('Plotting Pengajar Selesai')
                        ->body("Berhasil mendaftarkan kombinasi pengajar ke {$inserted} slot kelas & mata pelajaran.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
