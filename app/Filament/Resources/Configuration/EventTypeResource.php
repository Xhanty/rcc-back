<?php

namespace App\Filament\Resources\Configuration;

use App\Filament\Resources\Configuration\EventTypeResource\Pages;
use App\Models\EventType;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class EventTypeResource extends Resource
{
    protected static ?string $model = EventType::class;

    protected static ?string $modelLabel = 'Tipo de Evento';

    protected static ?string $pluralModelLabel = 'Tipos de Eventos';

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasModuleAccess('event_types') ?? false;
    }

    protected static ?string $navigationLabel = 'Tipo de Eventos';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tipo de Evento')
                    ->prefixIcon(Heroicon::OutlinedTag)
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(string $operation, ?string $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                    ->placeholder('Ej. Bautizo'),

                TextInput::make('slug')
                    ->label('Slug')
                    ->prefixIcon(Heroicon::OutlinedLink)
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->readOnly()
                    ->placeholder('ej. bautizo'),

                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3)
                    ->maxLength(65535)
                    ->placeholder('Breve descripción del tipo de evento...')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->striped()
            ->searchPlaceholder('Buscar por nombre o descripción...')
            ->searchDebounce('500ms')
            ->emptyStateHeading('No hay tipos de eventos registrados')
            ->emptyStateDescription('Crea un nuevo tipo de evento para comenzar.')
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->columns([
                TextColumn::make('name')
                    ->label('Tipo de Evento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(fn(?string $state): ?string => $state)
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Última Modificación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()->native(false),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Modificar')
                        ->icon(Heroicon::OutlinedPencil)
                        ->color('info')
                        ->modalHeading('Modificar Tipo de Evento')
                        ->modalSubmitActionLabel('Modificar')
                        ->modalCancelActionLabel('Cancelar')
                        ->modalWidth('md')
                        ->slideOver(),

                    DeleteAction::make()
                        ->label('Eliminar')
                        ->icon(Heroicon::OutlinedTrash)
                        ->color('danger'),

                    RestoreAction::make()
                        ->label('Restaurar')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('success'),

                    ForceDeleteAction::make()
                        ->label('Eliminar permanente')
                        ->icon(Heroicon::OutlinedTrash)
                        ->color('danger'),
                ])
                    ->tooltip('Opciones')
                    ->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEventTypes::route('/'),
        ];
    }
}
