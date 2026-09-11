<?php

namespace App\Filament\Resources\StudentSavingResource\Pages;

use App\Filament\Resources\StudentSavingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentSaving extends CreateRecord
{
    protected static string $resource = StudentSavingResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
