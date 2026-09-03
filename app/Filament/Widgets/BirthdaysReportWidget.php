<?php

namespace App\Filament\Widgets;

use App\Models\Assistant;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BirthdaysReportWidget extends TableWidget
{
    public static function canView(): bool
    {
        return Auth::user()?->hasModuleAccess('assistants') ?? false;
    }

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Reporte de Asistentes Cumpleañeros')
            ->description('Listado de asistentes con su fecha de nacimiento y edad')
            ->query(
                Assistant::query()
                    ->whereNotNull('birth_date')
            )
            ->defaultSort('birth_date', 'desc')
            ->striped()
            ->searchPlaceholder('Buscar por cédula, nombre o correo...')
            ->searchDebounce('500ms')
            ->emptyStateHeading('No hay cumpleañeros registrados')
            ->emptyStateDescription('No se encontraron asistentes con fecha de nacimiento para los filtros seleccionados.')
            ->emptyStateIcon(Heroicon::OutlinedCake)
            ->columns([
                TextColumn::make('document')
                    ->label('Cédula / Documento')
                    ->icon(Heroicon::OutlinedIdentification)
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Cédula copiada')
                    ->copyMessageDuration(1500),

                TextColumn::make('name')
                    ->label('Nombre Completo')
                    ->icon(Heroicon::OutlinedUser)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Correo copiado')
                    ->copyMessageDuration(1500),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->icon(Heroicon::OutlinedPhone)
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('birth_date')
                    ->label('Fecha de Nacimiento')
                    ->icon(Heroicon::OutlinedCake)
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('birthday_formatted')
                    ->label('Día / Mes')
                    ->state(fn (Assistant $record) => $record->birth_date ? $record->birth_date->translatedFormat('d \d\e F') : '-')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('age')
                    ->label('Edad Actual')
                    ->state(fn (Assistant $record) => $record->birth_date ? $record->birth_date->age . ' años' : '-')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->label('Mes de Cumpleaños')
                    ->options([
                        '1' => 'Enero',
                        '2' => 'Febrero',
                        '3' => 'Marzo',
                        '4' => 'Abril',
                        '5' => 'Mayo',
                        '6' => 'Junio',
                        '7' => 'Julio',
                        '8' => 'Agosto',
                        '9' => 'Septiembre',
                        '10' => 'Octubre',
                        '11' => 'Noviembre',
                        '12' => 'Diciembre',
                    ])
                    ->default((string) now()->month)
                    ->selectablePlaceholder(false)
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'])) {
                            $query->whereMonth('birth_date', $data['value']);
                        }
                    })
                    ->native(false),
            ]);
    }
}
