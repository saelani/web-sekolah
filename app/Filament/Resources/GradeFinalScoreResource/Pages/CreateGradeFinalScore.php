<?php

namespace App\Filament\Resources\GradeFinalScoreResource\Pages;

use App\Filament\Resources\GradeFinalScoreResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGradeFinalScore extends CreateRecord
{
    protected static string $resource = GradeFinalScoreResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
