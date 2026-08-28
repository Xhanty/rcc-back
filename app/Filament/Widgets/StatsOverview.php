<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Assistant;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = [];
        $user = Auth::user();

        if ($user?->hasModuleAccess('users')) {
            $stats[] = Stat::make('Usuarios Registrados', User::count())
                ->description('Cuentas de acceso al panel')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary');
        }

        if ($user?->hasModuleAccess('assistants')) {
            $stats[] = Stat::make('Asistentes Registrados', Assistant::count())
                ->description('Asistentes registrados en eventos')
                ->descriptionIcon('heroicon-m-identification')
                ->color('success');
        }

        if ($user?->hasModuleAccess('events')) {
            $stats[] = Stat::make('Eventos Registrados', Event::count())
                ->description('Eventos creados en la plataforma')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info');
        }

        return $stats;
    }
}
