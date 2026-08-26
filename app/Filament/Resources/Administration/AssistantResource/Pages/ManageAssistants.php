<?php

namespace App\Filament\Resources\Administration\AssistantResource\Pages;

use App\Filament\Resources\Administration\AssistantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAssistants extends ManageRecords
{
    protected static string $resource = AssistantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Asistente')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->modalHeading('Crear Asistente')
                ->modalSubmitActionLabel('Crear')
                ->modalCancelActionLabel('Cancelar')
                ->modalWidth('md')
                ->createAnother(false)
                ->slideOver(),
        ];
    }
}
