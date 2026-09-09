<?php

namespace App\Filament\Resources\CbtExamResource\Pages;

use App\Filament\Resources\CbtExamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCbtExams extends ListRecords
{
    protected static string $resource = CbtExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
        ];
    }
}
