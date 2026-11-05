<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class Reports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reportes';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?string $title = 'Reportes';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.reports';

    // Pagination state
    public int $birthdaysPage = 1;
    public int $pastoreoPage = 1;
    public int $perPage = 15;

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public function getBirthdaysData(): array
    {
        $all = DB::select('SELECT * FROM vw_cumpleanos_mes_actual ORDER BY dia_cumpleanos ASC');
        $total = count($all);
        $offset = ($this->birthdaysPage - 1) * $this->perPage;
        $items = array_slice($all, $offset, $this->perPage);

        return [
            'items'       => $items,
            'total'       => $total,
            'currentPage' => $this->birthdaysPage,
            'lastPage'    => max(1, (int) ceil($total / $this->perPage)),
        ];
    }

    public function getPastoreoData(): array
    {
        $all = DB::select('SELECT * FROM vw_seguimiento_pastoreo ORDER BY dias_sin_asistir DESC');
        $total = count($all);
        $offset = ($this->pastoreoPage - 1) * $this->perPage;
        $items = array_slice($all, $offset, $this->perPage);

        return [
            'items'       => $items,
            'total'       => $total,
            'currentPage' => $this->pastoreoPage,
            'lastPage'    => max(1, (int) ceil($total / $this->perPage)),
        ];
    }

    public function birthdaysNextPage(): void
    {
        $data = $this->getBirthdaysData();
        if ($this->birthdaysPage < $data['lastPage']) {
            $this->birthdaysPage++;
        }
    }

    public function birthdaysPrevPage(): void
    {
        if ($this->birthdaysPage > 1) {
            $this->birthdaysPage--;
        }
    }

    public function pastoreoNextPage(): void
    {
        $data = $this->getPastoreoData();
        if ($this->pastoreoPage < $data['lastPage']) {
            $this->pastoreoPage++;
        }
    }

    public function pastoreoPrevPage(): void
    {
        if ($this->pastoreoPage > 1) {
            $this->pastoreoPage--;
        }
    }
}
