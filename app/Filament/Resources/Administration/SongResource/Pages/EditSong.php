<?php

namespace App\Filament\Resources\Administration\SongResource\Pages;

use App\Filament\Resources\Administration\SongResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSong extends EditRecord
{
    protected static string $resource = SongResource::class;

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
