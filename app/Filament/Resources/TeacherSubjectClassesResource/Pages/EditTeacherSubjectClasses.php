<?php

namespace App\Filament\Resources\TeacherSubjectClassesResource\Pages;

use App\Filament\Resources\TeacherSubjectClassesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherSubjectClass extends EditRecord
{
    protected static string $resource = TeacherSubjectClassesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
