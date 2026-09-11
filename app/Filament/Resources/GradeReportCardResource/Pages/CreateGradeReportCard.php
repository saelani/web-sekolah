<?php

namespace App\Filament\Resources\GradeReportCardResource\Pages;

use App\Filament\Resources\GradeReportCardResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGradeReportCard extends CreateRecord
{
    protected static string $resource = GradeReportCardResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
