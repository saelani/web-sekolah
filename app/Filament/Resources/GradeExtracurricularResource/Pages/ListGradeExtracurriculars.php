<?php

namespace App\Filament\Resources\GradeExtracurricularResource\Pages;

use App\Filament\Resources\GradeExtracurricularResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGradeExtracurriculars extends ListRecords
{
    protected static string $resource = GradeExtracurricularResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
