<?php

namespace App\Filament\Resources\Administration\SongResource\Pages;

use App\Filament\Resources\Administration\SongResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSongs extends ListRecords
{
    protected static string $resource = SongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva Canción')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary'),
        ];
    }
}
