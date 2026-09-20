<?php

namespace App\Filament\Resources\GradeP5ScoreResource\Pages;

use App\Filament\Resources\GradeP5ScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGradeP5Scores extends ListRecords
{
    protected static string $resource = GradeP5ScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('batch_input')
                ->label('Input Batch P5')
                ->color('success')
                ->icon('heroicon-o-table-cells')
                ->url(fn (): string => GradeP5ScoreResource::getUrl('batch')),

            Actions\CreateAction::make(),
        ];
    }
}
