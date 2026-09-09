<?php

namespace App\Filament\Resources\SchoolFacilityResource\Pages;

use App\Filament\Resources\SchoolFacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolFacilities extends ListRecords
{
    protected static string $resource = SchoolFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
