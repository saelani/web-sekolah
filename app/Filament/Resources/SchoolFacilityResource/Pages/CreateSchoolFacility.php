<?php

namespace App\Filament\Resources\SchoolFacilityResource\Pages;

use App\Filament\Resources\SchoolFacilityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchoolFacility extends CreateRecord
{
    protected static string $resource = SchoolFacilityResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
