<?php

namespace App\Filament\Resources\GradeFinalScoreResource\Pages;

use App\Filament\Resources\GradeFinalScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGradeFinalScore extends EditRecord
{
    protected static string $resource = GradeFinalScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
