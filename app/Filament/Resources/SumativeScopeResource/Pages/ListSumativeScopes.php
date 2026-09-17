<?php

namespace App\Filament\Resources\SumativeScopeResource\Pages;

use App\Filament\Resources\SumativeScopeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSumativeScopes extends ListRecords
{
    protected static string $resource = SumativeScopeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
