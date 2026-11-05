<?php

namespace App\Filament\Resources\Administration\PrayerResource\Pages;

use App\Filament\Resources\Administration\PrayerResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrayer extends CreateRecord
{
    protected static string $resource = PrayerResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
