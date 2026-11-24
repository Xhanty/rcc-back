<?php

namespace App\Filament\Resources\Administration\SocialMediaItemResource\Pages;

use App\Filament\Resources\Administration\SocialMediaItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSocialMediaItem extends CreateRecord
{
    protected static string $resource = SocialMediaItemResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
