<?php

namespace App\Filament\Resources\GradeP5ScoreResource\Pages;

use App\Filament\Resources\GradeP5ScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGradeP5Score extends EditRecord
{
    protected static string $resource = GradeP5ScoreResource::class;

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
