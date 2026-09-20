<?php

namespace App\Filament\Resources\GradeSumatifResource\Pages;

use App\Filament\Resources\GradeSumatifResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGradeSumatif extends CreateRecord
{
    protected static string $resource = GradeSumatifResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
