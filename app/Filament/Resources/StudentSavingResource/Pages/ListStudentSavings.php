<?php
namespace App\Filament\Resources\StudentSavingResource\Pages;

use App\Filament\Resources\StudentSavingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentSavings extends ListRecords
{
    protected static string $resource = StudentSavingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('batchInput')
                ->label('Input Tabungan Massal')
                ->icon('heroicon-o-pencil-square')
                ->color('success')
                ->url(StudentSavingResource::getUrl('batch-saving')),
            Actions\CreateAction::make(),
        ];
    }
}