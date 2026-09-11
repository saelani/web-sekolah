<?php

namespace App\Filament\Resources\GradeFormatifResource\Pages;

use App\Filament\Resources\GradeFormatifResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGradeFormatif extends CreateRecord
{
    protected static string $resource = GradeFormatifResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
