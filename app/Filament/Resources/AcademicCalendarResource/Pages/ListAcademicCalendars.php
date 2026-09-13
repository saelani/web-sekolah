<?php

namespace App\Filament\Resources\AcademicCalendarResource\Pages;

use App\Filament\Resources\AcademicCalendarResource;
use App\Filament\Widgets\AcademicCalendarWidget;
use App\Models\ClassRoom;
use App\Models\Subject;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListAcademicCalendars extends ListRecords
{
    protected static string $resource = AcademicCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Tombol Cetak & Preview Laporan Pemetaan Semester (Landscape)
            Actions\Action::make('printMonthlyReport')
                ->label('Laporan Pemetaan Semester')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    Forms\Components\Select::make('class_room_id')
                        ->label('Kelas')
                        ->options(function () {
                            $user = Auth::user();
                            $query = ClassRoom::query();
                            $userRole = $user->role ?? $user->role_type ?? '';

                            // Otomatis filter jika user yang login adalah guru/wali kelas
                            if (!in_array($userRole, ['admin', 'headmaster', 'super_admin']) && !($user->is_admin ?? false)) {
                                $teacherId = $user->teacher?->id ?? $user->teacher_id;
                                if ($teacherId) {
                                    $query->where('teacher_id', $teacherId);
                                } else {
                                    return [];
                                }
                            }

                            return $query->pluck('name', 'id');
                        })
                        ->exists('acad_classes', 'id')
                        ->required(),

                    Forms\Components\Select::make('subject_id')
                        ->label('Mata Pelajaran (Opsional)')
                        ->options(Subject::pluck('name', 'id'))
                        ->nullable(),

                    Forms\Components\Select::make('semester')
                        ->label('Semester')
                        ->options([
                            '1' => 'Semester 1 (Ganjil - Jul s/d Des)',
                            '2' => 'Semester 2 (Genap - Jan s/d Jun)',
                        ])
                        ->default('1')
                        ->required(),

                    Forms\Components\TextInput::make('year')
                        ->label('Tahun')
                        ->numeric()
                        ->default(date('Y'))
                        ->required(),
                ])
                ->modalHeading('Filter Laporan Pemetaan Pembelajaran Semester')
                ->modalSubmitActionLabel('Buka PDF Laporan')
                ->action(function (array $data) {
                    $url = route('academic-calendar.pdf', [
                        'class_room_id' => $data['class_room_id'],
                        'subject_id'    => $data['subject_id'] ?? null,
                        'semester'      => $data['semester'],
                        'year'          => $data['year'],
                    ]);

                    // Menggunakan DOM link sintetis agar aman dari popup blocker browser
                    $this->js("
                        const a = document.createElement('a');
                        a.href = '{$url}';
                        a.target = '_blank';
                        a.rel = 'noopener noreferrer';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    ");
                }),

            // 2. Tombol Tambah Agenda Bawaan Kamu
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AcademicCalendarWidget::class,
        ];
    }
}