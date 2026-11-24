<?php

namespace App\Filament\Resources\Administration\ReflectionResource\Pages;

use App\Filament\Resources\Administration\ReflectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReflections extends ListRecords
{
    protected static string $resource = ReflectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
