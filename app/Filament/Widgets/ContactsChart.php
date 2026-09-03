<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use App\Models\Petition;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Width;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ContactsChart extends ApexChartWidget
{
    use HasFiltersSchema;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user?->hasModuleAccess('contacts') || $user?->hasModuleAccess('petitions') ?: false;
    }

    protected static ?int $sort = 3;

    protected static ?string $chartId = 'contactsChart';

    protected int|string|array $columnSpan = 1;

    protected static Width|string $filterFormWidth = Width::Small;

    public function getHeading(): string
    {
        $type = $this->filters['type'] ?? 'contacts';
        return $type === 'contacts' ? 'Mensajes de Contacto' : 'Peticiones de Oración';
    }

    public function getSubheading(): string
    {
        $type = $this->filters['type'] ?? 'contacts';
        return $type === 'contacts' 
            ? 'Mensajes por estado (Pendientes y Completados)' 
            : 'Peticiones por estado (Pendientes y Completados)';
    }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->label('Ver información de')
                ->options([
                    'contacts' => 'Mensajes de Contacto',
                    'petitions' => 'Peticiones de Oración',
                ])
                ->default('contacts')
                ->selectablePlaceholder(false),
        ]);
    }

    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->updateOptions();
    }

    protected function getOptions(): array
    {
        $type = $this->filters['type'] ?? 'contacts';

        if ($type === 'contacts') {
            $pendingCount = Contact::query()->where('status', 'pending')->count();
            $completedCount = Contact::query()->where('status', 'completed')->count();
        } else {
            $pendingCount = Petition::query()->where('status', 'pending')->count();
            $completedCount = Petition::query()->where('status', 'completed')->count();
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 394,
            ],
            'series' => [
                $pendingCount,
                $completedCount,
            ],
            'labels' => [
                'Pendientes',
                'Completados',
            ],
            'colors' => [
                '#f59e0b', // Amber/Warning color for pending
                '#10b981', // Emerald/Success color for completed
            ],
            'legend' => [
                'position' => 'bottom',
                'horizontalAlign' => 'center',
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '65%',
                        'labels' => [
                            'show' => true,
                            'total' => [
                                'show' => true,
                                'label' => 'Total',
                                'color' => '#888888',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
