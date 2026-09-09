<?php

namespace App\Filament\Resources\GradeP5SubelementResource\Pages;

use App\Filament\Resources\GradeP5SubelementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGradeP5Subelement extends EditRecord
{
    protected static string $resource = GradeP5SubelementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
