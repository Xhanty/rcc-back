<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Resources\Administration\EventResource\Pages;
use App\Models\Event;
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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $modelLabel = 'Evento';

    protected static ?string $pluralModelLabel = 'Eventos';

    protected static ?string $navigationLabel = 'Eventos';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Información General')
                        ->schema([
                            TextInput::make('title')
                                ->label('Título')
                                ->prefixIcon(Heroicon::OutlinedTicket)
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(string $operation, ?string $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                                ->placeholder('Ej. Conferencia Anual de Jóvenes'),

                            TextInput::make('slug')
                                ->label('Slug')
                                ->prefixIcon(Heroicon::OutlinedLink)
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->readOnly()
                                ->placeholder('ej. conferencia-anual-de-jovenes'),

                            Select::make('event_type_id')
                                ->label('Tipo de Evento')
                                ->relationship('eventType', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder('Selecciona un tipo de evento'),

                            Textarea::make('short_description')
                                ->label('Descripción Corta')
                                ->rows(3)
                                ->maxLength(500)
                                ->placeholder('Una breve descripción que se mostrará en las tarjetas de eventos...'),
                        ]),

                    Step::make('Contenido y Multimedia')
                        ->schema([
                            RichEditor::make('content')
                                ->label('Contenido Detallado')
                                ->placeholder('Escribe la descripción completa y detalles del evento aquí...')
                                ->columnSpanFull(),

                            FileUpload::make('banner_image_path')
                                ->label('Imagen de Portada (Banner)')
                                ->image()
                                ->maxSize(5120)
                                ->visibility('public')
                                ->directory('events/banners')
                                ->columnSpanFull(),
                        ]),

                    Step::make('Modalidad y Ubicación')
                        ->schema([
                            Select::make('modality')
                                ->label('Modalidad')
                                ->options([
                                    'in_person' => 'Presencial',
                                    'virtual' => 'Virtual',
                                ])
                                ->required()
                                ->live()
                                ->default('in_person'),

                            TextInput::make('venue_name')
                                ->label('Nombre del Lugar')
                                ->prefixIcon(Heroicon::OutlinedBuildingOffice)
                                ->visible(fn(Get $get): bool => $get('modality') === 'in_person')
                                ->required(fn(Get $get): bool => $get('modality') === 'in_person')
                                ->maxLength(255)
                                ->placeholder('Ej. Auditorio Principal'),

                            TextInput::make('address')
                                ->label('Dirección')
                                ->prefixIcon(Heroicon::OutlinedMapPin)
                                ->visible(fn(Get $get): bool => $get('modality') === 'in_person')
                                ->required(fn(Get $get): bool => $get('modality') === 'in_person')
                                ->maxLength(255)
                                ->placeholder('Ej. Av. Siempreviva 123'),

                            TextInput::make('live_url')
                                ->label('Enlace de la Transmisión (URL)')
                                ->prefixIcon(Heroicon::OutlinedVideoCamera)
                                ->visible(fn(Get $get): bool => $get('modality') === 'virtual')
                                ->required(fn(Get $get): bool => $get('modality') === 'virtual')
                                ->url()
                                ->maxLength(2048)
                                ->placeholder('Ej. https://youtube.com/live/... o Zoom link'),
                        ]),

                    Step::make('Fechas y Configuración')
                        ->schema([
                            DateTimePicker::make('start_datetime')
                                ->label('Fecha y Hora de Inicio')
                                ->required()
                                ->native(false)
                                ->live()
                                ->placeholder('Selecciona la fecha y hora de inicio'),

                            DateTimePicker::make('end_datetime')
                                ->label('Fecha y Hora de Fin')
                                ->native(false)
                                ->afterOrEqual('start_datetime')
                                ->placeholder('Opcional'),

                            Select::make('status')
                                ->label('Estado')
                                ->options([
                                    'draft' => 'Borrador',
                                    'published' => 'Publicado',
                                    'not_published' => 'No Publicado',
                                    'cancelled' => 'Cancelado',
                                    'completed' => 'Completado',
                                ])
                                ->required()
                                ->default('draft'),

                            Toggle::make('is_featured')
                                ->label('Destacado (Mostrar en portada)')
                                ->default(false),
                        ]),
                ])
                    ->columnSpanFull()
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->searchPlaceholder('Buscar por título...')
            ->searchDebounce('500ms')
            ->emptyStateHeading('No hay eventos registrados')
            ->emptyStateDescription('Crea un nuevo evento para comenzar.')
            ->emptyStateIcon(Heroicon::OutlinedCalendar)
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('eventType.name')
                    ->label('Tipo de Evento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('modality')
                    ->label('Modalidad')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in_person' => 'Presencial',
                        'virtual' => 'Virtual',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'in_person' => 'info',
                        'virtual' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                SelectColumn::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                        'not_published' => 'No Publicado',
                        'cancelled' => 'Cancelado',
                        'completed' => 'Completado',
                    ])
                    ->sortable()
                    ->selectablePlaceholder(false),

                TextColumn::make('start_datetime')
                    ->label('Fecha de Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_type_id')
                    ->label('Tipo de Evento')
                    ->relationship('eventType', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('modality')
                    ->label('Modalidad')
                    ->options([
                        'in_person' => 'Presencial',
                        'virtual' => 'Virtual',
                    ]),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                        'not_published' => 'No Publicado',
                        'cancelled' => 'Cancelado',
                        'completed' => 'Completado',
                    ]),

                TernaryFilter::make('is_featured')
                    ->label('Destacado')
                    ->trueLabel('Solo Destacados')
                    ->falseLabel('No Destacados')
                    ->placeholder('Todos'),

                TrashedFilter::make()->native(false),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Modificar')
                        ->icon(Heroicon::OutlinedPencil)
                        ->color('info'),

                    Action::make('viewAssistants')
                        ->label('Ver Asistentes')
                        ->icon(Heroicon::OutlinedUsers)
                        ->color('success')
                        ->url(fn(Event $record): string => static::getUrl('assistants', ['record' => $record])),

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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'assistants' => Pages\ViewEventAssistants::route('/{record}/assistants'),
        ];
    }
}
