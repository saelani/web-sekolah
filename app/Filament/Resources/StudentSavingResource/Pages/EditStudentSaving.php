<?php

namespace App\Filament\Resources\StudentSavingResource\Pages;

use App\Filament\Resources\StudentSavingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentSaving extends EditRecord
{
    protected static string $resource = StudentSavingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
