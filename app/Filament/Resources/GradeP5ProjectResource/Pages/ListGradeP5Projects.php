<?php

namespace App\Filament\Resources\GradeP5ProjectResource\Pages;

use App\Filament\Resources\GradeP5ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGradeP5Projects extends ListRecords
{
    protected static string $resource = GradeP5ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
