<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Concerns\HasModulePermissions;
use App\Filament\Resources\Administration\MinistryResource\Pages;
use App\Models\Ministry;
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
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class MinistryResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = Ministry::class;

    protected static ?string $modelLabel = 'Ministerio';

    protected static ?string $pluralModelLabel = 'Ministerios';

    protected static function getPermissionModule(): string
    {
        return 'ministries';
    }

    protected static ?string $navigationLabel = 'Ministerios';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Ministerio')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Título del Ministerio')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, ?string $state, $set) {
                                        if ($operation === 'create' && filled($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->placeholder('Ej. Música (Salmistas), Intercesión, Jóvenes'),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->prefixIcon(Heroicon::OutlinedLink)
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('ej. musica-salmistas'),
                            ]),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Explica de qué trata el ministerio y cuál es su propósito en la comunidad...')
                            ->columnSpanFull(),

                        RichEditor::make('detail')
                            ->label('Información Adicional / Detalle (Opcional)')
                            ->placeholder('Agrega información ampliada sobre actividades, requisitos para unirse o misión del ministerio...')
                            ->columnSpanFull(),

                        FileUpload::make('image_path')
                            ->label('Imagen del Ministerio (Opcional)')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('ministries')
                            ->columnSpanFull(),
                    ]),

                Section::make('Liderazgo y Servidores')
                    ->description('Selecciona uno o varios líderes y el equipo de servidores entre los usuarios registrados en el panel administrativo.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('leaders')
                                    ->label('Líder(es) del Ministerio')
                                    ->relationship('leaders', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (?array $state, Get $get, Set $set): void {
                                        if (empty($state)) {
                                            return;
                                        }

                                        $leaderIds = array_map('strval', $state);
                                        $servants = (array) ($get('servants') ?? []);

                                        $filteredServants = array_values(array_filter(
                                            $servants,
                                            fn ($id): bool => ! in_array((string) $id, $leaderIds, true)
                                        ));

                                        if (count($filteredServants) !== count($servants)) {
                                            $set('servants', $filteredServants);
                                        }
                                    })
                                    ->prefixIcon(Heroicon::OutlinedUser)
                                    ->placeholder('Selecciona uno o varios líderes')
                                    ->helperText('Puedes asignar uno o más líderes para este ministerio.'),

                                Select::make('servants')
                                    ->label('Servidores del Ministerio')
                                    ->relationship(
                                        name: 'servants',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: function (Builder $query, Get $get): Builder {
                                            $leaders = array_filter((array) ($get('leaders') ?? []));

                                            return $query->when(
                                                ! empty($leaders),
                                                fn (Builder $q): Builder => $q->whereNotIn('users.id', $leaders)
                                            );
                                        }
                                    )
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon(Heroicon::OutlinedUsers)
                                    ->placeholder('Selecciona los servidores')
                                    ->helperText('Miembros de Resucitados que apoyan y sirven en este ministerio.'),
                            ]),
                    ]),

                Section::make('Día, Horario de Reunión y Visibilidad')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('meeting_day')
                                    ->label('Día de Reunión')
                                    ->options(Ministry::dayOptions())
                                    ->searchable()
                                    ->nullable()
                                    ->prefixIcon(Heroicon::OutlinedCalendar)
                                    ->placeholder('Selecciona el día'),

                                TimePicker::make('meeting_time')
                                    ->label('Horario de Reunión')
                                    ->prefixIcon(Heroicon::OutlinedClock)
                                    ->seconds(false)
                                    ->native(false)
                                    ->displayFormat('g:i A')
                                    ->format('H:i')
                                    ->placeholder('Selecciona la hora'),

                                TextInput::make('meeting_location')
                                    ->label('Lugar de Reunión (Opcional)')
                                    ->prefixIcon(Heroicon::OutlinedMapPin)
                                    ->maxLength(255)
                                    ->placeholder('Ej. Salón Parroquial / Virtual'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label('Orden de Visualización')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->default(fn (): int => max(1, ((int) Ministry::query()->max('sort_order')) + 1))
                                    ->minValue(1)
                                    ->unique(
                                        table: Ministry::class,
                                        column: 'sort_order',
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')
                                    )
                                    ->validationMessages([
                                        'unique' => 'Ya existe otro ministerio con este número de orden.',
                                    ])
                                    ->helperText('Número único de posición (1 aparece primero en la página principal).'),

                                Toggle::make('is_active')
                                    ->label('Visible en la Página Principal')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Desactívalo si deseas ocultarlo temporalmente del sitio público.'),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->reorderable(fn (): ?string => (Auth::user()?->hasPermission('ministries', 'edit') ?? false) ? 'sort_order' : null)
            ->striped()
            ->searchPlaceholder('Buscar por título o líder...')
            ->emptyStateHeading('No hay ministerios registrados')
            ->emptyStateDescription('Crea el primer ministerio de Resucitados RCC para mostrarlo en la página principal.')
            ->emptyStateIcon(Heroicon::OutlinedUserGroup)
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Imagen')
                    ->disk('public')
                    ->height(44)
                    ->circular()
                    ->defaultImageUrl(asset('icon.png')),

                TextColumn::make('title')
                    ->label('Ministerio')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('leaders.name')
                    ->label('Líder(es)')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->placeholder('Sin asignar'),

                TextColumn::make('servants_count')
                    ->label('Servidores')
                    ->counts('servants')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? '1 servidor' : "{$state} servidores")
                    ->alignCenter(),

                TextColumn::make('meeting_day')
                    ->label('Reunión')
                    ->formatStateUsing(function (Ministry $record): string {
                        $time = $record->meeting_time;
                        if (filled($time) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
                            $time = \Carbon\Carbon::createFromFormat('H:i', substr($time, 0, 5))->format('g:i A');
                        }

                        $parts = array_filter([$record->meeting_day, $time]);

                        return count($parts) > 0 ? implode(' · ', $parts) : '—';
                    })
                    ->description(fn (Ministry $record): ?string => $record->meeting_location),

                ToggleColumn::make('is_active')
                    ->label('Visible')
                    ->disabled(fn (): bool => ! (Auth::user()?->hasPermission('ministries', 'edit') ?? false)),

                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('meeting_day')
                    ->label('Día de Reunión')
                    ->options(Ministry::dayOptions()),

                TernaryFilter::make('is_active')
                    ->label('Visibilidad')
                    ->trueLabel('Solo visibles')
                    ->falseLabel('Solo ocultos'),

                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMinistries::route('/'),
            'create' => Pages\CreateMinistry::route('/create'),
            'edit'   => Pages\EditMinistry::route('/{record}/edit'),
        ];
    }
}
