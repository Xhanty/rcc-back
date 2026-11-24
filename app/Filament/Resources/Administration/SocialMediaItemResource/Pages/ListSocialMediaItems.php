<?php

namespace App\Filament\Resources\Administration\SocialMediaItemResource\Pages;

use App\Filament\Resources\Administration\SocialMediaItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSocialMediaItems extends ListRecords
{
    protected static string $resource = SocialMediaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Agregar Red o Contenido')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary'),
        ];
    }
}
