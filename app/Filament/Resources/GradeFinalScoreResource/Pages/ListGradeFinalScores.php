<?php

namespace App\Filament\Resources\GradeFinalScoreResource\Pages;

use App\Filament\Resources\GradeFinalScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGradeFinalScores extends ListRecords
{
    protected static string $resource = GradeFinalScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
