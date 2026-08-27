<?php

namespace App\Filament\Resources\Administration\ContactResource\Pages;

use App\Filament\Resources\Administration\ContactResource;
use Filament\Resources\Pages\ManageRecords;

class ManageContacts extends ManageRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
