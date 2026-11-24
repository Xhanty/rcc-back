<?php

namespace App\Filament\Resources\Administration\PollResource\Pages;

use App\Filament\Resources\Administration\PollResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPolls extends ListRecords
{
    protected static string $resource = PollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva Encuesta')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary'),
        ];
    }
}
