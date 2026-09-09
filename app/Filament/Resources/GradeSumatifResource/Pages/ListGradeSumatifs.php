<?php

namespace App\Filament\Resources\GradeSumatifResource\Pages;

use App\Filament\Resources\GradeSumatifResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGradeSumatifs extends ListRecords
{
    protected static string $resource = GradeSumatifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
