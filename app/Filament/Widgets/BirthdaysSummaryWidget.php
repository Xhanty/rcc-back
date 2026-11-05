<?php

namespace App\Filament\Widgets;

use App\Models\Assistant;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class BirthdaysSummaryWidget extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'lg' => 1,
        'xl' => 1,
    ];

    protected string $view = 'filament.widgets.birthdays-summary-widget';

    public static function canView(): bool
    {
        return Auth::user()?->hasModuleAccess('assistants') ?? false;
    }

    public function getBirthdayStats(): array
    {
        $currentMonth = now()->month;
        $currentDay = now()->day;
        $startOfWeekDay = now()->startOfWeek()->day;
        $endOfWeekDay = now()->endOfWeek()->day;

        $monthAssistants = Assistant::query()
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $currentMonth)
            ->get();

        $todayCount = 0;
        $thisWeekCount = 0;
        $restOfMonthCount = 0;

        foreach ($monthAssistants as $assistant) {
            $day = (int) $assistant->birth_date->day;

            if ($day === $currentDay) {
                $todayCount++;
            } elseif ($day >= $startOfWeekDay && $day <= $endOfWeekDay && $day > $currentDay) {
                $thisWeekCount++;
            } elseif ($day > $endOfWeekDay || ($day > $currentDay && ($day < $startOfWeekDay || $day > $endOfWeekDay))) {
                $restOfMonthCount++;
            }
        }

        return [
            'monthName' => ucfirst(now()->translatedFormat('F Y')),
            'total' => $monthAssistants->count(),
            'today' => $todayCount,
            'thisWeek' => $thisWeekCount,
            'restOfMonth' => $restOfMonthCount,
        ];
    }
}
