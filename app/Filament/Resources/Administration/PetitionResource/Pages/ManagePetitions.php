<?php

namespace App\Filament\Resources\Administration\PetitionResource\Pages;

use App\Filament\Resources\Administration\PetitionResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePetitions extends ManageRecords
{
    protected static string $resource = PetitionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
