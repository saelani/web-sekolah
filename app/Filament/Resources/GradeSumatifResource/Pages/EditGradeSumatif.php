<?php

namespace App\Filament\Resources\GradeSumatifResource\Pages;

use App\Filament\Resources\GradeSumatifResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGradeSumatif extends EditRecord
{
    protected static string $resource = GradeSumatifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
