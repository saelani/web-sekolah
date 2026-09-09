<?php

namespace App\Filament\Resources\AcademicCalendarResource\Pages;

use App\Filament\Resources\AcademicCalendarResource;
use App\Filament\Widgets\AcademicCalendarWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAcademicCalendars extends ListRecords
{
    protected static string $resource = AcademicCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AcademicCalendarWidget::class,
        ];
    }
}