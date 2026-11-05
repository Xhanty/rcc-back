<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Escritorio';

    protected static ?string $navigationLabel = 'Escritorio';

    public function getSubheading(): ?string
    {
        return 'Resumen general de la actividad y participación en Resucitados RCC';
    }

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'md' => 3,
            'lg' => 3,
            'xl' => 3,
        ];
    }
}
