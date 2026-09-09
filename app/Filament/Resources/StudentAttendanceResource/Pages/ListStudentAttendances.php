<?php
namespace App\Filament\Resources\StudentAttendanceResource\Pages;

use App\Filament\Resources\StudentAttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
            Actions\CreateAction::make(),
        ];
    }
}