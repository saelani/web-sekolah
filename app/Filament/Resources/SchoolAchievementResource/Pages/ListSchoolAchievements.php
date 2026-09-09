<?php

namespace App\Filament\Resources\SchoolAchievementResource\Pages;

use App\Filament\Resources\SchoolAchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolAchievements extends ListRecords
{
    protected static string $resource = SchoolAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
