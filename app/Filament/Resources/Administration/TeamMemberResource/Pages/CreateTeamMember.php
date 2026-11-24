<?php

namespace App\Filament\Resources\Administration\TeamMemberResource\Pages;

use App\Filament\Resources\Administration\TeamMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeamMember extends CreateRecord
{
    protected static string $resource = TeamMemberResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
