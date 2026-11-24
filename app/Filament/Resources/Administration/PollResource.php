<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Concerns\HasModulePermissions;
use App\Filament\Resources\Administration\PollResource\Pages;
use App\Models\Poll;
use BackedEnum;
use Filament\Actions\Action;
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
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class PollResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = Poll::class;

    protected static ?string $modelLabel = 'Encuesta / Inscripción';

    protected static ?string $pluralModelLabel = 'Encuestas e Inscripciones';

    protected static function getPermissionModule(): string
    {
        return 'polls';
    }

    protected static ?string $navigationLabel = 'Encuestas';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resultados e Inscritos')
                    ->description('Resultados o personas inscritas en tiempo real.')
                    ->visibleOn('edit')
                    ->schema([
                        View::make('filament.components.poll-results'),
                    ]),

                Section::make('Configuración General')
                    ->schema([
                        Toggle::make('requires_registration')
                            ->label('Habilitar como Inscripción / Registro con Documento (Retiros o Actividades)')
                            ->default(false)
                            ->live()
                            ->helperText('Al activarlo se ocultan las opciones de votación múltiple y la restricción por IP: la persona se inscribe ingresando su Documento de Identidad, carga/actualiza sus datos de Asistente y acepta Términos y Condiciones (Habeas Data).'),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('tag')
                                    ->label('Categoría / Etiqueta (Opcional)')
                                    ->maxLength(100)
                                    ->placeholder('Ej. FORMACIÓN & RETIROS, VIDA COMUNITARIA'),

                                TextInput::make('closing_label')
                                    ->label('Texto de Cierre / Estado (Opcional)')
                                    ->maxLength(150)
                                    ->placeholder(fn (Get $get): string => $get('requires_registration')
                                        ? 'Ej. Cupos disponibles hasta el Viernes'
                                        : 'Ej. Cierra el Domingo 11:00 AM'),
                            ]),

                        TextInput::make('question')
                            ->label(fn (Get $get): string => $get('requires_registration')
                                ? 'Título o Invitación de la Actividad / Retiro'
                                : 'Pregunta de la Encuesta')
                            ->required()
                            ->maxLength(500)
                            ->placeholder(fn (Get $get): string => $get('requires_registration')
                                ? 'Ej. ¿Vas a asistir al próximo Retiro Espiritual? Regístrate aquí con tu documento'
                                : 'Ej. ¿Qué tema te gustaría profundizar en el próximo retiro comunitario?')
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('closes_at')
                                    ->label('Fecha de Cierre Automático (Opcional)')
                                    ->native(false)
                                    ->placeholder('Sin fecha límite'),

                                TextInput::make('sort_order')
                                    ->label('Orden de Visualización')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->default(fn (): int => max(1, ((int) Poll::query()->max('sort_order')) + 1))
                                    ->minValue(1)
                                    ->unique(
                                        table: Poll::class,
                                        column: 'sort_order',
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')
                                    )
                                    ->validationMessages([
                                        'unique' => 'Ya existe otra encuesta con este número de orden.',
                                    ]),

                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Solo las activas se muestran en la página web.'),
                            ]),

                        Toggle::make('restrict_by_ip')
                            ->label('Restricción estricta: 1 voto por dirección IP')
                            ->default(false)
                            ->visible(fn (Get $get): bool => ! (bool) $get('requires_registration'))
                            ->helperText('Déjalo desactivado para permitir que varias personas conectadas al mismo Wi-Fi puedan votar desde sus propios celulares.'),
                    ]),

                Section::make('Opciones de Respuesta')
                    ->description('Agrega las opciones entre las que podrán elegir las personas (mínimo 2 opciones).')
                    ->visible(fn (Get $get): bool => ! (bool) $get('requires_registration'))
                    ->schema([
                        Repeater::make('options')
                            ->label('Opciones')
                            ->relationship('options')
                            ->orderColumn('sort_order')
                            ->visible(fn (Get $get): bool => ! (bool) $get('requires_registration'))
                            ->schema([
                                TextInput::make('label')
                                    ->label('Texto de la Opción')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej. Sanación interior y liberación en el Espíritu'),
                            ])
                            ->defaultItems(2)
                            ->minItems(fn (Get $get): int => $get('requires_registration') ? 0 : 2)
                            ->addActionLabel('+ Agregar opción')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Nueva opción')
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->reorderable(fn (): ?string => (Auth::user()?->hasPermission('polls', 'edit') ?? false) ? 'sort_order' : null)
            ->striped()
            ->searchPlaceholder('Buscar por pregunta o etiqueta...')
            ->emptyStateHeading('No hay encuestas ni inscripciones registradas')
            ->emptyStateDescription('Crea una encuesta comunitaria o un formulario de inscripción a actividades.')
            ->emptyStateIcon(Heroicon::OutlinedChartPie)
            ->columns([
                TextColumn::make('question')
                    ->label('Pregunta / Actividad')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->description(fn (Poll $record): ?string => $record->tag),

                TextColumn::make('requires_registration')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? '📝 Inscripción (Documento)' : '📊 Votación Rápida')
                    ->color(fn (bool $state): string => $state ? 'warning' : 'info')
                    ->alignCenter(),

                TextColumn::make('votes_count')
                    ->label('Participaciones')
                    ->counts('votes')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(function (int $state, Poll $record): string {
                        if ($record->requires_registration) {
                            return $state === 1 ? '1 inscrito' : "{$state} inscritos";
                        }

                        return $state === 1 ? '1 voto' : "{$state} votos";
                    })
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('closing_label')
                    ->label('Estado / Cierre')
                    ->placeholder('—')
                    ->description(fn (Poll $record): ?string => $record->closes_at ? 'Cierra: ' . $record->closes_at->format('d/m/Y H:i') : null),

                ToggleColumn::make('is_active')
                    ->label('Activa')
                    ->disabled(fn (): bool => ! (Auth::user()?->hasPermission('polls', 'edit') ?? false)),

                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('requires_registration')
                    ->label('Tipo')
                    ->trueLabel('Solo inscripciones (con Documento)')
                    ->falseLabel('Solo encuestas de votación'),

                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),

                TrashedFilter::make(),
            ])
            ->actions([
                Action::make('results')
                    ->label(fn (Poll $record): string => $record->requires_registration ? 'Ver inscritos' : 'Ver conteo')
                    ->icon(Heroicon::OutlinedChartBar)
                    ->color('warning')
                    ->modalHeading(fn (Poll $record): string => $record->requires_registration ? 'Personas Inscritas' : 'Conteo de Votos')
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(fn (Poll $record) => view('filament.components.poll-results', ['poll' => $record])),

                ActionGroup::make([
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
            'index'  => Pages\ListPolls::route('/'),
            'create' => Pages\CreatePoll::route('/create'),
            'edit'   => Pages\EditPoll::route('/{record}/edit'),
        ];
    }
}
