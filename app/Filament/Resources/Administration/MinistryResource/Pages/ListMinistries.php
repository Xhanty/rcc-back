<?php

namespace App\Filament\Resources\Administration\MinistryResource\Pages;

use App\Filament\Resources\Administration\MinistryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMinistries extends ListRecords
{
    protected static string $resource = MinistryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo Ministerio')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary'),
        ];
    }
}
