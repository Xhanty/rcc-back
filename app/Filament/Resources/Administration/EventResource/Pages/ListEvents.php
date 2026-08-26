<?php

namespace App\Filament\Resources\Administration\EventResource\Pages;

use App\Filament\Resources\Administration\EventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Evento')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary'),
        ];
    }
}
