<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Resources\Administration\AssistantResource\Pages;
use App\Models\Assistant;
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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AssistantResource extends Resource
{
    protected static ?string $model = Assistant::class;

    protected static ?string $modelLabel = 'Asistente';

    protected static ?string $pluralModelLabel = 'Asistentes';

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasModuleAccess('assistants') ?? false;
    }

    protected static ?string $navigationLabel = 'Asistentes';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('document')
                    ->label('Documento')
                    ->prefixIcon(Heroicon::OutlinedIdentification)
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Ej. 123456789'),

                TextInput::make('name')
                    ->label('Nombre Completo')
                    ->prefixIcon(Heroicon::OutlinedUser)
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej. Juan Pérez'),

                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->prefixIcon(Heroicon::OutlinedEnvelope)
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('ejemplo@correo.com'),

                TextInput::make('phone')
                    ->label('Teléfono')
                    ->prefixIcon(Heroicon::OutlinedPhone)
                    ->tel()
                    ->maxLength(30)
                    ->placeholder('Ej. +57 300 123 4567'),

                DatePicker::make('birth_date')
                    ->label('Fecha de Nacimiento')
                    ->prefixIcon(Heroicon::OutlinedCake)
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(now())
                    ->required()
                    ->placeholder('Selecciona la fecha de nacimiento'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->searchPlaceholder('Buscar por documento, nombre o correo...')
            ->searchDebounce('500ms')
            ->emptyStateHeading('No hay asistentes registrados')
            ->emptyStateDescription('Crea un nuevo asistente para comenzar a registrar su participación en eventos.')
            ->emptyStateIcon(Heroicon::OutlinedIdentification)
            ->columns([
                TextColumn::make('document')
                    ->label('Documento')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Documento copiado')
                    ->copyMessageDuration(1500),

                TextColumn::make('name')
                    ->label('Nombre Completo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Correo copiado')
                    ->copyMessageDuration(1500),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('birth_date')
                    ->label('Fecha de Nacimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Registrado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Modificado el')
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
                        ->modalHeading('Modificar Asistente')
                        ->modalSubmitActionLabel('Modificar')
                        ->modalCancelActionLabel('Cancelar')
                        ->modalWidth('md')
                        ->slideOver(),

                    Action::make('viewEvents')
                        ->label('Ver Eventos Asistidos')
                        ->icon(Heroicon::OutlinedCalendar)
                        ->color('success')
                        ->url(fn(Assistant $record): string => static::getUrl('events', ['record' => $record])),

                    DeleteAction::make()
                        ->label('Eliminar')
                        ->icon(Heroicon::OutlinedTrash),

                    RestoreAction::make()
                        ->label('Restaurar')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('success'),

                    ForceDeleteAction::make()
                        ->label('Eliminar permanentemente')
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
            'index' => Pages\ManageAssistants::route('/'),
            'events' => Pages\ViewAssistantEvents::route('/{record}/events'),
        ];
    }
}
