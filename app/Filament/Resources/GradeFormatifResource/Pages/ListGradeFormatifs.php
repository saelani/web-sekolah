<?php

namespace App\Filament\Resources\GradeFormatifResource\Pages;

use App\Filament\Resources\GradeFormatifResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGradeFormatifs extends ListRecords
{
    protected static string $resource = GradeFormatifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('batchInput')
                ->label('Input Massal 1 Kelas')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->url(fn (): string => GradeFormatifResource::getUrl('batch')),
            Actions\CreateAction::make(),
        ];
    }
}
