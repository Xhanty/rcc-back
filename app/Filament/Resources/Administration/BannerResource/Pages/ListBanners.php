<?php

namespace App\Filament\Resources\Administration\BannerResource\Pages;

use App\Filament\Resources\Administration\BannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListBanners extends ListRecords
{
    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo Banner')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary'),
        ];
    }
}
