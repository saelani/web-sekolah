<?php

namespace App\Filament\Resources\GradeReportCardResource\Pages;

use App\Filament\Resources\GradeReportCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGradeReportCard extends EditRecord
{
    protected static string $resource = GradeReportCardResource::class;

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
