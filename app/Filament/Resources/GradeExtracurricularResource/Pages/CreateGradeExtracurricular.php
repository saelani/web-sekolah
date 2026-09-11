<?php

namespace App\Filament\Resources\GradeExtracurricularResource\Pages;

use App\Filament\Resources\GradeExtracurricularResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGradeExtracurricular extends CreateRecord
{
    protected static string $resource = GradeExtracurricularResource::class;
    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
