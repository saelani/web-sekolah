<?php

namespace App\Filament\Resources\CbtExamSessionResource\Pages;

use App\Filament\Resources\CbtExamSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCbtExamSessions extends ListRecords
{
    protected static string $resource = CbtExamSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
