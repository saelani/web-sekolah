<?php

namespace App\Filament\Resources\StudentAttendanceResource\Pages;

use App\Filament\Resources\StudentAttendanceResource;
use App\Models\ClassRoom;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListStudentAttendances extends ListRecords
{
    protected static string $resource = StudentAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('batchAttendance')
                ->label('Input Presensi Massal')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->url(StudentAttendanceResource::getUrl('batch-attendance')),

            // TOMBOL CETAK LAPORAN PREVIEW (DIRECT TAB BARU)
            Actions\Action::make('printReport')
                ->label('Laporan Presensi')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    Forms\Components\Select::make('class_id')
                        ->label('Pilih Kelas')
                        ->options(function () {
                            $user = Auth::user();
                            $query = ClassRoom::query();
                            $userRole = $user->role ?? $user->role_type ?? '';

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

                    Forms\Components\DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->default(now()->startOfMonth())
                        ->required(),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->default(now()->endOfMonth())
                        ->required(),
                ])
                ->modalHeading('Filter Laporan Presensi')
                ->modalSubmitActionLabel('Buka PDF Laporan')
                ->action(function (array $data) {
                    $url = route('student-attendance.pdf', [
                        'class_id'   => $data['class_id'],
                        'start_date' => $data['start_date'],
                        'end_date'   => $data['end_date'],
                    ]);

                    // Trik aman memicu pencetakan/preview di tab baru tanpa terblokir popup blocker
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

            Actions\CreateAction::make(),
        ];
    }
}