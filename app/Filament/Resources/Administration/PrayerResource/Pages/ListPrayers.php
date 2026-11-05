<?php

namespace App\Filament\Resources\Administration\PrayerResource\Pages;

use App\Filament\Resources\Administration\PrayerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPrayers extends ListRecords
{
    protected static string $resource = PrayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva Oración o Novena')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary'),
        ];
    }
}
