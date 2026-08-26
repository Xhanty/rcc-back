<?php

namespace App\Filament\Resources\Configuration\EventTypeResource\Pages;

use App\Filament\Resources\Configuration\EventTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageEventTypes extends ManageRecords
{
    protected static string $resource = EventTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Tipo de Evento')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary')
                ->modalHeading('Crear Tipo de Evento')
                ->modalSubmitActionLabel('Crear')
                ->modalCancelActionLabel('Cancelar')
                ->modalWidth('md')
                ->createAnother(false)
                ->slideOver(),
        ];
    }
}
