<?php

namespace App\Filament\Resources\Administration\PrayerResource\Pages;

use App\Filament\Resources\Administration\PrayerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPrayer extends EditRecord
{
    protected static string $resource = PrayerResource::class;

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
