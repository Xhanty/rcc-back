<?php

namespace App\Filament\Resources\Administration\MinistryResource\Pages;

use App\Filament\Resources\Administration\MinistryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMinistry extends EditRecord
{
    protected static string $resource = MinistryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar')
                ->color('danger'),
            ForceDeleteAction::make()
                ->label('Eliminar permanentemente'),
            RestoreAction::make()
                ->label('Restaurar')
                ->color('success'),
        ];
    }
}
