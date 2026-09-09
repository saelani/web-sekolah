<?php

namespace App\Filament\Resources\CbtExamResource\Pages;

use App\Filament\Resources\CbtExamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCbtExam extends EditRecord
{
    protected static string $resource = CbtExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
