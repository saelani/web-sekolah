<?php

namespace App\Filament\Resources\GradeP5ScoreResource\Pages;

use App\Filament\Resources\GradeP5ScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGradeP5Score extends CreateRecord
{
    protected static string $resource = GradeP5ScoreResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
