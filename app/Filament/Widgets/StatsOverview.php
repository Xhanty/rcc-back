<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Assistant;
use App\Models\Event;
use App\Models\Contact;
use App\Models\Petition;
use App\Filament\Resources\Administration\ContactResource;
use App\Filament\Resources\Administration\PetitionResource;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $stats = [];
        $user = Auth::user();

        if ($user?->hasModuleAccess('users')) {
            $stats[] = Stat::make('Usuarios Registrados', User::count())
                ->description('Cuentas de acceso')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary');
        }

        if ($user?->hasModuleAccess('assistants')) {
            $stats[] = Stat::make('Asistentes Registrados', Assistant::count())
                ->description('En eventos')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success');
        }

        if ($user?->hasModuleAccess('events')) {
            $stats[] = Stat::make('Eventos Registrados', Event::count())
                ->description('En plataforma')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info');
        }

        if ($user?->hasModuleAccess('contacts')) {
            $pendingContacts = Contact::where('status', 'pending')->count();
            $stat = Stat::make('Mensajes Contacto', $pendingContacts)
                ->description('Ver bandeja →')
                ->descriptionIcon('heroicon-m-envelope')
                ->color($pendingContacts === 0 ? 'success' : 'warning');

            if (class_exists(ContactResource::class)) {
                $stat->url(ContactResource::getUrl('index'));
            }

            $stats[] = $stat;
        }

        if ($user?->hasModuleAccess('petitions')) {
            $pendingPetitions = Petition::where('status', 'pending')->count();
            $stat = Stat::make('Peticiones', $pendingPetitions)
                ->description('Ver bandeja →')
                ->descriptionIcon('heroicon-m-heart')
                ->color($pendingPetitions === 0 ? 'success' : 'warning');

            if (class_exists(PetitionResource::class)) {
                $stat->url(PetitionResource::getUrl('index'));
            }

            $stats[] = $stat;
        }

        return $stats;
    }
}
