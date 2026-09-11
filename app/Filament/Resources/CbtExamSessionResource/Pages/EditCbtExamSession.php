<?php

namespace App\Filament\Resources\CbtExamSessionResource\Pages;

use App\Filament\Resources\CbtExamSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCbtExamSession extends EditRecord
{
    protected static string $resource = CbtExamSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
