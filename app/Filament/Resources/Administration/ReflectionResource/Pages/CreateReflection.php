<?php

namespace App\Filament\Resources\Administration\ReflectionResource\Pages;

use App\Filament\Resources\Administration\ReflectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReflection extends CreateRecord
{
    protected static string $resource = ReflectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
