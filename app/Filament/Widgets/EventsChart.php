<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\EventType;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class EventsChart extends ApexChartWidget
{
    use HasFiltersSchema;

    protected static ?int $sort = 2;

    protected static ?string $chartId = 'eventsChart';

    protected static ?string $heading = 'Estadísticas de Eventos';

    protected static ?string $subheading = 'Cantidad de eventos registrados por modalidad y mes';

    protected int|string|array $columnSpan = 'full';

    protected static Width|string $filterFormWidth = Width::ThreeExtraLarge;

    public function filtersSchema(Schema $schema): Schema
    {
        $currentYear = now()->year;
        
        $years = Event::query()
            ->selectRaw('YEAR(start_datetime) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        if (empty($years)) {
            $years = [$currentYear => (string)$currentYear];
        }

        $defaultYear = array_key_first($years) ?? $currentYear;

        return $schema->components([
            Select::make('year')
                ->label('Año')
                ->options($years)
                ->default($defaultYear)
                ->selectablePlaceholder(false)
                ->searchable()
                ->preload(),

            Select::make('event_type_id')
                ->label('Tipo de Evento')
                ->options(fn () => EventType::pluck('name', 'id')->toArray())
                ->placeholder('Todos')
                ->searchable()
                ->preload(),

            Select::make('modality')
                ->label('Modalidad')
                ->options([
                    'in_person' => 'Presencial',
                    'virtual' => 'Virtual',
                ])
                ->placeholder('Todas')
                ->searchable()
                ->preload(),

            Select::make('status')
                ->label('Estado')
                ->options([
                    'draft' => 'Borrador',
                    'published' => 'Publicado',
                    'not_published' => 'No Publicado',
                    'cancelled' => 'Cancelado',
                    'completed' => 'Completado',
                ])
                ->placeholder('Todos')
                ->searchable()
                ->preload(),
        ])
        ->columns(4);
    }

    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->updateOptions();
    }

    protected function getOptions(): array
    {
        $year = $this->filters['year'] ?? now()->year;
        $eventTypeId = $this->filters['event_type_id'] ?? null;
        $modality = $this->filters['modality'] ?? null;
        $status = $this->filters['status'] ?? null;

        $query = Event::query()
            ->whereYear('start_datetime', $year);

        if ($eventTypeId) {
            $query->where('event_type_id', $eventTypeId);
        }

        if ($modality) {
            $query->where('modality', $modality);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $events = $query->select(['id', 'title', 'modality', 'start_datetime', 'event_type_id'])
            ->with('eventType:id,name')
            ->get();

        $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $inPersonCounts = array_fill(0, 12, 0);
        $virtualCounts = array_fill(0, 12, 0);
        $inPersonTitles = array_fill(0, 12, []);
        $virtualTitles = array_fill(0, 12, []);

        foreach ($events as $event) {
            $monthIndex = (int)$event->start_datetime->month - 1;
            $eventTypeName = $event->eventType?->name ?? 'Sin Tipo';
            if ($event->modality === 'in_person') {
                $inPersonCounts[$monthIndex]++;
                $inPersonTitles[$monthIndex][] = [
                    'title' => $event->title,
                    'type' => $eventTypeName,
                ];
            } elseif ($event->modality === 'virtual') {
                $virtualCounts[$monthIndex]++;
                $virtualTitles[$monthIndex][] = [
                    'title' => $event->title,
                    'type' => $eventTypeName,
                ];
            }
        }

        $series = [];
        $colors = [];

        if (!$modality || $modality === 'in_person') {
            $series[] = [
                'name' => 'Presencial',
                'data' => $inPersonCounts,
                'titles' => $inPersonTitles,
            ];
            $colors[] = '#f59e0b'; // Amber
        }

        if (!$modality || $modality === 'virtual') {
            $series[] = [
                'name' => 'Virtual',
                'data' => $virtualCounts,
                'titles' => $virtualTitles,
            ];
            $colors[] = '#3b82f6'; // Blue
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 320,
                'parentHeightOffset' => 0,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => $series,
            'xaxis' => [
                'categories' => $months,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'stepSize' => 1,
            ],
            'colors' => $colors,
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 6,
                    'borderRadiusApplication' => 'end',
                    'columnWidth' => '45%',
                ],
            ],
            'grid' => [
                'show' => true,
                'borderColor' => '#f1f5f9',
                'strokeDashArray' => 4,
                'padding' => [
                    'top' => 10,
                    'right' => 10,
                    'bottom' => 0,
                    'left' => 10,
                ],
            ],
            'stroke' => [
                'show' => true,
                'width' => 2,
                'colors' => ['transparent'],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'show' => true,
                'position' => 'top',
                'horizontalAlign' => 'right',
                'fontFamily' => 'inherit',
                'fontWeight' => 500,
                'markers' => [
                    'width' => 10,
                    'height' => 10,
                    'radius' => 10,
                ],
                'itemMargin' => [
                    'horizontal' => 15,
                    'vertical' => 5,
                ],
            ],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            tooltip: {
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    var titles = w.config.series[seriesIndex].titles;
                    var monthTitles = titles ? titles[dataPointIndex] : [];
                    var count = w.config.series[seriesIndex].data[dataPointIndex];
                    if (!monthTitles || monthTitles.length === 0) {
                        return `<div style='padding: 8px; font-size: 12px; color: #94a3b8; font-family: inherit;'>Sin eventos en este mes</div>`;
                    }
                    
                    var isDark = document.querySelector('html').classList.contains('dark');
                    var bgColor = isDark ? '#1e293b' : '#ffffff';
                    var borderColor = isDark ? '#334155' : '#e2e8f0';
                    var borderBottomColor = isDark ? '#334155' : '#f1f5f9';
                    var textColor = isDark ? '#f8fafc' : '#0f172a';
                    var badgeBg = isDark ? '#334155' : '#f1f5f9';
                    var badgeText = isDark ? '#cbd5e1' : '#475569';
                    var itemTextColor = isDark ? '#cbd5e1' : '#334155';
                    
                    var dotColor = w.config.series[seriesIndex].name === 'Presencial' ? '#f59e0b' : '#3b82f6';
                    var displayTitles = monthTitles;
                    var extraCount = 0;
                    if (monthTitles.length > 5) {
                        displayTitles = monthTitles.slice(0, 5);
                        extraCount = monthTitles.length - 5;
                    }
                    var list = displayTitles.map(function(item) {
                        return `<div style='display: flex; align-items: flex-start; gap: 8px; font-size: 12px; line-height: 1.4; color: ${itemTextColor}; margin-bottom: 6px;'>` +
                            `<span style='color: #94a3b8; flex-shrink: 0; margin-top: 1px;'>•</span>` +
                            `<div style='display: flex; flex-direction: column; overflow: hidden;'>` +
                            `<span style='font-weight: 600; color: ${textColor}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;'>${item.title}</span>` +
                            `<span style='font-size: 10px; color: #94a3b8; font-weight: 500;'>${item.type}</span>` +
                            `</div>` +
                            `</div>`;
                    }).join(``);
                    if (extraCount > 0) {
                        list += `<div style='font-size: 10px; color: #94a3b8; margin-top: 4px; padding-left: 12px; font-weight: 600;'>+ ${extraCount} eventos más...</div>`;
                    }
                    return `<div style='padding: 12px; background-color: ${bgColor}; border: 1px solid ${borderColor}; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); border-radius: 12px; min-width: 220px; max-width: 280px; font-family: inherit; pointer-events: none;'>` +
                        `<div style='display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 8px; padding-bottom: 8px; border-b: 1px solid ${borderBottomColor};'>` +
                        `<div style='display: flex; align-items: center; gap: 6px;'>` +
                        `<span style='width: 8px; height: 8px; border-radius: 50%; background-color: ${dotColor}; display: inline-block;'></span>` +
                        `<span style='font-weight: 700; font-size: 12px; color: ${textColor};'>${w.config.series[seriesIndex].name}</span>` +
                        `</div>` +
                        `<span style='padding: 2px 8px; font-size: 10px; font-weight: 700; border-radius: 9999px; background-color: ${badgeBg}; color: ${badgeText};'>${count} ${count === 1 ? 'evento' : 'eventos'}</span>` +
                        `</div>` +
                        `<div style='display: flex; flex-direction: column;'>${list}</div>` +
                        `</div>`;
                }
            }
        }
        JS);
    }
}
