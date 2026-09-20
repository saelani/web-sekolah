<?php

namespace App\Filament\Resources\CbtExamResource\Pages;

use App\Filament\Resources\CbtExamResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCbtExam extends CreateRecord
{
    protected static string $resource = CbtExamResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
