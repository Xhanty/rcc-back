<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Resources\Administration\PetitionResource\Pages;
use App\Models\Petition;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PetitionResource extends Resource
{
    protected static ?string $model = Petition::class;

    protected static ?string $modelLabel = 'Petición';

    protected static ?string $pluralModelLabel = 'Peticiones';

    protected static ?string $navigationLabel = 'Peticiones';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->prefixIcon(Heroicon::OutlinedUser)
                    ->readOnly(),

                TextInput::make('phone')
                    ->label('Teléfono')
                    ->prefixIcon(Heroicon::OutlinedPhone)
                    ->readOnly(),

                Textarea::make('petition')
                    ->label('Petición de Oración')
                    ->rows(6)
                    ->readOnly(),

                TextInput::make('status')
                    ->label('Estado')
                    ->prefixIcon(Heroicon::OutlinedCheckCircle)
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        default => $state,
                    })
                    ->readOnly(),

                TextInput::make('created_at')
                    ->label('Fecha de Envío')
                    ->prefixIcon(Heroicon::OutlinedCalendar)
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i A') : null)
                    ->readOnly(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->searchPlaceholder('Buscar por nombre o petición...')
            ->searchDebounce('500ms')
            ->emptyStateHeading('No hay peticiones de oración')
            ->emptyStateDescription('Las peticiones enviadas desde la web aparecerán aquí.')
            ->emptyStateIcon(Heroicon::OutlinedHeart)
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('petition')
                    ->label('Petición')
                    ->searchable()
                    ->limit(50),

                SelectColumn::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                    ])
                    ->sortable()
                    ->selectablePlaceholder(false),

                TextColumn::make('created_at')
                    ->label('Fecha de Envío')
                    ->dateTime('d/m/Y H:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendientes',
                        'completed' => 'Completados',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Ver Petición')
                        ->icon(Heroicon::OutlinedEye)
                        ->color('info')
                        ->modalHeading('Detalle de la Petición de Oración')
                        ->modalSubmitActionLabel(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->modalWidth('lg')
                        ->slideOver(),

                    DeleteAction::make()
                        ->label('Eliminar')
                        ->icon(Heroicon::OutlinedTrash),
                ])
                    ->tooltip('Opciones')
                    ->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePetitions::route('/'),
        ];
    }
}
