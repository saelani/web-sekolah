<?php

namespace App\Filament\Resources\CbtExamSessionResource\Pages;

use App\Filament\Resources\CbtExamSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCbtExamSession extends CreateRecord
{
    protected static string $resource = CbtExamSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return $resource = static::getResource()::getUrl('index');
    }
}
