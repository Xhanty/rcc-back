<?php

namespace App\Filament\Resources\Configuration\UserResource\Pages;

use App\Filament\Resources\Configuration\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Usuario')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->modalHeading('Crear Usuario')
                ->modalSubmitActionLabel('Crear')
                ->modalCancelActionLabel('Cancelar')
                ->modalWidth('md')
                ->createAnother(false)
                ->slideOver(),
        ];
    }
}
