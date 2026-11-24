<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Concerns\HasModulePermissions;
use App\Filament\Resources\Administration\ReflectionResource\Pages;
use App\Models\Reflection;
use App\Models\User;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use UnitEnum;

class ReflectionResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = Reflection::class;

    protected static ?string $modelLabel = 'Lectura o Reflexión';

    protected static ?string $pluralModelLabel = 'Lecturas y Reflexiones';

    protected static function getPermissionModule(): string
    {
        return 'reflections';
    }

    protected static ?string $navigationLabel = 'Lecturas y Reflexiones';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Principal de la Lectura / Reflexión')
                    ->description('Publica reflexiones, cartas pastorales o artículos de formación espiritual para la comunidad.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(function (?string $state, Set $set): void {
                                        if (filled($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->placeholder('Ej. Cómo discernir los carismas en la vida cotidiana'),

                                TextInput::make('slug')
                                    ->label('Slug (URL amigable)')
                                    ->prefixIcon(Heroicon::OutlinedLink)
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->readOnly()
                                    ->placeholder('Se genera automáticamente desde el título'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('category')
                                    ->label('Categoría / Etiqueta Superior')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Ej. CARISMAS & VIDA, TEOLOGÍA & MAGISTERIO, ORACIÓN DE INTERCESIÓN'),

                                DatePicker::make('published_at')
                                    ->label('Fecha de Publicación')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->default(now()),

                                TextInput::make('reading_time_minutes')
                                    ->label('Tiempo de Lectura (Minutos)')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(120)
                                    ->default(5)
                                    ->suffix('min lectura')
                                    ->helperText('Se muestra como «5 MIN LECTURA» en la tarjeta.'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Autor (Usuario del Sistema · Opcional)')
                                    ->options(fn () => User::query()
                                        ->whereRaw('LOWER(email) != ?', ['admin@resucitadosrcc.com'])
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set): void {
                                        if ($state) {
                                            $user = User::find($state);
                                            if ($user) {
                                                $set('author_name', $user->name);
                                            }
                                        }
                                    })
                                    ->helperText('Si seleccionas un usuario, toma su nombre y avatar automáticamente.'),

                                TextInput::make('author_name')
                                    ->label('Nombre del Autor / Ministerio visible en la Tarjeta')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej. Pbro. David Solano, Elena Martínez o Ministerio Intercesión')
                                    ->helperText('Las iniciales (ej. DS, EM, MI) se calculan automáticamente a partir de este nombre.'),
                            ]),

                        Textarea::make('excerpt')
                            ->label('Resumen / Introducción Corta (se muestra en la tarjeta)')
                            ->required()
                            ->rows(3)
                            ->maxLength(600)
                            ->placeholder('Ej. No todos los dones se manifiestan dentro del templo. Aprende a reconocer las mociones del Espíritu Santo en tu trabajo...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Contenido Completo e Imagen de Portada')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Imagen de Portada')
                            ->image()
                            ->disk('public')
                            ->directory('reflections')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Contenido Completo de la Lectura / Reflexión')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                if (filled($state) && (! $get('reading_time_minutes') || (int) $get('reading_time_minutes') === 5)) {
                                    $set('reading_time_minutes', Reflection::estimateReadingTime($state));
                                }
                            })
                            ->placeholder('Escribe aquí el desarrollo completo de la reflexión o carta pastoral...')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Publicada / Activa en la Web')
                            ->default(true)
                            ->inline(false),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->striped()
            ->searchPlaceholder('Buscar por título, categoría o autor...')
            ->emptyStateHeading('No hay lecturas ni reflexiones registradas')
            ->emptyStateDescription('Publica la primera reflexión o carta pastoral para la comunidad.')
            ->emptyStateIcon(Heroicon::OutlinedNewspaper)
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Portada')
                    ->disk('public')
                    ->height(44)
                    ->defaultImageUrl(asset('icon.png'))
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50)
                    ->description(fn (Reflection $record): string => "{$record->reading_time_minutes} MIN LECTURA · Autor: {$record->display_author_name} ({$record->author_initials})"),

                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Fecha')
                    ->date('d M Y')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Publicada')
                    ->disabled(fn (): bool => ! (Auth::user()?->hasPermission('reflections', 'edit') ?? false)),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Solo publicadas')
                    ->falseLabel('Solo ocultas'),

                TrashedFilter::make(),
            ])
            ->actions([
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
            ->with('user')
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
            'index'  => Pages\ListReflections::route('/'),
            'create' => Pages\CreateReflection::route('/create'),
            'edit'   => Pages\EditReflection::route('/{record}/edit'),
        ];
    }
}
