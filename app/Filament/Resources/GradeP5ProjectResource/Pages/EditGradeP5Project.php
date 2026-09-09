<?php

namespace App\Filament\Resources\GradeP5ProjectResource\Pages;

use App\Filament\Resources\GradeP5ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGradeP5Project extends EditRecord
{
    protected static string $resource = GradeP5ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
