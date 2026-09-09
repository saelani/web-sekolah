<?php

namespace App\Filament\Resources\GradeFormatifResource\Pages;

use App\Filament\Resources\GradeFormatifResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGradeFormatif extends EditRecord
{
    protected static string $resource = GradeFormatifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
