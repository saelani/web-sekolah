<?php

namespace App\Filament\Resources\SchoolAchievementResource\Pages;

use App\Filament\Resources\SchoolAchievementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchoolAchievement extends CreateRecord
{
    protected static string $resource = SchoolAchievementResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
