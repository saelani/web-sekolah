<?php

namespace App\Filament\Resources\GradeReportCardResource\Pages;

use App\Filament\Resources\GradeReportCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGradeReportCards extends ListRecords
{
    protected static string $resource = GradeReportCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
