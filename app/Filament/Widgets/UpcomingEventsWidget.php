<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class UpcomingEventsWidget extends Widget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'lg' => 2,
        'xl' => 2,
    ];

    protected string $view = 'filament.widgets.upcoming-events-widget';

    public static function canView(): bool
    {
        return Auth::user()?->hasModuleAccess('events') ?? false;
    }

    public function getUpcomingEvents()
    {
        return Event::query()
            ->whereDate('start_datetime', '>=', now()->toDateString())
            ->orderBy('start_datetime', 'asc')
            ->limit(4)
            ->get();
    }
}
