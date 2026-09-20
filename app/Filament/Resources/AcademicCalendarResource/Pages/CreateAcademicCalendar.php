<?php

namespace App\Filament\Resources\AcademicCalendarResource\Pages;

use App\Filament\Resources\AcademicCalendarResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAcademicCalendar extends CreateRecord
{
    protected static string $resource = AcademicCalendarResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
