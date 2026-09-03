<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Width;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class EventAttendeesChart extends ApexChartWidget
{
    use HasFiltersSchema;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user?->hasModuleAccess('events') || $user?->hasModuleAccess('attendance') ?: false;
    }

    protected static ?int $sort = 2;

    protected static ?string $chartId = 'eventAttendeesChart';

    protected static ?string $heading = 'Asistencia por Evento';

    protected static ?string $subheading = 'Cantidad de asistentes registrados';

    protected int|string|array $columnSpan = 1;

    protected static Width|string $filterFormWidth = Width::Medium;

    public function filtersSchema(Schema $schema): Schema
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $years = Event::query()
            ->selectRaw('YEAR(start_datetime) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        if (empty($years)) {
            $years = [$currentYear => (string)$currentYear];
        }

        $months = Event::query()
            ->selectRaw('MONTH(start_datetime) as month')
            ->distinct()
            ->orderBy('month', 'asc')
            ->pluck('month')
            ->mapWithKeys(fn ($month) => [
                $month => match ((int)$month) {
                    1 => 'Enero',
                    2 => 'Febrero',
                    3 => 'Marzo',
                    4 => 'Abril',
                    5 => 'Mayo',
                    6 => 'Junio',
                    7 => 'Julio',
                    8 => 'Agosto',
                    9 => 'Septiembre',
                    10 => 'Octubre',
                    11 => 'Noviembre',
                    12 => 'Diciembre',
                    default => (string)$month,
                }
            ])
            ->toArray();

        if (empty($months)) {
            $months = [$currentMonth => 'Este Mes'];
        }

        return $schema->components([
            Select::make('year')
                ->label('Año')
                ->options($years)
                ->default($currentYear)
                ->selectablePlaceholder(false)
                ->searchable()
                ->preload(),

            Select::make('month')
                ->label('Mes')
                ->options($months)
                ->default($currentMonth)
                ->selectablePlaceholder(false)
                ->searchable()
                ->preload(),
        ])
        ->columns(2);
    }

    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->updateOptions();
    }

    protected function getOptions(): array
    {
        $year = $this->filters['year'] ?? now()->year;
        $month = $this->filters['month'] ?? now()->month;

        // Query the events for the selected year and month with their attendance count
        $events = Event::query()
            ->whereIn('status', ['published', 'completed', 'not_published'])
            ->whereYear('start_datetime', $year)
            ->whereMonth('start_datetime', $month)
            ->withCount('attendances')
            ->orderBy('start_datetime', 'DESC')
            ->get()
            ->reverse();

        $categories = $events->pluck('title')->toArray();
        $data = $events->pluck('attendances_count')->toArray();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 380,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'borderRadius' => 4,
                    'barHeight' => '60%',
                ],
            ],
            'colors' => ['#f59e0b'], // Amber primary theme color
            'series' => [
                [
                    'name' => 'Asistentes',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'style' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'tooltip' => [
                'theme' => 'dark',
            ],
        ];
    }
}
