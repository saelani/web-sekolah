<?php

namespace App\Filament\Resources\TeacherSubjectClassesResource\Pages;

use App\Filament\Resources\TeacherSubjectClassesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacherSubjectClasses extends CreateRecord
{
    protected static string $resource = TeacherSubjectClassesResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
