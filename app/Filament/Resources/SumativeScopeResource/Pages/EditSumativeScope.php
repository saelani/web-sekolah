<?php

namespace App\Filament\Resources\SumativeScopeResource\Pages;

use App\Filament\Resources\SumativeScopeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSumativeScope extends EditRecord
{
    protected static string $resource = SumativeScopeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
