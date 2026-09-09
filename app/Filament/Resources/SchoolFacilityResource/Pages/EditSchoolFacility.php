<?php

namespace App\Filament\Resources\SchoolFacilityResource\Pages;

use App\Filament\Resources\SchoolFacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolFacility extends EditRecord
{
    protected static string $resource = SchoolFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
