<?php

namespace App\Filament\Resources\Administration\MinistryResource\Pages;

use App\Filament\Resources\Administration\MinistryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMinistry extends CreateRecord
{
    protected static string $resource = MinistryResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
