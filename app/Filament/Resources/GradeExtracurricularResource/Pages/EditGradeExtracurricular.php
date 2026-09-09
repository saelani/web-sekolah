<?php

namespace App\Filament\Resources\GradeExtracurricularResource\Pages;

use App\Filament\Resources\GradeExtracurricularResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGradeExtracurricular extends EditRecord
{
    protected static string $resource = GradeExtracurricularResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
