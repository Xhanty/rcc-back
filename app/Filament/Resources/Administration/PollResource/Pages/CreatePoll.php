<?php

namespace App\Filament\Resources\Administration\PollResource\Pages;

use App\Filament\Resources\Administration\PollResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePoll extends CreateRecord
{
    protected static string $resource = PollResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
